<?php

namespace Tests\Feature\Api;

use App\Models\SarOperation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SarOperationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_sar_operations(): void
    {
        SarOperation::create([
            'code' => 'SAR-202609-001',
            'title' => 'Operasi Pencarian Korban Banjir',
            'type' => 'Operasi SAR',
            'location' => 'Maros, Sulsel',
            'latitude' => -5.0215,
            'longitude' => 119.4682,
            'status' => 'Operasi Aktif',
            'severity_level' => 'Tinggi',
            'commander_name' => 'Ahmad Roni',
            'personnel_count' => 12,
            'start_date' => '2026-09-20',
        ]);

        $response = $this->getJson('/api/v1/sar-operations');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonStructure([
                'success',
                'count',
                'stats' => ['total_all', 'total_operasi', 'total_siaga', 'total_aktif', 'total_personnel', 'total_victims_saved'],
                'data' => [
                    '*' => ['id', 'code', 'title', 'type', 'location', 'status', 'severity_level', 'commander_name']
                ]
            ]);
    }

    public function test_can_create_sar_operation_with_sanctum_auth(): void
    {
        $user = User::factory()->create(['role' => 'administrator']);
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Siaga SAR Gelombang Tinggi Pantai Losari',
            'type' => 'Siaga SAR',
            'location' => 'Pantai Losari, Makassar',
            'status' => 'Siaga SAR',
            'severity_level' => 'Sedang',
            'commander_name' => 'Farhan Saputra',
            'personnel_count' => 8,
            'potensi_sar' => 'MKT Rescue, Basarnas',
            'start_date' => '2026-09-23',
            'description' => 'Patroli pengawasan kesiapsiagaan pengunjung pesisir',
        ];

        $response = $this->postJson('/api/v1/sar-operations', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Siaga SAR Gelombang Tinggi Pantai Losari')
            ->assertJsonPath('data.type', 'Siaga SAR');

        $this->assertDatabaseHas('sar_operations', [
            'title' => 'Siaga SAR Gelombang Tinggi Pantai Losari',
            'commander_name' => 'Farhan Saputra',
        ]);
    }

    public function test_can_create_sar_operation_with_legacy_payload_mapping(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $legacyPayload = [
            'title' => 'Operasi Evakuasi Pohon Tumbang',
            'location' => 'Jl. Perintis Kemerdekaan KM 9',
            'team_name' => 'Tim Reaksi Cepat MKT',
            'team_leader' => 'Budi Santoso',
            'severity' => 'Sedang',
            'victim_count' => 0,
            'equipment' => 'Chainsaw, Mobil Rescue',
        ];

        $response = $this->postJson('/api/v1/sar-operations', $legacyPayload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.commander_name', 'Budi Santoso')
            ->assertJsonPath('data.potensi_sar', 'Tim Reaksi Cepat MKT')
            ->assertJsonPath('data.equipment_used', 'Chainsaw, Mobil Rescue');
    }

    public function test_can_show_and_update_sar_operation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $op = SarOperation::create([
            'code' => 'SAR-202609-099',
            'title' => 'Siaga Bencana Kekeringan',
            'type' => 'Siaga SAR',
            'location' => 'Jeneponto',
            'status' => 'Siaga SAR',
            'severity_level' => 'Sedang',
            'personnel_count' => 5,
            'start_date' => '2026-09-22',
        ]);

        $detailResponse = $this->getJson("/api/v1/sar-operations/{$op->id}");
        $detailResponse->assertStatus(200)
            ->assertJsonPath('data.code', 'SAR-202609-099');

        $updateResponse = $this->putJson("/api/v1/sar-operations/{$op->id}", [
            'status' => 'Selesai',
            'victims_saved' => 15,
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Selesai')
            ->assertJsonPath('data.victims_saved', 15);
    }
}
