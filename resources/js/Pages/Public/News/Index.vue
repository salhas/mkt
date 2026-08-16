<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import heroRescueImg from '../../../../images/hero_rescue.jpg';

const props = defineProps({
    news: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || 'Semua');

const categoryList = ['Semua', 'Evakuasi', 'Logistik', 'Kesehatan', 'Edukasi', 'Mitigasi', 'Relawan', 'Umum'];

const filterNews = () => {
    router.get(route('public.news'), {
        search: search.value,
        category: selectedCategory.value !== 'Semua' ? selectedCategory.value : null,
    }, {
        preserveState: true,
        replace: true,
    });
};

const selectCat = (cat) => {
    selectedCategory.value = cat;
    filterNews();
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <PublicLayout 
        title="Berita & Artikel Kebencanaan - Yayasan MKT Indonesia"
        description="Pusat rilis pers, liputan aksi rescue lapangan, edukasi mitigasi pra-bencana, dan kabar kemanusiaan Yayasan MKT Indonesia."
    >
        <template #default>
            
            <!-- HEADER -->
            <section class="py-14 sm:py-20 bg-gradient-to-b from-orange-500/10 via-amber-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800 text-center space-y-4">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                        📰 PUBLIKASI & KABAR LAPANGAN
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                        Berita, Liputan & Artikel MKT
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Dokumentasi aksi evakuasi SAR, penyaluran logistik, aksi donor darah, dan panduan mitigasi bencana untuk keselamatan bersama.
                    </p>
                </div>
            </section>

            <!-- SEARCH & CATEGORY FILTER BAR -->
            <section class="py-8 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-20 z-30 shadow-xs">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        
                        <!-- Search Bar -->
                        <div class="relative w-full sm:w-80">
                            <input
                                v-model="search"
                                @keyup.enter="filterNews"
                                type="text"
                                placeholder="Cari berita, topik, atau penulis..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-2xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-orange-500 focus:ring-orange-500 text-xs sm:text-sm font-medium"
                            />
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        <!-- Category Filters (Horizontal Carousel) -->
                        <div class="flex items-center space-x-2 overflow-x-auto w-full sm:w-auto pb-1">
                            <button
                                v-for="cat in categoryList"
                                :key="cat"
                                @click="selectCat(cat)"
                                :class="[
                                    selectedCategory === cat 
                                        ? 'bg-orange-600 text-white shadow-md shadow-orange-600/25 border-orange-600' 
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-orange-50 border-slate-200 dark:border-slate-700',
                                    'px-3.5 py-2 rounded-xl text-xs font-bold border shrink-0 transition-all'
                                ]"
                            >
                                {{ cat }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- NEWS ARTICLES LISTING -->
            <section class="py-14 sm:py-16 bg-slate-50 dark:bg-slate-950 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    
                    <!-- News Grid -->
                    <div v-if="news.data && news.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div
                            v-for="item in news.data"
                            :key="item.id"
                            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl hover:border-orange-500/40 transition-all duration-300 flex flex-col justify-between group"
                        >
                            <!-- Cover Image Thumbnail -->
                            <Link :href="route('public.news.show', item.slug || item.id)" class="relative h-52 overflow-hidden bg-slate-800 block">
                                <img 
                                    :src="item.image_url || heroRescueImg" 
                                    :alt="item.title" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
                                <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-black uppercase text-white bg-orange-600 shadow-sm">
                                    {{ item.category }}
                                </span>
                            </Link>

                            <!-- Content Body -->
                            <div class="p-6 space-y-4 flex-1 flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs text-slate-400 font-medium">
                                        <span>✍️ {{ item.author || 'Humas MKT' }}</span>
                                        <span>📅 {{ formatDate(item.published_at || item.created_at) }}</span>
                                    </div>
                                    <Link :href="route('public.news.show', item.slug || item.id)">
                                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-snug line-clamp-2 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                            {{ item.title }}
                                        </h3>
                                    </Link>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed">
                                        {{ item.content }}
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                    <Link :href="route('public.news.show', item.slug || item.id)" class="text-xs font-bold text-orange-600 dark:text-orange-400 inline-flex items-center space-x-1 hover:underline">
                                        <span>Baca Artikel Lengkap</span>
                                        <span>&rarr;</span>
                                    </Link>
                                    <span class="text-[11px] text-slate-400 font-mono">3 min baca</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-300 dark:border-slate-800 p-8 space-y-3">
                        <span class="text-5xl block">🔍</span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tidak Ada Artikel Ditemukan</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                            Coba ubah kata kunci pencarian atau pilih kategori berita lainnya.
                        </p>
                        <button @click="search = ''; selectedCategory = 'Semua'; filterNews()" class="px-5 py-2.5 rounded-xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 transition">
                            Reset Filter Pencarian
                        </button>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="news.links && news.links.length > 3" class="flex justify-center items-center space-x-1 pt-6">
                        <template v-for="(link, i) in news.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    link.active 
                                        ? 'bg-orange-600 text-white shadow-md' 
                                        : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 border border-slate-200 dark:border-slate-800',
                                    'px-3.5 py-2 rounded-xl text-xs font-bold transition'
                                ]"
                            />
                            <span 
                                v-else 
                                v-html="link.label" 
                                class="px-3.5 py-2 text-xs text-slate-400 opacity-50"
                            />
                        </template>
                    </div>

                </div>
            </section>

        </template>
    </PublicLayout>
</template>
