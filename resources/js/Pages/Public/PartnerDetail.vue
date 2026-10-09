<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

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
    otherPartners: {
        type: Array,
        default: () => [],
    },
    mktProfile: {
        type: Object,
        default: null,
    },
});

// Tab state
const activeTab = ref('profil'); // 'profil', 'pilar', 'personel', 'misi'

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
                gradient: 'from-blue-900 via-indigo-900 to-slate-950',
                label: 'Operasi SAR & Evakuasi',
            };
        case 'BPBD':
            return {
                icon: '🏛️',
                badgeBg: 'bg-amber-600',
                badgeText: 'text-white',
                gradient: 'from-amber-900 via-orange-950 to-slate-950',
                label: 'Komando Darurat & Mitigasi',
            };
        case 'PMI':
            return {
                icon: '🩸',
                badgeBg: 'bg-rose-600',
                badgeText: 'text-white',
                gradient: 'from-rose-950 via-red-950 to-slate-950',
                label: 'Donor Darah & Pelayanan Medis',
            };
        case 'Rumah Sakit':
            return {
                icon: '🏥',
                badgeBg: 'bg-teal-600',
                badgeText: 'text-white',
                gradient: 'from-teal-950 via-cyan-950 to-slate-950',
                label: 'Fasilitas Medis Rujukan',
            };
        case 'Filantropi':
            return {
                icon: '🤝',
                badgeBg: 'bg-emerald-600',
                badgeText: 'text-white',
                gradient: 'from-emerald-950 via-teal-950 to-slate-950',
                label: 'Penyalur Donasi & Program CSR',
            };
        case 'Tim Rescue':
        default:
            return {
                icon: '🚨',
                badgeBg: 'bg-orange-600',
                badgeText: 'text-white',
                gradient: 'from-orange-950 via-amber-950 to-slate-950',
                label: 'Unit Potensi SAR & Rescue',
            };
    }
});
</script>

