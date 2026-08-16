<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BmkgWeatherWidget from '@/Components/BmkgWeatherWidget.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
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
            <span>Dashboard</span>
        </template>

        <!-- Dedicated Page Header Section -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Dashboard Operational Hub</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ringkasan Penanggulangan Bencana, Donasi, Relawan & Logistik MKT Indonesia</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                    Sistem Operasional Aktif
                </span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Donations Stat -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Penghimpunan Donasi</p>
                        <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ formatIDR(donationStats.total_amount) }}</h3>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                                {{ donationStats.total_count }} Transaksi
                            </span>
                            <span v-if="donationStats.pending_count > 0" class="text-xs font-medium text-amber-600 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
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
                        <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ volunteerStats.total }} Relawan</h3>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                                {{ volunteerStats.active }} Aktif
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ volunteerStats.rescue }} Rescue
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
                        <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ logisticStats.total_items }} Kategori</h3>
                        <div class="flex items-center space-x-2 mt-2">
                            <span v-if="logisticStats.low_stock > 0" class="text-xs font-medium text-red-600 bg-red-50 dark:bg-red-950/30 dark:text-red-400 px-2 py-0.5 rounded-full">
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
                        <h3 class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ formatIDR(financialStats.balance) }}</h3>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                Masuk: {{ formatIDR(financialStats.revenue) }}
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
    </AuthenticatedLayout>
</template>
