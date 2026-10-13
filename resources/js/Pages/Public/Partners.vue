<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import pillarPreImg from '../../../images/pillar_pre.jpg';
import pillarDuringImg from '../../../images/pillar_during.jpg';
import pillarPostImg from '../../../images/pillar_post.jpg';

const props = defineProps({
    partners: {
        type: Array,
        default: () => [],
    },
    ecosystems: {
        type: Array,
        default: () => [],
    },
    mktProfile: {
        type: Object,
        default: null,
    },
    defaultTab: {
        type: String,
        default: 'semua',
    },
});

// Main Directory Tab: 'semua' | 'mitra' | 'ekosistem' | 'pilar'
const activeMainTab = ref(props.defaultTab === 'ekosistem' ? 'ekosistem' : (props.defaultTab === 'pilar' ? 'pilar' : 'semua'));

onMounted(() => {
    // If URL has hash or query, sync tab
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab && ['semua', 'mitra', 'ekosistem', 'pilar'].includes(tab)) {
            activeMainTab.value = tab;
        }
    }
});

// Partner Search & Filter
const selectedCategory = ref('Semua');
const partnerCategories = ['Semua', 'Tim Rescue', 'Basarnas', 'BPBD', 'PMI', 'Rumah Sakit', 'Filantropi'];
const partnerSearch = ref('');

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

const displayPartners = computed(() => {
    let list = props.partners && props.partners.length > 0 ? props.partners : defaultPartners;
    if (selectedCategory.value !== 'Semua') {
        list = list.filter(p => p.category === selectedCategory.value);
    }
    if (partnerSearch.value.trim()) {
        const q = partnerSearch.value.toLowerCase();
        list = list.filter(p => 
            (p.name && p.name.toLowerCase().includes(q)) ||
            (p.code && p.code.toLowerCase().includes(q)) ||
            (p.description && p.description.toLowerCase().includes(q)) ||
            (p.category && p.category.toLowerCase().includes(q))
        );
    }
    return list;
});

// Ecosystem Search & Filter
const ecosystemSearch = ref('');

const filteredEcosystems = computed(() => {
    if (!props.ecosystems || props.ecosystems.length === 0) return [];
    if (!ecosystemSearch.value.trim()) return props.ecosystems;
    const q = ecosystemSearch.value.toLowerCase();
    return props.ecosystems.filter(e => 
        (e.name && e.name.toLowerCase().includes(q)) ||
        (e.code && e.code.toLowerCase().includes(q)) ||
        (e.region && e.region.toLowerCase().includes(q)) ||
        (e.lead_institution && e.lead_institution.toLowerCase().includes(q))
    );
});

const totalPartnersInAllEcosystems = computed(() => {
    return props.ecosystems.reduce((acc, e) => acc + (e.partners_count || (e.partners ? e.partners.length : 0)), 0);
});

// 3 Pilar Siklus Kebencanaan
const activePillar = ref('during');

