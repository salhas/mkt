<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerRegistrationTermsTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_terms_download_endpoint_returns_pdf(): void
    {
        $response = $this->get('/syarat-ketentuan-kemitraan');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_partner_terms_web_page_renders_successfully(): void
    {
        $response = $this->get('/syarat-ketentuan');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/PartnerTerms')
            ->has('pdfUrl')
            ->has('downloadUrl')
        );
    }

    public function test_partner_registration_fails_without_terms_acceptance(): void
    {
        $payload = [
            'name' => 'Organisasi Relawan Siaga',
            'category' => 'Tim Rescue',
            'pic_name' => 'Ahmad Fauzi',
            'pic_phone' => '081298765432',
            'email' => 'siaga@rescue.org',
            'password' => 'secret123',
            // terms_accepted missing / false
        ];

        $response = $this->post('/register-partner', $payload);

        $response->assertSessionHasErrors('terms_accepted');
    }

    public function test_partner_registration_succeeds_when_terms_accepted(): void
    {
        $payload = [
            'name' => 'PMI Cabang Utama',
            'category' => 'PMI',
            'pic_name' => 'Siti Nurhaliza',
            'pic_phone' => '081122334455',
            'email' => 'sekretariat@pmi-utama.org',
            'password' => 'mitra2026',
            'terms_accepted' => true,
        ];

        $response = $this->postJson('/register-partner', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('partners', [
            'email' => 'sekretariat@pmi-utama.org',
            'name' => 'PMI Cabang Utama',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'sekretariat@pmi-utama.org',
            'role' => 'mitra',
        ]);
    }
}
