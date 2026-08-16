<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { showSuccessToast, showErrorToast } from '@/Utils/toast.js';

const props = defineProps({
    partners: Array,
    volunteers: Object,
    stats: Object,
    categories: Array,
    roles: Array,
    filters: Object,
});

// Active Main Tab: 'partners' | 'volunteers' | 'management'
const activeTab = ref('partners');

// Filter state
const searchPartner = ref(props.filters.search_partner || '');
const selectedCategory = ref(props.filters.category || 'Semua');

const searchVolunteer = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || 'Semua');
const selectedStatus = ref(props.filters.status || 'Semua');
const selectedBloodType = ref(props.filters.blood_type || 'Semua');
const selectedPartnerId = ref(props.filters.partner_id || '');

const handlePartnerFilter = () => {
    router.get(route('volunteers.index'), {
        search_partner: searchPartner.value,
        category: selectedCategory.value,
        search: searchVolunteer.value,
        role: selectedRole.value,
        status: selectedStatus.value,
        blood_type: selectedBloodType.value,
        partner_id: selectedPartnerId.value,
    }, { preserveState: true, replace: true });
};

const handleVolunteerFilter = () => {
    router.get(route('volunteers.index'), {
        search_partner: searchPartner.value,
        category: selectedCategory.value,
        search: searchVolunteer.value,
        role: selectedRole.value,
        status: selectedStatus.value,
        blood_type: selectedBloodType.value,
        partner_id: selectedPartnerId.value,
    }, { preserveState: true, replace: true });
};

const clearVolunteerFilters = () => {
    searchVolunteer.value = '';
    selectedRole.value = 'Semua';
    selectedStatus.value = 'Semua';
    selectedBloodType.value = 'Semua';
    selectedPartnerId.value = '';
    handleVolunteerFilter();
};

// --- PARTNER MODAL CONTROL ---
const isPartnerModalOpen = ref(false);
const editingPartner = ref(null);
const isPartnerDetailOpen = ref(false);
const activePartnerDetail = ref(null);

const partnerForm = useForm({
    id: null,
    code: '',
    name: '',
    category: 'PMI',
    pic_name: '',
    pic_phone: '',
    pic_email: '',
    phone: '',
    email: '',
    address: '',
    status: 'Aktif',
    mou_number: '',
    personnel_count: 0,
    description: '',
});

const openAddPartnerModal = () => {
    editingPartner.value = null;
    partnerForm.reset();
    partnerForm.clearErrors();
    partnerForm.category = 'PMI';
    partnerForm.status = 'Aktif';
    isPartnerModalOpen.value = true;
};

const openEditPartnerModal = (p) => {
    editingPartner.value = p;
    partnerForm.clearErrors();
    partnerForm.id = p.id;
    partnerForm.code = p.code || '';
    partnerForm.name = p.name || '';
    partnerForm.category = p.category || 'PMI';
    partnerForm.pic_name = p.pic_name || '';
    partnerForm.pic_phone = p.pic_phone || '';
    partnerForm.pic_email = p.pic_email || '';
    partnerForm.phone = p.phone || '';
    partnerForm.email = p.email || '';
    partnerForm.address = p.address || '';
    partnerForm.status = p.status || 'Aktif';
    partnerForm.mou_number = p.mou_number || '';
    partnerForm.personnel_count = p.personnel_count || 0;
    partnerForm.description = p.description || '';
    isPartnerModalOpen.value = true;
};

const openPartnerDetail = (p) => {
    activePartnerDetail.value = p;
    isPartnerDetailOpen.value = true;
};

const submitPartnerForm = () => {
    if (editingPartner.value) {
        partnerForm.patch(route('partners.update', editingPartner.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isPartnerModalOpen.value = false;
                showSuccessToast('Profil Mitra berhasil diperbarui!');
            },
            onError: (errs) => showErrorToast('Gagal memperbarui profil mitra.')
        });
    } else {
        partnerForm.post(route('partners.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isPartnerModalOpen.value = false;
                partnerForm.reset();
                showSuccessToast('Profil Mitra berhasil ditambahkan!');
            },
            onError: (errs) => showErrorToast('Gagal menambahkan mitra baru.')
        });
    }
};

