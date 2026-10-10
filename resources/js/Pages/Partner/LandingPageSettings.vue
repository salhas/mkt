<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    partner: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({
            totalVolunteers: 0,
            sarMissionsCount: 0,
        }),
    },
    landingPageUrl: {
        type: String,
        required: true,
    },
});

const activeTab = ref('hero'); // 'hero', 'profil', 'pillars', 'recruitment', 'contact'
const copied = ref(false);

const form = useForm({
    name: props.partner.name || '',
    slug: props.partner.slug || '',
    category: props.partner.category || 'Tim Rescue',
    tagline: props.partner.tagline || '',
    description: props.partner.description || '',
    vision: props.partner.vision || '',
    mission: props.partner.mission || '',
    mou_number: props.partner.mou_number || '',
    readiness_status: props.partner.readiness_status || 'Siaga Operasi 24/7',
    recruitment_status: props.partner.recruitment_status || 'Buka',
    personnel_count: props.partner.personnel_count || 0,
    membership_terms: props.partner.membership_terms || '',
    pillar_pre: props.partner.pillar_pre || '',
    pillar_during: props.partner.pillar_during || '',
    pillar_post: props.partner.pillar_post || '',
    pic_name: props.partner.pic_name || '',
    pic_phone: props.partner.pic_phone || '',
    pic_email: props.partner.pic_email || '',
    phone: props.partner.phone || '',
    email: props.partner.email || '',
    address: props.partner.address || '',
    website: props.partner.website || '',
    instagram: props.partner.instagram || '',
    facebook: props.partner.facebook || '',
    logo: null,
    remove_logo: false,
    banner: null,
    remove_banner: false,
});

const logoPreview = ref(props.partner.logo_path || null);
const bannerPreview = ref(props.partner.banner_path || null);

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        form.remove_logo = false;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = null;
};

const handleBannerChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.banner = file;
        form.remove_banner = false;
        bannerPreview.value = URL.createObjectURL(file);
    }
};

const removeBanner = () => {
    form.banner = null;
    form.remove_banner = true;
    bannerPreview.value = null;
};

const copyLandingPageUrl = () => {
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
        navigator.clipboard.writeText(props.landingPageUrl);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2500);
    }
};

