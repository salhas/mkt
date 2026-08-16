<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    members: Object,
    stats: Object,
    chartData: Object,
    tiers: Array,
    divisions: Array,
    filters: Object,
});

// Active Main Tab: 'chart' | 'Dewan Pembina' | 'Dewan Pengawas' | 'Pengurus' | 'Anggota'
const activeTab = ref(props.filters.tier && props.filters.tier !== 'Semua' ? props.filters.tier : 'chart');

// Display Style for Tier Tabs: 'grid' or 'table'
const displayMode = ref('grid');

// Filters state
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'Semua');
const selectedDivision = ref(props.filters.division || 'Semua');

const switchTab = (tabName) => {
    activeTab.value = tabName;
    if (tabName === 'chart') {
        router.get(route('management.index'), {}, { preserveState: true, replace: true });
    } else {
        router.get(route('management.index'), {
            tier: tabName,
            search: search.value,
            status: selectedStatus.value,
            division: selectedDivision.value,
        }, { preserveState: true, replace: true });
    }
};

const handleFilter = () => {
    router.get(route('management.index'), {
        tier: activeTab.value === 'chart' ? 'Semua' : activeTab.value,
        search: search.value,
        status: selectedStatus.value,
        division: selectedDivision.value,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = 'Semua';
    selectedDivision.value = 'Semua';
    handleFilter();
};

// Detail Modal State
const isDetailModalOpen = ref(false);
const activeMemberDetail = ref(null);

const openDetailModal = (member) => {
    activeMemberDetail.value = member;
    isDetailModalOpen.value = true;
};

// Form Modal State (Add / Edit)
const isFormModalOpen = ref(false);
const editingMember = ref(null);

const form = useForm({
    id: null,
    member_number: '',
    name: '',
    tier: 'Pengurus',
    position: '',
    division: '',
    email: '',
    phone: '',
    address: '',
    status: 'Aktif',
    period: '2024 - 2029',
    order_index: 0,
    notes: '',
    photo: null,
});

const openAddModal = (presetTier = 'Pengurus') => {
    editingMember.value = null;
    form.reset();
    form.clearErrors();
    form.tier = (activeTab.value !== 'chart') ? activeTab.value : presetTier;
    form.status = 'Aktif';
    form.period = '2024 - 2029';
    isFormModalOpen.value = true;
};

const openEditModal = (member) => {
    editingMember.value = member;
    form.clearErrors();
    form.id = member.id;
    form.member_number = member.member_number || '';
    form.name = member.name || '';
    form.tier = member.tier || 'Pengurus';
    form.position = member.position || '';
    form.division = member.division || '';
    form.email = member.email || '';
    form.phone = member.phone || '';
    form.address = member.address || '';
    form.status = member.status || 'Aktif';
    form.period = member.period || '2024 - 2029';
    form.order_index = member.order_index || 0;
    form.notes = member.notes || '';
    form.photo = null;
    isFormModalOpen.value = true;
};

const submitForm = () => {
    if (editingMember.value) {
        form.post(route('management.update', editingMember.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isFormModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('management.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isFormModalOpen.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = (member) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data "${member.name}" dari struktur pengurus/anggota?`)) {
        router.delete(route('management.destroy', member.id), {
            preserveScroll: true,
        });
    }
};

// Body Scroll Lock Watcher
watch([isDetailModalOpen, isFormModalOpen], ([detailOpen, formOpen]) => {
    if (detailOpen || formOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

// Styling Helpers for Tiers & Statuses
const getTierBadgeClass = (tier) => {
    switch (tier) {
        case 'Dewan Pembina':
            return 'bg-purple-100 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800';
        case 'Dewan Pengawas':
            return 'bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800';
        case 'Pengurus':
            return 'bg-brand-100 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 border-brand-200 dark:border-brand-800';
        case 'Anggota':
        default:
            return 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Aktif':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
        case 'Demisioner':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800';
        case 'Tidak Aktif':
        default:
            return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800';
    }
};
</script>

<template>
    <Head title="Manajemen Pengurus & Anggota MKT" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="p-6 md:p-8 rounded-3xl bg-gradient-to-r from-brand-500 via-amber-500 to-orange-500 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold backdrop-blur-md mb-3">
                            <span>🏛️ Struktur Keorganisasian MKT</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Manajemen Pengurus & Anggota</h1>
                        <p class="text-sm text-brand-100 mt-1 max-w-2xl">
                            Pusat tata kelola struktur Dewan Pembina, Dewan Pengawas, Pengurus Harian, serta Anggota Relawan MKT.
                        </p>
                    </div>
                    <div class="flex items-center space-x-3 shrink-0">
                        <button
                            @click="openAddModal('Pengurus')"
                            class="px-5 py-2.5 rounded-2xl bg-white text-brand-700 font-bold text-xs hover:bg-brand-50 shadow-md transition flex items-center space-x-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>+ Tambah Pengurus / Anggota</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================= MAIN ELEMENT TABS BAR ================= -->
            <div class="p-2 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm flex items-center overflow-x-auto scrollbar-thin space-x-2">
                <!-- TAB 1: ALL STRUCTURE POHON HIERARKI -->
                <button
                    @click="switchTab('chart')"
                    :class="[
                        activeTab === 'chart'
                            ? 'bg-gradient-to-r from-brand-500 to-amber-500 text-white font-extrabold shadow-md'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-semibold',
                        'px-4 py-3 rounded-2xl text-xs transition flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span>🌲 Pohon Bagan Struktur</span>
                </button>

                <!-- TAB 2: DEWAN PEMBINA -->
                <button
                    @click="switchTab('Dewan Pembina')"
                    :class="[
                        activeTab === 'Dewan Pembina'
                            ? 'bg-purple-600 text-white font-extrabold shadow-md'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-950/40 font-semibold',
                        'px-4 py-3 rounded-2xl text-xs transition flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01-6.479L12 14z"></path>
                    </svg>
                    <span>Dewan Pembina</span>
                    <span :class="[activeTab === 'Dewan Pembina' ? 'bg-white/20 text-white' : 'bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300', 'px-2 py-0.5 rounded-full text-[10px] font-bold ml-1']">
                        {{ stats.total_pembina || 0 }}
                    </span>
                </button>

                <!-- TAB 3: DEWAN PENGAWAS -->
                <button
                    @click="switchTab('Dewan Pengawas')"
                    :class="[
                        activeTab === 'Dewan Pengawas'
                            ? 'bg-blue-600 text-white font-extrabold shadow-md'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-950/40 font-semibold',
                        'px-4 py-3 rounded-2xl text-xs transition flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span>Dewan Pengawas</span>
                    <span :class="[activeTab === 'Dewan Pengawas' ? 'bg-white/20 text-white' : 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300', 'px-2 py-0.5 rounded-full text-[10px] font-bold ml-1']">
                        {{ stats.total_pengawas || 0 }}
                    </span>
                </button>

                <!-- TAB 4: PENGURUS HARIAN -->
                <button
                    @click="switchTab('Pengurus')"
                    :class="[
                        activeTab === 'Pengurus'
                            ? 'bg-brand-600 text-white font-extrabold shadow-md'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-950/40 font-semibold',
                        'px-4 py-3 rounded-2xl text-xs transition flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Pengurus Harian</span>
                    <span :class="[activeTab === 'Pengurus' ? 'bg-white/20 text-white' : 'bg-brand-100 dark:bg-brand-950 text-brand-700 dark:text-brand-300', 'px-2 py-0.5 rounded-full text-[10px] font-bold ml-1']">
                        {{ stats.total_pengurus || 0 }}
                    </span>
                </button>

                <!-- TAB 5: ANGGOTA & RELAWAN -->
                <button
                    @click="switchTab('Anggota')"
                    :class="[
                        activeTab === 'Anggota'
                            ? 'bg-emerald-600 text-white font-extrabold shadow-md'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 font-semibold',
                        'px-4 py-3 rounded-2xl text-xs transition flex items-center space-x-2 shrink-0'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Anggota & Relawan</span>
                    <span :class="[activeTab === 'Anggota' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300', 'px-2 py-0.5 rounded-full text-[10px] font-bold ml-1']">
                        {{ stats.total_anggota || 0 }}
                    </span>
                </button>
            </div>

            <!-- ================= TAB CONTENT 1: VISUAL TREE STRUCTURE (POHON HIERARKI ORGANISASI) ================= -->
            <div v-if="activeTab === 'chart'" class="space-y-6">
                <div class="p-6 md:p-8 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-x-auto space-y-10 min-w-[760px]">
                    <!-- Tree Header Banner -->
                    <div class="text-center max-w-xl mx-auto space-y-1">
                        <span class="px-3.5 py-1 rounded-full bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 font-bold text-[11px] uppercase tracking-wider">
                            🌲 Pohon Hierarki Keorganisasian MKT
                        </span>
                        <h2 class="text-xl font-black text-gray-800 dark:text-gray-100">Struktur Pohon Organisasi Yayasan MKT</h2>
                        <p class="text-xs text-gray-400">Klik pada foto/nama pengurus untuk membuka kartu informasi detail & kontak</p>
                    </div>

                    <!-- TREE LEVEL 1: DEWAN PEMBINA (APEX ADVISORY NODE) -->
                    <div class="flex flex-col items-center relative">
                        <div class="px-4 py-1.5 rounded-full bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 font-extrabold text-[11px] uppercase tracking-wider shadow-sm flex items-center space-x-1.5 mb-4 border border-purple-200 dark:border-purple-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path></svg>
                            <span>Level 1: Dewan Pembina</span>
                        </div>

                        <div class="flex items-center justify-center gap-6">
                            <div
                                v-for="member in chartData.pembina"
                                :key="member.id"
                                @click="openDetailModal(member)"
                                class="p-4 rounded-2xl bg-gradient-to-b from-purple-50 via-white to-purple-50/30 dark:from-purple-950/40 dark:to-gray-900 border-2 border-purple-300 dark:border-purple-800 hover:scale-105 transition cursor-pointer shadow-md w-64 text-center group"
                            >
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-purple-500 to-indigo-600 text-white font-black text-xl flex items-center justify-center shadow-md overflow-hidden mb-2 ring-4 ring-purple-100 dark:ring-purple-950">
                                    <img v-if="member.photo_path" :src="member.photo_path" class="w-full h-full object-cover" />
                                    <span v-else>{{ member.name.charAt(0) }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 block mb-1 truncate">
                                    {{ member.position }}
                                </span>
                                <h4 class="text-xs font-black text-gray-800 dark:text-gray-100 group-hover:text-purple-600 transition truncate">{{ member.name }}</h4>
                                <p class="text-[10px] text-gray-400 mt-0.5 truncate">{{ member.division || 'Pembina Utama' }}</p>
                            </div>
                        </div>

                        <!-- Vertical Connecting Trunk Line Down -->
                        <div class="w-0.5 h-10 bg-gradient-to-b from-purple-400 to-blue-400 my-2"></div>
                    </div>

                    <!-- TREE LEVEL 2: DEWAN PENGAWAS & PENGURUS EKSEKUTIF -->
                    <div class="grid grid-cols-2 gap-8 items-start relative max-w-4xl mx-auto">
                        <!-- Horizontal Branch Connector across Level 2 -->
                        <div class="absolute top-0 left-1/4 right-1/4 h-0.5 bg-blue-300 dark:bg-blue-800 -translate-y-3"></div>

                        <!-- Left Branch: Dewan Pengawas -->
                        <div class="flex flex-col items-center">
                            <div class="px-4 py-1.5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 font-extrabold text-[11px] uppercase tracking-wider shadow-sm flex items-center space-x-1.5 mb-3 border border-blue-200 dark:border-blue-800">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <span>Level 2: Dewan Pengawas</span>
                            </div>

                            <div class="space-y-3 w-full max-w-xs">
                                <div
                                    v-for="member in chartData.pengawas"
                                    :key="member.id"
                                    @click="openDetailModal(member)"
                                    class="p-3.5 rounded-2xl bg-gradient-to-r from-blue-50 via-white to-blue-50/20 dark:from-blue-950/30 dark:to-gray-900 border border-blue-200 dark:border-blue-900/50 hover:scale-102 transition cursor-pointer shadow-sm flex items-center space-x-3 group"
                                >
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-500 to-sky-500 text-white font-bold text-sm flex items-center justify-center shadow overflow-hidden shrink-0">
                                        <img v-if="member.photo_path" :src="member.photo_path" class="w-full h-full object-cover" />
                                        <span v-else>{{ member.name.charAt(0) }}</span>
                                    </div>
                                    <div class="truncate">
                                        <span class="text-[9px] font-bold text-blue-600 dark:text-blue-400 block truncate">{{ member.position }}</span>
                                        <h4 class="text-xs font-bold text-gray-800 dark:text-gray-100 group-hover:text-blue-600 transition truncate">{{ member.name }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Branch: Pengurus Eksekutif (Ketua Umum) -->
                        <div class="flex flex-col items-center">
                            <div class="px-4 py-1.5 rounded-full bg-brand-100 dark:bg-brand-950 text-brand-700 dark:text-brand-300 font-extrabold text-[11px] uppercase tracking-wider shadow-sm flex items-center space-x-1.5 mb-3 border border-brand-200 dark:border-brand-800">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span>Level 2: Pengurus Eksekutif</span>
                            </div>

                            <!-- Ketua Umum Node -->
                            <div
                                v-if="chartData.pengurus && chartData.pengurus.length > 0"
                                @click="openDetailModal(chartData.pengurus[0])"
                                class="p-4 rounded-2xl bg-gradient-to-b from-brand-500 to-amber-500 text-white hover:scale-105 transition cursor-pointer shadow-lg w-full max-w-xs text-center group"
                            >
                                <div class="w-12 h-12 mx-auto rounded-xl bg-white/20 text-white font-black text-lg flex items-center justify-center shadow-md overflow-hidden mb-1.5 ring-2 ring-white/50">
                                    <img v-if="chartData.pengurus[0].photo_path" :src="chartData.pengurus[0].photo_path" class="w-full h-full object-cover" />
                                    <span v-else>{{ chartData.pengurus[0].name.charAt(0) }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-white text-brand-700 block mb-1 uppercase tracking-wider">
                                    {{ chartData.pengurus[0].position }}
                                </span>
                                <h4 class="text-xs font-black">{{ chartData.pengurus[0].name }}</h4>
                            </div>

                            <!-- Vertical Trunk down to Bidang/Divisi -->
                            <div class="w-0.5 h-8 bg-brand-300 dark:bg-brand-800 my-2"></div>
                        </div>
                    </div>

                    <!-- TREE LEVEL 3: DIVISI & KEPALA BIDANG EKSEKUTIF -->
                    <div class="space-y-4 pt-6 border-t border-dashed border-gray-200 dark:border-gray-800">
                        <div class="text-center">
                            <span class="px-4 py-1.5 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 font-extrabold text-[11px] uppercase tracking-wider shadow-sm inline-flex items-center space-x-1.5 border border-amber-200 dark:border-amber-800">
                                ⭐ Level 3: Sekretariat, Keuangan & Kepala Bidang
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div
                                v-for="member in (chartData.pengurus ? chartData.pengurus.slice(1) : [])"
                                :key="member.id"
                                @click="openDetailModal(member)"
                                class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-brand-100 dark:border-brand-900/40 hover:border-brand-300 hover:shadow-md transition cursor-pointer space-y-2 group"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-400 to-orange-500 text-white font-bold text-sm flex items-center justify-center shadow-sm overflow-hidden shrink-0">
                                        <img v-if="member.photo_path" :src="member.photo_path" class="w-full h-full object-cover" />
                                        <span v-else>{{ member.name.charAt(0) }}</span>
                                    </div>
                                    <div class="truncate">
                                        <span class="text-[10px] font-bold text-brand-600 dark:text-brand-400 block truncate">{{ member.position }}</span>
                                        <h4 class="text-xs font-bold text-gray-800 dark:text-gray-100 group-hover:text-brand-600 transition truncate">{{ member.name }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TREE LEVEL 4: ANGGOTA & RELAWAN LAPANGAN -->
                    <div class="space-y-4 pt-6 border-t border-dashed border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="px-4 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-extrabold text-[11px] uppercase tracking-wider shadow-sm inline-flex items-center space-x-1.5 border border-emerald-200 dark:border-emerald-800">
                                👥 Level 4: Tim Operasional & Relawan
                            </span>
                            <button @click="switchTab('Anggota')" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                Lihat Semua Relawan ({{ stats.total_anggota }}) ➔
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                            <div
                                v-for="member in chartData.anggota"
                                :key="member.id"
                                @click="openDetailModal(member)"
                                class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 hover:shadow-sm transition cursor-pointer space-y-1.5 group"
                            >
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-emerald-500 to-teal-500 text-white font-bold text-xs flex items-center justify-center shadow-sm overflow-hidden shrink-0">
                                        <img v-if="member.photo_path" :src="member.photo_path" class="w-full h-full object-cover" />
                                        <span v-else>{{ member.name.charAt(0) }}</span>
                                    </div>
                                    <div class="truncate">
                                        <h4 class="text-xs font-bold text-gray-800 dark:text-gray-100 group-hover:text-emerald-600 transition truncate">{{ member.name }}</h4>
                                        <span class="text-[9px] text-gray-400 block truncate">{{ member.position }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= TAB CONTENT 2: SPECIFIC CATEGORY TAB VIEW ================= -->
            <div v-else class="space-y-6">
                <!-- Toolbar: Search & Segmented Display Mode Switcher -->
                <div class="p-5 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <!-- Left: Search Box & Filters -->
                        <div class="flex flex-wrap items-center gap-3 flex-1">
                            <div class="relative flex-1 min-w-[220px]">
                                <input
                                    v-model="search"
                                    type="text"
                                    @keyup.enter="handleFilter"
                                    :placeholder="`Cari dalam ${activeTab}...`"
                                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-brand-500 transition"
                                />
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <select
                                v-model="selectedStatus"
                                @change="handleFilter"
                                class="py-2.5 px-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-brand-500 transition min-w-[130px]"
                            >
                                <option value="Semua">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                                <option value="Demisioner">Demisioner</option>
                            </select>

                            <button
                                @click="handleFilter"
                                class="px-4 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs shadow-sm transition"
                            >
                                Cari
                            </button>
                            <button
                                @click="clearFilters"
                                class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-100 text-xs font-semibold transition"
                            >
                                Reset
                            </button>
                        </div>

                        <!-- Right: Sub-View Segmented Display Switcher (Grid vs Table) -->
                        <div class="bg-gray-100 dark:bg-gray-800 p-1 rounded-2xl flex items-center space-x-1 shrink-0">
                            <button
                                @click="displayMode = 'grid'"
                                :class="[
                                    displayMode === 'grid'
                                        ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm font-bold'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium',
                                    'px-3.5 py-1.5 rounded-xl text-xs transition flex items-center space-x-1.5'
                                ]"
                            >
                                <span>Kartu Grid</span>
                            </button>
                            <button
                                @click="displayMode = 'table'"
                                :class="[
                                    displayMode === 'table'
                                        ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm font-bold'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium',
                                    'px-3.5 py-1.5 rounded-xl text-xs transition flex items-center space-x-1.5'
                                ]"
                            >
                                <span>Tabel Data</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SUB-VIEW 1: CARDS GRID DISPLAY -->
                <div v-if="displayMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div
                        v-for="member in members.data"
                        :key="member.id"
                        class="p-5 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition space-y-4 flex flex-col justify-between group"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-500 to-amber-500 text-white font-black text-xl flex items-center justify-center shadow-md overflow-hidden shrink-0">
                                    <img v-if="member.photo_path" :src="member.photo_path" class="w-full h-full object-cover" />
                                    <span v-else>{{ member.name.charAt(0) }}</span>
                                </div>
                                <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadgeClass(member.status)]">
                                    {{ member.status }}
                                </span>
                            </div>

                            <div>
                                <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold border inline-block mb-1', getTierBadgeClass(member.tier)]">
                                    {{ member.position }}
                                </span>
                                <h3 class="text-sm font-extrabold text-gray-800 dark:text-gray-100 group-hover:text-brand-600 transition">{{ member.name }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ member.division || 'Masa Jabatan: ' + member.period }}</p>
                            </div>

                            <div class="space-y-1 text-xs pt-2 border-t border-gray-100 dark:border-gray-800 text-gray-600 dark:text-gray-300">
                                <p v-if="member.phone" class="flex items-center space-x-2">
                                    <span>📞</span>
                                    <span>{{ member.phone }}</span>
                                </p>
                                <p v-if="member.email" class="flex items-center space-x-2 text-gray-400 text-[11px] truncate">
                                    <span>✉️</span>
                                    <span class="truncate">{{ member.email }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs">
                            <span class="font-mono text-[10px] text-gray-400">{{ member.member_number || 'NIKA' }}</span>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="openDetailModal(member)"
                                    class="px-3 py-1.5 rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 font-bold hover:bg-brand-100 transition"
                                >
                                    Detail
                                </button>
                                <button
                                    @click="openEditModal(member)"
                                    class="p-1.5 rounded-xl text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition"
                                >
                                    ✏️
                                </button>
                                <button
                                    @click="confirmDelete(member)"
                                    class="p-1.5 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="!members.data || members.data.length === 0" class="col-span-full p-12 text-center text-xs text-gray-400 italic bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800">
                        Belum ada anggota yang terdaftar dalam tab "{{ activeTab }}".
                    </div>
                </div>

                <!-- SUB-VIEW 2: DATA TABLE DISPLAY -->
                <div v-else class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800/70 border-b border-gray-100 dark:border-gray-800 text-gray-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="p-4">Nama & No. Induk</th>
                                    <th class="p-4">Jabatan & Bidang</th>
                                    <th class="p-4">Periode</th>
                                    <th class="p-4">Kontak</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="member in members.data"
                                    :key="member.id"
                                    class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-400 to-amber-500 text-white font-bold text-xs flex items-center justify-center shadow-sm overflow-hidden shrink-0">
                                                <img v-if="member.photo_path" :src="member.photo_path" class="w-full h-full object-cover" />
                                                <span v-else>{{ member.name.charAt(0) }}</span>
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-800 dark:text-gray-100 block">{{ member.name }}</span>
                                                <span class="text-[10px] font-mono text-gray-400">{{ member.member_number || '-' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <span class="font-semibold text-gray-800 dark:text-gray-200 block">{{ member.position }}</span>
                                        <span class="text-[10px] text-gray-400 block">{{ member.division || '-' }}</span>
                                    </td>

                                    <td class="p-4 text-gray-600 dark:text-gray-300">
                                        {{ member.period || '-' }}
                                    </td>

                                    <td class="p-4 space-y-0.5">
                                        <span v-if="member.phone" class="block text-gray-700 dark:text-gray-300">📞 {{ member.phone }}</span>
                                        <span v-if="member.email" class="block text-gray-400 text-[10px]">✉️ {{ member.email }}</span>
                                    </td>

                                    <td class="p-4">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadgeClass(member.status)]">
                                            {{ member.status }}
                                        </span>
                                    </td>

                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <button
                                                @click="openDetailModal(member)"
                                                class="p-1.5 rounded-lg text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-950/40 transition"
                                                title="Lihat Detail"
                                            >
                                                👁️
                                            </button>
                                            <button
                                                @click="openEditModal(member)"
                                                class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition"
                                                title="Edit"
                                            >
                                                ✏️
                                            </button>
                                            <button
                                                @click="confirmDelete(member)"
                                                class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                                                title="Hapus"
                                            >
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!members.data || members.data.length === 0">
                                    <td colspan="6" class="p-8 text-center text-xs text-gray-400">
                                        Tidak ada data yang sesuai dalam tab "{{ activeTab }}".
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="members.links && members.links.length > 3" class="p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm flex items-center justify-between">
                    <span class="text-xs text-gray-500">Menampilkan {{ members.from || 0 }} - {{ members.to || 0 }} dari total {{ members.total }} anggota</span>
                    <div class="flex items-center space-x-1">
                        <Link
                            v-for="(link, i) in members.links"
                            :key="i"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                link.active
                                    ? 'bg-brand-500 text-white font-bold'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200',
                                'px-3 py-1.5 rounded-lg text-xs transition'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= DETAIL MODAL ================= -->
        <div v-if="isDetailModalOpen && activeMemberDetail" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 backdrop-blur-sm" @click.self="isDetailModalOpen = false">
            <div class="bg-white dark:bg-gray-900 w-full max-w-lg rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[85vh] my-auto">
                <!-- Header -->
                <div class="p-6 bg-gradient-to-r from-brand-500/10 via-amber-500/5 to-transparent border-b border-gray-100 dark:border-gray-800 flex items-start justify-between shrink-0">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-500 to-amber-500 text-white font-black text-2xl flex items-center justify-center shadow-md overflow-hidden shrink-0">
                            <img v-if="activeMemberDetail.photo_path" :src="activeMemberDetail.photo_path" class="w-full h-full object-cover" />
                            <span v-else>{{ activeMemberDetail.name.charAt(0) }}</span>
                        </div>
                        <div class="space-y-1">
                            <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getTierBadgeClass(activeMemberDetail.tier)]">
                                {{ activeMemberDetail.tier }}
                            </span>
                            <h2 class="text-lg font-extrabold text-gray-800 dark:text-gray-100">{{ activeMemberDetail.name }}</h2>
                            <p class="text-xs font-semibold text-brand-600 dark:text-brand-400">{{ activeMemberDetail.position }}</p>
                        </div>
                    </div>
                    <button @click="isDetailModalOpen = false" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        ✕
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto space-y-4 flex-1 text-sm scrollbar-thin">
                    <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 text-xs">
                        <div>
                            <span class="text-gray-400 block font-semibold">No. Induk Anggota</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ activeMemberDetail.member_number || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-semibold">Masa Jabatan</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ activeMemberDetail.period || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-semibold">Bidang / Divisi</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ activeMemberDetail.division || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-semibold">Status Keanggotaan</span>
                            <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold inline-block border mt-0.5', getStatusBadgeClass(activeMemberDetail.status)]">
                                {{ activeMemberDetail.status }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <span class="text-gray-400">📞 No. Telepon / WA</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ activeMemberDetail.phone || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <span class="text-gray-400">✉️ Alamat Email</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ activeMemberDetail.email || '-' }}</span>
                        </div>
                    </div>

                    <div v-if="activeMemberDetail.address" class="text-xs">
                        <span class="font-bold text-gray-400 uppercase tracking-wider block mb-1">Alamat Domisili</span>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                            {{ activeMemberDetail.address }}
                        </div>
                    </div>

                    <div v-if="activeMemberDetail.notes" class="text-xs">
                        <span class="font-bold text-gray-400 uppercase tracking-wider block mb-1">Catatan / Profil Singkat</span>
                        <div class="p-3 rounded-xl bg-brand-50/30 dark:bg-brand-950/20 border border-brand-100 dark:border-brand-900/30 text-gray-700 dark:text-gray-300 italic">
                            {{ activeMemberDetail.notes }}
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end space-x-2 shrink-0">
                    <button
                        @click="isDetailModalOpen = false; openEditModal(activeMemberDetail)"
                        class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 font-bold text-xs hover:bg-amber-100 transition"
                    >
                        Edit Data
                    </button>
                    <button
                        @click="isDetailModalOpen = false"
                        class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= FORM SLIDE-OVER MODAL (TAMBAH / EDIT) ================= -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isFormModalOpen" class="fixed inset-0 z-50 overflow-hidden">
                <div @click="isFormModalOpen = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="fixed inset-y-0 right-0 max-w-full flex pl-10 z-10">
                    <form @submit.prevent="submitForm" class="w-screen max-w-xl bg-white dark:bg-gray-900 shadow-2xl border-l border-gray-100 dark:border-gray-800 flex flex-col h-full overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-5 bg-gradient-to-r from-brand-500 via-amber-500 to-orange-500 text-white flex items-center justify-between shrink-0 shadow-md">
                            <div>
                                <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-semibold text-white tracking-wide uppercase">Formulir Keorganisasian</span>
                                <h2 class="text-xl font-extrabold mt-0.5">{{ editingMember ? 'Edit Data Pengurus / Anggota' : 'Tambah Pengurus / Anggota Baru' }}</h2>
                            </div>
                            <button
                                type="button"
                                @click="isFormModalOpen = false"
                                class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition focus:outline-none"
                            >
                                ✕
                            </button>
                        </div>

                        <!-- Body (Scrollable) -->
                        <div class="p-6 overflow-y-auto flex-1 space-y-4 text-sm scrollbar-thin">
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="Contoh: H. Ahmad Dahlan, S.E."
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                />
                                <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1 block">{{ form.errors.name }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tingkatan (Tier) <span class="text-rose-500">*</span></label>
                                    <select
                                        v-model="form.tier"
                                        required
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                    >
                                        <option value="Dewan Pembina">Dewan Pembina</option>
                                        <option value="Dewan Pengawas">Dewan Pengawas</option>
                                        <option value="Pengurus">Pengurus Harian</option>
                                        <option value="Anggota">Anggota / Relawan</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Jabatan <span class="text-rose-500">*</span></label>
                                    <input
                                        v-model="form.position"
                                        type="text"
                                        required
                                        placeholder="Contoh: Ketua Umum / Kabid Rescue"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">No. Induk Anggota (NIKA)</label>
                                    <input
                                        v-model="form.member_number"
                                        type="text"
                                        placeholder="Kosongkan untuk otomatisasi"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                    />
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Bidang / Divisi</label>
                                    <input
                                        v-model="form.division"
                                        type="text"
                                        placeholder="Contoh: Tim Rescuer / Keuangan"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Masa Jabatan / Periode</label>
                                    <input
                                        v-model="form.period"
                                        type="text"
                                        required
                                        placeholder="Contoh: 2024 - 2029"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                    />
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Status Keanggotaan</label>
                                    <select
                                        v-model="form.status"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Demisioner">Demisioner</option>
                                        <option value="Tidak Aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">No. Telepon / WhatsApp</label>
                                    <input
                                        v-model="form.phone"
                                        type="text"
                                        placeholder="0812xxxxxxxx"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                    />
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="nama@mkt-charity.org"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Domisili</label>
                                <textarea
                                    v-model="form.address"
                                    rows="2"
                                    placeholder="Alamat lengkap..."
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan / Bio Singkat</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="2"
                                    placeholder="Keterangan tambahan..."
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Unggah Foto Profil</label>
                                <input
                                    type="file"
                                    @change="(e) => form.photo = e.target.files[0]"
                                    accept="image/*"
                                    class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition"
                                />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50/90 dark:bg-gray-800/90 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end space-x-3 shrink-0">
                            <button
                                type="button"
                                @click="isFormModalOpen = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold hover:bg-gray-100 transition"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
