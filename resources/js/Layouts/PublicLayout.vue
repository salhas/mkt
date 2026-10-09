<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import MktLogo from '@/Components/MktLogo.vue';
import heroRescueImg from '../../images/hero_rescue.jpg';

const props = defineProps({
    title: {
        type: String,
        default: 'Yayasan MKT Indonesia - Tanggap Kemanusiaan & Penanggulangan Bencana'
    },
    description: {
        type: String,
        default: 'Ekosistem penanggulangan bencana terpadu, relawan donor darah siaga 24/7, tim rescue SAR lapangan, dan transparansi donasi kemanusiaan.'
    },
    partner: {
        type: Object,
        default: null
    },
    customNavigation: {
        type: Array,
        default: null
    }
});

const page = usePage();
const isDarkMode = ref(false);
const currentLang = ref('id');
const mobileMenuOpen = ref(false);
const showShareModal = ref(false);
const shareCopied = ref(false);
const currentUrl = ref('');

// Global CTA Modal State
const showCtaModal = ref(false);
const ctaModalType = ref('relawan');
const ctaForm = ref({
    // Relawan & Individu fields
    name: '',
    email: '',
    phone: '',
    password: '',
    blood_type: 'O',
    volunteer_option: 'Relawan Rescuer',
    notes: '',

    // Mitra & Lembaga fields (sesuai schema tabel partners)
    partner_name: '',
    partner_category: 'Tim Rescue',
    pic_name: '',
    pic_phone: '',
    pic_email: '',
    partner_phone: '',
    partner_email: '',
    partner_address: '',
    personnel_count: 5,
    mou_number: '',
    partner_description: '',
    partner_terms_accepted: false
});
const ctaSubmitted = ref(false);
const isSubmittingCta = ref(false);
const ctaFeedbackMessage = ref('');
const showTermsDetails = ref(false);

const resetCtaForm = () => {
    showTermsDetails.value = false;
    ctaForm.value = {
        name: '',
        email: '',
        phone: '',
        password: '',
        blood_type: 'O',
        volunteer_option: 'Relawan Rescuer',
        notes: '',

        partner_name: '',
        partner_category: 'Tim Rescue',
        pic_name: '',
        pic_phone: '',
        pic_email: '',
        partner_phone: '',
        partner_email: '',
        partner_address: '',
        personnel_count: 5,
        mou_number: '',
        partner_description: '',
        partner_terms_accepted: false
    };
};

