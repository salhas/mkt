<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    donors: Object,
    donations: Object,
    filters: Object,
});

// Filters for Donors
const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');

// Filters for Donations
const donationSearch = ref(props.filters.donation_search || '');
const donationStatus = ref(props.filters.donation_status || '');

const handleFilterDonors = () => {
    router.get(route('donors.index'), {
        search: search.value,
        type: type.value,
        donation_search: donationSearch.value,
        donation_status: donationStatus.value
    }, { preserveState: true, replace: true });
};

const handleFilterDonations = () => {
    router.get(route('donors.index'), {
        search: search.value,
        type: type.value,
        donation_search: donationSearch.value,
        donation_status: donationStatus.value
    }, { preserveState: true, replace: true });
};

// Modals Setup
const isDonorModalOpen = ref(false);
const editingDonor = ref(null);
const donorForm = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    type: 'Personal',
    status: 'Aktif'
});

const isDonationModalOpen = ref(false);
const editingDonation = ref(null);
const donationForm = useForm({
    donor_id: '',
    amount: '',
    donation_date: new Date().toISOString().split('T')[0],
    payment_method: 'Bank Transfer (BSI)',
    status: 'Sukses',
    description: '',
    reference_number: ''
});

// Donor actions
const openAddDonor = () => {
    editingDonor.value = null;
    donorForm.reset();
    donorForm.clearErrors();
    isDonorModalOpen.value = true;
};

const openEditDonor = (d) => {
    editingDonor.value = d;
    donorForm.name = d.name;
    donorForm.email = d.email;
    donorForm.phone = d.phone;
    donorForm.address = d.address;
    donorForm.type = d.type;
    donorForm.status = d.status;
    donorForm.clearErrors();
    isDonorModalOpen.value = true;
};

const submitDonor = () => {
    if (editingDonor.value) {
        donorForm.patch(route('donors.update', editingDonor.value.id), {
            onSuccess: () => isDonorModalOpen.value = false
        });
    } else {
        donorForm.post(route('donors.store'), {
            onSuccess: () => isDonorModalOpen.value = false
        });
    }
};

// Donation actions
const openAddDonation = () => {
    editingDonation.value = null;
    donationForm.reset();
    donationForm.donation_date = new Date().toISOString().split('T')[0];
    donationForm.clearErrors();
    isDonationModalOpen.value = true;
};

const openEditDonation = (dn) => {
    editingDonation.value = dn;
    donationForm.donor_id = dn.donor_id || '';
    donationForm.amount = dn.amount;
    donationForm.donation_date = dn.donation_date;
    donationForm.payment_method = dn.payment_method;
    donationForm.status = dn.status;
    donationForm.description = dn.description;
    donationForm.reference_number = dn.reference_number;
    donationForm.clearErrors();
    isDonationModalOpen.value = true;
};

const submitDonation = () => {
    if (editingDonation.value) {
        donationForm.patch(route('donations.update', editingDonation.value.id), {
            onSuccess: () => isDonationModalOpen.value = false
        });
    } else {
        donationForm.post(route('donations.store'), {
            onSuccess: () => isDonationModalOpen.value = false
        });
    }
};

