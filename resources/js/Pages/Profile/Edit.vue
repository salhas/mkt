<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Active tab: 'info' | 'password' | 'security'
const activeTab = ref('info');
</script>

<template>
    <Head title="Pengaturan Akun" />

    <AuthenticatedLayout>
        <template #header>
            <span>Pengaturan Akun</span>
        </template>

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Dedicated Page Header Section -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Pengaturan Akun Pengguna</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola Informasi Profil, Kata Sandi, dan Keamanan Akun Anda</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        Keamanan & Akun
                    </span>
                </div>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="bg-gray-100/80 dark:bg-gray-800/60 p-1.5 rounded-2xl flex items-center space-x-1 border border-gray-200/60 dark:border-gray-700/60">
                <button
                    @click="activeTab = 'info'"
                    type="button"
                    :class="[
                        activeTab === 'info'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center space-x-2'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Informasi Profil Pengguna</span>
                </button>

                <button
                    @click="activeTab = 'password'"
                    type="button"
                    :class="[
                        activeTab === 'password'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center space-x-2'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Ubah Kata Sandi</span>
                </button>

                <button
                    @click="activeTab = 'security'"
                    type="button"
                    :class="[
                        activeTab === 'security'
                            ? 'bg-white dark:bg-gray-900 text-rose-600 dark:text-rose-400 shadow-md font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-800/50 font-medium',
                        'px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center space-x-2'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>Hapus Akun</span>
                </button>
            </div>

            <!-- Tab 1: Profile Info -->
            <div v-show="activeTab === 'info'" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 shadow-sm rounded-3xl">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <!-- Tab 2: Update Password -->
            <div v-show="activeTab === 'password'" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 shadow-sm rounded-3xl">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <!-- Tab 3: Security / Delete Account -->
            <div v-show="activeTab === 'security'" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 shadow-sm rounded-3xl">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
