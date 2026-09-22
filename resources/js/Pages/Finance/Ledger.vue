<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import FinancialPrintHeader from '@/Components/FinancialPrintHeader.vue';
import FinancialPrintSignatures from '@/Components/FinancialPrintSignatures.vue';
import FinancialPrintModal from '@/Components/FinancialPrintModal.vue';

const props = defineProps({
    accounts: Array,
    selectedAccountId: Number,
    selectedAccount: Object,
    ledgerItems: Array,
    initialBalance: {
        type: Number,
        default: 0,
    },
    filters: Object,
});

// Filter parameters
const accountId = ref(props.selectedAccountId || (props.accounts[0] ? props.accounts[0].id : ''));
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const isPrintModalOpen = ref(false);

const handleFilter = () => {
    router.get(route('finance.ledger.index'), {
        account_id: accountId.value,
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true, replace: true });
};

const selectAccount = (id) => {
    accountId.value = id;
    handleFilter();
};

const resetFilter = () => {
    startDate.value = '';
    endDate.value = '';
    handleFilter();
};

// Calculate running balance starting from initialBalance
const computedLedgerItems = computed(() => {
    let balance = parseFloat(props.initialBalance || 0);
    const isDebitIncrease = props.selectedAccount && (props.selectedAccount.type === 'Asset' || props.selectedAccount.type === 'Expense');

    return (props.ledgerItems || []).map(item => {
        const amount = parseFloat(item.amount || 0);
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

const totalLedgerDebit = computed(() => {
    return computedLedgerItems.value
        .filter(i => i.type === 'Debit')
        .reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
});

const totalLedgerCredit = computed(() => {
    return computedLedgerItems.value
        .filter(i => i.type === 'Credit')
        .reduce((sum, i) => sum + (parseFloat(i.amount) || 0), 0);
});

const finalBalance = computed(() => {
    if (computedLedgerItems.value.length === 0) {
        return parseFloat(props.initialBalance || 0);
    }
    return computedLedgerItems.value[computedLedgerItems.value.length - 1].running_balance || 0;
});

const activeAccountsWithMutations = computed(() => {
    return (props.accounts || []).filter(a => a.mutations_count > 0);
});

const formatIDR = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
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
        <div class="hidden print:block mb-6">
            <FinancialPrintHeader 
                :title="`LAPORAN BUKU BESAR — [${selectedAccount ? selectedAccount.code : ''}] ${selectedAccount ? selectedAccount.name : ''}`"
                :period="startDate && endDate ? `${startDate} s/d ${endDate}` : 'Keseluruhan Mutasi Akun'"
                :doc-number="`Klasifikasi: ${selectedAccount ? selectedAccount.type : '-'}`"
            />
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
            <div class="flex items-center flex-wrap gap-2">
                <button
                    v-if="selectedAccount"
                    @click="isPrintModalOpen = true"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-md shadow-amber-500/20 cursor-pointer"
                    title="Buka Pratinjau Cetak Mode Terang"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>Pratinjau Cetak</span>
                </button>
            </div>
        </div>

        <!-- Quick Account Selector Chips (Hidden in print) -->
        <div v-if="activeAccountsWithMutations.length > 0" class="print:hidden mb-6 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">⚡ Akun dengan Mutasi Aktif (Klik untuk Melihat):</span>
                <span class="text-[10px] text-slate-400 font-mono">{{ activeAccountsWithMutations.length }} Akun Aktif</span>
            </div>
            <div class="flex items-center flex-wrap gap-2">
                <button
                    v-for="acc in activeAccountsWithMutations"
                    :key="'chip-' + acc.id"
                    @click="selectAccount(acc.id)"
                    :class="[
                        selectedAccountId === acc.id
                            ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20 font-bold'
                            : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700',
                        'px-3 py-1.5 rounded-xl text-xs flex items-center space-x-2 transition-all cursor-pointer'
                    ]"
                >
                    <span class="font-mono text-[11px]">[{{ acc.code }}]</span>
                    <span>{{ acc.name }}</span>
                    <span :class="[selectedAccountId === acc.id ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200', 'px-1.5 py-0.5 rounded text-[10px] font-bold']">
                        {{ formatIDR(acc.current_balance) }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Filters Section (Hidden in print) -->
        <div class="print:hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Select Account Dropdown -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Pilih Akun Buku Besar</label>
                    <select
                        v-model="accountId"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none font-semibold text-gray-800 dark:text-gray-200"
                        @change="handleFilter"
                    >
                        <option value="" disabled>Pilih Akun...</option>
                        <option v-for="a in accounts" :key="a.id" :value="a.id">
                            [{{ a.code }}] {{ a.name }} ({{ a.type }}) — Saldo: {{ formatIDR(a.current_balance) }} ({{ a.mutations_count }} Mutasi)
                        </option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Dari Tanggal</label>
                    <input
                        v-model="startDate"
                        type="date"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                        @change="handleFilter"
                    />
                </div>

                <!-- End Date & Reset -->
                <div class="flex items-center space-x-2">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Sampai Tanggal</label>
                        <input
                            v-model="endDate"
                            type="date"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @change="handleFilter"
                        />
                    </div>
                    <button
                        v-if="startDate || endDate"
                        @click="resetFilter"
                        type="button"
                        class="mt-5 px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold transition-colors cursor-pointer"
                        title="Reset Filter Tanggal"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Ledger Table Card (Printable Table) -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden print:border-black print:shadow-none print:rounded-none">
            <!-- Account Badge in Web View -->
            <div v-if="selectedAccount" class="print:hidden p-5 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="flex items-center space-x-3">
                    <span class="font-mono px-2.5 py-1 rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400 font-black text-sm border border-brand-200 dark:border-brand-800">
                        {{ selectedAccount.code }}
                    </span>
                    <div>
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">{{ selectedAccount.name }}</h3>
                        <p class="text-xs text-gray-500">
                            Tipe: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ selectedAccount.type }}</span> 
                            | Saldo Normal: <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ selectedAccount.normal_balance || (selectedAccount.type === 'Asset' || selectedAccount.type === 'Expense' ? 'Debit' : 'Credit') }}</span>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs text-gray-400 block font-medium">Saldo Akhir Berjalan:</span>
                    <span class="text-lg font-black text-gray-900 dark:text-white font-mono text-emerald-600 dark:text-emerald-400">
                        {{ formatIDR(finalBalance) }}
                    </span>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm print:text-xs">
                    <thead>
                        <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800 uppercase text-[10px] font-black tracking-wider print:text-black print:border-black print:bg-slate-100">
                            <th class="p-4 font-semibold">Tanggal / Bukti</th>
                            <th class="p-4 font-semibold">Keterangan / Transaksi</th>
                            <th class="p-4 font-semibold text-right">Debit (Rp)</th>
                            <th class="p-4 font-semibold text-right">Credit (Rp)</th>
                            <th class="p-4 font-semibold text-right">Saldo Berjalan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 print:divide-gray-400">
                        <!-- Saldo Awal Row if filtered or initialBalance exists -->
                        <tr v-if="startDate || initialBalance !== 0" class="bg-amber-50/50 dark:bg-amber-950/20 font-semibold">
                            <td class="p-4 align-top print:p-2 text-slate-500 text-xs font-mono">
                                {{ startDate ? formatDate(startDate) : 'Awal Periode' }}
                            </td>
                            <td class="p-4 align-top print:p-2 text-amber-800 dark:text-amber-300 font-bold">
                                SALDO AWAL (OPENING BALANCE)
                            </td>
                            <td class="p-4 align-top text-right text-slate-400 print:p-2">-</td>
                            <td class="p-4 align-top text-right text-slate-400 print:p-2">-</td>
                            <td class="p-4 align-top text-right font-bold text-slate-900 dark:text-white font-mono print:p-2">
                                {{ formatIDR(initialBalance) }}
                            </td>
                        </tr>

                        <tr
                            v-for="item in computedLedgerItems"
                            :key="item.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors print:text-black print-zebra"
                        >
                            <td class="p-4 align-top print:p-2">
                                <span class="block text-gray-900 dark:text-white font-bold print:text-black">
                                    {{ item.entry ? formatDate(item.entry.entry_date) : '-' }}
                                </span>
                                <span class="text-xs text-gray-400 font-mono print:text-gray-700 print:text-[9px]">
                                    {{ item.entry ? item.entry.reference_number : '-' }}
                                </span>
                            </td>
                            <td class="p-4 align-top text-gray-800 dark:text-gray-200 print:text-black print:p-2">
                                {{ item.entry ? item.entry.description : '-' }}
                            </td>
                            <td class="p-4 align-top text-right font-medium text-emerald-600 dark:text-emerald-400 print:text-black print:p-2">
                                {{ item.type === 'Debit' ? formatIDR(item.amount) : '-' }}
                            </td>
                            <td class="p-4 align-top text-right font-medium text-brand-600 dark:text-brand-400 print:text-black print:p-2">
                                {{ item.type === 'Credit' ? formatIDR(item.amount) : '-' }}
                            </td>
                            <td class="p-4 align-top text-right font-bold text-gray-900 dark:text-white font-mono print:text-black print:p-2">
                                {{ formatIDR(item.running_balance) }}
                            </td>
                        </tr>

                        <!-- Summary Row in Print -->
                        <tr v-if="computedLedgerItems.length > 0" class="hidden print:table-row bg-slate-100 font-bold border-t-2 border-slate-400 text-black">
                            <td colspan="2" class="p-3 text-right uppercase tracking-wider font-extrabold">TOTAL MUTASI & SALDO AKHIR:</td>
                            <td class="p-3 text-right font-black">{{ formatIDR(totalLedgerDebit) }}</td>
                            <td class="p-3 text-right font-black">{{ formatIDR(totalLedgerCredit) }}</td>
                            <td class="p-3 text-right font-black underline">{{ formatIDR(finalBalance) }}</td>
                        </tr>

                        <tr v-if="computedLedgerItems.length === 0 && initialBalance === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400 italic">
                                Belum ada mutasi transaksi untuk akun ini pada periode yang dipilih.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PRINT FOOTER SIGNATURE SECTION (Only Visible When Printing) -->
        <div class="hidden print:block">
            <FinancialPrintSignatures doc-number="MKT-GL-2026-VAL" />
        </div>

        <!-- ON-SCREEN LIGHT MODE PRINT PREVIEW MODAL -->
        <FinancialPrintModal 
            :show="isPrintModalOpen" 
            :title="`Pratinjau Cetak Buku Besar [${selectedAccount ? selectedAccount.code : ''}] ${selectedAccount ? selectedAccount.name : ''}`"
            @close="isPrintModalOpen = false"
        >
            <FinancialPrintHeader 
                :title="`LAPORAN BUKU BESAR — [${selectedAccount ? selectedAccount.code : ''}] ${selectedAccount ? selectedAccount.name : ''}`"
                :period="startDate && endDate ? `${startDate} s/d ${endDate}` : 'Keseluruhan Mutasi Akun'"
                :doc-number="`Klasifikasi: ${selectedAccount ? selectedAccount.type : '-'}`"
            />

            <!-- Preview Table -->
            <table class="w-full text-left text-xs border border-slate-300">
                <thead>
                    <tr class="bg-slate-100 text-slate-900 border-b border-slate-300 font-bold uppercase text-[10px]">
                        <th class="p-2.5 border border-slate-300">Tanggal / Ref</th>
                        <th class="p-2.5 border border-slate-300">Keterangan / Transaksi</th>
                        <th class="p-2.5 border border-slate-300 text-right">Debit (Rp)</th>
                        <th class="p-2.5 border border-slate-300 text-right">Credit (Rp)</th>
                        <th class="p-2.5 border border-slate-300 text-right">Saldo Berjalan (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800">
                    <!-- Saldo Awal Row in Preview -->
                    <tr v-if="startDate || initialBalance !== 0" class="bg-amber-50 font-semibold border-b border-slate-200">
                        <td class="p-2 align-top border border-slate-300 font-mono text-[10px]">
                            {{ startDate ? formatDate(startDate) : 'Awal Periode' }}
                        </td>
                        <td class="p-2 align-top border border-slate-300 font-bold text-amber-900">
                            SALDO AWAL (OPENING BALANCE)
                        </td>
                        <td class="p-2 text-right border border-slate-300 text-slate-400">-</td>
                        <td class="p-2 text-right border border-slate-300 text-slate-400">-</td>
                        <td class="p-2 text-right font-bold text-slate-900 font-mono border border-slate-300">
                            {{ formatIDR(initialBalance) }}
                        </td>
                    </tr>

                    <tr
                        v-for="item in computedLedgerItems"
                        :key="'prev-' + item.id"
                        class="border-b border-slate-200 hover:bg-slate-50"
                    >
                        <td class="p-2 align-top border border-slate-300">
                            <span class="block font-bold text-slate-900">
                                {{ item.entry ? formatDate(item.entry.entry_date) : '-' }}
                            </span>
                            <span class="text-[10px] text-slate-500 font-mono">
                                {{ item.entry ? item.entry.reference_number : '-' }}
                            </span>
                        </td>
                        <td class="p-2 align-top text-slate-800 border border-slate-300">
                            {{ item.entry ? item.entry.description : '-' }}
                        </td>
                        <td class="p-2 align-top text-right font-medium text-emerald-700 border border-slate-300">
                            {{ item.type === 'Debit' ? formatIDR(item.amount) : '-' }}
                        </td>
                        <td class="p-2 align-top text-right font-medium text-amber-800 border border-slate-300">
                            {{ item.type === 'Credit' ? formatIDR(item.amount) : '-' }}
                        </td>
                        <td class="p-2 align-top text-right font-bold text-slate-900 font-mono border border-slate-300">
                            {{ formatIDR(item.running_balance) }}
                        </td>
                    </tr>

                    <tr v-if="computedLedgerItems.length > 0" class="bg-slate-100 font-bold border-t-2 border-slate-400 text-slate-900">
                        <td colspan="2" class="p-2.5 text-right uppercase tracking-wider font-extrabold">TOTAL MUTASI & SALDO AKHIR:</td>
                        <td class="p-2.5 text-right font-black text-emerald-700">{{ formatIDR(totalLedgerDebit) }}</td>
                        <td class="p-2.5 text-right font-black text-amber-800">{{ formatIDR(totalLedgerCredit) }}</td>
                        <td class="p-2.5 text-right font-black underline">{{ formatIDR(finalBalance) }}</td>
                    </tr>
                </tbody>
            </table>

            <FinancialPrintSignatures doc-number="MKT-GL-2026-VAL" />
        </FinancialPrintModal>
    </AuthenticatedLayout>
</template>
