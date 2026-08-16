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

const form = useForm({
    email: '',
    password: '',
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

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const translations = {
    id: {
        title: 'Login Sistem MKT',
        welcome: 'Selamat Datang Kembali',
        subtitle: 'Masukkan kredensial akun Anda untuk mengakses sistem panel.',
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
        subtitle: 'Enter your account credentials to access the panel system.',
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
            
            <!-- Left Panel: Visual Banner & Branding (Larger Width > Form Width) -->
            <div class="hidden lg:flex lg:w-7/12 xl:w-3/5 bg-slate-900 relative overflow-hidden flex-col justify-between p-10 xl:p-14 text-white">
                <!-- Background Image overlay -->
                <div class="absolute inset-0 bg-cover bg-center opacity-45 scale-105 transition-transform duration-1000" :style="{ backgroundImage: 'url(' + loginBannerImg + ')' }"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-900/40"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-slate-950/20 to-slate-950/60"></div>

                <!-- Top Brand Logo -->
                <div class="relative z-10 flex items-center">
                    <MktLogo variant="full" icon-size="w-12 h-12" text-size="text-xl" :show-subtitle="true" />
                </div>

                <!-- Center Banner Content -->
                <div class="relative z-10 my-auto py-12 max-w-xl">
                    <span class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30 mb-5 backdrop-blur-md shadow-sm">
                        <span>🛡️ {{ translations[currentLang].foundationTag }}</span>
                    </span>
                    <h2 class="text-3xl xl:text-4xl 2xl:text-5xl font-black text-white leading-tight tracking-tight">
                        {{ translations[currentLang].bannerHeader }}
                    </h2>
                    <p class="text-slate-200 text-sm sm:text-base mt-4 leading-relaxed font-normal max-w-lg">
                        {{ translations[currentLang].bannerSub }}
                    </p>
                </div>

                <!-- Bottom Footer Meta -->
                <div class="relative z-10 text-xs text-slate-400 border-t border-slate-800/80 pt-6 flex justify-between items-center">
                    <span>&copy; 2026 Yayasan MKT Indonesia</span>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700/50 text-slate-300 font-semibold">v2.5 Operational Hub</span>
                </div>
            </div>

            <!-- Right Panel: Login Form (Compact & Focused Width) -->
            <div class="flex-1 lg:w-5/12 xl:w-2/5 flex flex-col justify-between p-6 sm:p-10 lg:p-12 relative bg-white dark:bg-slate-950 transition-colors">
                
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
                <div class="w-full max-w-md mx-auto my-auto space-y-6">
                    <!-- Heading -->
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ translations[currentLang].welcome }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{ translations[currentLang].subtitle }}
                        </p>
                    </div>

                    <!-- Status Notification Banner -->
                    <div v-if="status" class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl font-semibold flex items-center space-x-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ status }}</span>
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