// Helpers
const formatIDR = (val) => {
    if (!val) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Donatur & Donasi" />

    <AuthenticatedLayout>
        <template #header>
            <span>Donatur & Donasi</span>
        </template>

        <!-- Dedicated Page Header Section -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Manajemen Donatur & Donasi</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pencatatan Donatur, Transaksi Donasi Masuk, dan Transparansi Dana Kebencanaan</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="openAddDonor"
                    class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 active:scale-95 text-white text-xs font-semibold rounded-xl transition-all shadow-sm flex items-center space-x-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    <span>+ Donatur</span>
                </button>
                <button
                    @click="openAddDonation"
                    class="px-3.5 py-2 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center space-x-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>+ Catat Donasi</span>
                </button>
            </div>
        </div>

        <!-- Main split columns layout -->
        <div class="space-y-8">
            
            <!-- Section A: Donors (Daftar Donatur) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center space-x-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Daftar Donatur</span>
                    </h3>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari donatur..."
                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @input="handleFilterDonors"
                        />
                        <select
                            v-model="type"
                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @change="handleFilterDonors"
                        >
                            <option value="">Semua Jenis</option>
                            <option value="Personal">Personal</option>
                            <option value="Lembaga">Lembaga/Corporate</option>
                        </select>
                        <button
                            @click="openAddDonor"
                            class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20"
                        >
                            + Donatur Baru
                        </button>
                    </div>
                </div>

                <!-- Donors Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800">
                                <th class="pb-3 font-semibold">Nama Donatur</th>
                                <th class="pb-3 font-semibold">Kontak</th>
                                <th class="pb-3 font-semibold">Tipe</th>
                                <th class="pb-3 font-semibold text-center">Donasi Sukses</th>
                                <th class="pb-3 font-semibold text-right">Total Donasi</th>
                                <th class="pb-3 font-semibold text-center">Status</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="d in donors.data" :key="d.id" class="text-gray-700 dark:text-gray-300">
                                <td class="py-3.5">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ d.name }}</div>
                                    <div class="text-xs text-gray-400">{{ d.address || '-' }}</div>
                                </td>
                                <td class="py-3.5 text-xs">
                                    <div>{{ d.email || '-' }}</div>
                                    <div class="text-gray-400">{{ d.phone || '-' }}</div>
                                </td>
                                <td class="py-3.5">
                                    <span :class="[d.type === 'Lembaga' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300', 'px-2 py-0.5 rounded-full text-xs font-medium']">
                                        {{ d.type }}
                                    </span>
                                </td>
                                <td class="py-3.5 text-center font-medium">{{ d.donations_count }}x</td>
                                <td class="py-3.5 text-right font-bold text-gray-900 dark:text-white">
                                    {{ formatIDR(d.donations_sum_amount) }}
                                </td>
                                <td class="py-3.5 text-center">
                                    <span :class="[d.status === 'Aktif' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400', 'px-2 py-0.5 rounded-full text-xs font-semibold']">
                                        {{ d.status }}
                                    </span>
                                </td>
                                <td class="py-3.5 text-right">
                                    <button
                                        @click="openEditDonor(d)"
                                        class="p-1 rounded-lg text-gray-400 hover:text-brand-500 hover:bg-brand-50/50 dark:hover:bg-brand-950/20 transition-all focus:outline-none"
                                        title="Edit Donatur"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="donors.data.length === 0">
                                <td colspan="7" class="py-6 text-center text-gray-400 italic">
                                    Tidak ada data donatur ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Donors Pagination -->
                <div v-if="donors.links.length > 3" class="mt-4 flex items-center justify-between">
                    <div class="text-xs text-gray-400">Total: {{ donors.total }} donatur</div>
                    <div class="flex items-center space-x-1">
                        <template v-for="(link, key) in donors.links" :key="key">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[link.active ? 'bg-brand-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800', 'px-2.5 py-1 text-xs rounded-lg transition-colors']"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Section B: Donation Ledger (Buku Catatan Donasi) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center space-x-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span>Buku Catatan Donasi Masuk</span>
                    </h3>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            v-model="donationSearch"
                            type="text"
                            placeholder="Cari transaksi / donatur..."
                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @input="handleFilterDonations"
                        />
                        <select
                            v-model="donationStatus"
                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @change="handleFilterDonations"
                        >
                            <option value="">Semua Status</option>
                            <option value="Sukses">Sukses</option>
                            <option value="Pending">Pending</option>
                            <option value="Gagal">Gagal</option>
                        </select>
                        <button
                            @click="openAddDonation"
                            class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20"
                        >
                            + Catat Donasi
                        </button>
                    </div>
                </div>

                <!-- Donations Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800">
                                <th class="pb-3 font-semibold">Ref / Tanggal</th>
                                <th class="pb-3 font-semibold">Donatur</th>
                                <th class="pb-3 font-semibold">Metode Bayar</th>
                                <th class="pb-3 font-semibold">Keterangan</th>
                                <th class="pb-3 font-semibold text-right">Jumlah</th>
                                <th class="pb-3 font-semibold text-center">Status</th>
                                <th class="pb-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="dn in donations.data" :key="dn.id" class="text-gray-700 dark:text-gray-300">
                                <td class="py-3.5 text-xs">
                                    <span class="font-mono font-semibold block">{{ dn.reference_number }}</span>
                                    <span class="text-gray-400">{{ formatDate(dn.donation_date) }}</span>
                                </td>
                                <td class="py-3.5">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ dn.donor ? dn.donor.name : 'Hamba Allah (Anonim)' }}</span>
                                </td>
                                <td class="py-3.5 text-xs">{{ dn.payment_method }}</td>
                                <td class="py-3.5 text-xs text-gray-400 max-w-[200px] truncate" :title="dn.description">
                                    {{ dn.description || '-' }}
                                </td>
                                <td class="py-3.5 text-right font-bold text-gray-900 dark:text-white">
                                    {{ formatIDR(dn.amount) }}
                                </td>
                                <td class="py-3.5 text-center">
                                    <span :class="[
                                        dn.status === 'Sukses' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' :
                                        dn.status === 'Pending' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' :
                                        'bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400',
                                        'px-2 py-0.5 rounded-full text-xs font-semibold'
                                    ]">
                                        {{ dn.status }}
                                    </span>
                                </td>
                                <td class="py-3.5 text-right">
                                    <button
                                        @click="openEditDonation(dn)"
                                        class="p-1 rounded-lg text-gray-400 hover:text-brand-500 hover:bg-brand-50/50 dark:hover:bg-brand-950/20 transition-all focus:outline-none"
                                        title="Edit Transaksi"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="donations.data.length === 0">
                                <td colspan="7" class="py-6 text-center text-gray-400 italic">
                                    Tidak ada data donasi ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Donations Pagination -->
                <div v-if="donations.links.length > 3" class="mt-4 flex items-center justify-between">
                    <div class="text-xs text-gray-400">Total: {{ donations.total }} donasi</div>
                    <div class="flex items-center space-x-1">
                        <template v-for="(link, key) in donations.links" :key="key">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[link.active ? 'bg-brand-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800', 'px-2.5 py-1 text-xs rounded-lg transition-colors']"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal A: Add/Edit Donor -->
        <div v-if="isDonorModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
                <div class="h-14 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-brand-50/30 dark:bg-brand-950/10">
                    <span class="font-bold text-gray-900 dark:text-white">
                        {{ editingDonor ? 'Edit Data Donatur' : 'Tambah Donatur Baru' }}
                    </span>
                    <button @click="isDonorModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitDonor" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Donatur</label>
                        <input v-model="donorForm.name" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Email</label>
                            <input v-model="donorForm.email" type="email" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Telepon</label>
                            <input v-model="donorForm.phone" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tipe</label>
                            <select v-model="donorForm.type" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required>
                                <option value="Personal">Personal</option>
                                <option value="Lembaga">Lembaga</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status</label>
                            <select v-model="donorForm.status" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Alamat</label>
                        <textarea v-model="donorForm.address" rows="2" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isDonorModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 rounded-xl text-sm font-semibold hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="donorForm.processing" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-sm font-semibold shadow-md">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal B: Add/Edit Donation -->
        <div v-if="isDonationModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
                <div class="h-14 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-brand-50/30 dark:bg-brand-950/10">
                    <span class="font-bold text-gray-900 dark:text-white">
                        {{ editingDonation ? 'Edit Catatan Donasi' : 'Catat Donasi Masuk' }}
                    </span>
                    <button @click="isDonationModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitDonation" class="p-6 space-y-4">
                    <!-- Link Donor -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Donatur (Kosongkan jika Anonim/Hamba Allah)</label>
                        <select v-model="donationForm.donor_id" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">Hamba Allah / Anonim</option>
                            <option v-for="d in donors.data" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>

                    <!-- Amount & Date -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jumlah Donasi (Rupiah)</label>
                            <input v-model="donationForm.amount" type="number" step="0.01" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal</label>
                            <input v-model="donationForm.donation_date" type="date" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>

                    <!-- Method & Status -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Metode Pembayaran</label>
                            <input v-model="donationForm.payment_method" type="text" placeholder="BSI, BCA, OVO, Cash..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status</label>
                            <select v-model="donationForm.status" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required>
                                <option value="Sukses">Sukses</option>
                                <option value="Pending">Pending</option>
                                <option value="Gagal">Gagal</option>
                            </select>
                        </div>
                    </div>

                    <!-- Ref Number -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nomor Referensi (Kosongkan untuk auto-generate)</label>
                        <input v-model="donationForm.reference_number" type="text" placeholder="TX-XXXXXXXX-XX" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pesan / Deskripsi Donasi</label>
                        <textarea v-model="donationForm.description" rows="2" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isDonationModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 rounded-xl text-sm font-semibold hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="donationForm.processing" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-sm font-semibold shadow-md">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
