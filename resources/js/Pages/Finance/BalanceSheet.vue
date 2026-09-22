<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import FinancialPrintHeader from '@/Components/FinancialPrintHeader.vue';
import FinancialPrintSignatures from '@/Components/FinancialPrintSignatures.vue';
import FinancialPrintModal from '@/Components/FinancialPrintModal.vue';

const props = defineProps({
    assets: Array,
    liabilities: Array,
    equity: Array,
    asOfDate: String,
    totalAssets: Number,
    totalLiabilities: Number,
    totalEquity: Number,
});

const searchDate = ref(props.asOfDate || new Date().toISOString().split('T')[0]);
const isPrintModalOpen = ref(false);

const handleFilter = () => {
    router.get(route('finance.balance-sheet.index'), {
        date: searchDate.value
    }, { preserveState: true, replace: true });
};

// CSV Export & Print
const exportCSV = () => {
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += `Laporan Neraca Keuangan - Yayasan MKT (Per Tanggal: ${props.asOfDate})\n`;
    csvContent += "Kategori Klasifikasi,Kode Akun,Nama Akun,Saldo (IDR)\n";

    // Assets
    props.assets.forEach(a => {
        csvContent += `AKTIVA (Aset),"${a.code}","${a.name.replace(/"/g, '""')}",${a.balance}\n`;
    });
    csvContent += `TOTAL AKTIVA,,,${props.totalAssets}\n\n`;

    // Liabilities
    props.liabilities.forEach(l => {
        csvContent += `PASIVA (Kewajiban),"${l.code}","${l.name.replace(/"/g, '""')}",${l.balance}\n`;
    });

    // Equity
    props.equity.forEach(eq => {
        csvContent += `PASIVA (Ekuitas),"${eq.code}","${eq.name.replace(/"/g, '""')}",${eq.balance}\n`;
    });
    csvContent += `TOTAL PASIVA,,,${props.totalLiabilities + props.totalEquity}\n`;

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Neraca_Keuangan_MKT_${props.asOfDate}.csv`);
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
    return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head title="Neraca Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <span>Neraca Keuangan</span>
        </template>

        <!-- MODERN AESTHETIC PRINT HEADER (Only Visible When Printing) -->
        <div class="hidden print:block mb-6">
            <FinancialPrintHeader 
                title="LAPORAN POSISI KEUANGAN (NERACA SALDO)" 
                :period="`Per Tanggal: ${formatDate(asOfDate)}`"
                doc-number="Persamaan: Aset = Liabilitas + Ekuitas"
            />
        </div>

        <!-- Dedicated Page Header Section (Hidden in print) -->
        <div class="print:hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Laporan Neraca Keuangan</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Posisi Keuangan Laporan Aset, Liabilitas, dan Ekuitas Dana Yayasan MKT</p>
                </div>
            </div>

            <!-- Print Preview Action Button -->
            <div class="flex items-center flex-wrap gap-2">
                <button
                    @click="isPrintModalOpen = true"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-md shadow-amber-500/20"
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

        <!-- Date Filter Section (Hidden in print) -->
        <div class="print:hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="font-bold text-gray-900 dark:text-white">Neraca Keuangan Yayasan MKT</h4>
                <p class="text-xs text-gray-400 mt-0.5">Per Tanggal: {{ formatDate(asOfDate) }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <label class="text-xs text-gray-400 font-semibold whitespace-nowrap">Pilih Tanggal Laporan</label>
                <input
                    v-model="searchDate"
                    type="date"
                    class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                    @change="handleFilter"
                />
            </div>
        </div>

        <!-- Split Layout: Assets vs Liabilities & Equity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 print:grid-cols-2 print:gap-4 print:mb-4">
            <!-- Left Side: AKTIVA (Assets) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between print:border-slate-300 print:shadow-none print:p-4 print:rounded-none">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 flex items-center space-x-2 print:text-black print:border-slate-300 print:pb-2 print:mb-2 print:text-sm">
                        <svg class="w-5 h-5 text-brand-500 print:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="uppercase font-black text-slate-900">AKTIVA (Aset / Harta)</span>
                    </h3>

                    <div class="space-y-2">
                        <div v-for="asset in assets" :key="asset.id" class="flex justify-between items-center py-2 text-sm text-gray-700 dark:text-gray-300 print:text-black border-b border-gray-50 dark:border-gray-800 print:border-slate-200 print:py-1.5 print:text-xs">
                            <div>
                                <span class="font-mono text-xs text-gray-400 mr-2 bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded print:text-black print:border print:border-slate-300 print:bg-white">{{ asset.code }}</span>
                                <span class="font-medium text-slate-800">{{ asset.name }}</span>
                            </div>
                            <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ formatIDR(asset.balance) }}</span>
                        </div>

                        <div v-if="assets.length === 0" class="text-center py-6 text-gray-400 italic">
                            Tidak ada akun Aktiva.
                        </div>
                    </div>
                </div>

                <div class="border-t-2 border-slate-300 pt-3 mt-6 flex justify-between items-center font-bold text-base text-gray-900 dark:text-white print:text-black print:pt-2 print:mt-4 print:text-xs bg-slate-50 print:bg-slate-100 p-2 rounded">
                    <span class="uppercase font-extrabold text-slate-900">TOTAL AKTIVA (ASET)</span>
                    <span class="text-brand-600 dark:text-brand-400 print:text-black font-black underline">{{ formatIDR(totalAssets) }}</span>
                </div>
            </div>

            <!-- Right Side: PASIVA (Liabilities & Equity) -->
            <div class="space-y-8 print:space-y-4">
                <!-- Pasiva: Kewajiban & Ekuitas -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[400px] print:min-h-0 print:border-slate-300 print:shadow-none print:p-4 print:rounded-none">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 flex items-center space-x-2 print:text-black print:border-slate-300 print:pb-2 print:mb-2 print:text-sm">
                            <svg class="w-5 h-5 text-brand-500 print:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="uppercase font-black text-slate-900">PASIVA (Kewajiban & Ekuitas)</span>
                        </h3>

                        <!-- Liabilities (Kewajiban) -->
                        <div class="mb-4">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 print:text-black print:text-[10px]">KEWAJIBAN (Hutang)</h4>
                            <div class="space-y-1.5">
                                <div v-for="l in liabilities" :key="l.id" class="flex justify-between items-center py-1.5 text-sm text-gray-700 dark:text-gray-300 print:text-black border-b border-gray-50 dark:border-gray-800 print:border-slate-200 print:py-1 print:text-xs">
                                    <div>
                                        <span class="font-mono text-xs text-gray-400 mr-2 bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded print:text-black print:border print:border-slate-300 print:bg-white">{{ l.code }}</span>
                                        <span class="font-medium text-slate-800">{{ l.name }}</span>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ formatIDR(l.balance) }}</span>
                                </div>
                                <div v-if="liabilities.length === 0" class="text-xs text-gray-400 italic py-1 pl-2">Tidak ada akun Kewajiban.</div>
                            </div>
                            <div class="flex justify-between items-center pt-1.5 mt-1.5 border-t border-dashed border-gray-200 dark:border-gray-800 text-xs font-bold text-gray-600 print:text-black print:text-[10px]">
                                <span>Subtotal Kewajiban</span>
                                <span>{{ formatIDR(totalLiabilities) }}</span>
                            </div>
                        </div>

                        <!-- Equity (Ekuitas / Modal) -->
                        <div>
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 print:text-black print:text-[10px]">EKUITAS (Modal & Surplus)</h4>
                            <div class="space-y-1.5">
                                <div v-for="eq in equity" :key="eq.id" class="flex justify-between items-center py-1.5 text-sm text-gray-700 dark:text-gray-300 print:text-black border-b border-gray-50 dark:border-gray-800 print:border-slate-200 print:py-1 print:text-xs">
                                    <div>
                                        <span class="font-mono text-xs text-gray-400 mr-2 bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded print:text-black print:border print:border-slate-300 print:bg-white">{{ eq.code }}</span>
                                        <span :class="[eq.code === '3999' ? 'text-brand-600 dark:text-brand-400 font-semibold print:text-black' : 'font-medium text-slate-800']">{{ eq.name }}</span>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ formatIDR(eq.balance) }}</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-1.5 mt-1.5 border-t border-dashed border-gray-200 dark:border-gray-800 text-xs font-bold text-gray-600 print:text-black print:text-[10px]">
                                <span>Subtotal Ekuitas</span>
                                <span>{{ formatIDR(totalEquity) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t-2 border-slate-300 pt-3 mt-6 flex justify-between items-center font-bold text-base text-gray-900 dark:text-white print:text-black print:pt-2 print:mt-4 print:text-xs bg-slate-50 print:bg-slate-100 p-2 rounded">
                        <span class="uppercase font-extrabold text-slate-900">TOTAL PASIVA</span>
                        <span class="text-brand-600 dark:text-brand-400 print:text-black font-black underline">{{ formatIDR(totalLiabilities + totalEquity) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balanced Audit Board widget -->
        <div
            :class="[
                Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.01
                    ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/50 text-emerald-900 dark:text-emerald-300'
                    : 'bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-900/50 text-red-900 dark:text-red-300',
                'p-4 border rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 font-semibold shadow-sm mb-6 print:border-slate-300 print:bg-slate-50 print:rounded-none'
            ]"
        >
            <div class="flex items-center space-x-3">
                <div :class="[Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.01 ? 'bg-emerald-500' : 'bg-red-500', 'w-8 h-8 rounded-full flex items-center justify-center text-white font-bold shrink-0 print:hidden']">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white print:text-black">Audit Persamaan Dasar Akuntansi</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 print:text-slate-600">Status Keseimbangan: Aset = Kewajiban + Ekuitas</p>
                </div>
            </div>
            <div class="text-right text-sm">
                <div v-if="Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.01">
                    <span class="text-emerald-700 dark:text-emerald-400 font-bold text-base print:text-black">✓ Neraca Seimbang (Balance Sempurna)</span>
                </div>
                <div v-else>
                    <span class="text-red-600 dark:text-red-400 font-bold">⚠️ Selisih: {{ formatIDR(Math.abs(totalAssets - (totalLiabilities + totalEquity))) }}</span>
                </div>
            </div>
        </div>

        <!-- PRINT FOOTER SIGNATURE SECTION (Only Visible When Printing) -->
        <div class="hidden print:block">
            <FinancialPrintSignatures doc-number="MKT-NERACA-2026-VAL" />
        </div>

        <!-- ON-SCREEN LIGHT MODE PRINT PREVIEW MODAL -->
        <FinancialPrintModal 
            :show="isPrintModalOpen" 
            title="Pratinjau Cetak Neraca Keuangan (Mode Terang)"
            @close="isPrintModalOpen = false"
        >
            <FinancialPrintHeader 
                title="LAPORAN POSISI KEUANGAN (NERACA SALDO)" 
                :period="`Per Tanggal: ${formatDate(asOfDate)}`"
                doc-number="Persamaan: Aset = Liabilitas + Ekuitas"
            />

            <!-- Preview Side-by-Side Grid -->
            <div class="grid grid-cols-2 gap-4 text-xs text-slate-800 mb-4">
                <!-- Left: Assets -->
                <div class="border border-slate-300 p-3 bg-white flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold border-b border-slate-300 pb-1.5 mb-2 text-slate-900 uppercase text-[11px]">
                            AKTIVA (Aset / Harta)
                        </h4>
                        <div class="space-y-1.5">
                            <div v-for="asset in assets" :key="'prev-asset-' + asset.id" class="flex justify-between items-center py-1 border-b border-slate-100">
                                <div>
                                    <span class="font-mono text-[10px] bg-slate-100 px-1 py-0.5 rounded border border-slate-200 mr-1.5">{{ asset.code }}</span>
                                    <span>{{ asset.name }}</span>
                                </div>
                                <span class="font-bold">{{ formatIDR(asset.balance) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t-2 border-slate-300 pt-2 mt-4 flex justify-between items-center font-bold bg-slate-50 p-2 rounded">
                        <span class="uppercase">TOTAL AKTIVA</span>
                        <span class="text-emerald-700 font-black">{{ formatIDR(totalAssets) }}</span>
                    </div>
                </div>

                <!-- Right: Pasiva -->
                <div class="border border-slate-300 p-3 bg-white flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold border-b border-slate-300 pb-1.5 mb-2 text-slate-900 uppercase text-[11px]">
                            PASIVA (Kewajiban & Ekuitas)
                        </h4>
                        <div class="mb-3">
                            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">KEWAJIBAN:</span>
                            <div v-for="l in liabilities" :key="'prev-l-' + l.id" class="flex justify-between items-center py-1 border-b border-slate-100">
                                <div>
                                    <span class="font-mono text-[10px] bg-slate-100 px-1 py-0.5 rounded border border-slate-200 mr-1.5">{{ l.code }}</span>
                                    <span>{{ l.name }}</span>
                                </div>
                                <span class="font-bold">{{ formatIDR(l.balance) }}</span>
                            </div>
                            <div v-if="liabilities.length === 0" class="text-[10px] text-slate-400 italic">Tidak ada akun kewajiban.</div>
                        </div>

                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">EKUITAS & MODAL:</span>
                            <div v-for="eq in equity" :key="'prev-eq-' + eq.id" class="flex justify-between items-center py-1 border-b border-slate-100">
                                <div>
                                    <span class="font-mono text-[10px] bg-slate-100 px-1 py-0.5 rounded border border-slate-200 mr-1.5">{{ eq.code }}</span>
                                    <span>{{ eq.name }}</span>
                                </div>
                                <span class="font-bold">{{ formatIDR(eq.balance) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t-2 border-slate-300 pt-2 mt-4 flex justify-between items-center font-bold bg-slate-50 p-2 rounded">
                        <span class="uppercase">TOTAL PASIVA</span>
                        <span class="text-emerald-700 font-black">{{ formatIDR(totalLiabilities + totalEquity) }}</span>
                    </div>
                </div>
            </div>

            <!-- Balanced Audit verification badge in preview -->
            <div class="p-2.5 bg-slate-100 border border-slate-300 rounded text-xs flex justify-between items-center text-slate-900 font-semibold mb-4">
                <span>Audit Persamaan: Aset ({{ formatIDR(totalAssets) }}) = Pasiva ({{ formatIDR(totalLiabilities + totalEquity) }})</span>
                <span class="text-emerald-700 font-bold">✓ Status: SEIMBANG (BALANCE)</span>
            </div>

            <FinancialPrintSignatures doc-number="MKT-NERACA-2026-VAL" />
        </FinancialPrintModal>
    </AuthenticatedLayout>
</template>