const pillars = [
    {
        id: 'pre',
        title: '1. Pra-Bencana (Mitigasi & Kesiapsiagaan)',
        tagline: 'Mencegah Risiko, Memetakan Kerentanan, Melatih Kesiapan Warga',
        badge: 'Tahap Pencegahan (Mitigasi)',
        badgeColor: 'bg-blue-600',
        coverImg: pillarPreImg,
        desc: 'Fase pra-bencana berfokus pada pembangunan ketahanan masyarakat sebelum musibah terjadi melalui edukasi simulasi evakuasi, penyusunan database relawan donor darah, pemetaan daerah rawan bencana (Desa Tangguh Bencana), serta sertifikasi keahlian teknis personel Rescue SAR.',
        activities: [
            { icon: '🗺️', title: 'Pemetaan GIS Rawan Bencana', desc: 'Identifikasi titik banjir, lereng longsor, dan patahan gempa menggunakan koordinat geospasial.' },
            { icon: '👨‍🏫', title: 'Sosialisasi & Simulasi Sekolah/Warga', desc: 'Pelatihan simulasi gempa dan pertolongan pertama di sekolah, pondok pesantren, dan permukiman.' },
            { icon: '🩸', title: 'Pendataan Bank Darah Mandiri', desc: 'Perekrutan relawan pendonor darah on-call per kecamatan untuk kesiapan musibah massal.' },
            { icon: '📦', title: 'Logistik Penyangga Darurat', desc: 'Penyimpanan stok tenda darurat, perahu karet RIB, pelampung, genset, dan paket medis di posko.' }
        ]
    },
    {
        id: 'during',
        title: '2. Saat Bencana (Tanggap Darurat & Penyelamatan)',
        tagline: 'Respon Cepat 24 Jam, Evakuasi Korban, Bantuan Medis & Dapur Lapangan',
        badge: 'Tahap Penyelamatan (Emergency)',
        badgeColor: 'bg-rose-600',
        coverImg: pillarDuringImg,
        desc: 'Fase tanggap darurat diaktifkan seketika musibah terjadi. Melalui Pusat Komando Kolaborasi Mitra Peduli, tim Search and Rescue (SAR) diterjunkan bersama Basarnas dan BPBD untuk evakuasi korban, pendirian posko kesehatan darurat, dan penyaluran makanan siap saji dari Dapur Umum.',
        activities: [
            { icon: '🚨', title: 'Aktivasi Tim SAR & Evakuasi Cepat', desc: 'Penyisiran lokasi bencana menggunakan perahu karet, perahu motor, dan drone pencari korban.' },
            { icon: '🏥', title: 'Posko Kesehatan & Medis Lapangan', desc: 'Pelayanan dokter dan perawat darurat untuk penanganan luka, trauma, dan rujukan ke rumah sakit.' },
            { icon: '🍲', title: 'Dapur Umum & Distribusi Makanan Siap Saji', desc: 'Penyediaan ribuan porsi makanan hangat higienis untuk para pengungsi dan relawan bertugas.' },
            { icon: '📻', title: 'Komunikasi Radio Darurat & Koordinasi', desc: 'Sinergi frekuensi HT radio kebencanaan bersama instansi komando pemerintah (Basarnas/BPBD).' }
        ]
    },
    {
        id: 'post',
        title: '3. Pasca-Bencana (Pemulihan & Rekonstruksi)',
        tagline: 'Rehabilitasi Sarana, Trauma Healing, Stimulus Ekonomi Penyintas',
        badge: 'Tahap Pemulihan (Recovery)',
        badgeColor: 'bg-emerald-600',
        coverImg: pillarPostImg,
        desc: 'Fase pasca-bencana memastikan para penyintas dapat bangkit kembali dengan mandiri. Kolaborasi Mitra Peduli menyalurkan bantuan stimulus pembangunan hunian sementara (huntara), trauma healing untuk anak-anak dan lansia, pembersihan fasilitas ibadah/sekolah, serta audit laporan penyaluran donasi publik.',
        activities: [
            { icon: '🧘', title: 'Dukungan Psikososial (Trauma Healing)', desc: 'Pendampingan psikologis untuk anak-anak dan keluarga penyintas pasca kehilangan kerabat/tempat tinggal.' },
            { icon: '🏫', title: 'Rehabilitasi Fasilitas Umum & MCK', desc: 'Pembersihan material lumpur sisa banjir dan pembangunan sarana air bersih serta sanitasi darurat.' },
            { icon: '🔨', title: 'Penyaluran Stimulus Material Rekonstruksi', desc: 'Bantuan semen, atap seng, dan perkakas pertukangan untuk perbaikan hunian keluarga rentan.' },
            { icon: '📊', title: 'Laporan Keuangan & Akuntabilitas Publik', desc: 'Penerbitan jurnal dan neraca donasi resmi yang dapat diakses publik dan donatur filantropi.' }
        ]
    }
];