const submitForm = () => {
    form.post(route('partner.landing-page.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Pengaturan Landing Page - ${partner.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <span class="text-orange-500">🎨</span>
                <span>Pengaturan Konten Landing Page</span>
            </div>
        </template>

        <div class="py-6 sm:py-8 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- TOP HERO CARD: LIVE PREVIEW & URL BANNER -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-950 to-orange-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-white/10 relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-3 max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-500/20 text-orange-400 border border-orange-500/30">
                                🌐 Landing Page Publik Aktif
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-slate-300">
                                Kode: {{ partner.code }}
                            </span>
                            <span :class="[
                                'px-2.5 py-0.5 rounded-full text-[10px] font-bold',
                                form.recruitment_status === 'Buka' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                            ]">
                                Rekrutmen: {{ form.recruitment_status === 'Buka' ? 'Terbuka' : 'Ditutup' }}
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            {{ partner.name }}
                        </h1>

                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Atur seluruh elemen narasi, media hero, 3 pilar kebencanaan, dan syarat keanggotaan landing page publik secara mandiri.
                        </p>

                        <!-- Public URL Bar -->
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <div class="px-3.5 py-2 rounded-xl bg-black/40 border border-white/15 text-xs font-mono text-orange-300 flex items-center space-x-2 select-all">
                                <span>🔗</span>
                                <span class="truncate max-w-xs sm:max-w-md">{{ landingPageUrl }}</span>
                            </div>
                            <button 
                                type="button"
                                @click="copyLandingPageUrl"
                                class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition active:scale-95"
                            >
                                {{ copied ? '✓ Tersalin!' : 'Salin Tautan' }}
                            </button>
                        </div>
                    </div>

                    <!-- Right Action Button -->
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                        <a 
                            :href="landingPageUrl" 
                            target="_blank" 
                            rel="noopener"
                            class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs sm:text-sm shadow-xl shadow-orange-500/25 active:scale-95 transition text-center flex items-center justify-center space-x-2"
                        >
                            <span>Pratinjau Landing Page</span>
                            <span>↗</span>
                        </a>

                        <div class="grid grid-cols-2 gap-2 text-center text-xs">
                            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold">Relawan Aktif</span>
                                <span class="text-base font-black text-white mt-0.5 block">{{ stats.totalVolunteers }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold">Misi SAR</span>
                                <span class="text-base font-black text-orange-400 mt-0.5 block">{{ stats.sarMissionsCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABS NAVIGATION -->
            <div class="flex border-b border-gray-200 dark:border-gray-800 overflow-x-auto no-scrollbar space-x-2 sm:space-x-4">
                <button
                    type="button"
                    @click="activeTab = 'hero'"
                    :class="[
                        'pb-3 pt-1 px-3 text-xs sm:text-sm font-bold border-b-2 whitespace-nowrap transition flex items-center space-x-2',
                        activeTab === 'hero'
                            ? 'border-orange-500 text-orange-600 dark:text-orange-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                    ]"
                >
                    <span>🖼️</span>
                    <span>1. Hero & Identitas</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'profil'"
                    :class="[
                        'pb-3 pt-1 px-3 text-xs sm:text-sm font-bold border-b-2 whitespace-nowrap transition flex items-center space-x-2',
                        activeTab === 'profil'
                            ? 'border-orange-500 text-orange-600 dark:text-orange-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                    ]"
                >
                    <span>📖</span>
                    <span>2. Profil, Visi & Misi</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'pillars'"
                    :class="[
                        'pb-3 pt-1 px-3 text-xs sm:text-sm font-bold border-b-2 whitespace-nowrap transition flex items-center space-x-2',
                        activeTab === 'pillars'
                            ? 'border-orange-500 text-orange-600 dark:text-orange-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                    ]"
                >
                    <span>🏛️</span>
                    <span>3. Peran di 3 Pilar Bencana</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'recruitment'"
                    :class="[
                        'pb-3 pt-1 px-3 text-xs sm:text-sm font-bold border-b-2 whitespace-nowrap transition flex items-center space-x-2',
                        activeTab === 'recruitment'
                            ? 'border-orange-500 text-orange-600 dark:text-orange-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                    ]"
                >
                    <span>📜</span>
                    <span>4. Syarat Keanggotaan & Form</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'contact'"
                    :class="[
                        'pb-3 pt-1 px-3 text-xs sm:text-sm font-bold border-b-2 whitespace-nowrap transition flex items-center space-x-2',
                        activeTab === 'contact'
                            ? 'border-orange-500 text-orange-600 dark:text-orange-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                    ]"
                >
                    <span>📞</span>
                    <span>5. Kontak & Media Sosial</span>
                </button>
            </div>

            <!-- FORM CONTAINER -->
            <form @submit.prevent="submitForm" class="space-y-6">

                <!-- TAB 1: HERO & IDENTITAS -->
                <div v-show="activeTab === 'hero'" class="bg-white dark:bg-gray-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-800 space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center space-x-2">
                            <span>🖼️</span>
                            <span>Konfigurasi Hero Section & Identitas Utama</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Elemen visual pertama yang dilihat oleh pengunjung saat membuka tautan landing page.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Lembaga -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Nama Resmi Lembaga / Organisasi <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                            <span v-if="form.errors.name" class="text-[11px] text-rose-500">{{ form.errors.name }}</span>
                        </div>

                        <!-- Slug URL -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Slug URL Publik (Path Alamat Web) <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center">
                                <span class="px-3 py-2.5 rounded-l-xl bg-gray-100 dark:bg-gray-800 border border-r-0 border-gray-200 dark:border-gray-700 text-gray-500 text-xs font-mono">
                                    /mitra/
                                </span>
                                <input 
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="sar-unhas"
                                    class="w-full px-4 py-2.5 rounded-r-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm font-mono focus:ring-2 focus:ring-orange-500"
                                />
                            </div>
                            <span v-if="form.errors.slug" class="text-[11px] text-rose-500">{{ form.errors.slug }}</span>
                        </div>

                        <!-- Kategori Mitra -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Kategori Lembaga Mitra <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                v-model="form.category"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            >
                                <option value="Tim Rescue">Tim Rescue / Potensi SAR</option>
                                <option value="Basarnas">Basarnas</option>
                                <option value="BPBD">BPBD</option>
                                <option value="PMI">PMI (Palang Merah Indonesia)</option>
                                <option value="Rumah Sakit">Rumah Sakit / Fasilitas Medis</option>
                                <option value="Filantropi">Lembaga Filantropi</option>
                                <option value="CSR Swasta">CSR Perusahaan Swasta</option>
                            </select>
                        </div>

                        <!-- Status Kesiapsiagaan -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Status Kesiapsiagaan Operasional
                            </label>
                            <input 
                                v-model="form.readiness_status"
                                type="text"
                                placeholder="Contoh: Siaga Operasi 24/7"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <!-- Tagline / Motto Hero -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Tagline / Motto Hero Section
                            </label>
                            <input 
                                v-model="form.tagline"
                                type="text"
                                placeholder="Contoh: Unit Penyelamat Potensi SAR Siaga Tanggap Darurat Bencana di Garda Terdepan"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                            <p class="text-[11px] text-gray-400 mt-1">
                                Ditampilkan sebagai teks pembuka utama yang tegas di bawah nama lembaga pada banner hero.
                            </p>
                        </div>

                        <!-- Dokumen MoU & Kekuatan Personel -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Nomor Dokumen MoU / Kerjasama MKT
                            </label>
                            <input 
                                v-model="form.mou_number"
                                type="text"
                                placeholder="Contoh: MoU/MKT-SAR/2026/001"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Estimasi Kekuatan Personel (Personel Siaga)
                            </label>
                            <input 
                                v-model="form.personnel_count"
                                type="number"
                                min="0"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <!-- Upload Logo Mitra -->
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-200/80 dark:border-gray-800 space-y-3">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">
                                Logo Resmi Lembaga (Header & Hero)
                            </span>
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                                    <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="max-w-full max-h-full object-contain" />
                                    <span v-else class="text-2xl">🏛️</span>
                                </div>
                                <div class="space-y-1.5 flex-1 min-w-0">
                                    <input 
                                        type="file" 
                                        accept="image/*"
                                        @change="handleLogoChange"
                                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-600 dark:file:bg-orange-950/40 dark:file:text-orange-400"
                                    />
                                    <button 
                                        v-if="logoPreview" 
                                        type="button" 
                                        @click="removeLogo"
                                        class="text-[11px] font-bold text-rose-500 hover:underline"
                                    >
                                        Hapus Logo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Foto Background Hero -->
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-200/80 dark:border-gray-800 space-y-3">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">
                                Foto Background Hero Section (Resolusi Tinggi)
                            </span>
                            <div class="flex items-center space-x-4">
                                <div class="w-24 h-16 rounded-2xl bg-slate-900 border border-gray-200 dark:border-gray-700 overflow-hidden shrink-0 relative shadow-xs">
                                    <img v-if="bannerPreview" :src="bannerPreview" alt="Banner" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-xs text-slate-400 font-bold">
                                        Preset Bawaan
                                    </div>
                                </div>
                                <div class="space-y-1.5 flex-1 min-w-0">
                                    <input 
                                        type="file" 
                                        accept="image/*"
                                        @change="handleBannerChange"
                                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-600 dark:file:bg-orange-950/40 dark:file:text-orange-400"
                                    />
                                    <button 
                                        v-if="bannerPreview" 
                                        type="button" 
                                        @click="removeBanner"
                                        class="text-[11px] font-bold text-rose-500 hover:underline"
                                    >
                                        Gunakan Preset Bawaan
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- TAB 2: PROFIL, VISI & MISI -->
                <div v-show="activeTab === 'profil'" class="bg-white dark:bg-gray-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-800 space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center space-x-2">
                            <span>📖</span>
                            <span>Profil Lembaga, Narasi Latar Belakang, Visi & Misi</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Informasi ini ditampilkan pada section <em>#profil</em> agar publik mengenal komitmen lembaga Anda secara mendalam.
                        </p>
                    </div>

                    <div class="space-y-5">
                        <!-- Latar Belakang -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Latar Belakang & Peran Strategis Lembaga
                            </label>
                            <textarea 
                                v-model="form.description"
                                rows="4"
                                placeholder="Jelaskan sejarah singkat, spesialisasi keahlian, dan peran lembaga Anda..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            ></textarea>
                        </div>

                        <!-- Visi & Misi Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    🎯 Visi Lembaga
                                </label>
                                <textarea 
                                    v-model="form.vision"
                                    rows="4"
                                    placeholder="Tuliskan arah jangka panjang dan visi lembaga..."
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    🚀 Misi Utama Lembaga (Gunakan baris baru untuk tiap poin)
                                </label>
                                <textarea 
                                    v-model="form.mission"
                                    rows="4"
                                    placeholder="1. Membina relawan rescuer berkualitas tinggi&#10;2. Melakukan respon cepat saat bencana&#10;3. Berkoordinasi aktif dengan Pusdalops MKT"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: 3 PILAR SIKLUS KEBENCANAAN -->
                <div v-show="activeTab === 'pillars'" class="bg-white dark:bg-gray-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-800 space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center space-x-2">
                            <span>🏛️</span>
                            <span>Peran Lembaga pada 3 Pilar Siklus Kebencanaan</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Deskripsikan kontribusi spesifik organisasi Anda pada fase pra-bencana, tanggap darurat, dan pemulihan pasca-bencana.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Pilar 1: Pra-Bencana -->
                        <div class="p-5 rounded-2xl bg-blue-50/60 dark:bg-blue-950/20 border border-blue-200/80 dark:border-blue-900/40 space-y-3">
                            <div class="flex items-center space-x-2">
                                <span class="text-xl">🗺️</span>
                                <h4 class="text-xs font-black uppercase text-blue-700 dark:text-blue-400">
                                    1. Pra-Bencana (Mitigasi)
                                </h4>
                            </div>
                            <textarea 
                                v-model="form.pillar_pre"
                                rows="5"
                                placeholder="Contoh: Penyegaran teknik water rescue, jungle rescue, pemetaan rute evakuasi, dan pemeliharaan alat pelampung serta perahu karet di markas posko."
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-xs leading-relaxed focus:ring-2 focus:ring-blue-500"
                            ></textarea>
                        </div>

                        <!-- Pilar 2: Saat Bencana -->
                        <div class="p-5 rounded-2xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200/80 dark:border-rose-900/40 space-y-3">
                            <div class="flex items-center space-x-2">
                                <span class="text-xl">🚨</span>
                                <h4 class="text-xs font-black uppercase text-rose-700 dark:text-rose-400">
                                    2. Saat Bencana (Darurat)
                                </h4>
                            </div>
                            <textarea 
                                v-model="form.pillar_during"
                                rows="5"
                                placeholder="Contoh: Mobilisasi tim rescue dan rescuer ke lokasi terdampak, pencarian korban musibah, dan pendirian posko darurat bersama tim gabungan SAR."
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-xs leading-relaxed focus:ring-2 focus:ring-rose-500"
                            ></textarea>
                        </div>

                        <!-- Pilar 3: Pasca-Bencana -->
                        <div class="p-5 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-900/40 space-y-3">
                            <div class="flex items-center space-x-2">
                                <span class="text-xl">🌱</span>
                                <h4 class="text-xs font-black uppercase text-emerald-700 dark:text-emerald-400">
                                    3. Pasca-Bencana (Pemulihan)
                                </h4>
                            </div>
                            <textarea 
                                v-model="form.pillar_post"
                                rows="5"
                                placeholder="Contoh: Distribusi bantuan penyintas, pemulihan sarana air bersih dan fasilitas ibadah, serta pelaporan evaluasi operasi kepada komando MKT."
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-xs leading-relaxed focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: REKRUTMEN & SYARAT KEANGGOTAAN -->
                <div v-show="activeTab === 'recruitment'" class="bg-white dark:bg-gray-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-800 space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center space-x-2">
                            <span>📜</span>
                            <span>Pengaturan Formulir Pendaftaran & Syarat Keanggotaan</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Kelola status penerimaan pendaftar serta klausul Syarat & Ketentuan khusus bagi calon Anggota lembaga Anda.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <!-- Status Pendaftaran Switch -->
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-200/80 dark:border-gray-800">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                    Status Pembukaan Pendaftaran Anggota & Relawan
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Jika ditutup, formulir pendaftaran di landing page akan menampilkan pengumuman bahwa rekrutmen ditutup sementara.
                                </p>
                            </div>
                            <select 
                                v-model="form.recruitment_status"
                                class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                            >
                                <option value="Buka">🟢 Buka Pendaftaran</option>
                                <option value="Tutup">🔴 Ditutup Sementara</option>
                            </select>
                        </div>

                        <!-- Syarat & Ketentuan Khusus Keanggotaan -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Syarat & Ketentuan Khusus Calon Anggota (Tulis 1 poin per baris)
                            </label>
                            <textarea 
                                v-model="form.membership_terms"
                                rows="6"
                                placeholder="Contoh:&#10;1. Bersedia mengikuti tahapan Pendidikan Dasar (Diksar) internal SAR Unhas.&#10;2. Mematuhi Anggaran Dasar & Anggaran Rumah Tangga (AD/ART) lembaga.&#10;3. Siap bertugas piket posko dan mobilisasi operasi SAR kebencanaan."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm font-mono leading-relaxed focus:ring-2 focus:ring-orange-500"
                            ></textarea>
                            <p class="text-[11px] text-gray-400 mt-1">
                                * Poin-poin ini akan otomatis ditampilkan pada kotak klausul Syarat & Ketentuan resmi saat pengunjung memilih bergabung sebagai Anggota.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: KONTAK & MEDIA SOSIAL -->
                <div v-show="activeTab === 'contact'" class="bg-white dark:bg-gray-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-800 space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center space-x-2">
                            <span>📞</span>
                            <span>Kontak Sekretariat, Koordinator PIC & Tautan Media Sosial</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Data kontak ini terhubung langsung dengan tombol WhatsApp narahubung dan informasi posko di section <em>#kontak</em>.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Alamat Lengkap -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Alamat Lengkap Sekretariat / Markas Posko
                            </label>
                            <textarea 
                                v-model="form.address"
                                rows="2"
                                placeholder="Gedung PKM, Kampus Tamalanrea, Jl. Perintis Kemerdekaan KM 10, Makassar"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            ></textarea>
                        </div>

                        <!-- Koordinator PIC -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Nama Koordinator / Narahubung PIC <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                v-model="form.pic_name"
                                type="text"
                                required
                                placeholder="Nama lengkap koordinator"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <!-- No. WhatsApp PIC -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                No. WhatsApp / HP Narahubung PIC <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                v-model="form.pic_phone"
                                type="tel"
                                required
                                placeholder="08123456789"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <!-- Email Resmi -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Email Resmi Lembaga <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="sekretariat@lembaga.org"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <!-- Telepon Kantor -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                No. Telepon Kantor / Hotline
                            </label>
                            <input 
                                v-model="form.phone"
                                type="tel"
                                placeholder="(0411) 586200"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <!-- Website Resmi -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Website Resmi (URL)
                            </label>
                            <input 
                                v-model="form.website"
                                type="text"
                                placeholder="https://sarunhas.org"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <!-- Instagram -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Akun Instagram (Username / URL)
                            </label>
                            <input 
                                v-model="form.instagram"
                                type="text"
                                placeholder="@sar_unhas atau https://instagram.com/sar_unhas"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>

                        <!-- Facebook -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Akun Facebook (Halaman / URL)
                            </label>
                            <input 
                                v-model="form.facebook"
                                type="text"
                                placeholder="https://facebook.com/sarunhas.official"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-orange-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- SAVE BUTTON BAR -->
                <div class="flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-md">
                    <div class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">
                        Perubahan akan langsung terupdate di halaman landing page publik.
                    </div>
                    <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                        <Link
                            :href="route('partner.profile')"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs transition"
                        >
                            Ke Profil Lembaga
                        </Link>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs shadow-md transition disabled:opacity-50 flex items-center space-x-2"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>💾 Simpan Perubahan Konten</span>
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </AuthenticatedLayout>
</template>
