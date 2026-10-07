<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BmkgWeatherWidget from '@/Components/BmkgWeatherWidget.vue';
import SystemRoleFlowchart from '@/Components/SystemRoleFlowchart.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const canViewFlowchart = computed(() => {
    return currentUser.value && ['webmaster', 'administrator'].includes(currentUser.value.role);
});

const activeMainTab = ref('overview'); // 'overview' | 'flowchart'

const checkUrlTab = () => {
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if ((tab === 'alur' || tab === 'flowchart') && canViewFlowchart.value) {
            activeMainTab.value = 'flowchart';
        } else {
            activeMainTab.value = 'overview';
        }
    }
};

onMounted(() => {
    checkUrlTab();
});

watch(() => page.url, () => {
    checkUrlTab();
});

const setMainTab = (tab) => {
    if (tab === 'flowchart' && !canViewFlowchart.value) return;
    activeMainTab.value = tab;
    if (typeof window !== 'undefined') {
        const url = new URL(window.location);
        if (tab === 'flowchart') {
            url.searchParams.set('tab', 'alur');
        } else {
            url.searchParams.delete('tab');
        }
        window.history.replaceState({}, '', url);
    }
};

const props = defineProps({
    isPartner: Boolean,
    partner: Object,
    partnerStats: Object,
    recentPartnerMembers: Array,
    volunteerStats: Object,
    donationStats: Object,
    logisticStats: Object,
    financialStats: Object,
    recentDonations: Array,
    recentVolunteers: Array,
    recentLogistics: Array,
    weatherData: Object,
});