const deletePartner = (p) => {
    if (confirm(`Apakah Anda yakin ingin menghapus profil mitra "${p.name}"?`)) {
        router.delete(route('partners.destroy', p.id), {
            onSuccess: () => showSuccessToast('Mitra berhasil dihapus.')
        });
    }
};

// --- VOLUNTEER MODAL CONTROL ---
const isVolunteerModalOpen = ref(false);
const editingVolunteer = ref(null);
const isVolunteerDetailOpen = ref(false);
const activeVolunteerDetail = ref(null);

const volunteerForm = useForm({
    id: null,
    partner_id: '',
    name: '',
    email: '',
    phone: '',
    address: '',
    blood_type: 'O',
    role: 'Relawan Rescuer',
    status: 'Aktif',
    certifications: '',
    registered_at: new Date().toISOString().substr(0, 10),
    notes: '',
});

const openAddVolunteerModal = () => {
    editingVolunteer.value = null;
    volunteerForm.reset();
    volunteerForm.clearErrors();
    volunteerForm.blood_type = 'O';
    volunteerForm.role = 'Relawan Rescuer';
    volunteerForm.status = 'Aktif';
    volunteerForm.registered_at = new Date().toISOString().substr(0, 10);
    isVolunteerModalOpen.value = true;
};

const openEditVolunteerModal = (v) => {
    editingVolunteer.value = v;
    volunteerForm.clearErrors();
    volunteerForm.id = v.id;
    volunteerForm.partner_id = v.partner_id || '';
    volunteerForm.name = v.name || '';
    volunteerForm.email = v.email || '';
    volunteerForm.phone = v.phone || '';
    volunteerForm.address = v.address || '';
    volunteerForm.blood_type = v.blood_type || 'O';
    volunteerForm.role = v.role || 'Relawan Rescuer';
    volunteerForm.status = v.status || 'Aktif';
    volunteerForm.certifications = v.certifications || '';
    volunteerForm.registered_at = v.registered_at || new Date().toISOString().substr(0, 10);
    volunteerForm.notes = v.notes || '';
    isVolunteerModalOpen.value = true;
};

const openVolunteerDetail = (v) => {
    activeVolunteerDetail.value = v;
    isVolunteerDetailOpen.value = true;
};

const submitVolunteerForm = () => {
    if (editingVolunteer.value) {
        volunteerForm.patch(route('volunteers.update', editingVolunteer.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isVolunteerModalOpen.value = false;
                showSuccessToast('Data Anggota / Relawan berhasil diperbarui!');
            },
            onError: (errs) => showErrorToast('Gagal memperbarui data relawan.')
        });
    } else {
        volunteerForm.post(route('volunteers.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isVolunteerModalOpen.value = false;
                volunteerForm.reset();
                showSuccessToast('Anggota / Relawan baru berhasil ditambahkan!');
            },
            onError: (errs) => showErrorToast('Gagal menambahkan relawan.')
        });
    }
};

const deleteVolunteer = (v) => {
    if (confirm(`Apakah Anda yakin ingin menghapus relawan "${v.name}"?`)) {
        router.delete(route('volunteers.destroy', v.id), {
            onSuccess: () => showSuccessToast('Data relawan berhasil dihapus.')
        });
    }
};

// Category badge helper
const getCategoryStyle = (category) => {
    switch (category) {
        case 'PMI': return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800';
        case 'Basarnas': return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-800';
        case 'BPBD': return 'bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-400 border-orange-200 dark:border-orange-800';
        case 'Rumah Sakit': return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
        case 'Tim Rescue': return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-200 dark:border-blue-800';
        default: return 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border-purple-200 dark:border-purple-800';
    }
};
</script>

