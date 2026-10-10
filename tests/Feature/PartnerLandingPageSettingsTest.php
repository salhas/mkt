<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerLandingPageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_partner_can_view_landing_page_settings(): void
    {
        $partner = Partner::create([
            'name' => 'SAR Unhas',
            'slug' => 'sar-unhas',
            'category' => 'Tim Rescue',
            'pic_name' => 'Juno',
            'pic_phone' => '08123456789',
            'email' => 'sarunhas@gmail.com',
            'status' => 'Aktif',
        ]);

        $user = User::factory()->create([
            'role' => 'mitra',
            'partner_id' => $partner->id,
            'email' => 'sarunhas@gmail.com',
        ]);

        $response = $this->actingAs($user)->get(route('partner.landing-page'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Partner/LandingPageSettings')
                ->where('partner.name', 'SAR Unhas')
                ->where('partner.slug', 'sar-unhas')
                ->has('landingPageUrl')
        );
    }

    public function test_partner_can_update_landing_page_elements_connected_to_database(): void
    {
        $partner = Partner::create([
            'name' => 'SAR Unhas',
            'slug' => 'sar-unhas',
            'category' => 'Tim Rescue',
            'pic_name' => 'Juno',
            'pic_phone' => '08123456789',
            'email' => 'sarunhas@gmail.com',
            'status' => 'Aktif',
        ]);

        $user = User::factory()->create([
            'role' => 'mitra',
            'partner_id' => $partner->id,
            'email' => 'sarunhas@gmail.com',
        ]);

        $response = $this->actingAs($user)->post(route('partner.landing-page.update'), [
            'name' => 'SAR Unhas Makassar',
            'slug' => 'sar-unhas-makassar',
            'category' => 'Tim Rescue',
            'tagline' => 'Unit Reaksi Cepat SAR Siaga Bencana 24 Jam',
            'description' => 'Lembaga SAR mahasiswa tertua dan terlatih.',
            'vision' => 'Visi Tanggap Cepat',
            'mission' => '1. Misi Rescue Lapangan&#10;2. Bantuan Kemanusiaan',
            'mou_number' => 'MOU/MKT-SAR/2026/009',
            'readiness_status' => 'Posko Operasi 24/7 Siaga Penuh',
            'recruitment_status' => 'Buka',
            'personnel_count' => 45,
            'membership_terms' => "1. Wajib Diksar SAR Unhas\n2. Mematuhi AD/ART",
            'pillar_pre' => 'Fokus latihan water rescue berkala',
            'pillar_during' => 'Pengerahan perahu LCR dan personil evakuasi',
            'pillar_post' => 'Penyaluran logistik & trauma healing',
            'pic_name' => 'Juno Rescuer',
            'pic_phone' => '081299990000',
            'email' => 'sekretariat@sarunhas.org',
            'address' => 'Gedung PKM Unhas Tamalanrea',
            'website' => 'https://sarunhas.org',
            'instagram' => '@sar_unhas',
            'facebook' => 'sarunhas.official',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $partner->refresh();
        $this->assertEquals('SAR Unhas Makassar', $partner->name);
        $this->assertEquals('sar-unhas-makassar', $partner->slug);
        $this->assertEquals('Unit Reaksi Cepat SAR Siaga Bencana 24 Jam', $partner->tagline);
        $this->assertEquals('Posko Operasi 24/7 Siaga Penuh', $partner->readiness_status);
        $this->assertEquals(45, $partner->personnel_count);
        $this->assertEquals("1. Wajib Diksar SAR Unhas\n2. Mematuhi AD/ART", $partner->membership_terms);
        $this->assertEquals('Fokus latihan water rescue berkala', $partner->pillar_pre);

        // Verifikasi landing page publik membaca data baru dari database
        $publicResponse = $this->get('/mitra/sar-unhas-makassar');
        $publicResponse->assertStatus(200);
        $publicResponse->assertInertia(fn ($page) =>
            $page->component('Public/PartnerDetail')
                ->where('partner.tagline', 'Unit Reaksi Cepat SAR Siaga Bencana 24 Jam')
                ->where('partner.readiness_status', 'Posko Operasi 24/7 Siaga Penuh')
                ->where('partner.pillar_pre', 'Fokus latihan water rescue berkala')
        );
    }
}
