<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { showSuccessToast, showErrorToast } from '@/Utils/toast.js';
import FinancialPrintHeader from '@/Components/FinancialPrintHeader.vue';
import FinancialPrintSignatures from '@/Components/FinancialPrintSignatures.vue';
import FinancialPrintModal from '@/Components/FinancialPrintModal.vue';

const props = defineProps({
    entries: Object,
    accounts: Array,
    filters: Object,
});

// Filters
const search = ref(props.filters.search || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const isSyncing = ref(false);
const isPrintModalOpen = ref(false);

const syncDonations = () => {
    isSyncing.value = true;
    router.post(route('finance.sync-donations'), {}, {
        preserveScroll: true,
        onFinish: () => {
            isSyncing.value = false;
            showSuccessToast('Transaksi donasi berhasil disinkronkan ke Jurnal & Laporan Keuangan.');
        }
    });
};

const handleFilter = () => {
    router.get(route('finance.journal.index'), {
        search: search.value,
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true, replace: true });
};

// Modal Control (Add & Edit)
const isModalOpen = ref(false);
const editingJournal = ref(null);

const form = useForm({
    id: null,
    entry_date: new Date().toISOString().split('T')[0],
    description: '',
    reference_number: '',
    items: [
        { account_id: '', type: 'Debit', amount: '' },
        { account_id: '', type: 'Credit', amount: '' }
    ]
});

const openAddModal = () => {
    editingJournal.value = null;
    form.reset();
    form.clearErrors();
    form.entry_date = new Date().toISOString().split('T')[0];
    form.items = [
        { account_id: '', type: 'Debit', amount: '' },
        { account_id: '', type: 'Credit', amount: '' }
    ];
    isModalOpen.value = true;
};

const openEditModal = (entry) => {
    editingJournal.value = entry;
    form.clearErrors();
    form.id = entry.id;
    form.entry_date = entry.entry_date ? entry.entry_date.split('T')[0] : new Date().toISOString().split('T')[0];
    form.description = entry.description || '';
    form.reference_number = entry.reference_number || '';
    form.items = (entry.items && entry.items.length > 0)
        ? entry.items.map(i => ({
            account_id: i.account_id,
            type: i.type,
            amount: parseFloat(i.amount)
        }))
        : [
            { account_id: '', type: 'Debit', amount: '' },
            { account_id: '', type: 'Credit', amount: '' }
        ];
    isModalOpen.value = true;
};

// Items helpers
const addJournalItem = () => {
    form.items.push({ account_id: '', type: 'Debit', amount: '' });
};

const removeJournalItem = (idx) => {
    if (form.items.length > 2) {
        form.items.splice(idx, 1);
    }
};

// Balanced validation sums for form
const totalDebit = computed(() => {
    return form.items
        .filter(i => i.type === 'Debit')
        .reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
});

const totalCredit = computed(() => {
    return form.items
        .filter(i => i.type === 'Credit')
        .reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
});

const isBalanced = computed(() => {
    return Math.abs(totalDebit.value - totalCredit.value) < 0.01 && totalDebit.value > 0;
});

// Grand Totals for current entries view
const grandTotalDebit = computed(() => {
    let sum = 0;
    if (props.entries && props.entries.data) {
        props.entries.data.forEach(e => {
            if (e.items) {
                e.items.forEach(i => {
                    if (i.type === 'Debit') sum += parseFloat(i.amount) || 0;
                });
            }
        });
    }
    return sum;
});

const grandTotalCredit = computed(() => {
    let sum = 0;
    if (props.entries && props.entries.data) {
        props.entries.data.forEach(e => {
            if (e.items) {
                e.items.forEach(i => {
                    if (i.type === 'Credit') sum += parseFloat(i.amount) || 0;
                });
            }
        });
    }
    return sum;
});

const submit = () => {
    if (!isBalanced.value) {
        showErrorToast('Jurnal tidak balance! Total Debit harus sama dengan total Credit.');
        return;
    }

    if (editingJournal.value) {
        form.patch(route('finance.journal.update', editingJournal.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                showSuccessToast('Jurnal transaksi berhasil diperbarui!');
            },
            onError: (errs) => showErrorToast('Gagal memperbarui jurnal.')
        });
    } else {
        form.post(route('finance.journal.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
                showSuccessToast('Jurnal transaksi berhasil disimpan!');
            },
            onError: (errs) => showErrorToast('Gagal menyimpan jurnal.')
        });
    }
};

