<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PartnerEquipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerEquipmentTest extends TestCase
{
    use RefreshDatabase;

    private function createPartnerWithUser(): array
    {
        $partner = Partner::create([
            'name' => 'Tim SAR Kemanusiaan Mitra',
            'category' => 'Tim Rescue',
            'pic_name' => 'Komandan Rescue',
            'pic_phone' => '081234567890',
            'email' => 'rescue@mitra.org',
            'status' => 'Aktif',
        ]);

        $user = User::factory()->create([
            'name' => 'Akun Mitra',
            'email' => 'rescue@mitra.org',
            'role' => 'mitra',
            'partner_id' => $partner->id,
        ]);

        return [$partner, $user];
    }

    public function test_mitra_can_view_equipments_page(): void
    {
        [$partner, $user] = $this->createPartnerWithUser();

        $equipment = PartnerEquipment::create([
            'partner_id' => $partner->id,
            'item_code' => 'ALUT-01',
            'name' => 'Perahu Karet LCR 4.2M',
            'category' => 'Water Rescue',
            'quantity' => 2,
            'unit' => 'Set',
            'condition' => 'Siap Pakai',
            'status' => 'Tersedia',
        ]);

        $response = $this->actingAs($user)->get(route('partner.equipments'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Partner/Equipments')
            ->has('partner')
            ->has('equipments.data', 1)
            ->where('equipments.data.0.id', $equipment->id)
            ->where('equipments.data.0.name', 'Perahu Karet LCR 4.2M')
        );
    }

    public function test_mitra_can_add_equipment(): void
    {
        [$partner, $user] = $this->createPartnerWithUser();

        $payload = [
            'name' => 'Genset Lapangan 5000 Watt',
            'item_code' => 'GEN-01',
            'category' => 'Penerangan & Kelistrikan',
            'quantity' => 1,
            'unit' => 'Unit',
            'condition' => 'Siap Pakai',
            'storage_location' => 'Gudang Logistik',
            'ownership_status' => 'Milik Sendiri',
            'status' => 'Tersedia',
            'notes' => 'Bahan bakar bensin, starter elektrik',
        ];

        $response = $this->actingAs($user)->post(route('partner.equipments.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('partner_equipments', [
            'partner_id' => $partner->id,
            'name' => 'Genset Lapangan 5000 Watt',
            'item_code' => 'GEN-01',
            'category' => 'Penerangan & Kelistrikan',
        ]);
    }

    public function test_mitra_can_update_own_equipment(): void
    {
        [$partner, $user] = $this->createPartnerWithUser();

        $equipment = PartnerEquipment::create([
            'partner_id' => $partner->id,
            'item_code' => 'MED-01',
            'name' => 'Tandu Basket Stretcher',
            'category' => 'Medis & Evakuasi',
            'quantity' => 2,
            'unit' => 'Unit',
            'condition' => 'Baik',
            'ownership_status' => 'Milik Sendiri',
            'status' => 'Tersedia',
        ]);

        $updatePayload = [
            'name' => 'Tandu Basket Stretcher & Spinal Board',
            'item_code' => 'MED-01',
            'category' => 'Medis & Evakuasi',
            'quantity' => 4,
            'unit' => 'Unit',
            'condition' => 'Siap Pakai',
            'storage_location' => 'Ambulans Rescue',
            'ownership_status' => 'Milik Sendiri',
            'status' => 'Sedang Digunakan',
            'notes' => 'Tambahan 2 unit baru',
        ];

        $response = $this->actingAs($user)->post(route('partner.equipments.update', $equipment->id), $updatePayload);

        $response->assertRedirect();
        $this->assertDatabaseHas('partner_equipments', [
            'id' => $equipment->id,
            'name' => 'Tandu Basket Stretcher & Spinal Board',
            'quantity' => 4,
            'status' => 'Sedang Digunakan',
        ]);
    }

    public function test_mitra_cannot_update_other_partner_equipment(): void
    {
        [$partner1, $user1] = $this->createPartnerWithUser();

        $partner2 = Partner::create([
            'name' => 'Mitra Lain',
            'category' => 'PMI',
            'pic_name' => 'Ketua PMI',
            'email' => 'other@mitra.org',
            'status' => 'Aktif',
        ]);

        $equipment2 = PartnerEquipment::create([
            'partner_id' => $partner2->id,
            'item_code' => 'OTHER-01',
            'name' => 'Peralatan Rahasia Mitra Lain',
            'category' => 'Water Rescue',
            'quantity' => 1,
            'unit' => 'Unit',
            'condition' => 'Baik',
            'ownership_status' => 'Milik Sendiri',
            'status' => 'Tersedia',
        ]);

        $response = $this->actingAs($user1)->post(route('partner.equipments.update', $equipment2->id), [
            'name' => 'Hacked Item',
            'category' => 'Water Rescue',
            'quantity' => 1,
            'unit' => 'Unit',
            'condition' => 'Baik',
            'ownership_status' => 'Milik Sendiri',
            'status' => 'Tersedia',
        ]);

        $response->assertStatus(403);
    }

    public function test_mitra_can_delete_own_equipment(): void
    {
        [$partner, $user] = $this->createPartnerWithUser();

        $equipment = PartnerEquipment::create([
            'partner_id' => $partner->id,
            'item_code' => 'DEL-01',
            'name' => 'Peralatan Rusak Berat',
            'category' => 'Lainnya',
            'quantity' => 1,
            'unit' => 'Unit',
            'condition' => 'Rusak Berat',
            'ownership_status' => 'Milik Sendiri',
            'status' => 'Tidak Aktif',
        ]);

        $response = $this->actingAs($user)->delete(route('partner.equipments.destroy', $equipment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('partner_equipments', [
            'id' => $equipment->id,
        ]);
    }

    public function test_equipments_search_and_filters(): void
    {
        [$partner, $user] = $this->createPartnerWithUser();

        PartnerEquipment::create([
            'partner_id' => $partner->id,
            'item_code' => 'RADIO-01',
            'name' => 'Handy Talky Icom VHF',
            'category' => 'Komunikasi & Navigasi',
            'quantity' => 6,
            'unit' => 'Unit',
            'condition' => 'Siap Pakai',
            'ownership_status' => 'Milik Sendiri',
            'status' => 'Tersedia',
        ]);

        PartnerEquipment::create([
            'partner_id' => $partner->id,
            'item_code' => 'TENT-01',
            'name' => 'Tenda Regu Bencana',
            'category' => 'Shelter & Tenda',
            'quantity' => 1,
            'unit' => 'Set',
            'condition' => 'Rusak Ringan',
            'ownership_status' => 'Milik Sendiri',
            'status' => 'Maintenance',
        ]);

        // Test search
        $searchResponse = $this->actingAs($user)->get(route('partner.equipments', ['search' => 'Icom']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertInertia(fn ($page) => $page
            ->has('equipments.data', 1)
            ->where('equipments.data.0.name', 'Handy Talky Icom VHF')
        );

        // Test category filter
        $catResponse = $this->actingAs($user)->get(route('partner.equipments', ['category' => 'Shelter & Tenda']));
        $catResponse->assertStatus(200);
        $catResponse->assertInertia(fn ($page) => $page
            ->has('equipments.data', 1)
            ->where('equipments.data.0.name', 'Tenda Regu Bencana')
        );
    }
}
