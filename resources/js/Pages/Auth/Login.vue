<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import MktLogo from '@/Components/MktLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

import loginBannerImg from '../../../images/login_banner.jpg';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const getAsset = (path) => {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
        return path;
    }
    const clean = path.startsWith('/') ? path.slice(1) : path;
    if (typeof window !== 'undefined') {
        const p = window.location.pathname;
        if (p.includes('/public')) {
            const base = p.substring(0, p.indexOf('/public') + 7) + '/';
            return base + clean;
        }
    }
    return '/' + clean;
};

const isDarkMode = ref(false);
const currentLang = ref('id');
const activeRole = ref('webmaster');

const form = useForm({
    email: 'webmaster@mkt.or.id',
    password: 'password123',
    remember: false,
});

onMounted(() => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
    const savedLang = localStorage.getItem('app_lang');
    if (savedLang) {
        currentLang.value = savedLang;
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

const toggleLang = (lang) => {
    currentLang.value = lang;
    localStorage.setItem('app_lang', lang);
};

const roles = [
    {
        key: 'webmaster',
        nameId: 'Webmaster',
        nameEn: 'Webmaster',
        email: 'webmaster@mkt.or.id',
        badge: 'Super Admin',
        badgeEn: 'Super Admin',
        descId: 'Akses penuh seluruh modul sistem & konfigurasi platform',
        descEn: 'Full system module & platform configuration access',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        color: 'from-purple-500 to-indigo-600',
        bgSoft: 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30'
    },
    {
        key: 'administrator',
        nameId: 'Admin MKT',
        nameEn: 'Administrator',
        email: 'administrator@mkt.or.id',
        badge: 'Manajemen',
        badgeEn: 'Management',
        descId: 'Manajemen pengguna, operasional, & otorisasi penuh data keuangan',
        descEn: 'User management, operations & full financial authorization',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        color: 'from-rose-500 to-red-600',
        bgSoft: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30'
    },
    {
        key: 'finance',
        nameId: 'Finance',
        nameEn: 'Finance',
        email: 'finance@mkt.or.id',
        badge: 'Keuangan',
        badgeEn: 'Accounting',
        descId: 'Hak akses CRUD Keuangan, COA Kode Akun, Jurnal, Buku Besar, & Neraca',
        descEn: 'Full Finance CRUD access, Chart of Accounts, Journal, & Balance Sheet',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'from-emerald-500 to-teal-600',
        bgSoft: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30'
    },
    {
        key: 'staff',
        nameId: 'Staff',
        nameEn: 'Staff',
        email: 'staff@mkt.or.id',
        badge: 'Operasional',
        badgeEn: 'Operational',
        descId: 'Operasional kegiatan & Read-Only (hanya lihat) data keuangan',
        descEn: 'Operational activities & Read-Only access to financial data',
        icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        color: 'from-blue-500 to-sky-600',
        bgSoft: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/30'
    },
    {
        key: 'mitra',
        nameId: 'Mitra',
        nameEn: 'Partner',
        email: 'mitra@mkt.or.id',
        badge: 'Basarnas / BPBD',
        badgeEn: 'Agencies',
        descId: 'Koordinasi Basarnas, BPBD, PMI, RS & rescue bersama',
        descEn: 'Basarnas, BPBD, PMI, Hospital & rescue joint coordination',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        color: 'from-cyan-500 to-blue-600',
        bgSoft: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/30'
    },
    {
        key: 'relawan',
        nameId: 'Relawan',
        nameEn: 'Volunteer',
        email: 'relawan@mkt.or.id',
        badge: 'Tim Rescue',
        badgeEn: 'Rescue Team',
        descId: 'Aktivasi giat evakuasi darurat & pendonor darah aktif',
        descEn: 'Emergency rescue activation & active blood donor',
        icon: 'M13 10V3L4 14h7v7l9-11h-7z',
        color: 'from-amber-500 to-orange-600',
        bgSoft: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30'
    },
    {
        key: 'medis',
        nameId: 'Medis',
        nameEn: 'Medical',
        email: 'medis@mkt.or.id',
        badge: 'Dokter / Nakes',
        badgeEn: 'Healthcare',
        descId: 'Pertolongan medis darurat & penyediaan posko kesehatan',
        descEn: 'Emergency medical aid & health post setup',
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        color: 'from-emerald-500 to-teal-600',
        bgSoft: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30'
    },
    {
        key: 'donatur',
        nameId: 'Donatur',
        nameEn: 'Donor',
        email: 'donatur@mkt.or.id',
        badge: 'Filantropis',
        badgeEn: 'Philanthropist',
        descId: 'Penyaluran donasi publik & pantau transparansi dana',
        descEn: 'Public donation allocation & fund transparency tracking',
        icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        color: 'from-pink-500 to-rose-600',
        bgSoft: 'bg-pink-500/10 text-pink-600 dark:text-pink-400 border-pink-500/30'
    }
];

const selectQuickRole = (roleItem) => {
    activeRole.value = roleItem.key;
    form.email = roleItem.email;
    form.password = 'password123';
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const translations = {
    id: {
        title: 'Login Sistem MKT',
        welcome: 'Selamat Datang Kembali',
        subtitle: 'Silakan pilih role untuk Login Cepat atau masukkan kredensial akun Anda.',
        quickLoginTitle: '⚡ Akses Login Cepat Sesuai Role Pengguna MKT:',
        emailLabel: 'Alamat Email',
        passwordLabel: 'Kata Sandi',
        forgotPassword: 'Lupa Sandi?',
        rememberMe: 'Ingat Saya',
        loginButton: 'Masuk Ke Dashboard',
        loggingIn: 'Memproses Login...',
        backToLanding: 'Kembali ke Halaman Utama',
        foundationTag: 'Yayasan Mitra Kemanusiaan Terpadu',
        bannerHeader: 'Ekosistem Kebencanaan Tangguh & Terintegrasi',
        bannerSub: 'Sistem operasional penanganan prabencana, tanggap darurat rescue, donor darah, hingga akuntansi laporan keuangan filantropi.'
    },
    en: {
        title: 'MKT System Login',
        welcome: 'Welcome Back',
        subtitle: 'Select a role for Quick Login or enter your account credentials below.',
        quickLoginTitle: '⚡ Quick Role Login Access:',
        emailLabel: 'Email Address',
        passwordLabel: 'Password',
        forgotPassword: 'Forgot Password?',
        rememberMe: 'Remember Me',
        loginButton: 'Sign In To Dashboard',
        loggingIn: 'Processing Login...',
        backToLanding: 'Back to Main Landing Page',
        foundationTag: 'Integrated Humanitarian Partner Foundation',
        bannerHeader: 'Resilient & Integrated Disaster Management Ecosystem',
        bannerSub: 'Operational system covering pre-disaster mitigation, emergency rescue, blood donation, and transparent financial accounting.'
    }
};
</script>

<template>
    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950 transition-colors duration-300 font-sans selection:bg-orange-500 selection:text-white">
        <Head :title="translations[currentLang].title" />

        <!-- Split Layout -->
        <div class="flex-1 flex flex-col lg:flex-row w-full min-h-screen">
            
            <!-- Left Panel: Visual Banner & Branding (Hidden on small screens) -->
            <div class="hidden lg:flex lg:w-5/12 bg-slate-900 relative overflow-hidden flex-col justify-between p-12 text-white">
                <!-- Background Image overlay -->
                <div class="absolute inset-0 bg-cover bg-center opacity-40 scale-105 transition-transform duration-1000" :style="{ backgroundImage: 'url(' + loginBannerImg + ')' }"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-900/40"></div>

                <!-- Top Brand Logo -->
                <div class="relative z-10 flex items-center">
                    <MktLogo variant="full" icon-size="w-12 h-12" text-size="text-xl" :show-subtitle="true" />
                </div>

                <!-- Center Banner Content -->
                <div class="relative z-10 my-auto py-12 max-w-md">
                    <span class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-orange-500/20 text-orange-300 border border-orange-500/30 mb-4 backdrop-blur-md">
                        <span>🛡️ {{ translations[currentLang].foundationTag }}</span>
                    </span>
                    <h2 class="text-3xl xl:text-4xl font-black text-white leading-tight">
                        {{ translations[currentLang].bannerHeader }}
                    </h2>
                    <p class="text-slate-300 text-sm mt-4 leading-relaxed font-normal">
                        {{ translations[currentLang].bannerSub }}
                    </p>
                </div>

                <!-- Bottom Footer Meta -->
                <div class="relative z-10 text-xs text-slate-400 border-t border-slate-800/80 pt-6 flex justify-between items-center">
                    <span>&copy; 2026 Yayasan MKT Indonesia</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300">v2.5 Release</span>
                </div>
            </div>

            <!-- Right Panel: Login Form & Quick Role Selection -->
            <div class="flex-1 lg:w-7/12 flex flex-col justify-between p-6 sm:p-10 lg:p-12 relative bg-white dark:bg-slate-950 transition-colors">
                
                <!-- Header Controls: Theme & Language Switchers -->
                <div class="flex items-center justify-between lg:justify-end space-x-3 w-full mb-6">
                    <!-- Mobile Logo -->
                    <div class="flex items-center space-x-2 lg:hidden">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-orange-500 to-amber-400 flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <span class="font-black text-base text-slate-900 dark:text-white">MKT Indonesia</span>
                    </div>

                    <div class="flex items-center space-x-3 ml-auto">
                        <!-- Language Switcher -->
                        <div class="flex items-center bg-slate-100 dark:bg-slate-900 p-1 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold">
                            <button
                                @click="toggleLang('id')"
                                class="px-2.5 py-1 rounded-lg transition-all"
                                :class="currentLang === 'id' ? 'bg-white dark:bg-slate-800 text-orange-600 dark:text-orange-400 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                            >
                                🇮🇩 ID
                            </button>
                            <button
                                @click="toggleLang('en')"
                                class="px-2.5 py-1 rounded-lg transition-all"
                                :class="currentLang === 'en' ? 'bg-white dark:bg-slate-800 text-orange-600 dark:text-orange-400 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                            >
                                🇬🇧 EN
                            </button>
                        </div>

                        <!-- Theme Toggle -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 rounded-xl text-slate-500 hover:text-orange-500 bg-slate-100 dark:bg-slate-900 hover:bg-orange-50 dark:hover:bg-orange-950/30 border border-slate-200 dark:border-slate-800 transition-all"
                            :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                        >
                            <svg v-if="isDarkMode" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Main Form Body Container -->
                <div class="w-full max-w-2xl mx-auto my-auto space-y-6">
                    <!-- Heading -->
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ translations[currentLang].welcome }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{ translations[currentLang].subtitle }}
                        </p>
                    </div>

                    <!-- Quick Role Selector Cards (Fitur Login Cepat 8 Role) -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400">
                            {{ translations[currentLang].quickLoginTitle }}
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button
                                v-for="roleItem in roles"
                                :key="roleItem.key"
                                type="button"
                                @click="selectQuickRole(roleItem)"
                                class="p-2.5 rounded-2xl border text-left transition-all duration-200 flex flex-col justify-between relative overflow-hidden group"
                                :class="[
                                    activeRole === roleItem.key 
                                        ? 'bg-orange-50 dark:bg-orange-950/40 border-orange-500 text-slate-900 dark:text-white shadow-md ring-2 ring-orange-500/20' 
                                        : 'bg-slate-50 dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:border-orange-300 hover:bg-orange-50/50 dark:hover:bg-slate-900'
                                ]"
                            >
                                <div class="flex items-center justify-between w-full mb-1">
                                    <div 
                                        class="w-7 h-7 rounded-xl flex items-center justify-center text-white bg-gradient-to-tr"
                                        :class="roleItem.color"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="roleItem.icon"></path>
                                        </svg>
                                    </div>
                                    <span 
                                        v-if="activeRole === roleItem.key" 
                                        class="w-2 h-2 rounded-full bg-orange-500 animate-ping"
                                    ></span>
                                </div>
                                <div>
                                    <span class="font-bold text-xs block leading-tight">
                                        {{ currentLang === 'id' ? roleItem.nameId : roleItem.nameEn }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium block truncate mt-0.5">
                                        {{ currentLang === 'id' ? roleItem.badge : roleItem.badgeEn }}
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Status Notification Banner -->
                    <div v-if="status" class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold flex items-center space-x-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ status }}</span>
                    </div>

                    <!-- Active Role Description Badge -->
                    <div 
                        v-if="activeRole"
                        class="p-3 rounded-xl border flex items-center space-x-3 text-xs transition-all duration-200"
                        :class="roles.find(r => r.key === activeRole)?.bgSoft"
                    >
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <span class="font-bold block">Role: {{ roles.find(r => r.key === activeRole)?.[currentLang === 'id' ? 'nameId' : 'nameEn'] }} ({{ form.email }})</span>
                            <span class="text-[11px] opacity-90">{{ roles.find(r => r.key === activeRole)?.[currentLang === 'id' ? 'descId' : 'descEn'] }}</span>
                        </div>
                    </div>

                    <!-- Authentication Form -->
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Email Input -->
                        <div>
                            <InputLabel for="email" :value="translations[currentLang].emailLabel" class="text-xs font-bold text-slate-700 dark:text-slate-300" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full rounded-xl border-slate-200 dark:border-slate-800 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="nama@mkt.or.id"
                            />
                            <InputError class="mt-1" :message="form.errors.email" />
                        </div>

                        <!-- Password Input -->
                        <div>
                            <div class="flex justify-between items-center">
                                <InputLabel for="password" :value="translations[currentLang].passwordLabel" class="text-xs font-bold text-slate-700 dark:text-slate-300" />
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs font-semibold text-orange-600 hover:text-orange-700 dark:text-orange-400"
                                >
                                    {{ translations[currentLang].forgotPassword }}
                                </Link>
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full rounded-xl border-slate-200 dark:border-slate-800 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <InputError class="mt-1" :message="form.errors.password" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center cursor-pointer">
                                <Checkbox name="remember" v-model:checked="form.remember" class="text-orange-500 focus:ring-orange-500 rounded" />
                                <span class="ms-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                                    {{ translations[currentLang].rememberMe }}
                                </span>
                            </label>
                            <span class="text-[11px] text-slate-400">Default Pass: <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-orange-600 dark:text-orange-400 font-mono">password123</code></span>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <PrimaryButton
                                class="w-full flex justify-center items-center py-3 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 active:scale-[0.98] transition-all"
                                :class="{ 'opacity-50 pointer-events-none': form.processing }"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ form.processing ? translations[currentLang].loggingIn : translations[currentLang].loginButton }}</span>
                            </PrimaryButton>
                        </div>
                    </form>

                    <!-- Back to Landing Page Link -->
                    <div class="text-center pt-2">
                        <Link href="/" class="text-xs font-bold text-slate-500 hover:text-orange-500 dark:text-slate-400 dark:hover:text-orange-400 inline-flex items-center space-x-1.5 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span>{{ translations[currentLang].backToLanding }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Footer info -->
                <div class="text-center text-[11px] text-slate-400 dark:text-slate-600 mt-6">
                    Sistem Tanggap Bencana & Operational Hub - Yayasan MKT Indonesia
                </div>
            </div>
        </div>
    </div>
</template>
