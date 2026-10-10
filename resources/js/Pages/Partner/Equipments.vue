<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { showSuccessToast, showErrorToast } from '@/Utils/toast.js';

const props = defineProps({
    partner: Object,
    equipments: Object,
    stats: Object,
    categories: Array,
    conditions: Array,
    statuses: Array,
    units: Array,
    ownershipStatuses: Array,
    filters: Object,
});

// View mode: 'grid' | 'table'
const viewMode = ref('grid');

// Search & Filters
const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || 'Semua');
const selectedCondition = ref(props.filters?.condition || 'Semua');
const selectedStatus = ref(props.filters?.status || 'Semua');

const applyFilter = () => {
    router.get(route('partner.equipments'), {
        search: search.value || undefined,
        category: selectedCategory.value !== 'Semua' ? selectedCategory.value : undefined,
        condition: selectedCondition.value !== 'Semua' ? selectedCondition.value : undefined,
        status: selectedStatus.value !== 'Semua' ? selectedStatus.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    selectedCategory.value = 'Semua';
    selectedCondition.value = 'Semua';
    selectedStatus.value = 'Semua';
    applyFilter();
};

// --- MODAL STATE: ADD / EDIT ---
const showModal = ref(false);
const isEditing = ref(false);
const editingEquipmentId = ref(null);
const photoPreview = ref(null);

const form = useForm({
    id: null,
    name: '',
    item_code: '',
    category: 'Water Rescue',
    quantity: 1,
    unit: 'Unit',
    condition: 'Siap Pakai',
    storage_location: '',
    ownership_status: 'Milik Sendiri',
    status: 'Tersedia',
    notes: '',
    photo: null,
    remove_photo: false,
});

const openAddModal = () => {
    isEditing.value = false;
    editingEquipmentId.value = null;
    photoPreview.value = null;
    form.reset();
    form.clearErrors();
    form.category = props.categories?.[0] || 'Water Rescue';
    form.quantity = 1;
    form.unit = 'Unit';
    form.condition = 'Siap Pakai';
    form.ownership_status = 'Milik Sendiri';
    form.status = 'Tersedia';
    form.remove_photo = false;
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingEquipmentId.value = item.id;
    form.clearErrors();
    form.id = item.id;
    form.name = item.name || '';
    form.item_code = item.item_code || '';
    form.category = item.category || 'Water Rescue';
    form.quantity = item.quantity || 1;
    form.unit = item.unit || 'Unit';
    form.condition = item.condition || 'Siap Pakai';
    form.storage_location = item.storage_location || '';
    form.ownership_status = item.ownership_status || 'Milik Sendiri';
    form.status = item.status || 'Tersedia';
    form.notes = item.notes || '';
    form.photo = null;
    form.remove_photo = false;
    photoPreview.value = item.photo_path || null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    photoPreview.value = null;
    form.reset();
};

const handlePhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        form.remove_photo = false;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const removeSelectedPhoto = () => {
    form.photo = null;
    form.remove_photo = true;
    photoPreview.value = null;
};

const submitForm = () => {
    if (isEditing.value) {
        form.post(route('partner.equipments.update', editingEquipmentId.value), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                showSuccessToast('Data peralatan berhasil diperbarui!');
            },
            onError: (errs) => {
                const firstErr = errs ? Object.values(errs)[0] : null;
                showErrorToast(firstErr || 'Gagal memperbarui data peralatan.');
            }
        });
    } else {
        form.post(route('partner.equipments.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                showSuccessToast('Peralatan baru berhasil ditambahkan!');
            },
            onError: (errs) => {
                const firstErr = errs ? Object.values(errs)[0] : null;
                showErrorToast(firstErr || 'Gagal menambahkan peralatan.');
            }
        });
    }
};

const deleteEquipment = (item) => {
    if (confirm(`Apakah Anda yakin ingin menghapus "${item.name}" dari inventaris lembaga?`)) {
        router.delete(route('partner.equipments.destroy', item.id), {
            preserveScroll: true,
            onSuccess: () => showSuccessToast('Peralatan berhasil dihapus dari inventaris.'),
            onError: () => showErrorToast('Gagal menghapus peralatan.')
        });
    }
};

