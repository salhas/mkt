<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    accounts: Array,
    selectedAccountId: Number,
    selectedAccount: Object,
    ledgerItems: Array,
    filters: Object,
});

// Filter parameters
const accountId = ref(props.selectedAccountId || (props.accounts[0] ? props.accounts[0].id : ''));
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const handleFilter = () => {
    router.get(route('finance.ledger.index'), {
        account_id: accountId.value,
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true, replace: true });
};

// Calculate running balance
const computedLedgerItems = computed(() => {
    let balance = 0;
    const isDebitIncrease = props.selectedAccount && (props.selectedAccount.type === 'Asset' || props.selectedAccount.type === 'Expense');

    return Object.values(props.ledgerItems).map(item => {
        const amount = parseFloat(item.amount);
        if (item.type === 'Debit') {
            balance += isDebitIncrease ? amount : -amount;
        } else {
            balance += isDebitIncrease ? -amount : amount;
        }
        return {
            ...item,
            running_balance: balance
        };
    });
});

// CSV Export & Print
const exportCSV = () => {
    if (!props.selectedAccount) return;

    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += `Buku Besar - [${props.selectedAccount.code}] ${props.selectedAccount.name}\n`;
    csvContent += "Tanggal,No. Referensi,Deskripsi / Keterangan,Debit (IDR),Credit (IDR),Saldo Berjalan (IDR)\n";

    computedLedgerItems.value.forEach(item => {
        const date = item.entry ? (item.entry.entry_date ? item.entry.entry_date.split('T')[0] : '') : '';
        const ref = `"${(item.entry ? item.entry.reference_number || '' : '').replace(/"/g, '""')}"`;
        const desc = `"${(item.entry ? item.entry.description || '' : '').replace(/"/g, '""')}"`;
        const debit = item.type === 'Debit' ? item.amount : 0;
        const credit = item.type === 'Credit' ? item.amount : 0;
        const running = item.running_balance || 0;

        csvContent += `${date},${ref},${desc},${debit},${credit},${running}\n`;
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Buku_Besar_${props.selectedAccount.code}_${new Date().toISOString().split('T')[0]}.csv`);
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
    <Head title="Buku Besar" />

    <AuthenticatedLayout>
        <template #header>
            <span>Buku Besar (General Ledger)</span>
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
                    <h2 class="text-xs font-bold uppercase tracking-wider">
                        LAPORAN BUKU BESAR — {{ selectedAccount ? `[${selectedAccount.code}] ${selectedAccount.name}` : '' }}
                    </h2>
                    <p class="text-[10px] text-gray-600">Filter Tanggal: {{ startDate && endDate ? `${startDate} s/d ${endDate}` : 'Keseluruhan Mutasi Akun' }}</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-mono font-bold bg-white px-2 py-0.5 border border-gray-300 rounded">Klasifikasi: {{ selectedAccount ? selectedAccount.type : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Dedicated Page Header Section (Hidden in print) -->
        <div class="print:hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Buku Besar (General Ledger)</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rincian Pergerakan Mutasi Debet/Kredit & Saldo Akun Keuangan Yayasan MKT</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button
                    v-if="selectedAccount"
                    @click="exportCSV"
                    class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-2xs"
                    title="Download Excel / CSV"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download CSV</span>
                </button>
                <button
                    v-if="selectedAccount"
                    @click="triggerPrint"
                    class="px-3.5 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 border border-gray-200 dark:border-gray-700"
                    title="Pratinjau Cetak / PDF"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print / PDF</span>
                </button>
            </div>
        </div>

        <!-- Filters Section (Hidden in print) -->
        <div class="print:hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Select Account -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Pilih Akun Buku Besar</label>
                    <select
                        v-model="accountId"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                        @change="handleFilter"
                    >
                        <option value="" disabled>Pilih Akun...</option>
                        <option v-for="a in accounts" :key="a.id" :value="a.id">
                            [{{ a.code }}] {{ a.name }} ({{ a.type }})
                        </option>
                    </select>
                </div>

                <!-- Date Range -->
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
        </div>

        <!-- Ledger details -->
        <div v-if="selectedAccount" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden print:border-black print:shadow-none">
            <!-- Account Summary Header -->
            <div class="p-6 bg-brand-50/30 dark:bg-brand-950/10 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center print:bg-gray-100 print:border-black">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white print:text-black">
                        [{{ selectedAccount.code }}] {{ selectedAccount.name }}
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 print:text-gray-700">Klasifikasi Akun: {{ selectedAccount.type }}</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold text-gray-400 uppercase block print:text-black">Saldo Akhir</span>
                    <span class="text-xl font-bold text-brand-600 dark:text-brand-400 print:text-black">
                        {{ computedLedgerItems.length > 0 ? formatIDR(computedLedgerItems[computedLedgerItems.length - 1].running_balance) : 'Rp 0' }}
                    </span>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm print:text-xs">
                    <thead>
                        <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800 uppercase text-[10px] font-black tracking-wider print:text-black print:border-black">
                            <th class="p-4 font-semibold">Tanggal</th>
                            <th class="p-4 font-semibold">Referensi</th>
                            <th class="p-4 font-semibold">Deskripsi / Keterangan</th>
                            <th class="p-4 font-semibold text-right">Debit</th>
                            <th class="p-4 font-semibold text-right">Credit</th>
                            <th class="p-4 font-semibold text-right">Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 print:divide-gray-400">
                        <!-- Ledger items rows -->
                        <tr v-for="item in computedLedgerItems" :key="item.id" class="text-gray-700 dark:text-gray-300 hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors print:text-black">
                            <td class="p-4 text-xs text-gray-400 print:text-black">{{ formatDate(item.entry.entry_date) }}</td>
                            <td class="p-4 font-mono text-xs">{{ item.entry.reference_number }}</td>
                            <td class="p-4">{{ item.entry.description }}</td>
                            <td class="p-4 text-right font-semibold text-emerald-600 dark:text-emerald-400 print:text-black">
                                {{ item.type === 'Debit' ? formatIDR(item.amount) : '-' }}
                            </td>
                            <td class="p-4 text-right font-semibold text-rose-600 dark:text-rose-400 print:text-black">
                                {{ item.type === 'Credit' ? formatIDR(item.amount) : '-' }}
                            </td>
                            <td class="p-4 text-right font-bold text-gray-900 dark:text-white print:text-black">
                                {{ formatIDR(item.running_balance) }}
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="computedLedgerItems.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400 italic">
                                Tidak ada transaksi buku besar untuk akun ini pada periode yang dipilih.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-else class="text-center p-12 bg-white dark:bg-gray-900 border border-dashed border-gray-200 dark:border-gray-800 rounded-2xl">
            <p class="text-gray-400 italic">Pilih akun buku besar terlebih dahulu.</p>
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
    </AuthenticatedLayout>
</template>
