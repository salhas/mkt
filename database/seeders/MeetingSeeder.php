<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;

class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $userId = $user ? $user->id : null;

        $meetings = [
            [
                'title' => 'Rapat Koordinasi Penanganan Tanggap Darurat Bencana Banjir Bandang',
                'meeting_date' => Carbon::now()->subDays(2)->setTime(9, 30),
                'location' => 'Ruang Rapat Utama Posko Utama MKT & Zoom Online',
                'category' => 'Evaluasi Bencana',
                'leader' => 'Ir. H. Ahmad Fauzi (Ketua Umum MKT)',
                'notewriter' => 'Siti Rahmawati, S.Kom',
                'attendees' => ['Ir. H. Ahmad Fauzi', 'Siti Rahmawati', 'Dr. Hendra Wijaya (Rescuer)', 'Budi Santoso (Logistik)', 'Maya Indah (Keuangan)', 'Tim Relawan Lapangan'],
                'agenda' => "1. Evaluasi penyaluran bantuan di 3 titik posko banjir.\n2. Alokasi armada ambulans rescue dan personel medis.\n3. Rencana distribusi 500 paket sembako & perlengkapan hygiene kit.",
                'summary' => "Rapat menyetujui percepatan pengiriman logistik medis ke Posko Wilayah B. Tim Rescuer disiagakan 24/7. Dana darurat dialokasikan sebesar Rp 45.000.000 untuk pengadaan paket pangan keluarga rentan.",
                'action_items' => [
                    ['task' => 'Pengadaan 500 Hygiene Kit & Sembako', 'pic' => 'Budi Santoso', 'deadline' => Carbon::now()->addDays(2)->format('Y-m-d'), 'completed' => true],
                    ['task' => 'Penyiapan 2 unit Ambulans Rescue Tanggap Bencana', 'pic' => 'Dr. Hendra Wijaya', 'deadline' => Carbon::now()->addDays(1)->format('Y-m-d'), 'completed' => true],
                    ['task' => 'Laporan Pertanggungjawaban Pencairan Dana Darurat', 'pic' => 'Maya Indah', 'deadline' => Carbon::now()->addDays(5)->format('Y-m-d'), 'completed' => false],
                ],
                'status' => 'Selesai',
                'created_by' => $userId,
            ],
            [
                'title' => 'Rapat Konsolidasi Tim Relawan Donor Darah & Perekrutan Anggota Baru',
                'meeting_date' => Carbon::now()->subDays(7)->setTime(14, 0),
                'location' => 'Aula Serbaguna Gedung MKT Indonesia',
                'category' => 'Rapat Koordinasi',
                'leader' => 'Dr. Hendra Wijaya',
                'notewriter' => 'Rian Pratama',
                'attendees' => ['Dr. Hendra Wijaya', 'Rian Pratama', 'Doni Kurniawan', 'Anita Permata', 'Perwakilan PMI Kota'],
                'agenda' => "1. Penjadwalan event Donor Darah Massal Bulan Depan.\n2. Target pencapaian 300 kantong darah.\n3. Digitalisasi pendaftaran calon relawan via web MKT.",
                'summary' => "PMI Kota menyetujui pengiriman 10 bed donor dan 4 tenaga medis pendamping. Sistem pendaftaran online relawan donor darah telah diluncurkan di portal MKT.",
                'action_items' => [
                    ['task' => 'Surat Koordinasi Resmi ke PMI & Dinas Kesehatan', 'pic' => 'Rian Pratama', 'deadline' => Carbon::now()->subDays(3)->format('Y-m-d'), 'completed' => true],
                    ['task' => 'Publikasi Flyer Pendaftaran Online di Social Media', 'pic' => 'Anita Permata', 'deadline' => Carbon::now()->addDays(3)->format('Y-m-d'), 'completed' => false],
                ],
                'status' => 'Selesai',
                'created_by' => $userId,
            ],
            [
                'title' => 'Rapat Pleno Strategi Penghimpunan Donasi Publik & Kemitraan Korporat',
                'meeting_date' => Carbon::now()->subDays(14)->setTime(10, 0),
                'location' => 'Ruang Boardroom Lt. 2',
                'category' => 'Sosialisasi Donasi',
                'leader' => 'Maya Indah (Direktur Fundraiser)',
                'notewriter' => 'Rina Amalia',
                'attendees' => ['Maya Indah', 'Rina Amalia', 'Dedi Kurnia (Partnership)', 'Hendra Setiawan (IT Support)'],
                'agenda' => "1. Integrasi payment gateway otomatis untuk portal Donasi.\n2. Program Orang Tua Asuh Anak Bencana.\n3. Proposal CSR untuk 5 Perusahaan BUMN.",
                'summary' => "Draf proposal CSR disetujui. QRIS Statis dan Transfer Mandiri/BCA telah terintegrasi dalam sistem pelaporan donasi harian.",
                'action_items' => [
                    ['task' => 'Follow up proposal CSR BUMN Pertamina & Telkom', 'pic' => 'Dedi Kurnia', 'deadline' => Carbon::now()->addDays(7)->format('Y-m-d'), 'completed' => false],
                    ['task' => 'Auditing Sistem Keuangan Transparan Donasi Publik', 'pic' => 'Maya Indah', 'deadline' => Carbon::now()->addDays(10)->format('Y-m-d'), 'completed' => false],
                ],
                'status' => 'Selesai',
                'created_by' => $userId,
            ],
            [
                'title' => 'Rapat Evaluasi Internal Manajemen & Review Kinerja Triwulan',
                'meeting_date' => Carbon::now()->addDays(3)->setTime(13, 30),
                'location' => 'Google Meet (Virtual)',
                'category' => 'Internal Tim',
                'leader' => 'Ir. H. Ahmad Fauzi',
                'notewriter' => 'Siti Rahmawati',
                'attendees' => ['Pengurus Harian MKT', 'Kepala Divisi Operasional', 'Koordinator Daerah'],
                'agenda' => "1. Paparan kinerja divisi Operasional, Logistik, dan Keuangan.\n2. Pembukaan cabang posko relawan di Wilayah Pesisir.\n3. Pelatihan Simulasi Tanggap Bencana Gempa.",
                'summary' => "Agenda rapat telah diterbitkan. Seluruh kadiv diharapkan menyiapkan materi slide persentase kinerja sebelum H-1.",
                'action_items' => [
                    ['task' => 'Pengumpulan slide laporan per divisi', 'pic' => 'Semua Kadiv', 'deadline' => Carbon::now()->addDays(2)->format('Y-m-d'), 'completed' => false],
                ],
                'status' => 'Terjadwal',
                'created_by' => $userId,
            ]
        ];

        foreach ($meetings as $meetingData) {
            Meeting::create($meetingData);
        }
    }
}
