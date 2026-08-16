<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MktProfile;
use App\Models\Volunteer;
use App\Models\Donor;
use App\Models\Donation;
use App\Models\Logistic;
use App\Models\LogisticTransaction;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\DisasterEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Users for each Role
        $users = [
            ['name' => 'Webmaster Utama', 'email' => 'webmaster@mkt.or.id', 'role' => 'webmaster', 'password' => Hash::make('password123')],
            ['name' => 'Administrator MKT', 'email' => 'administrator@mkt.or.id', 'role' => 'administrator', 'password' => Hash::make('password123')],
            ['name' => 'Finance & Keuangan MKT', 'email' => 'finance@mkt.or.id', 'role' => 'finance', 'password' => Hash::make('password123')],
            ['name' => 'Staff Operasional MKT', 'email' => 'staff@mkt.or.id', 'role' => 'staff', 'password' => Hash::make('password123')],
            ['name' => 'Mitra Basarnas & BPBD', 'email' => 'mitra@mkt.or.id', 'role' => 'mitra', 'password' => Hash::make('password123')],
            ['name' => 'Tim Rescue & Relawan', 'email' => 'relawan@mkt.or.id', 'role' => 'relawan', 'password' => Hash::make('password123')],
            ['name' => 'Donatur Utama Kemanusiaan', 'email' => 'donatur@mkt.or.id', 'role' => 'donatur', 'password' => Hash::make('password123')],
            ['name' => 'Dokter & Tim Medis MKT', 'email' => 'medis@mkt.or.id', 'role' => 'medis', 'password' => Hash::make('password123')],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'password' => $u['password'],
                    'email_verified_at' => now(),
                ]
            );
        }

        // 2. Seed MKT Profile
        MktProfile::create([
            'name' => 'Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu)',
            'description' => 'Yayasan amal sosial kemanusiaan yang berfokus pada penghimpunan donasi publik, penggerak relawan donor darah, dan respon cepat tim rescue dalam penanggulangan bencana.',
            'address' => 'Perumahan Insignia Oasis Blok B1-11 No 7, Kota Makassar, Sulawesi Selatan',
            'phone' => '+62 812-3456-7890',
            'email' => 'info@mkt.or.id',
            'vision' => 'Menjadi lembaga kemanusiaan terdepan dalam membangun ekosistem kebencanaan yang tanggap, tangguh, dan inklusif di Indonesia.',
            'mission' => "1. Menyelenggarakan penghimpunan donasi publik yang transparan dan akuntabel.\n2. Menggerakkan jaringan relawan donor darah untuk kebutuhan darurat medis.\n3. Membentuk tim rescue yang terampil, cepat tanggap, dan siap siaga.\n4. Mengedukasi masyarakat terkait kesiapsiagaan bencana prabencana, tanggap darurat, dan pascabencana.",
            'logo' => null,
            'bank_accounts' => [
                ['bank' => 'Bank Syariah Indonesia (BSI)', 'account_number' => '777-888-999-1', 'account_name' => 'Yayasan MKT - Donasi Kemanusiaan'],
                ['bank' => 'Bank Mandiri', 'account_number' => '123-456-789-0', 'account_name' => 'Yayasan MKT - Operasional Rescue'],
                ['bank' => 'Bank Central Asia (BCA)', 'account_number' => '888-012-345-6', 'account_name' => 'Yayasan MKT - Donasi Tanggap Darurat']
            ]
        ]);

        // 3. Seed Volunteers
        $volunteers = [
            ['name' => 'Ahmad Roni', 'email' => 'ahmad.roni@gmail.com', 'phone' => '+62 812-9988-7711', 'address' => 'Tangerang', 'blood_type' => 'O', 'role' => 'Tim Rescue', 'status' => 'Aktif', 'registered_at' => '2025-01-10', 'notes' => 'Sertifikasi Search and Rescue (SAR) tingkat nasional.'],
            ['name' => 'Budi Cahyono', 'email' => 'budi.c@gmail.com', 'phone' => '+62 813-1122-3344', 'address' => 'Depok', 'blood_type' => 'A', 'role' => 'Tim Rescue', 'status' => 'Aktif', 'registered_at' => '2025-02-15', 'notes' => 'Keahlian panjat tebing dan navigasi darat.'],
            ['name' => 'Siti Aminah', 'email' => 'siti.aminah@yahoo.com', 'phone' => '+62 856-7788-9900', 'address' => 'Jakarta Timur', 'blood_type' => 'B', 'role' => 'Donor Darah', 'status' => 'Aktif', 'registered_at' => '2025-03-01', 'notes' => 'Pendonor rutin 3 bulan sekali.'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@gmail.com', 'phone' => '+62 819-2233-4455', 'address' => 'Bekasi', 'blood_type' => 'AB', 'role' => 'Donor Darah', 'status' => 'Aktif', 'registered_at' => '2025-03-12', 'notes' => 'Anggota KSR PMI.'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra.w@gmail.com', 'phone' => '+62 812-4455-6677', 'address' => 'Jakarta Selatan', 'blood_type' => 'O', 'role' => 'Relawan Umum', 'status' => 'Aktif', 'registered_at' => '2025-04-05', 'notes' => 'Fokus di bagian penyaluran logistik & dapur umum.'],
            ['name' => 'Farhan Saputra', 'email' => 'farhan.s@gmail.com', 'phone' => '+62 821-3344-5566', 'address' => 'Bogor', 'blood_type' => 'B', 'role' => 'Tim Rescue', 'status' => 'Aktif', 'registered_at' => '2025-05-20', 'notes' => 'Spesialis medis darurat (First Responder).'],
            ['name' => 'Rina Marlina', 'email' => 'rina.m@gmail.com', 'phone' => '+62 878-1122-8899', 'address' => 'Tangerang Selatan', 'blood_type' => 'A', 'role' => 'Relawan Umum', 'status' => 'Aktif', 'registered_at' => '2025-06-01', 'notes' => 'Dukungan psikososial anak-anak korban bencana.'],
            ['name' => 'Taufik Hidayat', 'email' => 'taufik.h@gmail.com', 'phone' => '+62 813-5566-7788', 'address' => 'Jakarta Barat', 'blood_type' => 'O', 'role' => 'Donor Darah', 'status' => 'Tidak Aktif', 'registered_at' => '2025-01-20', 'notes' => 'Sedang tugas belajar ke luar kota.'],
        ];
        foreach ($volunteers as $v) {
            Volunteer::updateOrCreate(
                ['email' => $v['email']],
                $v
            );
        }

        // 4. Seed Donors
        $donors = [
            ['name' => 'H. Muhammad Yusuf', 'email' => 'yusuf.haj@gmail.com', 'phone' => '+62 811-1234-567', 'address' => 'Menteng, Jakarta Pusat', 'type' => 'Personal', 'status' => 'Aktif'],
            ['name' => 'PT. Mega Bintang Sejahtera', 'email' => 'csr@megabintang.co.id', 'phone' => '+62 21-555-1234', 'address' => 'Sudirman CBD, Jakarta', 'type' => 'Lembaga', 'status' => 'Aktif'],
            ['name' => 'Yayasan Bakti Bersama', 'email' => 'contact@baktibersama.org', 'phone' => '+62 21-777-9999', 'address' => 'Kemang, Jakarta Selatan', 'type' => 'Lembaga', 'status' => 'Aktif'],
            ['name' => 'Lina Marlina', 'email' => 'lina.marlina@hotmail.com', 'phone' => '+62 813-9876-543', 'address' => 'Bandung', 'type' => 'Personal', 'status' => 'Aktif'],
            ['name' => 'Rian Ardianto', 'email' => 'rian.a@gmail.com', 'phone' => '+62 856-1122-334', 'address' => 'Surabaya', 'type' => 'Personal', 'status' => 'Aktif'],
        ];
        $createdDonors = [];
        foreach ($donors as $d) {
            $createdDonors[] = Donor::updateOrCreate(
                ['email' => $d['email']],
                $d
            );
        }

        // 5. Seed Donations
        $donations = [
            ['donor_id' => 1, 'amount' => 5000000.00, 'donation_date' => '2026-07-01', 'payment_method' => 'Bank Transfer (BSI)', 'status' => 'Sukses', 'description' => 'Sedekah awal bulan untuk bencana banjir', 'reference_number' => 'TX-20260701-01'],
            ['donor_id' => 2, 'amount' => 50000000.00, 'donation_date' => '2026-07-05', 'payment_method' => 'Bank Transfer (Mandiri)', 'status' => 'Sukses', 'description' => 'Dana CSR Peduli Gempa', 'reference_number' => 'TX-20260705-02'],
            ['donor_id' => 3, 'amount' => 25000000.00, 'donation_date' => '2026-07-08', 'payment_method' => 'Bank Transfer (BCA)', 'status' => 'Sukses', 'description' => 'Donasi Program Dapur Umum Rescue', 'reference_number' => 'TX-20260708-03'],
            ['donor_id' => 4, 'amount' => 1500000.00, 'donation_date' => '2026-07-10', 'payment_method' => 'E-Wallet (GoPay)', 'status' => 'Sukses', 'description' => 'Infak kemanusiaan umum', 'reference_number' => 'TX-20260710-04'],
            ['donor_id' => 5, 'amount' => 500000.00, 'donation_date' => '2026-07-11', 'payment_method' => 'E-Wallet (OVO)', 'status' => 'Pending', 'description' => 'Donasi peduli longsor', 'reference_number' => 'TX-20260711-05'],
            ['donor_id' => 1, 'amount' => 10000000.00, 'donation_date' => '2026-07-15', 'payment_method' => 'Bank Transfer (BSI)', 'status' => 'Sukses', 'description' => 'Donasi untuk pengadaan alat rescue air', 'reference_number' => 'TX-20260715-06'],
            ['donor_id' => null, 'amount' => 250000.00, 'donation_date' => '2026-07-16', 'payment_method' => 'QRIS (ShopeePay)', 'status' => 'Sukses', 'description' => 'Hamba Allah - Donasi Bencana Alam', 'reference_number' => 'TX-20260716-07'],
        ];
        foreach ($donations as $dn) {
            Donation::create($dn);
        }

        // 6. Seed Logistics
        $logistics = [
            ['item_name' => 'Tenda Pengungsian Darurat', 'category' => 'Rescue Equipment', 'quantity' => 15, 'unit' => 'Unit', 'description' => 'Tenda pleton ukuran 6x12m lengkap dengan tiang.'],
            ['item_name' => 'Beras Pandan Wangi 5kg', 'category' => 'Makanan', 'quantity' => 120, 'unit' => 'Pack', 'description' => 'Beras konsumsi dapur umum atau distribusi langsung.'],
            ['item_name' => 'Mie Instan (Kardus)', 'category' => 'Makanan', 'quantity' => 85, 'unit' => 'Kardus', 'description' => 'Campuran rasa soto dan goreng.'],
            ['item_name' => 'Selimut Wol Tebal', 'category' => 'Pakaian', 'quantity' => 250, 'unit' => 'Pcs', 'description' => 'Selimut hangat untuk pengungsi.'],
            ['item_name' => 'Kotak P3K Lengkap', 'category' => 'Obat-obatan', 'quantity' => 42, 'unit' => 'Box', 'description' => 'Berisi antiseptik, perban, plester, minyak kayu putih.'],
            ['item_name' => 'Air Mineral 600ml (Kardus)', 'category' => 'Makanan', 'quantity' => 150, 'unit' => 'Kardus', 'description' => 'Air minum kemasan botol isi 24 pcs.'],
            ['item_name' => 'Perahu Karet Rescue', 'category' => 'Rescue Equipment', 'quantity' => 4, 'unit' => 'Unit', 'description' => 'Perahu karet kapasitas 6 orang lengkap dengan dayung & mesin tempel.'],
        ];
        $createdLogistics = [];
        foreach ($logistics as $l) {
            $createdLogistics[] = Logistic::create($l);
        }

        // 7. Seed Logistic Transactions
        $logisticTransactions = [
            ['logistic_id' => 1, 'type' => 'Masuk', 'quantity' => 10, 'transaction_date' => '2026-07-02', 'recipient_or_donor' => 'CSR PT. Mega Bintang Sejahtera', 'notes' => 'Donasi perlengkapan bencana.'],
            ['logistic_id' => 2, 'type' => 'Masuk', 'quantity' => 150, 'transaction_date' => '2026-07-03', 'recipient_or_donor' => 'H. Muhammad Yusuf', 'notes' => 'Beras untuk dapur umum.'],
            ['logistic_id' => 2, 'type' => 'Keluar', 'quantity' => 30, 'transaction_date' => '2026-07-06', 'recipient_or_donor' => 'Posko Banjir Ciliwung', 'notes' => 'Penyaluran dapur umum banjir Jakarta.'],
            ['logistic_id' => 3, 'type' => 'Keluar', 'quantity' => 20, 'transaction_date' => '2026-07-06', 'recipient_or_donor' => 'Posko Banjir Ciliwung', 'notes' => 'Penyaluran dapur umum banjir Jakarta.'],
            ['logistic_id' => 4, 'type' => 'Keluar', 'quantity' => 50, 'transaction_date' => '2026-07-07', 'recipient_or_donor' => 'Korban Longsor Bogor', 'notes' => 'Penyaluran selimut hangat.'],
            ['logistic_id' => 5, 'type' => 'Masuk', 'quantity' => 50, 'transaction_date' => '2026-07-09', 'recipient_or_donor' => 'Kementerian Kesehatan', 'notes' => 'Bantuan obat-obatan darurat.'],
            ['logistic_id' => 5, 'type' => 'Keluar', 'quantity' => 8, 'transaction_date' => '2026-07-12', 'recipient_or_donor' => 'Tim Medis Rescue MKT', 'notes' => 'Operasi tanggap darurat longsor.'],
        ];
        foreach ($logisticTransactions as $lt) {
            LogisticTransaction::create($lt);
            
            // Adjust current stock quantity in Logistic table based on transaction
            $logistic = Logistic::find($lt['logistic_id']);
            if ($lt['type'] == 'Masuk') {
                $logistic->quantity += $lt['quantity'];
            } else {
                $logistic->quantity -= $lt['quantity'];
            }
            $logistic->save();
        }

        // 8. Seed Accounts (Chart of Accounts for Accounting System)
        $accounts = [
            // Assets (1000 - 1999)
            ['code' => '1001', 'name' => 'Kas Utama Yayasan', 'type' => 'Asset'],
            ['code' => '1002', 'name' => 'Bank Mandiri - Rekening Rescue', 'type' => 'Asset'],
            ['code' => '1003', 'name' => 'Bank Syariah Indonesia - Rekening Donasi', 'type' => 'Asset'],
            ['code' => '1004', 'name' => 'Bank BCA - Rekening Tanggap Bencana', 'type' => 'Asset'],
            
            // Liabilities (2000 - 2999)
            ['code' => '2001', 'name' => 'Hutang Pembelian Logistik', 'type' => 'Liability'],
            
            // Equity (3000 - 3999)
            ['code' => '3001', 'name' => 'Saldo Awal Modal Yayasan', 'type' => 'Equity'],
            
            // Revenues (4000 - 4999)
            ['code' => '4001', 'name' => 'Pendapatan Donasi Publik', 'type' => 'Revenue'],
            ['code' => '4002', 'name' => 'Pendapatan CSR Lembaga/Corporate', 'type' => 'Revenue'],
            
            // Expenses (5000 - 5999)
            ['code' => '5001', 'name' => 'Beban Logistik & Dapur Umum Bencana', 'type' => 'Expense'],
            ['code' => '5002', 'name' => 'Beban Operasional Tim Rescue', 'type' => 'Expense'],
            ['code' => '5003', 'name' => 'Beban Promosi & Kampanye Amal', 'type' => 'Expense'],
            ['code' => '5004', 'name' => 'Beban Gaji & Operasional Kantor', 'type' => 'Expense'],
        ];
        foreach ($accounts as $a) {
            Account::updateOrCreate(
                ['code' => $a['code']],
                $a
            );
        }

        // 9. Seed Financial Journal (Jurnal & Laporan)
        // Setup initial balance, donation income, and operational expenses
        
        // Transaction A: Saldo Awal Modal Yayasan (100M IDR)
        $je1 = JournalEntry::create([
            'entry_date' => '2026-06-01',
            'description' => 'Saldo awal modal pendirian Yayasan MKT',
            'reference_number' => 'JE-202606-001'
        ]);
        JournalItem::create(['journal_entry_id' => $je1->id, 'account_id' => 1, 'type' => 'Debit', 'amount' => 100000000.00]); // Kas Utama
        JournalItem::create(['journal_entry_id' => $je1->id, 'account_id' => 6, 'type' => 'Credit', 'amount' => 100000000.00]); // Saldo Awal Modal

        // Transaction B: Penerimaan Donasi CSR PT Mega Bintang Sejahtera (50M IDR)
        $je2 = JournalEntry::create([
            'entry_date' => '2026-07-05',
            'description' => 'Penerimaan Donasi CSR PT Mega Bintang Sejahtera',
            'reference_number' => 'JE-202607-002'
        ]);
        JournalItem::create(['journal_entry_id' => $je2->id, 'account_id' => 2, 'type' => 'Debit', 'amount' => 50000000.00]); // Bank Mandiri
        JournalItem::create(['journal_entry_id' => $je2->id, 'account_id' => 8, 'type' => 'Credit', 'amount' => 50000000.00]); // Pendapatan CSR

        // Transaction C: Pembelian logistik dapur umum (beras, mie dll) untuk banjir (15M IDR)
        $je3 = JournalEntry::create([
            'entry_date' => '2026-07-06',
            'description' => 'Pengeluaran kas pembelian beras & mie instan bencana banjir Jakarta',
            'reference_number' => 'JE-202607-003'
        ]);
        JournalItem::create(['journal_entry_id' => $je3->id, 'account_id' => 9, 'type' => 'Debit', 'amount' => 15000000.00]); // Beban Logistik
        JournalItem::create(['journal_entry_id' => $je3->id, 'account_id' => 1, 'type' => 'Credit', 'amount' => 15000000.00]); // Kas Utama

        // Transaction D: Pembelian bahan bakar & sewa perlengkapan tanggap darurat Rescue (5.5M IDR)
        $je4 = JournalEntry::create([
            'entry_date' => '2026-07-07',
            'description' => 'Pengeluaran operasional solar perahu & evakuasi rescue banjir',
            'reference_number' => 'JE-202607-004'
        ]);
        JournalItem::create(['journal_entry_id' => $je4->id, 'account_id' => 10, 'type' => 'Debit', 'amount' => 5500000.00]); // Beban Rescue
        JournalItem::create(['journal_entry_id' => $je4->id, 'account_id' => 1, 'type' => 'Credit', 'amount' => 5500000.00]); // Kas Utama

        // Transaction E: Penerimaan Donasi H. Muhammad Yusuf (5M IDR)
        $je5 = JournalEntry::create([
            'entry_date' => '2026-07-01',
            'description' => 'Penerimaan Donasi H. Muhammad Yusuf via BSI',
            'reference_number' => 'JE-202607-001'
        ]);
        JournalItem::create(['journal_entry_id' => $je5->id, 'account_id' => 3, 'type' => 'Debit', 'amount' => 5000000.00]); // Bank BSI
        JournalItem::create(['journal_entry_id' => $je5->id, 'account_id' => 7, 'type' => 'Credit', 'amount' => 5000000.00]); // Pendapatan Donasi Publik

        // 10. Seed Disaster Events (Peta Operasi)
        DisasterEvent::create([
            'title' => 'Banjir Luapan Sungai Ciliwung',
            'category' => 'Banjir',
            'location' => 'Kampung Melayu, Jakarta Timur',
            'latitude' => -6.2244,
            'longitude' => 106.8622,
            'severity' => 'Tinggi',
            'status' => 'Evakuasi',
            'description' => 'Luapan sungai Ciliwung akibat curah hujan tinggi di hulu. Air mencapai ketinggian 1.5 meter, mengevakuasi warga bantaran sungai.',
            'rescue_team_leader' => 'Ahmad Roni',
            'victim_count' => 150,
            'date_occurred' => '2026-07-10',
        ]);

        DisasterEvent::create([
            'title' => 'Tanah Longsor Bukit Cisolok',
            'category' => 'Longsor',
            'location' => 'Cisolok, Sukabumi, Jawa Barat',
            'latitude' => -6.9277,
            'longitude' => 106.9300,
            'severity' => 'Darurat',
            'status' => 'Evakuasi',
            'description' => 'Tebing setinggi 30 meter longsor menimpa akses jalan utama dan 4 rumah warga akibat hujan deras intensitas tinggi.',
            'rescue_team_leader' => 'Farhan Saputra',
            'victim_count' => 35,
            'date_occurred' => '2026-07-12',
        ]);

        DisasterEvent::create([
            'title' => 'Kebakaran Pemukiman Padat Tambora',
            'category' => 'Kebakaran',
            'location' => 'Tambora, Jakarta Barat',
            'latitude' => -6.1485,
            'longitude' => 106.8015,
            'severity' => 'Sedang',
            'status' => 'Pemulihan',
            'description' => 'Kebakaran melanda area pemukiman padat penduduk. Api berhasil dipadamkan setelah mengerahkan 15 unit damkar. Memasuki tahap trauma healing.',
            'rescue_team_leader' => 'Budi Cahyono',
            'victim_count' => 85,
            'date_occurred' => '2026-07-14',
        ]);

        DisasterEvent::create([
            'title' => 'Kesiapsiagaan Aktivitas Gunung Merapi',
            'category' => 'Erupsi',
            'location' => 'Sleman, D.I. Yogyakarta',
            'latitude' => -7.5407,
            'longitude' => 110.4457,
            'severity' => 'Sedang',
            'status' => 'Siaga',
            'description' => 'Gunung Merapi mengalami peningkatan guguran lava pijar. Koordinasi posko siaga evakuasi radius 5km dari puncak.',
            'rescue_team_leader' => 'Hendra Wijaya',
            'victim_count' => 0,
            'date_occurred' => '2026-07-16',
        ]);

        $this->call(SarOperationSeeder::class);
        $this->call(SarParticipationSeeder::class);

        // 11. Seed News Articles
        $newsList = [
            [
                'title' => 'Banjir Bandang Terjang Pemukiman Lereng Gunung Makassar & Gowa',
                'category' => 'Evakuasi',
                'author' => 'Humas Tim Rescue MKT',
                'image_url' => 'https://images.unsplash.com/photo-1547683905-f686c993aae5?w=600&h=400&fit=crop',
                'content' => 'Tim Rescue Gabungan MKT bergerak cepat menerjunkan 3 perahu karet untuk evakuasi lansia dan balita di pemukiman terdampak banjir akibat luapan sungai. Posko dapur umum telah didirikan di Insignia Oasis.',
                'published_at' => '2026-07-30',
            ],
            [
                'title' => 'Penyaluran Logistik Sembako & Selimut Tahap II Korban Gempa',
                'category' => 'Logistik',
                'author' => 'Staff Operasional MKT',
                'image_url' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&h=400&fit=crop',
                'content' => 'Sebanyak 500 paket logistik berisi beras, mie instan, obat-obatan, dan selimut hangat telah berhasil disalurkan langsung oleh armada posko MKT ke daerah terisolir.',
                'published_at' => '2026-07-28',
            ],
            [
                'title' => 'Aksi Donor Darah Massal Kolaborasi Yayasan MKT & PMI Sulsel',
                'category' => 'Kesehatan',
                'author' => 'Tim Medis MKT',
                'image_url' => 'https://images.unsplash.com/photo-1615461066841-6116ecdccd04?w=600&h=400&fit=crop',
                'content' => 'Guna mengantisipasi kelangkaan stok darah saat tanggap darurat bencana, MKT menyelenggarakan aksi donor darah yang berhasil menghimpun 120 kantong darah segar.',
                'published_at' => '2026-07-25',
            ],
        ];

        foreach ($newsList as $n) {
            \App\Models\News::updateOrCreate(
                ['title' => $n['title']],
                $n
            );
        }
    }
}
