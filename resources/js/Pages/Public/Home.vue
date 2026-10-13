<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

// Vite static images
import heroRescueImg from '../../../images/hero_rescue.jpg';
import aboutMktImg from '../../../images/about_mkt.jpg';
import bloodDonorImg from '../../../images/blood_donor.jpg';
import pillarPreImg from '../../../images/pillar_pre.jpg';
import pillarDuringImg from '../../../images/pillar_during.jpg';
import pillarPostImg from '../../../images/pillar_post.jpg';

const props = defineProps({
    latestNews: {
        type: Array,
        default: () => [],
    },
    partners: {
        type: Array,
        default: () => [],
    },
    ecosystems: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

// Kategori Filter Mitra Nyata
const selectedCategory = ref('Semua');
const partnerCategories = ['Semua', 'Basarnas', 'BPBD', 'PMI', 'Rumah Sakit', 'Tim Rescue', 'Filantropi'];

const getCategoryIcon = (category) => {
    switch (category) {
        case 'Basarnas': return '⚓';
        case 'BPBD': return '🏛️';
        case 'PMI': return '🩸';
        case 'Rumah Sakit': return '🏥';
        case 'Filantropi': return '🤝';
        case 'Tim Rescue':
        default: return '🚨';
    }
};

const getCategoryColor = (category) => {
    switch (category) {
        case 'Basarnas': return 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20';
        case 'BPBD': return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
        case 'PMI': return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20';
        case 'Rumah Sakit': return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
        case 'Filantropi': return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20';
        default: return 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20';
    }
};

const filteredPartners = computed(() => {
    if (!props.partners || props.partners.length === 0) return [];
    if (selectedCategory.value === 'Semua') {
        return props.partners;
    }
    return props.partners.filter(p => p.category === selectedCategory.value);
});

// Klaster Kolaborasi Lintas Lembaga
const activeCluster = ref('sar');
const collaborationClusters = [
    {
        id: 'sar',
        name: 'Klaster SAR & Evakuasi Cepat',
        lead: 'BASARNAS & Tim Rescue Lapangan',
        badge: 'Operasi Siaga 24/7',
        color: 'from-orange-600 to-amber-600',
        icon: '🚨',
        desc: 'Sinergi komando penyisiran medan bencana, evakuasi warga terdampak, pengerahan perahu karet RIB, drone thermal, dan teknik pertolongan di ketinggian maupun perairan.',
        roles: [
            'Operasi Search and Rescue (SAR) satu rencana operasi terpadu',
            'Pengerahan potensi SAR dan relawan rescuer bersertifikasi',
            'Dukungan armada perahu motor dan peralatan pertolongan medan ekstrem'
        ]
    },
    {
        id: 'gov',
        name: 'Klaster Komando Kebencanaan & Pemda',
        lead: 'BPBD Provinsi & Kabupaten/Kota',
        badge: 'Regulasi & Koordinasi Posko',
        color: 'from-blue-600 to-cyan-600',
        icon: '🏛️',
        desc: 'Penyelarasan kebijakan darurat bencana, pengkajian cepat (rapid assessment), pembagian zona terdampak, dan integrasi data posko terpadu bersama pemerintah daerah.',
        roles: [
            'Pemberlakuan status tanggap darurat dan komando wilayah',
            'Sinkronisasi data korban dan klaster pengungsian resmi',
            'Fasilitasi koridor bantuan kemanusiaan aman dan teratur'
        ]
    },
    {
        id: 'medis',
        name: 'Klaster Medis, Darah & Kesehatan',
        lead: 'PMI & Jaringan Rumah Sakit Rujukan',
        badge: 'Gawat Darurat & Bank Darah',
        color: 'from-rose-600 to-pink-600',
        icon: '🩸',
        desc: 'Pelayanan medis darurat di garis depan, pendirian tenda triase bencana, mobilisasi donor darah siaga on-demand, dan rujukan cepat ke fasilitas rumah sakit terdekat.',
        roles: [
            'Penyediaan kantong darah siaga untuk korban luka dan operasi darurat',
            'Penerjunan dokter darurat, perawat trauma, dan ambulans siaga',
            'Penyediaan posko kesehatan umum dan obat-obatan pasca-bencana'
        ]
    },
    {
        id: 'filantropi',
        name: 'Klaster Logistik & Filantropi',
        lead: 'Konsorsium ZISWAF, CSR Swasta & Donatur',
        badge: 'Transparansi Bantuan',
        color: 'from-emerald-600 to-teal-600',
        icon: '🤝',
        desc: 'Mobilisasi donasi publik, penyediaan sembako, pendirian Dapur Umum higienis, penyediaan air bersih darurat, serta pelaporan keuangan terbuka berbasis jurnal akuntansi.',
        roles: [
            'Pendistribusian makanan siap saji dari Dapur Umum Terpadu',
            'Penyediaan tenda pengungsian, selimut, dan kebutuhan bayi/lansia',
            'Pertanggungjawaban keuangan donasi publik berstandar Buku Besar & Neraca'
        ]
    }
];

// Alur Kolaborasi Kerja Sama Saat Bencana Terjadi
const workflowSteps = [
    {
        step: '01',
        title: 'Deteksi & Peringatan Terpadu',
        timeframe: 'Menit 0 - 15',
        desc: 'Pusdalops MKT menerima peringatan gempa/cuaca ekstrem BMKG dan laporan warga, memvalidasi koordinat kejadian ke posko terdekat.',
        icon: '📡'
    },
    {
        step: '02',
        title: 'Aktivasi Komando Lintas Lembaga',
        timeframe: 'Menit 15 - 30',
        desc: 'Koordinasi lintas instansi (Basarnas, BPBD, PMI, Tim Rescue) diaktifkan melalui radio dan sistem digital untuk menyepakati rencana operasi bersama.',
        icon: '⚡'
    },
    {
        step: '03',
        title: 'Penyelamatan & Evakuasi Gabungan',
        timeframe: 'Fase Emas (Golden Time)',
        desc: 'Rescuer gabungan terjun ke titik krisis melakukan evakuasi korban dengan perahu karet, tali temali, dan peralatan pencarian terpadu.',
        icon: '🛶'
    },
    {
        step: '04',
        title: 'Posko Medis, Bank Darah & Dapur',
        timeframe: 'Jam 2 - 12',
        desc: 'PMI dan RS mendirikan posko triase darurat, donor darah disiapkan, dan Dapur Umum Mitra Peduli membagikan makanan hangat bagi pengungsi.',
        icon: '🍲'
    },
    {
        step: '05',
        title: 'Rehabilitasi & Laporan Terbuka',
        timeframe: 'Pasca-Bencana',
        desc: 'Pendampingan trauma healing anak-anak, pembersihan fasilitas publik, dan penerbitan laporan pertanggungjawaban donasi secara terbuka.',
        icon: '📊'
    }
];

const pillarsList = [
    {
        id: 'pre',
        title: 'Pra-Bencana (Mitigasi & Kesiapsiagaan)',
        sub: 'Bersiap Sebelum Musibah Terjadi',
        desc: 'Gladi posko bersama BPBD, pelatihan SAR gabungan dengan Basarnas, edukasi simulasi sekolah, dan pendataan relawan donor darah on-call.',
        img: pillarPreImg,
        color: 'from-blue-600 to-cyan-600',
        badge: 'Mitigasi Multi-Pihak'
    },
    {
        id: 'during',
        title: 'Saat Bencana (Tanggap Darurat Terpadu)',
        sub: 'Satu Komando Penyelamatan Jiwa',
        desc: 'Pengerahan cepat Tim Rescue gabungan ke titik musibah, posko medis lapangan bersama PMI/RS, evakuasi perahu karet, dan distribusi dapur umum.',
        img: pillarDuringImg,
        color: 'from-rose-600 to-orange-600',
        badge: 'Respon Darurat 24/7'
    },
    {
        id: 'post',
        title: 'Pasca-Bencana (Pemulihan & Rekonstruksi)',
        sub: 'Membangun Kembali Harapan',
        desc: 'Rehabilitasi sarana publik dan MCK darurat, trauma healing psikososial, penyaluran material rekonstruksi, dan audit donasi terbuka.',
        img: pillarPostImg,
        color: 'from-emerald-600 to-teal-600',
        badge: 'Rehabilitasi & Transparansi'
    }
];

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <PublicLayout 
        title="Beranda - Kolaborasi Lintas Lembaga Kemanusiaan & Kebencanaan MKT"
        description="Pusat kolaborasi terpadu penanggulangan bencana: sinergi Basarnas, BPBD, PMI, Rumah Sakit, Tim Rescue, dan relawan kemanusiaan Indonesia."
    >
        <template #default="{ openCtaModal, openShareModal, currentLang }">
            
            <!-- HERO SECTION: KOLABORASI LINTAS LEMBAGA -->
            <section class="relative overflow-hidden min-h-[600px] sm:min-h-[720px] flex items-center justify-center py-12 sm:py-20 lg:py-24 bg-amber-50/40 dark:bg-slate-950 transition-colors border-b border-orange-500/10 dark:border-slate-800/80">
                <!-- Background Image Layer -->
                <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
                    <img 
                        :src="heroRescueImg" 
                        alt="MKT Rescue Operation Background" 
                        class="w-full h-full object-cover object-center scale-105 transition-transform duration-1000 ease-out opacity-85 dark:opacity-40" 
                    />
                    <div class="block dark:hidden absolute inset-0 bg-gradient-to-r from-white via-white/92 to-white/40"></div>
                    <div class="block dark:hidden absolute inset-0 bg-gradient-to-t from-white via-transparent to-white/60"></div>
                    <div class="block dark:hidden absolute inset-0 bg-orange-500/5 mix-blend-multiply"></div>
                    <div class="hidden dark:block absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/95 to-slate-900/60"></div>
                    <div class="hidden dark:block absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-slate-950/80"></div>
                </div>

                <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        
                        <!-- Left Headline & CTA Column -->
                        <div class="lg:col-span-7 space-y-6 text-left">
                            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-black bg-white/95 dark:bg-slate-900/90 backdrop-blur-md text-orange-600 dark:text-orange-400 border border-orange-500/30 shadow-md shadow-orange-500/10">
                                <span class="animate-pulse">🤝</span>
                                <span>SINERGI LINTAS LEMBAGA KEMANUSIAAN & KEBENCANAAN</span>
                            </div>

                            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-950 dark:text-white tracking-tight leading-[1.12]">
                                Satu Komando Kemanusiaan,<br />
                                <span class="bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 dark:from-orange-400 dark:via-amber-300 dark:to-orange-400 bg-clip-text text-transparent">
                                    Sinergi Menghadapi Bencana
                                </span>
                            </h1>

                            <p class="text-sm sm:text-base lg:text-lg text-slate-700 dark:text-slate-200 leading-relaxed font-medium max-w-2xl">
                                Kolaborasi Mitra Peduli mengintegrasikan jaringan kerja sama antar lembaga: <strong>BASARNAS, BPBD, PMI, Rumah Sakit, Tim Rescue, Korporasi CSR, dan Filantropi</strong>. Bersatu dalam satu ekosistem tanggap darurat, evakuasi SAR terpadu, bank darah siaga, dan transparansi donasi kemanusiaan.
                            </p>

                            <!-- Hero Button Actions -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                                <button
                                    @click="openCtaModal('mitra')"
                                    class="px-7 py-4 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 text-white font-black rounded-2xl shadow-xl shadow-orange-500/30 hover:shadow-orange-500/40 active:scale-95 transition-all text-center flex items-center justify-center space-x-2.5 text-sm sm:text-base border border-white/20"
                                >
                                    <span>🤝 Gabung Kemitraan Lembaga</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>

                                <button
                                    @click="openCtaModal('relawan')"
                                    class="px-7 py-4 bg-white/90 dark:bg-white/10 hover:bg-white dark:hover:bg-white/20 backdrop-blur-md border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-400 font-bold rounded-2xl shadow-md hover:shadow-lg active:scale-95 transition-all text-center flex items-center justify-center space-x-2.5 text-sm sm:text-base"
                                >
                                    <span>🚑 Relawan & Potensi SAR</span>
                                </button>
                            </div>

                            <!-- Live Collaborative Stats Counter Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4 pt-6 border-t border-slate-300/80 dark:border-white/15">
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/70 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-sm">
                                    <span class="text-xl sm:text-2xl font-black text-orange-600 dark:text-orange-400 block">{{ stats?.partners || '6' }} Lembaga</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Mitra Lembaga Terhubung</span>
                                </div>
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/70 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-sm">
                                    <span class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 block">{{ stats?.ecosystems || '1' }} Ekosistem</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Kolaborasi Sinergi Wilayah</span>
                                </div>
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/70 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-sm">
                                    <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 block">{{ stats?.operations || '42+' }} Operasi</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Giat SAR & Bencana Bersama</span>
                                </div>
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/70 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-sm">
                                    <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 block">{{ stats?.volunteers || '1,250+' }}</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Personel & Relawan Terpadu</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Highlight Tactical Cards Column -->
                        <div class="lg:col-span-5 space-y-4">
                            <!-- Card 1: Koordinasi Satu Komando -->
                            <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/85 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl hover:border-orange-500/50 hover:-translate-y-0.5 transition-all group">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 border border-orange-500/20 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                        ⚓
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Komando Gabungan SAR & BPBD</h3>
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-orange-500/15 text-orange-600 dark:text-orange-400">Terintegrasi</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                            Sinergi pengerahan potensi SAR Basarnas dan komando bencana daerah BPBD. Pemantauan GPS real-time, armada perahu karet, dan komunikasi frekuensi darurat.
                                        </p>
                                        <Link :href="route('public.partners')" class="inline-flex items-center text-xs font-bold text-orange-600 dark:text-orange-400 mt-2 hover:underline">
                                            <span>Lihat Integrasi Lembaga &rarr;</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Jaringan Medis & Bank Darah -->
                            <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/85 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl hover:border-rose-500/50 hover:-translate-y-0.5 transition-all group">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                        🩸
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Jejaring Medis & Bank Darah PMI/RS</h3>
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-600 dark:text-rose-400">Siaga 24/7</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                            Kolaborasi penyediaan kantong darah on-demand bersama Palang Merah Indonesia dan rujukan korban ke rumah sakit rujukan darurat bencana.
                                        </p>
                                        <button @click="openCtaModal('relawan')" class="inline-flex items-center text-xs font-bold text-rose-600 dark:text-rose-400 mt-2 hover:underline">
                                            <span>Daftar Pendonor & Medis &rarr;</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Konsorsium Logistik & Filantropi -->
                            <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/85 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl hover:border-emerald-500/50 hover:-translate-y-0.5 transition-all group">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                        🤝
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Konsorsium Donasi & Dapur Umum</h3>
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">Transparan</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                            Penyaluran logistik tanpa rantai birokrasi berbelit. Didukung pelaporan akuntansi jurnal umum dan neraca terbuka untuk para mitra filantropi & CSR.
                                        </p>
                                        <Link :href="route('public.about')" class="inline-flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-2 hover:underline">
                                            <span>Audit & Transparansi Keuangan &rarr;</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- SECTION: 4 KLASTER KOLABORASI LEMBAGA KEMANUSIAAN -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center max-w-3xl mx-auto space-y-3">
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                            🛡️ MODEL KOLABORASI MULTI-PIHAK
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            4 Klaster Lembaga yang Bersinergi dalam Ekosistem MKT
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400">
                            Penanganan musibah tidak dapat berdiri sendiri. Setiap instansi dan organisasi mengambil peran vital sesuai kompetensi dan otoritas resminya.
                        </p>
                    </div>

                    <!-- 4 Clusters Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div 
                            v-for="cluster in collaborationClusters" 
                            :key="cluster.id"
                            class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/90 dark:border-slate-800 flex flex-col justify-between hover:shadow-xl hover:border-orange-500/40 transition-all duration-300 group"
                        >
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-4xl p-2.5 rounded-2xl bg-white dark:bg-slate-900 shadow-sm border border-slate-200/80 dark:border-slate-800">
                                        {{ cluster.icon }}
                                    </span>
                                    <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ cluster.badge }}
                                    </span>
                                </div>

                                <div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                        {{ cluster.name }}
                                    </h3>
                                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400 block mt-0.5">
                                        {{ cluster.lead }}
                                    </span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                                        {{ cluster.desc }}
                                    </p>
                                </div>

                                <div class="space-y-1.5 pt-2 border-t border-slate-200/80 dark:border-slate-800/80">
                                    <span class="text-[11px] font-extrabold uppercase text-slate-400 block">Peran Kemitraan:</span>
                                    <ul class="space-y-1 text-xs text-slate-700 dark:text-slate-300 font-medium">
                                        <li v-for="(r, idx) in cluster.roles" :key="idx" class="flex items-start space-x-1.5">
                                            <span class="text-orange-500 text-sm leading-none shrink-0">•</span>
                                            <span>{{ r }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="pt-5 mt-4 border-t border-slate-200/60 dark:border-slate-800/60">
                                <Link :href="route('public.partners')" class="text-xs font-extrabold text-orange-600 dark:text-orange-400 hover:underline inline-flex items-center space-x-1">
                                    <span>Eksplorasi Profil Lembaga &rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: SHOWCASE LEMBAGA MITRA & EKOSISTEM NYATA -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div class="space-y-2 max-w-2xl">
                            <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 uppercase tracking-wider">
                                🤝 JEJARING MITRA RESMI TERDAFTAR
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                Lembaga & Organisasi yang Telah Berkolaborasi
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                                Instansi SAR, badan penanggulangan bencana, unit transfusi darah, rumah sakit darurat, dan tim rescue kampus yang aktif dalam ekosistem kemanusiaan MKT.
                            </p>
                        </div>

                        <!-- Category Filter Pills -->
                        <div class="flex flex-wrap gap-1.5 shrink-0">
                            <button
                                v-for="cat in partnerCategories"
                                :key="cat"
                                @click="selectedCategory = cat"
                                :class="[
                                    selectedCategory === cat
                                        ? 'bg-orange-500 text-white font-extrabold shadow-sm'
                                        : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 font-semibold',
                                    'px-3 py-1.5 rounded-xl text-xs transition-all'
                                ]"
                            >
                                {{ cat }}
                            </button>
                        </div>
                    </div>

                    <!-- Ekosistem Terpadu Highlight Banner (Jika ada data Ekosistem) -->
                    <div v-if="ecosystems && ecosystems.length > 0" class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-orange-500/10 via-amber-500/5 to-transparent border border-orange-500/20 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-orange-500/20 shrink-0">
                                🌐
                            </div>
                            <div>
                                <span class="text-[11px] font-black uppercase text-orange-600 dark:text-orange-400 tracking-wider">Ekosistem Kemanusiaan Wilayah</span>
                                <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                                    {{ ecosystems[0].name }}
                                </h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-xl">
                                    {{ ecosystems[0].tagline || 'Konsorsium gabungan organisasi mahasiswa, korps relawan KSR PMI, SAR kampus, dan posko medis regional.' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <Link 
                                :href="route('public.ecosystem.show', ecosystems[0].slug || ecosystems[0].id)"
                                class="w-full md:w-auto px-5 py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all text-center"
                            >
                                <span>Buka Halaman Ekosistem</span> &rarr;
                            </Link>
                        </div>
                    </div>

                    <!-- Partner Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="partner in filteredPartners"
                            :key="partner.id"
                            class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-orange-500/50 transition-all duration-300 flex flex-col justify-between group"
                        >
                            <div class="space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0">
                                            <img 
                                                v-if="partner.logo_path" 
                                                :src="partner.logo_path" 
                                                :alt="partner.name" 
                                                class="w-full h-full object-contain p-1"
                                                @error="(e) => e.target.style.display = 'none'"
                                            />
                                            <span v-else class="text-2xl">{{ getCategoryIcon(partner.category) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md border" :class="getCategoryColor(partner.category)">
                                                {{ partner.category }}
                                            </span>
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white mt-1 leading-snug line-clamp-2 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                                {{ partner.name }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed">
                                    {{ partner.description || partner.tagline || 'Lembaga mitra resmi dalam ekosistem penanggulangan bencana dan kemanusiaan terpadu MKT.' }}
                                </p>

                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-[11px]">
                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950/60">
                                        <span class="text-slate-400 block font-bold">Personel:</span>
                                        <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ partner.volunteers_count || partner.personnel_count || 0 }} Anggota</span>
                                    </div>
                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950/60">
                                        <span class="text-slate-400 block font-bold">Kesiapan:</span>
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ partner.readiness_status || 'Siaga Bencana' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <Link 
                                    :href="route('public.partner.show', partner.slug || partner.id)"
                                    class="text-xs font-black text-orange-600 dark:text-orange-400 hover:underline inline-flex items-center space-x-1"
                                >
                                    <span>Profil & Landing Page Lembaga</span>
                                    <span>&rarr;</span>
                                </Link>
                                <span class="text-[10px] text-slate-400 font-semibold">{{ partner.code }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Button for Partners -->
                    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                        <h4 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Apakah Lembaga Anda Siap Berkolaborasi?
                        </h4>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-xl mx-auto">
                            Buka akses landing page resmi lembaga Anda, kelola database anggota rescuer, dan integrasikan peralatan tanggap darurat dalam satu sistem terpadu.
                        </p>
                        <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                            <button
                                @click="openCtaModal('mitra')"
                                class="px-6 py-3 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white text-xs sm:text-sm font-extrabold shadow-md hover:shadow-lg transition-all"
                            >
                                <span>🤝 Daftarkan Kemitraan Lembaga Sekarang</span>
                            </button>
                            <Link
                                :href="route('public.partner-terms')"
                                class="px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-bold transition-all"
                            >
                                <span>📄 Baca Syarat & Ketentuan Kemitraan</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: ALUR KOORDINASI KERJA SAMA SAAT BENCANA (OPERATIONAL FLOW) -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center max-w-3xl mx-auto space-y-3">
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider">
                            ⚡ SATU KOMANDO & RESPON CEPAT
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            Alur Kolaborasi Saat Terjadi Musibah / Bencana
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400">
                            Tata kelola penanganan darurat tanpa hambatan birokrasi, menjaga kecepatan pengerahan tim dan keselamatan jiwa korban.
                        </p>
                    </div>

                    <!-- Workflow Timeline Horizontal / Stack -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 relative">
                        <div 
                            v-for="(w, idx) in workflowSteps" 
                            :key="w.step"
                            class="p-5 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/90 dark:border-slate-800 flex flex-col justify-between hover:border-orange-500/50 hover:shadow-lg transition-all relative group"
                        >
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-black text-orange-600 dark:text-orange-400 font-mono">{{ w.step }}</span>
                                    <span class="text-2xl">{{ w.icon }}</span>
                                </div>
                                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-orange-500/10 text-orange-600 dark:text-orange-400 block w-fit">
                                    {{ w.timeframe }}
                                </span>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white leading-snug">
                                    {{ w.title }}
                                </h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                    {{ w.desc }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: 3 PILAR SIKLUS KEBENCANAAN OVERVIEW -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center max-w-3xl mx-auto space-y-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                            🛡️ SIKLUS PENANGGULANGAN BENCANA
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            3 Tahap Terpadu Penanganan Risiko Musibah
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400">
                            Kolaborasi aktif di seluruh siklus bencana untuk memperkecil dampak kerugian dan mempercepat kebangkitan masyarakat.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                        <div 
                            v-for="p in pillarsList" 
                            :key="p.id"
                            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
                        >
                            <div class="relative h-48 overflow-hidden bg-slate-900">
                                <img :src="p.img" :alt="p.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                                <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-black uppercase text-white bg-gradient-to-r shadow-sm" :class="p.color">
                                    {{ p.badge }}
                                </span>
                            </div>
                            <div class="p-6 space-y-3 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ p.title }}</h3>
                                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400 block mt-0.5">{{ p.sub }}</span>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">{{ p.desc }}</p>
                                </div>
                                <Link :href="route('public.partners')" class="text-xs font-bold text-orange-600 dark:text-orange-400 inline-flex items-center space-x-1 hover:underline pt-2">
                                    <span>Lihat Peran Lembaga dalam Siklus Ini &rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-2">
                        <Link :href="route('public.partners')" class="inline-flex items-center space-x-2 px-6 py-3 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 font-extrabold text-xs sm:text-sm hover:bg-orange-500/20 transition-all">
                            <span>Eksplorasi Sinergi Lengkap di Halaman Mitra & Kolaborasi</span>
                            <span>&rarr;</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- SECTION: BERITA & PUBLIKASI AKSI KOLABORASI LAPANGAN -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                        <div class="space-y-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                                📰 PUBLIKASI & KABAR TERKINI
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                Berita, Liputan Bersama & Edukasi Kemanusiaan
                            </h2>
                        </div>
                        <Link :href="route('public.news')" class="inline-flex items-center space-x-1.5 text-xs sm:text-sm font-bold text-orange-600 dark:text-orange-400 hover:underline shrink-0">
                            <span>Lihat Semua Berita & Artikel</span>
                            <span>&rarr;</span>
                        </Link>
                    </div>

                    <!-- News Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            v-for="item in latestNews"
                            :key="item.id"
                            class="bg-slate-50 dark:bg-slate-950 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
                        >
                            <Link :href="route('public.news.show', item.slug || item.id)" class="relative h-48 overflow-hidden bg-slate-800 block">
                                <img :src="item.image_url || heroRescueImg" :alt="item.title" @error="(e) => e.target.src = heroRescueImg" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
                                <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-black uppercase text-white bg-orange-600 shadow-sm">
                                    {{ item.category }}
                                </span>
                            </Link>
                            <div class="p-5 space-y-3 flex-1 flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                                        <span>✍️ {{ item.author || 'Humas MKT' }}</span>
                                        <span>{{ formatDate(item.published_at || item.created_at) }}</span>
                                    </div>
                                    <Link :href="route('public.news.show', item.slug || item.id)">
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug line-clamp-2 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                            {{ item.title }}
                                        </h3>
                                    </Link>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                        {{ item.content }}
                                    </p>
                                </div>
                                <Link :href="route('public.news.show', item.slug || item.id)" class="text-xs font-bold text-orange-600 dark:text-orange-400 inline-flex items-center space-x-1 hover:underline pt-2">
                                    <span>Baca Selengkapnya</span>
                                    <span>&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: PIAGAM INTEGRITAS & NILAI KEMITRAAN MKT -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center max-w-3xl mx-auto space-y-3">
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                            ⭐ NILAI DASAR KEMITRAAN
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            Prinsip Nilai Kolaborasi Mitra Peduli
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400">
                            Fondasi moral dan operasional yang menyatukan seluruh lembaga mitra di lapangan demi kemanusiaan.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-3 shadow-sm">
                            <span class="text-3xl">🕊️</span>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Netral & Universal</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                Menolong korban tanpa membedakan suku, agama, ras, maupun latar belakang politik. Murni berlandaskan kemanusiaan.
                            </p>
                        </div>

                        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-3 shadow-sm">
                            <span class="text-3xl">⚡</span>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Kecepatan Lapangan</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                Koordinasi terpadu memangkas hambatan birokrasi, memungkinkan bantuan dan perahu penyelamat tiba lebih cepat.
                            </p>
                        </div>

                        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-3 shadow-sm">
                            <span class="text-3xl">📖</span>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Akuntabilitas Finansial</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                Setiap rupiah donasi tercatat rapi dalam jurnal umum, buku besar, dan neraca keuangan yang dapat diaudit publik.
                            </p>
                        </div>

                        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-3 shadow-sm">
                            <span class="text-3xl">🛡️</span>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Keselamatan Relawan</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                Penerapan standar keselamatan kerja (K3) SAR ketat dengan perlengkapan pelampung, helm, dan asuransi proteksi diri.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: CALL TO ACTION (CTA BANNER) -->
            <section class="py-16 sm:py-20 bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 text-white relative overflow-hidden">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center space-y-6 relative z-10">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-white/20 text-white uppercase tracking-wider backdrop-blur-md">
                        ✨ BERSATU DALAM AKSI KEMANUSIAAN
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                        Bersama Membangun Indonesia Tangguh & Sigap Bencana
                    </h2>
                    <p class="text-sm sm:text-base text-white/90 max-w-2xl mx-auto leading-relaxed font-medium">
                        Kekuatan terbesar saat bencana adalah gotong royong lintas lembaga. Hubungkan institusi Anda, salurkan donasi, atau bergabunglah sebagai relawan siaga hari ini.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                        <button
                            @click="openCtaModal('mitra')"
                            class="w-full sm:w-auto px-8 py-4 bg-white text-orange-600 hover:bg-orange-50 font-black rounded-2xl shadow-xl active:scale-95 transition-all text-sm sm:text-base"
                        >
                            🤝 Daftarkan Lembaga Mitra
                        </button>
                        <button
                            @click="openCtaModal('donatur')"
                            class="w-full sm:w-auto px-8 py-4 bg-orange-950/40 hover:bg-orange-950/60 border border-white/30 text-white font-bold rounded-2xl backdrop-blur-md active:scale-95 transition-all text-sm sm:text-base"
                        >
                            💖 Salurkan Donasi Terbuka
                        </button>
                        <button
                            @click="openCtaModal('relawan')"
                            class="w-full sm:w-auto px-8 py-4 bg-orange-950/20 hover:bg-orange-950/40 border border-white/20 text-white font-bold rounded-2xl backdrop-blur-md active:scale-95 transition-all text-sm sm:text-base"
                        >
                            🚑 Daftar Relawan & Donor
                        </button>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
