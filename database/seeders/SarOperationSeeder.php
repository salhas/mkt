<?php

namespace Database\Seeders;

use App\Models\SarOperation;
use Illuminate\Database\Seeder;

class SarOperationSeeder extends Seeder
{
    public function run(): void
    {
        $operations = [
            [
                'code' => 'SAR-202608-001',
                'title' => 'Operasi SAR Pencarian Nelayan Hilang di Perairan Maros',
                'type' => 'Operasi SAR',
                'location' => 'Perairan Pantai Takalar - Maros, Sulawesi Selatan',
                'latitude' => -5.021500,
                'longitude' => 119.468200,
                'status' => 'Operasi Aktif',
                'severity_level' => 'Siaga 1 / Kritis',
                'commander_name' => 'Kapten Hendra Suwandi (SMC Basarnas / MKT)',
                'personnel_count' => 28,
                'potensi_sar' => 'Basarnas Sulsel, BPBD Maros, PMI, Tim Rescue MKT 727, Polairud',
                'deployed_teams' => 'Tim Rescue 727 MKT (10 Personel), Basarnas Special Group (BSG) (8 Personel), Polairud Patroli Laut (5 Personel), Tim Rescue PMI (5 Personel)',
                'standby_teams' => 'Tim Medis Darurat MKT (Posko Pantai), Tim Logistik BPBD Maros, Relawan Donor Darah PMI',
                'start_date' => '2026-08-01',
                'end_date' => null,
                'description' => 'Perahu nelayan diterjang ombak tinggi saat melaut di cuaca buruk. 2 korban telah berhasil dievakuasi selamat, 1 korban dalam pencarian penyisiran perairan.',
                'equipment_used' => '2 Unit Perahu Karet RIB, 1 Unit Drone Thermal, Alkon, Perlengkapan Selam, P3K Darurat',
                'victims_saved' => 2,
                'victims_injured' => 1,
                'victims_deceased' => 0,
                'victims_missing' => 1,
            ],
            [
                'code' => 'SAR-202608-002',
                'title' => 'Siaga SAR Pengamanan Arus Wisata Pesisir Pantai Tanjung Bira',
                'type' => 'Siaga SAR',
                'location' => 'Kawasan Pesisir Pantai Tanjung Bira, Bulukumba',
                'latitude' => -5.612800,
                'longitude' => 120.460100,
                'status' => 'Siaga SAR',
                'severity_level' => 'Sedang',
                'commander_name' => 'Ahmad Roni (Koordinator Posko Siaga MKT)',
                'personnel_count' => 15,
                'potensi_sar' => 'Tim Rescue 727 MKT, Lifeguard Bira, Balawista, BPBD Bulukumba',
                'deployed_teams' => 'Tim Lifeguard Pantai Bira (6 Personel), Patroli Laut MKT (4 Personel), Posko Medis Lapangan (5 Personel)',
                'standby_teams' => 'Tim Mobilization Rescuer MKT Makassar, Ambulance Gawat Darurat RS Bulukumba',
                'start_date' => '2026-08-03',
                'end_date' => '2026-08-10',
                'description' => 'Posko Kesiapsiagaan dan Pantauan KPRS Pantai menghadapi lonjakan pengunjung akhir pekan dan potensi ombak selatan tinggi.',
                'equipment_used' => '1 Unit Perahu Karet Engine, Life Jacket 30 Pcs, Ring Buoy, Radio HT Repiter, Tenda Posko',
                'victims_saved' => 0,
                'victims_injured' => 0,
                'victims_deceased' => 0,
                'victims_missing' => 0,
            ],
            [
                'code' => 'SAR-202607-003',
                'title' => 'Operasi Evakuasi Korban Longsor Tebing Cisolok',
                'type' => 'Operasi SAR',
                'location' => 'Kecamatan Cisolok, Sukabumi, Jawa Barat',
                'latitude' => -6.927700,
                'longitude' => 106.930000,
                'status' => 'Evakuasi Selesai',
                'severity_level' => 'Tinggi',
                'commander_name' => 'Farhan Saputra (Danru Rescue MKT)',
                'personnel_count' => 45,
                'potensi_sar' => 'Basarnas Jabar, BPBD Sukabumi, Tagana, Tim Rescue MKT, TNI/Polri',
                'deployed_teams' => 'Tim Evakuasi Lapangan MKT (15 Personel), Tim Anjing Pelacak K-9 Basarnas (10 Personel), Tim Operator Alat Berat (5 Personel), Tagana Sukabumi (15 Personel)',
                'standby_teams' => 'Tim Trauma Healing MKT, Tim DVI Polda Jabar',
                'start_date' => '2026-07-12',
                'end_date' => '2026-07-18',
                'description' => 'Pencarian dan pembersihan material tanah longsor menimpa pemukiman warga. Seluruh titik musibah telah dibersihkan dan korban berhasil dievakuasi.',
                'equipment_used' => 'Alat Berat Excavator, Alat Ekstrikasi, Anjing K-9, Alkon Penyemprot Lumpur, Ambulance MKT',
                'victims_saved' => 32,
                'victims_injured' => 8,
                'victims_deceased' => 3,
                'victims_missing' => 0,
            ],
            [
                'code' => 'SAR-202608-004',
                'title' => 'Siaga SAR Kesiapsiagaan Cuaca Ekstrem & Angin Kencang Makassar',
                'type' => 'Siaga SAR',
                'location' => 'Posko Induk Yayasan MKT, Panakkukang, Kota Makassar',
                'latitude' => -5.147665,
                'longitude' => 119.432731,
                'status' => 'Siaga SAR',
                'severity_level' => 'Tinggi',
                'commander_name' => 'Dr. Syamsul Rizal / Tim Medis & Rescue MKT',
                'personnel_count' => 20,
                'potensi_sar' => 'Basarnas Sulsel, BPBD Kota Makassar, PMI Makassar, MKT Rescue',
                'deployed_teams' => 'Tim Peringatan Dini & Posko MKT (8 Personel), Tim Tree Cutting / Chainsaw (6 Personel), Tim Medis Mobile (6 Personel)',
                'standby_teams' => 'Tim Swift Water Rescue MKT, Fleet Perahu Karet MKT 727',
                'start_date' => '2026-08-04',
                'end_date' => null,
                'description' => 'Monitoring peringatan dini BMKG terkait potensi gelombang tinggi dan cuaca ekstrem wilayah pesisir Makassar & Gowa. Kesiapan armada penolong 24 jam.',
                'equipment_used' => 'Mobil Rescue 4x4, Chainsaw Pemotong Pohon Tumbang, Tenda Darurat, Logistik Sembako & Obat',
                'victims_saved' => 0,
                'victims_injured' => 0,
                'victims_deceased' => 0,
                'victims_missing' => 0,
            ],
        ];

        foreach ($operations as $op) {
            SarOperation::updateOrCreate(
                ['code' => $op['code']],
                $op
            );
        }
    }
}