const defaultPartners = [
    {
        name: 'BASARNAS (Badan Nasional Pencarian & Pertolongan)',
        role: 'Instansi SAR Utama',
        category: 'Basarnas',
        desc: 'Kolaborasi pengerahan rescuer bersertifikasi, perahu karet RIB, drone thermal, dan koordinasi rencana operasi SAR bersama.',
        icon: '⚓'
    },
    {
        name: 'BPBD RI (Badan Penanggulangan Bencana Daerah)',
        role: 'Pemerintah Daerah',
        category: 'BPBD',
        desc: 'Sinergi pemetaan wilayah rawan bencana, penyaluran logistik bantuan pengungsi, dan pendirian tenda darurat.',
        icon: '🏛️'
    },
    {
        name: 'PMI & Jaringan Unit Transfusi Darah (UTD)',
        role: 'Kemanusiaan Medis',
        category: 'PMI',
        desc: 'Mobilisasi database relawan donor darah on-demand untuk mencukupi stok transfusi darurat di rumah sakit rujukan.',
        icon: '🩸'
    },
    {
        name: 'Rumah Sakit & Jaringan Medis Gawat Darurat',
        role: 'Layanan Kesehatan',
        category: 'Rumah Sakit',
        desc: 'Penyediaan ambulans gawat darurat, perawatan korban luka bencana, dan posko pemeriksaan kesehatan gratis.',
        icon: '🏥'
    },
    {
        name: 'Konsorsium Lembaga Filantropi & CSR Korporasi',
        role: 'Mitra Filantropi',
        category: 'Filantropi',
        desc: 'Penyaluran dana CSR korporasi untuk pembangunan hunian sementara, trauma healing, dan stimulus ekonomi keluarga penyintas.',
        icon: '🤝'
    }
];

const synergyBenefits = [
    { title: 'Respons Satu Komando', desc: 'Meminimalkan tumpang tindih pengerahan tim lapangan saat situasi darurat bencana.', icon: '🎯' },
    { title: 'Efisiensi Logistik', desc: 'Penyaluran sembako dan obat tepat sasaran ke titik-titik pengungsian paling terisolir.', icon: '📦' },
    { title: 'Pertukaran Data Real-Time', desc: 'Sinkronisasi status korban, jumlah pengungsi, dan kebutuhan suplai darah medis.', icon: '📊' },
    { title: 'Akuntabilitas Donasi Bersama', desc: 'Pelaporan transparansi audit keuangan bagi mitra korporasi dan lembaga amal.', icon: '📑' }
];
</script>

