<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    ecosystems: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    mktProfile: {
        type: Object,
        default: null,
    },
});

const searchQuery = ref(props.filters.search || '');

const filteredEcosystems = computed(() => {
    if (!searchQuery.value) return props.ecosystems;
    const q = searchQuery.value.toLowerCase();
    return props.ecosystems.filter(e => 
        (e.name && e.name.toLowerCase().includes(q)) ||
        (e.code && e.code.toLowerCase().includes(q)) ||
        (e.region && e.region.toLowerCase().includes(q)) ||
        (e.lead_institution && e.lead_institution.toLowerCase().includes(q))
    );
});

const totalPartnersInAllEcosystems = computed(() => {
    return props.ecosystems.reduce((acc, e) => acc + (e.partners_count || (e.partners ? e.partners.length : 0)), 0);
});
</script>

<template>
    <PublicLayout
        title="Jejaring Ekosistem Kemanusiaan - Kolaborasi Mitra Peduli"
        description="Direktori Klaster dan Ekosistem Kemanusiaan Resmi Terintegrasi dalam Sistem Tanggap Bencana Kolaborasi Mitra Peduli."
    >
        <template #default="{ openCtaModal }">
            
            <!-- HERO HEADER -->
            <section class="relative py-16 sm:py-24 bg-gradient-to-b from-indigo-500/10 via-purple-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800 text-center overflow-hidden">
                <!-- Background Glow -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    <span class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full text-xs font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 uppercase tracking-wider">
                        <span>🌐</span>
                        <span>DIREKTORI KLASTER KEMANUSIAAN INDONESIA</span>
                    </span>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                        Jejaring Ekosistem Kemanusiaan Terpadu
                    </h1>

                    <p class="text-sm sm:text-base md:text-lg text-slate-600 dark:text-slate-300 max-w-3xl mx-auto leading-relaxed">
                        Konsorsium dan wadah sinergi multi-lembaga (kampus, potensi SAR, relawan medis, ormas, dan filantropi) yang bergerak bersama dalam satu komando wilayah demi percepatan respon kebencanaan.
                    </p>

                    <!-- Summary Quick Stats -->
                    <div class="pt-4 flex flex-wrap items-center justify-center gap-4 sm:gap-8">
                        <div class="px-5 py-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 shadow-sm flex items-center space-x-3">
                            <span class="text-2xl">🌐</span>
                            <div class="text-left">
                                <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white block">{{ ecosystems.length }}</span>
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Ekosistem Wilayah</span>
                            </div>
                        </div>

                        <div class="px-5 py-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 shadow-sm flex items-center space-x-3">
                            <span class="text-2xl">🏛️</span>
                            <div class="text-left">
                                <span class="text-lg sm:text-xl font-black text-indigo-600 dark:text-indigo-400 block">{{ totalPartnersInAllEcosystems }}</span>
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Lembaga Tergabung</span>
                            </div>
                        </div>

                        <div class="px-5 py-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 shadow-sm flex items-center space-x-3">
                            <span class="text-2xl">⚡</span>
                            <div class="text-left">
                                <span class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 block">Siaga 24/7</span>
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Mobilisasi Darurat</span>
                            </div>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="pt-4 max-w-md mx-auto">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama ekosistem, kampus induk, atau kota/wilayah..."
                                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl pl-11 pr-4 py-3 text-xs sm:text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none shadow-sm transition"
                            />
                            <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            <!-- DIRECTORY LIST OF ECOSYSTEMS -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-200 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

                    <!-- Empty State -->
                    <div v-if="filteredEcosystems.length === 0" class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-800 text-center space-y-3">
                        <span class="text-5xl block">🌐</span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Ekosistem Tidak Ditemukan</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                            Tidak ada hasil yang cocok dengan kata kunci "{{ searchQuery }}". Silakan gunakan kata kunci lain atau lihat seluruh ekosistem.
                        </p>
                        <button
                            @click="searchQuery = ''"
                            class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition"
                        >
                            Tampilkan Semua Ekosistem
                        </button>
                    </div>

                    <!-- Grid of Ecosystem Cards -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                        <div
                            v-for="eco in filteredEcosystems"
                            :key="eco.id"
                            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-7 shadow-sm hover:shadow-xl hover:border-indigo-500/40 transition-all duration-300 flex flex-col justify-between space-y-6 group"
                        >
                            <div class="space-y-5">
                                <!-- Card Top: Code & Region -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60 font-mono">
                                            {{ eco.code || 'EKOSISTEM' }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                            ● {{ eco.status || 'Aktif' }}
                                        </span>
                                    </div>

                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/40 flex items-center justify-center text-2xl shrink-0 group-hover:scale-105 transition-transform overflow-hidden">
                                        <img v-if="eco.logo_path" :src="eco.logo_path" :alt="eco.name" class="w-full h-full object-contain p-1" />
                                        <span v-else>🌐</span>
                                    </div>
                                </div>

                                <!-- Ecosystem Name & Tagline -->
                                <div class="space-y-1">
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-snug">
                                        {{ eco.name }}
                                    </h3>
                                    <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 line-clamp-1">
                                        {{ eco.tagline || (eco.lead_institution ? 'Induk: ' + eco.lead_institution : 'Ekosistem Kemanusiaan Terpadu') }}
                                    </p>
                                </div>

                                <!-- Meta Info Pills -->
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 text-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-medium flex items-center space-x-1">
                                            <span>📍</span>
                                            <span>Wilayah:</span>
                                        </span>
                                        <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[170px]">
                                            {{ eco.region || 'Nasional' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-medium flex items-center space-x-1">
                                            <span>🏛️</span>
                                            <span>Lembaga Induk:</span>
                                        </span>
                                        <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[170px]">
                                            {{ eco.lead_institution || '-' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500 font-medium flex items-center space-x-1">
                                            <span>👤</span>
                                            <span>Koordinator PIC:</span>
                                        </span>
                                        <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[170px]">
                                            {{ eco.pic_name || '-' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2">
                                    {{ eco.description || 'Wadah sinergi respon darurat, mobilisasi tim relawan, dan penyaluran bantuan di kawasan terkait.' }}
                                </p>

                                <!-- Member Institutions Preview -->
                                <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-700 dark:text-slate-300">Lembaga Tergabung:</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                            {{ eco.partners_count || (eco.partners ? eco.partners.length : 0) }} Lembaga Mitra
                                        </span>
                                    </div>

                                    <div v-if="eco.partners && eco.partners.length > 0" class="flex flex-wrap gap-1 max-h-16 overflow-hidden">
                                        <span
                                            v-for="p in eco.partners.slice(0, 3)"
                                            :key="p.id"
                                            class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700"
                                        >
                                            {{ p.name }}
                                        </span>
                                        <span v-if="eco.partners.length > 3" class="px-2 py-0.5 rounded-lg text-[10px] font-bold text-indigo-600 dark:text-indigo-400">
                                            +{{ eco.partners.length - 3 }} lainnya
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer Action -->
                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500">
                                    Sinergi Bersama MKT
                                </span>

                                <Link
                                    :href="route('public.ecosystem.show', eco.slug || eco.code || eco.id)"
                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold text-xs transition shadow-sm flex items-center space-x-1.5 group-hover:shadow-md"
                                >
                                    <span>Buka Landing Page</span>
                                    <span>→</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- CALL TO ACTION FOR NEW INSTITUTIONS / ECOSYSTEM CREATION -->
            <section class="py-16 sm:py-20 bg-gradient-to-r from-indigo-950 via-slate-900 to-purple-950 text-white border-t border-indigo-500/20">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-wider">
                        🤝 INGIN MENDAFTARKAN ATAU BERGABUNG DENGAN EKOSISTEM?
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                        Wujudkan Ekosistem Kemanusiaan di Kawasan atau Kampus Anda
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Jika lembaga Anda ingin bergabung ke dalam salah satu ekosistem yang ada atau mengusulkan pembentukan ekosistem baru di wilayah Anda, tim kemitraan Kolaborasi Mitra Peduli siap memfasilitasi MoU dan integrasi sistem.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-4">
                        <button
                            @click="openCtaModal('mitra')"
                            class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-xs sm:text-sm shadow-lg shadow-orange-500/20 transition active:scale-95 flex items-center space-x-2"
                        >
                            <span>📝</span>
                            <span>Daftarkan Lembaga Mitra Baru</span>
                        </button>
                        <Link
                            :href="route('public.partner-terms')"
                            class="px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/20 transition flex items-center space-x-2"
                        >
                            <span>📄</span>
                            <span>Pelajari Syarat Kemitraan</span>
                        </Link>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
