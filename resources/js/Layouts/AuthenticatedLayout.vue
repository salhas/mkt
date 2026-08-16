<script setup>
import { ref, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import MktLogo from '@/Components/MktLogo.vue';

const page = usePage();

// Navigation state
const isSidebarOpen = ref(true);
const isFinanceOpen = ref(true);
const isProfileDropdownOpen = ref(false);

// Dark mode state
const isDarkMode = ref(false);

onMounted(() => {
    // Check initial dark mode from documentElement class
    isDarkMode.value = document.documentElement.classList.contains('dark');
    
    // Auto-close sidebar on mobile devices (< 1024px) for clean viewport
    if (typeof window !== 'undefined' && window.innerWidth < 1024) {
        isSidebarOpen.value = false;
    }
});

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const toggleFinance = () => {
    isFinanceOpen.value = !isFinanceOpen.value;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-800 dark:bg-gray-950 dark:text-gray-200 transition-colors duration-200">
        
        <!-- Mobile Sidebar Backdrop Overlay -->
        <transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isSidebarOpen"
                @click="isSidebarOpen = false"
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-30 lg:hidden"
                aria-hidden="true"
            ></div>
        </transition>

        <!-- Sidebar Navigation -->
        <aside
            :class="[
                isSidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-20',
                'fixed top-0 bottom-0 left-0 z-40 bg-white dark:bg-gray-900 border-r border-gray-100 dark:border-gray-800 transition-all duration-300 ease-in-out flex flex-col print:hidden shadow-2xl lg:shadow-none'
            ]"
        >
            <!-- Logo Section -->
            <div class="h-16 flex items-center justify-between px-3.5 border-b border-gray-100 dark:border-gray-800 bg-brand-50/40 dark:bg-brand-950/20">
                <Link :href="route('dashboard')" class="flex items-center overflow-hidden min-w-0">
                    <MktLogo 
                        :variant="isSidebarOpen ? 'full' : 'icon'" 
                        icon-size="w-9 h-9" 
                        text-size="text-sm font-black" 
                        :show-subtitle="isSidebarOpen" 
                    />
                </Link>
                <!-- Collapse Button for Mobile / Desktop -->
                <button
                    @click="toggleSidebar"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none lg:hidden shrink-0"
                    :title="isSidebarOpen ? 'Tutup Sidebar' : 'Buka Sidebar'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- MKT Organization Status Strip in Sidebar -->
            <div v-if="isSidebarOpen" class="px-4 py-2 bg-gradient-to-r from-orange-500/10 via-amber-500/5 to-transparent border-b border-orange-500/10 dark:border-orange-500/15 transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-orange-700 dark:text-orange-300">
                            Pusdalops MKT
                        </span>
                    </div>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-orange-500/15 text-orange-600 dark:text-orange-400">
                        SIAGA 24/7
                    </span>
                </div>
            </div>

            <!-- Menus Section -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-thin">
                <!-- SECTION 1: UTAMA & OPERASI BENCANA -->
                <div class="space-y-1">
                    <span v-if="isSidebarOpen" class="px-3 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 block mb-1">
                        Utama & Kebencanaan
                    </span>

                    <!-- Dashboard -->
                    <Link
                        :href="route('dashboard')"
                        :class="[
                            route().current('dashboard')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Dashboard</span>
                    </Link>

                    <!-- Peta Operasi Bencana -->
                    <Link
                        :href="route('disaster-map.index')"
                        :class="[
                            route().current('disaster-map.index')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Peta Bencana</span>
                    </Link>

                    <!-- Operasi & Siaga SAR -->
                    <Link
                        :href="route('sar-operations.index')"
                        :class="[
                            route().current('sar-operations.index')
                                ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 text-rose-500 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm font-medium">Operasi & Siaga SAR</span>
                    </Link>

                    <!-- Command Center Pusdalops (MENU BARU) -->
                    <Link
                        :href="route('sar-operations.command-center')"
                        :class="[
                            route().current('sar-operations.command-center')
                                ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 text-amber-500 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm font-medium">Command Center Pusdalops</span>
                    </Link>

                    <!-- Mitra & Relawan -->
                    <Link
                        :href="route('volunteers.index')"
                        :class="[
                            route().current('volunteers.index')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Mitra & Relawan</span>
                    </Link>

                    <!-- Logistik Darurat -->
                    <Link
                        :href="route('logistics.index')"
                        :class="[
                            route().current('logistics.index')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Logistik Bencana</span>
                    </Link>
                </div>

                <!-- SECTION 2: FILANTROPI & KEUANGAN -->
                <div class="space-y-1 pt-2 border-t border-gray-100 dark:border-gray-800/60">
                    <span v-if="isSidebarOpen" class="px-3 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 block mb-1">
                        Filantropi & Keuangan
                    </span>

                    <!-- Donatur & Donasi -->
                    <Link
                        :href="route('donors.index')"
                        :class="[
                            route().current('donors.index')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Donatur & Donasi</span>
                    </Link>

                    <!-- Finance (Collapse Group) -->
                    <div>
                        <button
                            @click="toggleFinance"
                            :class="[
                                route().current('finance.*')
                                    ? 'text-brand-600 dark:text-brand-400 font-semibold bg-brand-50/30 dark:bg-brand-950/10'
                                    : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                                'flex items-center justify-between w-full px-3 py-2 rounded-xl transition-all duration-150 group'
                            ]"
                        >
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <span v-if="isSidebarOpen" class="text-sm">Keuangan & Laporan</span>
                            </div>
                            <svg
                                v-if="isSidebarOpen"
                                :class="[
                                    isFinanceOpen ? 'rotate-180' : 'rotate-0',
                                    'w-4 h-4 transition-transform duration-200 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-200'
                                ]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Submenus -->
                        <div v-show="isFinanceOpen && isSidebarOpen" class="pl-8 mt-1 space-y-1">
                            <Link
                                :href="route('finance.coa.index')"
                                :class="[
                                    route().current('finance.coa.index')
                                        ? 'text-brand-600 dark:text-brand-400 font-semibold'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200',
                                    'block py-1.5 text-xs transition-all duration-150'
                                ]"
                            >
                                • Daftar COA (Kode Akun)
                            </Link>
                            <Link
                                :href="route('finance.journal.index')"
                                :class="[
                                    route().current('finance.journal.index')
                                        ? 'text-brand-600 dark:text-brand-400 font-semibold'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200',
                                    'block py-1.5 text-xs transition-all duration-150'
                                ]"
                            >
                                • Jurnal Umum
                            </Link>
                            <Link
                                :href="route('finance.ledger.index')"
                                :class="[
                                    route().current('finance.ledger.index')
                                        ? 'text-brand-600 dark:text-brand-400 font-semibold'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200',
                                    'block py-1.5 text-xs transition-all duration-150'
                                ]"
                            >
                                • Buku Besar (Ledger)
                            </Link>
                            <Link
                                :href="route('finance.balance-sheet.index')"
                                :class="[
                                    route().current('finance.balance-sheet.index')
                                        ? 'text-brand-600 dark:text-brand-400 font-semibold'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200',
                                    'block py-1.5 text-xs transition-all duration-150'
                                ]"
                            >
                                • Neraca Keuangan
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: YAYASAN & MANAJEMEN -->
                <div class="space-y-1 pt-2 border-t border-gray-100 dark:border-gray-800/60">
                    <span v-if="isSidebarOpen" class="px-3 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 block mb-1">
                        Yayasan & Manajemen
                    </span>

                    <!-- Profil MKT -->
                    <Link
                        :href="route('mkt-profile.index')"
                        :class="[
                            route().current('mkt-profile.index')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Profil MKT</span>
                    </Link>

                    <!-- Pengurus & Struktur Organisasi MKT -->
                    <Link
                        :href="route('management.index')"
                        :class="[
                            route().current('management.index')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Pengurus & Anggota</span>
                    </Link>

                    <!-- Arsip Rapat -->
                    <Link
                        :href="route('meetings.index')"
                        :class="[
                            route().current('meetings.*')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Arsip Rapat</span>
                    </Link>

                    <!-- Manajemen User (Pengguna) -->
                    <Link
                        v-if="['webmaster', 'administrator'].includes($page.props.auth.user.role)"
                        :href="route('users.index')"
                        :class="[
                            route().current('users.index')
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/30 dark:text-brand-400 font-semibold'
                                : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-100',
                            'flex items-center space-x-3 px-3 py-2 rounded-xl transition-all duration-150 group'
                        ]"
                    >
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span v-if="isSidebarOpen" class="text-sm">Manajemen User</span>
                    </Link>
                </div>
            </nav>

            <!-- Bottom Panel / User Profile Info -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <div v-if="isSidebarOpen" class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-orange-400 to-amber-500 flex items-center justify-center font-bold text-white shrink-0">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="truncate text-left">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[10px] text-gray-400 truncate">{{ $page.props.auth.user.email }}</p>
                        </div>
                    </div>
                    <!-- Logout Trigger -->
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-all focus:outline-none"
                        title="Log Out"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </Link>
                </div>
                <div v-else class="flex justify-center">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-orange-400 to-amber-500 flex items-center justify-center font-bold text-white">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div
            :class="[
                isSidebarOpen ? 'lg:pl-64' : 'lg:pl-20',
                'min-h-screen flex flex-col transition-all duration-300 ease-in-out print:pl-0 print:m-0 print:w-full'
            ]"
        >
            <!-- Top Navbar -->
            <header class="h-14 sm:h-16 sticky top-0 z-30 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 flex items-center justify-between px-3.5 sm:px-6 transition-colors duration-200 print:hidden">
                <div class="flex items-center space-x-2 sm:space-x-4 min-w-0 flex-1">
                    <!-- Toggle sidebar trigger -->
                    <button
                        @click="toggleSidebar"
                        class="p-1.5 sm:p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none shrink-0"
                        aria-label="Toggle Sidebar"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <!-- Title/Header Slot -->
                    <div class="font-bold text-xs sm:text-sm md:text-base text-gray-800 dark:text-gray-200 truncate min-w-0">
                        <slot name="header"></slot>
                    </div>
                </div>

                <!-- Right Toolbar Actions -->
                <div class="flex items-center space-x-1.5 sm:space-x-3 shrink-0">
                    <!-- Dark Mode Toggle Button -->
                    <button
                        @click="toggleDarkMode"
                        class="p-1.5 sm:p-2 rounded-xl text-gray-400 hover:text-brand-500 hover:bg-brand-50/50 dark:hover:bg-brand-950/20 focus:outline-none transition-all duration-150 shrink-0"
                        aria-label="Toggle Theme"
                    >
                        <!-- Sun Icon for Light Mode (shown in dark mode) -->
                        <svg v-if="isDarkMode" class="w-4 h-4 sm:w-5 sm:h-5 text-amber-400 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                        </svg>
                        <!-- Moon Icon for Dark Mode (shown in light mode) -->
                        <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>

                    <!-- Profile Dropdown (Alternative top trigger) -->
                    <div class="relative shrink-0">
                        <button
                            @click="isProfileDropdownOpen = !isProfileDropdownOpen"
                            class="flex items-center space-x-1 p-0.5 sm:p-1 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition focus:outline-none"
                            aria-label="Profile Menu"
                        >
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-brand-100 dark:bg-brand-950 text-brand-700 dark:text-brand-300 font-bold text-xs sm:text-sm flex items-center justify-center">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <!-- Dropdown Content -->
                        <div
                            v-if="isProfileDropdownOpen"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl shadow-xl z-50 py-1.5"
                        >
                            <Link
                                :href="route('profile.edit')"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
                                @click="isProfileDropdownOpen = false"
                            >
                                Pengaturan Akun
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20"
                                @click="isProfileDropdownOpen = false"
                            >
                                Keluar
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-3.5 sm:p-6 lg:p-8">
                <!-- Notifications Alert -->
                <transition name="fade">
                    <div
                        v-if="page.props.flash && page.props.flash.success"
                        class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center justify-between"
                    >
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ page.props.flash.success }}</span>
                        </div>
                        <button @click="page.props.flash.success = null" class="text-emerald-400 hover:text-emerald-600 focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </transition>

                <!-- Page view goes here -->
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 20px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.5);
}
.animate-spin-slow {
    animation: spin 8s linear infinite;
}
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
