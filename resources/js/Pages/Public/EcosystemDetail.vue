<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    ecosystem: {
        type: Object,
        required: true,
    },
    partners: {
        type: Array,
        default: () => [],
    },
    volunteers: {
        type: Array,
        default: () => [],
    },
    totalVolunteersCount: {
        type: Number,
        default: 0,
    },
    totalPersonnelEstimated: {
        type: Number,
        default: 0,
    },
    equipments: {
        type: Array,
        default: () => [],
    },
    totalEquipmentsCount: {
        type: Number,
        default: 0,
    },
    ecosystemNews: {
        type: Array,
        default: () => [],
    },
    otherEcosystems: {
        type: Array,
        default: () => [],
    },
    mktProfile: {
        type: Object,
        default: null,
    },
});

// Modal Ajukan Gabung ke Ekosistem Ini
const showJoinModal = ref(false);
const joinSubmitted = ref(false);

const joinForm = useForm({
    name: '',
    category: 'Tim Rescue',
    pic_name: '',
    pic_phone: '',
    pic_email: '',
    email: '',
    phone: '',
    address: '',
    personnel_count: 5,
    mou_number: '',
    description: '',
    password: '',
    terms_accepted: false,
    ecosystem_id: props.ecosystem.id,
});

const submitJoinForm = () => {
    joinForm.post(route('partners.public-register'), {
        preserveScroll: true,
        onSuccess: () => {
            joinSubmitted.value = true;
            joinForm.reset();
        },
    });
};

const copyShareLink = () => {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Tautan halaman ekosistem berhasil disalin ke clipboard! Bagikan tautan ini ke rekan atau jejaring Anda.');
    });
};
</script>

