<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import defaultMktLogo from '../../images/mkt_logo.png';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    period: {
        type: String,
        default: '',
    },
    docNumber: {
        type: String,
        default: '',
    }
});

const page = usePage();

const profile = computed(() => page?.props?.mktProfile || {});

const logoUrl = computed(() => {
    if (profile.value?.logo) return profile.value.logo;
    return defaultMktLogo;
});

const orgName = computed(() => {
    return profile.value?.name || 'YAYASAN MITRA KEMANUSIAAN TERPADU (MKT)';
});

const orgAddress = computed(() => {
    return profile.value?.address || 'Jl. Ujung Pandang No. 45, Kota Makassar, Sulawesi Selatan';
});

const orgPhone = computed(() => {
    return profile.value?.phone || '+62 812-3456-7890';
});

const orgEmail = computed(() => {
    return profile.value?.email || 'info@mkt.or.id';
});

const todayDateFormatted = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});
</script>

<template>
    <div class="mb-6 pb-4 border-b-2 border-amber-600 print-kop-container text-slate-900">
        <!-- Main Letterhead / Kop Surat Resmi -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <!-- Official Logo Image Container -->
                <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 p-1 flex items-center justify-center shrink-0 shadow-xs">
                    <img 
                        :src="logoUrl" 
                        :alt="orgName" 
                        class="w-full h-full object-contain"
                    />
                </div>
                <div>
                    <h1 class="text-base font-black text-gray-900 uppercase tracking-tight leading-tight">
                        {{ orgName }}
                    </h1>
                    <p class="text-[11px] font-bold text-amber-700 leading-tight mt-0.5">
                        Ekosistem Penanggulangan Bencana, Tim Rescue & Relawan Donor Darah
                    </p>
                    <p class="text-[9.5px] text-gray-600 mt-0.5 leading-normal">
                        {{ orgAddress }} | Hotline: {{ orgPhone }} | Email: {{ orgEmail }}
                    </p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <span class="inline-block px-2.5 py-1 bg-amber-50 text-amber-900 text-[9px] font-extrabold uppercase rounded border border-amber-300">
                    Dokumen Keuangan Resmi
                </span>
                <p class="text-[9.5px] text-gray-500 mt-1">
                    Tgl Cetak: <span class="font-medium text-gray-700">{{ todayDateFormatted }}</span>
                </p>
            </div>
        </div>

        <!-- Double Amber Divider Line -->
        <div class="mt-3 pt-2 border-t border-amber-500/40"></div>

        <!-- Document Header Box -->
        <div class="mt-2 bg-slate-50 border border-slate-200 rounded-lg p-3 flex justify-between items-center text-gray-900">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-gray-900">
                    {{ title }}
                </h2>
                <p v-if="period || subtitle" class="text-[10px] text-gray-600 font-medium mt-0.5">
                    {{ period ? `Periode Laporan: ${period}` : subtitle }}
                </p>
            </div>
            <div class="text-right">
                <span class="text-[9.5px] font-mono font-bold bg-white px-2 py-0.5 border border-slate-300 rounded text-slate-800">
                    {{ docNumber || 'Double-Entry Verified' }}
                </span>
            </div>
        </div>
    </div>
</template>
