<?php

namespace Tests\Feature;

use App\Models\Ecosystem;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerEcosystemTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function createMitraUser(): User
    {
        return User::factory()->create([
            'role' => 'mitra',
        ]);
    }

    public function test_admin_can_view_volunteers_page_with_ecosystems(): void
    {
        $admin = $this->createAdminUser();

        $ecosystem = Ecosystem::firstOrCreate(
            ['code' => 'U-HUMANITY'],
            [
                'name' => 'Ekosistem Lembaga Kemanusiaan Unhas (U-Humanity)',
                'region' => 'Makassar, Sulawesi Selatan',
                'lead_institution' => 'Universitas Hasanuddin',
                'status' => 'Aktif',
            ]
        );

        $partner = Partner::create([
            'ecosystem_id' => $ecosystem->id,
            'name' => 'KSR PMI Unit Unhas',
            'category' => 'PMI',
            'pic_name' => 'Koordinator KSR',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->get('/volunteers');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Volunteers/Index')
            ->has('ecosystems')
            ->where('ecosystems.0.id', $ecosystem->id)
            ->where('ecosystems.0.name', 'Ekosistem Lembaga Kemanusiaan Unhas (U-Humanity)')
            ->where('ecosystems.0.region', 'Makassar, Sulawesi Selatan')
        );
    }

    public function test_admin_can_create_new_ecosystem(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'name' => 'Ekosistem Kemanusiaan Jawa Barat',
            'code' => 'JABAR-HUMANITY',
            'region' => 'Bandung, Jawa Barat',
            'lead_institution' => 'Forum Relawan Jabar',
            'pic_name' => 'Kang Asep',
            'pic_phone' => '081234567890',
            'pic_email' => 'asep@jabar-relawan.id',
            'description' => 'Jejaring relawan dan kemitraan Jawa Barat',
            'status' => 'Aktif',
        ];

        $response = $this->actingAs($admin)->post('/ecosystems', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('ecosystems', [
            'name' => 'Ekosistem Kemanusiaan Jawa Barat',
            'code' => 'JABAR-HUMANITY',
            'region' => 'Bandung, Jawa Barat',
        ]);
    }

    public function test_admin_can_update_existing_ecosystem(): void
    {
        $admin = $this->createAdminUser();

        $ecosystem = Ecosystem::create([
            'name' => 'Ekosistem Kemanusiaan Yogyakarta',
            'code' => 'DIY-HUMANITY',
            'region' => 'Yogyakarta, D.I. Yogyakarta',
            'status' => 'Aktif',
        ]);

        $updatePayload = [
            'name' => 'Ekosistem Lembaga Kemanusiaan DIY Mandiri',
            'code' => 'DIY-HUMANITY',
            'region' => 'Sleman & Bantul, DIY',
            'lead_institution' => 'UGM Peduli',
            'status' => 'Aktif',
        ];

        $response = $this->actingAs($admin)->patch("/ecosystems/{$ecosystem->id}", $updatePayload);

        $response->assertRedirect();
        $this->assertDatabaseHas('ecosystems', [
            'id' => $ecosystem->id,
            'name' => 'Ekosistem Lembaga Kemanusiaan DIY Mandiri',
            'region' => 'Sleman & Bantul, DIY',
            'lead_institution' => 'UGM Peduli',
        ]);
    }

    public function test_admin_can_delete_ecosystem_and_partner_is_detached(): void
    {
        $admin = $this->createAdminUser();

        $ecosystem = Ecosystem::create([
            'name' => 'Ekosistem Siap Hapus',
            'region' => 'Kota Makassar',
            'status' => 'Aktif',
        ]);

        $partner = Partner::create([
            'ecosystem_id' => $ecosystem->id,
            'name' => 'Mitra Binaan',
            'category' => 'Lembaga Medis',
            'pic_name' => 'Dr. Budi',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->delete("/ecosystems/{$ecosystem->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('ecosystems', ['id' => $ecosystem->id]);

        $partner->refresh();
        $this->assertNull($partner->ecosystem_id);
    }

    public function test_mitra_role_cannot_modify_ecosystems(): void
    {
        $mitra = $this->createMitraUser();

        $payload = [
            'name' => 'Ekosistem Ilegal Mitra',
            'region' => 'Jakarta',
            'status' => 'Aktif',
        ];

        $response = $this->actingAs($mitra)->post('/ecosystems', $payload);
        $response->assertStatus(403);

        $ecosystem = Ecosystem::create([
            'name' => 'Ekosistem Asli',
            'region' => 'Makassar',
            'status' => 'Aktif',
        ]);

        $updateResponse = $this->actingAs($mitra)->patch("/ecosystems/{$ecosystem->id}", [
            'name' => 'Hacked Name',
            'region' => 'Makassar',
        ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($mitra)->delete("/ecosystems/{$ecosystem->id}");
        $deleteResponse->assertStatus(403);
    }

    public function test_partner_can_be_filtered_by_ecosystem(): void
    {
        $admin = $this->createAdminUser();

        $eco1 = Ecosystem::create(['name' => 'Ekosistem Unhas', 'region' => 'Makassar']);
        $eco2 = Ecosystem::create(['name' => 'Ekosistem UNM', 'region' => 'Makassar']);

        $p1 = Partner::create(['ecosystem_id' => $eco1->id, 'name' => 'Partner Unhas', 'category' => 'PMI', 'pic_name' => 'A']);
        $p2 = Partner::create(['ecosystem_id' => $eco2->id, 'name' => 'Partner UNM', 'category' => 'PMI', 'pic_name' => 'B']);

        $response = $this->actingAs($admin)->get("/volunteers?ecosystem_id={$eco1->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Volunteers/Index')
            ->where('partners.0.id', $p1->id)
            ->has('partners', 1)
        );
    }

    public function test_ecosystem_creation_fails_with_duplicate_code(): void
    {
        $admin = $this->createAdminUser();

        Ecosystem::create([
            'name' => 'Ekosistem Pertama',
            'code' => 'KODE-SAMA',
            'region' => 'Makassar',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->post('/ecosystems', [
            'name' => 'Ekosistem Kedua',
            'code' => 'KODE-SAMA',
            'region' => 'Gowa',
            'status' => 'Aktif',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_ecosystem_creation_auto_generates_unique_code_when_code_empty(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post('/ecosystems', [
            'name' => 'Ekosistem Tanpa Kode Manual',
            'code' => '',
            'region' => 'Maros',
            'status' => 'Aktif',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ecosystems', [
            'name' => 'Ekosistem Tanpa Kode Manual',
        ]);

        $created = Ecosystem::where('name', 'Ekosistem Tanpa Kode Manual')->first();
        $this->assertNotNull($created->code);
        $this->assertStringStartsWith('EKO-', $created->code);
    }
}
