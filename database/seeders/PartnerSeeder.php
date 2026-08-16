<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partner;
use App\Models\Volunteer;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            [
                'code' => 'MTR-PMI-001',
                'name' => 'Palang Merah Indonesia (PMI) Kota Makassar',
                'category' => 'PMI',
                'pic_name' => 'Dr. H. Syamsul Rizal, S.E., M.Si.',
                'pic_phone' => '08114455667',
                'pic_email' => 'syamsul.rizal@pmi-makassar.or.id',
                'phone' => '0411-855123',
                'email' => 'info@pmi-makassar.or.id',
                'address' => 'Jl. Kandea No. 16, Kota Makassar, Sulawesi Selatan',
                'status' => 'Aktif',
                'mou_number' => 'MoU/MKT-PMI/2025/001',
                'personnel_count' => 120,
                'description' => 'Mitra penyedia stok darah darurat, pelayanan Ambulans, dan Korps Sukarela (KSR) PMI.',
            ],
            [
                'code' => 'MTR-BAS-001',
                'name' => 'BASARNAS - Kantor SAR Kelas A Makassar',
                'category' => 'Basarnas',
                'pic_name' => 'Mexianus Bekabel, S.Sos., M.M.',
                'pic_phone' => '08129988770',
                'pic_email' => 'mexianus@basarnas.go.id',
                'phone' => '0411-554433',
                'email' => 'kansar.makassar@basarnas.go.id',
                'address' => 'Jl. Bandara Hasanuddin No. 1, Maros - Makassar',
                'status' => 'Aktif',
                'mou_number' => 'MoU/MKT-BASARNAS/2025/004',
                'personnel_count' => 85,
                'description' => 'Mitra utama Komando Search and Rescue, pelatihan teknik evakuasi darat/air, dan koordinasi emergency alert.',
            ],
            [
                'code' => 'MTR-BPBD-001',
                'name' => 'BPBD Provinsi Sulawesi Selatan',
                'category' => 'BPBD',
                'pic_name' => 'Drs. A. Hasim, M.Si.',
                'pic_phone' => '08137766554',
                'pic_email' => 'hasim@bpbd.sulselprov.go.id',
                'phone' => '0411-456789',
                'email' => 'pusdalops@bpbd.sulselprov.go.id',
                'address' => 'Jl. Perintis Kemerdekaan Km. 11, Makassar',
                'status' => 'Aktif',
                'mou_number' => 'MoU/MKT-BPBD/2025/002',
                'personnel_count' => 150,
                'description' => 'Pusat Pengendalian Operasi (Pusdalops) kebencanaan daerah, mitigasi prabencana, & logistik darurat.',
            ],
            [
                'code' => 'MTR-RS-001',
                'name' => 'RSUP Dr. Wahidin Sudirohusodo (Tim Medis Bencana)',
                'category' => 'Rumah Sakit',
                'pic_name' => 'dr. Andi Nurhayati, Sp.An-KIC',
                'pic_phone' => '08123344556',
                'pic_email' => 'medis.emergency@rs-wahidin.co.id',
                'phone' => '0411-584058',
                'email' => 'humas@rs-wahidin.co.id',
                'address' => 'Jl. Perintis Kemerdekaan Km. 11 Tamalanrea, Makassar',
                'status' => 'Aktif',
                'mou_number' => 'MoU/MKT-RSW/2025/007',
                'personnel_count' => 45,
                'description' => 'Rumah Sakit Rujukan Bencana, penyedia dokter spesialis trauma, Rumah Sakit Lapangan & Mobile Clinic.',
            ],
            [
                'code' => 'MTR-RSC-001',
                'name' => 'Tim Rescue 727 Terpadu MKT',
                'category' => 'Tim Rescue',
                'pic_name' => 'Kapten (Purn) Hendra Suwandi',
                'pic_phone' => '08128899112',
                'pic_email' => 'rescue727@mkt.or.id',
                'phone' => '0812-3456-7890',
                'email' => 'rescue@mkt.or.id',
                'address' => 'Perumahan Insignia Oasis Blok B1-11 No 7, Makassar',
                'status' => 'Siaga Bencana',
                'mou_number' => 'INTERNAL-MKT-RESCUE-01',
                'personnel_count' => 60,
                'description' => 'Unit Reaksi Cepat (URC) evakuasi korban bencana darat, air (water rescue), dan vertical rescue.',
            ],
        ];

        foreach ($partners as $pData) {
            $partner = Partner::updateOrCreate(['code' => $pData['code']], $pData);

            // Assign existing volunteers to matching partners
            if ($pData['category'] === 'PMI') {
                Volunteer::where('role', 'Donor Darah')->update(['partner_id' => $partner->id]);
            } elseif ($pData['category'] === 'Basarnas' || $pData['category'] === 'Tim Rescue') {
                Volunteer::where('role', 'Tim Rescue')->update(['partner_id' => $partner->id]);
            }
        }
    }
}