<template>
    <PublicLayout 
        title="Mitra & Ekosistem Kemanusiaan - Kolaborasi Mitra Peduli"
        description="Direktori Terpadu Lembaga Mitra dan Ekosistem Klaster Kemanusiaan Resmi Kolaborasi Mitra Peduli."
    >
        <template #default="{ openCtaModal }">
            
            <!-- HERO HEADER SECTION -->
            <section class="relative py-14 sm:py-20 bg-gradient-to-b from-blue-500/10 via-indigo-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800 text-center overflow-hidden">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    <span class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-black bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 uppercase tracking-wider">
                        <span>🤝</span>
                        <span>DIREKTORI MITRA & EKOSISTEM KEMANUSIAAN TERPADU</span>
                    </span>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                        Mitra & Ekosistem Kolaborasi
                    </h1>

                    <p class="text-sm sm:text-base md:text-lg text-slate-600 dark:text-slate-300 max-w-3xl mx-auto leading-relaxed">
                        Satu gerbang integrasi yang menyatukan seluruh <strong>Lembaga Mitra Resmi</strong> (Basarnas, BPBD, PMI, RS, Rescue, CSR), <strong>Klaster Ekosistem Wilayah</strong>, dan <strong>3 Pilar Siklus Penanggulangan Bencana</strong> dalam satu kesatuan aksi kemanusiaan.
                    </p>

                    <!-- Quick Highlight Stats -->
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                        <div class="px-5 py-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 shadow-sm flex items-center space-x-3">
                            <span class="text-2xl">🏛️</span>
                            <div class="text-left">
                                <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white block">{{ partners.length || displayPartners.length }}</span>
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Lembaga Mitra</span>
                            </div>
                        </div>

                        <div class="px-5 py-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 shadow-sm flex items-center space-x-3">
                            <span class="text-2xl">🌐</span>
                            <div class="text-left">
                                <span class="text-lg sm:text-xl font-black text-indigo-600 dark:text-indigo-400 block">{{ ecosystems.length }}</span>
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Ekosistem Wilayah</span>
                            </div>
                        </div>

                        <div class="px-5 py-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 shadow-sm flex items-center space-x-3">
                            <span class="text-2xl">🛡️</span>
                            <div class="text-left">
                                <span class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 block">3 Pilar</span>
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Siklus Bencana</span>
                            </div>
                        </div>
                    </div>

                    <!-- DIRECTORY MAIN TABS SWITCHER -->
                    <div class="pt-4 flex items-center justify-center">
                        <div class="inline-flex p-1.5 rounded-2xl bg-slate-200/80 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 shadow-inner max-w-full overflow-x-auto">
                            <button
                                type="button"
                                @click="activeMainTab = 'semua'"
                                :class="[
                                    activeMainTab === 'semua'
                                        ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-md font-black'
                                        : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-semibold',
                                    'px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm transition-all whitespace-nowrap flex items-center space-x-2'
                                ]"
                            >
                                <span>📋</span>
                                <span>Semua Tampilan</span>
                            </button>

                            <button
                                type="button"
                                @click="activeMainTab = 'mitra'"
                                :class="[
                                    activeMainTab === 'mitra'
                                        ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-md font-black'
                                        : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-semibold',
                                    'px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm transition-all whitespace-nowrap flex items-center space-x-2'
                                ]"
                            >
                                <span>🏛️</span>
                                <span>Lembaga Mitra ({{ partners.length || displayPartners.length }})</span>
                            </button>

                            <button
                                type="button"
                                @click="activeMainTab = 'ekosistem'"
                                :class="[
                                    activeMainTab === 'ekosistem'
                                        ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-md font-black'
                                        : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-semibold',
                                    'px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm transition-all whitespace-nowrap flex items-center space-x-2'
                                ]"
                            >
                                <span>🌐</span>
                                <span>Ekosistem Wilayah ({{ ecosystems.length }})</span>
                            </button>

                            <button
                                type="button"
                                @click="activeMainTab = 'pilar'"
                                :class="[
                                    activeMainTab === 'pilar'
                                        ? 'bg-white dark:bg-slate-900 text-orange-600 dark:text-orange-400 shadow-md font-black'
                                        : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-semibold',
                                    'px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm transition-all whitespace-nowrap flex items-center space-x-2'
                                ]"
                            >
                                <span>🛡️</span>
                                <span>3 Pilar Kebencanaan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 1: LEMBAGA MITRA RESMI -->
            <section 
                v-show="activeMainTab === 'semua' || activeMainTab === 'mitra'"
                class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-200 dark:border-slate-800"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div class="space-y-2 max-w-2xl">
                            <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 uppercase tracking-wider">
                                🏛️ DIREKTORI LEMBAGA MITRA
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                Jaringan Mitra Lembaga & Instansi
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Sinergi operasional bersama Basarnas, BPBD, Palang Merah Indonesia (PMI), RS Rujukan, Tim Rescue Swadaya, dan Konsorsium Filantropi.
                            </p>
                        </div>

                        <!-- Search & Category Filters -->
                        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                            <div class="relative w-full sm:w-64">
                                <input
                                    v-model="partnerSearch"
                                    type="text"
                                    placeholder="Cari nama mitra..."
                                    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                                <span class="absolute left-3 top-2.5 text-xs text-slate-400">🔍</span>
                            </div>

                            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 max-w-full">
                                <button
                                    v-for="cat in partnerCategories"
                                    :key="cat"
                                    @click="selectedCategory = cat"
                                    :class="[
                                        selectedCategory === cat
                                            ? 'bg-blue-600 text-white shadow-md font-bold'
                                            : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border-slate-200 dark:border-slate-700 font-medium',
                                        'px-3 py-2 rounded-xl border text-xs shrink-0 transition-all'
                                    ]"
                                >
                                    {{ cat }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state mitra -->
                    <div v-if="displayPartners.length === 0" class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-800 text-center space-y-3">
                        <span class="text-4xl block">🔍</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Mitra Tidak Ditemukan</h3>
                        <p class="text-xs text-slate-500">Coba kata kunci lain atau ubah kategori filter.</p>
                        <button @click="selectedCategory = 'Semua'; partnerSearch = ''" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold">
                            Reset Filter Mitra
                        </button>
                    </div>

                    <!-- Partners Cards Grid -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                        <div
                            v-for="p in displayPartners"
                            :key="p.id || p.name"
                            class="p-6 sm:p-7 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-blue-500/40 transition-all duration-300 flex flex-col justify-between space-y-5 group"
                        >
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-950 p-2 shadow-xs border border-slate-200/80 dark:border-slate-800 flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform">
                                        <img 
                                            v-if="p.logo_path" 
                                            :src="p.logo_path" 
                                            :alt="p.name" 
                                            class="w-full h-full object-contain"
                                        />
                                        <span v-else class="text-2xl">
                                            {{ p.icon || getCategoryIcon(p.category) }}
                                        </span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                        {{ p.category }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ p.name }}
                                    </h3>
                                    <span v-if="p.code" class="text-[11px] font-mono text-slate-400 block">
                                        {{ p.code }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-3">
                                    {{ p.description || p.desc || 'Mitra strategis kemanusiaan dan penanggulangan bencana resmi Kolaborasi Mitra Peduli.' }}
                                </p>
                            </div>

                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    👥 {{ p.volunteers_count || p.personnel_count || 0 }} Personel
                                </span>
                                
                                <Link 
                                    v-if="p.slug || p.id"
                                    :href="route('public.partner.show', p.slug || p.id)"
                                    class="px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-600 hover:text-white text-blue-600 dark:text-blue-400 font-bold text-xs transition flex items-center space-x-1"
                                >
                                    <span>Landing Page</span>
                                    <span>→</span>
                                </Link>
                                <span v-else class="text-xs font-bold text-blue-600 dark:text-blue-400">
                                    🛡️ {{ p.role || 'Mitra Utama' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 2: KLASTER EKOSISTEM WILAYAH -->
            <section 
                v-show="activeMainTab === 'semua' || activeMainTab === 'ekosistem'"
                class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-200 dark:border-slate-800"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div class="space-y-2 max-w-2xl">
                            <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 uppercase tracking-wider">
                                🌐 DIREKTORI KLASTER EKOSISTEM
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                Jejaring Klaster Ekosistem Wilayah
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Konsorsium gabungan kampus, ormas, dan potensi relawan regional yang beroperasi dalam satu komando terpadu kawasan.
                            </p>
                        </div>

                        <!-- Search Input for Ecosystems -->
                        <div class="relative w-full sm:w-72">
                            <input
                                v-model="ecosystemSearch"
                                type="text"
                                placeholder="Cari ekosistem / wilayah..."
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                            <span class="absolute left-3 top-2.5 text-xs text-slate-400">🔍</span>
                        </div>
                    </div>

                    <!-- Empty state ekosistem -->
                    <div v-if="filteredEcosystems.length === 0" class="p-12 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-dashed border-slate-300 dark:border-slate-800 text-center space-y-3">
                        <span class="text-4xl block">🌐</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Ekosistem Tidak Ditemukan</h3>
                        <p class="text-xs text-slate-500">Belum ada ekosistem yang cocok dengan pencarian "{{ ecosystemSearch }}".</p>
                        <button @click="ecosystemSearch = ''" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold">
                            Tampilkan Semua Ekosistem
                        </button>
                    </div>

                    <!-- Grid of Ecosystem Cards -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                        <div
                            v-for="eco in filteredEcosystems"
                            :key="eco.id"
                            class="bg-slate-50 dark:bg-slate-950 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 shadow-sm hover:shadow-xl hover:border-indigo-500/40 transition-all duration-300 flex flex-col justify-between space-y-5 group"
                        >
                            <div class="space-y-4">
                                <!-- Card Top -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60 font-mono">
                                            {{ eco.code || 'EKOSISTEM' }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                            ● {{ eco.status || 'Aktif' }}
                                        </span>
                                    </div>

                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/40 flex items-center justify-center text-2xl shrink-0 group-hover:scale-105 transition-transform overflow-hidden">
                                        <img v-if="eco.logo_path" :src="eco.logo_path" :alt="eco.name" class="w-full h-full object-contain p-1" />
                                        <span v-else>🌐</span>
                                    </div>
                                </div>

                                <!-- Ecosystem Name & Tagline -->
                                <div class="space-y-1">
                                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-snug">
                                        {{ eco.name }}
                                    </h3>
                                    <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 line-clamp-1">
                                        {{ eco.tagline || (eco.lead_institution ? 'Induk: ' + eco.lead_institution : 'Ekosistem Kemanusiaan Terpadu') }}
                                    </p>
                                </div>

                                <!-- Meta Info Pills -->
                                <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-medium">📍 Wilayah:</span>
                                        <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[170px]">
                                            {{ eco.region || 'Nasional' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-medium">🏛️ Induk:</span>
                                        <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[170px]">
                                            {{ eco.lead_institution || '-' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2">
                                    {{ eco.description || 'Wadah sinergi respon darurat, mobilisasi tim relawan, dan penyaluran bantuan di kawasan terkait.' }}
                                </p>

                                <!-- Member Institutions Preview -->
                                <div class="space-y-2 pt-2 border-t border-slate-200/80 dark:border-slate-800">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-700 dark:text-slate-300">Lembaga Tergabung:</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                            {{ eco.partners_count || (eco.partners ? eco.partners.length : 0) }} Lembaga
                                        </span>
                                    </div>

                                    <div v-if="eco.partners && eco.partners.length > 0" class="flex flex-wrap gap-1 max-h-16 overflow-hidden">
                                        <span
                                            v-for="p in eco.partners.slice(0, 3)"
                                            :key="p.id"
                                            class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800"
                                        >
                                            {{ p.name }}
                                        </span>
                                        <span v-if="eco.partners.length > 3" class="px-2 py-0.5 rounded-lg text-[10px] font-bold text-indigo-600 dark:text-indigo-400">
                                            +{{ eco.partners.length - 3 }} lainnya
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer Action -->
                            <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">
                                    Sinergi Bersama
                                </span>

                                <Link
                                    :href="route('public.ecosystem.show', eco.slug || eco.code || eco.id)"
                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold text-xs transition shadow-sm flex items-center space-x-1.5"
                                >
                                    <span>Landing Page</span>
                                    <span>→</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: 3 PILAR KEBENCANAAN INTERACTIVE EXPLORER -->
            <section 
                v-show="activeMainTab === 'semua' || activeMainTab === 'pilar'"
                class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-200 dark:border-slate-800"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    
                    <div class="text-center space-y-2 max-w-2xl mx-auto">
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                            🛡️ 3 PILAR SIKLUS KEBENCANAAN
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            Tahapan Operasi & Kesiapsiagaan Bencana
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                            Setiap mitra lembaga mengambil peran strategis sesuai spesialisasi dalam siklus pra-bencana, saat bencana, hingga pasca-bencana.
                        </p>
                    </div>

                    <!-- Pilar Tabs Switcher -->
                    <div class="flex items-center justify-center space-x-2 sm:space-x-4 overflow-x-auto pb-1">
                        <button
                            v-for="p in pillars"
                            :key="p.id"
                            @click="activePillar = p.id"
                            :class="[
                                activePillar === p.id 
                                    ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/30 border-orange-600 font-extrabold' 
                                    : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 border-slate-200 dark:border-slate-700 font-semibold',
                                'px-4 sm:px-6 py-2.5 sm:py-3 rounded-2xl border text-xs sm:text-sm shrink-0 transition-all'
                            ]"
                        >
                            <span>{{ p.title }}</span>
                        </button>
                    </div>

                    <!-- Selected Pillar Card Details -->
                    <div 
                        v-for="pillar in pillars.filter(item => item.id === activePillar)" 
                        :key="pillar.id"
                        class="space-y-8 animate-fadeIn"
                    >
                        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xl">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                                <div class="lg:col-span-6 p-8 sm:p-10 space-y-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase text-white tracking-wider" :class="pillar.badgeColor">
                                        {{ pillar.badge }}
                                    </span>
                                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white leading-tight">
                                        {{ pillar.title }}
                                    </h3>
                                    <p class="text-xs sm:text-sm font-semibold text-orange-600 dark:text-orange-400">
                                        {{ pillar.tagline }}
                                    </p>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                        {{ pillar.desc }}
                                    </p>
                                    <div class="pt-2 flex flex-wrap gap-3">
                                        <button @click="openCtaModal('relawan')" class="px-5 py-2.5 rounded-xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 transition shadow-md">
                                            Gabung Relawan Rescuer
                                        </button>
                                        <button @click="openCtaModal('donatur')" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                            Dukung Donasi Fase Ini
                                        </button>
                                    </div>
                                </div>
                                <div class="lg:col-span-6 h-[320px] lg:h-full min-h-[300px] relative bg-slate-900">
                                    <img :src="pillar.coverImg" :alt="pillar.title" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-slate-950/60 via-transparent to-transparent"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Pillar Activities Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div
                                v-for="act in pillar.activities"
                                :key="act.title"
                                class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1.5 flex items-start space-x-3.5 hover:border-orange-500/40 transition-all"
                            >
                                <span class="text-2xl p-2.5 bg-orange-500/10 rounded-2xl shrink-0">{{ act.icon }}</span>
                                <div class="space-y-0.5">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ act.title }}</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ act.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- SECTION: BENEFIT SINERGI BERSAMA -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-200 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center space-y-2 max-w-2xl mx-auto">
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                            ✨ NILAI TAMBAH KOLABORASI
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                            Mengapa Kolaborasi Terpadu Penting?
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div
                            v-for="b in synergyBenefits"
                            :key="b.title"
                            class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center space-y-3 hover:border-orange-500/40 transition-all"
                        >
                            <span class="text-3xl p-3 bg-orange-500/10 rounded-2xl inline-block">{{ b.icon }}</span>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ b.title }}</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ b.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: CALL TO ACTION BERGABUNG / KEMITRAAN -->
            <section class="py-16 sm:py-20 bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950 text-white">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-blue-500/20 text-blue-300 border border-blue-500/30 uppercase tracking-wider">
                        🤝 MARI BERSINERGI BERSAMA KAMI
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                        Tertarik Mendaftarkan Lembaga atau Membentuk Ekosistem Baru?
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Kolaborasi Mitra Peduli membuka pintu kemitraan seluas-luasnya bagi instansi pemerintah, BUMN, swasta, ormas, dan komunitas relawan untuk bersama-sama membangun ketahanan bencana bangsa.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-4">
                        <button
                            @click="openCtaModal('mitra')"
                            class="px-8 py-4 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black rounded-2xl shadow-xl shadow-orange-500/20 active:scale-95 transition text-sm sm:text-base flex items-center space-x-2"
                        >
                            <span>📝</span>
                            <span>Ajukan Kerjasama / Registrasi Mitra</span>
                        </button>
                        <Link
                            :href="route('public.partner-terms')"
                            class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-bold rounded-2xl border border-white/20 transition text-sm sm:text-base flex items-center space-x-2"
                        >
                            <span>📄</span>
                            <span>Pelajari Syarat Kemitraan</span>
                        </Link>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
