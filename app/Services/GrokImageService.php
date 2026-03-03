<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GrokImageService
{
    protected $apiKey;
    protected $visionApiUrl;
    protected $visionModel;
    protected $imagineApiUrl;
    protected $imagineModel;
    protected $imagineSize;
    protected $imagineQuality;
    protected $videoApiUrl;
    protected $videoModel;
    protected $maxTokens;
    protected $timeout;

    public function __construct()
    {
        // Get API key with priority: Database > Config > Env
        $this->apiKey = $this->getApiKey();
        $this->visionApiUrl = $this->getSettingOrConfig('grok_vision_api_url', 'image-prompt.grok.vision_api_url');
        $this->visionModel = $this->getSettingOrConfig('grok_vision_model', 'image-prompt.grok.vision_model');
        $this->imagineApiUrl = $this->getSettingOrConfig('grok_imagine_api_url', 'image-prompt.grok.imagine_api_url');
        $this->imagineModel = config('image-prompt.grok.imagine_model');
        $this->imagineSize = config('image-prompt.grok.imagine_size', '1024x1024');
        $this->imagineQuality = config('image-prompt.grok.imagine_quality', 'high');
        $this->videoApiUrl = $this->getSettingOrConfig('grok_video_api_url', 'image-prompt.grok.video_api_url');
        $this->videoModel = config('image-prompt.grok.video_model', 'grok-imagine-video');
        $this->maxTokens = config('image-prompt.grok.max_tokens', 2000);
        $this->timeout = (int) $this->getSettingOrConfig('grok_timeout', 'image-prompt.grok.timeout', 180);
    }

    /**
     * Get API key with priority: Database > Config
     */
    protected function getApiKey()
    {
        try {
            // Try database first
            $dbKey = \App\Models\Setting::get('grok_api_key');
            if ($dbKey && !empty($dbKey) && $dbKey !== 'your_openai_api_key_here') {
                Log::info('GrokImageService: Using API key from database settings');
                return $dbKey;
            }
        } catch (\Exception $e) {
            Log::warning('GrokImageService: Could not retrieve API key from database', [
                'error' => $e->getMessage()
            ]);
        }
        
        // Fall back to config (which may have env or default)
        $configKey = config('image-prompt.grok.api_key');
        if ($configKey && !empty($configKey) && $configKey !== 'your_openai_api_key_here') {
            Log::info('GrokImageService: Using API key from config/env');
            return $configKey;
        }
        
        Log::error('GrokImageService: No valid API key found in database or config');
        return '';
    }

    /**
     * Get setting from database or config with fallback
     */
    protected function getSettingOrConfig($settingKey, $configKey, $default = null)
    {
        try {
            // Try database first
            $dbValue = \App\Models\Setting::get($settingKey);
            if ($dbValue && !empty($dbValue)) {
                return $dbValue;
            }
        } catch (\Exception $e) {
            // Database not available, fall through to config
        }
        
        // Fall back to config
        return config($configKey, $default);
    }

    /**
     * Make an image publicly accessible and return its URL
     * 
     * @param string $imagePath Path to the image file
     * @return string|null Public URL of the image, or null on failure
     */
    protected function makeImagePubliclyAccessible(string $imagePath): ?string
    {
        try {
            // Check if the file is already in public storage
            if (strpos($imagePath, storage_path('app/public/')) === 0) {
                // File is in public storage, generate URL
                $relativePath = str_replace(storage_path('app/public/'), '', $imagePath);
                return asset('storage/' . $relativePath);
            }
            
            // Check if it's already a URL
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return $imagePath;
            }
            
            // Copy file to public storage temporarily
            $filename = 'temp_vision_' . uniqid() . '_' . basename($imagePath);
            $publicPath = 'image-prompts/temp/' . $filename;
            
            // Ensure directory exists
            Storage::disk('public')->makeDirectory('image-prompts/temp');
            
            // Copy the file
            $fileContents = file_get_contents($imagePath);
            if ($fileContents === false) {
                Log::error('Failed to read image file for public access', ['path' => $imagePath]);
                return null;
            }
            
            Storage::disk('public')->put($publicPath, $fileContents);
            
            // Generate public URL
            $url = asset('storage/' . $publicPath);
            
            Log::info('Image made publicly accessible', [
                'original_path' => $imagePath,
                'public_url' => $url
            ]);
            
            return $url;
            
        } catch (\Exception $e) {
            Log::error('Failed to make image publicly accessible', [
                'error' => $e->getMessage(),
                'path' => $imagePath
            ]);
            return null;
        }
    }

    /**
     * Detect if the prompt is requesting video generation
     *
     * @param string $prompt The user's prompt
     * @return bool True if video generation is requested
     */
    protected function isVideoGenerationRequested(string $prompt): bool
    {
        $videoKeywords = [
            'video', 'animate', 'animation', 'moving', 'motion', 'movement',
            'walk', 'run', 'dance', 'fly', 'swim', 'jump', 'move',
            'cinematic', 'clip', 'footage', 'sequence', 'frames'
        ];

        $lowerPrompt = strtolower($prompt);
        
        foreach ($videoKeywords as $keyword) {
            if (strpos($lowerPrompt, $keyword) !== false) {
                Log::info('Video generation detected in prompt', [
                    'keyword' => $keyword,
                    'prompt' => $prompt
                ]);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Generate an image from a text prompt using Grok Imagine
     *
     * @param string $prompt The text description of the image to generate
     * @param array $options Optional parameters (size, quality, n)
     * @return array ['success' => bool, 'image_url' => string|null, 'image_base64' => string|null, 'error' => string|null]
     */
    public function generateImageFromPrompt(string $prompt, array $options = []): array
    {
        try {
            Log::info('Grok Imagine: Generating image from prompt', [
                'prompt' => $prompt,
                'options' => $options
            ]);

            if (empty($this->apiKey)) {
                throw new \Exception('Grok API key is not configured. Please set it in Admin Settings.');
            }
            
            if ($this->apiKey === 'your_openai_api_key_here') {
                throw new \Exception('Grok API key is still set to placeholder value. Please update it in Admin Settings with your actual API key from https://console.x.ai/');
            }

            $payload = [
                'model' => $this->imagineModel,
                'prompt' => $prompt,
                'quality' => $options['quality'] ?? $this->imagineQuality,
                'n' => $options['n'] ?? 1,
            ];

            Log::info('Grok Imagine: Sending request', [
                'url' => $this->imagineApiUrl,
                'payload' => $payload,
                'api_key_prefix' => substr($this->apiKey, 0, 8) . '...',
                'api_key_length' => strlen($this->apiKey)
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->imagineApiUrl, $payload);

            if (!$response->successful()) {
                // Get error message but truncate if too long
                $errorBody = $response->body();
                $errorMessage = 'API request failed (Status: ' . $response->status() . ')';
                
                // Try to extract just the error message from JSON response
                try {
                    $errorData = json_decode($errorBody, true);
                    if (isset($errorData['error'])) {
                        $errorMessage .= ': ' . (is_string($errorData['error']) ? $errorData['error'] : json_encode($errorData['error']));
                    }
                } catch (\Exception $e) {
                    // If not JSON, just take first 500 chars of body
                    $errorMessage .= ': ' . substr($errorBody, 0, 500);
                }
                
                // Ensure error message is not too long for database
                $errorMessage = substr($errorMessage, 0, 1000);
                
                Log::error('Grok Imagine: Request failed', [
                    'status' => $response->status(),
                    'error_preview' => substr($errorBody, 0, 500)
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'image_url' => null,
                    'image_base64' => null
                ];
            }

            $result = $response->json();
            Log::info('Grok Imagine: Response received', ['result' => $result]);

            // Extract image data from response
            // Grok Imagine follows OpenAI format: data[0].url or data[0].b64_json
            $imageData = $result['data'][0] ?? null;
            
            if (!$imageData) {
                throw new \Exception('No image data in response');
            }

            return [
                'success' => true,
                'image_url' => $imageData['url'] ?? null,
                'image_base64' => $imageData['b64_json'] ?? null,
                'error' => null
            ];

        } catch (\Exception $e) {
            Log::error('Grok Imagine: Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'image_url' => null,
                'image_base64' => null
            ];
        }
    }

    /**
     * Generate a video from an image using Grok Video API
     *
     * @param string $imagePath Path to the source image file
     * @param string $prompt Description of the video to generate
     * @param array $options Optional parameters
     * @return array ['success' => bool, 'video_url' => string|null, 'video_base64' => string|null, 'error' => string|null, 'type' => 'video']
     */
    public function generateVideoFromImage(string $imagePath, string $prompt, array $options = []): array
    {
        try {
            // Increase execution time limit for video generation (can take 5+ minutes)
            set_time_limit(600); // 10 minutes
            
            Log::info('Grok Video: Generating video from image', [
                'image_path' => $imagePath,
                'prompt' => $prompt
            ]);

            if (!file_exists($imagePath)) {
                throw new \Exception('Image file not found: ' . $imagePath);
            }

            if (empty($this->apiKey)) {
                throw new \Exception('Grok API key is not configured. Please set it in Admin Settings.');
            }
            
            if ($this->apiKey === 'your_openai_api_key_here') {
                throw new \Exception('Grok API key is still set to placeholder value. Please update it in Admin Settings with your actual API key from https://console.x.ai/');
            }

            // Read the image file and convert to base64
            $imageData = file_get_contents($imagePath);
            if ($imageData === false) {
                throw new \Exception('Failed to read image file: ' . $imagePath);
            }

            $base64Image = base64_encode($imageData);
            
            // Detect MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $imagePath);
            finfo_close($finfo);

            // Create data URL (RFC 2397 format)
            $dataUrl = "data:{$mimeType};base64,{$base64Image}";

            Log::info('Grok Video: Image prepared for upload', [
                'mime_type' => $mimeType,
                'size_bytes' => strlen($imageData),
                'data_url_length' => strlen($dataUrl)
            ]);

            // Prepare payload - xAI expects image object with url field (can be data URL)
            $payload = [
                'model' => $this->videoModel,
                'prompt' => $prompt,
                'image' => [
                    'url' => $dataUrl
                ],
                'duration' => $options['duration'] ?? 5, // seconds
                'fps' => $options['fps'] ?? 24,
            ];

            Log::info('Grok Video: Sending request with data URL', [
                'url' => $this->videoApiUrl,
                'payload_keys' => array_keys($payload),
                'prompt' => $prompt
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->videoApiUrl, $payload);

            if (!$response->successful()) {
                // Get error message but truncate if too long (avoid storing huge base64 data)
                $errorBody = $response->body();
                $errorMessage = 'Video API request failed (Status: ' . $response->status() . ')';
                
                // Try to extract just the error message from JSON response
                try {
                    $errorData = json_decode($errorBody, true);
                    if (isset($errorData['error'])) {
                        $errorMessage .= ': ' . (is_string($errorData['error']) ? $errorData['error'] : json_encode($errorData['error']));
                    } elseif (isset($errorData['message'])) {
                        $errorMessage .= ': ' . $errorData['message'];
                    }
                } catch (\Exception $e) {
                    // If not JSON, just take first 500 chars of body
                    $errorMessage .= ': ' . substr($errorBody, 0, 500);
                }
                
                // Ensure error message is not too long for database
                $errorMessage = substr($errorMessage, 0, 1000);
                
                Log::error('Grok Video: Request failed', [
                    'status' => $response->status(),
                    'error_body' => substr($errorBody, 0, 1000),
                    'headers' => $response->headers()
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'video_url' => null,
                    'video_base64' => null,
                    'type' => 'video'
                ];
            }

            $result = $response->json();
            Log::info('Grok Video: Response received', ['result' => $result]);

            // Check if video is immediately available (synchronous response)
            if (isset($result['data']) && is_array($result['data']) && count($result['data']) > 0) {
                $videoData = $result['data'][0];
                
                Log::info('Grok Video: Video immediately available', ['video_data' => $videoData]);
                
                return [
                    'success' => true,
                    'video_url' => $videoData['url'] ?? null,
                    'video_base64' => $videoData['b64_json'] ?? null,
                    'error' => null,
                    'type' => 'video'
                ];
            }

            // Check if we got a request_id (async generation)
            if (isset($result['request_id'])) {
                $requestId = $result['request_id'];
                Log::info('Grok Video: Got request_id, will poll for result', ['request_id' => $requestId]);
                
                // Try different possible status endpoints
                $possibleEndpoints = [
                    str_replace('/generations', '/generations/status', $this->videoApiUrl) . '/' . $requestId,
                    $this->videoApiUrl . '/' . $requestId,
                    'https://api.x.ai/v1/videos/status/' . $requestId,
                    'https://api.x.ai/v1/videos/' . $requestId,
                ];
                
                Log::info('Grok Video: Will try multiple status endpoints', [
                    'endpoints' => $possibleEndpoints
                ]);
                
                // Poll for the video result (max 20 attempts = ~2 minutes with 5 second intervals)
                // Reduced attempts since we're trying multiple endpoints
                $maxAttempts = 20;
                $pollInterval = 5; // seconds
                
                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    sleep($pollInterval);
                    
                    // Reset time limit on each iteration to prevent timeout
                    set_time_limit(60);
                    
                    Log::info('Grok Video: Polling attempt', [
                        'attempt' => $attempt,
                        'request_id' => $requestId
                    ]);
                    
                    // Try each possible endpoint until one works
                    $pollSuccess = false;
                    foreach ($possibleEndpoints as $endpointIndex => $statusUrl) {
                        // Poll the status endpoint
                        $pollResponse = Http::timeout(30)
                            ->withHeaders([
                                'Authorization' => 'Bearer ' . $this->apiKey,
                                'Content-Type' => 'application/json',
                            ])
                            ->get($statusUrl);
                    
                    if ($pollResponse->successful()) {
                        $pollResult = $pollResponse->json();
                        Log::info('Grok Video: Poll response', [
                            'attempt' => $attempt,
                            'endpoint_index' => $endpointIndex,
                            'endpoint' => $statusUrl,
                            'status' => $pollResult['status'] ?? 'unknown',
                            'result' => $pollResult
                        ]);
                        
                        $pollSuccess = true;
                        
                        // Check if video is ready
                        // Grok API returns status as 'unknown' but includes video.url when ready
                        if (isset($pollResult['video']['url']) || isset($pollResult['url'])) {
                            // Video URL is present, consider it ready
                            $videoData = $pollResult['video'] ?? $pollResult['data'][0] ?? $pollResult;
                            
                            Log::info('Grok Video: Video ready, extracting URL', [
                                'video_url' => $videoData['url'] ?? null
                            ]);
                            
                            return [
                                'success' => true,
                                'video_url' => $videoData['url'] ?? null,
                                'video_base64' => $videoData['b64_json'] ?? null,
                                'error' => null,
                                'type' => 'video'
                            ];
                        } elseif (isset($pollResult['status'])) {
                            if ($pollResult['status'] === 'completed' || $pollResult['status'] === 'succeeded') {
                                // Video is ready (legacy check)
                                $videoData = $pollResult['data'][0] ?? $pollResult;
                                
                                return [
                                    'success' => true,
                                    'video_url' => $videoData['url'] ?? null,
                                    'video_base64' => $videoData['b64_json'] ?? null,
                                    'error' => null,
                                    'type' => 'video'
                                ];
                            } elseif ($pollResult['status'] === 'failed' || $pollResult['status'] === 'error') {
                                // Generation failed
                                $errorMsg = $pollResult['error'] ?? 'Video generation failed';
                                Log::error('Grok Video: Generation failed', ['error' => $errorMsg]);
                                
                                return [
                                    'success' => false,
                                    'error' => $errorMsg,
                                    'video_url' => null,
                                    'video_base64' => null,
                                    'type' => 'video'
                                ];
                            }
                            // else status is 'pending' or 'processing', continue polling
                        }
                        
                        // Found working endpoint, use it for future attempts
                        $possibleEndpoints = [$statusUrl];
                        break;
                        
                    } else {
                        Log::warning('Grok Video: Poll request failed for endpoint', [
                            'attempt' => $attempt,
                            'endpoint_index' => $endpointIndex,
                            'endpoint' => $statusUrl,
                            'status' => $pollResponse->status(),
                            'body' => substr($pollResponse->body(), 0, 200)
                        ]);
                        // Try next endpoint
                    }
                }
                
                if (!$pollSuccess) {
                    Log::error('Grok Video: All endpoints failed for this attempt', [
                        'attempt' => $attempt
                    ]);
                }
                }
                
                // Timeout - video generation took too long or polling endpoint is incorrect
                Log::error('Grok Video: Polling timeout or incorrect endpoint', [
                    'request_id' => $requestId,
                    'tried_endpoints' => $possibleEndpoints
                ]);
                return [
                    'success' => false,
                    'error' => 'Video generation is taking longer than expected. Request ID: ' . $requestId . '. The xAI Video API may not support status polling, or the endpoint format is incorrect. Please check the xAI API documentation.',
                    'video_url' => null,
                    'video_base64' => null,
                    'type' => 'video'
                ];
            }

            // If no request_id, assume immediate response (fallback to old behavior)
            $videoData = $result['data'][0] ?? null;
            
            if (!$videoData) {
                throw new \Exception('No video data in response');
            }

            return [
                'success' => true,
                'video_url' => $videoData['url'] ?? null,
                'video_base64' => $videoData['b64_json'] ?? null,
                'error' => null,
                'type' => 'video'
            ];

        } catch (\Exception $e) {
            Log::error('Grok Video: Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'video_url' => null,
                'video_base64' => null,
                'type' => 'video'
            ];
        }
    }

    /**
     * Transform an existing image using Grok Vision + Grok Imagine
     * 
     * This is a two-step process:
     * 1. Analyze the uploaded image with Grok Vision to get detailed description
     * 2. Use that description + transformation prompt with Grok Imagine to generate result
     *
     * @param string $imagePath Path to the source image file
     * @param string $prompt Description of how to transform the image
     * @param array $options Optional parameters
     * @return array ['success' => bool, 'image_url' => string|null, 'image_base64' => string|null, 'error' => string|null, 'type' => 'image']
     */
    public function transformImage(string $imagePath, string $prompt, array $options = []): array
    {
        try {
            Log::info('Grok Imagine: Starting image transformation', [
                'image_path' => $imagePath,
                'transformation_prompt' => $prompt
            ]);

            if (!file_exists($imagePath)) {
                throw new \Exception('Image file not found: ' . $imagePath);
            }

            if (empty($this->apiKey)) {
                throw new \Exception('Grok API key is not configured. Please set it in Admin Settings.');
            }
            
            if ($this->apiKey === 'your_openai_api_key_here') {
                throw new \Exception('Grok API key is still set to placeholder value. Please update it in Admin Settings with your actual API key from https://console.x.ai/');
            }

            // Check if video generation is requested in the prompt
            if ($this->isVideoGenerationRequested($prompt)) {
                Log::info('Grok: Video generation detected, switching to video generation');
                $result = $this->generateVideoFromImage($imagePath, $prompt, $options);
                return $result;
            }

            // STEP 1: Analyze the image with Grok Vision to get detailed description
            Log::info('Grok Vision: Analyzing image for transformation');
            
            $analysisPrompt = "Describe this image in extreme detail for AI image generation. Include: "
                . "1. The main subject (person, object, scene) - describe their appearance, pose, clothing, features in detail. "
                . "2. Background and environment. "
                . "3. Lighting, colors, and mood. "
                . "4. Style and composition. "
                . "Be very specific and detailed so another AI can recreate a similar image. "
                . "Focus on visual details that can be used to generate a similar image.";
            
            $analysisResult = $this->analyzeImage($imagePath, $analysisPrompt);
            
            if (!$analysisResult['success']) {
                throw new \Exception('Failed to analyze image: ' . ($analysisResult['error'] ?? 'Unknown error'));
            }
            
            $imageDescription = $analysisResult['analysis'];
            
            Log::info('Grok Vision: Image analysis complete', [
                'description_length' => strlen($imageDescription),
                'description_preview' => substr($imageDescription, 0, 200) . '...'
            ]);

            // STEP 2: Create a combined prompt for Grok Imagine
            // This combines the detailed description with the transformation request
            $fullPrompt = "Create an image based on this description, but with the following changes:\n\n"
                . "ORIGINAL IMAGE DESCRIPTION:\n{$imageDescription}\n\n"
                . "TRANSFORMATION TO APPLY:\n{$prompt}\n\n"
                . "Generate a realistic image that maintains the composition, pose, background, and style of the original, "
                . "but applies the requested transformation. Keep all other details as similar as possible to the original description.";

            Log::info('Grok Imagine: Generating transformed image', [
                'combined_prompt_length' => strlen($fullPrompt)
            ]);

            // STEP 3: Generate the transformed image with Grok Imagine
            $payload = [
                'model' => $this->imagineModel,
                'prompt' => $fullPrompt,
                'quality' => $options['quality'] ?? $this->imagineQuality,
                'n' => $options['n'] ?? 1,
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->imagineApiUrl, $payload);

            if (!$response->successful()) {
                $error = 'API request failed: ' . $response->body();
                Log::error('Grok Imagine: Transformation failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => $error,
                    'image_url' => null,
                    'image_base64' => null,
                    'type' => 'image'
                ];
            }

            $result = $response->json();
            Log::info('Grok Imagine: Transformation response received', ['result' => $result]);

            // Extract image data from response
            $imageData = $result['data'][0] ?? null;
            
            if (!$imageData) {
                throw new \Exception('No image data in transformation response');
            }

            Log::info('Grok Imagine: Transformation successful', [
                'image_url' => $imageData['url'] ?? 'none'
            ]);

            return [
                'success' => true,
                'image_url' => $imageData['url'] ?? null,
                'image_base64' => $imageData['b64_json'] ?? null,
                'error' => null,
                'type' => 'image'
            ];

        } catch (\Exception $e) {
            Log::error('Grok Imagine: Transformation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'image_url' => null,
                'image_base64' => null,
                'type' => 'image'
            ];
        }
    }

    /**
     * Analyze an image using Grok Vision (for analysis only, not generation)
     *
     * @param string $imagePath Path to the image file
     * @param string $prompt Question or instruction about the image
     * @return array ['success' => bool, 'analysis' => string|null, 'error' => string|null]
     */
    public function analyzeImage(string $imagePath, string $prompt): array
    {
        try {
            Log::info('Grok Vision: Analyzing image', [
                'image_path' => $imagePath,
                'prompt' => $prompt
            ]);

            if (!file_exists($imagePath)) {
                throw new \Exception('Image file not found: ' . $imagePath);
            }

            if (empty($this->apiKey)) {
                throw new \Exception('Grok API key is not configured. Please set it in Admin Settings.');
            }
            
            if ($this->apiKey === 'your_openai_api_key_here') {
                throw new \Exception('Grok API key is still set to placeholder value. Please update it in Admin Settings with your actual API key from https://console.x.ai/');
            }

            // xAI's Grok models require images to be accessible via public URL
            // We need to make the image temporarily accessible
            $imageUrl = $this->makeImagePubliclyAccessible($imagePath);
            
            if (!$imageUrl) {
                throw new \Exception('Failed to make image publicly accessible');
            }

            Log::info('Grok Vision: Image made publicly accessible', ['url' => $imageUrl]);

            // Use simple text prompt with URL - Grok will fetch and analyze the image
            $fullPrompt = $prompt . "\n\nImage URL: " . $imageUrl;

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->visionApiUrl, [
                    'model' => $this->visionModel,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $fullPrompt
                        ]
                    ],
                    'max_tokens' => $this->maxTokens,
                    'temperature' => 0.7,
                ]);

            if (!$response->successful()) {
                $error = 'Vision API request failed: ' . $response->body();
                Log::error('Grok Vision: Request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'analysis' => null,
                    'error' => $error
                ];
            }

            $result = $response->json();
            $analysis = $result['choices'][0]['message']['content'] ?? 'No response';

            Log::info('Grok Vision: Analysis complete', ['analysis_length' => strlen($analysis)]);

            return [
                'success' => true,
                'analysis' => $analysis,
                'error' => null
            ];

        } catch (\Exception $e) {
            Log::error('Grok Vision: Analysis exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'analysis' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use transformImage() instead
     */
    public function processImage($imagePath, $prompt)
    {
        return $this->transformImage($imagePath, $prompt);
    }

    /**
     * Process video - now properly implemented
     * This method is kept for backward compatibility but redirects to transformImage
     * which will detect if video generation is needed based on the prompt
     */
    public function processVideo($videoPath, $prompt)
    {
        // For now, video files are not supported as input
        // But we can generate videos from images if the prompt requests it
        return [
            'success' => false,
            'error' => 'Video files as input are not yet supported. Please upload an image and use a prompt requesting video generation (e.g., "animate this", "create a video", etc.)',
            'image_url' => null,
            'image_base64' => null,
            'type' => 'error'
        ];
    }
}
