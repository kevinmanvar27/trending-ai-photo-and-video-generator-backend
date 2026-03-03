<!-- Footer -->
<footer class="bg-white mt-12 py-6 border-t border-gray-200">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <p class="text-gray-600 text-sm">
                &copy; {{ date('Y') }} {{ setting('site_title', config('app.name')) }}. 
                {{ setting('footer_text', 'All rights reserved.') }}
            </p>
            
            @if(setting('footer_code'))
                <div class="mt-4">
                    {!! setting('footer_code') !!}
                </div>
            @endif
        </div>
    </div>
</footer>
