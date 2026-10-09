<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import heroRescueImg from '../../../images/hero_rescue.jpg';
import bloodDonorImg from '../../../images/blood_donor.jpg';
import pillarPreImg from '../../../images/pillar_pre.jpg';
import pillarDuringImg from '../../../images/pillar_during.jpg';

const props = defineProps({
    partner: {
        type: Object,
        required: true,
    },
    members: {
        type: Array,
        default: () => [],
    },
    totalMembersCount: {
        type: Number,
        default: 0,
    },
    sarMissions: {
        type: Array,
        default: () => [],
    },
    partnerNews: {
        type: Array,
        default: () => [],
    },
    otherPartners: {
        type: Array,
        default: () => [],
    },
    mktProfile: {
        type: Object,
        default: null,
    },
});

// Modal Registrasi Khusus Mitra Ini
const showRegisterModal = ref(false);
const registerSuccess = ref(false);

const registerForm = useForm({
    partner_id: props.partner.id,
    name: '',
    email: '',
    phone: '',
    blood_type: 'O',
    role: props.partner.category === 'PMI' ? 'Donor Darah' : (props.partner.category === 'Rumah Sakit' ? 'Tenaga Medis' : 'Relawan Rescuer'),
    password: '',
    notes: '',
});

const submitRegister = () => {
    registerForm.post(route('volunteers.public-register'), {
        preserveScroll: true,
        onSuccess: () => {
            registerSuccess.value = true;
            registerForm.reset();
            setTimeout(() => {
                showRegisterModal.value = false;
                registerSuccess.value = false;
            }, 3000);
        },
    });
};

// Copy Share Link
const copied = ref(false);
const copyCurrentUrl = () => {
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2500);
    }
};

// Format WhatsApp URL
const whatsappUrl = computed(() => {
    const rawPhone = props.partner.pic_phone || props.partner.phone || '';
    if (!rawPhone) return null;
    let clean = rawPhone.replace(/\D/g, '');
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    }
    const message = encodeURIComponent(`Halo ${props.partner.name}, saya mendapatkan informasi melalui Portal Resmi Yayasan MKT Indonesia dan ingin berkoordinasi.`);
    return `https://wa.me/${clean}?text=${message}`;
});

// Category Styling Helper
const categoryMeta = computed(() => {
    switch (props.partner.category) {
        case 'Basarnas':
            return {
                icon: '⚓',
                badgeBg: 'bg-blue-600',
                badgeText: 'text-white',
                gradient: 'from-blue-950 via-slate-900 to-indigo-950',
                accentColor: 'text-blue-500',
                label: 'Operasi SAR & Evakuasi',
            };
        case 'BPBD':
            return {
                icon: '🏛️',
                badgeBg: 'bg-amber-600',
                badgeText: 'text-white',
                gradient: 'from-amber-950 via-slate-900 to-orange-950',
                accentColor: 'text-amber-500',
                label: 'Komando Darurat & Mitigasi',
            };
        case 'PMI':
            return {
                icon: '🩸',
                badgeBg: 'bg-rose-600',
                badgeText: 'text-white',
                gradient: 'from-rose-950 via-slate-900 to-red-950',
                accentColor: 'text-rose-500',
                label: 'Donor Darah & Pelayanan Medis',
            };
        case 'Rumah Sakit':
            return {
                icon: '🏥',
                badgeBg: 'bg-teal-600',
                badgeText: 'text-white',
                gradient: 'from-teal-950 via-slate-900 to-cyan-950',
                accentColor: 'text-teal-500',
                label: 'Fasilitas Medis Rujukan',
            };
        case 'Filantropi':
            return {
                icon: '🤝',
                badgeBg: 'bg-emerald-600',
                badgeText: 'text-white',
                gradient: 'from-emerald-950 via-slate-900 to-teal-950',
                accentColor: 'text-emerald-500',
                label: 'Penyalur Donasi & Program CSR',
            };
        case 'Tim Rescue':
        default:
            return {
                icon: '🚨',
                badgeBg: 'bg-orange-600',
                badgeText: 'text-white',
                gradient: 'from-orange-950 via-slate-900 to-amber-950',
                accentColor: 'text-orange-500',
                label: 'Unit Potensi SAR & Rescue Lapangan',
            };
    }
});

