<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { showSuccessToast, showErrorToast } from '@/Utils/toast.js';

const props = defineProps({
    users: Object,
    rolesList: Array,
    filters: Object,
});

// Filters
const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || 'Semua');

const handleFilter = () => {
    router.get(route('users.index'), {
        search: search.value,
        role: roleFilter.value
    }, { preserveState: true, replace: true });
};

// Modal Control
const isModalOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    role: 'staff',
});

const openAddModal = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    form.role = 'staff';
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.clearErrors();
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.role;
    isModalOpen.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.patch(route('users.update', editingUser.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                showSuccessToast('Data pengguna berhasil diperbarui!');
            },
            onError: () => showErrorToast('Gagal memperbarui pengguna.')
        });
    } else {
        form.post(route('users.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
                showSuccessToast('Pengguna baru berhasil ditambahkan!');
            },
            onError: () => showErrorToast('Gagal menambahkan pengguna.')
        });
    }
};

const deleteUser = (user) => {
    if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${user.name}" (${user.email})?`)) {
        router.delete(route('users.destroy', user.id), {
            onSuccess: () => showSuccessToast('Pengguna berhasil dihapus.'),
            onError: (errs) => showErrorToast(errs.delete || 'Gagal menghapus pengguna.')
        });
    }
};

const getRoleBadge = (role) => {
    switch (role) {
        case 'webmaster':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300 border-purple-300';
        case 'administrator':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border-rose-300';
        case 'finance':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-300';
        case 'staff':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 border-blue-300';
        case 'relawan':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border-amber-300';
        case 'mitra':
            return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-300 border-cyan-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-300';
    }
};

const getRoleLabel = (role) => {
    switch (role) {
        case 'webmaster': return 'Super Webmaster';
        case 'administrator': return 'Administrator MKT';
        case 'finance': return 'Finance / Keuangan';
        case 'staff': return 'Staff Operasional';
        case 'relawan': return 'Relawan / Rescuer';
        case 'mitra': return 'Instansi Mitra';
        case 'donatur': return 'Donatur';
        case 'medis': return 'Dokter & Tim Medis';
        default: return role;
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Manajemen Pengguna (User Access)" />

    <AuthenticatedLayout>
        <template #header>
            <span>Manajemen Pengguna</span>
        </template>

        <!-- Header Banner -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Manajemen Pengguna MKT</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pengaturan Akun & Hak Akses User (Administrator, Finance, Staff, Relawan, Mitra)</p>
                </div>
            </div>
            <button
                @click="openAddModal"
                class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center space-x-1.5 self-start md:self-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Tambah User Baru</span>
            </button>
        </div>

        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Cari Nama / Email</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Ketik nama atau email..."
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                        @input="handleFilter"
                    />
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Filter Peran / Role</label>
                    <select
                        v-model="roleFilter"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                        @change="handleFilter"
                    >
                        <option value="Semua">Semua Role</option>
                        <option v-for="r in rolesList" :key="r" :value="r">{{ getRoleLabel(r) }}</option>
                    </select>
                </div>
                <div>
                    <button
                        @click="router.get(route('users.index'))"
                        class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-semibold rounded-xl transition-all w-full"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800 uppercase text-[10px] font-black tracking-wider bg-gray-50/50 dark:bg-gray-900/50">
                            <th class="p-4 font-semibold">Pengguna</th>
                            <th class="p-4 font-semibold">Alamat Email</th>
                            <th class="p-4 font-semibold">Peran / Role</th>
                            <th class="p-4 font-semibold">Terdaftar Pada</th>
                            <th class="p-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-orange-400 to-amber-500 flex items-center justify-center font-bold text-white shrink-0">
                                        {{ u.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ u.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 font-mono text-xs text-gray-600 dark:text-gray-400">{{ u.email }}</td>
                            <td class="p-4">
                                <span :class="[getRoleBadge(u.role), 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border']">
                                    {{ getRoleLabel(u.role) }}
                                </span>
                            </td>
                            <td class="p-4 text-xs text-gray-400">{{ formatDate(u.created_at) }}</td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button
                                        @click="openEditModal(u)"
                                        class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg text-xs font-bold transition-all"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteUser(u)"
                                        class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                        title="Hapus User"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400 italic">
                                Belum ada data pengguna yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links && users.links.length > 3" class="px-4 py-3 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div class="text-xs text-gray-400">Total: {{ users.total }} akun</div>
                <div class="flex items-center space-x-1">
                    <template v-for="(link, key) in users.links" :key="key">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[link.active ? 'bg-brand-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800', 'px-3 py-1.5 text-xs rounded-lg transition-colors font-medium']"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden transform transition-all">
                <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-brand-50/40 dark:bg-brand-950/20">
                    <span class="font-bold text-gray-900 dark:text-white">
                        {{ editingUser ? 'Edit Pengguna MKT' : 'Tambah Pengguna MKT Baru' }}
                    </span>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Lengkap</label>
                        <input v-model="form.name" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs focus:border-brand-500 focus:outline-none" placeholder="Masukkan nama..." required />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Alamat Email</label>
                        <input v-model="form.email" type="email" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs focus:border-brand-500 focus:outline-none" placeholder="email@mkt.or.id" required />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Peran / Role Akses System</label>
                        <select v-model="form.role" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs focus:border-brand-500 focus:outline-none" required>
                            <option value="administrator">Administrator MKT (Akses Penuh Manajemen & Finance)</option>
                            <option value="finance">Finance / Divisi Keuangan (CRUD Keuangan & COA)</option>
                            <option value="staff">Staff Operasional (Read-Only Keuangan)</option>
                            <option value="webmaster">Super Webmaster</option>
                            <option value="relawan">Tim Rescue & Relawan</option>
                            <option value="mitra">Instansi Mitra (PMI/Basarnas/BPBD/RS)</option>
                            <option value="medis">Dokter & Tim Medis</option>
                            <option value="donatur">Donatur</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">
                            {{ editingUser ? 'Kata Sandi Baru (Kosongkan jika tidak diubah)' : 'Kata Sandi' }}
                        </label>
                        <input v-model="form.password" type="password" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs focus:border-brand-500 focus:outline-none" :required="!editingUser" placeholder="Minimal 6 karakter" />
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 rounded-xl text-xs font-semibold hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">
                            {{ editingUser ? 'Simpan Perubahan' : 'Tambah User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
