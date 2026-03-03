<!-- Professional Footer -->
<footer class="bg-gradient-to-r from-slate-900 via-blue-900 to-slate-900 text-white mt-16">
    <div class="container mx-auto px-4 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <!-- Brand Section -->
            <div class="space-y-4">
                <h3 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                    {{ setting('site_title', config('app.name')) }}
                </h3>
                <p class="text-gray-300 text-sm">
                    {{ setting('footer_description', setting('site_description', 'Transform your media with AI-powered effects')) }}
                </p>
                <div class="flex space-x-4">
                    @if(setting('footer_facebook_url'))
                        <a href="{{ setting('footer_facebook_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif
                    @if(setting('footer_twitter_url'))
                        <a href="{{ setting('footer_twitter_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <i class="fab fa-twitter"></i>
                        </a>
                    @endif
                    @if(setting('footer_instagram_url'))
                        <a href="{{ setting('footer_instagram_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <i class="fab fa-instagram"></i>
                        </a>
                    @endif
                    @if(setting('footer_youtube_url'))
                        <a href="{{ setting('footer_youtube_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <i class="fab fa-youtube"></i>
                        </a>
                    @endif
                    @if(setting('footer_linkedin_url'))
                        <a href="{{ setting('footer_linkedin_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    @endif
                    @if(setting('footer_tiktok_url'))
                        <a href="{{ setting('footer_tiktok_url') }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-bold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('image-submission.index') }}" class="text-gray-300 hover:text-white transition-colors duration-300 flex items-center space-x-2">
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('image-submission.image-effects') }}" class="text-gray-300 hover:text-white transition-colors duration-300 flex items-center space-x-2">
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span>Image Effects</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('image-submission.video-effects') }}" class="text-gray-300 hover:text-white transition-colors duration-300 flex items-center space-x-2">
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span>Video Effects</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h4 class="text-lg font-bold mb-4">Get in Touch</h4>
                <ul class="space-y-3 text-gray-300 text-sm">
                    @if(setting('footer_email'))
                        <li class="flex items-start space-x-3">
                            <i class="fas fa-envelope mt-1 text-cyan-400"></i>
                            <a href="mailto:{{ setting('footer_email') }}" class="hover:text-white transition-colors duration-300">
                                {{ setting('footer_email') }}
                            </a>
                        </li>
                    @endif
                    @if(setting('footer_phone'))
                        <li class="flex items-start space-x-3">
                            <i class="fas fa-phone mt-1 text-cyan-400"></i>
                            <a href="tel:{{ setting('footer_phone') }}" class="hover:text-white transition-colors duration-300">
                                {{ setting('footer_phone') }}
                            </a>
                        </li>
                    @endif
                    @if(setting('footer_address'))
                        <li class="flex items-start space-x-3">
                            <i class="fas fa-map-marker-alt mt-1 text-cyan-400"></i>
                            <span>{{ setting('footer_address') }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <p class="text-gray-400 text-sm">
                &copy; {{ date('Y') }} {{ setting('site_title', config('app.name')) }}. All rights reserved.
            </p>
            <div class="flex space-x-6 text-sm">
                @if(setting('footer_privacy_url'))
                    <a href="{{ setting('footer_privacy_url') }}" class="text-gray-400 hover:text-white transition-colors duration-300">Privacy Policy</a>
                @endif
                @if(setting('footer_terms_url'))
                    <a href="{{ setting('footer_terms_url') }}" class="text-gray-400 hover:text-white transition-colors duration-300">Terms of Service</a>
                @endif
                @if(setting('footer_contact_url'))
                    <a href="{{ setting('footer_contact_url') }}" class="text-gray-400 hover:text-white transition-colors duration-300">Contact</a>
                @endif
            </div>
        </div>
    </div>
</footer>

@if(setting('footer_code'))
    {!! setting('footer_code') !!}
@endif