// --- DETAIL MODAL STATE ---
const showDetailModal = ref(false);
const selectedDetailItem = ref(null);

const openDetailModal = (item) => {
    selectedDetailItem.value = item;
    showDetailModal.value = true;
};

const closeDetailModal = () => {
    showDetailModal.value = false;
    selectedDetailItem.value = null;
};

// Helper: Category Icon
const getCategoryIcon = (category) => {
    switch (category) {
        case 'Water Rescue': return '🚤';
        case 'Vertical Rescue': return '🧗';
        case 'Medis & Evakuasi': return '🚑';
        case 'Komunikasi & Navigasi': return '📻';
        case 'Penerangan & Kelistrikan': return '💡';
        case 'Shelter & Tenda': return '🏕️';
        case 'Dapur Umum & Logistik': return '🍲';
        case 'APD & Perlengkapan Pribadi': return '🦺';
        case 'Kendaraan & Alut': return '🚒';
        case 'Peralatan Ekstrikasi': return '⚙️';
        default: return '📦';
    }
};

// Helper: Condition Badge Style
const getConditionStyle = (condition) => {
    switch (condition) {
        case 'Siap Pakai':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
        case 'Baik':
            return 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border-teal-200 dark:border-teal-800';
        case 'Rusak Ringan':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-800';
        case 'Rusak Berat':
            return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800';
        case 'Dalam Perawatan':
            return 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400 border-sky-200 dark:border-sky-800';
        default:
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
    }
};

// Helper: Status Badge Style
const getStatusStyle = (status) => {
    switch (status) {
        case 'Tersedia':
            return 'bg-emerald-100/70 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300';
        case 'Sedang Digunakan':
            return 'bg-purple-100/70 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300';
        case 'Maintenance':
            return 'bg-amber-100/70 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
        case 'Tidak Aktif':
            return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
        default:
            return 'bg-blue-100/70 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300';
    }
};
</script>

