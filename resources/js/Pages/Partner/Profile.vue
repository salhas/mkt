<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    partner: Object,
});

const form = useForm({
    name: props.partner?.name || '',
    slug: props.partner?.slug || '',
    category: props.partner?.category || 'Tim Rescue',
    pic_name: props.partner?.pic_name || '',
    pic_phone: props.partner?.pic_phone || '',
    pic_email: props.partner?.pic_email || '',
    phone: props.partner?.phone || '',
    email: props.partner?.email || '',
    website: props.partner?.website || '',
    instagram: props.partner?.instagram || '',
    facebook: props.partner?.facebook || '',
    address: props.partner?.address || '',
    mou_number: props.partner?.mou_number || '',
    personnel_count: props.partner?.personnel_count || 0,
    description: props.partner?.description || '',
    vision: props.partner?.vision || '',
    mission: props.partner?.mission || '',
    status: props.partner?.status || 'Aktif',
    logo: null,
    remove_logo: false,
    banner: null,
    remove_banner: false,
});

const logoPreview = ref(props.partner?.logo_path || null);
const fileInput = ref(null);
const fileError = ref(null);

watch(() => props.partner?.logo_path, (newVal) => {
    logoPreview.value = newVal;
    form.logo = null;
    form.remove_logo = false;
});

const triggerFileInput = () => {
    fileInput.value?.click();
};

const onFileChange = (e) => {
    fileError.value = null;
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Ukuran berkas logo maksimal 2MB.';
        e.target.value = '';
        return;
    }

    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/svg+xml'];
    if (!validTypes.includes(file.type)) {
        fileError.value = 'Format berkas harus JPG, PNG, WebP, atau SVG.';
        e.target.value = '';
        return;
    }

    form.logo = file;
    form.remove_logo = false;
    logoPreview.value = URL.createObjectURL(file);
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = null;
    fileError.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('partner.profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            fileError.value = null;
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};
</script>

