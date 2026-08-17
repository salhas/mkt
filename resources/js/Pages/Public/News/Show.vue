<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import heroRescueImg from '../../../../images/hero_rescue.jpg';

const props = defineProps({
    article: Object,
    relatedNews: Array,
});

const isCopied = ref(false);

const copyArticleLink = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        isCopied.value = true;
        setTimeout(() => { isCopied.value = false; }, 2500);
    } catch (e) {
        isCopied.value = true;
        setTimeout(() => { isCopied.value = false; }, 2500);
    }
};

const shareWA = () => {
    const text = encodeURIComponent(`${props.article.title}\n\nBaca selengkapnya di Portal MKT: ${window.location.href}`);
    window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
};

const shareTG = () => {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(props.article.title);
    window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
};

const shareFB = () => {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
};

const shareTW = () => {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(props.article.title);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <PublicLayout 
        :title="article.title + ' - Yayasan MKT Indonesia'"
        :description="article.content.substring(0, 150) + '...'"
    >
        <template #default="{ openCtaModal }">
            
            <!-- ARTICLE HERO & BREADCRUMB -->
            <section class="py-10 sm:py-14 bg-gradient-to-b from-orange-500/10 via-amber-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    
                    <!-- Breadcrumbs -->
                    <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <Link :href="route('home')" class="hover:text-orange-500">Beranda</Link>
                        <span>/</span>
                        <Link :href="route('public.news')" class="hover:text-orange-500">Berita & Artikel</Link>
                        <span>/</span>
                        <span class="text-orange-600 dark:text-orange-400 truncate">{{ article.category }}</span>
                    </div>

                    <!-- Category & Meta -->
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase text-white bg-orange-600 shadow-sm">
                            {{ article.category }}
                        </span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            📅 {{ formatDate(article.published_at || article.created_at) }}
                        </span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            ✍️ Penulis: <strong class="text-slate-700 dark:text-slate-200">{{ article.author || 'Humas MKT' }}</strong>
                        </span>
                    </div>

                    <!-- Main Headline Title -->
                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                        {{ article.title }}
                    </h1>
                </div>
            </section>

            <!-- MAIN ARTICLE BODY & SHARING SECTION -->
            <section class="py-12 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    
                    <!-- Cover Image Thumbnail -->
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 bg-slate-950">
                        <img 
                            :src="article.image_url || heroRescueImg" 
                            :alt="article.title" 
                            @error="(e) => e.target.src = heroRescueImg"
                            class="w-full max-h-[500px] object-cover object-center" 
                        />
                        <div class="p-3 bg-slate-900/90 text-slate-300 text-xs text-center border-t border-slate-800">
                            <span>📷 Dokumentasi Lapangan & Publikasi Resmi Yayasan Mitra Kemanusiaan Terpadu</span>
                        </div>
                    </div>

                    <!-- Share Action Bar (Top) -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center space-x-1.5">
                            <span>🔗 Bagikan Artikel Ini:</span>
                        </span>
                        <div class="flex items-center space-x-2">
                            <button @click="shareWA" class="p-2 px-3 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs hover:bg-emerald-500/20 transition flex items-center space-x-1">
                                <span>💬</span>
                                <span>WA</span>
                            </button>
                            <button @click="shareTG" class="p-2 px-3 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 font-bold text-xs hover:bg-sky-500/20 transition flex items-center space-x-1">
                                <span>✈️</span>
                                <span>Telegram</span>
                            </button>
                            <button @click="shareFB" class="p-2 px-3 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-xs hover:bg-blue-500/20 transition flex items-center space-x-1">
                                <span>📘</span>
                                <span>FB</span>
                            </button>
                            <button @click="shareTW" class="p-2 px-3 rounded-xl bg-slate-500/10 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-500/20 transition flex items-center space-x-1">
                                <span>🐦</span>
                                <span>X</span>
                            </button>
                            <button @click="copyArticleLink" class="p-2 px-3 rounded-xl text-white font-bold text-xs transition flex items-center space-x-1" :class="isCopied ? 'bg-emerald-600' : 'bg-orange-600 hover:bg-orange-700'">
                                <span>📋</span>
                                <span>{{ isCopied ? 'Tersalin!' : 'Salin' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Formatted Content Typography Body -->
                    <div class="prose dark:prose-invert max-w-none text-slate-800 dark:text-slate-200 space-y-6 text-base sm:text-lg leading-relaxed font-normal">
                        <p class="whitespace-pre-line leading-relaxed">
                            {{ article.content }}
                        </p>
                    </div>

                    <!-- Author Callout Box -->
                    <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 flex items-center justify-center font-bold text-white text-xl shrink-0">
                            🛡️
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-black tracking-wider text-orange-600 dark:text-orange-400">Rilis Publikasi</span>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ article.author || 'Divisi Humas & Pusdalops MKT' }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Yayasan Mitra Kemanusiaan Terpadu Indonesia</p>
                        </div>
                    </div>

                </div>
            </section>

            <!-- RELATED NEWS RECOMMENDATION SECTION -->
            <section v-if="relatedNews && relatedNews.length > 0" class="py-16 bg-slate-50 dark:bg-slate-950 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            Berita & Artikel Terkait Lainnya
                        </h3>
                        <Link :href="route('public.news')" class="text-xs sm:text-sm font-bold text-orange-600 dark:text-orange-400 hover:underline">
                            Lihat Semua &rarr;
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            v-for="rel in relatedNews"
                            :key="rel.id"
                            class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group"
                        >
                            <Link :href="route('public.news.show', rel.slug || rel.id)" class="relative h-44 overflow-hidden bg-slate-800 block">
                                <img :src="rel.image_url || heroRescueImg" :alt="rel.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-black uppercase text-white bg-orange-600 shadow-sm">
                                    {{ rel.category }}
                                </span>
                            </Link>
                            <div class="p-5 space-y-2 flex-1 flex flex-col justify-between">
                                <Link :href="route('public.news.show', rel.slug || rel.id)">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                        {{ rel.title }}
                                    </h4>
                                </Link>
                                <Link :href="route('public.news.show', rel.slug || rel.id)" class="text-xs font-bold text-orange-600 dark:text-orange-400 inline-flex items-center space-x-1 hover:underline pt-2">
                                    <span>Baca Artikel</span>
                                    <span>&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
