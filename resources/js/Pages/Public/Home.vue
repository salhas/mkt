<script setup>
import { computed } from 'vue';
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
    latestNews: Array,
    stats: Object,
});

const partners = [
    { name: 'BASARNAS', desc: 'Badan Nasional Pencarian & Pertolongan', category: 'SAR & Evakuasi', icon: '⚓' },
    { name: 'BPBD RI', desc: 'Badan Penanggulangan Bencana Daerah', category: 'Komando Kebencanaan', icon: '🏛️' },
    { name: 'PMI / Kemenkes', desc: 'Palang Merah Indonesia & Dinkes', category: 'Donor Darah & Medis', icon: '🩸' },
    { name: 'Lembaga Filantropi', desc: 'Konsorsium Yayasan Amal Sosial', category: 'Penyalur Donasi', icon: '🤝' },
    { name: 'Rumah Sakit Rujukan', desc: 'Jaringan Medis Darurat', category: 'Kesehatan', icon: '🏥' },
    { name: 'Mitra CSR Korporasi', desc: 'Perusahaan Peduli Kemanusiaan', category: 'Program CSR', icon: '💼' }
];

const pillarsList = [
    {
        id: 'pre',
        title: 'Pra-Bencana (Mitigasi)',
        desc: 'Pemetaan potensi bencana, edukasi kesiapsiagaan masyarakat, pelatihan SAR relawan, dan penyiapan logistik darurat.',
        img: pillarPreImg,
        color: 'from-blue-600 to-cyan-600',
        badge: 'Mitigasi & Edukasi'
    },
    {
        id: 'during',
        title: 'Saat Bencana (Tanggap Darurat)',
        desc: 'Pengerahan cepat Tim Rescue MKT ke titik musibah, evakuasi warga terdampak, pendirian posko kesehatan darurat & dapur umum.',
        img: pillarDuringImg,
        color: 'from-rose-600 to-orange-600',
        badge: 'Respon Cepat 24/7'
    },
    {
        id: 'post',
        title: 'Pasca-Bencana (Pemulihan)',
        desc: 'Rehabilitasi sarana publik, dukungan psikososial (trauma healing), penyaluran dana rekonstruksi, dan pemulihan ekonomi.',
        img: pillarPostImg,
        color: 'from-emerald-600 to-teal-600',
        badge: 'Rehabilitasi & Donasi'
    }
];

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <PublicLayout 
        title="Beranda - Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu)"
        description="Pusat informasi penanggulangan bencana, relawan donor darah siaga 24/7, tim rescue SAR, dan donasi kemanusiaan transparan."
    >
        <template #default="{ openCtaModal, openShareModal, currentLang }">
            
            <!-- HERO SECTION -->
            <section class="relative overflow-hidden min-h-[560px] sm:min-h-[700px] flex items-center justify-center py-12 sm:py-20 lg:py-24 bg-amber-50/40 dark:bg-slate-950 transition-colors border-b border-orange-500/10 dark:border-slate-800/80">
                <!-- Background Image Layer -->
                <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
                    <img 
                        :src="heroRescueImg" 
                        alt="MKT Rescue Operation Background" 
                        class="w-full h-full object-cover object-center scale-105 transition-transform duration-1000 ease-out opacity-85 dark:opacity-40" 
                    />
                    <div class="block dark:hidden absolute inset-0 bg-gradient-to-r from-white via-white/90 to-white/35"></div>
                    <div class="block dark:hidden absolute inset-0 bg-gradient-to-t from-white via-transparent to-white/60"></div>
                    <div class="block dark:hidden absolute inset-0 bg-orange-500/5 mix-blend-multiply"></div>
                    <div class="hidden dark:block absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/95 to-slate-900/60"></div>
                    <div class="hidden dark:block absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-slate-950/80"></div>
                </div>

                <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        
                        <!-- Left Headline & CTA Column -->
                        <div class="lg:col-span-7 space-y-6 text-left">
                            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-black bg-white/90 dark:bg-slate-900/80 backdrop-blur-md text-orange-600 dark:text-orange-400 border border-orange-500/30 shadow-md shadow-orange-500/10">
                                <span>📢 Ekosistem Penanggulangan Bencana Terpadu</span>
                            </div>

                            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-950 dark:text-white tracking-tight leading-[1.12]">
                                Membangun Ketangguhan,<br />
                                <span class="bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 dark:from-orange-400 dark:via-amber-300 dark:to-orange-400 bg-clip-text text-transparent">
                                    Menyalurkan Harapan Kemanusiaan
                                </span>
                            </h1>

                            <p class="text-sm sm:text-base lg:text-lg text-slate-700 dark:text-slate-200 leading-relaxed font-medium max-w-2xl">
                                Yayasan MKT Indonesia mengintegrasikan respons cepat evakuasi SAR, kesiapsiagaan relawan donor darah 24/7, posko medis, dan akuntabilitas pelaporan donasi publik yang transparan.
                            </p>

                            <!-- Hero Button Actions -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                                <button
                                    @click="openCtaModal('donatur')"
                                    class="px-7 py-4 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 text-white font-black rounded-2xl shadow-xl shadow-orange-500/30 hover:shadow-orange-500/40 active:scale-95 transition-all text-center flex items-center justify-center space-x-2.5 text-sm sm:text-base border border-white/20"
                                >
                                    <span>💖 Salurkan Donasi Sekarang</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>
                                
                                <button
                                    @click="openCtaModal('relawan')"
                                    class="px-7 py-4 bg-white/90 dark:bg-white/10 hover:bg-white dark:hover:bg-white/20 backdrop-blur-md border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-300 font-bold rounded-2xl shadow-md hover:shadow-lg active:scale-95 transition-all text-center flex items-center justify-center space-x-2.5 text-sm sm:text-base"
                                >
                                    <span>🚑 Gabung Relawan & Donor</span>
                                </button>
                            </div>

                            <!-- Live Stats Counter Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4 pt-6 border-t border-slate-300/80 dark:border-white/15">
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/70 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-sm">
                                    <span class="text-xl sm:text-2xl font-black text-orange-600 dark:text-orange-400 block">{{ stats?.volunteers || '1,250+' }}</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Relawan & SAR</span>
                                </div>
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/70 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-sm">
                                    <span class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 block">{{ stats?.donors || '850+' }}</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Pendonor Darah</span>
                                </div>
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/70 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-sm">
                                    <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 block">{{ stats?.operations || '42+' }}</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Operasi Lapangan</span>
                                </div>
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/70 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-sm">
                                    <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 block">{{ stats?.donations || 'Rp 1.5M+' }}</span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Donasi Tersalurkan</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Highlight Tactical Cards Column -->
                        <div class="lg:col-span-5 space-y-4">
                            <!-- Card 1: Siaga SAR 24/7 -->
                            <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl hover:border-orange-500/50 hover:-translate-y-0.5 transition-all group">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 border border-orange-500/20 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                        🚨
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pusat Komando Bencana MKT (Pusdalops 727)</h3>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                            Pemantauan koordinat GPS real-time, status kesiapan armada perahu karet RIB, drone thermal, dan koordinasi instansi SAR Basarnas & BPBD.
                                        </p>
                                        <Link :href="route('public.services')" class="inline-flex items-center text-xs font-bold text-orange-600 dark:text-orange-400 mt-2 hover:underline">
                                            <span>Pelajari Layanan Rescue &rarr;</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Donor Darah -->
                            <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl hover:border-rose-500/50 hover:-translate-y-0.5 transition-all group">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                        🩸
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Database Relawan Donor Darah On-Demand</h3>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                            Siaga transfusi darurat untuk rumah sakit rujukan dengan filter golongan darah (A, B, AB, O) dan rhesus aktif.
                                        </p>
                                        <button @click="openCtaModal('relawan')" class="inline-flex items-center text-xs font-bold text-rose-600 dark:text-rose-400 mt-2 hover:underline">
                                            <span>Daftar Jadi Pendonor Siaga &rarr;</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Transparansi Keuangan -->
                            <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl hover:border-emerald-500/50 hover:-translate-y-0.5 transition-all group">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                        📑
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Akuntabilitas Finansial & Laporan Jurnal</h3>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                            Setiap rupiah donasi kemanusiaan tercatat otomatis pada sistem Buku Besar & Neraca Keuangan MKT secara transparan.
                                        </p>
                                        <Link :href="route('public.about')" class="inline-flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-2 hover:underline">
                                            <span>Lihat Profil Transparansi &rarr;</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- SECTION: 3 PILAR SIKLUS KEBENCANAAN OVERVIEW -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center max-w-3xl mx-auto space-y-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                            🛡️ SIKLUS PENANGGULANGAN BENCANA
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            3 Tahap Terpadu Penanganan Risiko Musibah
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400">
                            MKT beroperasi secara berkesinambungan di semua fase siklus bencana untuk meminimalkan korban jiwa dan mempercepat pemulihan.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                        <div 
                            v-for="p in pillarsList" 
                            :key="p.id"
                            class="bg-slate-50 dark:bg-slate-950 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
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
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">{{ p.desc }}</p>
                                </div>
                                <Link :href="route('public.partners')" class="text-xs font-bold text-orange-600 dark:text-orange-400 inline-flex items-center space-x-1 hover:underline pt-2">
                                    <span>Lihat Sinergi & 3 Pilar Lengkap &rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-2">
                        <Link :href="route('public.partners')" class="inline-flex items-center space-x-2 px-6 py-3 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 font-extrabold text-xs sm:text-sm hover:bg-orange-500/20 transition-all">
                            <span>Eksplorasi Seluruh Siklus 3 Pilar Kebencanaan di Halaman Mitra</span>
                            <span>&rarr;</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- SECTION: BERITA & ARTIKEL TERBARU (NEWS HIGHLIGHT) -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                        <div class="space-y-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                                📰 PUBLIKASI & KABAR TERKINI
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                Berita, Liputan Lapangan & Artikel Edukasi
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
                            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
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

            <!-- SECTION: MITRA & KOLABORASI SUMMARY -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="text-center max-w-2xl mx-auto space-y-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 uppercase tracking-wider">
                            🤝 SINERGI KEMANUSIAAN
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            Mitra Terpadu Kebencanaan MKT
                        </h2>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div
                            v-for="p in partners"
                            :key="p.name"
                            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex flex-col items-center justify-center text-center space-y-1.5 hover:border-orange-500/50 hover:bg-orange-50/20 transition-all"
                        >
                            <span class="text-3xl">{{ p.icon }}</span>
                            <h4 class="font-extrabold text-xs text-slate-900 dark:text-white">{{ p.name }}</h4>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">{{ p.category }}</span>
                        </div>
                    </div>

                    <div class="text-center pt-2">
                        <Link :href="route('public.partners')" class="inline-flex items-center space-x-2 text-xs sm:text-sm font-bold text-orange-600 dark:text-orange-400 hover:underline">
                            <span>Lihat Informasi Sinergi & Pendaftaran Mitra Instansi &rarr;</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- SECTION: CALL TO ACTION (CTA BANNER) -->
            <section class="py-16 sm:py-20 bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 text-white relative overflow-hidden">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center space-y-6 relative z-10">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-white/20 text-white uppercase tracking-wider backdrop-blur-md">
                        ✨ AMBIL PERAN KEMANUSIAAN HARI INI
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                        Bersama Membangun Indonesia Siaga, Tanggap, & Tangguh Bencana
                    </h2>
                    <p class="text-sm sm:text-base text-white/90 max-w-2xl mx-auto leading-relaxed font-medium">
                        Setiap donasi, setiap kantong darah, dan setiap waktu yang didedikasikan relawan sangat berharga untuk menyelamatkan nyawa sesama.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                        <button
                            @click="openCtaModal('donatur')"
                            class="w-full sm:w-auto px-8 py-4 bg-white text-orange-600 hover:bg-orange-50 font-black rounded-2xl shadow-xl active:scale-95 transition-all text-sm sm:text-base"
                        >
                            💖 Salurkan Donasi MKT
                        </button>
                        <button
                            @click="openCtaModal('relawan')"
                            class="w-full sm:w-auto px-8 py-4 bg-orange-950/40 hover:bg-orange-950/60 border border-white/30 text-white font-bold rounded-2xl backdrop-blur-md active:scale-95 transition-all text-sm sm:text-base"
                        >
                            🚑 Daftar Relawan & Pendonor
                        </button>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
