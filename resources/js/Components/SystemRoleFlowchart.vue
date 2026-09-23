<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

// Authorization check: strictly webmaster and administrator
const isAuthorized = computed(() => {
    return currentUser.value && ['webmaster', 'administrator'].includes(currentUser.value.role);
});

// View modes: 'tree' (Bagan Pohon Hierarki), 'global' (End-to-End), 'roles' (Detail per Role), 'matrix' (Matriks Hak Akses)
const activeView = ref('tree');

// Active selected role for deep-dive
const selectedRoleKey = ref('webmaster');

// Search query for filtering workflows & tree
const searchQuery = ref('');

// Step detail expansion in roles view
const expandedStepId = ref(null);

const toggleStep = (id) => {
    expandedStepId.value = expandedStepId.value === id ? null : id;
};

// Tree Branch Collapsed States
const collapsedPillars = ref({});

const togglePillar = (id) => {
    collapsedPillars.value[id] = !collapsedPillars.value[id];
};

const isPillarCollapsed = (id) => {
    return !!collapsedPillars.value[id];
};

const expandAllPillars = () => {
    collapsedPillars.value = {};
};

const collapseAllPillars = () => {
    treeData.forEach(p => {
        collapsedPillars.value[p.id] = true;
    });
};

const isNodeHighlighted = (text) => {
    if (!searchQuery.value) return false;
    return text.toLowerCase().includes(searchQuery.value.toLowerCase());
};

// --- DATA 1: HIERARCHICAL TREE DATA (BAGAN POHON SISTEM & ROLE) ---
const treeData = [
    {
        id: 'pilar-governance',
        title: 'Pilar 1: Tata Kelola & Sistem',
        badge: 'Governance & Core',
        icon: '🏛️',
        colorTheme: 'purple',
        badgeClass: 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200 dark:border-purple-800',
        borderClass: 'border-purple-200 dark:border-purple-800',
        headerBg: 'bg-purple-50/60 dark:bg-purple-950/30 text-purple-900 dark:text-purple-200',
        accentBg: 'bg-purple-500',
        desc: 'Fondasi otoritas, administrasi hukum yayasan, otentikasi peran, dan keamanan sistem.',
        roles: [
            {
                name: 'Webmaster (Super Administrator)',
                code: 'webmaster',
                icon: '👑',
                colorClass: 'text-purple-600 dark:text-purple-400',
                badgeClass: 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                nodes: [
                    { title: 'Manajemen User & RBAC', url: '/users', desc: 'Pembuatan akun, kontrol 7 role, status aktif/non-aktif', code: 'US-01' },
                    { title: 'Profil Lembaga & Bank MKT', url: '/mkt-profile', desc: 'Visi-misi, legalitas SK Kemenkumham, rekening resmi BSI/Mandiri/BCA', code: 'PF-02' },
                    { title: 'AI Assistant & API Gateway', url: '/news-management', desc: 'Integrasi Google Gemini, OpenAI, Groq, webhook BMKG cuaca/gempa', code: 'AI-03' },
                    { title: 'Audit Trail & Keamanan', url: '/dashboard', desc: 'Monitoring integritas data, mitigasi downtime, audit transaksi', code: 'SC-04' },
                ]
            },
            {
                name: 'Administrator (Admin Operasional)',
                code: 'administrator',
                icon: '⚡',
                colorClass: 'text-blue-600 dark:text-blue-400',
                badgeClass: 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                nodes: [
                    { title: 'Verifikasi Relawan Mandiri', url: '/volunteers', desc: 'Validasi form pendaftaran calon relawan publik & golongan darah', code: 'VR-01' },
                    { title: 'Notulensi & Arsip Rapat', url: '/meetings', desc: 'Dokumentasi risalah rapat evaluasi bencana, keputusan taktis, berkas PDF', code: 'MT-02' },
                    { title: 'Kurasi Berita Lapangan', url: '/news-management', desc: 'Publikasi artikel respon bencana didukung prompt AI jurnalistik', code: 'NW-03' },
                    { title: 'Struktur Pengurus MKT', url: '/management', desc: 'Pengelolaan hierarki 3-tier: Dewan Pembina, Pengawas, Pengurus Harian', code: 'MG-04' },
                ]
            }
        ]
    },
    {
        id: 'pilar-rescue',
        title: 'Pilar 2: Kesiapsiagaan & Respon SAR',
        badge: 'Disaster & Rescue Ops',
        icon: '🚨',
        colorTheme: 'rose',
        badgeClass: 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        borderClass: 'border-rose-200 dark:border-rose-800',
        headerBg: 'bg-rose-50/60 dark:bg-rose-950/30 text-rose-900 dark:text-rose-200',
        accentBg: 'bg-rose-500',
        desc: 'Deteksi dini, koordinasi SAR gabungan, pencarian, pertolongan korban, dan Command Center 24/7.',
        roles: [
            {
                name: 'Tim Rescue & Relawan Lapangan',
                code: 'relawan',
                icon: '⛑️',
                colorClass: 'text-rose-600 dark:text-rose-400',
                badgeClass: 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300',
                nodes: [
                    { title: 'Deteksi Prabencana & BMKG', url: '/disaster-map', desc: 'Prakiraan cuaca maritim & plotting koordinat rawan di Peta Bencana', code: 'DM-01' },
                    { title: 'Registrasi Operasi SAR', url: '/sar-operations', desc: 'Penerbitan kode register SAR-YYYYMM-XXX, Danru/SMC, status darurat', code: 'SR-02' },
                    { title: 'Command Center Pusdalops', url: '/sar-operations/command-center', desc: 'Pusat pantau 24 jam armada perahu RIB, drone thermal, dan personel', code: 'CC-03' },
                    { title: 'Pelaporan Statistik Korban', url: '/sar-operations', desc: 'Update data korban selamat, luka-luka, meninggal dunia, dan hilang', code: 'KB-04' },
                ]
            },
            {
                name: 'Mitra Potensi SAR (Basarnas / BPBD / PMI)',
                code: 'mitra',
                icon: '🤝',
                colorClass: 'text-amber-600 dark:text-amber-400',
                badgeClass: 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
                nodes: [
                    { title: 'Direktori Kemitraan Resmi', url: '/volunteers', desc: 'Pendataan instansi lintas sektor, MoU kerjasama, person in charge', code: 'PT-01' },
                    { title: 'Pengerahan Tim Gabungan', url: '/sar-operations/command-center', desc: 'Deployment gabungan personel BSG Basarnas, PMI Rescue, Tagana, Polairud', code: 'PT-02' },
                ]
            }
        ]
    },
    {
        id: 'pilar-aid',
        title: 'Pilar 3: Medis Darurat & Bantuan Logistik',
        badge: 'Medical & Humanitarian Aid',
        icon: '📦',
        colorTheme: 'teal',
        badgeClass: 'bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border-teal-200 dark:border-teal-800',
        borderClass: 'border-teal-200 dark:border-teal-800',
        headerBg: 'bg-teal-50/60 dark:bg-teal-950/30 text-teal-900 dark:text-teal-200',
        accentBg: 'bg-teal-500',
        desc: 'Pertolongan pertama gawat darurat, mobilisasi darah PMI, dan rantai pasok kebutuhan pokok pengungsi.',
        roles: [
            {
                name: 'Dokter & Tim Medis MKT',
                code: 'medis',
                icon: '🩺',
                colorClass: 'text-teal-600 dark:text-teal-400',
                badgeClass: 'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300',
                nodes: [
                    { title: 'Posko Triase Medis Lapangan', url: '/sar-operations', desc: 'First aid korban darurat, stabilisasi kondisi, trauma healing anak', code: 'MD-01' },
                    { title: 'Aksi Donor Darah Rutin (PMI)', url: '/volunteers', desc: 'Pengerahan relawan donor darah (A, B, AB, O) untuk stok darurat rumah sakit', code: 'BD-02' },
                    { title: 'Rujukan Ambulance Medis', url: '/mitra', desc: 'Koordinasi pengantaran pasien gawat darurat ke rumah sakit mitra', code: 'AM-03' },
                ]
            },
            {
                name: 'Staff Gudang & Logistik',
                code: 'staff',
                icon: '🏕️',
                colorClass: 'text-emerald-600 dark:text-emerald-400',
                badgeClass: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
                nodes: [
                    { title: 'Inventarisasi Bantuan Masuk', url: '/logistics', desc: 'Penerimaan beras, mie, selimut, perahu karet, dan tenda pengungsian', code: 'LG-01' },
                    { title: 'Distribusi Logistik ke Posko', url: '/logistics', desc: 'Pencatatan barang keluar dengan tanda terima resmi penanggung jawab posko', code: 'LG-02' },
                ]
            }
        ]
    },
    {
        id: 'pilar-finance',
        title: 'Pilar 4: Filantropi & Akuntansi PSAK',
        badge: 'Donations & Transparent Finance',
        icon: '💰',
        colorTheme: 'emerald',
        badgeClass: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
        borderClass: 'border-emerald-200 dark:border-emerald-800',
        headerBg: 'bg-emerald-50/60 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200',
        accentBg: 'bg-emerald-500',
        desc: 'Pengelolaan donasi publik yang akuntabel, auto-journaling ke buku kas, dan pelaporan neraca berstandar yayasan.',
        roles: [
            {
                name: 'Donatur (Publik & Lembaga)',
                code: 'donatur',
                icon: '❤️',
                colorClass: 'text-orange-600 dark:text-orange-400',
                badgeClass: 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300',
                nodes: [
                    { title: 'Penyaluran Donasi Publik', url: '/', desc: 'Pemilihan program kemanusiaan & transfer dana via BSI/Mandiri/BCA', code: 'DN-01' },
                    { title: 'Kwitansi & Doa Elektronik', url: '/donors', desc: 'Penerbitan bukti tanda terima donasi sah dengan status Sukses', code: 'DN-02' },
                    { title: 'Monitoring Transparansi Dana', url: '/berita', desc: 'Pemantauan serapan dana donasi melalui berita & neraca keuangan publik', code: 'DN-03' },
                ]
            },
            {
                name: 'Finance & Keuangan (Bendahara)',
                code: 'finance',
                icon: '💵',
                colorClass: 'text-emerald-600 dark:text-emerald-400',
                badgeClass: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
                nodes: [
                    { title: 'Bagan Akun (COA Standar)', url: '/finance/coa', desc: 'Pengaturan akun Aset (1), Kewajiban (2), Ekuitas (3), Donasi (4), Beban (5)', code: 'FN-01' },
                    { title: 'Sinkronisasi Donasi (Auto-Journal)', url: '/donors', desc: 'Konversi donasi sukses otomatis mendebet Kas Bank & mengkredit Pendapatan', code: 'FN-02' },
                    { title: 'Jurnal Umum Berpasangan', url: '/finance/journal', desc: 'Pencatatan belanja operasional BBM rescue, logistik pangan, dan perawatan armada', code: 'FN-03' },
                    { title: 'Buku Besar & Saldo Awal Dinamis', url: '/finance/ledger', desc: 'Penghitungan mutasi kas dan akumulasi saldo berjalan per rentang tanggal', code: 'FN-04' },
                    { title: 'Neraca PSAK & Cetak Resmi', url: '/finance/balance-sheet', desc: 'Penerbitan Neraca resmi dengan Kop Yayasan & tanda tangan Ketua/Bendahara', code: 'FN-05' },
                ]
            }
        ]
    }
];

