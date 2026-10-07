<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    partner: Object,
    members: Object,
    stats: Object,
    roles: Array,
    filters: Object,
});

// Search & Filter State
const search = ref(props.filters?.search || '');
const selectedRole = ref(props.filters?.role || 'Semua');
const selectedStatus = ref(props.filters?.status || 'Semua');
const selectedBloodType = ref(props.filters?.blood_type || 'Semua');

const applyFilter = () => {
    router.get(route('partner.members'), {
        search: search.value || undefined,
        role: selectedRole.value !== 'Semua' ? selectedRole.value : undefined,
        status: selectedStatus.value !== 'Semua' ? selectedStatus.value : undefined,
        blood_type: selectedBloodType.value !== 'Semua' ? selectedBloodType.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    selectedRole.value = 'Semua';
    selectedStatus.value = 'Semua';
    selectedBloodType.value = 'Semua';
    applyFilter();
};

// Modal State
const showModal = ref(false);
const isEditing = ref(false);
const editingMemberId = ref(null);

const form = useForm({
    name: '',
    role: 'Anggota Personel',
    blood_type: 'O',
    phone: '',
    email: '',
    address: '',
    certifications: '',
    status: 'Aktif',
    notes: '',
});

const openAddModal = () => {
    isEditing.value = false;
    editingMemberId.value = null;
    form.reset();
    form.clearErrors();
    form.role = 'Anggota Personel';
    form.blood_type = 'O';
    form.status = 'Aktif';
    showModal.value = true;
};

const openEditModal = (member) => {
    isEditing.value = true;
    editingMemberId.value = member.id;
    form.clearErrors();
    form.name = member.name;
    form.role = member.role || 'Anggota Personel';
    form.blood_type = member.blood_type || 'O';
    form.phone = member.phone || '';
    form.email = member.email || '';
    form.address = member.address || '';
    form.certifications = member.certifications || '';
    form.status = member.status || 'Aktif';
    form.notes = member.notes || '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.patch(route('partner.members.update', editingMemberId.value), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        form.post(route('partner.members.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const confirmDelete = (member) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data anggota "${member.name}" dari lembaga ${props.partner?.name}?`)) {
        router.delete(route('partner.members.destroy', member.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Pengurus & Anggota Lembaga" />

    <AuthenticatedLayout>
        <template #header>
            <span>Pengurus & Anggota</span>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl font-bold shrink-0 shadow-sm border border-blue-100 dark:border-blue-900/40">
                        👥
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300">
                                {{ partner?.name }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight mt-0.5">
                            Pengurus & Anggota Lembaga
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Manajemen mandiri personel, tim SAR, dan relawan donor darah khusus naungan lembaga Anda.
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3 shrink-0">
                    <button
                        @click="openAddModal"
                        class="px-5 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-500/25 active:scale-95 transition-all flex items-center space-x-2"
                    >
                        <span>➕</span>
                        <span>Tambah Anggota / Pengurus</span>
                    </button>
                </div>
            </div>

            <!-- Stats Grid Lembaga -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5 sm:gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-4 rounded-2xl shadow-sm">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Personel</span>
                    <span class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 block">{{ stats?.total || 0 }}</span>
                    <span class="text-[10px] text-gray-500">{{ stats?.registered || 0 }} Terdaftar</span>
                </div>
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-4 rounded-2xl shadow-sm">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Personel Aktif</span>
                    <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ stats?.active || 0 }}</span>
                    <span class="text-[10px] text-emerald-600 font-semibold">Siaga Bencana</span>
                </div>
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-4 rounded-2xl shadow-sm">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Tim Rescue SAR</span>
                    <span class="text-xl sm:text-2xl font-black text-orange-600 dark:text-orange-400 mt-1 block">{{ stats?.rescue || 0 }}</span>
                    <span class="text-[10px] text-gray-500">Kualifikasi Evakuasi</span>
                </div>
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-4 rounded-2xl shadow-sm">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Donor Darah</span>
                    <span class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block">{{ stats?.donor || 0 }}</span>
                    <span class="text-[10px] text-gray-500">Pendonor Siaga</span>
                </div>
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-4 rounded-2xl shadow-sm col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Tenaga Medis</span>
                    <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1 block">{{ stats?.medis || 0 }}</span>
                    <span class="text-[10px] text-gray-500">P3K & Tim Kesehatan</span>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <div class="flex-1 relative">
                    <input
                        v-model="search"
                        @keydown.enter="applyFilter"
                        type="text"
                        placeholder="Cari nama anggota, no telepon, email, keahlian..."
                        class="w-full pl-9 pr-4 py-2 rounded-xl text-xs sm:text-sm border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500"
                    />
                    <svg class="w-4 h-4 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="selectedRole"
                        @change="applyFilter"
                        class="rounded-xl text-xs border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 py-2"
                    >
                        <option v-for="r in roles" :key="r" :value="r">{{ r === 'Semua' ? 'Semua Peran' : r }}</option>
                    </select>

                    <select
                        v-model="selectedBloodType"
                        @change="applyFilter"
                        class="rounded-xl text-xs border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 py-2"
                    >
                        <option value="Semua">Semua Gol. Darah</option>
                        <option value="A">Gol. Darah A</option>
                        <option value="B">Gol. Darah B</option>
                        <option value="AB">Gol. Darah AB</option>
                        <option value="O">Gol. Darah O</option>
                    </select>

                    <select
                        v-model="selectedStatus"
                        @change="applyFilter"
                        class="rounded-xl text-xs border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 py-2"
                    >
                        <option value="Semua">Semua Status</option>
                        <option value="Aktif">Status Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>

                    <button
                        @click="resetFilter"
                        class="px-3 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Tabel Daftar Anggota -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300 font-bold uppercase text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-6">Nama Anggota</th>
                                <th class="py-3.5 px-4">Peran / Posisi</th>
                                <th class="py-3.5 px-4">Gol. Darah</th>
                                <th class="py-3.5 px-4">Kontak WhatsApp / Email</th>
                                <th class="py-3.5 px-4">Keahlian & Sertifikasi</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-if="members.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <span class="text-3xl block mb-2">👤</span>
                                    Belum ada data anggota untuk lembaga ini. Silakan klik tombol "+ Tambah Anggota / Pengurus" di atas.
                                </td>
                            </tr>
                            <tr
                                v-for="m in members.data"
                                :key="m.id"
                                class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors"
                            >
                                <td class="py-4 px-4 sm:px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                            {{ m.name ? m.name.charAt(0) : 'A' }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-900 dark:text-white block">{{ m.name }}</span>
                                            <span class="text-[10px] text-gray-400">{{ m.address || 'Alamat belum diisi' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900/40">
                                        {{ m.role }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40">
                                        🩸 {{ m.blood_type || '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-xs">
                                    <div class="space-y-0.5">
                                        <div class="font-semibold text-gray-800 dark:text-gray-200">{{ m.phone }}</div>
                                        <div class="text-[11px] text-gray-400">{{ m.email || '-' }}</div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-xs">
                                    <span v-if="m.certifications" class="text-gray-700 dark:text-gray-300 font-medium">
                                        {{ m.certifications }}
                                    </span>
                                    <span v-else class="text-gray-400 italic text-[11px]">-</span>
                                </td>
                                <td class="py-4 px-4">
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                        :class="m.status === 'Aktif' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                                    >
                                        {{ m.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-right">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <button
                                            @click="openEditModal(m)"
                                            class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition"
                                            title="Edit Data Anggota"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            @click="confirmDelete(m)"
                                            class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                                            title="Hapus Anggota"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="members.links && members.links.length > 3" class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-xs">
                    <span class="text-gray-500">
                        Menampilkan {{ members.from || 0 }} - {{ members.to || 0 }} dari {{ members.total }} anggota
                    </span>
                    <div class="flex items-center space-x-1">
                        <Link
                            v-for="(link, i) in members.links"
                            :key="i"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                link.active ? 'bg-blue-600 text-white font-bold' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800',
                                !link.url ? 'opacity-40 pointer-events-none' : '',
                                'px-3 py-1.5 rounded-lg transition'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL FORM TAMBAH / EDIT ANGGOTA -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
            @click.self="closeModal"
        >
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-2xl p-6 sm:p-7 space-y-4 max-h-[92vh] overflow-y-auto animate-scaleUp">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                            {{ partner?.name }}
                        </span>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white">
                            {{ isEditing ? 'Edit Data Anggota / Pengurus' : 'Tambah Anggota / Pengurus Baru' }}
                        </h3>
                    </div>
                    <button @click="closeModal" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Nama Lengkap Personel <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Contoh: Rian Pratama"
                            class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                        />
                        <span v-if="form.errors.name" class="text-rose-500 text-xs mt-1 block">{{ form.errors.name }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Peran / Posisi di Lembaga <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.role"
                                required
                                class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                            >
                                <option value="Ketua / Koordinator Lembaga">Ketua / Koordinator Lembaga</option>
                                <option value="Sekretaris Lembaga">Sekretaris Lembaga</option>
                                <option value="Bendahara Lembaga">Bendahara Lembaga</option>
                                <option value="Koordinator Lapangan SAR">Koordinator Lapangan SAR</option>
                                <option value="Tim Rescue">Tim Rescue / Evakuasi</option>
                                <option value="Tenaga Medis">Tenaga Medis / Kesehatan</option>
                                <option value="Donor Darah">Donor Darah Siaga</option>
                                <option value="Anggota Personel">Anggota Personel</option>
                                <option value="Relawan Rescuer">Relawan Rescuer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Golongan Darah <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.blood_type"
                                required
                                class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                            >
                                <option value="A">Golongan Darah A</option>
                                <option value="B">Golongan Darah B</option>
                                <option value="AB">Golongan Darah AB</option>
                                <option value="O">Golongan Darah O</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                No. WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.phone"
                                type="text"
                                required
                                placeholder="+62 812..."
                                class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Alamat Email (Opsional)
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="nama@email.com"
                                class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Keahlian / Sertifikasi Khusus
                        </label>
                        <input
                            v-model="form.certifications"
                            type="text"
                            placeholder="Contoh: Water Rescue Basarnas, Sertifikasi P3K Darurat, Vertical Rescue"
                            class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Alamat Domisili
                        </label>
                        <input
                            v-model="form.address"
                            type="text"
                            placeholder="Domisili tempat tinggal..."
                            class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Status Keaktifan
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                            >
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Catatan Tambahan
                            </label>
                            <input
                                v-model="form.notes"
                                type="text"
                                placeholder="Keterangan..."
                                class="w-full rounded-2xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-3.5"
                            />
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end space-x-2">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 active:scale-95 transition flex items-center space-x-1.5 disabled:opacity-70"
                        >
                            <span>{{ form.processing ? 'Menyimpan...' : (isEditing ? 'Perbarui Anggota' : 'Simpan Anggota') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
