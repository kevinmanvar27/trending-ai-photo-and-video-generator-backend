<?php

namespace App\Http\ViewComposers;

use App\Models\Setting;
use Illuminate\View\View;

class SettingsComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // Get site settings with fallback defaults
        $siteTitle = Setting::get('site_title', 'AI Image & Video Processing');
        $siteDescription = Setting::get('site_description', 'Transform your content with AI-powered effects');
        $footerText = Setting::get('footer_text', 'Powered by AI');
        
        // Share with all views using this composer
        $view->with([
            'siteTitle' => $siteTitle,
            'siteDescription' => $siteDescription,
            'footerText' => $footerText,
        ]);
    }
}
