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

// Create Form State
const createFileInput = ref(null);
const createPreviewUrl = ref(null);
const createMode = ref('file'); // 'file' or 'url'

const createForm = useForm({
    title: '',
    category: 'Evakuasi',
    author: '',
    image_url: '',
    image_file: null,
    content: '',
    published_at: new Date().toISOString().split('T')[0],
});

// Edit Form State
const editFileInput = ref(null);
const editPreviewUrl = ref(null);
const editMode = ref('file'); // 'file' or 'url'

const editForm = useForm({
    title: '',
    category: 'Evakuasi',
    author: '',
    image_url: '',
    image_file: null,
    content: '',
    published_at: '',
});

const triggerCreatePicker = () => {
    createFileInput.value?.click();
};

const handleCreateImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        createForm.image_file = file;
        createPreviewUrl.value = URL.createObjectURL(file);
    }
};

const removeCreateImage = () => {
    createForm.image_file = null;
    createPreviewUrl.value = null;
    if (createFileInput.value) createFileInput.value.value = '';
};

const submitCreate = () => {
    createForm.post(route('news.store'), {
        forceFormData: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            createPreviewUrl.value = null;
        },
    });
};

const triggerEditPicker = () => {
    editFileInput.value?.click();
};

const handleEditImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        editForm.image_file = file;
        editPreviewUrl.value = URL.createObjectURL(file);
    }
};

const removeEditImage = () => {
    editForm.image_file = null;
    editPreviewUrl.value = null;
    if (editFileInput.value) editFileInput.value.value = '';
};