const heroBackgroundImage = computed(() => {
    if (props.partner.banner_path) {
        return props.partner.banner_path;
    }
    switch (props.partner.category) {
        case 'PMI':
            return bloodDonorImg;
        case 'BPBD':
            return pillarPreImg;
        case 'Rumah Sakit':
            return pillarDuringImg;
        case 'Basarnas':
        case 'Tim Rescue':
        default:
            return heroRescueImg;
    }
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <PublicLayout 
        :title="`${partner.name} - Mitra Resmi Yayasan MKT Indonesia`"
        :description="partner.description || `Profil resmi kemitraan dan kolaborasi ${partner.name} bersama Yayasan MKT Indonesia.`"
        :partner="partner"
    >
        <template #default="{ openCtaModal }">
            <div class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen scroll-smooth">
                
                <!-- ========================================== -->
                <!-- 1. HERO SECTION (id="home") -->
                <!-- ========================================== -->
                <section id="home" class="relative overflow-hidden bg-slate-950 text-white min-h-[620px] sm:min-h-[680px] flex items-center pt-8 pb-16">
                    
                    <!-- Hero Background Image with Aesthetic Cinematic Overlays -->
                    <div class="absolute inset-0 z-0 overflow-hidden">
                        <img 
                            :src="heroBackgroundImage" 
                            :alt="partner.name" 
                            class="w-full h-full object-cover object-center filter brightness-90 contrast-105 scale-105 transform animate-pulse duration-10000 opacity-40 dark:opacity-35"
                        />
                        <!-- Dynamic Color Tint Matching Category -->
                        <div :class="['absolute inset-0 bg-gradient-to-br', categoryMeta.gradient, 'opacity-65 mix-blend-multiply']"></div>
                        <!-- High Contrast Gradients for Text Readability -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-slate-950/50"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/60 to-transparent"></div>
                        <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:24px_24px]"></div>
                    </div>

                    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-8 space-y-8">
                        
                        <!-- Top Breadcrumb & Status Pill -->
                        <div class="flex flex-wrap items-center gap-3">
                            <Link 
                                :href="route('public.partners')" 
                                class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 hover:bg-white/20 text-slate-200 border border-white/10 transition"
                            >
                                <span>← Direktori Mitra</span>
                            </Link>

                            <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-500/20 text-orange-400 border border-orange-500/30">
                                <span>🤝 MITRA RESMI YAYASAN MKT</span>
                            </span>

                            <span 
                                v-if="partner.status === 'Aktif' || partner.status === 'Siaga Bencana'"
                                class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30"
                            >
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>{{ partner.status }}</span>
                            </span>
                        </div>

                        <!-- Main Hero Grid: Logo + Partner Title & Motto -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            
                            <div class="lg:col-span-8 space-y-6">
                                
                                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                                    <!-- Partner Logo Frame -->
                                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white dark:bg-slate-900 p-2.5 shadow-2xl border-2 border-white/20 shrink-0 flex items-center justify-center overflow-hidden">
                                        <img 
                                            v-if="partner.logo_path" 
                                            :src="partner.logo_path" 
                                            :alt="partner.name" 
                                            class="w-full h-full object-contain"
                                        />
                                        <div v-else class="text-4xl">
                                            {{ categoryMeta.icon }}
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <span :class="['px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider inline-block', categoryMeta.badgeBg, categoryMeta.badgeText]">
                                            {{ categoryMeta.icon }} {{ partner.category }} &bull; ID: {{ partner.code || 'MTR-MKT' }}
                                        </span>
                                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight">
                                            {{ partner.name }}
                                        </h1>
                                    </div>
                                </div>

                                <!-- Partner Motto / Tagline Description -->
                                <p class="text-sm sm:text-base md:text-lg text-slate-300 max-w-3xl leading-relaxed">
                                    {{ partner.description || 'Unit potensi kemanusiaan dan penanggulangan bencana yang terintegrasi secara resmi dalam jaringan koordinasi tanggap darurat Yayasan MKT Indonesia.' }}
                                </p>

                                <!-- Hero Action Buttons -->
                                <div class="pt-2 flex flex-wrap items-center gap-3.5">
                                    <button 
                                        @click="showRegisterModal = true"
                                        class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-xs sm:text-sm shadow-xl shadow-orange-500/25 active:scale-95 transition flex items-center space-x-2"
                                    >
                                        <span>🤝</span>
                                        <span>Gabung Relawan / Anggota Mitra</span>
                                    </button>

                                    <a 
                                        v-if="whatsappUrl" 
                                        :href="whatsappUrl" 
                                        target="_blank" 
                                        rel="noopener"
                                        class="px-5 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-lg active:scale-95 transition flex items-center space-x-2"
                                    >
                                        <span>💬 Hubungi Narahubung WhatsApp</span>
                                    </a>

                                    <a 
                                        href="#profil" 
                                        class="px-5 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/20 backdrop-blur-md transition flex items-center space-x-1.5"
                                    >
                                        <span>Pelajari Profil Lembaga ↓</span>
                                    </a>
                                </div>

                            </div>

                            <!-- Right Column: Quick Stats Card -->
                            <div class="lg:col-span-4">
                                <div class="bg-white/10 backdrop-blur-xl border border-white/15 rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5">
                                    <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                        <div>
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-orange-400 block">Status Kolaborasi</span>
                                            <span class="text-lg font-black text-white">Kerjasama Resmi MKT</span>
                                        </div>
                                        <span class="text-3xl">🛡️</span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Kekuatan Personel</span>
                                            <span class="text-2xl font-black text-white mt-0.5 block">
                                                {{ totalMembersCount }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">Personel Siaga</span>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Misi Operasi SAR</span>
                                            <span class="text-2xl font-black text-orange-400 mt-0.5 block">
                                                {{ sarMissions.length }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">Aksi Lapangan</span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-xs pt-1">
                                        <div class="flex justify-between py-1 border-b border-white/5">
                                            <span class="text-slate-400">Dokumen MoU:</span>
                                            <span class="font-mono font-bold text-slate-200">{{ partner.mou_number || 'Tervalidasi' }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-white/5">
                                            <span class="text-slate-400">Kesiapsiagaan:</span>
                                            <span class="font-bold text-emerald-400">Siaga Operasi 24/7</span>
                                        </div>
                                        <div class="flex justify-between py-1">
                                            <span class="text-slate-400">Koordinator PIC:</span>
                                            <span class="font-semibold text-slate-200">{{ partner.pic_name || 'Sekretariat' }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                </section>

                <!-- ========================================== -->
                <!-- 2. PROFIL, VISI MISI & 3 PILAR (id="profil") -->
                <!-- ========================================== -->
                <section id="profil" class="py-16 sm:py-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 scroll-mt-24">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                        
                        <!-- Header Section -->
                        <div class="text-center max-w-2xl mx-auto space-y-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                🏢 PROFIL RESMI LEMBAGA
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                Mengenal Lebih Dekat {{ partner.name }}
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Sinergi dedikasi, integritas, dan keahlian spesifik dalam jejaring aksi kemanusiaan.
                            </p>
                        </div>

                        <!-- Profil & Visi Misi Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            
                            <!-- Left: Detail Cerita Profil -->
                            <div class="lg:col-span-7 space-y-6">
                                <div class="bg-slate-50 dark:bg-slate-950 p-6 sm:p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                                    <h3 class="text-xl font-black text-slate-900 dark:text-white flex items-center space-x-2">
                                        <span>📖</span>
                                        <span>Latar Belakang & Peran Strategis</span>
                                    </h3>
                                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                        {{ partner.description || 'Lembaga ini merupakan bagian integral dari potensi Search and Rescue serta aksi kemanusiaan yang terkoordinasi bersama Yayasan MKT Indonesia, Basarnas, dan BPBD.' }}
                                    </p>
                                </div>

                                <!-- Visi & Misi Cards -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-slate-50 dark:bg-slate-950 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 space-y-3">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black bg-blue-500/10 text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                            🎯 VISI LEMBAGA
                                        </span>
                                        <h4 class="text-base font-bold text-slate-900 dark:text-white">Arah & Komitmen</h4>
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                            {{ partner.vision || 'Menjadi unit penanggulangan bencana dan kemanusiaan yang tangguh, profesional, serta responsif demi kemaslahatan masyarakat luas.' }}
                                        </p>
                                    </div>

                                    <div class="bg-slate-50 dark:bg-slate-950 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 space-y-3">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                            🚀 MISI UTAMA
                                        </span>
                                        <h4 class="text-base font-bold text-slate-900 dark:text-white">Aksi Nyata</h4>
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                            {{ partner.mission || '1. Membina relawan rescuer berkompetensi tinggi.\n2. Memberikan respon cepat dalam situasi darurat bencana.\n3. Membangun koordinasi erat bersama Pusdalops MKT.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Personel & Keahlian Inti -->
                            <div class="lg:col-span-5 space-y-6">
                                <div class="bg-slate-50 dark:bg-slate-950 p-6 sm:p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 space-y-6">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center space-x-2">
                                            <span>👥</span>
                                            <span>Personel & Potensi Tim</span>
                                        </h3>
                                        <span class="text-xs font-bold text-orange-600 dark:text-orange-400">
                                            {{ totalMembersCount }} Anggota
                                        </span>
                                    </div>

                                    <!-- Sample Member List -->
                                    <div v-if="members.length > 0" class="space-y-3">
                                        <div 
                                            v-for="m in members.slice(0, 5)" 
                                            :key="m.id"
                                            class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 flex items-center justify-between"
                                        >
                                            <div class="flex items-center space-x-3 min-w-0">
                                                <div class="w-9 h-9 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center font-black text-xs shrink-0">
                                                    {{ m.name.charAt(0) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ m.name }}</h5>
                                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate">{{ m.role }}</span>
                                                </div>
                                            </div>
                                            <span v-if="m.blood_type" class="px-2 py-0.5 rounded-full text-[9px] font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 shrink-0">
                                                Gol: {{ m.blood_type }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-6 text-slate-400 text-xs">
                                        Daftar personel aktif terpusat pada basis data Pusdalops.
                                    </div>

                                    <div class="pt-2">
                                        <button 
                                            @click="showRegisterModal = true"
                                            class="w-full py-3 rounded-2xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-md transition"
                                        >
                                            + Bergabung Bersama Tim Ini
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- 3 PILAR KEBENCANAAN SINERGI -->
                        <div class="pt-8 border-t border-slate-100 dark:border-slate-800 space-y-6">
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white text-center">
                                Peran {{ partner.name }} di 3 Pilar Siklus Kebencanaan
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-3">
                                    <span class="text-2xl p-2 rounded-xl bg-blue-500/10 inline-block">🗺️</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white uppercase block w-max">
                                        1. Pra-Bencana (Mitigasi)
                                    </span>
                                    <h4 class="font-bold text-base text-slate-900 dark:text-white">Kesiapsiagaan & Latihan</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                        Penyegaran teknik water rescue, jungle rescue, pemetaan rute evakuasi, dan pemeliharaan alat pelampung serta perahu karet di markas posko.
                                    </p>
                                </div>

                                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-3">
                                    <span class="text-2xl p-2 rounded-xl bg-rose-500/10 inline-block">🚨</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white uppercase block w-max">
                                        2. Saat Bencana (Darurat)
                                    </span>
                                    <h4 class="font-bold text-base text-slate-900 dark:text-white">Respon Cepat & Evakuasi</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                        Mobilisasi tim rescue dan rescuer ke lokasi terdampak, pencarian korban musibah, dan pendirian posko darurat bersama tim gabungan SAR.
                                    </p>
                                </div>

                                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-3">
                                    <span class="text-2xl p-2 rounded-xl bg-emerald-500/10 inline-block">🌱</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white uppercase block w-max">
                                        3. Pasca-Bencana (Pemulihan)
                                    </span>
                                    <h4 class="font-bold text-base text-slate-900 dark:text-white">Rehabilitasi & Evaluasi</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                        Distribusi bantuan penyintas, pemulihan sarana air bersih dan fasilitas ibadah, serta pelaporan evaluasi operasi kepada komando MKT.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- ========================================== -->
                <!-- 3. BERITA & ARTIKEL TERKINI (id="berita") -->
                <!-- ========================================== -->
                <section id="berita" class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 scroll-mt-24">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                        
                        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                            <div class="space-y-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                    📰 INFORMASI & PUBLIKASI
                                </span>
                                <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                    Berita & Dokumentasi Lapangan
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                    Aksi kemanusiaan, sosialisasi, dan kabar terkini penanggulangan bencana.
                                </p>
                            </div>
                            <Link :href="route('public.news')" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline">
                                Lihat Semua Berita &bull; Portal MKT →
                            </Link>
                        </div>

                        <!-- News Grid -->
                        <div v-if="partnerNews.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                            <article 
                                v-for="news in partnerNews" 
                                :key="news.id"
                                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl hover:border-orange-500/40 transition-all flex flex-col justify-between group"
                            >
                                <div class="space-y-4">
                                    <!-- Image Thumbnail -->
                                    <div class="h-48 w-full bg-slate-800 relative overflow-hidden">
                                        <img 
                                            v-if="news.image_path" 
                                            :src="news.image_path" 
                                            :alt="news.title" 
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-500">
                                            📰 Dokumentasi MKT
                                        </div>
                                        <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-600 text-white shadow-md">
                                            {{ news.category || 'Kemanusiaan' }}
                                        </span>
                                    </div>

                                    <!-- Content -->
                                    <div class="px-6 space-y-2">
                                        <span class="text-[11px] font-medium text-slate-400 block">
                                            📅 {{ formatDate(news.created_at) }}
                                        </span>
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors line-clamp-2">
                                            {{ news.title }}
                                        </h3>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed">
                                            {{ news.content ? news.content.replace(/<[^>]*>/g, '') : '' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                                    <Link 
                                        :href="route('public.news.show', news.slug || news.id)"
                                        class="inline-flex items-center space-x-1.5 text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline"
                                    >
                                        <span>Baca Selengkapnya</span>
                                        <span>→</span>
                                    </Link>
                                </div>
                            </article>
                        </div>

                        <div v-else class="bg-white dark:bg-slate-900 rounded-3xl p-10 text-center border border-slate-200/80 dark:border-slate-800 space-y-2">
                            <span class="text-3xl">📰</span>
                            <h4 class="font-bold text-base text-slate-900 dark:text-white">Publikasi Belum Tersedia</h4>
                            <p class="text-xs text-slate-400">Liputan kegiatan terbaru akan dipublikasikan secara berkala.</p>
                        </div>

                    </div>
                </section>

                <!-- ========================================== -->
                <!-- 4. KONTAK & POSKO OPERASI (id="kontak") -->
                <!-- ========================================== -->
                <section id="kontak" class="py-16 sm:py-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 scroll-mt-24">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                        
                        <div class="text-center max-w-2xl mx-auto space-y-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                📍 NARAHUBUNG & MARKAS
                            </span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                                Hubungi {{ partner.name }}
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Terbuka untuk koordinasi misi kemanusiaan, respon darurat, dan registrasi relawan.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            
                            <!-- Contact Cards Grid -->
                            <div class="lg:col-span-6 space-y-4">
                                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-2">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Markas / Alamat Posko Operasi</span>
                                    <h4 class="text-base font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                                        <span>📍</span>
                                        <span>{{ partner.address || 'Kota Makassar, Sulawesi Selatan' }}</span>
                                    </h4>
                                    <p class="text-xs text-slate-500">Pusat koordinasi penugasan relawan dan logistik penyelamatan.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-2">
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Koordinator PIC</span>
                                        <h4 class="text-base font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                                            <span>👤</span>
                                            <span>{{ partner.pic_name || 'Narahubung Mitra' }}</span>
                                        </h4>
                                        <p class="text-xs text-slate-500">Penanggung jawab operasional.</p>
                                    </div>

                                    <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-2">
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kontak WhatsApp / Telp</span>
                                        <h4 class="text-base font-mono font-bold text-orange-600 dark:text-orange-400 flex items-center space-x-2">
                                            <span>📞</span>
                                            <span>{{ partner.pic_phone || partner.phone || '0812-xxxx-xxxx' }}</span>
                                        </h4>
                                        <p class="text-xs text-slate-500">Saluran darurat aktif.</p>
                                    </div>
                                </div>

                                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-2">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Surat Elektronik & Media Resmi</span>
                                    <p class="text-sm font-mono text-slate-900 dark:text-white">
                                        ✉️ {{ partner.email || 'info@mitra.org' }}
                                    </p>
                                    <div class="pt-2 flex flex-wrap gap-2">
                                        <a 
                                            v-if="partner.website" 
                                            :href="partner.website" 
                                            target="_blank" 
                                            rel="noopener"
                                            class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-orange-600 transition"
                                        >
                                            🌐 Website
                                        </a>
                                        <a 
                                            v-if="partner.instagram" 
                                            :href="partner.instagram" 
                                            target="_blank" 
                                            rel="noopener"
                                            class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border text-xs font-bold text-pink-600 transition"
                                        >
                                            📸 Instagram
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Quick Connect Form -->
                            <div class="lg:col-span-6 bg-gradient-to-br from-slate-950 to-slate-900 text-white p-8 sm:p-10 rounded-3xl shadow-2xl border border-slate-800 space-y-6">
                                <div class="space-y-2">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-500 text-white">
                                        SIAP BERSINERGI
                                    </span>
                                    <h3 class="text-2xl font-black tracking-tight">
                                        Tertarik Bergabung atau Kolaborasi Lapangan?
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                        Daftarkan diri Anda sebagai relawan atau kirimkan pesan langsung kepada tim {{ partner.name }}.
                                    </p>
                                </div>

                                <div class="pt-2 flex flex-col sm:flex-row gap-3">
                                    <button 
                                        @click="showRegisterModal = true"
                                        class="px-6 py-4 rounded-2xl bg-orange-600 hover:bg-orange-500 text-white font-black text-xs sm:text-sm shadow-xl active:scale-95 transition text-center"
                                    >
                                        🤝 Isi Formulir Pendaftaran Relawan
                                    </button>

                                    <a 
                                        v-if="whatsappUrl" 
                                        :href="whatsappUrl" 
                                        target="_blank" 
                                        rel="noopener"
                                        class="px-6 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-xl active:scale-95 transition text-center"
                                    >
                                        💬 WhatsApp Koordinator
                                    </a>
                                </div>

                                <div class="pt-4 border-t border-slate-800 text-[11px] text-slate-400">
                                    Terdaftar secara resmi dalam Sistem Manajemen Yayasan MKT Indonesia (Nomor Kode: <strong class="text-white">{{ partner.code }}</strong>).
                                </div>
                            </div>

                        </div>

                    </div>
                </section>

                <!-- ========================================== -->
                <!-- 5. MITRA STRATEGIS LAINNYA -->
                <!-- ========================================== -->
                <section v-if="otherPartners.length > 0" class="py-16 bg-slate-50 dark:bg-slate-950">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                                    Jelajahi Mitra Resmi Lainnya
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Potensi kemanusiaan terpadu di bawah naungan Yayasan MKT.
                                </p>
                            </div>
                            <Link :href="route('public.partners')" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline">
                                Semua Mitra →
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <Link 
                                v-for="op in otherPartners" 
                                :key="op.id"
                                :href="route('public.partner.show', op.slug || op.id)"
                                class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 hover:shadow-lg transition-all group flex flex-col justify-between space-y-3"
                            >
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-2xl p-2 rounded-xl bg-slate-100 dark:bg-slate-800">
                                            {{ op.category === 'Basarnas' ? '⚓' : (op.category === 'BPBD' ? '🏛️' : (op.category === 'PMI' ? '🩸' : (op.category === 'Rumah Sakit' ? '🏥' : '🚨'))) }}
                                        </span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                            {{ op.category }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition truncate">
                                        {{ op.name }}
                                    </h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                        {{ op.description || 'Mitra resmi Yayasan MKT Indonesia.' }}
                                    </p>
                                </div>
                                <div class="pt-2 text-xs font-bold text-orange-600 dark:text-orange-400 flex items-center space-x-1">
                                    <span>Buka Landing Page</span>
                                    <span>→</span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </section>

                <!-- MODAL REGISTRASI RELAWAN MITRA -->
                <div v-if="showRegisterModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs animate-fadeIn">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6 relative max-h-[90vh] overflow-y-auto">
                        
                        <button 
                            @click="showRegisterModal = false"
                            class="absolute top-5 right-5 w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center text-sm font-bold transition"
                        >
                            ✕
                        </button>

                        <div class="space-y-1">
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                🤝 FORMULIR ANGGOTA & RELAWAN
                            </span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                                Bergabung bersama {{ partner.name }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Pendaftaran Anda akan otomatis terafiliasi dengan lembaga mitra {{ partner.name }} dalam ekosistem Yayasan MKT.
                            </p>
                        </div>

                        <div v-if="registerSuccess" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs sm:text-sm font-semibold space-y-1">
                            <p class="font-bold">🎉 Pendaftaran Berhasil!</p>
                            <p>Data Anda telah terdaftar sebagai relawan/personel {{ partner.name }}. Kami telah mengirimkan detail akses ke email Anda.</p>
                        </div>

                        <form v-else @submit.prevent="submitRegister" class="space-y-4 text-left">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                                <input 
                                    v-model="registerForm.name" 
                                    type="text" 
                                    required 
                                    placeholder="Nama sesuai KTP"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                />
                                <span v-if="registerForm.errors.name" class="text-[11px] text-rose-500">{{ registerForm.errors.name }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Aktif *</label>
                                    <input 
                                        v-model="registerForm.email" 
                                        type="email" 
                                        required 
                                        placeholder="nama@email.com"
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                    />
                                    <span v-if="registerForm.errors.email" class="text-[11px] text-rose-500">{{ registerForm.errors.email }}</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. WhatsApp / HP *</label>
                                    <input 
                                        v-model="registerForm.phone" 
                                        type="tel" 
                                        required 
                                        placeholder="08123456789"
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                    />
                                    <span v-if="registerForm.errors.phone" class="text-[11px] text-rose-500">{{ registerForm.errors.phone }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Golongan Darah</label>
                                    <select 
                                        v-model="registerForm.blood_type"
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                    >
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                        <option value="-">Tidak Tahu / Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Keahlian / Peran</label>
                                    <select 
                                        v-model="registerForm.role"
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                    >
                                        <option value="Relawan Rescuer">Relawan Rescuer</option>
                                        <option value="Tim Rescue">Tim Rescue Lapangan</option>
                                        <option value="Tenaga Medis">Tenaga Medis / First Aid</option>
                                        <option value="Donor Darah">Relawan Donor Darah</option>
                                        <option value="Relawan Logistik">Logistik & Dapur Lapangan</option>
                                        <option value="Anggota Personel">Anggota Personel Lembaga</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Akun (Opsional)</label>
                                <input 
                                    v-model="registerForm.password" 
                                    type="password" 
                                    placeholder="Minimal 6 karakter (default: password123)"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan / Keterangan Keahlian</label>
                                <textarea 
                                    v-model="registerForm.notes" 
                                    rows="2" 
                                    placeholder="Contoh: Pengalaman Water Rescue, Sertifikasi Basarnas, dll."
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                ></textarea>
                            </div>

                            <div class="pt-3 flex items-center justify-end space-x-3">
                                <button 
                                    type="button" 
                                    @click="showRegisterModal = false"
                                    class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    :disabled="registerForm.processing"
                                    class="px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-md transition disabled:opacity-50"
                                >
                                    <span v-if="registerForm.processing">Memproses...</span>
                                    <span v-else>Kirim Pendaftaran</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </template>
    </PublicLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>
