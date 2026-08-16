<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    logistics: Object,
    transactions: Object,
    stats: {
        type: Object,
        default: () => ({
            total_items: 0,
            low_stock_items: 0,
            total_received: 0,
            total_distributed: 0,
        })
    },
    filters: Object,
});

// Active Tab state: 'inventory' (Gudang Persediaan) | 'history' (History Penyaluran & Penerimaan)
const activeTab = ref(props.filters.tab || 'inventory');

const setActiveTab = (tab) => {
    activeTab.value = tab;
    // update URL parameter without full page refresh reload
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url);
};

// Filters for Logistics
const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');

// Filters for Transactions
const transactionType = ref(props.filters.transaction_type || '');

const handleFilterLogistics = () => {
    router.get(route('logistics.index'), {
        tab: activeTab.value,
        search: search.value,
        category: category.value,
        transaction_type: transactionType.value
    }, { preserveState: true, replace: true });
};

const handleFilterTransactions = () => {
    router.get(route('logistics.index'), {
        tab: activeTab.value,
        search: search.value,
        category: category.value,
        transaction_type: transactionType.value
    }, { preserveState: true, replace: true });
};

// Reset Filters
const resetLogisticsFilter = () => {
    search.value = '';
    category.value = '';
    handleFilterLogistics();
};

const resetTxFilter = () => {
    transactionType.value = '';
    handleFilterTransactions();
};

// Modals
const isItemModalOpen = ref(false);
const editingItem = ref(null);
const itemForm = useForm({
    item_name: '',
    category: 'Makanan',
    quantity: 0,
    unit: 'Pcs',
    description: ''
});

const isTxModalOpen = ref(false);
const txForm = useForm({
    logistic_id: '',
    type: 'Masuk', // Masuk, Keluar
    quantity: 1,
    transaction_date: new Date().toISOString().split('T')[0],
    recipient_or_donor: '',
    notes: ''
});

// Item actions
const openAddItem = () => {
    editingItem.value = null;
    itemForm.reset();
    itemForm.clearErrors();
    isItemModalOpen.value = true;
};

const openEditItem = (item) => {
    editingItem.value = item;
    itemForm.item_name = item.item_name;
    itemForm.category = item.category;
    itemForm.quantity = item.quantity;
    itemForm.unit = item.unit;
    itemForm.description = item.description;
    itemForm.clearErrors();
    isItemModalOpen.value = true;
};

const submitItem = () => {
    if (editingItem.value) {
        itemForm.patch(route('logistics.items.update', editingItem.value.id), {
            onSuccess: () => isItemModalOpen.value = false
        });
    } else {
        itemForm.post(route('logistics.items.store'), {
            onSuccess: () => isItemModalOpen.value = false
        });
    }
};

// Transaction actions
const openAddTx = (item = null) => {
    txForm.reset();
    txForm.transaction_date = new Date().toISOString().split('T')[0];
    if (item) {
        txForm.logistic_id = item.id;
    } else if (props.logistics.data.length > 0) {
        txForm.logistic_id = props.logistics.data[0].id;
    }
    txForm.clearErrors();
    isTxModalOpen.value = true;
};