const openEditModal = (item) => {
    editingNews.value = item;
    editForm.title = item.title;
    editForm.category = item.category;
    editForm.author = item.author;
    editForm.image_url = item.image_url;
    editForm.image_file = null;
    editForm.content = item.content;
    editForm.published_at = item.published_at || item.created_at?.split('T')[0];
    editPreviewUrl.value = item.image_url || null;
    editMode.value = item.image_url && item.image_url.startsWith('http') ? 'url' : 'file';
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!editingNews.value) return;
    editForm.post(route('news.update', editingNews.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showEditModal.value = false;
            editingNews.value = null;
            editPreviewUrl.value = null;
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
                                        <div class="w-16 h-12 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 shrink-0 border border-gray-200 dark:border-gray-700 shadow-xs">
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
                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 max-w-xl w-full p-6 space-y-4 shadow-2xl animate-scaleUp max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">📰 Terbitkan Berita / Artikel Baru</h3>
                        <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Judul Artikel</label>
                            <input v-model="createForm.title" type="text" required placeholder="Judul berita/liputan..." class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                <select v-model="createForm.category" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500">
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Penulis (Author)</label>
                                <input v-model="createForm.author" type="text" placeholder="Humas MKT" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500" />
                            </div>
                        </div>

                        <!-- Image Selector: Device Gallery vs URL -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Gambar Cover (Thumbnail)</label>
                                <div class="flex items-center space-x-1 text-[11px] font-bold">
                                    <button
                                        type="button"
                                        @click="createMode = 'file'"
                                        class="px-2 py-0.5 rounded-lg transition"
                                        :class="createMode === 'file' ? 'bg-orange-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                    >
                                        📁 Dari Galeri Perangkat
                                    </button>
                                    <button
                                        type="button"
                                        @click="createMode = 'url'"
                                        class="px-2 py-0.5 rounded-lg transition"
                                        :class="createMode === 'url' ? 'bg-orange-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                    >
                                        🔗 Link URL
                                    </button>
                                </div>
                            </div>

                            <!-- Mode File / Device Gallery Upload -->
                            <div v-if="createMode === 'file'" class="space-y-2">
                                <input
                                    ref="createFileInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleCreateImageChange"
                                />

                                <!-- Dropzone / Click to choose -->
                                <div 
                                    v-if="!createPreviewUrl"
                                    @click="triggerCreatePicker"
                                    class="border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-orange-500 dark:hover:border-orange-500 bg-gray-50 dark:bg-gray-800/50 hover:bg-orange-50/20 rounded-2xl p-6 text-center cursor-pointer transition-all flex flex-col items-center justify-center space-y-2 group"
                                >
                                    <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                        🖼️
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                            Klik untuk memilih foto dari galeri / penyimpanan perangkat
                                        </p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">
                                            Format: JPG, PNG, WEBP, GIF (Maksimal 5MB)
                                        </p>
                                    </div>
                                </div>

                                <!-- Image Preview Card -->
                                <div v-else class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900 group">
                                    <img :src="createPreviewUrl" alt="Preview Cover" class="w-full h-44 object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end justify-between p-3">
                                        <span class="text-xs text-white font-medium truncate max-w-xs">
                                            Foto Terpilih: {{ createForm.image_file?.name || 'Gambar Galeri' }}
                                        </span>
                                        <div class="flex items-center space-x-2">
                                            <button
                                                type="button"
                                                @click="triggerCreatePicker"
                                                class="px-2.5 py-1 bg-white/90 hover:bg-white text-gray-900 rounded-lg text-xs font-bold shadow-md transition"
                                            >
                                                Ganti Foto
                                            </button>
                                            <button
                                                type="button"
                                                @click="removeCreateImage"
                                                class="p-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs transition"
                                                title="Hapus Foto"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mode URL Input -->
                            <div v-else>
                                <input 
                                    v-model="createForm.image_url" 
                                    type="url" 
                                    placeholder="https://images.unsplash.com/..." 
                                    class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500" 
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Konten Lengkap Berita</label>
                            <textarea v-model="createForm.content" required rows="6" placeholder="Tulis isi narasi artikel berita..." class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs p-3 focus:border-brand-500 focus:ring-brand-500"></textarea>
                        </div>

                        <div class="pt-2 flex justify-end space-x-2">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs">Batal</button>
                            <button type="submit" :disabled="createForm.processing" class="px-5 py-2.5 rounded-xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 shadow-md">
                                {{ createForm.processing ? 'Mengunggah & Menerbitkan...' : 'Publikasikan Berita' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Edit News -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm" @click.self="showEditModal = false">
                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 max-w-xl w-full p-6 space-y-4 shadow-2xl animate-scaleUp max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">✏️ Edit Berita / Artikel</h3>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Judul Artikel</label>
                            <input v-model="editForm.title" type="text" required class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                <select v-model="editForm.category" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500">
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Penulis (Author)</label>
                                <input v-model="editForm.author" type="text" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500" />
                            </div>
                        </div>

                        <!-- Image Selector: Device Gallery vs URL for Edit -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Gambar Cover (Thumbnail)</label>
                                <div class="flex items-center space-x-1 text-[11px] font-bold">
                                    <button
                                        type="button"
                                        @click="editMode = 'file'"
                                        class="px-2 py-0.5 rounded-lg transition"
                                        :class="editMode === 'file' ? 'bg-orange-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                    >
                                        📁 Dari Galeri Perangkat
                                    </button>
                                    <button
                                        type="button"
                                        @click="editMode = 'url'"
                                        class="px-2 py-0.5 rounded-lg transition"
                                        :class="editMode === 'url' ? 'bg-orange-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                    >
                                        🔗 Link URL
                                    </button>
                                </div>
                            </div>

                            <!-- Mode File / Device Gallery Upload -->
                            <div v-if="editMode === 'file'" class="space-y-2">
                                <input
                                    ref="editFileInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleEditImageChange"
                                />

                                <!-- Dropzone / Click to choose -->
                                <div 
                                    v-if="!editPreviewUrl"
                                    @click="triggerEditPicker"
                                    class="border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-orange-500 dark:hover:border-orange-500 bg-gray-50 dark:bg-gray-800/50 hover:bg-orange-50/20 rounded-2xl p-6 text-center cursor-pointer transition-all flex flex-col items-center justify-center space-y-2 group"
                                >
                                    <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                        🖼️
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                            Klik untuk memilih foto dari galeri / penyimpanan perangkat
                                        </p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">
                                            Format: JPG, PNG, WEBP, GIF (Maksimal 5MB)
                                        </p>
                                    </div>
                                </div>

                                <!-- Image Preview Card -->
                                <div v-else class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900 group">
                                    <img :src="editPreviewUrl" alt="Preview Cover" class="w-full h-44 object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end justify-between p-3">
                                        <span class="text-xs text-white font-medium truncate max-w-xs">
                                            {{ editForm.image_file?.name || 'Foto Sampul Aktif' }}
                                        </span>
                                        <div class="flex items-center space-x-2">
                                            <button
                                                type="button"
                                                @click="triggerEditPicker"
                                                class="px-2.5 py-1 bg-white/90 hover:bg-white text-gray-900 rounded-lg text-xs font-bold shadow-md transition"
                                            >
                                                Ganti Foto
                                            </button>
                                            <button
                                                type="button"
                                                @click="removeEditImage"
                                                class="p-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs transition"
                                                title="Hapus Foto"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mode URL Input -->
                            <div v-else>
                                <input 
                                    v-model="editForm.image_url" 
                                    type="url" 
                                    placeholder="https://images.unsplash.com/..." 
                                    class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:border-brand-500 focus:ring-brand-500" 
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Konten Lengkap Berita</label>
                            <textarea v-model="editForm.content" required rows="6" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs p-3 focus:border-brand-500 focus:ring-brand-500"></textarea>
                        </div>

                        <div class="pt-2 flex justify-end space-x-2">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs">Batal</button>
                            <button type="submit" :disabled="editForm.processing" class="px-5 py-2.5 rounded-xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 shadow-md">
                                {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
