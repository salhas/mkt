<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    news: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || 'Semua');

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingNews = ref(null);

const createForm = useForm({
    title: '',
    category: 'Evakuasi',
    author: '',
    image_url: '',
    content: '',
    published_at: new Date().toISOString().split('T')[0],
});

const editForm = useForm({
    title: '',
    category: 'Evakuasi',
    author: '',
    image_url: '',
    content: '',
    published_at: '',
});

const submitCreate = () => {
    createForm.post(route('news.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (item) => {
    editingNews.value = item;
    editForm.title = item.title;
    editForm.category = item.category;
    editForm.author = item.author;
    editForm.image_url = item.image_url;
    editForm.content = item.content;
    editForm.published_at = item.published_at || item.created_at?.split('T')[0];
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!editingNews.value) return;
    editForm.patch(route('news.update', editingNews.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editingNews.value = null;
        },
    });
};

const deleteNews = (item) => {
    if (confirm(`Hapus artikel berita "${item.title}"?`)) {
        router.delete(route('news.destroy', item.id));
    }
};

const filterNews = () => {
    router.get(route('news.index'), {
        search: search.value,
        category: selectedCategory.value !== 'Semua' ? selectedCategory.value : null,
    }, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Manajemen Berita & Artikel - Dashboard Admin MKT" />

    <AuthenticatedLayout>
        <template #header>
            <span class="font-bold text-gray-900 dark:text-white">Manajemen Berita & Artikel MKT</span>
        </template>

        <div class="space-y-6">
            
            <!-- Top Controls Bar -->
            <div class="bg-white dark:bg-gray-900 p-5 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-72">
                        <input
                            v-model="search"
                            @keyup.enter="filterNews"
                            type="text"
                            placeholder="Cari judul, topik, atau penulis..."
                            class="w-full pl-9 pr-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500"
                        />
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <select
                        v-model="selectedCategory"
                        @change="filterNews"
                        class="rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-700 dark:text-gray-300 py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500"
                    >
                        <option value="Semua">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                </div>

                <button
                    @click="showCreateModal = true"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center justify-center space-x-1.5"
                >
                    <span>➕</span>
                    <span>Tambah Berita Baru</span>
                </button>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50/80 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 text-gray-400 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-4">Cover & Judul Artikel</th>
                                <th class="p-4">Kategori</th>
                                <th class="p-4">Penulis</th>
                                <th class="p-4">Tanggal Publikasi</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                            <tr v-for="item in news.data" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                                <td class="p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-14 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 shrink-0 border border-gray-200 dark:border-gray-700">
                                            <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 font-bold text-[10px]">MKT</div>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white leading-snug line-clamp-1 max-w-md">{{ item.title }}</h4>
                                            <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">{{ item.content }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">
                                        {{ item.category }}
                                    </span>
                                </td>
                                <td class="p-4 font-medium">{{ item.author || 'Humas MKT' }}</td>
                                <td class="p-4 font-mono text-[11px] text-gray-400">{{ item.published_at || item.created_at?.split('T')[0] }}</td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button @click="openEditModal(item)" class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition" title="Edit">
                                            ✏️
                                        </button>
                                        <button @click="deleteNews(item)" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 transition" title="Hapus">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!news.data || news.data.length === 0">
                                <td colspan="5" class="p-12 text-center text-gray-400 italic">
                                    Belum ada artikel berita yang dipublikasikan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="news.links && news.links.length > 3" class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center space-x-1">
                    <template v-for="(link, i) in news.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="[link.active ? 'bg-orange-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300', 'px-3 py-1.5 rounded-lg text-xs transition']"
                        />
                    </template>
                </div>
            </div>

            <!-- Modal Create News -->
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm" @click.self="showCreateModal = false">
                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 max-w-xl w-full p-6 space-y-4 shadow-2xl animate-scaleUp">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">📰 Terbitkan Berita / Artikel Baru</h3>
                        <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form @submit.prevent="submitCreate" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Judul Artikel</label>
                            <input v-model="createForm.title" type="text" required placeholder="Judul berita/liputan..." class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                <select v-model="createForm.category" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5">
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Penulis (Author)</label>
                                <input v-model="createForm.author" type="text" placeholder="Humas MKT" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">URL Gambar Cover (Thumbnail)</label>
                            <input v-model="createForm.image_url" type="url" placeholder="https://..." class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Konten Lengkap Berita</label>
                            <textarea v-model="createForm.content" required rows="6" placeholder="Tulis isi narasi artikel berita..." class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs p-3"></textarea>
                        </div>

                        <div class="pt-2 flex justify-end space-x-2">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs">Batal</button>
                            <button type="submit" :disabled="createForm.processing" class="px-5 py-2.5 rounded-xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 shadow-md">Publikasikan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Edit News -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm" @click.self="showEditModal = false">
                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 max-w-xl w-full p-6 space-y-4 shadow-2xl animate-scaleUp">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">✏️ Edit Berita / Artikel</h3>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form @submit.prevent="submitEdit" class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Judul Artikel</label>
                            <input v-model="editForm.title" type="text" required class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                <select v-model="editForm.category" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5">
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Penulis (Author)</label>
                                <input v-model="editForm.author" type="text" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">URL Gambar Cover (Thumbnail)</label>
                            <input v-model="editForm.image_url" type="url" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Konten Lengkap Berita</label>
                            <textarea v-model="editForm.content" required rows="6" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs p-3"></textarea>
                        </div>

                        <div class="pt-2 flex justify-end space-x-2">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs">Batal</button>
                            <button type="submit" :disabled="editForm.processing" class="px-5 py-2.5 rounded-xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 shadow-md">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