const deleteJournal = (entry) => {
    if (confirm(`Apakah Anda yakin ingin menghapus jurnal "${entry.reference_number} - ${entry.description}"?`)) {
        router.delete(route('finance.journal.destroy', entry.id), {
            onSuccess: () => showSuccessToast('Jurnal transaksi berhasil dihapus.')
        });
    }
};

// CSV Export & Print
const exportCSV = () => {
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Tanggal,No. Referensi,Keterangan,Kode Akun,Nama Akun,Debit (IDR),Credit (IDR)\n";
    
    props.entries.data.forEach(entry => {
        const date = entry.entry_date ? entry.entry_date.split('T')[0] : '';
        const ref = `"${(entry.reference_number || '').replace(/"/g, '""')}"`;
        const desc = `"${(entry.description || '').replace(/"/g, '""')}"`;
        
        if (entry.items && entry.items.length > 0) {
            entry.items.forEach(item => {
                const code = `"${(item.account ? item.account.code : '').replace(/"/g, '""')}"`;
                const accountName = `"${(item.account ? item.account.name : '').replace(/"/g, '""')}"`;
                const debit = item.type === 'Debit' ? item.amount : 0;
                const credit = item.type === 'Credit' ? item.amount : 0;
                csvContent += `${date},${ref},${desc},${code},${accountName},${debit},${credit}\n`;
            });
        } else {
            csvContent += `${date},${ref},${desc},,,,\n`;
        }
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Jurnal_Keuangan_MKT_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const triggerPrint = () => {
    window.print();
};

const formatIDR = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Jurnal Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <span>Jurnal Keuangan</span>
        </template>

        <!-- MODERN AESTHETIC PRINT HEADER (Only Visible When Printing) -->
        <div class="hidden print:block mb-6">
            <FinancialPrintHeader 
                title="LAPORAN JURNAL TRANSAKSI KEUANGAN" 
                :period="startDate && endDate ? `${startDate} s/d ${endDate}` : 'Semua Transaksi Terdaftar'"
                doc-number="Double-Entry Verified"
            />
        </div>

        <!-- Dedicated Page Header Section (Hidden in print) -->
        <div class="print:hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Jurnal Keuangan & Transaksi</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pencatatan & Pratinjau Cetak Jurnal Double-Entry Filantropi</p>
                </div>
            </div>

            <!-- Action Buttons: Sync Donations, Print Preview & Add Journal -->
            <div class="flex items-center flex-wrap gap-2.5">
                <button
                    v-if="['webmaster', 'administrator', 'finance'].includes($page.props.auth.user.role)"
                    @click="syncDonations"
                    :disabled="isSyncing"
                    class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-2xs disabled:opacity-50"
                    title="Sinkronkan seluruh donasi masuk ke Jurnal Keuangan"
                >
                    <svg :class="['w-4 h-4', isSyncing ? 'animate-spin' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>{{ isSyncing ? 'Menyinkronkan...' : 'Sinkronkan Donasi' }}</span>
                </button>
                <button
                    @click="isPrintModalOpen = true"
                    class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-md shadow-amber-500/20"
                    title="Buka Pratinjau Cetak Mode Terang"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>Pratinjau Cetak</span>
                </button>
                <button
                    v-if="['webmaster', 'administrator', 'finance'].includes($page.props.auth.user.role)"
                    @click="openAddModal"
                    class="px-3.5 py-2 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center space-x-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Buat Jurnal Baru</span>
                </button>
                <span v-else class="px-3 py-1.5 bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 text-xs font-semibold rounded-xl border border-gray-200 dark:border-gray-700">
                    🔒 Read-Only (Hanya Lihat)
                </span>
            </div>
        </div>

        <!-- Filters (Hidden in Print) -->
        <div class="print:hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Deskripsi / Ref</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari deskripsi jurnal..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @input="handleFilter"
                        />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Dari Tanggal</label>
                        <input
                            v-model="startDate"
                            type="date"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @change="handleFilter"
                        />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Sampai Tanggal</label>
                        <input
                            v-model="endDate"
                            type="date"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @change="handleFilter"
                        />
                    </div>
                </div>
                <div class="shrink-0 flex items-center space-x-3">
                    <button
                        @click="router.get(route('finance.journal.index'))"
                        class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-semibold rounded-xl transition-all"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Journal Entries List (Printable Table) -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden print:border-black print:shadow-none print:rounded-none">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm print:text-xs">
                    <thead>
                        <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800 uppercase text-[10px] font-black tracking-wider print:text-black print:border-black print:bg-slate-100">
                            <th class="p-4 font-semibold">Tanggal / Ref</th>
                            <th class="p-4 font-semibold">Keterangan / Deskripsi</th>
                            <th class="p-4 font-semibold">Kode Akun & Nama Akun</th>
                            <th class="p-4 font-semibold text-right">Debit</th>
                            <th class="p-4 font-semibold text-right">Credit</th>
                            <th class="p-4 font-semibold text-right print:hidden">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 print:divide-gray-400">
                        <template v-for="entry in entries.data" :key="entry.id">
                            <!-- Entry Header Row -->
                            <tr class="bg-gray-50/40 dark:bg-gray-900/30 font-medium hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors print:bg-gray-100 print:text-black">
                                <td class="p-4 align-top print:p-2">
                                    <span class="block text-gray-900 dark:text-white font-bold print:text-black">{{ formatDate(entry.entry_date) }}</span>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="text-xs text-gray-400 font-mono print:text-gray-800 print:text-[9px]">{{ entry.reference_number }}</span>
                                        <span v-if="entry.reference_number && (entry.reference_number.startsWith('DON-') || entry.reference_number.startsWith('TX-'))" class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 text-[9px] font-extrabold uppercase print:border print:border-amber-400 print:text-black">
                                            Donasi
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 align-top text-gray-800 dark:text-gray-200 font-bold print:text-black print:p-2" colspan="3">
                                    {{ entry.description }}
                                </td>
                                <td class="print:hidden"></td>
                                <td v-if="['webmaster', 'administrator', 'finance'].includes($page.props.auth.user.role)" class="p-4 align-top text-right print:hidden">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <button
                                            @click="openEditModal(entry)"
                                            class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg text-xs font-bold transition-all"
                                            title="Edit Transaksi Jurnal"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteJournal(entry)"
                                            class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                            title="Hapus Jurnal"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                                <td v-else class="print:hidden"></td>
                            </tr>
                            <!-- Items (debits and credits) -->
                            <tr v-for="item in entry.items" :key="item.id" class="text-gray-700 dark:text-gray-300 print:text-black">
                                <td></td>
                                <td></td>
                                <td class="p-4 text-xs print:p-2">
                                    <div :class="[item.type === 'Credit' ? 'pl-8 print:pl-6' : '', 'font-medium']">
                                        <span class="font-mono bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded text-[10px] mr-2 print:border print:border-black print:bg-white print:text-black">
                                            {{ item.account ? item.account.code : '-' }}
                                        </span>
                                        {{ item.account ? item.account.name : 'Unknown Account' }}
                                    </div>
                                </td>
                                <td class="p-4 text-right font-semibold text-gray-900 dark:text-white print:text-black print:p-2">
                                    {{ item.type === 'Debit' ? formatIDR(item.amount) : '-' }}
                                </td>
                                <td class="p-4 text-right font-semibold text-gray-900 dark:text-white print:text-black print:p-2">
                                    {{ item.type === 'Credit' ? formatIDR(item.amount) : '-' }}
                                </td>
                                <td class="print:hidden"></td>
                            </tr>
                        </template>

                        <!-- Grand Total Row in Print -->
                        <tr v-if="entries.data.length > 0" class="hidden print:table-row bg-slate-100 font-bold border-t-2 border-slate-400 text-black">
                            <td colspan="3" class="p-3 text-right uppercase tracking-wider font-extrabold">TOTAL KESELURUHAN (BALANCE):</td>
                            <td class="p-3 text-right font-black">{{ formatIDR(grandTotalDebit) }}</td>
                            <td class="p-3 text-right font-black">{{ formatIDR(grandTotalCredit) }}</td>
                            <td class="print:hidden"></td>
                        </tr>

                        <tr v-if="entries.data.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400 italic">
                                Belum ada jurnal transaksi tercatat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (Hidden in print) -->
            <div v-if="entries.links.length > 3" class="print:hidden px-4 py-3 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div class="text-xs text-gray-400">Total: {{ entries.total }} jurnal</div>
                <div class="flex items-center space-x-1">
                    <template v-for="(link, key) in entries.links" :key="key">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[link.active ? 'bg-brand-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800', 'px-3 py-1.5 text-xs rounded-lg transition-colors font-medium']"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- PRINT FOOTER SIGNATURE SECTION (Only Visible When Printing) -->
        <div class="hidden print:block">
            <FinancialPrintSignatures doc-number="MKT-JRN-2026-VAL" />
        </div>

        <!-- Add / Edit Journal Entry Modal (Hidden in print) -->
        <div v-if="isModalOpen" class="print:hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden transform transition-all my-8">
                <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-brand-50/40 dark:bg-brand-950/20">
                    <span class="font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>{{ editingJournal ? 'Edit Jurnal Transaksi' : 'Input Jurnal Transaksi Baru' }}</span>
                    </span>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal Transaksi</label>
                            <input
                                v-model="form.entry_date"
                                type="date"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">No. Referensi / Bukti (Opsional)</label>
                            <input
                                v-model="form.reference_number"
                                type="text"
                                placeholder="Contoh: JE-202607-001"
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none font-mono"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Deskripsi / Keterangan Transaksi</label>
                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="Contoh: Pembelian operasional logistik dapur umum banjir..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            required
                        />
                    </div>

                    <!-- Items rows (Debit & Credit) -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-900 dark:text-white uppercase">Pos Transaksi Akun (Debit & Credit)</span>
                            <button
                                type="button"
                                @click="addJournalItem"
                                class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center space-x-1"
                            >
                                <span>+ Tambah Baris Akun</span>
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(item, idx) in form.items"
                                :key="idx"
                                class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/60"
                            >
                                <div class="flex-1">
                                    <select
                                        v-model="item.account_id"
                                        class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 text-xs focus:border-brand-500 focus:outline-none"
                                        required
                                    >
                                        <option value="" disabled>Pilih Akun COA...</option>
                                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                            [{{ acc.code }}] {{ acc.name }} ({{ acc.type }})
                                        </option>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <select
                                        v-model="item.type"
                                        class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 text-xs focus:border-brand-500 focus:outline-none font-bold"
                                        :class="item.type === 'Debit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-brand-600 dark:text-brand-400'"
                                        required
                                    >
                                        <option value="Debit">Debit</option>
                                        <option value="Credit">Credit</option>
                                    </select>
                                </div>
                                <div class="w-36">
                                    <input
                                        v-model="item.amount"
                                        type="number"
                                        step="0.01"
                                        placeholder="Nominal"
                                        class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 text-xs focus:border-brand-500 focus:outline-none font-semibold text-right"
                                        required
                                    />
                                </div>
                                <button
                                    v-if="form.items.length > 2"
                                    type="button"
                                    @click="removeJournalItem(idx)"
                                    class="p-1.5 text-gray-400 hover:text-rose-500 rounded-lg"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Balanced Indicator -->
                        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700/60 flex items-center justify-between text-xs font-semibold">
                            <div class="flex items-center space-x-4">
                                <span>Debit: <strong class="text-emerald-600 dark:text-emerald-400">{{ formatIDR(totalDebit) }}</strong></span>
                                <span>Credit: <strong class="text-brand-600 dark:text-brand-400">{{ formatIDR(totalCredit) }}</strong></span>
                            </div>
                            <div>
                                <span v-if="isBalanced" class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Jurnal Seimbang (Balance)</span>
                                </span>
                                <span v-else class="text-rose-600 dark:text-rose-400 font-bold">
                                    ⚠️ Belum Balance (Selisih: {{ formatIDR(Math.abs(totalDebit - totalCredit)) }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end space-x-3">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing || !isBalanced"
                            class="px-5 py-2 bg-brand-500 hover:bg-brand-600 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-md transition-all"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Jurnal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ON-SCREEN LIGHT MODE PRINT PREVIEW MODAL -->
        <FinancialPrintModal 
            :show="isPrintModalOpen" 
            title="Pratinjau Cetak Jurnal Transaksi Keuangan (Mode Terang)"
            @close="isPrintModalOpen = false"
        >
            <FinancialPrintHeader 
                title="LAPORAN JURNAL TRANSAKSI KEUANGAN" 
                :period="startDate && endDate ? `${startDate} s/d ${endDate}` : 'Semua Transaksi Terdaftar'"
                doc-number="Double-Entry Verified"
            />

            <!-- Preview Table -->
            <table class="w-full text-left text-xs border border-slate-300">
                <thead>
                    <tr class="bg-slate-100 text-slate-900 border-b border-slate-300 font-bold uppercase text-[10px]">
                        <th class="p-2.5 border border-slate-300">Tanggal / Ref</th>
                        <th class="p-2.5 border border-slate-300">Keterangan / Deskripsi</th>
                        <th class="p-2.5 border border-slate-300">Kode & Nama Akun</th>
                        <th class="p-2.5 border border-slate-300 text-right">Debit (Rp)</th>
                        <th class="p-2.5 border border-slate-300 text-right">Credit (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800">
                    <template v-for="entry in entries.data" :key="'prev-' + entry.id">
                        <tr class="bg-slate-50/70 font-semibold border-b border-slate-200">
                            <td class="p-2.5 align-top border border-slate-300">
                                <span class="block font-bold text-slate-900">{{ formatDate(entry.entry_date) }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ entry.reference_number }}</span>
                                <span v-if="entry.reference_number && (entry.reference_number.startsWith('DON-') || entry.reference_number.startsWith('TX-'))" class="ml-1 px-1 py-0.2 rounded bg-amber-100 text-amber-800 text-[8.5px] font-bold">
                                    Donasi
                                </span>
                            </td>
                            <td class="p-2.5 align-top text-slate-900 font-bold border border-slate-300" colspan="4">
                                {{ entry.description }}
                            </td>
                        </tr>
                        <tr v-for="item in entry.items" :key="'prev-item-' + item.id">
                            <td class="border border-slate-300"></td>
                            <td class="border border-slate-300"></td>
                            <td class="p-2 border border-slate-300">
                                <div :class="item.type === 'Credit' ? 'pl-6' : ''">
                                    <span class="font-mono bg-slate-100 text-slate-700 px-1 py-0.5 rounded text-[10px] mr-1.5 border border-slate-200">
                                        {{ item.account ? item.account.code : '-' }}
                                    </span>
                                    {{ item.account ? item.account.name : 'Unknown Account' }}
                                </div>
                            </td>
                            <td class="p-2 text-right font-medium border border-slate-300">
                                {{ item.type === 'Debit' ? formatIDR(item.amount) : '-' }}
                            </td>
                            <td class="p-2 text-right font-medium border border-slate-300">
                                {{ item.type === 'Credit' ? formatIDR(item.amount) : '-' }}
                            </td>
                        </tr>
                    </template>

                    <tr v-if="entries.data.length > 0" class="bg-slate-100 font-bold border-t-2 border-slate-400 text-slate-900">
                        <td colspan="3" class="p-2.5 text-right uppercase tracking-wider font-extrabold">TOTAL KESELURUHAN (BALANCE):</td>
                        <td class="p-2.5 text-right font-black text-emerald-700">{{ formatIDR(grandTotalDebit) }}</td>
                        <td class="p-2.5 text-right font-black text-emerald-700">{{ formatIDR(grandTotalCredit) }}</td>
                    </tr>
                </tbody>
            </table>

            <FinancialPrintSignatures doc-number="MKT-JRN-2026-VAL" />
        </FinancialPrintModal>
    </AuthenticatedLayout>
</template>
