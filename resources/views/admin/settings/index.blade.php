@extends('admin.layout')

@section('title', 'Site Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-cog mr-2"></i>Site Settings
        </h1>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Tabs Navigation -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button type="button" onclick="showTab('general')" id="tab-general" 
                    class="tab-button border-b-2 border-blue-500 py-4 px-1 text-center text-sm font-medium text-blue-600">
                    <i class="fas fa-info-circle mr-2"></i>General
                </button>
                <button type="button" onclick="showTab('appearance')" id="tab-appearance"
                    class="tab-button border-b-2 border-transparent py-4 px-1 text-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-palette mr-2"></i>Appearance
                </button>
                <button type="button" onclick="showTab('payment')" id="tab-payment"
                    class="tab-button border-b-2 border-transparent py-4 px-1 text-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-credit-card mr-2"></i>Payment Settings
                </button>
                <button type="button" onclick="showTab('api')" id="tab-api"
                    class="tab-button border-b-2 border-transparent py-4 px-1 text-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-key mr-2"></i>API Settings
                </button>
                <button type="button" onclick="showTab('referral')" id="tab-referral"
                    class="tab-button border-b-2 border-transparent py-4 px-1 text-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-users mr-2"></i>Referral System
                </button>
                <button type="button" onclick="showTab('authentication')" id="tab-authentication"
                    class="tab-button border-b-2 border-transparent py-4 px-1 text-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-shield-alt mr-2"></i>Authentication
                </button>
                <button type="button" onclick="showTab('footer')" id="tab-footer"
                    class="tab-button border-b-2 border-transparent py-4 px-1 text-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-shoe-prints mr-2"></i>Footer
                </button>
            </nav>
        </div>

        <!-- General Settings Tab -->
        <div id="content-general" class="tab-content">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-building mr-2"></i>Site Information
                </h2>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Site Title
                    </label>
                    <input type="text" name="site_title" 
                        value="{{ old('site_title', $settings->get('site_title')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="My Awesome Site">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Site Description
                    </label>
                    <textarea name="site_description" rows="3"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="A brief description of your site">{{ old('site_description', $settings->get('site_description')->value ?? '') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Footer Text
                    </label>
                    <input type="text" name="footer_text" 
                        value="{{ old('footer_text', $settings->get('footer_text')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Powered by AI">
                    <p class="text-gray-500 text-xs mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        This text will appear in the footer after the copyright notice
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Contact Email
                    </label>
                    <input type="email" name="contact_email" 
                        value="{{ old('contact_email', $settings->get('contact_email')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="contact@example.com">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Contact Phone
                    </label>
                    <input type="text" name="contact_phone" 
                        value="{{ old('contact_phone', $settings->get('contact_phone')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="+1 234 567 8900">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Address
                    </label>
                    <textarea name="address" rows="3"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="123 Main Street, City, Country">{{ old('address', $settings->get('address')->value ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Appearance Tab -->
        <div id="content-appearance" class="tab-content hidden">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-image mr-2"></i>Logo & Branding
                </h2>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Site Logo
                    </label>
                    @if($settings->has('site_logo') && $settings->get('site_logo')->value)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $settings->get('site_logo')->value) }}" 
                                alt="Site Logo" class="h-20 border rounded p-2">
                            <button type="button" onclick="deleteFile('site_logo')" 
                                class="mt-2 text-red-500 hover:text-red-700 text-sm">
                                <i class="fas fa-trash mr-1"></i>Remove Logo
                            </button>
                        </div>
                    @endif
                    <input type="file" name="site_logo" accept="image/*"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-gray-500 text-xs mt-1">Recommended size: 200x60px. Max 2MB. (JPG, PNG, GIF, SVG)</p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Site Favicon
                    </label>
                    @if($settings->has('site_favicon') && $settings->get('site_favicon')->value)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $settings->get('site_favicon')->value) }}" 
                                alt="Favicon" class="h-8 border rounded p-1">
                            <button type="button" onclick="deleteFile('site_favicon')" 
                                class="mt-2 text-red-500 hover:text-red-700 text-sm">
                                <i class="fas fa-trash mr-1"></i>Remove Favicon
                            </button>
                        </div>
                    @endif
                    <input type="file" name="site_favicon" accept=".ico,.png"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-gray-500 text-xs mt-1">Recommended size: 32x32px or 16x16px. (ICO, PNG)</p>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-code mr-2"></i>Custom Code
                </h2>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Header Code
                    </label>
                    <textarea name="header_code" rows="5"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 font-mono text-sm leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="<!-- Add custom code to <head> section -->">{{ old('header_code', $settings->get('header_code')->value ?? '') }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">This code will be inserted in the &lt;head&gt; section of your site. Use for analytics, meta tags, etc.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Footer Code
                    </label>
                    <textarea name="footer_code" rows="5"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 font-mono text-sm leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="<!-- Add custom code before </body> tag -->">{{ old('footer_code', $settings->get('footer_code')->value ?? '') }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">This code will be inserted before the closing &lt;/body&gt; tag. Use for scripts, tracking codes, etc.</p>
                </div>
            </div>
        </div>

        <!-- Payment Settings Tab -->
        <div id="content-payment" class="tab-content hidden">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fab fa-cc-stripe mr-2"></i>Razorpay Configuration
                </h2>

                <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Get your Razorpay API credentials from your 
                                <a href="https://dashboard.razorpay.com/app/keys" target="_blank" class="font-semibold underline">
                                    Razorpay Dashboard
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="razorpay_enabled" value="1" 
                            {{ old('razorpay_enabled', $settings->get('razorpay_enabled')->value ?? '0') == '1' ? 'checked' : '' }}
                            class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700 font-bold">Enable Razorpay Payments</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Razorpay Key ID
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="razorpay_key" 
                        value="{{ old('razorpay_key', $settings->get('razorpay_key')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="rzp_test_xxxxxxxxxxxxx">
                    <p class="text-gray-500 text-xs mt-1">Your Razorpay Key ID (starts with rzp_test_ or rzp_live_)</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Razorpay Key Secret
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="razorpay_secret" id="razorpay_secret"
                            value="{{ old('razorpay_secret', $settings->get('razorpay_secret')->value ?? '') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono pr-10"
                            placeholder="••••••••••••••••••••">
                        <button type="button" onclick="togglePassword('razorpay_secret')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                            <i class="fas fa-eye" id="razorpay_secret-icon"></i>
                        </button>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">Your Razorpay Key Secret (keep this confidential)</p>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mt-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Important:</strong> Keep your Razorpay Key Secret secure. Never share it publicly or commit it to version control.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-question-circle mr-2"></i>Testing Razorpay
                </h2>
                
                <div class="prose max-w-none">
                    <p class="text-gray-700 mb-3">Use these test credentials for testing payments:</p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2">
                        <li><strong>Test Card:</strong> 4111 1111 1111 1111</li>
                        <li><strong>CVV:</strong> Any 3 digits</li>
                        <li><strong>Expiry:</strong> Any future date</li>
                        <li><strong>UPI:</strong> success@razorpay</li>
                        <li><strong>Netbanking:</strong> Select any bank and use "success" as the password</li>
                    </ul>
                    <p class="text-gray-600 text-sm mt-3">
                        <i class="fas fa-book mr-1"></i>
                        <a href="https://razorpay.com/docs/payments/payments/test-card-details/" target="_blank" class="text-blue-600 hover:underline">
                            View full test credentials documentation
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- API Settings Tab -->
        <div id="content-api" class="tab-content hidden">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-robot mr-2"></i>Grok AI API Configuration
                </h2>

                <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Get your Grok API key from 
                                <a href="https://console.x.ai/" target="_blank" class="font-semibold underline">
                                    xAI Console
                                </a>. 
                                Grok is used for intelligent image analysis, transformation, and generation.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Grok API Key
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="grok_api_key" id="grok_api_key"
                            value="{{ old('grok_api_key', $settings->get('grok_api_key')->value ?? '') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono pr-10"
                            placeholder="xai-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        <button type="button" onclick="togglePassword('grok_api_key')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                            <i class="fas fa-eye" id="grok_api_key-icon"></i>
                        </button>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">Your Grok API key (starts with xai-)</p>
                    
                    <!-- Test API Key Button -->
                    <div class="mt-2">
                        <button type="button" onclick="testApiKey()" id="test-api-key-btn"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm inline-flex items-center">
                            <i class="fas fa-vial mr-2"></i>
                            Test API Key
                        </button>
                        <div id="api-test-result" class="mt-2 hidden"></div>
                    </div>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mt-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Important:</strong> Keep your Grok API key secure. Never share it publicly or commit it to version control. This key is required for all image processing features.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-cog mr-2"></i>Advanced API Settings
                </h2>

                <!-- Vision API Settings -->
                <h3 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">
                    <i class="fas fa-eye mr-2"></i>Vision API (Image Analysis)
                </h3>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Vision API URL
                    </label>
                    <input type="text" name="grok_vision_api_url" 
                        value="{{ old('grok_vision_api_url', $settings->get('grok_vision_api_url')->value ?? 'https://api.x.ai/v1/chat/completions') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="https://api.x.ai/v1/chat/completions">
                    <p class="text-gray-500 text-xs mt-1">Grok Vision API endpoint for image analysis</p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Vision Model
                    </label>
                    <input type="text" name="grok_vision_model" 
                        value="{{ old('grok_vision_model', $settings->get('grok_vision_model')->value ?? 'grok-vision-beta') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="grok-vision-beta">
                    <p class="text-gray-500 text-xs mt-1">Model name for vision/analysis (e.g., grok-vision-beta, grok-beta, grok-2-latest)</p>
                </div>

                <!-- Imagine API Settings -->
                <h3 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2 mt-6">
                    <i class="fas fa-image mr-2"></i>Imagine API (Image Generation)
                </h3>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Imagine API URL
                    </label>
                    <input type="text" name="grok_imagine_api_url" 
                        value="{{ old('grok_imagine_api_url', $settings->get('grok_imagine_api_url')->value ?? 'https://api.x.ai/v1/images/generations') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="https://api.x.ai/v1/images/generations">
                    <p class="text-gray-500 text-xs mt-1">Grok Imagine API endpoint for image generation</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Imagine Model
                    </label>
                    <input type="text" name="grok_imagine_model" 
                        value="{{ old('grok_imagine_model', $settings->get('grok_imagine_model')->value ?? 'grok-2-latest') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="grok-2-latest">
                    <p class="text-gray-500 text-xs mt-1">Model name for image generation (e.g., grok-2-latest, grok-beta)</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Image Size
                        </label>
                        <input type="text" name="grok_imagine_size" 
                            value="{{ old('grok_imagine_size', $settings->get('grok_imagine_size')->value ?? '1024x1024') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="1024x1024">
                        <p class="text-gray-500 text-xs mt-1">Default image size (e.g., 1024x1024, 512x512)</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Image Quality
                        </label>
                        <select name="grok_imagine_quality" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="high" {{ old('grok_imagine_quality', $settings->get('grok_imagine_quality')->value ?? 'high') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="standard" {{ old('grok_imagine_quality', $settings->get('grok_imagine_quality')->value ?? 'high') == 'standard' ? 'selected' : '' }}>Standard</option>
                        </select>
                        <p class="text-gray-500 text-xs mt-1">Image generation quality</p>
                    </div>
                </div>

                <!-- Video API Settings -->
                <h3 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2 mt-6">
                    <i class="fas fa-video mr-2"></i>Video API (Video Generation)
                </h3>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Video API URL
                    </label>
                    <input type="text" name="grok_video_api_url" 
                        value="{{ old('grok_video_api_url', $settings->get('grok_video_api_url')->value ?? 'https://api.x.ai/v1/videos/generations') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="https://api.x.ai/v1/videos/generations">
                    <p class="text-gray-500 text-xs mt-1">Grok Video API endpoint for video generation</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Video Model
                    </label>
                    <input type="text" name="grok_video_model" 
                        value="{{ old('grok_video_model', $settings->get('grok_video_model')->value ?? 'grok-2-latest') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="grok-2-latest">
                    <p class="text-gray-500 text-xs mt-1">Model name for video generation (e.g., grok-2-latest, grok-beta)</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Video Duration (seconds)
                        </label>
                        <input type="number" name="grok_video_duration" 
                            value="{{ old('grok_video_duration', $settings->get('grok_video_duration')->value ?? '5') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="5" min="1" max="60">
                        <p class="text-gray-500 text-xs mt-1">Default video duration (1-60 seconds)</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Video FPS
                        </label>
                        <input type="number" name="grok_video_fps" 
                            value="{{ old('grok_video_fps', $settings->get('grok_video_fps')->value ?? '24') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="24" min="1" max="60">
                        <p class="text-gray-500 text-xs mt-1">Frames per second (1-60)</p>
                    </div>
                </div>

                <!-- General API Settings -->
                <h3 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2 mt-6">
                    <i class="fas fa-sliders-h mr-2"></i>General API Settings
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Max Tokens
                        </label>
                        <input type="number" name="grok_max_tokens" 
                            value="{{ old('grok_max_tokens', $settings->get('grok_max_tokens')->value ?? '2000') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="2000" min="100" max="10000">
                        <p class="text-gray-500 text-xs mt-1">Maximum tokens for API responses (100-10000)</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            API Timeout (seconds)
                        </label>
                        <input type="number" name="grok_timeout" 
                            value="{{ old('grok_timeout', $settings->get('grok_timeout')->value ?? '180') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="180" min="30" max="600">
                        <p class="text-gray-500 text-xs mt-1">Maximum time to wait for API responses (30-600 seconds)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referral System Tab -->
        <div id="content-referral" class="tab-content hidden">
            <!-- Signup Bonus Section -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-gift mr-2"></i>Signup Bonus Configuration
                        </h2>
                        <p class="text-gray-600 text-sm mt-1">
                            Award coins to ALL new users when they register (works for mobile app only)
                        </p>
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <!-- Hidden input to ensure a value is always sent -->
                                <input type="hidden" name="signup_bonus_enabled" value="0">
                                <input type="checkbox" name="signup_bonus_enabled" value="1"
                                    {{ old('signup_bonus_enabled', $settings->get('signup_bonus_enabled')->value ?? '0') == '1' ? 'checked' : '' }}
                                    class="sr-only" id="signup-bonus-toggle">
                                <div class="block bg-gray-300 w-14 h-8 rounded-full"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                            </div>
                            <div class="ml-3 text-gray-700 font-medium">
                                <span id="signup-bonus-status-text">
                                    {{ old('signup_bonus_enabled', $settings->get('signup_bonus_enabled')->value ?? '0') == '1' ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-500 text-white rounded-full p-3 mr-4">
                            <i class="fas fa-star text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Welcome Bonus</h3>
                            <p class="text-sm text-gray-600">Coins for every new user registration</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Signup Bonus Coins
                        </label>
                        <div class="relative">
                            <input type="number" name="signup_bonus_coins" 
                                value="{{ old('signup_bonus_coins', $settings->get('signup_bonus_coins')->value ?? '0') }}"
                                class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 text-lg font-semibold leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500"
                                placeholder="0" min="0" max="10000" id="signup-bonus-coins">
                            <div class="absolute right-3 top-3 text-gray-400">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 text-xs mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Given to ALL new users upon registration (0-10,000 coins)
                        </p>
                        <div class="mt-3 p-3 bg-white rounded border border-purple-200">
                            <p class="text-sm text-gray-700">
                                <strong>Example:</strong> If set to <span id="signup-bonus-preview" class="font-bold text-purple-600">{{ old('signup_bonus_coins', $settings->get('signup_bonus_coins')->value ?? '0') }}</span> coins, 
                                every new user will receive <span id="signup-bonus-preview-2" class="font-bold text-purple-600">{{ old('signup_bonus_coins', $settings->get('signup_bonus_coins')->value ?? '0') }}</span> coins automatically on signup.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-mobile-alt text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Mobile App Only</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>This signup bonus is automatically awarded to new users registering through your mobile application. This is separate from the referral bonus below.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referral System Section -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-gift mr-2"></i>Referral Rewards Configuration
                        </h2>
                        <p class="text-gray-600 text-sm mt-1">
                            Configure how many coins users earn through referrals
                        </p>
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <!-- Hidden input to ensure a value is always sent -->
                                <input type="hidden" name="referral_system_enabled" value="0">
                                <input type="checkbox" name="referral_system_enabled" value="1"
                                    {{ old('referral_system_enabled', $settings->get('referral_system_enabled')->value ?? '1') == '1' ? 'checked' : '' }}
                                    class="sr-only" id="referral-toggle">
                                <div class="block bg-gray-300 w-14 h-8 rounded-full"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                            </div>
                            <div class="ml-3 text-gray-700 font-medium">
                                <span id="referral-status-text">
                                    {{ old('referral_system_enabled', $settings->get('referral_system_enabled')->value ?? '1') == '1' ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Referrer Coins -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-500 text-white rounded-full p-3 mr-4">
                                <i class="fas fa-coins text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Referrer Reward</h3>
                                <p class="text-sm text-gray-600">Coins for the person who refers</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Coins Per Successful Referral
                            </label>
                            <div class="relative">
                                <input type="number" name="referral_coins_per_referral" 
                                    value="{{ old('referral_coins_per_referral', $settings->get('referral_coins_per_referral')->value ?? '100') }}"
                                    class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 text-lg font-semibold leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"
                                    placeholder="100" min="0" max="10000" id="referrer-coins">
                                <div class="absolute right-3 top-3 text-gray-400">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 text-xs mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Awarded when referred user subscribes (0-10,000 coins)
                            </p>
                            <div class="mt-3 p-3 bg-white rounded border border-blue-200">
                                <p class="text-sm text-gray-700">
                                    <strong>Example:</strong> If set to <span id="referrer-preview" class="font-bold text-blue-600">100</span> coins, 
                                    each successful referral earns the referrer <span id="referrer-preview-2" class="font-bold text-blue-600">100</span> coins.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- New User Bonus -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-green-500 text-white rounded-full p-3 mr-4">
                                <i class="fas fa-user-plus text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">New User Bonus</h3>
                                <p class="text-sm text-gray-600">Welcome bonus for new users</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Bonus Coins for New Users
                            </label>
                            <div class="relative">
                                <input type="number" name="referral_bonus_for_new_user" 
                                    value="{{ old('referral_bonus_for_new_user', $settings->get('referral_bonus_for_new_user')->value ?? '50') }}"
                                    class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 text-lg font-semibold leading-tight focus:outline-none focus:shadow-outline focus:border-green-500"
                                    placeholder="50" min="0" max="10000" id="new-user-coins">
                                <div class="absolute right-3 top-3 text-gray-400">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 text-xs mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Bonus given to users who register with a referral code (0-10,000 coins)
                            </p>
                            <div class="mt-3 p-3 bg-white rounded border border-green-200">
                                <p class="text-sm text-gray-700">
                                    <strong>Example:</strong> If set to <span id="new-user-preview" class="font-bold text-green-600">50</span> coins, 
                                    new users get <span id="new-user-preview-2" class="font-bold text-green-600">50</span> bonus coins on signup.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Summary -->
                <div class="mt-6 bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-chart-line mr-2"></i>Referral System Overview
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-sm text-gray-600 mb-1">Total Cost Per Referral</div>
                            <div class="text-2xl font-bold text-blue-600" id="total-cost">
                                {{ (old('referral_coins_per_referral', $settings->get('referral_coins_per_referral')->value ?? '100') + old('referral_bonus_for_new_user', $settings->get('referral_bonus_for_new_user')->value ?? '50')) }} coins
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Referrer + New User</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-sm text-gray-600 mb-1">System Status</div>
                            <div class="text-2xl font-bold" id="system-status-display">
                                <span class="{{ old('referral_system_enabled', $settings->get('referral_system_enabled')->value ?? '1') == '1' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ old('referral_system_enabled', $settings->get('referral_system_enabled')->value ?? '1') == '1' ? '● Active' : '● Inactive' }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Current State</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="text-sm text-gray-600 mb-1">Quick Actions</div>
                            <div class="flex gap-2 mt-2">
                                <button type="button" onclick="setPreset('standard')" 
                                    class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                    Standard (100/50)
                                </button>
                                <button type="button" onclick="setPreset('aggressive')" 
                                    class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                    Aggressive (300/150)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Tips for Setting Referral Rewards</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Standard:</strong> 100 coins for referrer, 50 for new user - Balanced approach</li>
                                    <li><strong>Aggressive Growth:</strong> 300/150 - High incentive for viral growth campaigns</li>
                                    <li><strong>Conservative:</strong> 50/25 - Lower cost per acquisition</li>
                                    <li><strong>Premium:</strong> 500/200 - Target high-value customers</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication Tab -->
        <div id="content-authentication" class="tab-content hidden">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fab fa-google mr-2"></i>Google Login Configuration
                </h2>

                <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Configure Google Sign-In for your mobile application. Get your credentials from 
                                <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="font-semibold underline">
                                    Google Cloud Console
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="flex items-center mb-4">
                        <input type="hidden" name="google_login_enabled" value="0">
                        <input type="checkbox" name="google_login_enabled" value="1" 
                            {{ old('google_login_enabled', $settings->get('google_login_enabled')->value ?? '0') == '1' ? 'checked' : '' }}
                            class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700 font-bold">Enable Google Login</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Google Client ID
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="google_client_id" 
                        value="{{ old('google_client_id', $settings->get('google_client_id')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="123456789012-abcdefghijklmnopqrstuvwxyz123456.apps.googleusercontent.com">
                    <p class="text-gray-500 text-xs mt-1">Your Google OAuth 2.0 Client ID (ends with .apps.googleusercontent.com)</p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">
                        <i class="fas fa-book mr-2"></i>Setup Instructions
                    </h3>
                    <ol class="list-decimal list-inside text-sm text-gray-700 space-y-2">
                        <li>Go to <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a></li>
                        <li>Create a new project or select an existing one</li>
                        <li>Enable the Google+ API</li>
                        <li>Go to "Credentials" and create OAuth 2.0 Client ID</li>
                        <li>Select "Android" or "iOS" as application type</li>
                        <li>Add your app's package name and SHA-1 certificate fingerprint</li>
                        <li>Copy the Client ID and paste it above</li>
                        <li>For Flutter, also create a "Web" OAuth client for the web flow</li>
                    </ol>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mt-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Important:</strong> Make sure to configure authorized redirect URIs in Google Cloud Console. 
                                For mobile apps, you typically need both Android and iOS client IDs.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-mobile-alt mr-2"></i>Flutter Integration
                </h2>
                
                <div class="prose max-w-none">
                    <p class="text-gray-700 mb-3">To integrate Google Sign-In in your Flutter app:</p>
                    
                    <div class="bg-gray-800 text-gray-100 p-4 rounded-lg overflow-x-auto mb-4">
                        <pre class="text-sm"><code>// 1. Add dependency to pubspec.yaml
dependencies:
  google_sign_in: ^6.1.5
  http: ^1.1.0

// 2. Implement Google Sign-In
final GoogleSignInAccount? googleUser = await GoogleSignIn().signIn();
final GoogleSignInAuthentication googleAuth = await googleUser!.authentication;

// 3. Send ID token to your backend
final response = await http.post(
  Uri.parse('YOUR_BACKEND_URL/api/google-login'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'id_token': googleAuth.idToken,
    'referral_code': 'OPTIONAL123', // Optional
  }),
);

// 4. Get bearer token from response
final data = jsonDecode(response.body);
final bearerToken = data['data']['token'];</code></pre>
                    </div>

                    <p class="text-gray-600 text-sm mt-3">
                        <i class="fas fa-book mr-1"></i>
                        <a href="https://pub.dev/packages/google_sign_in" target="_blank" class="text-blue-600 hover:underline">
                            View google_sign_in package documentation
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Tab -->
        <div id="content-footer" class="tab-content hidden">
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-info-circle mr-2"></i>Footer Information
                </h2>

                <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Customize your website footer with company information, contact details, and social media links.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Footer Description
                    </label>
                    <textarea name="footer_description" rows="3"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Transform your media with AI-powered effects">{{ old('footer_description', $settings->get('footer_description')->value ?? '') }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Brief description that appears in the footer brand section
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-address-book mr-2"></i>Contact Information
                </h2>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-envelope mr-1"></i>Footer Email
                    </label>
                    <input type="email" name="footer_email" 
                        value="{{ old('footer_email', $settings->get('footer_email')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="support@example.com">
                    <p class="text-gray-500 text-xs mt-1">Email address displayed in footer</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-phone mr-1"></i>Footer Phone
                    </label>
                    <input type="text" name="footer_phone" 
                        value="{{ old('footer_phone', $settings->get('footer_phone')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="+1 (555) 123-4567">
                    <p class="text-gray-500 text-xs mt-1">Phone number displayed in footer</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-map-marker-alt mr-1"></i>Footer Address
                    </label>
                    <textarea name="footer_address" rows="2"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="123 AI Street, Tech City, TC 12345">{{ old('footer_address', $settings->get('footer_address')->value ?? '') }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">Physical address displayed in footer</p>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-share-alt mr-2"></i>Social Media Links
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fab fa-facebook-f mr-1 text-blue-600"></i>Facebook URL
                        </label>
                        <input type="url" name="footer_facebook_url" 
                            value="{{ old('footer_facebook_url', $settings->get('footer_facebook_url')->value ?? '') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="https://facebook.com/yourpage">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to hide Facebook icon</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fab fa-twitter mr-1 text-blue-400"></i>Twitter/X URL
                        </label>
                        <input type="url" name="footer_twitter_url" 
                            value="{{ old('footer_twitter_url', $settings->get('footer_twitter_url')->value ?? '') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="https://twitter.com/yourhandle">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to hide Twitter icon</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fab fa-instagram mr-1 text-cyan-600"></i>Instagram URL
                        </label>
                        <input type="url" name="footer_instagram_url" 
                            value="{{ old('footer_instagram_url', $settings->get('footer_instagram_url')->value ?? '') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="https://instagram.com/yourprofile">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to hide Instagram icon</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fab fa-youtube mr-1 text-red-600"></i>YouTube URL
                        </label>
                        <input type="url" name="footer_youtube_url" 
                            value="{{ old('footer_youtube_url', $settings->get('footer_youtube_url')->value ?? '') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="https://youtube.com/@yourchannel">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to hide YouTube icon</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fab fa-linkedin-in mr-1 text-blue-700"></i>LinkedIn URL
                        </label>
                        <input type="url" name="footer_linkedin_url" 
                            value="{{ old('footer_linkedin_url', $settings->get('footer_linkedin_url')->value ?? '') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="https://linkedin.com/company/yourcompany">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to hide LinkedIn icon</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            <i class="fab fa-tiktok mr-1 text-gray-800"></i>TikTok URL
                        </label>
                        <input type="url" name="footer_tiktok_url" 
                            value="{{ old('footer_tiktok_url', $settings->get('footer_tiktok_url')->value ?? '') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="https://tiktok.com/@youraccount">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to hide TikTok icon</p>
                    </div>
                </div>

                <div class="mt-4 bg-green-50 border-l-4 border-green-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                <strong>Tip:</strong> Only social media icons with URLs will be displayed in the footer. Empty fields will be automatically hidden.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-link mr-2"></i>Footer Links
                </h2>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Privacy Policy URL
                    </label>
                    <input type="url" name="footer_privacy_url" 
                        value="{{ old('footer_privacy_url', $settings->get('footer_privacy_url')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="https://yoursite.com/privacy-policy">
                    <p class="text-gray-500 text-xs mt-1">Link to your privacy policy page</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Terms of Service URL
                    </label>
                    <input type="url" name="footer_terms_url" 
                        value="{{ old('footer_terms_url', $settings->get('footer_terms_url')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="https://yoursite.com/terms-of-service">
                    <p class="text-gray-500 text-xs mt-1">Link to your terms of service page</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Contact Page URL
                    </label>
                    <input type="url" name="footer_contact_url" 
                        value="{{ old('footer_contact_url', $settings->get('footer_contact_url')->value ?? '') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="https://yoursite.com/contact">
                    <p class="text-gray-500 text-xs mt-1">Link to your contact page</p>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit" 
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded focus:outline-none focus:shadow-outline">
                <i class="fas fa-save mr-2"></i>Save Settings
            </button>
        </div>
    </form>
</div>

<script>
// Tab switching
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Delete file (logo/favicon)
function deleteFile(key) {
    if (!confirm('Are you sure you want to delete this file?')) {
        return;
    }
    
    fetch('{{ route("admin.settings.delete-file") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ key: key })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete file');
    });
}

// Test API Key
function testApiKey() {
    const apiKey = document.getElementById('grok_api_key').value;
    const btn = document.getElementById('test-api-key-btn');
    const resultDiv = document.getElementById('api-test-result');
    
    if (!apiKey) {
        resultDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><i class="fas fa-times-circle mr-2"></i>Please enter an API key first</div>';
        resultDiv.classList.remove('hidden');
        return;
    }
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
    resultDiv.innerHTML = '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded"><i class="fas fa-spinner fa-spin mr-2"></i>Testing API key connection...</div>';
    resultDiv.classList.remove('hidden');
    
    fetch('{{ route("admin.settings.test-api-key") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ api_key: apiKey })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"><i class="fas fa-check-circle mr-2"></i>' + data.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><i class="fas fa-times-circle mr-2"></i>' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><i class="fas fa-times-circle mr-2"></i>Error testing API key: ' + error.message + '</div>';
    })
    .finally(() => {
        // Reset button state
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-vial mr-2"></i>Test API Key';
    });
}

// Referral System Toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('referral-toggle');
    const statusText = document.getElementById('referral-status-text');
    const systemStatus = document.getElementById('system-status-display');
    
    if (toggle) {
        toggle.addEventListener('change', function() {
            const isEnabled = this.checked;
            statusText.textContent = isEnabled ? 'Enabled' : 'Disabled';
            
            if (systemStatus) {
                systemStatus.innerHTML = isEnabled 
                    ? '<span class="text-green-600">● Active</span>' 
                    : '<span class="text-red-600">● Inactive</span>';
            }
            
            // Toggle styling
            const toggleBg = this.parentElement.querySelector('.block');
            const toggleDot = this.parentElement.querySelector('.dot');
            
            if (isEnabled) {
                toggleBg.classList.remove('bg-gray-300');
                toggleBg.classList.add('bg-green-500');
                toggleDot.style.transform = 'translateX(1.5rem)';
            } else {
                toggleBg.classList.remove('bg-green-500');
                toggleBg.classList.add('bg-gray-300');
                toggleDot.style.transform = 'translateX(0)';
            }
        });
        
        // Set initial state
        if (toggle.checked) {
            const toggleBg = toggle.parentElement.querySelector('.block');
            const toggleDot = toggle.parentElement.querySelector('.dot');
            toggleBg.classList.remove('bg-gray-300');
            toggleBg.classList.add('bg-green-500');
            toggleDot.style.transform = 'translateX(1.5rem)';
        }
    }
    
    // Signup Bonus Toggle
    const signupToggle = document.getElementById('signup-bonus-toggle');
    const signupStatusText = document.getElementById('signup-bonus-status-text');
    
    if (signupToggle) {
        signupToggle.addEventListener('change', function() {
            const isEnabled = this.checked;
            signupStatusText.textContent = isEnabled ? 'Enabled' : 'Disabled';
            
            // Toggle styling
            const toggleBg = this.parentElement.querySelector('.block');
            const toggleDot = this.parentElement.querySelector('.dot');
            
            if (isEnabled) {
                toggleBg.classList.remove('bg-gray-300');
                toggleBg.classList.add('bg-purple-500');
                toggleDot.style.transform = 'translateX(1.5rem)';
            } else {
                toggleBg.classList.remove('bg-purple-500');
                toggleBg.classList.add('bg-gray-300');
                toggleDot.style.transform = 'translateX(0)';
            }
        });
        
        // Set initial state
        if (signupToggle.checked) {
            const toggleBg = signupToggle.parentElement.querySelector('.block');
            const toggleDot = signupToggle.parentElement.querySelector('.dot');
            toggleBg.classList.remove('bg-gray-300');
            toggleBg.classList.add('bg-purple-500');
            toggleDot.style.transform = 'translateX(1.5rem)';
        }
    }
    
    // Update preview values
    const referrerInput = document.getElementById('referrer-coins');
    const newUserInput = document.getElementById('new-user-coins');
    const signupBonusInput = document.getElementById('signup-bonus-coins');
    
    if (referrerInput) {
        referrerInput.addEventListener('input', updateReferralPreview);
    }
    if (newUserInput) {
        newUserInput.addEventListener('input', updateReferralPreview);
    }
    if (signupBonusInput) {
        signupBonusInput.addEventListener('input', updateSignupBonusPreview);
    }
    
    // Initial preview update
    updateReferralPreview();
    updateSignupBonusPreview();
});

function updateSignupBonusPreview() {
    const signupBonus = parseInt(document.getElementById('signup-bonus-coins')?.value || 0);
    
    // Update preview texts
    const signupBonusPreviews = document.querySelectorAll('#signup-bonus-preview, #signup-bonus-preview-2');
    signupBonusPreviews.forEach(el => el.textContent = signupBonus);
}

function updateReferralPreview() {
    const referrerCoins = parseInt(document.getElementById('referrer-coins')?.value || 0);
    const newUserCoins = parseInt(document.getElementById('new-user-coins')?.value || 0);
    
    // Update preview texts
    const referrerPreviews = document.querySelectorAll('#referrer-preview, #referrer-preview-2');
    referrerPreviews.forEach(el => el.textContent = referrerCoins);
    
    const newUserPreviews = document.querySelectorAll('#new-user-preview, #new-user-preview-2');
    newUserPreviews.forEach(el => el.textContent = newUserCoins);
    
    // Update total cost
    const totalCost = document.getElementById('total-cost');
    if (totalCost) {
        totalCost.textContent = (referrerCoins + newUserCoins) + ' coins';
    }
}

function setPreset(type) {
    const referrerInput = document.getElementById('referrer-coins');
    const newUserInput = document.getElementById('new-user-coins');
    
    if (type === 'standard') {
        referrerInput.value = 100;
        newUserInput.value = 50;
    } else if (type === 'aggressive') {
        referrerInput.value = 300;
        newUserInput.value = 150;
    }
    
    updateReferralPreview();
}


</script>
@endsection