<template>
    <Head title="Mitra & Relawan" />

    <AuthenticatedLayout>
        <template #header>
            <span>Mitra & Relawan</span>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Dedicated Header Banner -->
            <div class="bg-gradient-to-r from-brand-600 via-amber-600 to-amber-500 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-md">
                            <span>🤝 Ekosistem Kolaborasi Kebencanaan</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Mitra & Relawan Terpadu MKT</h1>
                        <p class="text-xs sm:text-sm text-white/80 max-w-2xl">
                            Direktori resmi instansi mitra (PMI, Basarnas, BPBD, Rumah Sakit, Tim Rescue) serta manajemen anggota personel relawan bencana.
                        </p>
                    </div>

                    <!-- Quick Add Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <button
                            @click="openAddPartnerModal"
                            class="px-4 py-2.5 bg-white text-brand-700 hover:bg-gray-50 text-xs font-bold rounded-xl shadow-lg transition-all flex items-center space-x-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>+ Tambah Mitra Lembaga</span>
                        </button>
                        <button
                            @click="openAddVolunteerModal"
                            class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-gray-900 text-xs font-bold rounded-xl shadow-lg transition-all flex items-center space-x-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            <span>+ Registrasi Anggota/Relawan</span>
                        </button>
                    </div>
                </div>

                <!-- Overall Stats Bar -->
                <div class="mt-6 pt-6 border-t border-white/20 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-amber-100 uppercase tracking-wider block font-semibold">Total Mitra</span>
                        <span class="text-xl font-black text-white">{{ stats.total_partners || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-amber-100 uppercase tracking-wider block font-semibold">Anggota/Relawan</span>
                        <span class="text-xl font-black text-white">{{ stats.total_volunteers || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-amber-100 uppercase tracking-wider block font-semibold">Tim Rescue</span>
                        <span class="text-xl font-black text-white">{{ stats.total_rescue || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-amber-100 uppercase tracking-wider block font-semibold">Tenaga Medis</span>
                        <span class="text-xl font-black text-white">{{ stats.total_medis || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center">
                        <span class="text-[10px] text-amber-100 uppercase tracking-wider block font-semibold">Relawan Donor</span>
                        <span class="text-xl font-black text-white">{{ stats.total_donor || 0 }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 text-center col-span-2 sm:col-span-1">
                        <span class="text-[10px] text-amber-100 uppercase tracking-wider block font-semibold">Basarnas & BPBD</span>
                        <span class="text-xl font-black text-white">{{ stats.total_basarnas_bpbd || 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Tab Segment Navigation -->
            <div class="bg-gray-100/80 dark:bg-gray-800/60 p-1.5 rounded-2xl flex items-center space-x-1 border border-gray-200/60 dark:border-gray-700/60 overflow-x-auto scrollbar-thin">
                <button
                    @click="activeTab = 'partners'"
                    type="button"
                    :class="[
                        activeTab === 'partners'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 whitespace-nowrap flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <span class="text-base">🏛️</span>
                    <span>Profil Mitra Lembaga (PMI, RS, Basarnas, BPBD, Rescue)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-bold ml-1">
                        {{ partners ? partners.length : 0 }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'volunteers'"
                    type="button"
                    :class="[
                        activeTab === 'volunteers'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 whitespace-nowrap flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <span class="text-base">👥</span>
                    <span>Daftar Anggota & Personel Relawan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-bold ml-1">
                        {{ volunteers ? volunteers.total : 0 }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'management'"
                    type="button"
                    :class="[
                        activeTab === 'management'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 whitespace-nowrap flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <span class="text-base">🎖️</span>
                    <span>Susunan Manajemen & Koordinator Mitra</span>
                </button>
            </div>

            <!-- TAB 1: PROFIL MITRA LEMBAGA -->
            <div v-show="activeTab === 'partners'" class="space-y-6">
                <!-- Search & Category Filter Bar -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <!-- Category Select Buttons -->
                        <div class="flex items-center space-x-1 overflow-x-auto scrollbar-thin py-1 w-full md:w-auto">
                            <button
                                v-for="cat in categories"
                                :key="cat"
                                @click="selectedCategory = cat; handlePartnerFilter();"
                                :class="[
                                    selectedCategory === cat
                                        ? 'bg-brand-500 text-white font-bold shadow-md shadow-brand-500/20'
                                        : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 font-medium',
                                    'px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all'
                                ]"
                            >
                                {{ cat }}
                            </button>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-72">
                        <input
                            v-model="searchPartner"
                            @keyup.enter="handlePartnerFilter"
                            type="text"
                            placeholder="Cari mitra / PIC / MoU..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-4 py-2 text-xs focus:border-brand-500 focus:outline-none"
                        />
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Partners Grid Cards -->
                <div v-if="partners && partners.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="partner in partners"
                        :key="partner.id"
                        class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between space-y-4 group relative"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-50 to-amber-100 dark:from-brand-950/40 dark:to-amber-950/30 border border-brand-200/50 dark:border-brand-800/50 flex items-center justify-center text-xl font-bold text-brand-600 dark:text-brand-400 shrink-0">
                                        <img v-if="partner.logo_path" :src="partner.logo_path" class="w-full h-full object-cover rounded-2xl" />
                                        <span v-else>{{ partner.name.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">{{ partner.code }}</span>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-brand-500 transition-colors">
                                            {{ partner.name }}
                                        </h3>
                                    </div>
                                </div>
                                <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold border shrink-0', getCategoryStyle(partner.category)]">
                                    {{ partner.category }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                {{ partner.description || 'Lembaga mitra terintegrasi dalam sistem penanggulangan bencana MKT.' }}
                            </p>

                            <!-- Partner Meta Info Box -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-3 text-xs space-y-2 border border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold flex items-center space-x-1 text-gray-500">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        <span>PIC:</span>
                                    </span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ partner.pic_name || '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold flex items-center space-x-1 text-gray-500">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h32a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"></path></svg>
                                        <span>Hotline/WA:</span>
                                    </span>
                                    <span class="font-bold text-amber-600 dark:text-amber-400">{{ partner.phone || partner.pic_phone || '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold text-gray-500">Kekuatan Personel:</span>
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 font-bold text-[11px]">
                                        {{ partner.personnel_count || 0 }} Personel
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer & Actions -->
                        <div class="pt-2 flex items-center justify-between border-t border-gray-100 dark:border-gray-800 text-xs">
                            <span class="text-[10px] text-gray-400 flex items-center space-x-1">
                                <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                <span>MoU: {{ partner.mou_number || 'MoU Resmi' }}</span>
                            </span>

                            <div class="flex items-center space-x-2">
                                <button
                                    @click="openPartnerDetail(partner)"
                                    class="p-1.5 text-gray-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-950/40 rounded-lg transition-colors"
                                    title="Detail Mitra"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                <button
                                    @click="openEditPartnerModal(partner)"
                                    class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-lg transition-colors"
                                    title="Edit Mitra"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button
                                    @click="deletePartner(partner)"
                                    class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors"
                                    title="Hapus Mitra"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Partner State -->
                <div v-else class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-12 text-center space-y-3 shadow-sm">
                    <span class="text-4xl block">🏛️</span>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Belum Ada Data Mitra Lembaga</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">Tambahkan instansi mitra seperti PMI, Basarnas, BPBD, Rumah Sakit, atau Tim Rescue untuk membangun ekosistem penanggulangan bencana terpadu.</p>
                    <button
                        @click="openAddPartnerModal"
                        class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-xl shadow-md transition-all inline-flex items-center space-x-1.5"
                    >
                        <span>+ Tambah Profil Mitra</span>
                    </button>
                </div>
            </div>

            <!-- TAB 2: DAFTAR ANGGOTA & RELAWAN -->
            <div v-show="activeTab === 'volunteers'" class="space-y-6">
                <!-- Filters & Search Bar -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 w-full md:w-auto">
                        <!-- Role Filter -->
                        <select
                            v-model="selectedRole"
                            @change="handleVolunteerFilter"
                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"
                        >
                            <option v-for="r in roles" :key="r" :value="r">Peran: {{ r }}</option>
                        </select>

                        <!-- Blood Type Filter -->
                        <select
                            v-model="selectedBloodType"
                            @change="handleVolunteerFilter"
                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"
                        >
                            <option value="Semua">Gol. Darah: Semua</option>
                            <option value="A">Gol. Darah A</option>
                            <option value="B">Gol. Darah B</option>
                            <option value="AB">Gol. Darah AB</option>
                            <option value="O">Gol. Darah O</option>
                        </select>

                        <!-- Status Filter -->
                        <select
                            v-model="selectedStatus"
                            @change="handleVolunteerFilter"
                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"
                        >
                            <option value="Semua">Status: Semua</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>

                        <!-- Clear Filter -->
                        <button
                            @click="clearVolunteerFilters"
                            class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-gray-900 dark:hover:text-white rounded-xl text-xs font-semibold transition-colors"
                        >
                            Reset Filter
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-72">
                        <input
                            v-model="searchVolunteer"
                            @keyup.enter="handleVolunteerFilter"
                            type="text"
                            placeholder="Cari nama / keahlian / telp..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-4 py-2 text-xs focus:border-brand-500 focus:outline-none"
                        />
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Volunteers Table View -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                            <thead class="bg-gray-50/80 dark:bg-gray-800/60 uppercase text-[10px] font-black text-gray-400 tracking-wider border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th class="px-6 py-4">Anggota / Relawan</th>
                                    <th class="px-6 py-4">Tipe Peran & Sertifikasi</th>
                                    <th class="px-6 py-4">Induk Mitra</th>
                                    <th class="px-6 py-4 text-center">Gol. Darah</th>
                                    <th class="px-6 py-4">Kontak & Alamat</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-for="v in volunteers.data" :key="v.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-amber-400 text-white font-bold flex items-center justify-center shrink-0 shadow-sm">
                                                {{ v.name.charAt(0) }}
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-900 dark:text-white">{{ v.name }}</h4>
                                                <span class="text-[10px] text-gray-400">Terdaftar: {{ v.registered_at || '-' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400 border border-brand-200/50">
                                                {{ v.role }}
                                            </span>
                                            <p v-if="v.certifications" class="text-[10px] text-gray-400 line-clamp-1">
                                                🎗️ {{ v.certifications }}
                                            </p>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span v-if="v.partner" class="font-semibold text-gray-900 dark:text-white flex items-center space-x-1">
                                            <span class="text-xs">🏛️</span>
                                            <span>{{ v.partner.name }}</span>
                                        </span>
                                        <span v-else class="text-gray-400 italic">MKT Independen</span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="w-7 h-7 rounded-full bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800 font-black inline-flex items-center justify-center text-xs">
                                            {{ v.blood_type || 'O' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="space-y-0.5">
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ v.phone || '-' }}</p>
                                            <p class="text-[10px] text-gray-400 truncate max-w-xs">{{ v.email || v.address || '-' }}</p>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span :class="[
                                            v.status === 'Aktif'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-300',
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold border'
                                        ]">
                                            {{ v.status }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-1.5">
                                            <button @click="openVolunteerDetail(v)" class="p-1.5 text-gray-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="KTA Card Preview">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"></path></svg>
                                            </button>
                                            <button @click="openEditVolunteerModal(v)" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Relawan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <button @click="deleteVolunteer(v)" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Relawan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: SUSUNAN MANAJEMEN MITRA & KOORDINATOR LAPANGAN -->
            <div v-show="activeTab === 'management'" class="space-y-6">
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <span class="text-lg">🎖️</span>
                            <span>Struktur Komando & Koordinator Mitra Bencana</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">Pemimpin unit dan Penanggung Jawab Operasional (PIC) lintas instansi mitra MKT.</p>
                    </div>

                    <!-- Category Hierarchy Matrix -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Card 1: Basarnas & BPBD Komando -->
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 rounded-3xl p-6 border border-amber-200/60 dark:border-amber-800/40 space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white font-bold flex items-center justify-center text-lg">
                                    🚨
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm">Basarnas & BPBD (Search & Rescue)</h4>
                                    <span class="text-[10px] text-amber-700 dark:text-amber-400 font-semibold">Komando Utama Respon Bencana</span>
                                </div>
                            </div>
                            <div class="space-y-3 pt-2">
                                <div class="bg-white dark:bg-gray-900 rounded-2xl p-3 shadow-sm border border-amber-100 dark:border-gray-800 flex items-center justify-between">
                                    <div>
                                        <h5 class="text-xs font-bold text-gray-900 dark:text-white">Mexianus Bekabel, S.Sos., M.M.</h5>
                                        <p class="text-[10px] text-gray-400">Kepala Kantor SAR Kelas A Makassar</p>
                                    </div>
                                    <span class="text-xs font-bold text-amber-600">08129988770</span>
                                </div>
                                <div class="bg-white dark:bg-gray-900 rounded-2xl p-3 shadow-sm border border-amber-100 dark:border-gray-800 flex items-center justify-between">
                                    <div>
                                        <h5 class="text-xs font-bold text-gray-900 dark:text-white">Drs. A. Hasim, M.Si.</h5>
                                        <p class="text-[10px] text-gray-400">Kepala Pusdalops BPBD Sulsel</p>
                                    </div>
                                    <span class="text-xs font-bold text-amber-600">08137766554</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: PMI & Unit Transfusi Darah -->
                        <div class="bg-gradient-to-br from-rose-50 to-red-50 dark:from-rose-950/20 dark:to-red-950/20 rounded-3xl p-6 border border-rose-200/60 dark:border-rose-800/40 space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-2xl bg-rose-500 text-white font-bold flex items-center justify-center text-lg">
                                    🩸
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm">PMI & Relawan Donor Darah</h4>
                                    <span class="text-[10px] text-rose-700 dark:text-rose-400 font-semibold">Stok Darah & Pertolongan Pertama</span>
                                </div>
                            </div>
                            <div class="space-y-3 pt-2">
                                <div class="bg-white dark:bg-gray-900 rounded-2xl p-3 shadow-sm border border-rose-100 dark:border-gray-800 flex items-center justify-between">
                                    <div>
                                        <h5 class="text-xs font-bold text-gray-900 dark:text-white">Dr. H. Syamsul Rizal, S.E.</h5>
                                        <p class="text-[10px] text-gray-400">Ketua PMI & Pengarah UTD Makassar</p>
                                    </div>
                                    <span class="text-xs font-bold text-rose-600">08114455667</span>
                                </div>
                                <div class="bg-white dark:bg-gray-900 rounded-2xl p-3 shadow-sm border border-rose-100 dark:border-gray-800 flex items-center justify-between">
                                    <div>
                                        <h5 class="text-xs font-bold text-gray-900 dark:text-white">Dewi Lestari, S.Kep.</h5>
                                        <p class="text-[10px] text-gray-400">Koordinator KSR PMI & Donor Darah</p>
                                    </div>
                                    <span class="text-xs font-bold text-rose-600">081922334455</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: RS & Tim Medis Bencana -->
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/20 dark:to-teal-950/20 rounded-3xl p-6 border border-emerald-200/60 dark:border-emerald-800/40 space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white font-bold flex items-center justify-center text-lg">
                                    🏥
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm">Rumah Sakit & Tenaga Medis</h4>
                                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-semibold">Triage, Trauma Center & Ambulans</span>
                                </div>
                            </div>
                            <div class="space-y-3 pt-2">
                                <div class="bg-white dark:bg-gray-900 rounded-2xl p-3 shadow-sm border border-emerald-100 dark:border-gray-800 flex items-center justify-between">
                                    <div>
                                        <h5 class="text-xs font-bold text-gray-900 dark:text-white">dr. Andi Nurhayati, Sp.An</h5>
                                        <p class="text-[10px] text-gray-400">Ketua Tim Medis Emergency RS Wahidin</p>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-600">08123344556</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Tim Rescue Terpadu MKT -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 rounded-3xl p-6 border border-blue-200/60 dark:border-blue-800/40 space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-2xl bg-blue-500 text-white font-bold flex items-center justify-center text-lg">
                                    🛶
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm">Tim Rescue 727 Terpadu MKT</h4>
                                    <span class="text-[10px] text-blue-700 dark:text-blue-400 font-semibold">Reaksi Cepat Field Rescuers</span>
                                </div>
                            </div>
                            <div class="space-y-3 pt-2">
                                <div class="bg-white dark:bg-gray-900 rounded-2xl p-3 shadow-sm border border-blue-100 dark:border-gray-800 flex items-center justify-between">
                                    <div>
                                        <h5 class="text-xs font-bold text-gray-900 dark:text-white">Kapten (Purn) Hendra Suwandi</h5>
                                        <p class="text-[10px] text-gray-400">Komandan Operasional Rescue MKT</p>
                                    </div>
                                    <span class="text-xs font-bold text-blue-600">08128899112</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- --- MODAL 1: FORM TAMBAH / EDIT MITRA LEMBAGA --- -->
        <div v-if="isPartnerModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-6 shadow-2xl my-8">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                        <span>🏛️</span>
                        <span>{{ editingPartner ? 'Edit Profil Mitra Lembaga' : 'Tambah Instansi Mitra Baru' }}</span>
                    </h3>
                    <button @click="isPartnerModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>

                <form @submit.prevent="submitPartnerForm" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Nama Mitra / Instansi</label>
                            <input v-model="partnerForm.name" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Kategori Mitra</label>
                            <select v-model="partnerForm.category" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none">
                                <option value="PMI">PMI (Palang Merah Indonesia)</option>
                                <option value="Basarnas">Basarnas (Search and Rescue)</option>
                                <option value="BPBD">BPBD (Penanggulangan Bencana)</option>
                                <option value="Rumah Sakit">Rumah Sakit / Tenaga Medis</option>
                                <option value="Tim Rescue">Tim Rescue Lembaga</option>
                                <option value="Filantropi">Lembaga Filantropi / Donatur</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Penanggung Jawab (PIC)</label>
                            <input v-model="partnerForm.pic_name" type="text" placeholder="Nama PIC Lembaga" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">No. Telp / WA PIC</label>
                            <input v-model="partnerForm.pic_phone" type="text" placeholder="0812xxxx" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">No. Surat MoU / Kerjasama</label>
                            <input v-model="partnerForm.mou_number" type="text" placeholder="MoU/MKT-PMI/2025/001" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Estimasi Kekuatan Personel</label>
                            <input v-model="partnerForm.personnel_count" type="number" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Alamat Kantor / Markas</label>
                        <textarea v-model="partnerForm.address" rows="2" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Deskripsi Ruang Lingkup Kerjasama</label>
                        <textarea v-model="partnerForm.description" rows="2" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex justify-end space-x-3">
                        <button type="button" @click="isPartnerModalOpen = false" class="px-4 py-2 border rounded-xl text-gray-600 dark:text-gray-300">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-brand-500 text-white font-bold rounded-xl shadow-md">Simpan Mitra</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- --- MODAL 2: FORM TAMBAH / EDIT ANGGOTA RELAWAN --- -->
        <div v-if="isVolunteerModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-6 shadow-2xl my-8">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                        <span>👥</span>
                        <span>{{ editingVolunteer ? 'Edit Data Anggota / Relawan' : 'Registrasi Anggota / Relawan Baru' }}</span>
                    </h3>
                    <button @click="isVolunteerModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>

                <form @submit.prevent="submitVolunteerForm" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Mitra Induk (Jika Ada)</label>
                        <select v-model="volunteerForm.partner_id" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none">
                            <option value="">-- MKT Independen (Tanpa Mitra Induk) --</option>
                            <option v-for="p in partners" :key="p.id" :value="p.id">{{ p.name }} ({{ p.category }})</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Nama Lengkap</label>
                            <input v-model="volunteerForm.name" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Peran Relawan</label>
                            <select v-model="volunteerForm.role" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none">
                                <option value="Relawan Rescuer">Relawan Rescuer (SAR)</option>
                                <option value="Tenaga Medis">Tenaga Medis (Dokter/Perawat)</option>
                                <option value="Donor Darah">Relawan Donor Darah (PMI)</option>
                                <option value="Relawan Logistik">Relawan Logistik & Dapur Umum</option>
                                <option value="Staff Basarnas/BPBD">Staff Basarnas / BPBD</option>
                                <option value="Relawan Umum">Relawan Umum</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">No. HP / WA</label>
                            <input v-model="volunteerForm.phone" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Golongan Darah</label>
                            <select v-model="volunteerForm.blood_type" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none">
                                <option value="A">Golongan Darah A</option>
                                <option value="B">Golongan Darah B</option>
                                <option value="AB">Golongan Darah AB</option>
                                <option value="O">Golongan Darah O</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Email (Untuk Notifikasi Akun)</label>
                        <input v-model="volunteerForm.email" type="email" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Sertifikasi & Keahlian Khusus</label>
                        <input v-model="volunteerForm.certifications" type="text" placeholder="Contoh: Sertifikasi SAR Water Rescue, BTCLS, KSR PMI" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Alamat Domisili</label>
                        <textarea v-model="volunteerForm.address" rows="2" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex justify-end space-x-3">
                        <button type="button" @click="isVolunteerModalOpen = false" class="px-4 py-2 border rounded-xl text-gray-600 dark:text-gray-300">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-amber-400 text-gray-900 font-bold rounded-xl shadow-md">Simpan Anggota</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
