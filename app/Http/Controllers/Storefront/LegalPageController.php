<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class LegalPageController extends Controller
{
    public function privacy(): Response
    {
        return Inertia::render('Storefront/Legal', [
            'title' => 'Privacy Policy',
            'updatedAt' => 'November 10, 2026',
            'sections' => [
                [
                    'heading' => 'Information we collect',
                    'body' => 'Fresh Food may process information you provide when you contact us, sign in, or use shopping features. The information handled depends on the features you choose to use.',
                ],
                [
                    'heading' => 'How information is used',
                    'body' => 'Information is used to operate and improve the storefront, respond to requests, maintain account security, and support shopping activity such as keeping your cart available.',
                ],
                [
                    'heading' => 'Sharing and retention',
                    'body' => 'We do not sell personal information. Information may be shared with service providers only as needed to operate the site or when disclosure is required by law. It is retained only for legitimate operational, legal, or security needs.',
                ],
                [
                    'heading' => 'Security and your choices',
                    'body' => 'Reasonable safeguards are used to protect information, although no online service can guarantee absolute security. You may contact us to ask about, correct, or request deletion of information associated with you, subject to applicable requirements.',
                ],
                [
                    'heading' => 'Contact',
                    'body' => 'For privacy questions or requests, please contact the Fresh Food site administrator.',
                ],
            ],
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('Storefront/Legal', [
            'title' => 'Terms & Conditions',
            'updatedAt' => 'November 10, 2026',
            'sections' => [
                [
                    'heading' => 'Using Fresh Food',
                    'body' => 'By using this site, you agree to use it lawfully and not interfere with its operation, security, or other visitors’ access.',
                ],
                [
                    'heading' => 'Products, shops, and availability',
                    'body' => 'Product descriptions, prices, stock, and availability are provided by participating shops and may change without notice. A product shown online may become unavailable before an order is confirmed.',
                ],
                [
                    'heading' => 'Shopping cart and orders',
                    'body' => 'Adding an item to a cart does not reserve stock or create a completed order. Checkout and payment are not currently available on this site; no purchase is complete until an order process is offered and confirmed.',
                ],
                [
                    'heading' => 'Accounts and site content',
                    'body' => 'You are responsible for keeping your account credentials secure and for activity under your account. Site branding, design, and content may not be copied or reused without permission.',
                ],
                [
                    'heading' => 'Changes and contact',
                    'body' => 'These terms may be updated as the service changes. Continued use after an update means you accept the revised terms. For questions, please contact the Fresh Food site administrator.',
                ],
            ],
        ]);
    }
}