<template>
    <Head title="Inventaris Peralatan & Perlengkapan Lembaga" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <span class="p-1.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg text-sm">🛠️</span>
                <span>Peralatan & Perlengkapan • {{ partner?.name }}</span>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Dedicated Header Banner -->
            <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-blue-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-md">
                            <span>🛡️ Kesiapsiagaan Sarana & ALUT Kebencanaan</span>
                            <span class="text-white/60">•</span>
                            <span class="text-amber-200 font-bold">{{ partner?.name }}</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                            Inventaris Peralatan & Perlengkapan
                        </h1>
                        <p class="text-xs sm:text-sm text-indigo-100 max-w-2xl leading-relaxed">
                            Manajemen sarana penyelamatan, alat utama (alut) pencarian & pertolongan, peralatan medis darurat, tenda posko, radio komunikasi, dan perlengkapan misi kebencanaan.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <button
                            @click="openAddModal"
                            class="px-5 py-3 bg-white hover:bg-indigo-50 text-indigo-700 font-extrabold text-xs sm:text-sm rounded-2xl shadow-lg transition-all hover:shadow-xl hover:scale-102 flex items-center space-x-2 group cursor-pointer"
                        >
                            <span class="text-base group-hover:rotate-90 transition-transform">➕</span>
                            <span>Tambah Peralatan</span>
                        </button>
                    </div>
                </div>

                <!-- Stats Summary Row -->
                <div class="mt-6 pt-6 border-t border-white/20 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-indigo-200 uppercase tracking-wider block font-semibold">Total Item</span>
                        <span class="text-xl font-black text-white">{{ stats.total_items || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-indigo-200 uppercase tracking-wider block font-semibold">Total Fisik</span>
                        <span class="text-xl font-black text-amber-300">{{ stats.total_quantity || 0 }} <span class="text-xs font-normal text-white/70">Unit</span></span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-indigo-200 uppercase tracking-wider block font-semibold">Siap Pakai / Baik</span>
                        <span class="text-xl font-black text-emerald-300">{{ stats.ready_count || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-indigo-200 uppercase tracking-wider block font-semibold">Sedang Digunakan</span>
                        <span class="text-xl font-black text-purple-300">{{ stats.in_use_count || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-indigo-200 uppercase tracking-wider block font-semibold">Perawatan / Rusak</span>
                        <span class="text-xl font-black text-rose-300">{{ stats.maintenance_count || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-indigo-200 uppercase tracking-wider block font-semibold">Water & Vertical</span>
                        <span class="text-xl font-black text-cyan-200">{{ stats.water_vertical || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center col-span-2 sm:col-span-1">
                        <span class="text-[10px] text-indigo-200 uppercase tracking-wider block font-semibold">Medis & Shelter</span>
                        <span class="text-xl font-black text-orange-200">{{ stats.medical_shelter || 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm space-y-3">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input
                            v-model="search"
                            @keydown.enter="applyFilter"
                            type="text"
                            placeholder="Cari nama alat, kode inventaris, lokasi simpan, spesifikasi..."
                            class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:border-indigo-500 focus:outline-none dark:text-white"
                        />
                        <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Category Select -->
                    <select
                        v-model="selectedCategory"
                        @change="applyFilter"
                        class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none dark:text-white"
                    >
                        <option value="Semua">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>

                    <!-- Condition Select -->
                    <select
                        v-model="selectedCondition"
                        @change="applyFilter"
                        class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none dark:text-white"
                    >
                        <option value="Semua">Semua Kondisi</option>
                        <option v-for="c in conditions" :key="c" :value="c">{{ c }}</option>
                    </select>

                    <!-- Status Select -->
                    <select
                        v-model="selectedStatus"
                        @change="applyFilter"
                        class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none dark:text-white"
                    >
                        <option value="Semua">Semua Status</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>

                    <!-- View Mode Toggle Buttons -->
                    <div class="flex items-center space-x-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl shrink-0">
                        <button
                            type="button"
                            @click="viewMode = 'grid'"
                            :class="[
                                viewMode === 'grid' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-400 hover:text-gray-600',
                                'p-1.5 rounded-lg transition text-xs font-bold'
                            ]"
                            title="Tampilan Grid Card"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                        </button>
                        <button
                            type="button"
                            @click="viewMode = 'table'"
                            :class="[
                                viewMode === 'table' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-400 hover:text-gray-600',
                                'p-1.5 rounded-lg transition text-xs font-bold'
                            ]"
                            title="Tampilan Tabel"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                        </button>
                    </div>

                    <!-- Reset Button -->
                    <button
                        v-if="search || selectedCategory !== 'Semua' || selectedCondition !== 'Semua' || selectedStatus !== 'Semua'"
                        @click="resetFilter"
                        class="text-xs text-rose-500 hover:text-rose-700 font-bold px-2 py-1 shrink-0"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- Content Area: Empty State -->
            <div v-if="!equipments || !equipments.data || equipments.data.length === 0" class="bg-white dark:bg-gray-900 border border-dashed border-gray-200 dark:border-gray-800 rounded-3xl p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-3xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-3xl mx-auto">
                    🛠️
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Belum Ada Peralatan & Perlengkapan Terdaftar</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Mulai inventarisasi sarana kesiapsiagaan, perahu rescue, tenda posko, radio HT, perlengkapan medis, dan alat misi lembaga Anda.
                    </p>
                </div>
                <button
                    @click="openAddModal"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition inline-flex items-center space-x-2"
                >
                    <span>➕</span>
                    <span>Tambah Peralatan Pertama</span>
                </button>
            </div>

            <!-- GRID CARDS VIEW -->
            <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="item in equipments.data"
                    :key="item.id"
                    class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-5 shadow-xs hover:shadow-lg transition-all duration-200 flex flex-col justify-between space-y-4 group"
                >
                    <div class="space-y-3.5">
                        <!-- Top Bar: Code, Category, & Quantity -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                                    {{ item.item_code || 'ASET' }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    {{ getCategoryIcon(item.category) }} {{ item.category }}
                                </span>
                            </div>

                            <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-indigo-600 text-white shadow-2xs shrink-0">
                                {{ item.quantity }} {{ item.unit }}
                            </span>
                        </div>

                        <!-- Image Thumbnail / Icon Box -->
                        <div
                            @click="openDetailModal(item)"
                            class="relative w-full h-36 rounded-2xl overflow-hidden bg-gradient-to-br from-gray-50 to-indigo-50/30 dark:from-gray-800 dark:to-gray-800/50 border border-gray-100 dark:border-gray-800 flex items-center justify-center cursor-pointer group-hover:border-indigo-200 dark:group-hover:border-indigo-800 transition"
                        >
                            <img
                                v-if="item.photo_path"
                                :src="item.photo_path"
                                :alt="item.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="text-center space-y-1">
                                <span class="text-4xl block">{{ getCategoryIcon(item.category) }}</span>
                                <span class="text-[10px] text-gray-400 font-medium">Klik untuk lihat detail</span>
                            </div>

                            <!-- Overlay Condition & Status Badges -->
                            <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between pointer-events-none">
                                <span :class="['px-2 py-0.5 rounded-lg text-[10px] font-bold border backdrop-blur-md shadow-2xs', getConditionStyle(item.condition)]">
                                    ● {{ item.condition }}
                                </span>
                                <span :class="['px-2 py-0.5 rounded-lg text-[10px] font-bold shadow-2xs', getStatusStyle(item.status)]">
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>

                        <!-- Title & Specifications -->
                        <div>
                            <h4
                                @click="openDetailModal(item)"
                                class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition cursor-pointer line-clamp-2 leading-snug"
                            >
                                {{ item.name }}
                            </h4>
                            <p v-if="item.notes" class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">
                                {{ item.notes }}
                            </p>
                        </div>

                        <!-- Meta Info Grid -->
                        <div class="grid grid-cols-2 gap-2 p-2.5 bg-gray-50/80 dark:bg-gray-800/40 rounded-xl text-[11px] border border-gray-100 dark:border-gray-800">
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">📍 Lokasi</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ item.storage_location || 'Posko Utama' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">🏛️ Kepemilikan</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ item.ownership_status }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <button
                            type="button"
                            @click="openDetailModal(item)"
                            class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 flex items-center space-x-1"
                        >
                            <span>Lihat Detail</span>
                            <span>→</span>
                        </button>

                        <div class="flex items-center space-x-1.5">
                            <button
                                type="button"
                                @click="openEditModal(item)"
                                class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition"
                                title="Edit Peralatan"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button
                                type="button"
                                @click="deleteEquipment(item)"
                                class="p-2 text-gray-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition"
                                title="Hapus Peralatan"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE VIEW -->
            <div v-else class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50/80 dark:bg-gray-800/60 uppercase font-black tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="p-4">Kode</th>
                                <th class="p-4">Nama Peralatan & Kategori</th>
                                <th class="p-4">Jumlah</th>
                                <th class="p-4">Kondisi</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Lokasi Simpan</th>
                                <th class="p-4">Kepemilikan</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                            <tr v-for="item in equipments.data" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                <td class="p-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ item.item_code || '-' }}
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-lg shrink-0 overflow-hidden">
                                            <img v-if="item.photo_path" :src="item.photo_path" class="w-full h-full object-cover" />
                                            <span v-else>{{ getCategoryIcon(item.category) }}</span>
                                        </div>
                                        <div>
                                            <button @click="openDetailModal(item)" class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 text-left">
                                                {{ item.name }}
                                            </button>
                                            <span class="block text-[11px] text-gray-400">{{ item.category }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ item.quantity }} {{ item.unit }}
                                </td>
                                <td class="p-4">
                                    <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getConditionStyle(item.condition)]">
                                        {{ item.condition }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold', getStatusStyle(item.status)]">
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-600 dark:text-gray-400">
                                    {{ item.storage_location || '-' }}
                                </td>
                                <td class="p-4 text-gray-600 dark:text-gray-400">
                                    {{ item.ownership_status }}
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <button
                                            type="button"
                                            @click="openDetailModal(item)"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                                            title="Lihat Detail"
                                        >
                                            👁️
                                        </button>
                                        <button
                                            type="button"
                                            @click="openEditModal(item)"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                                            title="Edit Peralatan"
                                        >
                                            ✏️
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteEquipment(item)"
                                            class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                                            title="Hapus Peralatan"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Bar -->
            <div v-if="equipments && equipments.links && equipments.links.length > 3" class="flex justify-center gap-1.5 pt-2">
                <Link
                    v-for="(link, idx) in equipments.links"
                    :key="idx"
                    :href="link.url || '#'"
                    :class="[
                        link.active
                            ? 'bg-indigo-600 text-white font-bold'
                            : link.url
                                ? 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800'
                                : 'bg-gray-100 dark:bg-gray-800/50 text-gray-400 cursor-not-allowed',
                        'px-3.5 py-1.5 rounded-xl text-xs transition'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- ==================== MODAL: TAMBAH / EDIT PERALATAN ==================== -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-6 shadow-2xl my-8">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                        <span class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl text-base">🛠️</span>
                        <span>{{ isEditing ? 'Edit Peralatan & Perlengkapan' : 'Tambah Peralatan / Perlengkapan Baru' }}</span>
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold transition">&times;</button>
                </div>

                <!-- Error Summary Alert -->
                <div v-if="Object.keys(form.errors).length > 0" class="p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-2xl flex items-start space-x-2 text-xs text-red-700 dark:text-red-300">
                    <span class="text-sm">⚠️</span>
                    <div>
                        <p class="font-bold">Gagal Menyimpan Data Peralatan</p>
                        <p class="text-[11px]">Silakan periksa kembali isian form yang ditandai merah.</p>
                    </div>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Nama Peralatan / Perlengkapan <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: Perahu Karet LCR 4.2M + Mesin Tempel 25 PK"
                            :class="[
                                'w-full bg-gray-50 dark:bg-gray-800 border rounded-xl px-3.5 py-2.5 focus:outline-none dark:text-white',
                                form.errors.name ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:border-indigo-500'
                            ]"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-red-500 text-[11px] font-semibold">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Kode Inventaris / Aset</label>
                            <input
                                v-model="form.item_code"
                                type="text"
                                placeholder="Contoh: ALUT-01-01"
                                :class="[
                                    'w-full bg-gray-50 dark:bg-gray-800 border rounded-xl px-3.5 py-2.5 focus:outline-none uppercase dark:text-white font-mono',
                                    form.errors.item_code ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:border-indigo-500'
                                ]"
                            />
                            <p class="mt-1 text-[11px] text-gray-400">Kosongkan jika ingin dibuatkan otomatis sistem.</p>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Kategori Peralatan <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.category"
                                :class="[
                                    'w-full bg-gray-50 dark:bg-gray-800 border rounded-xl px-3.5 py-2.5 focus:outline-none dark:text-white',
                                    form.errors.category ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:border-indigo-500'
                                ]"
                                required
                            >
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Jumlah Unit <span class="text-red-500">*</span></label>
                            <input
                                v-model.number="form.quantity"
                                type="number"
                                min="0"
                                :class="[
                                    'w-full bg-gray-50 dark:bg-gray-800 border rounded-xl px-3.5 py-2.5 focus:outline-none dark:text-white',
                                    form.errors.quantity ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:border-indigo-500'
                                ]"
                                required
                            />
                            <p v-if="form.errors.quantity" class="mt-1 text-red-500 text-[11px] font-semibold">{{ form.errors.quantity }}</p>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Satuan <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.unit"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:outline-none dark:text-white"
                                required
                            >
                                <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Kondisi Fisik <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.condition"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:outline-none dark:text-white"
                                required
                            >
                                <option v-for="c in conditions" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Status Operasional <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.status"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:outline-none dark:text-white"
                                required
                            >
                                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Lokasi Penyimpanan / Gudang</label>
                            <input
                                v-model="form.storage_location"
                                type="text"
                                placeholder="Contoh: Gudang Posko Utama / Kendaraan Rescue"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:outline-none dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Status Kepemilikan</label>
                            <select
                                v-model="form.ownership_status"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:outline-none dark:text-white"
                            >
                                <option v-for="own in ownershipStatuses" :key="own" :value="own">{{ own }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Photo Upload Section -->
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Foto Peralatan / Alut (Opsional)</label>
                        <div class="flex items-center space-x-4">
                            <div v-if="photoPreview" class="relative w-20 h-20 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0">
                                <img :src="photoPreview" class="w-full h-full object-cover" />
                                <button
                                    type="button"
                                    @click="removeSelectedPhoto"
                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow-md"
                                    title="Hapus Foto"
                                >
                                    &times;
                                </button>
                            </div>
                            <div class="flex-1">
                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    @change="handlePhotoChange"
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950/40 dark:file:text-indigo-300"
                                />
                                <p class="text-[11px] text-gray-400 mt-1">Maksimal 3MB (Format: JPG, PNG, WebP).</p>
                            </div>
                        </div>
                        <p v-if="form.errors.photo" class="mt-1 text-red-500 text-[11px] font-semibold">{{ form.errors.photo }}</p>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Catatan / Spesifikasi Teknis / Riwayat Pemeliharaan</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            placeholder="Tuliskan spesifikasi, kelengkapan aksesoris, tanggal servis terakhir, atau kebutuhan perbaikan..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:outline-none dark:text-white"
                        ></textarea>
                    </div>

                    <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100 dark:border-gray-800">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Perbarui Peralatan' : 'Simpan Peralatan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: DETAIL PERALATAN ==================== -->
        <div v-if="showDetailModal && selectedDetailItem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl my-8">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="space-y-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                            {{ selectedDetailItem.item_code || 'ASET' }}
                        </span>
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white leading-snug">
                            {{ selectedDetailItem.name }}
                        </h3>
                    </div>
                    <button @click="closeDetailModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold transition">&times;</button>
                </div>

                <!-- Large Image Preview if available -->
                <div v-if="selectedDetailItem.photo_path" class="w-full h-52 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-100 dark:border-gray-800">
                    <img :src="selectedDetailItem.photo_path" :alt="selectedDetailItem.name" class="w-full h-full object-cover" />
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3 p-4 bg-gray-50/80 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800 text-xs">
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Kategori</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ getCategoryIcon(selectedDetailItem.category) }} {{ selectedDetailItem.category }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Jumlah Stok Fisik</span>
                        <span class="font-extrabold text-indigo-600 dark:text-indigo-400 text-sm">{{ selectedDetailItem.quantity }} {{ selectedDetailItem.unit }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Kondisi Fisik</span>
                        <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border inline-block mt-0.5', getConditionStyle(selectedDetailItem.condition)]">
                            ● {{ selectedDetailItem.condition }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Status Operasional</span>
                        <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold inline-block mt-0.5', getStatusStyle(selectedDetailItem.status)]">
                            {{ selectedDetailItem.status }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Lokasi Penyimpanan</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ selectedDetailItem.storage_location || 'Gudang Utama' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Status Kepemilikan</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ selectedDetailItem.ownership_status }}</span>
                    </div>
                </div>

                <!-- Notes / Specifications -->
                <div class="space-y-1 text-xs">
                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Spesifikasi & Catatan Pemeliharaan</span>
                    <p class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl text-gray-700 dark:text-gray-300 leading-relaxed text-xs whitespace-pre-wrap">
                        {{ selectedDetailItem.notes || 'Tidak ada catatan tambahan untuk peralatan ini.' }}
                    </p>
                </div>

                <!-- Footer Action in Detail Modal -->
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <button
                        type="button"
                        @click="openEditModal(selectedDetailItem); closeDetailModal();"
                        class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-xl text-xs font-bold transition flex items-center space-x-1.5"
                    >
                        <span>✏️</span>
                        <span>Edit Data Ini</span>
                    </button>
                    <button
                        type="button"
                        @click="closeDetailModal"
                        class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 text-xs font-medium transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
