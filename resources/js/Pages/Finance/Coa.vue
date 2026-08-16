<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { showSuccessToast, showErrorToast } from '@/Utils/toast.js';

const props = defineProps({
    accounts: Array,
    stats: Object,
    accountTypes: Array,
    filters: Object,
});

// Filter State
const search = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || 'Semua');
const selectedStatus = ref(props.filters.status || 'Semua');

const handleFilter = () => {
    router.get(route('finance.coa.index'), {
        search: search.value,
        type: selectedType.value,
        status: selectedStatus.value,
    }, { preserveState: true, replace: true });
};

const clearFilter = () => {
    search.value = '';
    selectedType.value = 'Semua';
    selectedStatus.value = 'Semua';
    handleFilter();
};

// Modal Control
const isModalOpen = ref(false);
const editingAccount = ref(null);

const form = useForm({
    id: null,
    code: '',
    name: '',
    type: 'Asset',
    normal_balance: 'Debit',
    status: 'Aktif',
    description: '',
});

const openAddModal = () => {
    editingAccount.value = null;
    form.reset();
    form.clearErrors();
    form.type = 'Asset';
    form.normal_balance = 'Debit';
    form.status = 'Aktif';
    isModalOpen.value = true;
};

const openEditModal = (account) => {
    editingAccount.value = account;
    form.clearErrors();
    form.id = account.id;
    form.code = account.code || '';
    form.name = account.name || '';
    form.type = account.type || 'Asset';
    form.normal_balance = account.normal_balance || (['Asset', 'Expense'].includes(account.type) ? 'Debit' : 'Credit');
    form.status = account.status || 'Aktif';
    form.description = account.description || '';
    isModalOpen.value = true;
};

const autoSetNormalBalance = () => {
    if (['Asset', 'Expense'].includes(form.type)) {
        form.normal_balance = 'Debit';
    } else {
        form.normal_balance = 'Credit';
    }
};

const submitForm = () => {
    if (editingAccount.value) {
        form.patch(route('finance.coa.update', editingAccount.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                showSuccessToast('Kode Akun (COA) berhasil diperbarui!');
            },
            onError: (errs) => showErrorToast('Gagal memperbarui Kode Akun.')
        });
    } else {
        form.post(route('finance.coa.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
                showSuccessToast('Kode Akun (COA) baru berhasil ditambahkan!');
            },
            onError: (errs) => showErrorToast('Gagal menambahkan Kode Akun.')
        });
    }
};

const deleteAccount = (account) => {
    if (confirm(`Apakah Anda yakin ingin menghapus Kode Akun "${account.code} - ${account.name}"?`)) {
        router.delete(route('finance.coa.destroy', account.id), {
            onSuccess: () => showSuccessToast('Kode Akun berhasil dihapus.'),
            onError: (errs) => {
                const msg = errs.delete || 'Gagal menghapus akun.';
                showErrorToast(msg);
            }
        });
    }
};

const getTypeBadgeStyle = (type) => {
    switch (type) {
        case 'Asset': return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
        case 'Liability': return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800';
        case 'Equity': return 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border-purple-200 dark:border-purple-800';
        case 'Revenue': return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-200 dark:border-blue-800';
        case 'Expense': return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-800';
        default: return 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200';
    }
};
</script>