const formatIDR = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(val);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Dashboard Overview" />

    <AuthenticatedLayout>
        <template #header>
            <span>{{ isPartner ? 'Dashboard Lembaga Mitra' : 'Dashboard' }}</span>
        </template>

        <!-- Dedicated Page Header Section -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div 
                    :class="[
                        isPartner 
                            ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400' 
                            : 'bg-brand-50 dark:bg-brand-950/40 text-brand-500',
                        'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm'
                    ]"
                >
                    <svg v-if="isPartner" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
                        {{ isPartner ? ('Dashboard Lembaga - ' + (partner?.name || 'Mitra')) : 'Dashboard Operational Hub' }}
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ isPartner ? 'Panel Eksklusif Profil, Pengurus, Personel & Kesiapsiagaan Lembaga Mitra' : 'Ringkasan Penanggulangan Bencana, Donasi, Relawan & Logistik MKT Indonesia' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span 
                    :class="[
                        isPartner 
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800' 
                            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                        'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border'
                    ]"
                >
                    <span class="w-2 h-2 rounded-full mr-2" :class="isPartner ? 'bg-indigo-500' : 'bg-emerald-500 animate-pulse'"></span>
                    {{ isPartner ? ('Mitra ' + (partner?.status || 'Aktif')) : 'Sistem Operasional Aktif' }}
                </span>
            </div>
        </div>

        <!-- Navigation Tab Bar (Hanya jika non-mitra / bisa view flowchart) -->
        <div v-if="canViewFlowchart" class="flex items-center space-x-2 mb-6 border-b border-gray-200 dark:border-gray-800 pb-2">
            <button
                @click="setMainTab('overview')"
                :class="[
                    activeMainTab === 'overview'
                        ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold shadow-md shadow-orange-500/20'
                        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 font-medium',
                    'px-4 py-2.5 rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition-all'
                ]"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                </svg>
                <span>Ringkasan Operasional</span>
            </button>

            <!-- Tab Alur (Flowchart Sistem) - Khusus Webmaster & Administrator -->
            <button
                v-if="canViewFlowchart"
                @click="setMainTab('flowchart')"
                :class="[
                    activeMainTab === 'flowchart'
                        ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold shadow-md shadow-orange-500/20'
                        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 font-medium',
                    'px-4 py-2.5 rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition-all group'
                ]"
            >
                <span class="text-base group-hover:scale-110 transition-transform">🔀</span>
                <span>Alur Sistem (Flowchart)</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-400/30">
                    Khusus Webmaster & Admin
                </span>
            </button>
        </div>

        <!-- TAB CONTENT 1: ALUR SISTEM & FLOWCHART ROLE -->
        <div v-if="activeMainTab === 'flowchart' && canViewFlowchart">
            <SystemRoleFlowchart />
        </div>

        <!-- TAB CONTENT 2: RINGKASAN OPERASIONAL (OVERVIEW) -->
        <div v-else class="space-y-8">
            <!-- TAMPILAN KHUSUS ROLE MITRA (PMI, BASARNAS, KSR, DLL.) -->
            <template v-if="isPartner">
                <!-- Banner Profil Lembaga -->
                <div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="space-y-2">
                            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Akun Resmi Lembaga Mitra</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                {{ partner?.name || 'Lembaga Mitra' }}
                            </h2>
                            <p class="text-indigo-200/80 text-sm max-w-2xl leading-relaxed">
                                {{ partner?.notes || 'Selamat datang di panel resmi kemitraan. Kelola profil kelembagaan, struktur kepengurusan, dan personel siaga kebencanaan Anda di sini.' }}
                            </p>
                            <div class="pt-2 flex flex-wrap gap-4 text-xs text-indigo-200">
                                <div class="flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span>Tipe: <strong>{{ partner?.institution_type || 'Organisasi Relawan' }}</strong></span>
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>PIC: <strong>{{ partner?.pic_name || '-' }}</strong></span>
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span>Kontak: <strong>{{ partner?.phone || '-' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Navigation Actions -->
                        <div class="flex flex-row sm:flex-col gap-3 shrink-0">
                            <Link
                                :href="route('partner.profile')"
                                class="px-5 py-2.5 rounded-xl bg-white text-indigo-900 font-bold text-xs sm:text-sm hover:bg-indigo-50 transition shadow-md flex items-center justify-center space-x-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <span>Profil Lembaga</span>
                            </Link>
                            <Link
                                :href="route('partner.members')"
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs sm:text-sm hover:bg-indigo-500 transition shadow-md border border-indigo-400/30 flex items-center justify-center space-x-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span>Pengurus & Anggota</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid Personel Lembaga Mitra -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Personel -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Kekuatan Personel</p>
                                <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ partnerStats?.total || 0 }} Personel</h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Estimasi kesiapan siaga</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Personel Terdaftar -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Terdaftar di Sistem</p>
                                <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ partnerStats?.registered || 0 }} Orang</h3>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                                        {{ partnerStats?.active || 0 }} Aktif
                                    </span>
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Tim Rescue / SAR -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tim Rescue & Evakuasi</p>
                                <h3 class="text-2xl font-bold mt-2 text-rose-600 dark:text-rose-400">{{ partnerStats?.rescue || 0 }} Personel</h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Spesialisasi lapangan</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Medis & Donor Darah -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Medis & Donor Darah</p>
                                <h3 class="text-2xl font-bold mt-2 text-amber-600 dark:text-amber-400">
                                    {{ partnerStats?.medis || 0 }} <span class="text-xs font-normal text-gray-400">Medis</span> / {{ partnerStats?.donor || 0 }} <span class="text-xs font-normal text-gray-400">Donor</span>
                                </h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Kesehatan & transfusi</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BMKG Weather Widget (Kesiapsiagaan Cuaca) -->
                <BmkgWeatherWidget :initialWeather="weatherData" class="mb-8" />

                <!-- Tabel Personel & Pengurus Lembaga Terbaru -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="font-bold text-lg text-gray-950 dark:text-white">Daftar Pengurus & Anggota Lembaga</h3>
                            <p class="text-xs text-gray-400">Personel terdaftar di bawah naungan {{ partner?.name }}</p>
                        </div>
                        <Link :href="route('partner.members')" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            Kelola Semua Anggota →
                        </Link>
                    </div>

                    <div v-if="recentPartnerMembers && recentPartnerMembers.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800">
                                    <th class="pb-3 font-semibold">Nama Personel</th>
                                    <th class="pb-3 font-semibold">Peran / Jabatan</th>
                                    <th class="pb-3 font-semibold">Kontak</th>
                                    <th class="pb-3 font-semibold text-center">Gol. Darah</th>
                                    <th class="pb-3 font-semibold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-for="member in recentPartnerMembers" :key="member.id" class="text-gray-700 dark:text-gray-300">
                                    <td class="py-3.5 font-semibold text-gray-900 dark:text-white">
                                        {{ member.name }}
                                        <div class="text-[10px] text-gray-400">{{ member.email || '-' }}</div>
                                    </td>
                                    <td class="py-3.5">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ member.role }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ member.phone || '-' }}
                                    </td>
                                    <td class="py-3.5 text-center font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ member.blood_type || '-' }}
                                    </td>
                                    <td class="py-3.5 text-center">
                                        <span
                                            :class="[
                                                member.status === 'Aktif'
                                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400'
                                                    : 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400',
                                                'px-2 py-0.5 rounded-full text-xs font-semibold'
                                            ]"
                                        >
                                            {{ member.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-12 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 mx-auto rounded-full bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h4 class="font-bold text-gray-800 dark:text-gray-200 text-sm">Belum Ada Anggota Terdaftar</h4>
                        <p class="text-xs text-gray-400 max-w-sm mx-auto mt-1 mb-4">
                            Daftarkan jajaran pengurus atau personel siaga {{ partner?.name }} ke dalam sistem.
                        </p>
                        <Link
                            :href="route('partner.members')"
                            class="inline-flex items-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition"
                        >
                            <span>+ Tambah Personel Pertama</span>
                        </Link>
                    </div>
                </div>
            </template>

            <!-- TAMPILAN STANDAR INTERNAL YAYASAN MKT (Hanya untuk Pengurus / Internal MKT) -->
            <template v-else>
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Donations Stat -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Penghimpunan Donasi</p>
                                <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ formatIDR(donationStats ? donationStats.total_amount : 0) }}</h3>
                                <div class="flex items-center space-x-2 mt-2">
                                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                                        {{ donationStats ? donationStats.total_count : 0 }} Transaksi
                                    </span>
                                    <span v-if="donationStats && donationStats.pending_count > 0" class="text-xs font-medium text-amber-600 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
                                        {{ donationStats.pending_count }} Pending
                                    </span>
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-950/30 flex items-center justify-center text-brand-600 dark:text-brand-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Volunteers Stat -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Relawan & Anggota</p>
                                <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ volunteerStats ? volunteerStats.total : 0 }} Relawan</h3>
                                <div class="flex items-center space-x-2 mt-2">
                                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                                        {{ volunteerStats ? volunteerStats.active : 0 }} Aktif
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ volunteerStats ? volunteerStats.rescue : 0 }} Rescue
                                    </span>
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-950/30 flex items-center justify-center text-orange-600 dark:text-orange-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Logistics Stat -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Logistik Bencana</p>
                                <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ logisticStats ? logisticStats.total_items : 0 }} Kategori</h3>
                                <div class="flex items-center space-x-2 mt-2">
                                    <span v-if="logisticStats && logisticStats.low_stock > 0" class="text-xs font-medium text-red-600 bg-red-50 dark:bg-red-950/30 dark:text-red-400 px-2 py-0.5 rounded-full">
                                        {{ logisticStats.low_stock }} Stok Menipis
                                    </span>
                                    <span v-else class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                                        Stok Terpenuhi
                                    </span>
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Balance -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Saldo Keuangan Yayasan</p>
                                <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ formatIDR(financialStats ? financialStats.balance : 0) }}</h3>
                                <div class="flex items-center space-x-2 mt-2">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                        Masuk: {{ formatIDR(financialStats ? financialStats.revenue : 0) }}
                                    </span>
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/30 flex items-center justify-center text-teal-600 dark:text-teal-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real-Time BMKG Weather Forecast Widget -->
                <BmkgWeatherWidget :initialWeather="weatherData" class="mb-8" />

                <!-- Quick Activity Logs -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Recent Donations -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm p-6 lg:col-span-2">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-gray-950 dark:text-white">Donasi Terbaru</h3>
                            <Link :href="route('donors.index')" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                                Lihat Semua
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800">
                                        <th class="pb-3 font-semibold">Donatur</th>
                                        <th class="pb-3 font-semibold">Tanggal</th>
                                        <th class="pb-3 font-semibold text-right">Jumlah</th>
                                        <th class="pb-3 font-semibold text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="donation in recentDonations" :key="donation.id" class="text-gray-700 dark:text-gray-300">
                                        <td class="py-3.5">
                                            <div class="font-semibold">{{ donation.donor ? donation.donor.name : 'Hamba Allah' }}</div>
                                            <div class="text-[10px] text-gray-400">{{ donation.payment_method }}</div>
                                        </td>
                                        <td class="py-3.5 text-xs text-gray-400 dark:text-gray-500">
                                            {{ formatDate(donation.donation_date) }}
                                        </td>
                                        <td class="py-3.5 text-right font-bold text-gray-900 dark:text-white">
                                            {{ formatIDR(donation.amount) }}
                                        </td>
                                        <td class="py-3.5 text-center">
                                            <span
                                                :class="[
                                                    donation.status === 'Sukses' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' :
                                                    donation.status === 'Pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' :
                                                    'bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400',
                                                    'px-2 py-1 rounded-full text-xs font-semibold'
                                                ]"
                                            >
                                                {{ donation.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Volunteers -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-gray-950 dark:text-white">Relawan Terdaftar</h3>
                            <Link :href="route('volunteers.index')" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                                Lihat Semua
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="volunteer in recentVolunteers" :key="volunteer.id" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <div class="flex items-center space-x-3 overflow-hidden">
                                    <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-950/30 flex items-center justify-center font-bold text-orange-600 dark:text-orange-400 shrink-0">
                                        {{ volunteer.blood_type || '?' }}
                                    </div>
                                    <div class="truncate">
                                        <h4 class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ volunteer.name }}</h4>
                                        <p class="text-xs text-gray-400 truncate">{{ volunteer.role }}</p>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400 shrink-0">{{ formatDate(volunteer.registered_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Logistics Transactions -->
                <div class="mt-8 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg text-gray-950 dark:text-white">Pergerakan Logistik Bencana</h3>
                        <Link :href="route('logistics.index')" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                            Lihat Semua
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800">
                                    <th class="pb-3 font-semibold">Barang</th>
                                    <th class="pb-3 font-semibold">Kategori</th>
                                    <th class="pb-3 font-semibold">Jenis</th>
                                    <th class="pb-3 font-semibold text-center">Jumlah</th>
                                    <th class="pb-3 font-semibold">Pihak Terkait</th>
                                    <th class="pb-3 font-semibold">Catatan</th>
                                    <th class="pb-3 font-semibold text-right">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-for="tx in recentLogistics" :key="tx.id" class="text-gray-700 dark:text-gray-300">
                                    <td class="py-3 font-semibold">{{ tx.logistic ? tx.logistic.item_name : '-' }}</td>
                                    <td class="py-3 text-xs text-gray-400 dark:text-gray-500">
                                        {{ tx.logistic ? tx.logistic.category : '-' }}
                                    </td>
                                    <td class="py-3">
                                        <span
                                            :class="[
                                                tx.type === 'Masuk' ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 dark:text-emerald-400' :
                                                'text-rose-600 bg-rose-50 dark:bg-rose-950/20 dark:text-rose-400',
                                                'px-2 py-0.5 rounded-full text-xs font-semibold'
                                            ]"
                                        >
                                            {{ tx.type }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center font-semibold">
                                        {{ tx.quantity }} {{ tx.logistic ? tx.logistic.unit : '' }}
                                    </td>
                                    <td class="py-3 font-medium text-xs">{{ tx.recipient_or_donor || '-' }}</td>
                                    <td class="py-3 text-xs text-gray-400 max-w-[200px] truncate" :title="tx.notes">
                                        {{ tx.notes || '-' }}
                                    </td>
                                    <td class="py-3 text-right text-xs text-gray-400 dark:text-gray-500">
                                        {{ formatDate(tx.transaction_date) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