<template>
    <Head title="Profil Lembaga Mitra" />

    <AuthenticatedLayout>
        <template #header>
            <span>Profil Lembaga</span>
        </template>

        <div class="space-y-6 max-w-6xl mx-auto">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl shadow-md shrink-0 border border-white/20 overflow-hidden">
                            <img v-if="logoPreview" :src="logoPreview" :alt="partner?.name" class="w-full h-full object-contain p-1.5 bg-white rounded-2xl" />
                            <span v-else>🏢</span>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/25 backdrop-blur-md text-white border border-white/20">
                                    {{ partner?.code || 'MITRA-MKT' }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-400/25 text-emerald-200 border border-emerald-400/30">
                                    {{ partner?.status || 'Aktif' }}
                                </span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-black tracking-tight mt-1">{{ partner?.name }}</h1>
                            <p class="text-xs sm:text-sm text-blue-100 mt-0.5">
                                Kemitraan Resmi Kategori: <strong class="text-white">{{ partner?.category }}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 shrink-0">
                        <div class="bg-white/10 backdrop-blur-md border border-white/15 px-4 py-2.5 rounded-2xl text-right">
                            <span class="block text-[10px] font-semibold text-blue-200 uppercase">Kekuatan Personel</span>
                            <span class="text-lg font-black text-white">{{ partner?.personnel_count || 0 }} Personel Siaga</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- FORM CARD (Left 8 cols) -->
                <div class="lg:col-span-8 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white mb-1 flex items-center space-x-2">
                        <span>📝 Formulir Kelola Profil Lembaga</span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                        Pastikan data lembaga dan narahubung PIC akurat untuk koordinasi siaga darurat kebencanaan bersama Yayasan MKT.
                    </p>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Identitas Lembaga & Logo -->
                        <div class="space-y-4 pb-5 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">1. Identitas & Logo Lembaga</h3>
                            
                            <!-- Komponen Unggah Logo Lembaga -->
                            <div class="p-4 sm:p-5 rounded-2xl bg-blue-50/60 dark:bg-slate-800/40 border border-blue-100 dark:border-blue-900/40 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-wider text-blue-900 dark:text-blue-300">
                                            Logo / Lambang Lembaga Mitra
                                        </label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            Logo resmi yang ditampilkan di direktori publik, kartu potensi SAR, dan profil lembaga.
                                        </p>
                                    </div>
                                    <div>
                                        <span v-if="logoPreview && form.logo" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50">
                                            🟡 Pratinjau Baru (Belum Disimpan)
                                        </span>
                                        <span v-else-if="logoPreview" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700/50">
                                            🟢 Logo Terpasang
                                        </span>
                                        <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                            ⚪ Belum Ada Logo
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row items-center gap-4 pt-1">
                                    <!-- Preview Box -->
                                    <div class="relative group w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 flex items-center justify-center p-2 overflow-hidden shadow-inner shrink-0">
                                        <img 
                                            v-if="logoPreview" 
                                            :src="logoPreview" 
                                            :alt="form.name || 'Logo Mitra'" 
                                            class="w-full h-full object-contain"
                                        />
                                        <div v-else class="text-center text-gray-400 flex flex-col items-center justify-center space-y-1">
                                            <svg class="w-8 h-8 stroke-current" fill="none" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-[10px] font-medium">Tanpa Logo</span>
                                        </div>
                                    </div>

                                    <!-- Action Buttons and Instruction -->
                                    <div class="flex-1 space-y-2 text-center sm:text-left">
                                        <input 
                                            ref="fileInput" 
                                            type="file" 
                                            accept="image/jpeg,image/png,image/jpg,image/webp,image/svg+xml" 
                                            class="hidden" 
                                            @change="onFileChange"
                                        />
                                        
                                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                            <button 
                                                type="button" 
                                                @click="triggerFileInput"
                                                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-all active:scale-95 flex items-center space-x-1.5"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                </svg>
                                                <span>{{ logoPreview ? 'Ganti Logo Lembaga' : 'Pilih Logo Lembaga' }}</span>
                                            </button>

                                            <button 
                                                v-if="logoPreview" 
                                                type="button" 
                                                @click="removeLogo"
                                                class="px-3.5 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 text-xs font-bold transition-all active:scale-95 flex items-center space-x-1.5"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                <span>Hapus Logo</span>
                                            </button>
                                        </div>

                                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                            Format berkas: <strong>PNG, JPG, WebP, SVG</strong> (Maksimal <strong>2 MB</strong>). Disarankan rasio kotak (1:1).
                                        </p>

                                        <span v-if="fileError" class="text-rose-500 text-xs font-semibold block">{{ fileError }}</span>
                                        <span v-if="form.errors.logo" class="text-rose-500 text-xs font-semibold block">{{ form.errors.logo }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Lengkap Lembaga / Organisasi <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        v-model="form.name" 
                                        type="text" 
                                        required 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                    <span v-if="form.errors.name" class="text-rose-500 text-xs mt-1 block">{{ form.errors.name }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Kategori Kemitraan <span class="text-rose-500">*</span>
                                    </label>
                                    <select 
                                        v-model="form.category" 
                                        required 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    >
                                        <option value="Tim Rescue">⚓ Tim Rescue & SAR Swadaya</option>
                                        <option value="Basarnas">🚢 Basarnas</option>
                                        <option value="BPBD">🏛️ BPBD RI</option>
                                        <option value="PMI">🩸 PMI (Palang Merah Indonesia)</option>
                                        <option value="Rumah Sakit">🏥 Rumah Sakit / Medis</option>
                                        <option value="Filantropi">🤝 Lembaga Filantropi / Sosial</option>
                                        <option value="CSR Swasta">💼 CSR Perusahaan / Korporasi</option>
                                        <option value="Komunitas Kemanusiaan">🌐 Komunitas Kemanusiaan</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Nomor Dokumen MoU (Kerjasama)
                                    </label>
                                    <input 
                                        v-model="form.mou_number" 
                                        type="text" 
                                        placeholder="Contoh: MoU/PMI-MKT/2026/001" 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Narahubung & Kontak -->
                        <div class="space-y-3 pb-5 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">2. Kontak & Person in Charge (PIC)</h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Lengkap PIC / Penanggung Jawab <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        v-model="form.pic_name" 
                                        type="text" 
                                        required 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        No. WhatsApp / HP PIC <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        v-model="form.pic_phone" 
                                        type="text" 
                                        required 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Email Resmi Lembaga <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        v-model="form.email" 
                                        type="email" 
                                        required 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Email Personal PIC (Opsional)
                                    </label>
                                    <input 
                                        v-model="form.pic_email" 
                                        type="email" 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Telepon Kantor / Sekretariat
                                    </label>
                                    <input 
                                        v-model="form.phone" 
                                        type="text" 
                                        placeholder="0411-xxxxxx" 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Estimasi Personel Siaga / Armada
                                    </label>
                                    <input 
                                        v-model.number="form.personnel_count" 
                                        type="number" 
                                        min="0" 
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Domisili & Lingkup Kerjasama -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">3. Domisili, Media & Landing Page Publik</h3>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Slug URL Landing Page Publik (cth: sar-unhas)
                                </label>
                                <div class="flex rounded-2xl shadow-xs">
                                    <span class="inline-flex items-center px-4 rounded-l-2xl border border-r-0 border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-800 text-gray-500 text-xs font-mono">
                                        /mitra/
                                    </span>
                                    <input 
                                        v-model="form.slug"
                                        type="text" 
                                        placeholder="nama-singkat-mitra"
                                        class="w-full rounded-r-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5 font-mono"
                                    />
                                </div>
                                <span v-if="form.errors.slug" class="text-xs text-rose-500 mt-1 block">{{ form.errors.slug }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Website Resmi (Opsional)
                                    </label>
                                    <input 
                                        v-model="form.website" 
                                        type="url" 
                                        placeholder="https://lembaga.org"
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Instagram (Opsional)
                                    </label>
                                    <input 
                                        v-model="form.instagram" 
                                        type="text" 
                                        placeholder="https://instagram.com/username"
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Alamat Lengkap Markas / Kantor Lembaga
                                </label>
                                <textarea 
                                    v-model="form.address" 
                                    rows="2" 
                                    class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm p-3"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Deskripsi Profil & Fokus Kerjasama Kemanusiaan
                                </label>
                                <textarea 
                                    v-model="form.description" 
                                    rows="3" 
                                    placeholder="Jelaskan spesialisasi lembaga (misal: Unit Siaga Donor Darah, Tim Evakuasi Air, Fasilitas Medis, dsb)..." 
                                    class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm p-3"
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Visi Lembaga
                                    </label>
                                    <textarea 
                                        v-model="form.vision" 
                                        rows="2" 
                                        placeholder="Visi kemanusiaan lembaga..."
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm p-3"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Misi Lembaga
                                    </label>
                                    <textarea 
                                        v-model="form.mission" 
                                        rows="2" 
                                        placeholder="Misi dan fokus kerja lapangan..."
                                        class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm p-3"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="pt-4 flex items-center justify-end space-x-3">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm shadow-lg shadow-blue-500/25 active:scale-95 transition-all flex items-center space-x-2 disabled:opacity-70"
                            >
                                <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>{{ form.processing ? 'Menyimpan Perubahan...' : '💾 Simpan Perubahan Profil Lembaga' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- RIGHT SIDE INFO CARDS (Right 4 cols) -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Kartu Landing Page Publik -->
                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-slate-900 dark:to-orange-950/40 border border-orange-200 dark:border-orange-900/50 rounded-3xl p-6 shadow-sm space-y-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">🌐</span>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Landing Page Publik Anda</h4>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                            Lembaga Anda memiliki halaman landing page resmi yang dapat diakses publik untuk sosialisasi profil dan pendaftaran relawan.
                        </p>
                        <div class="p-2.5 rounded-xl bg-white/90 dark:bg-slate-950/90 border border-orange-200 dark:border-orange-800 text-[11px] font-mono break-all text-orange-700 dark:text-orange-400">
                            /mitra/{{ partner?.slug || partner?.id }}
                        </div>
                        <div class="pt-1 flex flex-wrap gap-2">
                            <a 
                                :href="route('public.partner.show', partner?.slug || partner?.id)" 
                                target="_blank"
                                class="inline-flex items-center px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold shadow-md transition"
                            >
                                <span>Buka Halaman Publik ↗</span>
                            </a>
                        </div>
                    </div>

                    <!-- Kartu Ringkasan Kemitraan -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg font-bold">
                                🛡️
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Status Legalitas Kemitraan</h3>
                                <span class="text-xs text-gray-400">Verifikasi Yayasan MKT</span>
                            </div>
                        </div>

                        <div class="space-y-2.5 text-xs pt-2 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Kode Registrasi:</span>
                                <span class="font-mono font-bold text-gray-800 dark:text-gray-200">{{ partner?.code }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Status Operasional:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ partner?.status }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Nomor Dokumen MoU:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ partner?.mou_number || 'Dalam Proses' }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">PIC Aktif:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ partner?.pic_name }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">No. WhatsApp:</span>
                                <span class="font-bold text-blue-600">{{ partner?.pic_phone }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Shortcut ke Pengurus & Anggota -->
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-slate-900 dark:to-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-3xl p-6 shadow-sm space-y-3">
                        <span class="text-2xl">👥</span>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Kelola Personel & Pengurus</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                            Daftarkan jajaran pengurus, tim rescuer, dan pendonor darah yang bernaung di bawah <strong>{{ partner?.name }}</strong>.
                        </p>
                        <Link 
                            :href="route('partner.members')" 
                            class="inline-flex items-center space-x-2 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline pt-1"
                        >
                            <span>Buka Menu Pengurus & Anggota</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
