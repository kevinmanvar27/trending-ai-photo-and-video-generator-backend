<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h1>About Us</h1><p>Welcome to our AI Photo and Video Generator platform! We are dedicated to providing cutting-edge AI technology to transform your photos and videos into stunning artistic creations.</p><h2>Our Mission</h2><p>Our mission is to make professional-quality photo and video editing accessible to everyone through the power of artificial intelligence.</p><h2>What We Do</h2><p>We offer a wide range of AI-powered templates and effects that can transform your images and videos in seconds. From cartoon effects to artistic styles, our platform provides endless creative possibilities.</p>',
                'meta_description' => 'Learn more about our AI-powered photo and video generation platform and our mission to make professional editing accessible to everyone.',
                'meta_keywords' => 'about us, AI photo generator, AI video generator, company information',
                'is_active' => true,
                'order' => 0
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><p><strong>Last Updated: March 2, 2026</strong></p><h2>1. Information We Collect</h2><p>We collect information that you provide directly to us, including:</p><ul><li>Name and email address</li><li>Account credentials</li><li>Photos and videos you upload</li><li>Payment information</li></ul><h2>2. How We Use Your Information</h2><p>We use the information we collect to:</p><ul><li>Provide and improve our services</li><li>Process your AI generation requests</li><li>Send you updates and notifications</li><li>Protect against fraud and abuse</li></ul><h2>3. Data Security</h2><p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, or disclosure.</p><h2>4. Your Rights</h2><p>You have the right to access, correct, or delete your personal information at any time through your account settings.</p><h2>5. Contact Us</h2><p>If you have any questions about this Privacy Policy, please contact us at privacy@example.com</p>',
                'meta_description' => 'Read our privacy policy to understand how we collect, use, and protect your personal information.',
                'meta_keywords' => 'privacy policy, data protection, user privacy, GDPR',
                'is_active' => true,
                'order' => 1
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h1>Terms of Service</h1><p><strong>Last Updated: March 2, 2026</strong></p><h2>1. Acceptance of Terms</h2><p>By accessing and using our service, you accept and agree to be bound by these Terms of Service.</p><h2>2. Use of Service</h2><p>You agree to use our service only for lawful purposes and in accordance with these Terms. You must not:</p><ul><li>Upload illegal or offensive content</li><li>Violate any intellectual property rights</li><li>Attempt to harm or exploit our service</li><li>Share your account credentials</li></ul><h2>3. Subscriptions and Payments</h2><p>Some features require a paid subscription. You agree to pay all fees associated with your subscription plan.</p><h2>4. Content Ownership</h2><p>You retain ownership of the content you upload. We retain the right to use generated content for service improvement and marketing purposes.</p><h2>5. Limitation of Liability</h2><p>We are not liable for any indirect, incidental, or consequential damages arising from your use of our service.</p><h2>6. Changes to Terms</h2><p>We reserve the right to modify these terms at any time. Continued use of the service constitutes acceptance of modified terms.</p>',
                'meta_description' => 'Read our terms of service to understand the rules and guidelines for using our AI photo and video generation platform.',
                'meta_keywords' => 'terms of service, terms and conditions, user agreement, legal',
                'is_active' => true,
                'order' => 2
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '<h1>Frequently Asked Questions</h1><h2>General Questions</h2><h3>What is this platform?</h3><p>We are an AI-powered photo and video generation platform that allows you to transform your images and videos using advanced artificial intelligence.</p><h3>How does it work?</h3><p>Simply upload your photo or video, select a template or effect, and our AI will process it to create stunning results in seconds.</p><h2>Account & Subscription</h2><h3>Do I need an account?</h3><p>Yes, you need to create a free account to use our services. Some features require a paid subscription.</p><h3>What payment methods do you accept?</h3><p>We accept all major credit cards, PayPal, and other popular payment methods.</p><h3>Can I cancel my subscription?</h3><p>Yes, you can cancel your subscription at any time from your account settings.</p><h2>Technical Questions</h2><h3>What file formats are supported?</h3><p>We support JPG, PNG, GIF for images and MP4, MOV for videos.</p><h3>What is the maximum file size?</h3><p>The maximum file size is 10MB for uploads and 50MB for processed files.</p><h3>How long does processing take?</h3><p>Most images process in 10-30 seconds. Videos may take 1-5 minutes depending on length and complexity.</p>',
                'meta_description' => 'Find answers to frequently asked questions about our AI photo and video generation service.',
                'meta_keywords' => 'FAQ, frequently asked questions, help, support',
                'is_active' => true,
                'order' => 3
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>We\'d love to hear from you! Get in touch with us using any of the methods below.</p><h2>Email</h2><p>For general inquiries: <a href="mailto:info@example.com">info@example.com</a></p><p>For support: <a href="mailto:support@example.com">support@example.com</a></p><p>For business inquiries: <a href="mailto:business@example.com">business@example.com</a></p><h2>Office Hours</h2><p>Monday - Friday: 9:00 AM - 6:00 PM (EST)<br>Saturday - Sunday: Closed</p><h2>Response Time</h2><p>We typically respond to all inquiries within 24-48 hours during business days.</p><h2>Social Media</h2><p>Follow us on social media for updates and news:</p><ul><li>Twitter: @example</li><li>Facebook: /example</li><li>Instagram: @example</li></ul>',
                'meta_description' => 'Get in touch with us for support, inquiries, or feedback about our AI photo and video generation service.',
                'meta_keywords' => 'contact us, support, customer service, help',
                'is_active' => true,
                'order' => 4
            ]
        ];

        foreach ($pages as $pageData) {
            Page::create($pageData);
        }
    }
}