<template>
    <PublicLayout 
        :title="`${partner.name} - Mitra Resmi Yayasan MKT Indonesia`"
        :description="partner.description || `Profil resmi kemitraan dan kolaborasi ${partner.name} bersama Yayasan MKT Indonesia.`"
    >
        <template #default="{ openCtaModal }">
            <div class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen">
                
                <!-- BREADCRUMB NAVIGATION -->
                <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 py-3">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            <Link :href="route('home')" class="hover:text-orange-600 dark:hover:text-orange-400 transition">
                                Beranda
                            </Link>
                            <span>/</span>
                            <Link :href="route('public.partners')" class="hover:text-orange-600 dark:hover:text-orange-400 transition">
                                Mitra Resmi
                            </Link>
                            <span>/</span>
                            <span class="text-slate-900 dark:text-white font-bold truncate max-w-xs sm:max-w-md">
                                {{ partner.name }}
                            </span>
                        </nav>
                    </div>
                </div>

                <!-- HERO BANNER SECTION -->
                <section class="relative overflow-hidden">
                    <!-- Background Cover (Uploaded Banner or Rich Dynamic Gradient) -->
                    <div class="h-64 sm:h-80 md:h-96 w-full relative bg-slate-900">
                        <template v-if="partner.banner_path">
                            <img 
                                :src="partner.banner_path" 
                                :alt="partner.name" 
                                class="w-full h-full object-cover"
                            />
                        </template>
                        <template v-else>
                            <div :class="['w-full h-full bg-gradient-to-r', categoryMeta.gradient, 'relative overflow-hidden']">
                                <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                                <div class="absolute -right-10 -bottom-10 w-96 h-96 rounded-full bg-orange-500/10 blur-3xl"></div>
                            </div>
                        </template>
                        
                        <!-- Overlay Darkening Gradient for Text Contrast -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
                    </div>

                    <!-- Main Hero Info Card (Overlapping Cover) -->
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-28 sm:-mt-36 relative z-10 pb-8">
                        <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl border border-slate-200/80 dark:border-slate-800">
                            
                            <div class="flex flex-col md:flex-row md:items-start gap-6 sm:gap-8">
                                <!-- Logo Box -->
                                <div class="relative shrink-0 flex justify-center md:justify-start">
                                    <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl bg-white dark:bg-slate-950 p-3 shadow-xl border-2 border-slate-100 dark:border-slate-800 flex items-center justify-center overflow-hidden">
                                        <img 
                                            v-if="partner.logo_path" 
                                            :src="partner.logo_path" 
                                            :alt="partner.name" 
                                            class="w-full h-full object-contain"
                                        />
                                        <div v-else class="text-4xl sm:text-5xl flex items-center justify-center w-full h-full bg-slate-100 dark:bg-slate-800 rounded-2xl">
                                            {{ categoryMeta.icon }}
                                        </div>
                                    </div>
                                    <span 
                                        v-if="partner.status === 'Aktif' || partner.status === 'Siaga Bencana'" 
                                        class="absolute -bottom-2 sm:-bottom-3 left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:right-0 px-3 py-1 rounded-full text-[10px] sm:text-xs font-black bg-emerald-500 text-white shadow-md flex items-center space-x-1"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                        <span>{{ partner.status }}</span>
                                    </span>
                                </div>

                                <!-- Partner Information Summary -->
                                <div class="flex-1 text-center md:text-left space-y-3">
                                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                                        <span :class="['px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider', categoryMeta.badgeBg, categoryMeta.badgeText]">
                                            {{ categoryMeta.icon }} {{ partner.category }}
                                        </span>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            ID: {{ partner.code || 'MTR-MKT' }}
                                        </span>
                                        <span v-if="partner.mou_number && partner.mou_number !== '-'" class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                            MoU: {{ partner.mou_number }}
                                        </span>
                                    </div>

                                    <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                                        {{ partner.name }}
                                    </h1>

                                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-3xl leading-relaxed">
                                        {{ partner.description || 'Mitra strategis resmi yang berkolaborasi dalam jejaring kemanusiaan, penanggulangan bencana, dan kesiapsiagaan darurat bersama Yayasan MKT Indonesia.' }}
                                    </p>

                                    <!-- Quick Contact and Action Row -->
                                    <div class="pt-4 flex flex-wrap items-center justify-center md:justify-start gap-3">
                                        <!-- Gabung Relawan Mitra CTA -->
                                        <button 
                                            @click="showRegisterModal = true"
                                            class="px-5 py-3 rounded-2xl bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-black text-xs sm:text-sm shadow-lg shadow-orange-500/25 active:scale-95 transition flex items-center space-x-2"
                                        >
                                            <span>🤝</span>
                                            <span>Gabung Relawan / Anggota Mitra</span>
                                        </button>

                                        <!-- Direct WhatsApp PIC -->
                                        <a 
                                            v-if="whatsappUrl" 
                                            :href="whatsappUrl" 
                                            target="_blank" 
                                            rel="noopener noreferrer"
                                            class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-md active:scale-95 transition flex items-center space-x-2"
                                        >
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.179.182-.077.357.101.174.449.741.963 1.2 0.662.592 1.22.776 1.393.863.174.087.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.144.39-.087s1.011.477 1.184.564.289.13.332.202c.044.073.044.42-.1 1.225z"/>
                                            </svg>
                                            <span>Hubungi Narahubung</span>
                                        </a>

                                        <!-- Salin Tautan / Share -->
                                        <button 
                                            @click="copyCurrentUrl"
                                            class="px-4 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs sm:text-sm border border-slate-200 dark:border-slate-700 transition flex items-center space-x-1.5"
                                        >
                                            <span v-if="copied">✅ Berhasil Disalin!</span>
                                            <span v-else>🔗 Bagikan Halaman</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Highlights Key Metrics -->
                            <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider block">Total Personel</span>
                                    <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5 block">
                                        {{ totalMembersCount }} Personel
                                    </span>
                                </div>
                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider block">Kategori Lembaga</span>
                                    <span class="text-xl sm:text-2xl font-black text-orange-600 dark:text-orange-400 mt-0.5 block truncate">
                                        {{ partner.category }}
                                    </span>
                                </div>
                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider block">Status Kolaborasi</span>
                                    <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 block">
                                        {{ partner.status }}
                                    </span>
                                </div>
                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider block">Kerjasama MKT</span>
                                    <span class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 mt-0.5 block">
                                        Resmi
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>

                <!-- NAVIGATION TABS -->
                <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 pb-6">
                    <div class="flex items-center space-x-2 overflow-x-auto pb-2 border-b border-slate-200 dark:border-slate-800">
                        <button
                            @click="activeTab = 'profil'"
                            :class="[
                                activeTab === 'profil'
                                    ? 'bg-orange-600 text-white shadow-md font-bold'
                                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium',
                                'px-5 py-2.5 rounded-2xl text-xs sm:text-sm shrink-0 transition-all flex items-center space-x-2'
                            ]"
                        >
                            <span>🏢</span>
                            <span>Profil & Visi Misi</span>
                        </button>

                        <button
                            @click="activeTab = 'pilar'"
                            :class="[
                                activeTab === 'pilar'
                                    ? 'bg-orange-600 text-white shadow-md font-bold'
                                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium',
                                'px-5 py-2.5 rounded-2xl text-xs sm:text-sm shrink-0 transition-all flex items-center space-x-2'
                            ]"
                        >
                            <span>🛡️</span>
                            <span>Peran 3 Pilar Bencana</span>
                        </button>

                        <button
                            @click="activeTab = 'personel'"
                            :class="[
                                activeTab === 'personel'
                                    ? 'bg-orange-600 text-white shadow-md font-bold'
                                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium',
                                'px-5 py-2.5 rounded-2xl text-xs sm:text-sm shrink-0 transition-all flex items-center space-x-2'
                            ]"
                        >
                            <span>👥</span>
                            <span>Personel & Relawan ({{ totalMembersCount }})</span>
                        </button>

                        <button
                            @click="activeTab = 'misi'"
                            :class="[
                                activeTab === 'misi'
                                    ? 'bg-orange-600 text-white shadow-md font-bold'
                                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium',
                                'px-5 py-2.5 rounded-2xl text-xs sm:text-sm shrink-0 transition-all flex items-center space-x-2'
                            ]"
                        >
                            <span>🚨</span>
                            <span>Aksi & Operasi SAR ({{ sarMissions.length }})</span>
                        </button>
                    </div>
                </section>

                <!-- TAB CONTENT AREA -->
                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 space-y-12">
                    
                    <!-- TAB 1: PROFIL & VISI MISI -->
                    <div v-if="activeTab === 'profil'" class="space-y-8 animate-fadeIn">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            
                            <!-- Main Text Details -->
                            <div class="lg:col-span-8 space-y-8">
                                <!-- Deskripsi Lengkap -->
                                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
                                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center space-x-2.5">
                                        <span class="p-2 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 text-lg">📖</span>
                                        <span>Tentang {{ partner.name }}</span>
                                    </h2>
                                    <div class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line space-y-3">
                                        <p>{{ partner.description || 'Lembaga ini berdedikasi dalam penanganan kemanusiaan, respon kebencanaan, serta pembinaan relawan dalam bingkai koordinasi terpadu Yayasan MKT Indonesia.' }}</p>
                                    </div>
                                </div>

                                <!-- Visi & Misi -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-500/10 text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                            🎯 VISI
                                        </span>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Arah & Komitmen</h3>
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                            {{ partner.vision || 'Mewujudkan kesiapsiagaan masyarakat dan kapasitas penyelamatan yang tangguh, profesional, serta berjiwa kemanusiaan tinggi.' }}
                                        </p>
                                    </div>

                                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                            🚀 MISI
                                        </span>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Langkah Nyata</h3>
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                                            {{ partner.mission || '1. Membina relawan rescuer berkompetensi tinggi.\n2. Siaga merespon situasi darurat kemanusiaan.\n3. Mempererat koordinasi dan pertukaran informasi kebencanaan terpadu.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Side Contact & Office Card -->
                            <div class="lg:col-span-4 space-y-6">
                                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center space-x-2">
                                        <span>📍</span>
                                        <span>Markas & Narahubung</span>
                                    </h3>

                                    <div class="space-y-4 text-xs sm:text-sm">
                                        <!-- Alamat -->
                                        <div class="space-y-1">
                                            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">Alamat Posko / Markas</span>
                                            <p class="font-semibold text-slate-800 dark:text-slate-200">
                                                {{ partner.address || 'Kota Makassar, Sulawesi Selatan' }}
                                            </p>
                                        </div>

                                        <!-- Narahubung -->
                                        <div class="space-y-1">
                                            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">Koordinator / PIC</span>
                                            <p class="font-semibold text-slate-800 dark:text-slate-200">
                                                {{ partner.pic_name || 'Sekretariat Mitra' }}
                                            </p>
                                        </div>

                                        <!-- Kontak Telepon / WA -->
                                        <div class="space-y-1">
                                            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">Nomor Kontak Darurat</span>
                                            <p class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                                {{ partner.pic_phone || partner.phone || '0812-xxxx-xxxx' }}
                                            </p>
                                        </div>

                                        <!-- Email -->
                                        <div class="space-y-1">
                                            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">Surat Elektronik (Email)</span>
                                            <p class="font-mono text-slate-800 dark:text-slate-200 break-all">
                                                {{ partner.email || 'info@mitra.org' }}
                                            </p>
                                        </div>

                                        <!-- Website / Sosmed -->
                                        <div v-if="partner.website || partner.instagram" class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                                            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block">Media Resmi</span>
                                            <div class="flex flex-wrap gap-2">
                                                <a 
                                                    v-if="partner.website" 
                                                    :href="partner.website" 
                                                    target="_blank" 
                                                    rel="noopener"
                                                    class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-orange-500/10 hover:text-orange-600 text-xs font-semibold transition"
                                                >
                                                    🌐 Website Resmi
                                                </a>
                                                <a 
                                                    v-if="partner.instagram" 
                                                    :href="partner.instagram" 
                                                    target="_blank" 
                                                    rel="noopener"
                                                    class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-pink-500/10 hover:text-pink-600 text-xs font-semibold transition"
                                                >
                                                    📸 Instagram
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Kolaborasi -->
                                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                                        <button 
                                            @click="openCtaModal('mitra')"
                                            class="w-full py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs transition"
                                        >
                                            Ajukan Kolaborasi Bersama MKT
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- TAB 2: PERAN DALAM 3 PILAR SIKLUS KEBENCANAAN -->
                    <div v-if="activeTab === 'pilar'" class="space-y-6 animate-fadeIn">
                        <div class="text-center max-w-2xl mx-auto space-y-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                🛡️ TATA KELOLA KEMITRAAN TERPADU
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                Integrasi {{ partner.name }} di 3 Pilar Bencana
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Kolaborasi berkesinambungan sebelum musibah, saat penanganan darurat, hingga tahap rehabilitasi pasca-bencana.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                            <!-- Pra-Bencana -->
                            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4 hover:border-blue-500/40 transition">
                                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl">
                                    🗺️
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-600 text-white uppercase">
                                    Fase 1: Pra-Bencana
                                </span>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Mitigasi & Kesiapsiagaan</h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                    Pelatihan bersama personel rescuer, pemetaan jalur evakuasi, penyusunan database pendonor darah darurat, dan perawatan peralatan teknis penyelamatan di markas posko.
                                </p>
                            </div>

                            <!-- Saat Bencana -->
                            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4 hover:border-rose-500/40 transition">
                                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl">
                                    🚨
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white uppercase">
                                    Fase 2: Saat Bencana
                                </span>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tanggap Darurat & SAR</h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                    Pengerahan cepat personel SAR ke titik koordinat terdampak, evakuasi warga dan korban, pendirian posko medis lapangan, serta koordinasi frekuensi radio bersama Pusat Komando MKT.
                                </p>
                            </div>

                            <!-- Pasca-Bencana -->
                            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4 hover:border-emerald-500/40 transition">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl">
                                    🌱
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white uppercase">
                                    Fase 3: Pasca-Bencana
                                </span>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pemulihan & Trauma Healing</h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                    Pendampingan psikososial keluarga terdampak, distribusi logistik pemulihan, perbaikan fasilitas air bersih dan sanitasi, serta evaluasi menyeluruh operasi bersama tim gabungan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: PERSONEL & RELAWAN -->
                    <div v-if="activeTab === 'personel'" class="space-y-6 animate-fadeIn">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                                    Personel & Potensi SAR Terdaftar
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                    Total {{ totalMembersCount }} personel siap bertugas di bawah naungan {{ partner.name }}.
                                </p>
                            </div>
                            <button 
                                @click="showRegisterModal = true"
                                class="px-5 py-2.5 rounded-2xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-md transition self-start sm:self-auto"
                            >
                                + Gabung Sebagai Personel
                            </button>
                        </div>

                        <div v-if="members.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div 
                                v-for="m in members" 
                                :key="m.id"
                                class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="w-10 h-10 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center font-black text-sm">
                                        {{ m.name.charAt(0) }}
                                    </div>
                                    <span v-if="m.blood_type" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                        Gol: {{ m.blood_type }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ m.name }}</h4>
                                    <p class="text-xs text-orange-600 dark:text-orange-400 font-medium">{{ m.role }}</p>
                                </div>
                                <div v-if="m.certifications" class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate">
                                        Sertifikasi: {{ m.certifications }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-else class="bg-white dark:bg-slate-900 rounded-3xl p-10 text-center border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <span class="text-4xl">👥</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Data Personel Terpusat</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                Personel terdaftar saat ini tercatat sebanyak {{ totalMembersCount }} personel di basis data internal komando.
                            </p>
                            <div class="pt-2">
                                <button @click="showRegisterModal = true" class="px-6 py-2.5 rounded-xl bg-orange-600 text-white font-bold text-xs shadow-md">
                                    Daftarkan Diri Anda ke Lembaga Ini
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: AKSI & OPERASI SAR -->
                    <div v-if="activeTab === 'misi'" class="space-y-6 animate-fadeIn">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                                    Aksi Lapangan & Partisipasi Operasi SAR
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                    Rekam jejak keterlibatan personel {{ partner.name }} dalam penanganan bencana dan musibah darurat.
                                </p>
                            </div>
                        </div>

                        <div v-if="sarMissions.length > 0" class="space-y-4">
                            <div 
                                v-for="mission in sarMissions" 
                                :key="mission.id"
                                class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-orange-500/40 transition"
                            >
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white uppercase">
                                            {{ mission.status }}
                                        </span>
                                        <span v-if="mission.operation" class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                            {{ mission.operation.title }}
                                        </span>
                                    </div>
                                    <h4 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                        {{ mission.organization_name }}
                                    </h4>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                                        Komandan Tim: <strong class="text-slate-900 dark:text-white">{{ mission.commander_name }}</strong> • Kekuatan: <strong class="text-orange-600 dark:text-orange-400">{{ mission.personnel_count }} Personel</strong>
                                    </p>
                                    <p v-if="mission.resources_deployed" class="text-xs text-slate-500 dark:text-slate-400">
                                        Alat Diterjunkan: {{ mission.resources_deployed }}
                                    </p>
                                </div>
                                <div class="shrink-0 flex items-center">
                                    <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        Posko: {{ mission.departure_location || 'Makassar' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-else class="bg-white dark:bg-slate-900 rounded-3xl p-10 text-center border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <span class="text-4xl">🚨</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Kesiapsiagaan Posko Siaga</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                {{ partner.name }} berada dalam status kesiapsiagaan penuh on-call untuk mobilisasi pengerahan personel SAR saat status darurat kebencanaan diaktifkan.
                            </p>
                        </div>
                    </div>

                    <!-- SECTION: MITRA STRATEGIS LAINNYA -->
                    <section v-if="otherPartners.length > 0" class="pt-8 border-t border-slate-200 dark:border-slate-800 space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                                    Jelajahi Mitra Resmi Lainnya
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Jaringan kolaborasi tanggap darurat dan potensi SAR Yayasan MKT.
                                </p>
                            </div>
                            <Link :href="route('public.partners')" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline">
                                Lihat Semua Mitra →
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <Link 
                                v-for="op in otherPartners" 
                                :key="op.id"
                                :href="route('public.partner.show', op.slug || op.id)"
                                class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 hover:shadow-lg transition-all group space-y-3 flex flex-col justify-between"
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
                    </section>

                </main>

                <!-- MODAL: DAFTAR RELAWAN DI BAWAH MITRA INI -->
                <div v-if="showRegisterModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs animate-fadeIn">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6 relative max-h-[90vh] overflow-y-auto">
                        
                        <!-- Close button -->
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

                        <!-- Success Alert -->
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
                                    placeholder="Contoh: Sertifikasi Water Rescue, Pengalaman Evakuasi, dll."
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
