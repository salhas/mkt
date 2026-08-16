<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { showSuccessToast, showErrorToast } from '@/Utils/toast.js';

const props = defineProps({
    profile: Object,
});

// Default profile values if not found
const profileData = props.profile || {
    name: 'Yayasan MKT Indonesia',
    description: 'Mitra Kemanusiaan Terpadu Indonesia — Lembaga filantropi dan tanggap bencana terintegrasi.',
    address: 'Jl. Kemanusiaan No. 88, Jakarta Selatan, DKI Jakarta',
    phone: '+62 812-3456-7890',
    email: 'info@mkt.or.id',
    vision: 'Menjadi lembaga kemanusiaan terdepan, akuntabel, dan tanggap dalam penanggulangan bencana di Indonesia.',
    mission: '1. Menyelenggarakan aksi evakuasi darurat dan bantuan bencana secara cepat.\n2. Mengelola penghimpunan donasi publik dengan prinsip transparansi tinggi.\n3. Membina jaringan relawan rescue dan donor darah terpadu.',
    logo: '',
    bank_accounts: [
        { bank: 'BSI (Bank Syariah Indonesia)', account_number: '7112233445', account_name: 'Yayasan MKT Indonesia' },
        { bank: 'Bank Mandiri', account_number: '1270009988776', account_name: 'Yayasan MKT Indonesia' }
    ]
};

const form = useForm({
    name: profileData.name || '',
    description: profileData.description || '',
    address: profileData.address || '',
    phone: profileData.phone || '',
    email: profileData.email || '',
    vision: profileData.vision || '',
    mission: profileData.mission || '',
    logo: profileData.logo || '',
    bank_accounts: [...(profileData.bank_accounts || [])]
});

// Tab state: 'identity' | 'contact' | 'vision' | 'banks'
const activeTab = ref('identity');

// Logo upload handling
const logoFileInput = ref(null);
const logoPreview = ref(form.logo);

const triggerLogoSelect = () => {
    logoFileInput.value.click();
};

const handleLogoUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran berkas logo tidak boleh melebihi 5MB.');
            return;
        }
        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const maxDim = 512;
                let width = img.width;
                let height = img.height;
                if (width > height) {
                    if (width > maxDim) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    }
                } else {
                    if (height > maxDim) {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                const compressedDataUrl = canvas.toDataURL('image/png');
                logoPreview.value = compressedDataUrl;
                form.logo = compressedDataUrl;
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    logoPreview.value = '';
    form.logo = '';
    if (logoFileInput.value) {
        logoFileInput.value.value = '';
    }
};

// Bank editing helpers
const newBank = ref('');
const newAccountNumber = ref('');
const newAccountName = ref('');

const addBankAccount = () => {
    if (newBank.value && newAccountNumber.value && newAccountName.value) {
        form.bank_accounts.push({
            bank: newBank.value,
            account_number: newAccountNumber.value,
            account_name: newAccountName.value
        });
        newBank.value = '';
        newAccountNumber.value = '';
        newAccountName.value = '';
    }
};

const removeBankAccount = (index) => {
    form.bank_accounts.splice(index, 1);
};

const submit = () => {
    form.post(route('mkt-profile.update'), {
        onSuccess: () => {
            showSuccessToast('Profil Lembaga MKT berhasil diperbarui!');
        },
        onError: (errs) => {
            const errorMsg = Object.values(errs).flat().join(', ') || 'Gagal memperbarui profil lembaga.';
            showErrorToast(errorMsg);
        }
    });
};
</script>

<template>
    <Head title="Profil MKT" />

    <AuthenticatedLayout>
        <template #header>
            <span>Profil MKT</span>
        </template>

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Dedicated Page Header Section -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Profil Lembaga & Legalitas MKT</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola identitas, logo, kontak, visi-misi, serta rekening resmi yayasan</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                        Profil Resmi Yayasan
                    </span>
                </div>
            </div>

            <!-- Organization Card Header Hero Banner -->
            <div class="bg-gradient-to-r from-brand-600 via-amber-600 to-amber-500 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
                    <!-- Logo Avatar Component -->
                    <div class="relative group">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white dark:bg-gray-900 p-2 shadow-2xl border-2 border-white/40 flex items-center justify-center overflow-hidden shrink-0">
                            <img
                                v-if="logoPreview || form.logo"
                                :src="logoPreview || form.logo"
                                alt="Logo MKT"
                                class="w-full h-full object-contain rounded-xl"
                            />
                            <div v-else class="w-full h-full rounded-xl bg-gradient-to-tr from-brand-500 to-amber-400 flex flex-col items-center justify-center text-white">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span class="text-[10px] font-black uppercase mt-1 tracking-wider">MKT</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="triggerLogoSelect"
                            class="absolute inset-0 bg-black/60 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white text-[10px] font-bold space-y-1"
                            title="Ganti Logo Lembaga"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Ganti Logo</span>
                        </button>
                    </div>

                    <!-- Hidden File Input -->
                    <input
                        ref="logoFileInput"
                        type="file"
                        accept="image/png, image/jpeg, image/webp, image/svg+xml"
                        class="hidden"
                        @change="handleLogoUpload"
                    />

                    <!-- Header Text info -->
                    <div class="flex-1 space-y-2">
                        <div class="inline-flex items-center space-x-2 px-3 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-md">
                            <span>🏛️ Lembaga Kebencanaan & Filantropi</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ form.name || 'Yayasan MKT Indonesia' }}</h2>
                        <p class="text-xs sm:text-sm text-white/80 max-w-2xl line-clamp-2">{{ form.description || 'Mitra Kemanusiaan Terpadu Indonesia' }}</p>
                        
                        <div class="pt-2 flex flex-wrap justify-center sm:justify-start gap-4 text-xs text-white/90">
                            <span v-if="form.phone" class="flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h32a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"></path></svg>
                                <span>{{ form.phone }}</span>
                            </span>
                            <span v-if="form.email" class="flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span>{{ form.email }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Tab Segment Control -->
            <div class="bg-gray-100/80 dark:bg-gray-800/60 p-1.5 rounded-2xl flex items-center space-x-1 border border-gray-200/60 dark:border-gray-700/60 overflow-x-auto scrollbar-thin">
                <!-- Tab 1: Identitas & Logo -->
                <button
                    @click="activeTab = 'identity'"
                    type="button"
                    :class="[
                        activeTab === 'identity'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 whitespace-nowrap flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span>Identitas & Logo Lembaga</span>
                </button>

                <!-- Tab 2: Kontak & Alamat -->
                <button
                    @click="activeTab = 'contact'"
                    type="button"
                    :class="[
                        activeTab === 'contact'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 whitespace-nowrap flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Kontak & Lokasi Pusat</span>
                </button>

                <!-- Tab 3: Visi & Misi -->
                <button
                    @click="activeTab = 'vision'"
                    type="button"
                    :class="[
                        activeTab === 'vision'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 whitespace-nowrap flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Visi & Misi Kemanusiaan</span>
                </button>

                <!-- Tab 4: Rekening Donasi -->
                <button
                    @click="activeTab = 'banks'"
                    type="button"
                    :class="[
                        activeTab === 'banks'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 whitespace-nowrap flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <span>Rekening Donasi</span>
                </button>
            </div>

            <!-- Tab Content Forms -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- TAB 1: IDENTITAS & LOGO -->
                <div v-show="activeTab === 'identity'" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <span class="text-lg">🏛️</span>
                            <span>Identitas Utama & Berkas Logo</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">Atur nama resmi yayasan, deskripsi profil, serta gambar logo organisasi.</p>
                    </div>

                    <!-- Logo Image Selector Field -->
                    <div class="p-5 bg-gray-50/80 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Logo Lembaga (Image File / URL)</label>
                        
                        <div class="flex flex-col sm:flex-row items-center gap-6">
                            <!-- Image Preview Card -->
                            <div class="w-24 h-24 rounded-2xl bg-white dark:bg-gray-900 p-2 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-center shrink-0">
                                <img
                                    v-if="logoPreview || form.logo"
                                    :src="logoPreview || form.logo"
                                    alt="Preview Logo"
                                    class="w-full h-full object-contain rounded-xl"
                                />
                                <div v-else class="text-center text-gray-400">
                                    <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-[9px] block mt-1">Belum ada logo</span>
                                </div>
                            </div>

                            <!-- Logo Actions & Input Fields -->
                            <div class="flex-1 space-y-3 w-full">
                                <div class="flex items-center space-x-3">
                                    <button
                                        type="button"
                                        @click="triggerLogoSelect"
                                        class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center space-x-1.5"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        <span>Unggah Gambar Logo Baru</span>
                                    </button>
                                    <button
                                        v-if="logoPreview || form.logo"
                                        type="button"
                                        @click="removeLogo"
                                        class="px-3 py-2 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-xs font-semibold rounded-xl transition-colors"
                                    >
                                        Hapus Logo
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Atau Gunakan Tautan URL Logo</label>
                                    <input
                                        v-model="form.logo"
                                        type="text"
                                        placeholder="https://domain.com/images/logo.png atau /images/logo.png"
                                        class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2 text-xs focus:border-brand-500 focus:outline-none"
                                        @input="logoPreview = form.logo"
                                    />
                                </div>
                                <p class="text-[11px] text-gray-400">Format yang didukung: PNG, JPG, WEBP, atau SVG (Maksimal 3MB).</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nama Resmi Yayasan / Organisasi</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:outline-none"
                                required
                            />
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Deskripsi Profil Singkat</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Jelaskan gambaran umum pergerakan dan bidang fokus yayasan..."
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:outline-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: KONTAK & LOKASI PUSAT -->
                <div v-show="activeTab === 'contact'" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <span class="text-lg">📞</span>
                            <span>Kontak Hotline & Alamat Kantor Pusat</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">Alamat sekretariat utama, saluran informasi darurat, dan alamat surel resmi.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">No. Telepon / Hotline 24 Jam</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                placeholder="Contoh: +62 812-3456-7890"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:outline-none"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Email Resmi Lembaga</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="info@mkt.or.id"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:outline-none"
                            />
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Alamat Lengkap Kantor Pusat / Sekretariat</label>
                            <textarea
                                v-model="form.address"
                                rows="3"
                                placeholder="Jalan, Gedung, Kecamatan, Kota/Kabupaten, Kode Pos..."
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <!-- Google Maps Preview Box -->
                        <div class="md:col-span-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-400 uppercase">Pratinjau Peta Google Maps Kantor Utama</label>
                                <span class="text-[11px] text-brand-600 dark:text-brand-400 font-semibold flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>Perumahan Insignia Oasis Blok B1-11 No 7</span>
                                </span>
                            </div>
                            <div class="w-full h-56 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-inner bg-gray-100 dark:bg-gray-800">
                                <iframe
                                    class="w-full h-full border-0"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    :src="`https://maps.google.com/maps?q=${encodeURIComponent(form.address || 'Perumahan Insignia Oasis Blok B1-11 No 7')}&t=&z=16&ie=UTF8&iwloc=&output=embed`"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: VISI & MISI -->
                <div v-show="activeTab === 'vision'" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <span class="text-lg">🎯</span>
                            <span>Visi & Misi Kemanusiaan</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">Prinsip filosofis dan misi operasional giat kemanusiaan yayasan.</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Vision -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Visi Utama Organisasi</label>
                            <textarea
                                v-model="form.vision"
                                rows="3"
                                placeholder="Tuliskan visi besar lembaga kemanusiaan..."
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <!-- Mission -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Misi Operasional (Daftar poin bernomor)</label>
                            <textarea
                                v-model="form.mission"
                                rows="6"
                                placeholder="1. Menyelenggarakan aksi evakuasi darurat...&#10;2. Mengelola donasi publik secara akuntabel..."
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:outline-none font-sans"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: REKENING BANK DONASI -->
                <div v-show="activeTab === 'banks'" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <span class="text-lg">🏦</span>
                            <span>Rekening Bank & Kanal Donasi Resmi</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">Daftar rekening bank penampung dana donasi filantropi & tanggap bencana.</p>
                    </div>

                    <!-- Bank Accounts List -->
                    <div class="space-y-3">
                        <div
                            v-for="(acc, index) in form.bank_accounts"
                            :key="index"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 hover:border-brand-300 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-950/30 text-brand-600 dark:text-brand-400 flex items-center justify-center font-black text-xs shrink-0">
                                    💳
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-brand-600 dark:text-brand-400 block">{{ acc.bank }}</span>
                                    <span class="font-mono text-sm font-black text-gray-900 dark:text-white tracking-wider">{{ acc.account_number }}</span>
                                    <span class="text-xs text-gray-400 block">a.n. {{ acc.account_name }}</span>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="removeBankAccount(index)"
                                class="text-rose-500 hover:text-rose-700 p-2 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all focus:outline-none"
                                title="Hapus Rekening Ini"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>

                        <p v-if="form.bank_accounts.length === 0" class="text-sm text-gray-400 italic text-center py-6">
                            Belum ada rekening bank yang terdaftar. Silakan tambahkan di bawah ini.
                        </p>
                    </div>

                    <!-- Add Bank Form -->
                    <div class="p-5 bg-gray-50/80 dark:bg-gray-800/40 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl space-y-4">
                        <span class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">+ Tambah Rekening Bank Donasi Baru</span>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Bank / Platform</label>
                                <input
                                    v-model="newBank"
                                    type="text"
                                    placeholder="Misal: BSI, Bank BCA, Mandiri, QRIS"
                                    class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nomor Rekening</label>
                                <input
                                    v-model="newAccountNumber"
                                    type="text"
                                    placeholder="Misal: 7112233445"
                                    class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Atas Nama Pemilik</label>
                                <input
                                    v-model="newAccountName"
                                    type="text"
                                    placeholder="Misal: Yayasan MKT Indonesia"
                                    class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"
                                />
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="button"
                                @click="addBankAccount"
                                class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center space-x-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>+ Sisipkan Rekening Baru</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="pt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-800">
                    <p class="text-xs text-gray-400 hidden sm:block">Perubahan akan langsung diperbarui ke sistem public & dashboard.</p>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center space-x-2 ml-auto"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Simpan Perubahan Profil</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