// --- DATA 2: ROLES DEFINITIONS & WORKFLOWS (FOR ROLES DEEP-DIVE) ---
const rolesData = [
    {
        key: 'webmaster',
        name: 'Webmaster (Super Administrator)',
        badgeColor: 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border-purple-200 dark:border-purple-800',
        cardBg: 'from-purple-500/10 to-transparent',
        icon: '👑',
        summary: 'Pemegang hak akses tertinggi sistem. Mengatur tata kelola akun, arsitektur data, integrasi API, AI Assistant, dan keamanan infrastruktur.',
        modules: ['Manajemen Pengguna (/users)', 'Profil MKT (/mkt-profile)', 'Seluruh Modul Operasi, Keuangan & Portal', 'Konfigurasi Layanan AI & API Token'],
        steps: [
            {
                id: 'wm-1',
                title: '1. Inisialisasi & Tata Kelola Pengguna',
                desc: 'Membuat, memverifikasi, dan menetapkan peran (Role RBAC) bagi seluruh staf, admin, dan relawan.',
                input: 'Data staf/relawan baru, alamat email, penugasan divisi.',
                action: 'Menentukan role (webmaster, administrator, finance, relawan, mitra, medis, donatur). Mengelola status aktif/non-aktif.',
                output: 'Akun terdaftar dengan otentikasi aman dan izin akses modul yang tepat.',
                moduleUrl: '/users',
                moduleName: 'Manajemen User'
            },
            {
                id: 'wm-2',
                title: '2. Pengaturan Profil & Legalitas Yayasan MKT',
                desc: 'Memperbarui profil lembaga, visi-misi, alamat kantor, kontak darurat, dan nomor rekening perbankan resmi.',
                input: 'Legalitas yayasan, data rekening BSI/Mandiri/BCA, struktur organisasi.',
                action: 'Update profil yayasan yang akan otomatis tampil di Portal Publik dan Kop Surat Laporan Keuangan.',
                output: 'Kredibilitas publik terjaga dan rekening donasi terverifikasi.',
                moduleUrl: '/mkt-profile',
                moduleName: 'Profil MKT'
            },
            {
                id: 'wm-3',
                title: '3. Konfigurasi AI Assistant & Integrasi API',
                desc: 'Mengelola integrasi Google Gemini / OpenAI untuk AI News Assistant dan koordinasi BMKG real-time feeds.',
                input: 'API Key (Gemini, OpenAI, Groq), endpoint webhook BMKG.',
                action: 'Menguji performa generate artikel berita, ringkasan situasi bencana, dan fallback rule.',
                output: 'Fitur kecerdasan buatan dan auto-feed BMKG beroperasi optimal 24/7.',
                moduleUrl: '/news-management',
                moduleName: 'Berita & AI Assistant'
            },
            {
                id: 'wm-4',
                title: '4. Pengawasan Keamanan & Audit Log',
                desc: 'Memantau integritas database, log transaksi donasi, dan sinkronisasi jurnal akuntansi.',
                input: 'Log aktivitas sistem, anomali data, atau kebutuhan reset password.',
                action: 'Audit trail, optimasi database, dan pemeliharaan performa server.',
                output: 'Sistem stabil, data terlindungi, dan kepatuhan audit terpenuhi.',
                moduleUrl: '/dashboard',
                moduleName: 'Dashboard Hub'
            }
        ]
    },
    {
        key: 'administrator',
        name: 'Administrator (Admin Operasional)',
        badgeColor: 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        cardBg: 'from-blue-500/10 to-transparent',
        icon: '⚡',
        summary: 'Penanggung jawab operasional harian yayasan. Mengawal verifikasi relawan publik, notulensi rapat, kurasi berita, dan koordinasi antar-divisi.',
        modules: ['Mitra & Relawan (/volunteers)', 'Arsip Rapat (/meetings)', 'Berita & Artikel (/news-management)', 'Struktur Pengurus (/management)', 'Logistik Darurat (/logistics)'],
        steps: [
            {
                id: 'adm-1',
                title: '1. Verifikasi Registrasi Relawan Publik',
                desc: 'Mengecek data pendaftaran calon relawan donor darah & rescue yang mendaftar mandiri via portal publik.',
                input: 'Form registrasi relawan (identitas, golongan darah, keahlian khusus).',
                action: 'Validasi nomor HP/WhatsApp, wawancara singkat, dan mengubah status menjadi "Aktif".',
                output: 'Relawan resmi terverifikasi dan siap dimobilisasi saat bencana.',
                moduleUrl: '/volunteers',
                moduleName: 'Mitra & Relawan'
            },
            {
                id: 'adm-2',
                title: '2. Dokumentasi & Pengarsipan Rapat Koordinasi',
                desc: 'Mencatat notulensi rapat evaluasi penanganan bencana, rapat kerja pengurus, dan keputusan strategis.',
                input: 'Agenda rapat, peserta hadir, berkas lampiran (PDF/DOCX).',
                action: 'Simpan risalah rapat, poin keputusan, dan tindak lanjut tugas (action items).',
                output: 'Arsip resmi notulensi yayasan yang mudah ditelusuri kapan saja.',
                moduleUrl: '/meetings',
                moduleName: 'Arsip Rapat'
            },
            {
                id: 'adm-3',
                title: '3. Kurasi & Publikasi Berita / Artikel MKT',
                desc: 'Menulis dokumentasi aksi kemanusiaan di lapangan dengan bantuan AI Assistant (Gemini / OpenAI).',
                input: 'Poin lapangan (foto evakuasi, jumlah logistik terdistribusi, lokasi maros/gowa).',
                action: 'Generate draft berita jurnalistik dengan AI, poles konten, pilih cover image, dan publish ke publik.',
                output: 'Artikel berita tayang di portal publik untuk transparansi donatur dan masyarakat.',
                moduleUrl: '/news-management',
                moduleName: 'Berita & Artikel'
            },
            {
                id: 'adm-4',
                title: '4. Supervisi Inventaris Logistik Bencana',
                desc: 'Memastikan stok logistik kritis (tenda, sembako, perahu karet, obat) terpantau dan siap dikirim.',
                input: 'Penerimaan bantuan natura atau pengeluaran logistik untuk posko.',
                action: 'Input transaksi barang masuk/keluar serta verifikasi jumlah sisa fisik di gudang.',
                output: 'Stok logistik selalu terupdate dan tidak terjadi kekosongan saat darurat.',
                moduleUrl: '/logistics',
                moduleName: 'Logistik Bencana'
            }
        ]
    },
    {
        key: 'finance',
        name: 'Finance & Keuangan (Bendahara)',
        badgeColor: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
        cardBg: 'from-emerald-500/10 to-transparent',
        icon: '💰',
        summary: 'Mengelola akuntansi keuangan yayasan berstandar PSAK. Mulai dari Chart of Accounts (COA), Jurnal Umum, Buku Besar (Ledger), hingga Neraca & Cetak Laporan Resmi.',
        modules: ['Daftar COA (/finance/coa)', 'Jurnal Umum (/finance/journal)', 'Buku Besar (/finance/ledger)', 'Neraca Keuangan (/finance/balance-sheet)', 'Sinkronisasi Donasi (/donors)'],
        steps: [
            {
                id: 'fin-1',
                title: '1. Pemeliharaan Bagan Kode Akun (COA)',
                desc: 'Menyusun struktur akun standar: Aset (1), Kewajiban (2), Ekuitas/Aset Neto (3), Pendapatan Donasi (4), Beban Bencana (5).',
                input: 'Kode akun baru atau penyesuaian pos keuangan yayasan.',
                action: 'Validasi kategori akun (Debit/Kredit normal) agar laporan keuangan seimbang.',
                output: 'Fondasi pembukuan akuntansi yang rapi dan terstandarisasi.',
                moduleUrl: '/finance/coa',
                moduleName: 'Chart of Accounts'
            },
            {
                id: 'fin-2',
                title: '2. Rekonsiliasi & Sinkronisasi Donasi Masuk',
                desc: 'Mencocokkan mutasi rekening bank dengan daftar donasi publik, lalu melakukan auto-journaling.',
                input: 'Rekening koran bank BSI/Mandiri/BCA dan data donasi berstatus "Sukses".',
                action: 'Klik "Sinkronisasi ke Jurnal Keuangan" untuk membuat entri jurnal otomatis (Debet: Kas Bank, Kredit: Pendapatan Donasi).',
                output: 'Setiap rupiah donasi publik tercatat sah di pembukuan tanpa entri manual ganda.',
                moduleUrl: '/donors',
                moduleName: 'Donatur & Donasi'
            },
            {
                id: 'fin-3',
                title: '3. Pencatatan Jurnal Umum & Beban Operasional',
                desc: 'Mencatat pengeluaran operasional respon bencana, pembelian sembako darurat, bahan bakar perahu SAR, dan logistik medis.',
                input: 'Kwitansi, invoice pembelian, bukti transfer belanja rescue.',
                action: 'Input jurnal entri berpasangan (Double Entry: Total Debit = Total Credit).',
                output: 'Buku jurnal umum terverifikasi dan berstatus balanced.',
                moduleUrl: '/finance/journal',
                moduleName: 'Jurnal Umum'
            },
            {
                id: 'fin-4',
                title: '4. Analisis Buku Besar (General Ledger) & Saldo Awal',
                desc: 'Memantau pergerakan kas dan mutasi setiap akun per periode dengan perhitungan saldo awal dinamis.',
                input: 'Filter rentang tanggal (start date - end date) dan pemilihan kode akun.',
                action: 'Sistem menghitung saldo awal kumulatif, mutasi debit/kredit, dan saldo akhir berjalan.',
                output: 'Transparansi mutasi per akun yang siap diuji kebenarannya.',
                moduleUrl: '/finance/ledger',
                moduleName: 'Buku Besar'
            },
            {
                id: 'fin-5',
                title: '5. Penyusunan Neraca & Pencetakan Laporan Standar PSAK',
                desc: 'Menghasilkan Neraca Keuangan resmi lengkap dengan Kop Surat Yayasan MKT, nomor surat, dan tanda tangan Ketua & Bendahara.',
                input: 'Periode pelaporan bulanan, triwulanan, atau tahunan.',
                action: 'Klik "Cetak Dokumen Resmi", atur tanda tangan pengurus, lalu ekspor PDF atau print.',
                output: 'Dokumen laporan keuangan terverifikasi siap diaudit atau dipublikasikan ke stakeholder.',
                moduleUrl: '/finance/balance-sheet',
                moduleName: 'Neraca Keuangan'
            }
        ]
    },
    {
        key: 'relawan',
        name: 'Tim Rescue & Relawan (Koordinator Lapangan)',
        badgeColor: 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        cardBg: 'from-rose-500/10 to-transparent',
        icon: '⛑️',
        summary: 'Ujung tombak respon cepat bencana di lapangan. Memantau BMKG, mengoperasikan perahu karet/drone, melaksanakan evakuasi, dan pelaporan korban di Command Center.',
        modules: ['Peta Bencana (/disaster-map)', 'Operasi & Siaga SAR (/sar-operations)', 'Command Center Pusdalops (/sar-operations/command-center)', 'Logistik Lapangan (/logistics)'],
        steps: [
            {
                id: 'res-1',
                title: '1. Pemantauan Peringatan Dini BMKG & Status Cuaca',
                desc: 'Memonitor prakiraan cuaca, potensi hujan lebat, gelombang tinggi, dan info gempa bumi terkini.',
                input: 'Widget cuaca BMKG terintegrasi di Dashboard dan Peta Bencana.',
                action: 'Menganalisis potensi bahaya prabencana dan menetapkan status siaga personel.',
                output: 'Kesiapsiagaan Tim Rescue MKT 727 dalam kondisi "Siaga 1".',
                moduleUrl: '/disaster-map',
                moduleName: 'Peta Bencana'
            },
            {
                id: 'res-2',
                title: '2. Pendaftaran Operasi & Siaga SAR Baru',
                desc: 'Menginput insiden darurat atau penetapan siaga pantai/sungai dengan kode registrasi resmi SAR-YYYYMM-XXX.',
                input: 'Laporan warga/BPBD: jenis musibah (banjir, orang tenggelam, longsor), titik koordinat GPS.',
                action: 'Input judul, status (Operasi Aktif / Siaga SAR), Danru/SMC, jumlah personel, dan peralatan (RIB, Alkon, Drone).',
                output: 'Operasi SAR resmi terdaftar dan muncul di Command Center real-time.',
                moduleUrl: '/sar-operations',
                moduleName: 'Operasi SAR'
            },
            {
                id: 'res-3',
                title: '3. Pengerahan Tim & Partisipasi Potensi SAR',
                desc: 'Mencatat pengerahan tim lapangan gabungan (Tim Rescue MKT, BSG Basarnas, Polairud, Tagana).',
                input: 'Instansi pendukung, nama regu, jumlah personel, status deployment.',
                action: 'Menghubungkan regu penolong ke operasi SAR aktif di lapangan.',
                output: 'Rantai komando pengerahan tim tercatat rapi dan terkoordinasi.',
                moduleUrl: '/sar-operations/command-center',
                moduleName: 'Command Center'
            },
            {
                id: 'res-4',
                title: '4. Pelaporan Korban & Evakuasi Lapangan',
                desc: 'Memperbarui data hasil evakuasi: jumlah korban selamat, luka-luka, meninggal dunia, dan dalam pencarian.',
                input: 'Data asesmen riil dari regu penyelamat di titik evakuasi.',
                action: 'Update statistik korban secara real-time pada sistem.',
                output: 'Data akurat korban bencana untuk kebutuhan rujukan medis dan informasi publik.',
                moduleUrl: '/sar-operations',
                moduleName: 'Operasi SAR'
            }
        ]
    },
    {
        key: 'mitra',
        name: 'Mitra Kemanusiaan (Basarnas, BPBD, PMI, Lembaga Filantropi)',
        badgeColor: 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        cardBg: 'from-amber-500/10 to-transparent',
        icon: '🤝',
        summary: 'Mitra strategis instansi pemerintah dan lembaga filantropi. Berkolaborasi dalam pilar mitigasi prabencana, SAR bersama saat tanggap darurat, dan rehabilitasi pascabencana.',
        modules: ['Mitra Kemanusiaan (/volunteers)', 'Pilar Kebencanaan (/mitra)', 'Command Center Bersama (/sar-operations/command-center)', 'Integrasi Mobile API (/api/v1/partners)'],
        steps: [
            {
                id: 'mit-1',
                title: '1. Pendaftaran & Validasi Kerjasama Lembaga',
                desc: 'Mendaftarkan profil instansi mitra (Basarnas Makassar, BPBD Maros, PMI Sulsel, dsb) lengkap dengan kontak PIC.',
                input: 'Nama instansi, kategori (Pemerintah/SAR/Filantropi/Medis), alamat, logo.',
                action: 'Admin/Webmaster memverifikasi dan menampilkan di direktori mitra resmi yayasan.',
                output: 'Jejaring kolaborasi lintas sektor yang kuat dan terlegitimasi.',
                moduleUrl: '/volunteers',
                moduleName: 'Mitra & Relawan'
            },
            {
                id: 'mit-2',
                title: '2. Integrasi Data & Operasi Gabungan (Potensi SAR)',
                desc: 'Sinergi pengerahan armada penolong dan personel saat terjadi musibah berskala besar.',
                input: 'Surat tugas operasi gabungan atau pengerahan armada rescue.',
                action: 'Sinkronisasi status operasi SAR di sistem App-MKT dan koordinasi frekuensi radio/posko.',
                output: 'Pencarian dan pertolongan korban berjalan efektif tanpa tumpang tindih peran.',
                moduleUrl: '/sar-operations/command-center',
                moduleName: 'Command Center'
            }
        ]
    },
    {
        key: 'medis',
        name: 'Dokter & Tim Medis (Kesehatan Darurat)',
        badgeColor: 'bg-teal-100 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border-teal-200 dark:border-teal-800',
        cardBg: 'from-teal-500/10 to-transparent',
        icon: '🩺',
        summary: 'Tenaga medis penolong korban dan penggerak relawan donor darah. Memastikan kebutuhan medis darurat dan ketersediaan stok darah saat bencana tercukupi.',
        modules: ['Donor Darah Relawan (/volunteers)', 'Logistik Obat & P3K (/logistics)', 'Posko Medis Darurat (/sar-operations)', 'Rujukan Rumah Sakit (/mitra)'],
        steps: [
            {
                id: 'med-1',
                title: '1. Kesiapsiagaan Posko Medis Darurat Lapangan',
                desc: 'Mendirikan posko pertolongan pertama (First Aid) dan triase korban di lokasi aman dekat bencana.',
                input: 'Korban luka yang dievakuasi oleh tim rescue.',
                action: 'Pemberian penanganan gawat darurat, stabilisasi tanda vital, dan rujukan ambulance ke RS mitra.',
                output: 'Korban mendapatkan pertolongan medis cepat guna menekan angka fatalitas.',
                moduleUrl: '/sar-operations',
                moduleName: 'Posko Medis'
            },
            {
                id: 'med-2',
                title: '2. Pengelolaan Relawan Donor Darah & Stok Darah',
                desc: 'Memobilisasi relawan pendonor darah rutin sesuai golongan darah (A, B, AB, O) bersama PMI.',
                input: 'Permintaan kantong darah darurat dari rumah sakit rujukan korban bencana.',
                action: 'Filter relawan donor darah aktif berdasarkan golongan darah dan kirim notifikasi aksi donor.',
                output: 'Kebutuhan darah darurat bagi korban luka berat segera terpenuhi.',
                moduleUrl: '/volunteers',
                moduleName: 'Data Relawan'
            }
        ]
    },
    {
        key: 'donatur',
        name: 'Donatur (Publik & Filantropi)',
        badgeColor: 'bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300 border-orange-200 dark:border-orange-800',
        cardBg: 'from-orange-500/10 to-transparent',
        icon: '❤️',
        summary: 'Masyarakat dan lembaga donatur yang menyalurkan infaq, sedekah, dan donasi tanggap darurat bencana untuk mendukung operasional kemanusiaan MKT.',
        modules: ['Portal Donasi Publik (/)', 'Riwayat Donasi (/donors)', 'Transparansi Berita (/berita)', 'Laporan Keuangan Publik (/profil)'],
        steps: [
            {
                id: 'don-1',
                title: '1. Pemilihan Program Donasi Bencana',
                desc: 'Donatur memilih program bantuan via portal website (Tanggap Bencana Banjir, Paket Sembako, Armada Rescue).',
                input: 'Pilihan program donasi, nominal bantuan (Rp), nama/hamba Allah, email/WhatsApp.',
                action: 'Donatur mentransfer ke rekening resmi BSI/Mandiri/BCA atas nama Yayasan MKT.',
                output: 'Data donasi tercatat di sistem berstatus "Pending" untuk verifikasi.',
                moduleUrl: '/',
                moduleName: 'Portal Donasi'
            },
            {
                id: 'don-2',
                title: '2. Konfirmasi & Penerbitan Kwitansi Resmi',
                desc: 'Sistem memverifikasi dana masuk dan menerbitkan bukti donasi elektronik.',
                input: 'Bukti transfer donatur atau notifikasi mutasi rekening.',
                action: 'Status donasi diubah menjadi "Sukses", memicu auto-journaling ke buku kas yayasan.',
                output: 'Donatur menerima kwitansi digital dan doa/tanda terima resmi dari yayasan.',
                moduleUrl: '/donors',
                moduleName: 'Kwitansi Donasi'
            },
            {
                id: 'don-3',
                title: '3. Pemantauan Transparansi Penyaluran Dana',
                desc: 'Donatur dapat memantau langsung foto penyaluran logistik via berita dan laporan neraca keuangan.',
                input: 'Akses berita berkala dan ringkasan dana terserap di portal publik.',
                action: 'Donatur melihat dampak langsung bantuan mereka bagi para penyintas bencana.',
                output: 'Kepercayaan publik meningkat dan hubungan filantropi jangka panjang terjalin.',
                moduleUrl: '/berita',
                moduleName: 'Portal Berita'
            }
        ]
    }
];

