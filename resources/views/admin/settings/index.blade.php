@extends('admin.layout')

@section('title', 'Site Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-cog mr-2"></i>Site Settings
        </h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

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

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Vision Model
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="grok_vision_model" 
                        value="{{ old('grok_vision_model', $settings->get('grok_vision_model')->value ?? 'grok-vision-beta') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono"
                        placeholder="grok-vision-beta">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 mt-2">
                        <p class="text-xs text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>CRITICAL:</strong> Use <code class="bg-yellow-100 px-1 rounded font-semibold">grok-vision-beta</code> for image analysis. 
                            Regular models like <code class="bg-yellow-100 px-1 rounded">grok-3</code> or <code class="bg-yellow-100 px-1 rounded">grok-beta</code> 
                            <strong>DO NOT support images</strong> and will cause errors.
                        </p>
                    </div>
                </div>

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

</script>
@endsection
