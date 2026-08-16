<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

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
                    <h2 class="text-xs font-bold uppercase tracking-wider">LAPORAN POSISI NERACA KEUANGAN</h2>
                    <p class="text-[10px] text-gray-600">Per Tanggal Laporan: {{ formatDate(asOfDate) }}</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-mono font-bold bg-white px-2 py-0.5 border border-gray-300 rounded">Persamaan: Aset = Liabilitas + Ekuitas</span>
                </div>
            </div>
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

            <!-- Export & Print Action Buttons -->
            <div class="flex items-center space-x-2">
                <button
                    @click="exportCSV"
                    class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-2xs"
                    title="Download Excel / CSV"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download CSV</span>
                </button>
                <button
                    @click="triggerPrint"
                    class="px-3.5 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 border border-gray-200 dark:border-gray-700"
                    title="Pratinjau Cetak / PDF"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print / PDF</span>
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Left Side: AKTIVA (Assets) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between print:border-black print:shadow-none">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 flex items-center space-x-2 print:text-black print:border-black">
                        <svg class="w-5 h-5 text-brand-500 print:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>AKTIVA (Aset / Harta)</span>
                    </h3>

                    <div class="space-y-3">
                        <div v-for="asset in assets" :key="asset.id" class="flex justify-between items-center py-2 text-sm text-gray-700 dark:text-gray-300 print:text-black border-b border-gray-50 dark:border-gray-800 print:border-gray-300">
                            <div>
                                <span class="font-mono text-xs text-gray-400 mr-2 bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded print:text-black print:border print:border-black">{{ asset.code }}</span>
                                <span class="font-medium">{{ asset.name }}</span>
                            </div>
                            <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ formatIDR(asset.balance) }}</span>
                        </div>

                        <div v-if="assets.length === 0" class="text-center py-6 text-gray-400 italic">
                            Tidak ada akun Aktiva.
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-800 pt-4 mt-6 flex justify-between items-center font-bold text-lg text-gray-900 dark:text-white print:text-black print:border-black">
                    <span>TOTAL AKTIVA</span>
                    <span class="text-brand-600 dark:text-brand-400 print:text-black">{{ formatIDR(totalAssets) }}</span>
                </div>
            </div>

            <!-- Right Side: PASIVA (Liabilities & Equity) -->
            <div class="space-y-8">
                <!-- Pasiva: Kewajiban & Ekuitas -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[400px] print:border-black print:shadow-none">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 flex items-center space-x-2 print:text-black print:border-black">
                            <svg class="w-5 h-5 text-brand-500 print:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>PASIVA (Kewajiban & Ekuitas)</span>
                        </h3>

                        <!-- Liabilities (Kewajiban) -->
                        <div class="mb-6">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 print:text-black">KEWAJIBAN (Hutang)</h4>
                            <div class="space-y-2">
                                <div v-for="l in liabilities" :key="l.id" class="flex justify-between items-center py-1.5 text-sm text-gray-700 dark:text-gray-300 print:text-black border-b border-gray-50 dark:border-gray-800 print:border-gray-300">
                                    <div>
                                        <span class="font-mono text-xs text-gray-400 mr-2 bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded print:text-black print:border print:border-black">{{ l.code }}</span>
                                        <span class="font-medium">{{ l.name }}</span>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ formatIDR(l.balance) }}</span>
                                </div>
                                <div v-if="liabilities.length === 0" class="text-xs text-gray-400 italic py-2 pl-4">Tidak ada akun Kewajiban.</div>
                            </div>
                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-dashed border-gray-100 dark:border-gray-800 text-xs font-bold text-gray-500 print:text-black">
                                <span>Subtotal Kewajiban</span>
                                <span>{{ formatIDR(totalLiabilities) }}</span>
                            </div>
                        </div>

                        <!-- Equity (Ekuitas / Modal) -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 print:text-black">EKUITAS (Modal & Surplus)</h4>
                            <div class="space-y-2">
                                <div v-for="eq in equity" :key="eq.id" class="flex justify-between items-center py-1.5 text-sm text-gray-700 dark:text-gray-300 print:text-black border-b border-gray-50 dark:border-gray-800 print:border-gray-300">
                                    <div>
                                        <span class="font-mono text-xs text-gray-400 mr-2 bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded print:text-black print:border print:border-black">{{ eq.code }}</span>
                                        <span :class="[eq.code === '3999' ? 'text-brand-600 dark:text-brand-400 font-semibold print:text-black' : 'font-medium']">{{ eq.name }}</span>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ formatIDR(eq.balance) }}</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-dashed border-gray-100 dark:border-gray-800 text-xs font-bold text-gray-500 print:text-black">
                                <span>Subtotal Ekuitas</span>
                                <span>{{ formatIDR(totalEquity) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-800 pt-4 mt-6 flex justify-between items-center font-bold text-lg text-gray-900 dark:text-white print:text-black print:border-black">
                        <span>TOTAL PASIVA</span>
                        <span class="text-brand-600 dark:text-brand-400 print:text-black">{{ formatIDR(totalLiabilities + totalEquity) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balanced Audit Board widget (Hidden in print) -->
        <div
            :class="[
                Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.01
                    ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300'
                    : 'bg-red-50 dark:bg-red-950/20 border-red-100 dark:border-red-900/50 text-red-800 dark:text-red-300',
                'print:hidden p-6 border rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 font-semibold shadow-sm'
            ]"
        >
            <div class="flex items-center space-x-3">
                <div :class="[Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.01 ? 'bg-emerald-500' : 'bg-red-500', 'w-8 h-8 rounded-full flex items-center justify-center text-white font-bold shrink-0']">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Audit Persamaan Dasar Akuntansi</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Persamaan: Aset = Kewajiban + Ekuitas</p>
                </div>
            </div>
            <div class="text-right text-sm">
                <div v-if="Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.01">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold text-lg">Balance Sempurna</span>
                </div>
                <div v-else>
                    <span class="text-red-600 dark:text-red-400 font-bold">Jurnal Selisih / Tidak Seimbang: {{ formatIDR(Math.abs(totalAssets - (totalLiabilities + totalEquity))) }}</span>
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
    </AuthenticatedLayout>
</template>