<template>
    <PublicLayout
        :title="`${ecosystem.name} - Ekosistem Kemanusiaan Resmi MKT`"
        :description="ecosystem.description || `Profil dan Jejaring Lembaga Kemanusiaan ${ecosystem.name} Terintegrasi Yayasan MKT Indonesia.`"
    >
        <template #default="{ openCtaModal }">

            <!-- HERO BANNER SECTION -->
            <section class="relative bg-slate-950 text-white overflow-hidden border-b border-indigo-900/40">
                <!-- Banner Background Image or Gradient -->
                <div class="absolute inset-0 z-0">
                    <img 
                        v-if="ecosystem.banner_path" 
                        :src="ecosystem.banner_path" 
                        :alt="ecosystem.name" 
                        class="w-full h-full object-cover opacity-25"
                    />
                    <div v-else class="w-full h-full bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 opacity-90"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent"></div>
                </div>

                <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-8">
                    <!-- Top Breadcrumb & Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <Link :href="route('public.ecosystems')" class="px-3 py-1 rounded-xl bg-white/10 hover:bg-white/20 text-white/80 text-xs font-semibold backdrop-blur-md transition flex items-center space-x-1">
                            <span>←</span>
                            <span>Semua Ekosistem</span>
                        </Link>
                        <span class="px-3 py-1 rounded-xl bg-indigo-500/30 text-indigo-300 border border-indigo-500/40 text-xs font-mono font-bold uppercase tracking-wider">
                            {{ ecosystem.code || 'EKOSISTEM' }}
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold">
                            ● {{ ecosystem.status || 'Aktif' }}
                        </span>
                        <span v-if="ecosystem.region" class="px-3 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold flex items-center space-x-1">
                            <span>📍</span>
                            <span>{{ ecosystem.region }}</span>
                        </span>
                    </div>

                    <!-- Main Ecosystem Title & Lead -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <div class="lg:col-span-8 space-y-4">
                            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-white">
                                {{ ecosystem.name }}
                            </h1>
                            <p class="text-base sm:text-lg text-indigo-300 font-semibold">
                                {{ ecosystem.tagline || (ecosystem.lead_institution ? 'Diprakarsai & Berinduk pada: ' + ecosystem.lead_institution : 'Klaster & Aliansi Penanggulangan Bencana Terpadu') }}
                            </p>
                            <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-3xl">
                                {{ ecosystem.description || 'Sinergi dan kolaborasi unit kemanusiaan, potensi SAR, relawan kebencanaan, tim medis darurat, dan jejaring filantropi di kawasan terkait.' }}
                            </p>

                            <!-- CTAs: Ajukan Bergabung & Bagikan -->
                            <div class="pt-4 flex flex-wrap items-center gap-3">
                                <button
                                    @click="showJoinModal = true"
                                    class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs sm:text-sm shadow-lg shadow-orange-500/25 active:scale-95 transition flex items-center space-x-2"
                                >
                                    <span>🤝</span>
                                    <span>Gabungkan Lembaga ke Ekosistem Ini</span>
                                </button>
                                <button
                                    @click="copyShareLink"
                                    class="px-5 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/20 transition flex items-center space-x-2"
                                    title="Salin Tautan Halaman"
                                >
                                    <span>🔗</span>
                                    <span>Bagikan Halaman</span>
                                </button>
                                <a
                                    v-if="ecosystem.pic_phone"
                                    :href="`https://wa.me/${ecosystem.pic_phone.replace(/[^0-9]/g, '')}?text=Halo%20PIC%20${encodeURIComponent(ecosystem.name)},%20kami%20ingin%20berkolaborasi%20dan%20bergabung.`"
                                    target="_blank"
                                    class="px-5 py-3.5 rounded-2xl bg-emerald-600/90 hover:bg-emerald-600 text-white font-bold text-xs sm:text-sm transition flex items-center space-x-2"
                                >
                                    <span>💬</span>
                                    <span>Kontak PIC WA</span>
                                </a>
                            </div>
                        </div>

                        <!-- Ecosystem Quick Card Summary -->
                        <div class="lg:col-span-4 bg-white/10 backdrop-blur-xl border border-white/15 rounded-3xl p-6 sm:p-7 space-y-5 shadow-2xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 rounded-2xl bg-white text-slate-900 flex items-center justify-center font-black text-3xl shadow-md p-2 overflow-hidden shrink-0">
                                    <img v-if="ecosystem.logo_path" :src="ecosystem.logo_path" :alt="ecosystem.name" class="w-full h-full object-contain" />
                                    <span v-else>🌐</span>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-300 block">Jejaring Resmi</span>
                                    <h4 class="font-black text-white text-base truncate">{{ ecosystem.lead_institution || 'Konsorsium Mitra' }}</h4>
                                </div>
                            </div>

                            <div class="space-y-3 pt-2 border-t border-white/10 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-300">🏛️ Lembaga Tergabung:</span>
                                    <span class="font-extrabold text-amber-400 text-sm">{{ partners.length }} Mitra</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-300">👥 Total Personel Gabungan:</span>
                                    <span class="font-extrabold text-white text-sm">{{ totalPersonnelEstimated }} Relawan</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-300">🚒 Armada & Alat Siaga:</span>
                                    <span class="font-extrabold text-white text-sm">{{ totalEquipmentsCount }} Unit</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-300">👤 Narahubung PIC:</span>
                                    <span class="font-bold text-white text-right truncate max-w-[150px]">{{ ecosystem.pic_name || '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- STATS NUMBERS BAR -->
            <section class="py-8 bg-indigo-900 text-white border-b border-indigo-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                        <div class="space-y-1">
                            <span class="text-3xl sm:text-4xl font-black text-amber-400 block">{{ partners.length }}</span>
                            <span class="text-xs font-bold text-indigo-200 uppercase tracking-wider">Lembaga Terintegrasi</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-3xl sm:text-4xl font-black text-white block">{{ totalPersonnelEstimated }}+</span>
                            <span class="text-xs font-bold text-indigo-200 uppercase tracking-wider">Kekuatan Personel Relawan</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-3xl sm:text-4xl font-black text-emerald-400 block">{{ totalEquipmentsCount }}</span>
                            <span class="text-xs font-bold text-indigo-200 uppercase tracking-wider">Peralatan & Perlengkapan</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-3xl sm:text-4xl font-black text-white block">1 Komando</span>
                            <span class="text-xs font-bold text-indigo-200 uppercase tracking-wider">Tanggap Darurat Terpadu</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: VISI & MISI EKOSISTEM -->
            <section class="py-14 sm:py-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="text-center space-y-2 max-w-2xl mx-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                            🎯 VISI & MISI SINERGI
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                            Arah Kolaborasi & Komitmen Bersama
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                        <!-- Visi Card -->
                        <div class="p-8 rounded-3xl bg-gradient-to-br from-indigo-50/70 to-purple-50/50 dark:from-slate-950 dark:to-slate-900 border border-indigo-100 dark:border-slate-800 space-y-4">
                            <div class="flex items-center space-x-3">
                                <span class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-bold shadow-md">
                                    🔭
                                </span>
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Visi Ekosistem</h3>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">Tujuan Jangka Panjang Bersama</span>
                                </div>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                {{ ecosystem.vision || 'Menjadi jejaring ekosistem kemanusiaan yang solid, profesional, responsif, dan adaptif dalam menghadapi setiap bencana serta krisis kemanusiaan di wilayah cakupan.' }}
                            </p>
                        </div>

                        <!-- Misi Card -->
                        <div class="p-8 rounded-3xl bg-gradient-to-br from-purple-50/70 to-indigo-50/50 dark:from-slate-950 dark:to-slate-900 border border-purple-100 dark:border-slate-800 space-y-4">
                            <div class="flex items-center space-x-3">
                                <span class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl font-bold shadow-md">
                                    🚀
                                </span>
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Misi & Langkah Kerja</h3>
                                    <span class="text-xs text-purple-600 dark:text-purple-400 font-semibold">Implementasi Lapangan</span>
                                </div>
                            </div>
                            <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                {{ ecosystem.mission || "1. Mengintegrasikan rantai komunikasi dan koordinasi darurat antar lembaga.\n2. Mengoptimalkan mobilisasi personel relawan bersertifikasi dan peralatan evakuasi.\n3. Menyelenggarakan latihan bersama, mitigasi risiko bencana, dan simulasi secara berkala." }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: DAFTAR LEMBAGA MITRA TERGABUNG (HERO SHOWCASE) -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                        <div class="space-y-2">
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                                🏛️ ANGGOTA RESMI KONSORSIUM
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                Lembaga & Organisasi Mitra Tergabung
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-xl">
                                Lembaga yang telah menandatangani MoU kerjasama kemitraan dan aktif berkolaborasi di dalam ekosistem {{ ecosystem.name }}.
                            </p>
                        </div>

                        <!-- CTA Join Button -->
                        <button
                            @click="showJoinModal = true"
                            class="px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center space-x-2 shrink-0 self-start md:self-auto"
                        >
                            <span>➕</span>
                            <span>Daftarkan Lembaga Anda ke Ekosistem Ini</span>
                        </button>
                    </div>

                    <!-- Empty State Lembaga -->
                    <div v-if="partners.length === 0" class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-800 text-center space-y-4">
                        <span class="text-4xl block">🤝</span>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Lembaga yang Terdaftar</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                            Jadilah lembaga pemrakarsa atau mitra pertama yang bergabung dalam ekosistem ini untuk membangun kekuatan tanggap bencana terpadu.
                        </p>
                        <button
                            @click="showJoinModal = true"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition"
                        >
                            Gabungkan Lembaga Sekarang
                        </button>
                    </div>

                    <!-- Partners Cards Grid -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                        <div
                            v-for="p in partners"
                            :key="p.id"
                            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-sm hover:shadow-xl hover:border-indigo-500/40 transition-all duration-300 flex flex-col justify-between space-y-6 group"
                        >
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-2 flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform">
                                        <img v-if="p.logo_path" :src="p.logo_path" :alt="p.name" class="w-full h-full object-contain" />
                                        <span v-else class="text-2xl">🏛️</span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                                        {{ p.category }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-snug">
                                        {{ p.name }}
                                    </h3>
                                    <span v-if="p.code" class="text-[11px] font-mono text-slate-400 block">{{ p.code }}</span>
                                </div>

                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2">
                                    {{ p.description || 'Lembaga mitra aktif dalam operasi kebencanaan dan aksi kemanusiaan terpadu.' }}
                                </p>

                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 text-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Kekuatan Personel:</span>
                                        <span class="font-bold text-slate-900 dark:text-white">{{ p.volunteers_count || p.personnel_count || 0 }} Orang</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Status MoU:</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Aktif & Terverifikasi</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">
                                    PIC: {{ p.pic_name || '-' }}
                                </span>

                                <Link
                                    :href="route('public.partner.show', p.slug || p.code || p.id)"
                                    class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-bold text-xs transition flex items-center space-x-1"
                                >
                                    <span>Landing Mitra</span>
                                    <span>→</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: PERALATAN & ARMADA GABUNGAN EKOSISTEM -->
            <section v-if="equipments.length > 0" class="py-16 sm:py-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="text-center space-y-2 max-w-2xl mx-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-500/10 text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                            🚒 KESIAPAN ALAT & ARMADA GABUNGAN
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                            Peralatan & Perlengkapan Siaga Operasi
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                            Inventaris alat SAR, evakuasi, medis, dan logistik yang siap dimobilisasi bersama dalam ekosistem ini saat tanggap darurat.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div
                            v-for="eq in equipments"
                            :key="eq.id"
                            class="p-5 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 hover:border-amber-500/40 transition-all flex flex-col justify-between"
                        >
                            <div class="space-y-3">
                                <div class="w-full h-36 rounded-2xl bg-slate-200 dark:bg-slate-800 overflow-hidden flex items-center justify-center">
                                    <img v-if="eq.photo_path" :src="eq.photo_path" :alt="eq.name" class="w-full h-full object-cover" />
                                    <span v-else class="text-3xl">🦺</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 uppercase">
                                        {{ eq.category }}
                                    </span>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-sm line-clamp-1">
                                        {{ eq.name }}
                                    </h4>
                                    <p class="text-[11px] text-slate-400 line-clamp-1">
                                        Milik: {{ eq.partner ? eq.partner.name : 'Mitra Ekosistem' }}
                                    </p>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                                <span class="font-extrabold text-amber-600 dark:text-amber-400">
                                    {{ eq.quantity }} {{ eq.unit }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    {{ eq.condition }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: BERITA & PUBLIKASI EKOSISTEM -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                        <div class="space-y-2">
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                                📰 DOKUMENTASI & KABAR TERBARU
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                Aksi, Edukasi & Publikasi Sinergi
                            </h2>
                        </div>
                        <Link
                            :href="route('public.news')"
                            class="text-xs sm:text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center space-x-1"
                        >
                            <span>Lihat Semua Berita</span>
                            <span>→</span>
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                        <div
                            v-for="item in ecosystemNews"
                            :key="item.id"
                            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
                        >
                            <div class="space-y-4">
                                <div class="h-48 w-full bg-slate-200 dark:bg-slate-800 overflow-hidden relative">
                                    <img 
                                        v-if="item.image_path" 
                                        :src="item.image_path" 
                                        :alt="item.title" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-4xl text-slate-400">
                                        📰
                                    </div>
                                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-900/80 text-white backdrop-blur-md">
                                        {{ item.category || 'Kemanusiaan' }}
                                    </span>
                                </div>

                                <div class="p-6 space-y-2">
                                    <span class="text-[11px] font-medium text-slate-400 block">
                                        {{ new Date(item.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                    </span>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2 leading-snug">
                                        {{ item.title }}
                                    </h3>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed">
                                        {{ item.summary || item.content?.replace(/<[^>]*>?/gm, '') }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-6 pt-0">
                                <Link
                                    :href="route('public.news.show', item.slug || item.id)"
                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center space-x-1"
                                >
                                    <span>Baca Selengkapnya</span>
                                    <span>→</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: EKOSISTEM WILAYAH LAINNYA -->
            <section v-if="otherEcosystems.length > 0" class="py-14 sm:py-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">EKSPLORASI WILAYAH</span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Ekosistem Kemanusiaan Lainnya</h3>
                        </div>
                        <Link :href="route('public.ecosystems')" class="text-xs font-bold text-indigo-600 hover:underline">
                            Lihat Semua →
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Link
                            v-for="oe in otherEcosystems"
                            :key="oe.id"
                            :href="route('public.ecosystem.show', oe.slug || oe.code || oe.id)"
                            class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-indigo-500/40 hover:shadow-md transition flex items-center space-x-4 group"
                        >
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                                🌐
                            </div>
                            <div class="space-y-0.5 overflow-hidden">
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm group-hover:text-indigo-600 truncate">
                                    {{ oe.name }}
                                </h4>
                                <p class="text-[11px] text-slate-500 truncate">
                                    📍 {{ oe.region || 'Nasional' }} • {{ oe.partners_count }} Lembaga
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- CTA SECTION BOTTOM -->
            <section class="py-16 sm:py-20 bg-gradient-to-r from-indigo-950 via-slate-900 to-purple-950 text-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-wider">
                        🤝 INGIN BERGABUNG DENGAN {{ ecosystem.name }}?
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                        Tingkatkan Kapasitas Penyelamatan & Kemanusiaan Lembaga Anda
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Dengan bergabung ke dalam ekosistem {{ ecosystem.name }}, lembaga Anda akan terhubung langsung ke jejaring logistik, komando operasi SAR, dan pelatihan kesiapsiagaan terpadu Yayasan MKT Indonesia.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-4">
                        <button
                            @click="showJoinModal = true"
                            class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs sm:text-sm shadow-lg shadow-orange-500/25 active:scale-95 transition flex items-center space-x-2"
                        >
                            <span>📝</span>
                            <span>Ajukan Pendaftaran Lembaga Sekarang</span>
                        </button>
                        <Link
                            :href="route('public.partner-terms')"
                            class="px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/20 transition flex items-center space-x-2"
                        >
                            <span>📄</span>
                            <span>Syarat & Ketentuan Kemitraan</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- MODAL PENDAFTARAN MITRA KHUSUS EKOSISTEM INI -->
            <div v-if="showJoinModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-6 animate-scaleUp max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200">
                                GABUNG EKOSISTEM: {{ ecosystem.code }}
                            </span>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white">
                                Formulir Pendaftaran Lembaga Mitra
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Daftarkan lembaga Anda untuk terhubung ke dalam ekosistem {{ ecosystem.name }}.
                            </p>
                        </div>
                        <button
                            @click="showJoinModal = false"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-xl"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Success State -->
                    <div v-if="joinSubmitted" class="p-8 text-center space-y-4">
                        <span class="text-5xl block">🎉</span>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">Pendaftaran Berhasil Dikirim!</h4>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                            Terima kasih telah mendaftar. Data profil lembaga Anda telah tercatat dan akan diverifikasi oleh Admin MKT bersama koordinator PIC {{ ecosystem.name }}.
                        </p>
                        <button
                            @click="showJoinModal = false; joinSubmitted = false;"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs"
                        >
                            Tutup
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form v-else @submit.prevent="submitJoinForm" class="space-y-4 text-xs">
                        <div class="space-y-1">
                            <label class="font-bold text-slate-700 dark:text-slate-300">Nama Resmi Lembaga / Organisasi *</label>
                            <input
                                v-model="joinForm.name"
                                type="text"
                                required
                                placeholder="Contoh: KSR PMI Unit Kampus, Mapala Rescue, dll."
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="joinForm.errors.name" class="text-rose-500 text-[11px]">{{ joinForm.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 dark:text-slate-300">Kategori Lembaga *</label>
                                <select
                                    v-model="joinForm.category"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs"
                                >
                                    <option value="Tim Rescue">Tim Rescue / Potensi SAR</option>
                                    <option value="PMI">PMI / Unit KSR Darah</option>
                                    <option value="BPBD">BPBD / Satlak PB</option>
                                    <option value="Basarnas">Basarnas / Siaga SAR</option>
                                    <option value="Rumah Sakit">Rumah Sakit / Medis</option>
                                    <option value="Filantropi">Lembaga Filantropi / CSR</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 dark:text-slate-300">Kekuatan Personel Relawan Aktif</label>
                                <input
                                    v-model="joinForm.personnel_count"
                                    type="number"
                                    min="1"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 dark:text-slate-300">Nama Narahubung / PIC *</label>
                                <input
                                    v-model="joinForm.pic_name"
                                    type="text"
                                    required
                                    placeholder="Nama Ketua / Penanggung Jawab"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 dark:text-slate-300">Nomor WhatsApp PIC *</label>
                                <input
                                    v-model="joinForm.pic_phone"
                                    type="text"
                                    required
                                    placeholder="08123456789"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 dark:text-slate-300">Email Resmi Lembaga *</label>
                                <input
                                    v-model="joinForm.email"
                                    type="email"
                                    required
                                    placeholder="official@lembaga.org"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 dark:text-slate-300">Password Akun Portal Lembaga</label>
                                <input
                                    v-model="joinForm.password"
                                    type="password"
                                    placeholder="Minimal 6 karakter"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs"
                                />
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="font-bold text-slate-700 dark:text-slate-300">Alamat Markas / Posko</label>
                            <textarea
                                v-model="joinForm.address"
                                rows="2"
                                placeholder="Alamat lengkap posko/markas lembaga..."
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs"
                            ></textarea>
                        </div>

                        <div class="space-y-1">
                            <label class="font-bold text-slate-700 dark:text-slate-300">Profil Singkat & Spesialisasi</label>
                            <textarea
                                v-model="joinForm.description"
                                rows="2"
                                placeholder="Jelaskan bidang fokus tim (water rescue, medis, dapur umum, dll)..."
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs"
                            ></textarea>
                        </div>

                        <!-- Checkbox Syarat -->
                        <div class="pt-2 flex items-start space-x-2">
                            <input
                                v-model="joinForm.terms_accepted"
                                type="checkbox"
                                id="terms"
                                required
                                class="rounded border-slate-300 text-indigo-600 mt-0.5 focus:ring-indigo-500"
                            />
                            <label for="terms" class="text-[11px] text-slate-600 dark:text-slate-400 leading-normal">
                                Lembaga kami menyetujui prinsip sinergi kemanusiaan non-profit dan siap berkoordinasi dalam ekosistem {{ ecosystem.name }} di bawah tata kelola Yayasan MKT Indonesia.
                            </label>
                        </div>

                        <!-- Modal Actions -->
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end space-x-3">
                            <button
                                type="button"
                                @click="showJoinModal = false"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="joinForm.processing || !joinForm.terms_accepted"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold shadow-md disabled:opacity-50"
                            >
                                {{ joinForm.processing ? 'Memproses...' : 'Kirim Pendaftaran' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </template>
    </PublicLayout>
</template>