<template>
    <Head title="Pengaturan COA (Chart of Accounts)" />

    <AuthenticatedLayout>
        <template #header>
            <span>Keuangan & Laporan</span>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Dedicated Page Header -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-500 to-amber-400 text-white flex items-center justify-center text-2xl shadow-lg shadow-brand-500/20 shrink-0">
                        📊
                    </div>
                    <div>
                        <div class="inline-flex items-center space-x-2 px-3 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400 border border-brand-200/50 mb-1">
                            <span>Akuntansi Yayasan MKT</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Pengaturan Chart of Accounts (COA)</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola struktur klasifikasi kode akun keuangan (Aktiva, Kewajiban, Modal, Pendapatan, Beban)</p>
                    </div>
                </div>

                <button
                    v-if="['webmaster', 'administrator', 'finance'].includes($page.props.auth.user.role)"
                    @click="openAddModal"
                    class="px-5 py-3 bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold rounded-2xl shadow-lg shadow-brand-500/20 transition-all flex items-center justify-center space-x-2 shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Tambah Kode Akun (COA)</span>
                </button>
                <span v-else class="px-4 py-2 bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 text-xs font-semibold rounded-2xl border border-gray-200 dark:border-gray-700 shrink-0">
                    🔒 Read-Only (Hanya Lihat)
                </span>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm text-center">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Total Akun</span>
                    <span class="text-2xl font-black text-gray-900 dark:text-white mt-1 block">{{ stats.total_accounts || 0 }}</span>
                </div>
                <div class="bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/40 rounded-2xl p-4 shadow-sm text-center">
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase tracking-wider block">Aktiva (Asset)</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ stats.total_asset || 0 }}</span>
                </div>
                <div class="bg-rose-50/50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-800/40 rounded-2xl p-4 shadow-sm text-center">
                    <span class="text-[10px] text-rose-700 dark:text-rose-400 font-bold uppercase tracking-wider block">Kewajiban</span>
                    <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block">{{ stats.total_liability || 0 }}</span>
                </div>
                <div class="bg-purple-50/50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-800/40 rounded-2xl p-4 shadow-sm text-center">
                    <span class="text-[10px] text-purple-700 dark:text-purple-400 font-bold uppercase tracking-wider block">Modal (Equity)</span>
                    <span class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1 block">{{ stats.total_equity || 0 }}</span>
                </div>
                <div class="bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-800/40 rounded-2xl p-4 shadow-sm text-center">
                    <span class="text-[10px] text-blue-700 dark:text-blue-400 font-bold uppercase tracking-wider block">Pendapatan</span>
                    <span class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 block">{{ stats.total_revenue || 0 }}</span>
                </div>
                <div class="bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-800/40 rounded-2xl p-4 shadow-sm text-center">
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold uppercase tracking-wider block">Beban Bencana</span>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ stats.total_expense || 0 }}</span>
                </div>
            </div>

            <!-- Search & Type Filter Bar -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-1.5 overflow-x-auto scrollbar-thin py-1 w-full md:w-auto">
                    <button
                        v-for="t in accountTypes"
                        :key="t"
                        @click="selectedType = t; handleFilter();"
                        :class="[
                            selectedType === t
                                ? 'bg-brand-500 text-white font-bold shadow-md shadow-brand-500/20'
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 font-medium',
                            'px-4 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all'
                        ]"
                    >
                        {{ t === 'Semua' ? 'Semua Tipe Akun' : t }}
                    </button>
                </div>

                <div class="flex items-center space-x-3 w-full md:w-auto">
                    <!-- Status Filter -->
                    <select
                        v-model="selectedStatus"
                        @change="handleFilter"
                        class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"
                    >
                        <option value="Semua">Status: Semua</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-64">
                        <input
                            v-model="search"
                            @keyup.enter="handleFilter"
                            type="text"
                            placeholder="Cari kode / nama akun..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-4 py-2 text-xs focus:border-brand-500 focus:outline-none"
                        />
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- COA Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50/80 dark:bg-gray-800/60 uppercase text-[10px] font-black text-gray-400 tracking-wider border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-6 py-4">Kode Akun</th>
                                <th class="px-6 py-4">Nama Akun COA</th>
                                <th class="px-6 py-4">Tipe Akun</th>
                                <th class="px-6 py-4 text-center">Saldo Normal</th>
                                <th class="px-6 py-4">Keterangan / Penggunaan</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="a in accounts" :key="a.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-4 font-mono font-black text-brand-600 dark:text-brand-400">
                                    {{ a.code }}
                                </td>
                                <td class="px-6 py-4">
                                    <h4 class="font-bold text-gray-900 dark:text-white">{{ a.name }}</h4>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border', getTypeBadgeStyle(a.type)]">
                                        {{ a.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="[
                                        a.normal_balance === 'Debit'
                                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-200'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200',
                                        'px-2 py-0.5 rounded-md text-[10px] font-bold border'
                                    ]">
                                        {{ a.normal_balance || 'Debit' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1">
                                        {{ a.description || 'Akun standar jurnal umum Yayasan MKT' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="[
                                        a.status === 'Aktif'
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-300',
                                        'px-2.5 py-0.5 rounded-full text-[10px] font-bold border'
                                    ]">
                                        {{ a.status || 'Aktif' }}
                                    </span>
                                </td>
                                <td v-if="['webmaster', 'administrator', 'finance'].includes($page.props.auth.user.role)" class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button
                                            @click="openEditModal(a)"
                                            class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-lg transition-colors"
                                            title="Edit Akun"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button
                                            @click="deleteAccount(a)"
                                            class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors"
                                            title="Hapus Akun"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                                <td v-else class="px-6 py-4 text-center text-xs text-gray-400 font-italic">
                                    Read Only
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FORM MODAL TAMBAH / EDIT KODE AKUN -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl my-8">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                        <span>📊</span>
                        <span>{{ editingAccount ? 'Edit Kode Akun (COA)' : 'Tambah Kode Akun Baru' }}</span>
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Kode Akun (COA Code)</label>
                            <input
                                v-model="form.code"
                                type="text"
                                placeholder="Contoh: 1005"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none font-mono"
                                required
                            />
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Tipe Klasifikasi Akun</label>
                            <select
                                v-model="form.type"
                                @change="autoSetNormalBalance"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none"
                            >
                                <option value="Asset">Asset (Aktiva / Kas / Bank / Peralatan)</option>
                                <option value="Liability">Liability (Kewajiban / Hutang)</option>
                                <option value="Equity">Equity (Modal / Ekuitas Yayasan)</option>
                                <option value="Revenue">Revenue (Pendapatan Donasi / CSR)</option>
                                <option value="Expense">Expense (Beban Operasional & Bencana)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Nama Akun Keuangan</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: Kas Kecil Field Rescue Bencana"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Saldo Normal Akun</label>
                            <select
                                v-model="form.normal_balance"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none"
                            >
                                <option value="Debit">Debit (+ Aktiva / Beban)</option>
                                <option value="Credit">Credit (+ Kewajiban / Modal / Pendapatan)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Status Penggunaan</label>
                            <select
                                v-model="form.status"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none"
                            >
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Keterangan / Penggunaan Akun</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Catatan fungsi penggunaan akun dalam jurnal transaksi..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 focus:border-brand-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="pt-4 flex justify-end space-x-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border rounded-xl text-gray-600 dark:text-gray-300">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-gradient-to-r from-brand-500 to-amber-500 text-white font-bold rounded-xl shadow-md">Simpan Kode Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
