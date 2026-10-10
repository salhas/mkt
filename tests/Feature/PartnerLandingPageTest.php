<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Volunteer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerLandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_auto_generates_slug_on_creation(): void
    {
        $partner = Partner::create([
            'name' => 'SAR Unhas',
            'code' => 'MTR-RSC-008',
            'category' => 'Tim Rescue',
            'pic_name' => 'Komandan SAR',
            'pic_phone' => '08123456789',
            'email' => 'sarunhas@gmail.com',
            'status' => 'Aktif',
            'personnel_count' => 30,
        ]);

        $this->assertEquals('sar-unhas', $partner->slug);
    }

    public function test_public_can_access_partners_directory_page(): void
    {
        $partner = Partner::create([
            'name' => 'SAR Unhas',
            'code' => 'MTR-RSC-008',
            'category' => 'Tim Rescue',
            'email' => 'sarunhas@gmail.com',
            'status' => 'Aktif',
        ]);

        $response = $this->get(route('public.partners'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Public/Partners')
                ->has('partners')
        );
    }

    public function test_public_can_access_single_partner_landing_page_by_slug(): void
    {
        $partner = Partner::create([
            'name' => 'SAR Unhas',
            'slug' => 'sar-unhas',
            'code' => 'MTR-RSC-008',
            'category' => 'Tim Rescue',
            'pic_name' => 'Juno',
            'pic_phone' => '08123456789',
            'email' => 'sarunhas@gmail.com',
            'description' => 'Unit SAR Mahasiswa Unhas',
            'vision' => 'Visi SAR Unggul',
            'mission' => 'Misi Tanggap Darurat',
            'status' => 'Aktif',
            'personnel_count' => 30,
        ]);

        // Tambah personel relawan
        Volunteer::create([
            'partner_id' => $partner->id,
            'name' => 'Relawan Pertama',
            'email' => 'relawan1@sarunhas.org',
            'phone' => '0811111111',
            'role' => 'Relawan Rescuer',
            'status' => 'Aktif',
        ]);

        $response = $this->get('/mitra/sar-unhas');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Public/PartnerDetail')
                ->where('partner.name', 'SAR Unhas')
                ->where('partner.slug', 'sar-unhas')
                ->where('totalMembersCount', 30)
                ->has('members', 1)
        );
    }

    public function test_public_can_access_partner_landing_page_by_code(): void
    {
        $partner = Partner::create([
            'name' => 'Basarnas Makassar',
            'slug' => 'basarnas-makassar',
            'code' => 'MTR-BAS-001',
            'category' => 'Basarnas',
            'email' => 'basarnas@gov.id',
            'status' => 'Aktif',
        ]);

        $response = $this->get('/mitra/MTR-BAS-001');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Public/PartnerDetail')
                ->where('partner.code', 'MTR-BAS-001')
        );
    }

    public function test_nonexistent_partner_landing_page_returns_404(): void
    {
        $response = $this->get('/mitra/mitra-tidak-ada');
        $response->assertStatus(404);
    }

    public function test_public_can_register_as_volunteer_for_specific_partner(): void
    {
        $partner = Partner::create([
            'name' => 'SAR Unhas',
            'slug' => 'sar-unhas',
            'category' => 'Tim Rescue',
            'email' => 'sarunhas@gmail.com',
            'status' => 'Aktif',
        ]);

        $response = $this->post(route('volunteers.public-register'), [
            'partner_id' => $partner->id,
            'membership_type' => 'relawan',
            'name' => 'Ahmad Rescuer',
            'email' => 'ahmad@gmail.com',
            'phone' => '081299998888',
            'blood_type' => 'O',
            'role' => 'Relawan Rescuer Lapangan',
            'password' => 'secret123',
            'notes' => 'Spesialisasi Water Rescue',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('volunteers', [
            'partner_id' => $partner->id,
            'membership_type' => 'relawan',
            'name' => 'Ahmad Rescuer',
            'email' => 'ahmad@gmail.com',
            'role' => 'Relawan Rescuer Lapangan',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad@gmail.com',
            'role' => 'relawan',
            'partner_id' => $partner->id,
        ]);
    }

    public function test_public_can_register_as_member_with_terms_accepted(): void
    {
        $partner = Partner::create([
            'name' => 'SAR Unhas',
            'slug' => 'sar-unhas',
            'category' => 'Tim Rescue',
            'email' => 'sarunhas@gmail.com',
            'status' => 'Aktif',
        ]);

        $response = $this->post(route('volunteers.public-register'), [
            'partner_id' => $partner->id,
            'membership_type' => 'anggota',
            'terms_accepted' => true,
            'name' => 'Budi Anggota',
            'email' => 'budi@sarunhas.org',
            'phone' => '081288887777',
            'blood_type' => 'AB',
            'role' => 'Anggota Operasional Lapangan',
            'password' => 'secret123',
            'notes' => 'Pernah mengikuti Diksar',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('volunteers', [
            'partner_id' => $partner->id,
            'membership_type' => 'anggota',
            'name' => 'Budi Anggota',
            'email' => 'budi@sarunhas.org',
            'role' => 'Anggota Operasional Lapangan',
        ]);
    }

    public function test_member_registration_fails_if_terms_not_accepted(): void
    {
        $partner = Partner::create([
            'name' => 'SAR Unhas',
            'slug' => 'sar-unhas',
            'category' => 'Tim Rescue',
            'email' => 'sarunhas@gmail.com',
            'status' => 'Aktif',
        ]);

        $response = $this->post(route('volunteers.public-register'), [
            'partner_id' => $partner->id,
            'membership_type' => 'anggota',
            'terms_accepted' => false,
            'name' => 'Budi Gagal',
            'email' => 'budi.gagal@sarunhas.org',
            'phone' => '081288887777',
            'blood_type' => 'AB',
            'role' => 'Anggota Operasional Lapangan',
        ]);

        $response->assertSessionHasErrors('terms_accepted');
        $this->assertDatabaseMissing('volunteers', [
            'email' => 'budi.gagal@sarunhas.org',
        ]);
    }
}
