<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { showSuccessToast, showErrorToast } from '@/Utils/toast.js';

const props = defineProps({
    entries: Object,
    accounts: Array,
    filters: Object,
});

// Filters
const search = ref(props.filters.search || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

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

// Balanced validation sums
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
        <div class="hidden print:block mb-8 pb-4 border-b-2 border-amber-500">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-xl shadow-md">
                        MKT
                    </div>
                    <div>
                        <h1 class="text-base font-black text-gray-900 uppercase tracking-tight">YAYASAN MITRA KEMANUSIAAN TERPADU (MKT)</h1>
                        <p class="text-xs font-bold text-amber-600">Ekosistem Penanggulangan Bencana & Filantropi Terpadu Indonesia</p>
                        <p class="text-[10px] text-gray-600">Perumahan Insignia Oasis Blok B1-11 No 7, Kota Makassar, Sulsel | Hotline: +62 812-3456-7890</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase rounded-full border border-amber-300">
                        Dokumen Keuangan Resmi
                    </span>
                    <p class="text-[10px] text-gray-500 mt-1">Tgl Cetak: {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-200 flex justify-between items-center bg-gray-50 p-2.5 rounded-xl text-black">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider">LAPORAN JURNAL TRANSAKSI KEUANGAN</h2>
                    <p class="text-[10px] text-gray-600">Filter Periode: {{ startDate && endDate ? `${startDate} s/d ${endDate}` : 'Semua Transaksi Terdaftar' }}</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-mono font-bold bg-white px-2 py-0.5 border border-gray-300 rounded">Double-Entry Verified</span>
                </div>
            </div>
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

            <!-- Action Buttons: Export CSV, Print Preview & Add Journal -->
            <div class="flex items-center flex-wrap gap-2.5">
                <button
                    @click="exportCSV"
                    class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-2xs"
                    title="Download Excel / CSV"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download CSV</span>
                </button>
                <button
                    @click="triggerPrint"
                    class="px-3 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 border border-gray-200 dark:border-gray-700"
                    title="Pratinjau Cetak / PDF"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print / PDF</span>
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
                        <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800 uppercase text-[10px] font-black tracking-wider print:text-black print:border-black">
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
                                <td class="p-4 align-top">
                                    <span class="block text-gray-900 dark:text-white font-bold print:text-black">{{ formatDate(entry.entry_date) }}</span>
                                    <span class="text-xs text-gray-400 font-mono print:text-gray-800">{{ entry.reference_number }}</span>
                                </td>
                                <td class="p-4 align-top text-gray-800 dark:text-gray-200 font-bold print:text-black" colspan="3">
                                    {{ entry.description }}
                                </td>
                                <td></td>
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
                                <td class="p-4 text-xs">
                                    <div :class="[item.type === 'Credit' ? 'pl-8' : '', 'font-medium']">
                                        <span class="font-mono bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded text-[10px] mr-2 print:border print:border-black">
                                            {{ item.account ? item.account.code : '-' }}
                                        </span>
                                        {{ item.account ? item.account.name : 'Unknown Account' }}
                                    </div>
                                </td>
                                <td class="p-4 text-right font-semibold text-gray-900 dark:text-white print:text-black">
                                    {{ item.type === 'Debit' ? formatIDR(item.amount) : '-' }}
                                </td>
                                <td class="p-4 text-right font-semibold text-gray-900 dark:text-white print:text-black">
                                    {{ item.type === 'Credit' ? formatIDR(item.amount) : '-' }}
                                </td>
                                <td class="print:hidden"></td>
                            </tr>
                        </template>
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
        <div class="hidden print:grid grid-cols-2 gap-8 mt-12 text-center text-xs text-black">
            <div>
                <p class="font-bold uppercase mb-16">Dibuat Oleh,<br />Bendahara / Keuangan</p>
                <p class="font-bold underline">( Siti Rahmah, S.Pd. )</p>
                <p class="text-[10px] text-gray-600">Bendahara Umum Yayasan MKT</p>
            </div>
            <div>
                <p class="font-bold uppercase mb-16">Disetujui Oleh,<br />Ketua Umum Yayasan MKT</p>
                <p class="font-bold underline">( Muhammad Ridwan, S.Kom. )</p>
                <p class="text-[10px] text-gray-600">Ketua Eksekutif Yayasan MKT</p>
            </div>
        </div>

        <!-- Add / Edit Journal Entry Modal (Hidden in print) -->
        <div v-if="isModalOpen" class="print:hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden transform transition-all my-8">
                <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-brand-50/40 dark:bg-brand-950/20">
                    <span class="font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                        <span>📝</span>
                        <span>{{ editingJournal ? 'Edit Jurnal Transaksi (Double Entry)' : 'Catat Jurnal Baru (Double Entry)' }}</span>
                    </span>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold">&times;</button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal Transaksi</label>
                            <input v-model="form.entry_date" type="date" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">No. Referensi (misal: JE-001)</label>
                            <input v-model="form.reference_number" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs focus:border-brand-500 focus:outline-none" placeholder="Auto-generated jika kosong" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Keterangan / Deskripsi Transaksi</label>
                        <input v-model="form.description" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs focus:border-brand-500 focus:outline-none" placeholder="Deskripsi pengeluaran / donasi..." required />
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase">Pos Debet & Kredit (Min. 2 Baris)</label>
                            <button type="button" @click="addJournalItem" class="text-xs font-bold text-brand-600 hover:text-brand-700 focus:outline-none">+ Tambah Baris</button>
                        </div>

                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1 scrollbar-thin">
                            <div v-for="(item, idx) in form.items" :key="idx" class="flex items-center space-x-3 bg-gray-50/80 dark:bg-gray-800/40 p-2.5 rounded-2xl border border-gray-100 dark:border-gray-800">
                                <select v-model="item.account_id" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none flex-1" required>
                                    <option value="" disabled>Pilih Akun...</option>
                                    <option v-for="a in accounts" :key="a.id" :value="a.id">
                                        [{{ a.code }}] {{ a.name }} ({{ a.type }})
                                    </option>
                                </select>

                                <select v-model="item.type" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none w-28" required>
                                    <option value="Debit">Debit</option>
                                    <option value="Credit">Credit</option>
                                </select>

                                <input v-model="item.amount" type="number" step="0.01" placeholder="Jumlah (IDR)" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none w-36" required />

                                <button type="button" @click="removeJournalItem(idx)" class="text-rose-500 hover:text-rose-700 disabled:opacity-30 p-1" :disabled="form.items.length <= 2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 p-4 rounded-2xl flex items-center justify-between text-xs font-semibold bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 mr-4">Total Debit: <span class="text-gray-900 dark:text-white font-bold">{{ formatIDR(totalDebit) }}</span></span>
                                <span class="text-gray-500 dark:text-gray-400">Total Credit: <span class="text-gray-900 dark:text-white font-bold">{{ formatIDR(totalCredit) }}</span></span>
                            </div>
                            <div class="flex items-center">
                                <span v-if="isBalanced" class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span>Jurnal Balance</span>
                                </span>
                                <span v-else class="text-rose-500 font-bold flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-8a1 1 0 00-1-1H7a1 1 0 000 2h1a1 1 0 001-1zm3 0a1 1 0 00-1-1h-1a1 1 0 000 2h1a1 1 0 001-1z" clip-rule="evenodd"></path></svg>
                                    <span>Selisih: {{ formatIDR(Math.abs(totalDebit - totalCredit)) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 rounded-xl text-xs font-semibold hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="form.processing || !isBalanced" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 disabled:bg-gray-300 dark:disabled:bg-gray-800 disabled:text-gray-400 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">
                            {{ editingJournal ? 'Perbarui Jurnal' : 'Simpan Jurnal Baru' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
