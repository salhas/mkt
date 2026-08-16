<?php

namespace Database\Seeders;

use App\Models\SarOperation;
use App\Models\SarParticipation;
use App\Models\User;
use Illuminate\Database\Seeder;

class SarParticipationSeeder extends Seeder
{
    public function run(): void
    {
        $op1 = SarOperation::where('code', 'SAR-202608-001')->first();
        $mitraUser = User::where('role', 'mitra')->first();
        $relawanUser = User::where('role', 'relawan')->first();
        $medisUser = User::where('role', 'medis')->first();
        $staffUser = User::where('role', 'staff')->first();

        if ($op1) {
            SarParticipation::create([
                'sar_operation_id' => $op1->id,
                'user_id' => $medisUser ? $medisUser->id : null,
                'organization_name' => 'PMI Cabang Makassar & Sulsel',
                'commander_name' => 'Dr. Rahmat Hidayat (Danru PMI)',
                'contact_number' => '0812-4455-6677 / HT Ch 14',
                'personnel_count' => 6,
                'status' => 'Aktif Operasi Evakuasi',
                'departure_location' => 'Markas PMI Jalan Kandea, Makassar',
                'latitude' => -5.1325,
                'longitude' => 119.4210,
                'resources_deployed' => '1 Unit Ambulance Pertolongan Pertama, 1 Unit Boat Karet 15 HP, 6 Kit Pertolongan Darurat, Tabung Oksigen',
                'preparation_notes' => 'Tim PMI siap bertugas menyisir bibir pantai dan penanganan medis darurat korban selamat di posko utama.',
            ]);

            SarParticipation::create([
                'sar_operation_id' => $op1->id,
                'user_id' => $relawanUser ? $relawanUser->id : null,
                'organization_name' => 'Basarnas Special Group (BSG) Sulsel',
                'commander_name' => 'Bambang Irawan, S.T. (BSG Leader)',
                'contact_number' => '0811-9988-7766 / HT Ch 01',
                'personnel_count' => 8,
                'status' => 'Aktif Operasi Evakuasi',
                'departure_location' => 'Kantor Basarnas Sulsel Mandai',
                'latitude' => -5.0680,
                'longitude' => 119.5300,
                'resources_deployed' => '1 Unit Perahu RIB Engine 100 HP, Drone Thermal Underwater Sonar, Set Alat Selam Deep Sea (4 Set)',
                'preparation_notes' => 'Penyisiran sektor 2 laut lepas 5 Mil Laut dari titik perahu nelayan karam.',
            ]);

            SarParticipation::create([
                'sar_operation_id' => $op1->id,
                'user_id' => $mitraUser ? $mitraUser->id : null,
                'organization_name' => 'Polairud Polda Sulsel',
                'commander_name' => 'Iptu Agus Kurniawan (Polairud)',
                'contact_number' => '0852-1122-3344',
                'personnel_count' => 5,
                'status' => 'Aktif Operasi Evakuasi',
                'departure_location' => 'Dermaga Polairud Paotere',
                'latitude' => -5.1140,
                'longitude' => 119.4180,
                'resources_deployed' => '1 Unit Kapal Patroli Polairud C2, Teropong Malam, Life Jacket 20 Set',
                'preparation_notes' => 'Pengamanan area penyisiran dan patroli perairan muara pantai.',
            ]);

            SarParticipation::create([
                'sar_operation_id' => $op1->id,
                'user_id' => $staffUser ? $staffUser->id : null,
                'organization_name' => 'Tagana & BPBD Kab. Maros',
                'commander_name' => 'Syarifuddin (Koordinator BPBD)',
                'contact_number' => '0821-8899-0011',
                'personnel_count' => 10,
                'status' => 'Tiba di Posko Utama',
                'departure_location' => 'Kantor BPBD Kab. Maros',
                'latitude' => -5.0090,
                'longitude' => 119.5740,
                'resources_deployed' => '1 Unit Tenda Induk SAR, 1 Unit Genset 5000W, Logistik Konsumsi 200 Porsi, Velbed 10 Set',
                'preparation_notes' => 'Pendirian Posko Dapur Umum & Logistik Darurat untuk seluruh personel penolong.',
            ]);
        }
    }
}
