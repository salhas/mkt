<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import defaultMktLogo from '../../images/mkt_logo.png';

const props = defineProps({
    variant: {
        type: String,
        default: 'full', // 'icon' | 'full'
    },
    iconSize: {
        type: String,
        default: 'w-11 h-11',
    },
    textSize: {
        type: String,
        default: 'text-xl',
    },
    showSubtitle: {
        type: Boolean,
        default: true,
    },
    customLogo: {
        type: String,
        default: '',
    }
});

const page = usePage();

// Dynamically use logo from Dashboard Admin Profil MKT or bundled mkt_logo.png
const logoUrl = computed(() => {
    if (props.customLogo) return props.customLogo;
    if (page?.props?.mktProfile?.logo) return page.props.mktProfile.logo;
    return defaultMktLogo;
});
</script>

<template>
    <div class="flex items-center space-x-2 sm:space-x-3 group select-none min-w-0">
        <!-- Logo Image Container with Soft Glow -->
        <div class="relative flex-shrink-0" :class="iconSize">
            <!-- Ambient Glow -->
            <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 blur-sm opacity-40 group-hover:opacity-75 transition duration-300"></div>
            
            <!-- Logo Box -->
            <div class="relative w-full h-full rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-1 sm:p-1.5 flex items-center justify-center shadow-md overflow-hidden">
                <img 
                    :src="logoUrl" 
                    alt="Logo Yayasan MKT" 
                    class="w-full h-full object-contain rounded-lg sm:rounded-xl transition-transform duration-300 group-hover:scale-105" 
                />
            </div>
        </div>

        <!-- Typography Brand Text (Only if variant === 'full') -->
        <div v-if="variant === 'full'" class="flex flex-col min-w-0">
            <div class="flex items-center space-x-1.5 sm:space-x-2">
                <span 
                    class="font-black tracking-tight leading-none bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 dark:from-orange-400 dark:via-amber-300 dark:to-orange-400 bg-clip-text text-transparent truncate"
                    :class="textSize"
                >
                    MKT INDONESIA
                </span>
                <span class="inline-block w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-500 animate-pulse shrink-0" title="Sistem Siaga Bencana Aktif 24/7"></span>
            </div>
            <span 
                v-if="showSubtitle" 
                class="block text-[8px] sm:text-[9px] font-bold tracking-wider text-slate-500 dark:text-slate-400 uppercase mt-0.5 truncate"
            >
                Yayasan Mitra Kemanusiaan Terpadu
            </span>
        </div>
    </div>
</template>