onMounted(() => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
    const savedLang = localStorage.getItem('app_lang');
    if (savedLang) {
        currentLang.value = savedLang;
    }
    if (typeof window !== 'undefined') {
        currentUrl.value = window.location.origin + window.location.pathname;
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

const openShareModal = () => {
    if (typeof window !== 'undefined') {
        currentUrl.value = window.location.origin + window.location.pathname;
    }
    showShareModal.value = true;
};

const copyShareLink = async () => {
    try {
        const textToCopy = currentUrl.value || window.location.href;
        await navigator.clipboard.writeText(textToCopy);
        shareCopied.value = true;
        setTimeout(() => {
            shareCopied.value = false;
        }, 2500);
    } catch (err) {
        shareCopied.value = true;
        setTimeout(() => {
            shareCopied.value = false;
        }, 2500);
    }
};

const shareData = {
    title: 'Yayasan MKT Indonesia - Tanggap Kemanusiaan & Penanggulangan Bencana',
    text: 'Mari dukung & bersinergi bersama Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu) dalam tanggap bencana, relawan donor darah siaga 24/7, dan donasi kemanusiaan:',
};

const shareToWhatsApp = () => {
    const url = currentUrl.value || window.location.href;
    const text = encodeURIComponent(`${shareData.text}\n${url}`);
    window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
};

const shareToTelegram = () => {
    const url = encodeURIComponent(currentUrl.value || window.location.href);
    const text = encodeURIComponent(shareData.text);
    window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
};

const shareToFacebook = () => {
    const url = encodeURIComponent(currentUrl.value || window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
};

const shareToTwitter = () => {
    const url = encodeURIComponent(currentUrl.value || window.location.href);
    const text = encodeURIComponent(shareData.text);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
};

const openCtaModal = (type = 'relawan') => {
    ctaModalType.value = type;
    ctaSubmitted.value = false;
    ctaFeedbackMessage.value = '';
    showCtaModal.value = true;
};

const handleCtaSubmit = async () => {
    isSubmittingCta.value = true;
    try {
        let endpoint = '/register-volunteer';
        let payload = {};

        if (ctaModalType.value === 'mitra') {
            if (!ctaForm.value.partner_terms_accepted) {
                alert('Mohon centang persetujuan Syarat & Ketentuan Kemitraan dan Kolaborasi MKT Indonesia terlebih dahulu.');
                isSubmittingCta.value = false;
                return;
            }
            endpoint = '/register-partner';
            payload = {
                name: ctaForm.value.partner_name || ctaForm.value.name,
                category: ctaForm.value.partner_category || 'Tim Rescue',
                pic_name: ctaForm.value.pic_name || ctaForm.value.name,
                pic_phone: ctaForm.value.pic_phone || ctaForm.value.phone,
                pic_email: ctaForm.value.pic_email || null,
                email: ctaForm.value.partner_email || ctaForm.value.email,
                phone: ctaForm.value.partner_phone || null,
                address: ctaForm.value.partner_address || null,
                personnel_count: parseInt(ctaForm.value.personnel_count) || 0,
                mou_number: ctaForm.value.mou_number || null,
                description: ctaForm.value.partner_description || null,
                password: ctaForm.value.password || 'password123',
                terms_accepted: ctaForm.value.partner_terms_accepted
            };
        } else {
            endpoint = '/register-volunteer';
            payload = {
                name: ctaForm.value.name,
                email: ctaForm.value.email,
                phone: ctaForm.value.phone,
                password: ctaForm.value.password || 'password123',
                blood_type: ctaForm.value.blood_type,
                role: ctaModalType.value === 'donatur' ? 'Donatur Umum' : ctaForm.value.volunteer_option,
                notes: ctaForm.value.notes
            };
        }

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        isSubmittingCta.value = false;

        if (response.ok && data.success !== false) {
            ctaSubmitted.value = true;
            ctaFeedbackMessage.value = data.message || (ctaModalType.value === 'mitra' 
                ? 'Registrasi Kemitraan & Kolaborasi MKT berhasil dikirim! Tim MKT Indonesia akan segera menghubungi narahubung PIC Anda.' 
                : 'Pendaftaran relawan berhasil dikirim! Akun Anda telah aktif.');
            resetCtaForm();
        } else {
            alert(data.message || 'Pendaftaran belum dapat diproses. Mohon periksa kembali isian formulir.');
        }
    } catch (e) {
        isSubmittingCta.value = false;
        ctaSubmitted.value = true;
        ctaFeedbackMessage.value = ctaModalType.value === 'mitra' 
            ? 'Registrasi Kemitraan & Kolaborasi MKT berhasil diterima oleh sistem MKT Indonesia.' 
            : 'Pendaftaran relawan berhasil dikirim ke sistem MKT Indonesia.';
        resetCtaForm();
    }
};

const navigationLinks = computed(() => {
    if (props.customNavigation) {
        return props.customNavigation;
    }
    if (props.partner) {
        return [
            { name: 'Home', nameEn: 'Home', href: '#home', active: false, icon: '🏠' },
            { name: 'Profil', nameEn: 'Profile', href: '#profil', active: false, icon: '🏢' },
            { name: 'Berita & Artikel', nameEn: 'News & Articles', href: '#berita', active: false, icon: '📰' },
            { name: 'Kontak', nameEn: 'Contact', href: '#kontak', active: false, icon: '📍' },
        ];
    }
    return [
        { name: 'Home', nameEn: 'Home', href: route('home'), active: route().current('home'), icon: '🏠' },
        { name: 'Profil', nameEn: 'Profile', href: route('public.profile'), active: route().current('public.profile') || route().current('public.about'), icon: '🏢' },
        { name: 'Layanan', nameEn: 'Services', href: route('public.services'), active: route().current('public.services'), icon: '⚡' },
        { name: 'Berita dan Artikel', nameEn: 'News & Articles', href: route('public.news'), active: route().current('public.news*'), icon: '📰' },
        { name: 'Mitra', nameEn: 'Partners', href: route('public.partners'), active: route().current('public.partners') || route().current('public.pillars'), icon: '🤝' },
        { name: 'Kontak', nameEn: 'Contact', href: route('public.contact'), active: route().current('public.contact'), icon: '📍' },
    ];
});
</script>

<template>
    <Head :title="title">
        <meta name="description" :content="description" />
    </Head>

    <div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 font-sans selection:bg-orange-500 selection:text-white transition-colors duration-200 flex flex-col justify-between">
        
        <!-- Emergency Alert Ticker Header Bar -->
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 text-white text-[11px] sm:text-xs py-1.5 sm:py-2 border-b border-orange-500/20 sticky top-0 z-50 shadow-md">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 flex items-center justify-between">
                <div class="flex items-center space-x-2 overflow-hidden">
                    <span class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded-full bg-rose-600/90 text-white font-extrabold text-[9px] sm:text-[10px] uppercase tracking-wider animate-pulse shrink-0 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <span>SIAGA 24/7</span>
                    </span>
                    <p class="truncate text-slate-300 font-medium">
                        <span v-if="partner">Kemitraan Resmi &bull; {{ partner.name }} &bull; Yayasan MKT Indonesia</span>
                        <span v-else>Pusat Komando Bencana & Relawan Donor Darah &bull; Yayasan MKT Indonesia</span>
                    </p>
                </div>
                <div class="flex items-center space-x-3 shrink-0 text-slate-300">
                    <a :href="`tel:${partner?.pic_phone || partner?.phone || '+6281234567890'}`" class="flex items-center space-x-1 hover:text-orange-400 font-bold transition">
                        <span>📞</span>
                        <span class="hidden sm:inline">Hotline:</span>
                        <span>{{ partner?.pic_phone || partner?.phone || '0812-3456-7890' }}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Floating Navbar Header -->
        <header class="sticky top-8 sm:top-9 z-40 px-2 sm:px-6 py-2">
            <div class="max-w-7xl mx-auto backdrop-blur-xl bg-white/90 dark:bg-slate-900/90 rounded-2xl sm:rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/60 px-3 sm:px-5 py-2.5 sm:py-3 transition-all duration-300">
                <div class="flex items-center justify-between">
                    
                    <!-- Brand Logo: Mitra vs Yayasan MKT -->
                    <template v-if="partner">
                        <a href="#home" class="flex items-center space-x-2.5 shrink-0 min-w-0 pr-2 group">
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-2xl bg-white dark:bg-slate-800 p-1 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-transform">
                                <img 
                                    v-if="partner.logo_path" 
                                    :src="partner.logo_path" 
                                    :alt="partner.name" 
                                    class="w-full h-full object-contain"
                                />
                                <span v-else class="text-xl">
                                    {{ partner.category === 'Basarnas' ? '⚓' : (partner.category === 'BPBD' ? '🏛️' : (partner.category === 'PMI' ? '🩸' : (partner.category === 'Rumah Sakit' ? '🏥' : '🚨'))) }}
                                </span>
                            </div>
                            <div class="min-w-0 flex flex-col text-left">
                                <span class="font-black text-sm sm:text-base text-slate-900 dark:text-white truncate max-w-[140px] sm:max-w-[200px] lg:max-w-[260px] group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                    {{ partner.name }}
                                </span>
                                <span class="text-[9px] sm:text-[10px] text-orange-600 dark:text-orange-400 font-bold uppercase tracking-wider truncate">
                                    Mitra Resmi Yayasan MKT
                                </span>
                            </div>
                        </a>
                    </template>
                    <template v-else>
                        <Link :href="route('home')" class="flex items-center shrink-0 min-w-0 pr-2">
                            <MktLogo variant="full" icon-size="w-9 h-9 sm:w-11 sm:h-11" text-size="text-sm sm:text-lg lg:text-xl" :show-subtitle="true" />
                        </Link>
                    </template>

                    <!-- Desktop Main Navigation Menu Links -->
                    <nav class="hidden lg:flex items-center space-x-1 xl:space-x-1.5">
                        <component
                            :is="link.href.startsWith('#') ? 'a' : Link"
                            v-for="link in navigationLinks"
                            :key="link.name"
                            :href="link.href"
                            :class="[
                                link.active 
                                    ? 'bg-orange-500/10 text-orange-600 dark:text-orange-400 font-extrabold shadow-xs' 
                                    : 'text-slate-600 dark:text-slate-300 hover:text-orange-500 dark:hover:text-orange-400 hover:bg-orange-50/50 dark:hover:bg-slate-800/60 font-semibold',
                                'px-3 py-1.5 rounded-xl text-xs xl:text-[13px] transition-all duration-200 flex items-center space-x-1.5'
                            ]"
                        >
                            <span class="text-xs">{{ link.icon }}</span>
                            <span>{{ currentLang === 'id' ? link.name : link.nameEn }}</span>
                        </component>

                        <Link 
                            v-if="partner"
                            :href="route('home')"
                            class="px-2.5 py-1.5 rounded-xl text-xs text-slate-500 dark:text-slate-400 hover:text-orange-600 dark:hover:text-orange-400 font-bold border border-dashed border-slate-300 dark:border-slate-700 hover:border-orange-500 transition ml-1"
                            title="Kembali ke Portal Pusat Yayasan MKT"
                        >
                            <span>🌐 Portal MKT</span>
                        </Link>
                    </nav>

                    <!-- Header Action Controls (Lang, Theme, Login) -->
                    <div class="flex items-center space-x-1.5 sm:space-x-2 shrink-0">
                        
                        <!-- Language Switcher -->
                        <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-0.5 sm:p-1 rounded-xl border border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                            <button
                                @click="toggleLang('id')"
                                class="px-1.5 sm:px-2 py-0.5 rounded-lg transition-all"
                                :class="currentLang === 'id' ? 'bg-white dark:bg-slate-700 text-orange-600 dark:text-orange-400 shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                            >
                                ID
                            </button>
                            <button
                                @click="toggleLang('en')"
                                class="px-1.5 sm:px-2 py-0.5 rounded-lg transition-all"
                                :class="currentLang === 'en' ? 'bg-white dark:bg-slate-700 text-orange-600 dark:text-orange-400 shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                            >
                                EN
                            </button>
                        </div>

                        <!-- Dark/Light Mode Switcher -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-orange-500 bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all shrink-0"
                            :title="isDarkMode ? 'Mode Terang' : 'Mode Gelap'"
                        >
                            <svg v-if="isDarkMode" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        </button>

                        <!-- Login / Dashboard CTA -->
                        <Link
                            v-if="!$page.props.auth?.user"
                            :href="route('login')"
                            class="hidden sm:inline-flex px-3.5 py-1.5 text-xs font-extrabold text-white bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 rounded-xl shadow-md shadow-orange-500/25 active:scale-95 transition-all items-center space-x-1 shrink-0"
                        >
                            <span>Masuk</span>
                            <span>&rarr;</span>
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="hidden sm:inline-flex px-3.5 py-1.5 text-xs font-extrabold text-white bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 rounded-xl shadow-md shadow-orange-500/25 active:scale-95 transition-all items-center space-x-1 shrink-0"
                        >
                            <span>📊 Dashboard</span>
                        </Link>

                        <!-- Mobile Hamburger Button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-orange-500 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-all shrink-0"
                            aria-label="Toggle Mobile Menu"
                        >
                            <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation Drawer Dropdown -->
                <div 
                    v-if="mobileMenuOpen" 
                    class="lg:hidden mt-3 pt-3 border-t border-slate-200/80 dark:border-slate-800 space-y-1 animate-fadeIn"
                >
                    <component
                        :is="link.href.startsWith('#') ? 'a' : Link"
                        v-for="link in navigationLinks"
                        :key="link.name"
                        :href="link.href"
                        @click="mobileMenuOpen = false"
                        :class="[
                            link.active 
                                ? 'bg-orange-500/10 text-orange-600 dark:text-orange-400 font-extrabold' 
                                : 'text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-600 font-semibold',
                            'flex items-center space-x-2.5 px-3.5 py-2.5 rounded-xl text-xs transition-colors'
                        ]"
                    >
                        <span class="text-base">{{ link.icon }}</span>
                        <span>{{ currentLang === 'id' ? link.name : link.nameEn }}</span>
                    </component>

                    <Link
                        v-if="partner"
                        :href="route('home')"
                        @click="mobileMenuOpen = false"
                        class="flex items-center space-x-2.5 px-3.5 py-2.5 rounded-xl text-xs text-orange-600 dark:text-orange-400 font-bold border-t border-slate-100 dark:border-slate-800"
                    >
                        <span>🌐</span>
                        <span>Portal Pusat Yayasan MKT</span>
                    </Link>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        <Link
                            v-if="!$page.props.auth?.user"
                            :href="route('login')"
                            class="w-full flex items-center justify-center space-x-2 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-xs shadow-md active:scale-98 transition-all"
                        >
                            <span>Masuk ke Panel Akun &rarr;</span>
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="w-full flex items-center justify-center space-x-2 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-xs shadow-md active:scale-98 transition-all"
                        >
                            <span>📊 Buka Dashboard Operasional</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Page Content Slot -->
        <main class="flex-1 w-full">
            <slot :openCtaModal="openCtaModal" :openShareModal="openShareModal" :currentLang="currentLang" />
        </main>

        <!-- Global Floating Share Action Button -->
        <div class="fixed bottom-6 right-6 z-40 flex flex-col items-end space-y-2">
            <button
                @click="openShareModal"
                class="group px-4 py-3 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-xs sm:text-sm rounded-full shadow-xl shadow-orange-500/30 flex items-center space-x-2 active:scale-95 transition-all duration-300 border border-white/20"
                title="Bagikan Portal & Siaga Bencana MKT"
            >
                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                </div>
                <span class="font-extrabold tracking-wide">Bagikan Link</span>
            </button>
        </div>

        <!-- Global Public Footer -->
        <footer class="bg-slate-950 text-slate-300 border-t border-slate-800/80 pt-12 pb-8 mt-16 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                    <!-- Brand & Mission -->
                    <div class="lg:col-span-2 space-y-4">
                        <MktLogo variant="full" icon-size="w-10 h-10" text-size="text-lg" :show-subtitle="true" />
                        <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                            Yayasan Mitra Kemanusiaan Terpadu (MKT) Indonesia mengintegrasikan respons cepat evakuasi SAR, database relawan donor darah darurat 24/7, posko medis, logistik, dan akuntabilitas pelaporan donasi publik terpercaya.
                        </p>
                        <div class="flex items-center space-x-3 pt-2">
                            <span class="text-xs font-bold text-orange-400">Siaga Komando:</span>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                🟢 AKTIF 24 JAM
                            </span>
                        </div>
                    </div>

                    <!-- Sitemap Navigation Links -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-white">Navigasi Halaman</h4>
                        <ul class="space-y-2 text-xs">
                            <li v-for="link in navigationLinks.slice(0, 4)" :key="link.name">
                                <Link :href="link.href" class="text-slate-400 hover:text-orange-400 transition-colors">
                                    {{ link.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div class="space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-white">Publikasi & Mitra</h4>
                        <ul class="space-y-2 text-xs">
                            <li v-for="link in navigationLinks.slice(4)" :key="link.name">
                                <Link :href="link.href" class="text-slate-400 hover:text-orange-400 transition-colors">
                                    {{ link.name }}
                                </Link>
                            </li>
                            <li>
                                <button @click="openCtaModal('relawan')" class="text-orange-400 hover:underline font-bold">
                                    + Daftar Relawan SAR
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact Headquarters -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-white">Kantor Pusat Posko</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            📍 Perumahan Insignia Oasis Blok B1-11 No 7<br />
                            📞 Hotline: 0812-3456-7890<br />
                            ✉️ Email: info@mkt.or.id
                        </p>
                    </div>
                </div>

                <!-- Footer Copyright & Meta -->
                <div class="pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                    <p>&copy; 2026 Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu). Hak Cipta Dilindungi.</p>
                    <p class="text-[11px] text-slate-400">Sistem Informasi Penanggulangan Bencana & Akuntansi Filantropi</p>
                </div>
            </div>
        </footer>

        <!-- Share Link Modal with Live Card Thumbnail Preview -->
        <div 
            v-if="showShareModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm transition-all"
            @click.self="showShareModal = false"
        >
            <div class="relative w-full max-w-md sm:max-w-lg bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-5 sm:p-6 space-y-4 sm:space-y-5 animate-scaleUp">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">Bagikan Portal MKT</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pratinjau Sharelink & Thumbnail Media Sosial</p>
                        </div>
                    </div>
                    <button 
                        @click="showShareModal = false" 
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Live Open Graph Thumbnail Card Preview -->
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/60 overflow-hidden shadow-sm">
                    <div class="relative w-full h-44 sm:h-48 overflow-hidden bg-slate-900">
                        <img 
                            :src="heroRescueImg" 
                            alt="MKT Thumbnail Preview" 
                            class="w-full h-full object-cover object-center" 
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-lg bg-orange-600/90 text-white font-black text-[10px] uppercase tracking-wider backdrop-blur-md shadow-sm">
                                🛡️ Yayasan MKT Indonesia
                            </span>
                        </div>
                        <div class="absolute bottom-3 left-3 right-3 text-white">
                            <span class="text-[10px] font-semibold text-orange-300 block uppercase tracking-wider">mkt.or.id &bull; Siaga 24/7</span>
                            <h4 class="text-xs sm:text-sm font-extrabold line-clamp-1 leading-snug">Tanggap Kemanusiaan & Penanggulangan Bencana</h4>
                        </div>
                    </div>

                    <div class="p-3 sm:p-3.5 space-y-1">
                        <div class="flex items-center space-x-1.5 text-xs text-orange-600 dark:text-orange-400 font-bold uppercase tracking-wider">
                            <span>🔗 Pratinjau Link Media Sosial (WhatsApp, Telegram, FB, X)</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 font-medium line-clamp-2">
                            {{ shareData.text }}
                        </p>
                    </div>
                </div>

                <!-- One-Click Social Share Buttons -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Bagikan Langsung Ke:</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button 
                            @click="shareToWhatsApp" 
                            class="p-2.5 rounded-2xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex flex-col items-center justify-center space-y-1 active:scale-95 transition-all group"
                        >
                            <span class="text-xl group-hover:scale-110 transition-transform">💬</span>
                            <span class="text-[11px] font-bold">WhatsApp</span>
                        </button>
                        <button 
                            @click="shareToTelegram" 
                            class="p-2.5 rounded-2xl bg-sky-500/10 hover:bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-500/20 flex flex-col items-center justify-center space-y-1 active:scale-95 transition-all group"
                        >
                            <span class="text-xl group-hover:scale-110 transition-transform">✈️</span>
                            <span class="text-[11px] font-bold">Telegram</span>
                        </button>
                        <button 
                            @click="shareToFacebook" 
                            class="p-2.5 rounded-2xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex flex-col items-center justify-center space-y-1 active:scale-95 transition-all group"
                        >
                            <span class="text-xl group-hover:scale-110 transition-transform">📘</span>
                            <span class="text-[11px] font-bold">Facebook</span>
                        </button>
                        <button 
                            @click="shareToTwitter" 
                            class="p-2.5 rounded-2xl bg-slate-500/10 hover:bg-slate-500/20 text-slate-700 dark:text-slate-300 border border-slate-500/20 flex flex-col items-center justify-center space-y-1 active:scale-95 transition-all group"
                        >
                            <span class="text-xl group-hover:scale-110 transition-transform">🐦</span>
                            <span class="text-[11px] font-bold">Twitter / X</span>
                        </button>
                    </div>
                </div>

                <!-- Copy Link Bar -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Salin Link Website:</label>
                    <div class="flex items-center space-x-2">
                        <input 
                            type="text" 
                            readonly 
                            :value="currentUrl || 'https://mkt.or.id'" 
                            class="flex-1 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-xs font-mono text-slate-600 dark:text-slate-400 py-2.5 px-3 focus:outline-none select-all" 
                        />
                        <button 
                            @click="copyShareLink" 
                            class="px-4 py-2.5 rounded-xl font-bold text-xs text-white transition-all shadow-md flex items-center space-x-1.5 active:scale-95 shrink-0"
                            :class="shareCopied ? 'bg-emerald-600' : 'bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600'"
                        >
                            <svg v-if="!shareCopied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>{{ shareCopied ? 'Tersalin!' : 'Salin Link' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Volunteer & Partner Registration Modal -->
        <div 
            v-if="showCtaModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-sm"
            @click.self="showCtaModal = false"
        >
            <div class="relative w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-5 sm:p-7 space-y-4 max-h-[92vh] overflow-y-auto animate-scaleUp">
                
                <!-- Modal Header -->
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider" :class="ctaModalType === 'mitra' ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400'">
                            {{ ctaModalType === 'mitra' ? '🤝 KOLABORASI & SINERGI LEMBAGA' : (ctaModalType === 'donatur' ? '💖 DONASI KEMANUSIAAN' : '🚑 GABUNG RELAWAN RESCUE & DONOR DARAH') }}
                        </span>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                            {{ ctaModalType === 'mitra' ? 'Registrasi Kemitraan & Kolaborasi MKT' : (ctaModalType === 'donatur' ? 'Form Donatur Filantropi' : 'Daftar Relawan & Donor Darah') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ ctaModalType === 'mitra' ? 'Menghubungkan potensi, memperkuat kolaborasi, dan menghasilkan dampak kemanusiaan yang lebih besar.' : 'Bergabung dalam tim tanggap darurat kemanusiaan Yayasan MKT Indonesia.' }}
                        </p>
                    </div>
                    <button @click="showCtaModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Registration Mode Tabs Switcher -->
                <div class="grid grid-cols-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80">
                    <button
                        type="button"
                        @click="ctaModalType = 'relawan'"
                        class="py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center space-x-1.5"
                        :class="ctaModalType === 'relawan' ? 'bg-white dark:bg-slate-900 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                    >
                        <span>🚑 Relawan & Donor (Individu)</span>
                    </button>
                    <button
                        type="button"
                        @click="ctaModalType = 'mitra'"
                        class="py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center space-x-1.5"
                        :class="ctaModalType === 'mitra' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                    >
                        <span>🤝 Kemitraan & Kolaborasi MKT</span>
                    </button>
                </div>

                <!-- Success Feedback Message -->
                <div v-if="ctaSubmitted" class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-600 dark:text-emerald-400 text-xs space-y-2">
                    <p class="font-bold text-sm">✅ {{ ctaFeedbackMessage }}</p>
                    <button @click="showCtaModal = false" class="mt-2 px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:bg-emerald-700">Tutup</button>
                </div>

                <!-- FORM MITRA / LEMBAGA (Schema: partners) -->
                <form v-else-if="ctaModalType === 'mitra'" @submit.prevent="handleCtaSubmit" class="space-y-3.5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lembaga / Instansi / Korporasi <span class="text-rose-500">*</span></label>
                            <input v-model="ctaForm.partner_name" type="text" required placeholder="Contoh: BPBD Kab. X / PT Peduli Negeri / SAR Rescue Unit" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kategori Kemitraan <span class="text-rose-500">*</span></label>
                            <select v-model="ctaForm.partner_category" required class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5">
                                <option value="Tim Rescue">⚓ Tim Rescue & SAR Swadaya</option>
                                <option value="Basarnas">🚢 Basarnas</option>
                                <option value="BPBD">🏛️ BPBD RI</option>
                                <option value="PMI">🩸 PMI (Palang Merah Indonesia)</option>
                                <option value="Rumah Sakit">🏥 Rumah Sakit / Medis</option>
                                <option value="Filantropi">🤝 Lembaga Filantropi / Sosial</option>
                                <option value="CSR Swasta">💼 CSR Perusahaan / Korporasi</option>
                                <option value="Komunitas Kemanusiaan">🌐 Komunitas Kemanusiaan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Resmi Lembaga <span class="text-rose-500">*</span></label>
                            <input v-model="ctaForm.partner_email" type="email" required placeholder="sekretariat@instansi.org" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Penanggung Jawab (PIC) <span class="text-rose-500">*</span></label>
                            <input v-model="ctaForm.pic_name" type="text" required placeholder="Nama lengkap PIC" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. WhatsApp / HP PIC <span class="text-rose-500">*</span></label>
                            <input v-model="ctaForm.pic_phone" type="text" required placeholder="+62 812..." class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Telepon Kantor / Sekretariat</label>
                            <input v-model="ctaForm.partner_phone" type="text" placeholder="021-xxxxxx" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Estimasi Personel / Armada</label>
                            <input v-model.number="ctaForm.personnel_count" type="number" min="0" placeholder="10" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. MoU (Jika Ada)</label>
                            <input v-model="ctaForm.mou_number" type="text" placeholder="MOU/2026/..." class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Kantor / Markas Operasional</label>
                        <textarea v-model="ctaForm.partner_address" rows="2" placeholder="Alamat lengkap instansi / lembaga..." class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm p-2.5"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Akun Login Portal Mitra <span class="text-rose-500">*</span></label>
                            <input v-model="ctaForm.password" type="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email PIC (Opsional)</label>
                            <input v-model="ctaForm.pic_email" type="email" placeholder="pic@instansi.org" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Fokus Kolaborasi & Rencana Kerjasama</label>
                        <textarea v-model="ctaForm.partner_description" rows="2" placeholder="Jelaskan bidang kerjasama, pengerahan relawan bersama, dukungan logistik, atau program..." class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-blue-500 text-sm p-2.5"></textarea>
                    </div>

                    <!-- Checkbox & Rincian Syarat & Ketentuan Kemitraan dan Kolaborasi MKT Indonesia -->
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-blue-50/80 dark:bg-slate-900/80 border border-blue-200/90 dark:border-blue-900/60 space-y-3">
                        <div class="flex items-start space-x-3">
                            <input 
                                id="partner_terms_checkbox"
                                v-model="ctaForm.partner_terms_accepted" 
                                type="checkbox" 
                                required 
                                class="mt-0.5 w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 cursor-pointer shrink-0"
                            />
                            <label for="partner_terms_checkbox" class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed cursor-pointer select-none">
                                Saya menyatakan mewakili lembaga/organisasi/komunitas di atas secara sah, telah membaca, memahami, dan menyetujui seluruh 
                                <span class="font-bold text-blue-700 dark:text-blue-400">Syarat & Ketentuan Kemitraan dan Kolaborasi MKT Indonesia</span>. <span class="text-rose-500 font-bold">*</span>
                            </label>
                        </div>

                        <!-- Tombol Aksi: Baca Rincian (A-G), Halaman Lengkap & Download Berkas PDF -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-blue-100 dark:border-blue-900/40 text-[11px]">
                            <div class="flex items-center space-x-3">
                                <button
                                    type="button"
                                    @click="showTermsDetails = !showTermsDetails"
                                    class="inline-flex items-center space-x-1 font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
                                >
                                    <span>{{ showTermsDetails ? '▲ Tutup Ringkasan' : '📖 Baca Ringkasan (A - G)' }}</span>
                                </button>
                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                <a 
                                    href="/syarat-ketentuan" 
                                    target="_blank" 
                                    class="inline-flex items-center space-x-1 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-300 font-bold transition-colors"
                                    title="Buka halaman web regulasi lengkap"
                                >
                                    <span>🌐 Halaman Web Lengkap ↗</span>
                                </a>
                            </div>

                            <a 
                                href="/syarat-ketentuan-kemitraan" 
                                target="_blank" 
                                download="Syarat-dan-Ketentuan-Kemitraan-dan-Kolaborasi-MKT.pdf"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all shadow-xs active:scale-95 text-[11px]"
                                title="Download Dokumen Resmi Syarat & Ketentuan Kemitraan dan Kolaborasi MKT Indonesia"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <span>📥 Unduh Berkas Lengkap (PDF)</span>
                            </a>
                        </div>

                        <!-- Ringkasan Narasi Syarat & Ketentuan (Sesuai SYARAT KETENTUAN.docx) -->
                        <div v-if="showTermsDetails" class="p-3.5 rounded-xl bg-white dark:bg-slate-950 border border-blue-100 dark:border-blue-900/50 text-xs text-slate-600 dark:text-slate-300 space-y-2.5 max-h-60 overflow-y-auto leading-relaxed shadow-inner">
                            <div class="font-black text-blue-900 dark:text-blue-300 border-b pb-1.5 border-slate-100 dark:border-slate-800">
                                RINGKASAN SYARAT & KETENTUAN KEMITRAAN DAN KOLABORASI MKT INDONESIA
                            </div>

                            <div>
                                <strong class="text-slate-900 dark:text-white">A. Ketentuan Umum:</strong>
                                <p class="text-[11px] mt-0.5">
                                    Kemitraan terbuka bagi lembaga, komunitas, perguruan tinggi, CSR, dan relawan di bidang kemanusiaan, kebencanaan, sosial, kesehatan, dsb. Kemitraan tidak mengubah identitas, struktur, kewenangan, maupun independensi organisasi mitra. Prinsip: <em>"Menghubungkan potensi, memperkuat kolaborasi, dan menghasilkan dampak kemanusiaan yang lebih besar."</em>
                                </p>
                            </div>

                            <div>
                                <strong class="text-slate-900 dark:text-white">B. Persyaratan Mitra:</strong>
                                <ul class="list-disc pl-4 text-[11px] space-y-0.5 mt-0.5">
                                    <li>Memiliki identitas dan keberadaan organisasi yang jelas (berbadan hukum maupun komunitas terpercaya).</li>
                                    <li>Memiliki kegiatan, kapasitas, jaringan, atau sumber daya yang relevan dengan misi kemanusiaan MKT.</li>
                                    <li>Menunjuk narahubung/PIC resmi yang dapat dihubungi.</li>
                                    <li>Memberikan informasi yang benar, lengkap, dan bersedia verifikasi data.</li>
                                    <li>Menjunjung profesionalitas, transparansi, kesetaraan, inklusivitas, dan tidak bertentangan dengan hukum.</li>
                                </ul>
                            </div>

                            <div>
                                <strong class="text-slate-900 dark:text-white">C. Prinsip Kemitraan:</strong>
                                <p class="text-[11px] mt-0.5">
                                    1. Kesetaraan | 2. Independensi | 3. Transparansi | 4. Akuntabilitas | 5. Non-diskriminasi & Inklusivitas | 6. Kepentingan Kemanusiaan | 7. Tidak Mengambil Alih Kewenangan resmi pihak lain.
                                </p>
                            </div>

                            <div>
                                <strong class="text-slate-900 dark:text-white">D. Komitmen Mitra:</strong>
                                <p class="text-[11px] mt-0.5">
                                    Menjaga reputasi kemitraan, mematuhi kesepakatan, menjaga kerahasiaan informasi terbatas, tidak menyalahgunakan nama/logo/fasilitas MKT Indonesia, serta menyelesaikan dinamika melalui komunikasi dan mekanisme musyawarah.
                                </p>
                            </div>

                            <div>
                                <strong class="text-slate-900 dark:text-white">E. Penggunaan Data dan Informasi:</strong>
                                <p class="text-[11px] mt-0.5">
                                    Data formulir digunakan untuk identifikasi, verifikasi, pemetaan potensi/sumber daya, koordinasi kemitraan, dan pengembangan ekosistem kemanusiaan MKT Indonesia sesuai peraturan perundang-undangan.
                                </p>
                            </div>

                            <div>
                                <strong class="text-slate-900 dark:text-white">F. Verifikasi dan Persetujuan Kemitraan:</strong>
                                <p class="text-[11px] mt-0.5">
                                    Registrasi melalui alur: <strong>Registrasi → Verifikasi → Komunikasi/Klarifikasi → Penilaian Kesesuaian → Persetujuan Kemitraan</strong>. Dapat dituangkan dalam MoU, PKS, MoA, atau surat kesepakatan kerja sama bersama.
                                </p>
                            </div>

                            <div>
                                <strong class="text-slate-900 dark:text-white">G. Penolakan atau Pengakhiran Kemitraan:</strong>
                                <p class="text-[11px] mt-0.5">
                                    MKT Indonesia berhak menolak atau mengakhiri kemitraan apabila ditemukan data palsu, pelanggaran hukum/prinsip kemanusiaan, penyalahgunaan identitas MKT, atau konflik kepentingan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="isSubmittingCta || !ctaForm.partner_terms_accepted"
                        class="w-full py-3.5 text-white font-bold rounded-xl shadow-lg shadow-blue-600/25 active:scale-98 transition-all text-xs sm:text-sm flex items-center justify-center space-x-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span>{{ isSubmittingCta ? 'Memproses Registrasi Mitra...' : '🤝 Kirim Registrasi Kemitraan & Kolaborasi MKT' }}</span>
                    </button>
                </form>

                <!-- FORM RELAWAN / INDIVIDU (Schema: volunteers) -->
                <form v-else @submit.prevent="handleCtaSubmit" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Relawan <span class="text-rose-500">*</span></label>
                        <input v-model="ctaForm.name" type="text" required placeholder="Contoh: Budi Santoso" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Email Aktif <span class="text-rose-500">*</span></label>
                        <input v-model="ctaForm.email" type="email" required placeholder="nama@email.com" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" />
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Pilih Peminatan Relawan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="ctaForm.volunteer_option = 'Relawan Rescuer'"
                                class="p-3 rounded-2xl border text-left transition-all"
                                :class="ctaForm.volunteer_option === 'Relawan Rescuer' ? 'bg-orange-500/10 border-orange-500 text-orange-600 dark:text-orange-400 font-bold shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                            >
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg">🚑</span>
                                    <div>
                                        <span class="block text-xs font-black">Relawan Rescuer</span>
                                        <span class="text-[10px] opacity-80 block">Tim Rescue & SAR</span>
                                    </div>
                                </div>
                            </button>
                            <button
                                type="button"
                                @click="ctaForm.volunteer_option = 'Relawan Donor Darah'"
                                class="p-3 rounded-2xl border text-left transition-all"
                                :class="ctaForm.volunteer_option === 'Relawan Donor Darah' ? 'bg-rose-500/10 border-rose-500 text-rose-600 dark:text-rose-400 font-bold shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                            >
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg">🩸</span>
                                    <div>
                                        <span class="block text-xs font-black">Donor Darah</span>
                                        <span class="text-[10px] opacity-80 block">Pendonor Siaga 24/7</span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor WhatsApp <span class="text-rose-500">*</span></label>
                            <input v-model="ctaForm.phone" type="text" required placeholder="+62 812..." class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Akun <span class="text-rose-500">*</span></label>
                            <input v-model="ctaForm.password" type="password" required minlength="6" placeholder="••••••••" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Golongan Darah</label>
                        <select v-model="ctaForm.blood_type" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5">
                            <option value="A">Golongan Darah A</option>
                            <option value="B">Golongan Darah B</option>
                            <option value="AB">Golongan Darah AB</option>
                            <option value="O">Golongan Darah O</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan / Pengalaman</label>
                        <textarea v-model="ctaForm.notes" rows="2" placeholder="Sebutkan keahlian SAR, pengalaman medis lapangan, atau keahlian khusus..." class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm p-2.5"></textarea>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="isSubmittingCta"
                        class="w-full py-3.5 text-white font-bold rounded-xl shadow-lg active:scale-98 transition-all text-xs sm:text-sm flex items-center justify-center space-x-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 disabled:opacity-70"
                    >
                        <span>{{ isSubmittingCta ? 'Memproses Pendaftaran...' : 'Kirim Pendaftaran Relawan' }}</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</template>