// Active role object
const selectedRole = computed(() => {
    return rolesData.find(r => r.key === selectedRoleKey.value) || rolesData[0];
});

// RACI / Access Permissions Matrix
const accessMatrix = [
    { module: 'Dashboard & Cuaca BMKG', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Read Only', res: 'Read Only', mit: 'Read Only', med: 'Read Only', don: 'Read Only' },
    { module: 'Peta Operasi Bencana', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Read Only', res: 'Full CRUD', mit: 'Read Only', med: 'Read Only', don: 'Read Only' },
    { module: 'Operasi & Siaga SAR', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Read Only', res: 'Full CRUD', mit: 'Partisipasi', med: 'Posko Medis', don: 'No Access' },
    { module: 'Command Center Pusdalops', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Read Only', res: 'Monitoring', mit: 'Monitoring', med: 'Monitoring', don: 'No Access' },
    { module: 'Mitra & Relawan', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Read Only', res: 'Read Only', mit: 'Self Profile', med: 'Data Donor', don: 'No Access' },
    { module: 'Logistik Darurat', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Audit Stok', res: 'Input Keluar', mit: 'Bantuan Masuk', med: 'Logistik Obat', don: 'No Access' },
    { module: 'Donatur & Donasi', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Full CRUD', res: 'No Access', mit: 'No Access', med: 'No Access', don: 'Self Donasi' },
    { module: 'COA & Jurnal Keuangan', wm: 'Full CRUD', adm: 'Audit View', fin: 'Full CRUD', res: 'No Access', mit: 'No Access', med: 'No Access', don: 'No Access' },
    { module: 'Buku Besar & Neraca PSAK', wm: 'Full CRUD', adm: 'Audit View', fin: 'Full CRUD (Cetak)', res: 'No Access', mit: 'No Access', med: 'No Access', don: 'No Access' },
    { module: 'Berita & AI Assistant', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'No Access', res: 'Submit Draft', mit: 'No Access', med: 'No Access', don: 'Read Publik' },
    { module: 'Arsip & Notulensi Rapat', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Notulensi Kas', res: 'Peserta Rapat', mit: 'Rapat Bersama', med: 'Rapat Medis', don: 'No Access' },
    { module: 'Manajemen Pengguna (Users)', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'No Access', res: 'No Access', mit: 'No Access', med: 'No Access', don: 'No Access' },
    { module: 'Profil Lembaga MKT', wm: 'Full CRUD', adm: 'Full CRUD', fin: 'Read Only', res: 'Read Only', mit: 'Read Only', med: 'Read Only', don: 'Read Publik' },
];

const printFlowchart = () => {
    window.print();
};
</script>

<template>
    <div class="space-y-6">
        <!-- Unauthorized Banner (Defense-in-depth) -->
        <div v-if="!isAuthorized" class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-2xl p-6 text-center">
            <span class="text-4xl">🚫</span>
            <h3 class="text-lg font-bold text-red-800 dark:text-red-200 mt-2">Akses Terbatas: Khusus Webmaster & Administrator</h3>
            <p class="text-sm text-red-600 dark:text-red-400 mt-1">Halaman alur proses dan flowchart sistem ini hanya dapat dibuka oleh peran tingkat manajerial tertinggi.</p>
        </div>

        <div v-else class="space-y-6">
            <!-- Header Section & Control Strip -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300 border border-orange-200 dark:border-orange-800 uppercase tracking-wider">
                            🛡️ SOP v2.5 Ekosistem MKT
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                            🔒 Khusus Webmaster & Admin
                        </span>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white mt-2 tracking-tight">
                        Alur & Bagan Pohon Sistem (System Tree Hierarchy & Workflows)
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-3xl">
                        Visualisasi hierarki pohon rantai proses, pembagian tugas 7 peran pengguna, pilar kesiapsiagaan SAR, logistik medis, dan akuntabilitas akuntansi PSAK Yayasan MKT.
                    </p>
                </div>

                <!-- Right Action Buttons -->
                <div class="flex items-center space-x-2 shrink-0">
                    <button
                        @click="printFlowchart"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition flex items-center space-x-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Cetak Dokumen</span>
                    </button>
                </div>
            </div>

            <!-- Main View Switcher Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 dark:border-gray-800 pb-2">
                <!-- Tab 1: Bagan Pohon (Tree Chart) -->
                <button
                    @click="activeView = 'tree'"
                    :class="[
                        activeView === 'tree'
                            ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold shadow-md shadow-orange-500/20'
                            : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800',
                        'px-4 py-2 rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition-all'
                    ]"
                >
                    <span>🌳</span>
                    <span>1. Bagan Pohon Sistem & Role (Tree Chart)</span>
                </button>

                <!-- Tab 2: Alur Global (Pipeline) -->
                <button
                    @click="activeView = 'global'"
                    :class="[
                        activeView === 'global'
                            ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold shadow-md shadow-orange-500/20'
                            : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800',
                        'px-4 py-2 rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition-all'
                    ]"
                >
                    <span>🌐</span>
                    <span>2. Alur Ekosistem Global (End-to-End)</span>
                </button>

                <!-- Tab 3: Detail per Role -->
                <button
                    @click="activeView = 'roles'"
                    :class="[
                        activeView === 'roles'
                            ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold shadow-md shadow-orange-500/20'
                            : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800',
                        'px-4 py-2 rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition-all'
                    ]"
                >
                    <span>👥</span>
                    <span>3. Detail Alur & SOP per Role (7 Peran)</span>
                </button>

                <!-- Tab 4: Matriks Hak Akses (RACI) -->
                <button
                    @click="activeView = 'matrix'"
                    :class="[
                        activeView === 'matrix'
                            ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold shadow-md shadow-orange-500/20'
                            : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800',
                        'px-4 py-2 rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition-all'
                    ]"
                >
                    <span>📊</span>
                    <span>4. Matriks Hak Akses Modul (RACI)</span>
                </button>
            </div>

            <!-- =================================================================== -->
            <!-- VIEW 1: BAGAN POHON HIERARKI SISTEM & ROLE (TREE CHART) -->
            <!-- =================================================================== -->
            <div v-if="activeView === 'tree'" class="space-y-6">
                <!-- Toolbar: Search filter & Expand/Collapse All -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            🔍
                        </span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari alur/modul dalam pohon (misal: 'donasi', 'SAR', 'perahu', 'jurnal')..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-orange-500"
                        />
                    </div>

                    <div class="flex items-center space-x-2 shrink-0">
                        <button
                            @click="expandAllPillars"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition"
                        >
                            Buka Semua Cabang
                        </button>
                        <button
                            @click="collapseAllPillars"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition"
                        >
                            Tutup Semua Cabang
                        </button>
                    </div>
                </div>

                <!-- TREE DIAGRAM CONTAINER -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 sm:p-10 shadow-sm relative overflow-hidden">
                    
                    <!-- TREE ROOT NODE: YAYASAN MKT INDONESIA -->
                    <div class="flex flex-col items-center">
                        <div class="relative z-10 max-w-lg w-full bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 text-white p-5 rounded-2xl shadow-xl shadow-orange-500/20 text-center border-2 border-white/20">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-black/20 text-amber-200 border border-white/10 mb-2">
                                🏛️ AKAR POHON EKOSISTEM (ROOT HUB)
                            </span>
                            <h3 class="text-xl font-black tracking-tight">YAYASAN MITRA KEMANUSIAAN TERPADU</h3>
                            <p class="text-xs text-orange-100 mt-1">
                                Pusat Komando Terintegrasi: Prabencana, Tanggap Darurat Rescue SAR, Medis Donor Darah, hingga Akuntansi Filantropi.
                            </p>
                            <div class="mt-3 flex justify-center items-center space-x-3 text-[11px] font-mono text-orange-200 border-t border-white/15 pt-2">
                                <span>Pusdalops 24/7</span>
                                <span>•</span>
                                <span>Makassar, Sulawesi Selatan</span>
                                <span>•</span>
                                <span>v2.5 Platform</span>
                            </div>
                        </div>

                        <!-- Stem Vertical Line connecting Root to Pillars -->
                        <div class="w-0.5 h-10 bg-gradient-to-b from-orange-500 to-gray-300 dark:to-gray-700"></div>

                        <!-- Horizontal Branch Crossbar (Desktop only) -->
                        <div class="hidden lg:block w-11/12 max-w-5xl h-0.5 bg-gray-300 dark:bg-gray-700 relative">
                            <!-- 4 Drops to Pillars -->
                            <div class="absolute left-[12.5%] -bottom-4 w-0.5 h-4 bg-purple-400"></div>
                            <div class="absolute left-[37.5%] -bottom-4 w-0.5 h-4 bg-rose-400"></div>
                            <div class="absolute left-[62.5%] -bottom-4 w-0.5 h-4 bg-teal-400"></div>
                            <div class="absolute left-[87.5%] -bottom-4 w-0.5 h-4 bg-emerald-400"></div>
                        </div>
                    </div>

                    <!-- TREE LEVEL 1: 4 PILAR UTAMA (BRANCHES) -->
                    <div class="mt-4 lg:mt-6 grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <div
                            v-for="pilar in treeData"
                            :key="pilar.id"
                            class="border rounded-2xl overflow-hidden transition-all duration-200 bg-white dark:bg-gray-900 flex flex-col"
                            :class="[
                                pilar.borderClass,
                                isNodeHighlighted(pilar.title) || isNodeHighlighted(pilar.desc) ? 'ring-2 ring-orange-500 shadow-lg' : 'shadow-sm'
                            ]"
                        >
                            <!-- Pillar Header -->
                            <div :class="['p-4 border-b flex items-start justify-between cursor-pointer transition', pilar.headerBg, pilar.borderClass]" @click="togglePillar(pilar.id)">
                                <div class="flex items-center space-x-2.5">
                                    <span class="text-2xl shrink-0">{{ pilar.icon }}</span>
                                    <div>
                                        <div class="flex items-center space-x-1.5">
                                            <h4 class="text-xs font-black tracking-tight">{{ pilar.title }}</h4>
                                        </div>
                                        <p class="text-[10px] opacity-75 mt-0.5 line-clamp-1">{{ pilar.desc }}</p>
                                    </div>
                                </div>
                                <button
                                    class="p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition text-gray-500 shrink-0"
                                    :title="isPillarCollapsed(pilar.id) ? 'Buka Cabang' : 'Tutup Cabang'"
                                >
                                    <svg
                                        class="w-4 h-4 transition-transform duration-200"
                                        :class="isPillarCollapsed(pilar.id) ? '-rotate-90' : 'rotate-0'"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Pillar Body (Sub-branches for each Role) -->
                            <div v-show="!isPillarCollapsed(pilar.id)" class="p-4 space-y-4 flex-1">
                                <!-- Roles in Pillar -->
                                <div
                                    v-for="(r, rIdx) in pilar.roles"
                                    :key="r.code"
                                    class="border border-gray-100 dark:border-gray-800 rounded-xl p-3.5 bg-gray-50/50 dark:bg-gray-800/20 relative"
                                    :class="isNodeHighlighted(r.name) ? 'ring-2 ring-orange-400' : ''"
                                >
                                    <!-- Role Card Title -->
                                    <div class="flex items-center space-x-2 mb-2 pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                                        <span class="text-lg">{{ r.icon }}</span>
                                        <div class="overflow-hidden">
                                            <h5 :class="['text-xs font-black tracking-tight truncate', r.colorClass]">
                                                {{ r.name }}
                                            </h5>
                                            <span class="text-[9px] text-gray-400 font-mono">role: {{ r.code }}</span>
                                        </div>
                                    </div>

                                    <!-- Leaf Nodes (Action & Sub-Process) -->
                                    <div class="space-y-1.5 pl-2 relative">
                                        <!-- Vertical connection line -->
                                        <div class="absolute left-0 top-1 bottom-1 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                                        <div
                                            v-for="node in r.nodes"
                                            :key="node.code"
                                            class="relative pl-3 text-xs group"
                                        >
                                            <!-- Branch tick line -->
                                            <div class="absolute left-0 top-3 w-2.5 h-0.5 bg-gray-200 dark:bg-gray-700"></div>

                                            <div
                                                class="p-2 rounded-lg border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-orange-400 dark:hover:border-orange-500 transition-all shadow-2xs"
                                                :class="isNodeHighlighted(node.title) || isNodeHighlighted(node.desc) ? 'ring-2 ring-orange-400 bg-orange-50/40 dark:bg-orange-950/20' : ''"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-gray-900 dark:text-gray-100 group-hover:text-orange-600 dark:group-hover:text-orange-400 text-[11px]">
                                                        {{ node.title }}
                                                    </span>
                                                    <span class="text-[9px] font-mono font-bold px-1.5 py-0.2 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                                        {{ node.code }}
                                                    </span>
                                                </div>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">
                                                    {{ node.desc }}
                                                </p>
                                                <div class="mt-1.5 flex justify-end">
                                                    <Link
                                                        v-if="node.url"
                                                        :href="node.url"
                                                        class="text-[9px] font-extrabold text-orange-600 dark:text-orange-400 hover:underline flex items-center space-x-0.5"
                                                    >
                                                        <span>Buka Modul</span>
                                                        <span>→</span>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- VIEW 2: GLOBAL END-TO-END FLOWCHART -->
            <!-- =================================================================== -->
            <div v-if="activeView === 'global'" class="space-y-6">
                <!-- Visual Pipeline Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Phase 1 -->
                    <div class="bg-gradient-to-b from-sky-500/10 to-transparent border border-sky-200 dark:border-sky-900/60 rounded-2xl p-5 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-black uppercase tracking-wider text-sky-600 dark:text-sky-400">FASE 1</span>
                            <span class="text-lg">🛰️</span>
                        </div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">Prabencana & Deteksi Dini</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemantauan otomatis data cuaca ekstrem & gempa BMKG di Dashboard & Peta Bencana.</p>
                        <div class="mt-3 pt-3 border-t border-sky-200/50 dark:border-sky-800/40 text-[11px] text-sky-700 dark:text-sky-300 font-semibold">
                            Actor: Tim Rescue, Basarnas, Pusdalops
                        </div>
                    </div>

                    <!-- Phase 2 -->
                    <div class="bg-gradient-to-b from-rose-500/10 to-transparent border border-rose-200 dark:border-rose-900/60 rounded-2xl p-5 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">FASE 2</span>
                            <span class="text-lg">🚨</span>
                        </div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">Tanggap Darurat & SAR</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Aktivasi Operasi SAR di Command Center, deployment perahu karet, dan penyelamatan korban.</p>
                        <div class="mt-3 pt-3 border-t border-rose-200/50 dark:border-rose-800/40 text-[11px] text-rose-700 dark:text-rose-300 font-semibold">
                            Actor: Tim Rescue, Medis, BPBD, Relawan
                        </div>
                    </div>

                    <!-- Phase 3 -->
                    <div class="bg-gradient-to-b from-amber-500/10 to-transparent border border-amber-200 dark:border-amber-900/60 rounded-2xl p-5 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">FASE 3</span>
                            <span class="text-lg">📦</span>
                        </div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">Logistik & Medis Darurat</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Penyaluran sembako & selimut, pendirian posko kesehatan, dan aksi donor darah massal PMI.</p>
                        <div class="mt-3 pt-3 border-t border-amber-200/50 dark:border-amber-800/40 text-[11px] text-amber-700 dark:text-amber-300 font-semibold">
                            Actor: Staff Logistik, Tim Medis, Administrator
                        </div>
                    </div>

                    <!-- Phase 4 -->
                    <div class="bg-gradient-to-b from-emerald-500/10 to-transparent border border-emerald-200 dark:border-emerald-900/60 rounded-2xl p-5 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">FASE 4</span>
                            <span class="text-lg">📑</span>
                        </div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">Akuntabilitas & Keuangan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Auto-journaling donasi publik ke Buku Kas, Buku Besar, Neraca PSAK, dan publikasi berita AI.</p>
                        <div class="mt-3 pt-3 border-t border-emerald-200/50 dark:border-emerald-800/40 text-[11px] text-emerald-700 dark:text-emerald-300 font-semibold">
                            Actor: Finance, Webmaster, Donatur
                        </div>
                    </div>
                </div>

                <!-- Comprehensive Flowchart Canvas -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Diagram Alir Komprehensif Antar-Role Yayasan MKT</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rantai proses input data, eksekusi lapangan, hingga pelaporan pertanggungjawaban publik.</p>
                        </div>
                    </div>

                    <!-- Interactive Step Nodes Flow -->
                    <div class="space-y-4">
                        <!-- Node 1: Deteksi Bencana -->
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-sky-500/20">
                                    01
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300">Peringatan Dini</span>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Ingesti Data Cuaca BMKG & Pemetaan Titik Bencana</h4>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Sistem secara otomatis mengambil data cuaca maritim dan gempa bumi dari API BMKG. Admin / Tim Rescue memplot koordinat kerentanan pada Peta Bencana Leaflet.
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Pusdalops Siaga 24/7</span>
                                <span class="text-emerald-500 text-lg">➔</span>
                            </div>
                        </div>

                        <!-- Node 2: Aktivasi SAR & Command Center -->
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-rose-500/20">
                                    02
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">Tanggap Darurat</span>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Registrasi Operasi SAR & Mobilisasi Command Center</h4>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Jika terjadi musibah, Tim Rescue membuat nomor register <code class="text-orange-600 font-mono text-[11px]">SAR-YYYYMM-XXX</code>, menugaskan SMC/Danru, mengerahkan armada perahu karet, dan menyatukan Potensi SAR (Basarnas, BPBD, PMI).
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Evakuasi & Pertolongan</span>
                                <span class="text-emerald-500 text-lg">➔</span>
                            </div>
                        </div>

                        <!-- Node 3: Asesmen Korban & Distribusi Bantuan -->
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-amber-500/20">
                                    03
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">Penyelamatan & Logistik</span>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Pencatatan Korban, Tenda Pengungsian & Layanan Medis</h4>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Tim Medis menangani korban cedera, Tim Logistik mencatat pengeluaran stok beras, selimut, dan tenda darurat. Tim Rescue menginput statistik korban (Selamat, Cedera, Meninggal, Hilang).
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Dukungan Filantropi</span>
                                <span class="text-emerald-500 text-lg">➔</span>
                            </div>
                        </div>

                        <!-- Node 4: Penghimpunan Donasi & Auto-Journaling -->
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-emerald-500/20">
                                    04
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">Akuntansi Keuangan</span>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Sinkronisasi Donasi ke Jurnal Umum & Buku Besar</h4>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Donasi publik dari Donatur disinkronkan oleh Tim Keuangan (Finance) ke Jurnal Umum. Pengeluaran operasional dicatat secara berpasangan (Double Entry) untuk pembentukan Buku Besar dan Neraca.
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Transparansi Publik</span>
                                <span class="text-emerald-500 text-lg">➔</span>
                            </div>
                        </div>

                        <!-- Node 5: Publikasi & Audit Resmi -->
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md shadow-purple-500/20">
                                    05
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">Pelaporan & Audit</span>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Rilis Berita Dokumentasi (AI Assisted) & Cetak Laporan Neraca PSAK</h4>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Administrator mempublikasikan artikel berita respon bencana yang dibantu AI Assistant. Finance mencetak Neraca Keuangan resmi lengkap dengan Kop Surat Yayasan MKT dan tanda tangan Ketua serta Bendahara.
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <span class="text-xs font-bold text-purple-600 dark:text-purple-400">Selesai & Akuntabel</span>
                                <span class="text-emerald-500 text-lg">✓</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- VIEW 3: DETAIL ALUR & SOP PER ROLE (DEEP DIVE) -->
            <!-- =================================================================== -->
            <div v-if="activeView === 'roles'" class="space-y-6">
                <!-- Role Selector Tabs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                    <button
                        v-for="r in rolesData"
                        :key="r.key"
                        @click="selectedRoleKey = r.key"
                        :class="[
                            selectedRoleKey === r.key
                                ? 'bg-orange-500 text-white font-black shadow-md shadow-orange-500/25 ring-2 ring-orange-500 ring-offset-2 dark:ring-offset-gray-900'
                                : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 font-semibold',
                            'p-3 rounded-2xl flex flex-col items-center justify-center text-center transition-all'
                        ]"
                    >
                        <span class="text-xl mb-1">{{ r.icon }}</span>
                        <span class="text-xs line-clamp-1">{{ r.name.split(' ')[0] }}</span>
                        <span class="text-[9px] opacity-75 truncate max-w-full">{{ r.key }}</span>
                    </button>
                </div>

                <!-- Selected Role Deep Dive Card -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm">
                    <!-- Role Card Header -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-gray-100 dark:border-gray-800 gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center text-3xl shrink-0 shadow-sm">
                                {{ selectedRole.icon }}
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-xl font-black text-gray-900 dark:text-white">{{ selectedRole.name }}</h3>
                                    <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold border', selectedRole.badgeColor]">
                                        Role: {{ selectedRole.key }}
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">
                                    {{ selectedRole.summary }}
                                </p>
                            </div>
                        </div>

                        <!-- Quick Accessible Modules Pills -->
                        <div class="text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">Akses Modul Utama</span>
                            <div class="flex flex-wrap md:justify-end gap-1.5">
                                <span
                                    v-for="m in selectedRole.modules"
                                    :key="m"
                                    class="text-[10px] px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-mono"
                                >
                                    {{ m }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Role Workflow Steps Sequence -->
                    <div class="mt-8">
                        <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-4">
                            Langkah Kerja & Standar Operasional Prosedur (SOP)
                        </h4>

                        <div class="space-y-4">
                            <div
                                v-for="(step, idx) in selectedRole.steps"
                                :key="step.id"
                                class="border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden transition-all duration-200"
                                :class="expandedStepId === step.id ? 'ring-2 ring-orange-500/30 dark:ring-orange-500/20' : ''"
                            >
                                <!-- Step Header Bar -->
                                <button
                                    @click="toggleStep(step.id)"
                                    class="w-full p-4 sm:p-5 text-left flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 dark:bg-gray-800/30 dark:hover:bg-gray-800/60 transition"
                                >
                                    <div class="flex items-center space-x-3.5">
                                        <div class="w-8 h-8 rounded-xl bg-orange-500 text-white flex items-center justify-center text-xs font-black shrink-0">
                                            {{ idx + 1 }}
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-gray-900 dark:text-white">{{ step.title }}</h5>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">{{ step.desc }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 shrink-0">
                                        <span class="text-xs font-medium text-orange-600 dark:text-orange-400 hidden sm:inline">
                                            {{ expandedStepId === step.id ? 'Tutup Detail' : 'Buka Detail' }}
                                        </span>
                                        <svg
                                            class="w-4 h-4 text-gray-400 transition-transform"
                                            :class="expandedStepId === step.id ? 'rotate-180' : ''"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Step Expanded Detail Panel -->
                                <div v-show="expandedStepId === step.id" class="p-5 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                                            <span class="font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">📥 Input Data</span>
                                            <p class="text-slate-700 dark:text-slate-300 font-medium">{{ step.input }}</p>
                                        </div>
                                        <div class="p-3.5 rounded-xl bg-orange-50 dark:bg-orange-950/30 border border-orange-100 dark:border-orange-900/40">
                                            <span class="font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider block mb-1">⚙️ Proses & Tindakan</span>
                                            <p class="text-orange-900 dark:text-orange-200 font-medium">{{ step.action }}</p>
                                        </div>
                                        <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block mb-1">📤 Output Sistem</span>
                                            <p class="text-emerald-900 dark:text-emerald-200 font-medium">{{ step.output }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-2">
                                        <span class="text-xs text-gray-400">Modul Terkait: <strong class="text-gray-700 dark:text-gray-300">{{ step.moduleName }}</strong></span>
                                        <Link
                                            v-if="step.moduleUrl"
                                            :href="step.moduleUrl"
                                            class="text-xs font-bold text-orange-600 hover:text-orange-700 dark:text-orange-400 inline-flex items-center space-x-1"
                                        >
                                            <span>Buka Halaman Modul</span>
                                            <span>→</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- VIEW 4: MATRIKS HAK AKSES MODUL (RACI) -->
            <!-- =================================================================== -->
            <div v-if="activeView === 'matrix'" class="space-y-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm overflow-hidden">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Matriks Hak Akses & Kewenangan Antar-Role (RACI)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pemetaan kewenangan akses data pada 13 modul fungsional sistem App-MKT Indonesia.</p>
                    </div>

                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                                    <th class="p-3 font-black text-gray-700 dark:text-gray-300">Modul Fungsional</th>
                                    <th class="p-3 font-bold text-purple-600 dark:text-purple-400 text-center">Webmaster</th>
                                    <th class="p-3 font-bold text-blue-600 dark:text-blue-400 text-center">Admin</th>
                                    <th class="p-3 font-bold text-emerald-600 dark:text-emerald-400 text-center">Finance</th>
                                    <th class="p-3 font-bold text-rose-600 dark:text-rose-400 text-center">Rescue/Relawan</th>
                                    <th class="p-3 font-bold text-amber-600 dark:text-amber-400 text-center">Mitra SAR</th>
                                    <th class="p-3 font-bold text-teal-600 dark:text-teal-400 text-center">Medis</th>
                                    <th class="p-3 font-bold text-orange-600 dark:text-orange-400 text-center">Donatur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                                <tr v-for="(row, idx) in accessMatrix" :key="idx" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">{{ row.module }}</td>
                                    
                                    <!-- Webmaster -->
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                            {{ row.wm }}
                                        </span>
                                    </td>

                                    <!-- Admin -->
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                            {{ row.adm }}
                                        </span>
                                    </td>

                                    <!-- Finance -->
                                    <td class="p-3 text-center">
                                        <span :class="row.fin.includes('Full') ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'text-gray-400'" class="px-2 py-0.5 rounded-md text-[10px] font-bold">
                                            {{ row.fin }}
                                        </span>
                                    </td>

                                    <!-- Rescue / Relawan -->
                                    <td class="p-3 text-center">
                                        <span :class="row.res.includes('Full') ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'text-gray-400'" class="px-2 py-0.5 rounded-md text-[10px] font-bold">
                                            {{ row.res }}
                                        </span>
                                    </td>

                                    <!-- Mitra -->
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] text-gray-500 dark:text-gray-400 font-semibold">
                                            {{ row.mit }}
                                        </span>
                                    </td>

                                    <!-- Medis -->
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] text-gray-500 dark:text-gray-400 font-semibold">
                                            {{ row.med }}
                                        </span>
                                    </td>

                                    <!-- Donatur -->
                                    <td class="p-3 text-center">
                                        <span :class="row.don.includes('Self') ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300' : 'text-gray-400'" class="px-2 py-0.5 rounded-md text-[10px] font-bold">
                                            {{ row.don }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    .space-y-6, .space-y-6 * {
        visibility: visible;
    }
    .space-y-6 {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
