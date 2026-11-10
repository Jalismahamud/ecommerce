<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use Tests\TestCase;

class StorefrontLegalPagesTest extends TestCase
{
    public function test_privacy_and_terms_pages_render_static_content(): void
    {
        $this->get('/privacy-policy', $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonPath('component', 'Storefront/Legal')
            ->assertJsonPath('props.title', 'Privacy Policy')
            ->assertJsonPath('props.sections.0.heading', 'Information we collect');

        $this->get('/terms-and-conditions', $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonPath('component', 'Storefront/Legal')
            ->assertJsonPath('props.title', 'Terms & Conditions')
            ->assertJsonPath('props.sections.0.heading', 'Using Fresh Food');
    }

    /**
     * @return array<string, string|null>
     */
    private function inertiaHeaders(): array
    {
        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()) ?? '',
        ];
    }
}
