<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Pratinjau Cetak Laporan Keuangan (Mode Terang)',
    }
});

const emit = defineEmits(['close']);

const printSheetRef = ref(null);

const triggerPrint = () => {
    if (!printSheetRef.value) {
        window.print();
        return;
    }

    // Create an isolated hidden iframe for 100% reliable pure light mode printing
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = 'none';
    document.body.appendChild(printFrame);

    const doc = printFrame.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>${props.title || 'Laporan Keuangan Yayasan MKT'}</title>
            <style>
                @page {
                    size: A4 portrait;
                    margin: 12mm 15mm;
                }
                *, *::before, *::after {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                body {
                    background: #ffffff !important;
                    color: #0f172a !important;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    font-size: 11px;
                    line-height: 1.4;
                    padding: 4px;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    margin-bottom: 12px;
                    font-size: 10px;
                    page-break-inside: auto;
                }
                tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }
                thead {
                    display: table-header-group;
                }
                th, td {
                    border: 1px solid #cbd5e1;
                    padding: 6px 8px;
                    color: #0f172a;
                }
                th {
                    background-color: #f1f5f9 !important;
                    font-weight: 800;
                    text-transform: uppercase;
                    font-size: 9px;
                    letter-spacing: 0.05em;
                }
                tbody tr:nth-child(even) td {
                    background-color: #f8fafc;
                }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .text-left { text-align: left; }
                .font-bold { font-weight: bold; }
                .font-black { font-weight: 900; }
                .font-medium { font-weight: 500; }
                .font-semibold { font-weight: 600; }
                .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
                .uppercase { text-transform: uppercase; }
                .flex { display: flex; }
                .justify-between { justify-content: space-between; }
                .items-center { align-items: center; }
                .space-x-4 > * + * { margin-left: 1rem; }
                .space-x-3 > * + * { margin-left: 0.75rem; }
                .space-x-2 > * + * { margin-left: 0.5rem; }
                .space-x-1 > * + * { margin-left: 0.25rem; }
                .space-y-1\\.5 > * + * { margin-top: 0.375rem; }
                .space-y-2 > * + * { margin-top: 0.5rem; }
                .space-y-3 > * + * { margin-top: 0.75rem; }
                .grid { display: grid; }
                .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .gap-2 { gap: 0.5rem; }
                .gap-4 { gap: 1rem; }
                .gap-8 { gap: 2rem; }
                .border-b-2 { border-bottom-width: 2px; }
                .border-b { border-bottom-width: 1px; }
                .border-t-2 { border-top-width: 2px; }
                .border-t { border-top-width: 1px; }
                .border-amber-600 { border-color: #d97706; }
                .border-amber-500 { border-color: #f59e0b; }
                .border-amber-300 { border-color: #fcd34d; }
                .border-slate-300 { border-color: #cbd5e1; }
                .border-slate-200 { border-color: #e2e8f0; }
                .border-slate-100 { border-color: #f1f5f9; }
                .border-gray-400 { border-color: #9ca3af; }
                .border { border: 1px solid #cbd5e1; }
                .text-amber-700 { color: #b45309; }
                .text-amber-800 { color: #92400e; }
                .text-amber-900 { color: #78350f; }
                .text-emerald-700 { color: #047857; }
                .text-emerald-800 { color: #065f46; }
                .text-slate-900 { color: #0f172a; }
                .text-slate-800 { color: #1e293b; }
                .text-slate-700 { color: #334155; }
                .text-slate-600 { color: #475569; }
                .text-slate-500 { color: #64748b; }
                .text-gray-900 { color: #0f172a; }
                .text-gray-800 { color: #1e293b; }
                .text-gray-700 { color: #334155; }
                .text-gray-600 { color: #475569; }
                .text-gray-500 { color: #64748b; }
                .bg-slate-50 { background-color: #f8fafc; }
                .bg-slate-100 { background-color: #f1f5f9; }
                .bg-amber-50 { background-color: #fffbeb; }
                .bg-amber-100 { background-color: #fef3c7; }
                .bg-emerald-50 { background-color: #ecfdf5; }
                .bg-white { background-color: #ffffff; }
                .rounded { border-radius: 0.25rem; }
                .rounded-lg { border-radius: 0.5rem; }
                .rounded-xl { border-radius: 0.75rem; }
                .p-1 { padding: 0.25rem; }
                .p-2 { padding: 0.5rem; }
                .p-2\\.5 { padding: 0.625rem; }
                .p-3 { padding: 0.75rem; }
                .px-1 { padding-left: 0.25rem; padding-right: 0.25rem; }
                .px-1\\.5 { padding-left: 0.375rem; padding-right: 0.375rem; }
                .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
                .px-2\\.5 { padding-left: 0.625rem; padding-right: 0.625rem; }
                .px-4 { padding-left: 1rem; padding-right: 1rem; }
                .py-0\\.5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }
                .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
                .py-1\\.5 { padding-top: 0.375rem; padding-bottom: 0.375rem; }
                .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
                .mb-1 { margin-bottom: 0.25rem; }
                .mb-2 { margin-bottom: 0.5rem; }
                .mb-3 { margin-bottom: 0.75rem; }
                .mb-4 { margin-bottom: 1rem; }
                .mb-6 { margin-bottom: 1.5rem; }
                .mb-16 { margin-bottom: 4rem; }
                .mt-0\\.5 { margin-top: 0.125rem; }
                .mt-1 { margin-top: 0.25rem; }
                .mt-2 { margin-top: 0.5rem; }
                .mt-3 { margin-top: 0.75rem; }
                .mt-4 { margin-top: 1rem; }
                .mt-6 { margin-top: 1.5rem; }
                .mt-8 { margin-top: 2rem; }
                .pt-2 { padding-top: 0.5rem; }
                .pt-3 { padding-top: 0.75rem; }
                .pt-4 { padding-top: 1rem; }
                .pb-0\\.5 { padding-bottom: 0.125rem; }
                .pb-1\\.5 { padding-bottom: 0.375rem; }
                .pb-2 { padding-bottom: 0.5rem; }
                .pb-3 { padding-bottom: 0.75rem; }
                .pb-4 { padding-bottom: 1rem; }
                .pl-6 { padding-left: 1.5rem; }
                .pl-8 { padding-left: 2rem; }
                .mr-1 { margin-right: 0.25rem; }
                .mr-1\\.5 { margin-right: 0.375rem; }
                .mr-2 { margin-right: 0.5rem; }
                .ml-1 { margin-left: 0.25rem; }
                .min-w-\\[180px\\] { min-width: 180px; }
                .inline-block { display: inline-block; }
                .block { display: block; }
                .w-16 { width: 4rem; }
                .h-16 { height: 4rem; }
                .w-full { width: 100%; }
                .h-full { height: 100%; }
                .object-contain { object-fit: contain; }
                .shrink-0 { flex-shrink: 0; }
                .italic { font-style: italic; }
                .underline { text-decoration: underline; }
                .break-inside-avoid { break-inside: avoid; }
            </style>
        </head>
        <body>
            ${printSheetRef.value.innerHTML}
        </body>
        </html>
    `);
    doc.close();

    // Give images & styles time to evaluate, then trigger native print dialog
    setTimeout(() => {
        printFrame.contentWindow.focus();
        printFrame.contentWindow.print();
        setTimeout(() => {
            if (document.body.contains(printFrame)) {
                document.body.removeChild(printFrame);
            }
        }, 1500);
    }, 300);
};
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-start justify-center p-4 sm:p-6 print:p-0 print:m-0 print:static print:bg-white print:overflow-visible print:block print-modal-container"
    >
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden my-6 flex flex-col max-h-[90vh] print:max-h-none print:my-0 print:shadow-none print:border-none print:rounded-none print:w-full print:max-w-full print:overflow-visible">
            
            <!-- Modal Top Bar (Hidden in print) -->
            <div class="h-14 px-6 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 shrink-0 print:hidden">
                <div class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs">
                        A4
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-tight">{{ title }}</h3>
                        <p class="text-[10px] text-slate-400">Simulasi Tampilan Kertas Cetak Standar Akuntansi Mode Terang</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button
                        type="button"
                        @click="triggerPrint"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all flex items-center space-x-2 shadow-md cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Cetak / Simpan PDF</span>
                    </button>
                    <button
                        type="button"
                        @click="emit('close')"
                        class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer"
                        title="Tutup Pratinjau"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Paper Container (Pure Light Mode Simulation) -->
            <div class="flex-1 overflow-y-auto p-6 sm:p-10 bg-slate-200 dark:bg-slate-950/80 flex justify-center print:p-0 print:m-0 print:bg-white print:overflow-visible">
                <div 
                    ref="printSheetRef"
                    class="w-full max-w-[210mm] bg-white text-slate-900 shadow-2xl p-8 sm:p-12 border border-slate-300 rounded-sm min-h-[297mm] print:shadow-none print:border-none print:p-0 print:m-0 print:min-h-0 print:max-w-full print:w-full print-sheet"
                >
                    <slot />
                </div>
            </div>

            <!-- Modal Footer (Hidden in print) -->
            <div class="px-6 py-3 bg-slate-100 dark:bg-gray-900 border-t border-slate-200 dark:border-gray-800 flex justify-between items-center text-xs text-slate-500 dark:text-gray-400 shrink-0 print:hidden">
                <span>💡 Tip: Gunakan orientasi <strong>Portrait</strong> dan centang <strong>Background Graphics</strong> saat mencetak via browser.</span>
                <button
                    type="button"
                    @click="emit('close')"
                    class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