const submitTx = () => {
    txForm.post(route('logistics.transactions.store'), {
        onSuccess: () => isTxModalOpen.value = false,
        onError: (errs) => {
            if (errs.message) alert(errs.message);
        }
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};

// Category badge color mapper
const getCategoryBadgeClass = (cat) => {
    switch (cat) {
        case 'Makanan':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/50';
        case 'Obat-obatan':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50';
        case 'Pakaian':
            return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/50';
        case 'Rescue Equipment':
            return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/50';
        case 'Huntara':
            return 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200/60 dark:border-purple-800/50';
        default:
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700';
    }
};

// Stock status helper
const getStockStatus = (qty) => {
    if (qty <= 0) {
        return { label: 'Habis', class: 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800' };
    } else if (qty < 10) {
        return { label: 'Stok Menipis', class: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800' };
    }
    return { label: 'Tersedia', class: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' };
};
</script>

<template>
    <Head title="Logistik Bencana" />

    <AuthenticatedLayout>
        <template #header>
            <span>Logistik Bencana</span>
        </template>

        <div class="space-y-6">
            <!-- Dedicated Page Header Section -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Manajemen Logistik Bencana</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pusat Pengelolaan Stok Gudang & History Penyaluran/Penerimaan Logistik</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="openAddItem"
                        class="px-3.5 py-2 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center space-x-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>+ Tambah Barang</span>
                    </button>
                    <button
                        @click="openAddTx()"
                        class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 active:scale-95 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center space-x-1.5"
                    >
                        <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span>Catat Pergerakan</span>
                    </button>
                </div>
            </div>
            <!-- 1. Statistics Cards Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Jenis Barang -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Jenis Logistik</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ stats.total_items }} <span class="text-xs font-semibold text-gray-400">Item</span></h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/30 text-brand-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center text-[11px] text-gray-400">
                        <span class="font-medium text-brand-600 dark:text-brand-400">Terdaftar di Sistem</span>
                    </div>
                </div>

                <!-- Card 2: Stok Menipis Kritis -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Stok Kritis / Menipis</p>
                            <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ stats.low_stock_items }} <span class="text-xs font-semibold text-gray-400">Item</span></h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/30 text-amber-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center text-[11px] text-amber-600 dark:text-amber-400 font-medium">
                        <span>Stok di bawah 10 unit</span>
                    </div>
                </div>

                <!-- Card 3: Total Penerimaan (Masuk) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Penerimaan (Masuk)</p>
                            <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">+{{ stats.total_received }} <span class="text-xs font-semibold text-gray-400">Unit</span></h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">
                        <span>Akumulasi barang masuk posko</span>
                    </div>
                </div>

                <!-- Card 4: Total Penyaluran (Keluar) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Penyaluran (Keluar)</p>
                            <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">-{{ stats.total_distributed }} <span class="text-xs font-semibold text-gray-400">Unit</span></h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/30 text-rose-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center text-[11px] text-rose-600 dark:text-rose-400 font-medium">
                        <span>Disalurkan ke warga & korban</span>
                    </div>
                </div>
            </div>

            <!-- 2. Modern Tab Navigation Bar -->
            <div class="bg-gray-100/80 dark:bg-gray-800/60 p-1.5 rounded-2xl flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 border border-gray-200/60 dark:border-gray-700/60">
                <div class="flex items-center space-x-1.5">
                    <!-- Tab 1: Gudang Persediaan Logistik -->
                    <button
                        @click="setActiveTab('inventory')"
                        :class="[
                            activeTab === 'inventory'
                                ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                            'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center space-x-2'
                        ]"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>Gudang Persediaan Logistik</span>
                        <span
                            :class="[
                                activeTab === 'inventory' ? 'bg-brand-100 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
                                'px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors'
                            ]"
                        >
                            {{ logistics.total }}
                        </span>
                    </button>

                    <!-- Tab 2: History Penyaluran dan Penerimaan -->
                    <button
                        @click="setActiveTab('history')"
                        :class="[
                            activeTab === 'history'
                                ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                            'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center space-x-2'
                        ]"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span>History Penyaluran & Penerimaan</span>
                        <span
                            :class="[
                                activeTab === 'history' ? 'bg-brand-100 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
                                'px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors'
                            ]"
                        >
                            {{ transactions.total }}
                        </span>
                    </button>
                </div>

                <div class="px-2 text-xs text-gray-400 dark:text-gray-500 font-medium self-center hidden md:block">
                    Tab Aktif: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ activeTab === 'inventory' ? 'Gudang Persediaan' : 'Riwayat Pergerakan' }}</span>
                </div>
            </div>

            <!-- 3. TAB 1: Gudang Persediaan Logistik -->
            <div v-show="activeTab === 'inventory'" class="space-y-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                    <!-- Filter Toolbar -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-xl bg-brand-50 dark:bg-brand-950/30 text-brand-500 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-gray-900 dark:text-white">Gudang Persediaan Logistik</h3>
                                <p class="text-xs text-gray-400">Daftar stok persediaan logistik darurat bencana</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari nama barang..."
                                    class="w-full sm:w-64 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-3 py-2 text-xs focus:border-brand-500 focus:outline-none transition-all"
                                    @input="handleFilterLogistics"
                                />
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <select
                                v-model="category"
                                class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none transition-all"
                                @change="handleFilterLogistics"
                            >
                                <option value="">Semua Kategori</option>
                                <option value="Makanan">Makanan & Minuman</option>
                                <option value="Obat-obatan">Obat-obatan / Medis</option>
                                <option value="Pakaian">Pakaian & Selimut</option>
                                <option value="Rescue Equipment">Rescue Equipment</option>
                                <option value="Huntara">Hunian Sementara (Huntara)</option>
                                <option value="Lainnya">Lain-lain</option>
                            </select>

                            <button
                                v-if="search || category"
                                @click="resetLogisticsFilter"
                                class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-xl transition-colors"
                                title="Reset Filter"
                            >
                                Reset
                            </button>

                            <button
                                @click="openAddItem"
                                class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20 whitespace-nowrap flex items-center justify-center space-x-1"
                            >
                                <span>+ Barang Baru</span>
                            </button>
                        </div>
                    </div>

                    <!-- Logistics Inventory Table -->
                    <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-gray-50/80 dark:bg-gray-800/40 text-gray-400 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800 text-xs">
                                    <th class="py-3 px-4 font-semibold">Nama Barang Logistik</th>
                                    <th class="py-3 px-4 font-semibold">Kategori</th>
                                    <th class="py-3 px-4 font-semibold text-center">Jumlah Stok</th>
                                    <th class="py-3 px-4 font-semibold">Status Stok</th>
                                    <th class="py-3 px-4 font-semibold">Deskripsi / Catatan</th>
                                    <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="item in logistics.data"
                                    :key="item.id"
                                    class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors text-gray-700 dark:text-gray-300"
                                >
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                                            <span>{{ item.item_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span :class="['text-xs px-2.5 py-1 rounded-lg font-medium inline-block', getCategoryBadgeClass(item.category)]">
                                            {{ item.category }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-base font-black text-gray-900 dark:text-white">{{ item.quantity }}</span>
                                        <span class="text-xs text-gray-400 ml-1 font-normal">{{ item.unit }}</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span :class="['text-[11px] px-2.5 py-1 rounded-full font-bold inline-flex items-center space-x-1', getStockStatus(item.quantity).class]">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="item.quantity <= 0 ? 'bg-rose-500 animate-pulse' : item.quantity < 10 ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                                            <span>{{ getStockStatus(item.quantity).label }}</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-gray-400 max-w-[220px] truncate" :title="item.description">
                                        {{ item.description || '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button
                                                @click="openAddTx(item)"
                                                class="px-2.5 py-1 text-[11px] font-bold bg-brand-50 text-brand-700 hover:bg-brand-500 hover:text-white dark:bg-brand-950/30 dark:text-brand-400 dark:hover:bg-brand-500 dark:hover:text-white rounded-lg transition-all focus:outline-none flex items-center space-x-1"
                                                title="Catat Penyaluran / Penerimaan Barang Ini"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                </svg>
                                                <span>In / Out</span>
                                            </button>
                                            <button
                                                @click="openEditItem(item)"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-brand-500 hover:bg-brand-50/50 dark:hover:bg-brand-950/20 transition-all focus:outline-none"
                                                title="Edit Barang"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="logistics.data.length === 0">
                                    <td colspan="6" class="py-12 text-center text-gray-400 italic">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                            <p class="text-sm font-medium text-gray-500">Tidak ada persediaan logistik ditemukan.</p>
                                            <p class="text-xs text-gray-400">Silakan ubah filter pencarian atau tambah barang baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Logistics Pagination -->
                    <div v-if="logistics.links.length > 3" class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <div class="text-xs text-gray-400">Menampilkan stok gudang (Total: {{ logistics.total }} barang)</div>
                        <div class="flex items-center space-x-1">
                            <template v-for="(link, key) in logistics.links" :key="key">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        link.active ? 'bg-brand-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800',
                                        'px-3 py-1.5 text-xs rounded-xl transition-colors'
                                    ]"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. TAB 2: History Penyaluran dan Penerimaan -->
            <div v-show="activeTab === 'history'" class="space-y-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                    <!-- Filter Toolbar -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-xl bg-brand-50 dark:bg-brand-950/30 text-brand-500 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-gray-900 dark:text-white">History Penyaluran & Penerimaan</h3>
                                <p class="text-xs text-gray-400">Jurnal audit riwayat keluar-masuk barang logistik ke posko & penerima</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <select
                                v-model="transactionType"
                                class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none transition-all"
                                @change="handleFilterTransactions"
                            >
                                <option value="">Semua Pergerakan Logistik</option>
                                <option value="Masuk">Masuk (Penerimaan / Donasi Masuk)</option>
                                <option value="Keluar">Keluar (Penyaluran / Distribusi)</option>
                            </select>

                            <button
                                v-if="transactionType"
                                @click="resetTxFilter"
                                class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-xl transition-colors"
                            >
                                Reset
                            </button>

                            <button
                                @click="openAddTx()"
                                class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20 whitespace-nowrap flex items-center justify-center space-x-1"
                            >
                                <span>+ Catat Transaksi Baru</span>
                            </button>
                        </div>
                    </div>

                    <!-- Transactions History Table -->
                    <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-gray-50/80 dark:bg-gray-800/40 text-gray-400 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800 text-xs">
                                    <th class="py-3 px-4 font-semibold">Tanggal Transaksi</th>
                                    <th class="py-3 px-4 font-semibold">Barang Logistik</th>
                                    <th class="py-3 px-4 font-semibold">Jenis Pergerakan</th>
                                    <th class="py-3 px-4 font-semibold text-center">Jumlah Vol.</th>
                                    <th class="py-3 px-4 font-semibold">Pihak / Posko / Donor</th>
                                    <th class="py-3 px-4 font-semibold">Catatan / Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="tx in transactions.data"
                                    :key="tx.id"
                                    class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors text-gray-700 dark:text-gray-300"
                                >
                                    <td class="py-3.5 px-4 text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ formatDate(tx.transaction_date) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                                        {{ tx.logistic ? tx.logistic.item_name : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                tx.type === 'Masuk'
                                                    ? 'text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50'
                                                    : 'text-rose-700 bg-rose-50 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/50',
                                                'px-2.5 py-1 rounded-full text-xs font-bold inline-flex items-center space-x-1'
                                            ]"
                                        >
                                            <span v-if="tx.type === 'Masuk'" class="text-xs">↓</span>
                                            <span v-else class="text-xs">↑</span>
                                            <span>{{ tx.type === 'Masuk' ? 'Penerimaan (Masuk)' : 'Penyaluran (Keluar)' }}</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span :class="[tx.type === 'Masuk' ? 'text-emerald-600 dark:text-emerald-400 font-black' : 'text-rose-600 dark:text-rose-400 font-black', 'text-sm']">
                                            {{ tx.type === 'Masuk' ? '+' : '-' }}{{ tx.quantity }}
                                        </span>
                                        <span class="text-xs text-gray-400 ml-1 font-normal">{{ tx.logistic ? tx.logistic.unit : '' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-semibold text-gray-800 dark:text-gray-200">
                                        {{ tx.recipient_or_donor || '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-gray-400 max-w-[220px] truncate" :title="tx.notes">
                                        {{ tx.notes || '-' }}
                                    </td>
                                </tr>
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="6" class="py-12 text-center text-gray-400 italic">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                            </svg>
                                            <p class="text-sm font-medium text-gray-500">Tidak ada riwayat transaksi logistik ditemukan.</p>
                                            <p class="text-xs text-gray-400">Silakan catat pergerakan logistik baru atau ubah filter pergerakan.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Transactions Pagination -->
                    <div v-if="transactions.links.length > 3" class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <div class="text-xs text-gray-400">Menampilkan log riwayat (Total: {{ transactions.total }} transaksi)</div>
                        <div class="flex items-center space-x-1">
                            <template v-for="(link, key) in transactions.links" :key="key">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        link.active ? 'bg-brand-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800',
                                        'px-3 py-1.5 text-xs rounded-xl transition-colors'
                                    ]"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal A: Add/Edit Item -->
        <div v-if="isItemModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="h-14 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-brand-50/30 dark:bg-brand-950/10">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="font-bold text-gray-900 dark:text-white text-sm">
                            {{ editingItem ? 'Edit Data Barang Logistik' : 'Tambah Barang Logistik Baru' }}
                        </span>
                    </div>
                    <button @click="isItemModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitItem" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Barang Logistik</label>
                        <input v-model="itemForm.item_name" type="text" placeholder="Contoh: Beras Premium 5kg, Air Mineral Box..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Kategori</label>
                            <select v-model="itemForm.category" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required>
                                <option value="Makanan">Makanan & Minuman</option>
                                <option value="Obat-obatan">Obat-obatan / Medis</option>
                                <option value="Pakaian">Pakaian & Selimut</option>
                                <option value="Rescue Equipment">Rescue Equipment</option>
                                <option value="Huntara">Hunian Sementara (Huntara)</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Satuan</label>
                            <input v-model="itemForm.unit" type="text" placeholder="Pcs, Box, Kg, Liter..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>
                    <div v-if="!editingItem">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Stok Awal Awal (Unit)</label>
                        <input v-model="itemForm.quantity" type="number" min="0" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Deskripsi / Spesifikasi Barang</label>
                        <textarea v-model="itemForm.description" rows="2.5" placeholder="Keterangan tambahan barang, kondisi, tanggal kedaluwarsa jika ada..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isItemModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 rounded-xl text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-800">Batal</button>
                        <button type="submit" :disabled="itemForm.processing" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-semibold shadow-md">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal B: Log Transaction (Penyaluran / Penerimaan) -->
        <div v-if="isTxModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="h-14 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-brand-50/30 dark:bg-brand-950/10">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span class="font-bold text-gray-900 dark:text-white text-sm">
                            Catat Pergerakan Logistik
                        </span>
                    </div>
                    <button @click="isTxModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitTx" class="p-6 space-y-4">
                    <!-- Item Selection -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pilih Barang Logistik</label>
                        <select v-model="txForm.logistic_id" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required>
                            <option value="">Pilih Barang...</option>
                            <option v-for="item in logistics.data" :key="item.id" :value="item.id">
                                {{ item.item_name }} (Sisa Stok: {{ item.quantity }} {{ item.unit }})
                            </option>
                        </select>
                    </div>

                    <!-- Movement Type & Quantity -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jenis Pergerakan</label>
                            <select v-model="txForm.type" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required>
                                <option value="Masuk">Masuk (Penerimaan)</option>
                                <option value="Keluar">Keluar (Penyaluran)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jumlah Volume</label>
                            <input v-model="txForm.quantity" type="number" min="1" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal Transaksi</label>
                            <input v-model="txForm.transaction_date" type="date" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">{{ txForm.type === 'Masuk' ? 'Penyumbang / Sumber' : 'Penerima / Posko Tujuan' }}</label>
                            <input v-model="txForm.recipient_or_donor" type="text" :placeholder="txForm.type === 'Masuk' ? 'Donor / Instansi...' : 'Posko Bencana / Warga...'" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none" />
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Catatan / Keterangan</label>
                        <textarea v-model="txForm.notes" rows="2.5" placeholder="Lokasi penyaluran, armada pengirim, nomor berita acara..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isTxModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 rounded-xl text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-800">Batal</button>
                        <button type="submit" :disabled="txForm.processing" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-semibold shadow-md">Simpan Pergerakan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
