<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    show: Boolean,
    categories: {
        type: Array,
        default: () => ['Evakuasi', 'Logistik', 'Kesehatan', 'Edukasi', 'Mitigasi', 'Relawan', 'Umum']
    },
    initialContent: {
        type: String,
        default: ''
    },
    initialTitle: {
        type: String,
        default: ''
    },
    initialCategory: {
        type: String,
        default: 'Evakuasi'
    },
    aiConfig: {
        type: Object,
        default: () => ({ hasServerKey: false, provider: 'gemini', model: 'gemini-1.5-flash' })
    }
});

const emit = defineEmits(['close', 'apply']);

// Active Tab: 'generate', 'enhance', 'headlines', 'settings'
const activeTab = ref('generate');

// Settings & API Key
const savedApiKey = ref('');
const activeProvider = ref('gemini');
const showSettingsDrawer = ref(false);

onMounted(() => {
    const localKey = localStorage.getItem('mkt_ai_api_key');
    if (localKey) {
        savedApiKey.value = localKey;
    }
    const localProvider = localStorage.getItem('mkt_ai_provider');
    if (localProvider) {
        activeProvider.value = localProvider;
    }
});

const saveSettings = () => {
    if (savedApiKey.value) {
        localStorage.setItem('mkt_ai_api_key', savedApiKey.value.trim());
    } else {
        localStorage.removeItem('mkt_ai_api_key');
    }
    localStorage.setItem('mkt_ai_provider', activeProvider.value);
    showSettingsDrawer.value = false;

    Swal.fire({
        icon: 'success',
        title: 'Pengaturan AI Disimpan',
        text: 'Kunci API tersimpan di browser untuk pembuatan artikel berita.',
        timer: 1800,
        showConfirmButton: false,
        customClass: { popup: 'rounded-2xl dark:bg-gray-900 dark:text-white' }
    });
};

const hasActiveKey = computed(() => {
    return !!savedApiKey.value || !!props.aiConfig?.hasServerKey;
});

// Generator State
const topic = ref('');
const category = ref(props.initialCategory || 'Evakuasi');
const tone = ref('jurnalistik');
const length = ref('standar');
const isGenerating = ref(false);
const generatedResult = ref(null);

// Enhancer State
const enhanceContentText = ref(props.initialContent || '');
const enhanceAction = ref('polish');
const isEnhancing = ref(false);
const enhancedResultText = ref('');

// Headlines State
const headlineSourceText = ref(props.initialTitle || props.initialContent || '');
const isGeneratingHeadlines = ref(false);
const headlinesList = ref([]);

// Quick Topic Inspirations
const quickPrompts = [
    'Penyaluran 500 paket sembako dan logistik darurat untuk warga terisolir bencana banjir di Maros oleh Tim Rescue MKT bersama BPBD.',
    'Aksi kemanusiaan donor darah serentak bersama PMI, relawan MKT berhasil kumpulkan 180 kantong darah untuk kebutuhan darurat RS.',
    'Pelatihan siaga bencana dan simulasi evakuasi mandiri gempa bumi bagi siswa sekolah oleh divisi edukasi dan mitigasi MKT.',
    'Pusdalops MKT koordinasikan operasi pencarian korban musibah orang hilang di kawasan pesisir bersama Basarnas.',
];

const pickPrompt = (item) => {
    topic.value = item;
};

// Generate Draft Method
const generateArticle = async () => {
    if (!topic.value.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Topik Kosong',
            text: 'Tuliskan topik atau poin singkat berita terlebih dahulu.',
            customClass: { popup: 'rounded-2xl dark:bg-gray-900 dark:text-white' }
        });
        return;
    }

    isGenerating.value = true;
    try {
        const res = await fetch(route('news.ai.generate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                topic: topic.value,
                category: category.value,
                tone: tone.value,
                length: length.value,
                api_key: savedApiKey.value || null,
                provider: activeProvider.value,
            })
        });

        const data = await res.json();
        if (data.success) {
            generatedResult.value = data;
        } else {
            throw new Error(data.message || 'Gagal menghasilkan artikel.');
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Membuat Artikel',
            text: err.message || 'Terjadi kesalahan saat memproses dengan AI.',
            customClass: { popup: 'rounded-2xl dark:bg-gray-900 dark:text-white' }
        });
    } finally {
        isGenerating.value = false;
    }
};

// Enhance Content Method
const enhanceContent = async () => {
    if (!enhanceContentText.value.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Teks Kosong',
            text: 'Masukkan draf teks berita yang ingin disempurnakan.',
            customClass: { popup: 'rounded-2xl dark:bg-gray-900 dark:text-white' }
        });
        return;
    }

    isEnhancing.value = true;
    try {
        const res = await fetch(route('news.ai.enhance'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                content: enhanceContentText.value,
                action: enhanceAction.value,
                api_key: savedApiKey.value || null,
                provider: activeProvider.value,
            })
        });

        const data = await res.json();
        if (data.success && data.content) {
            enhancedResultText.value = data.content;
        } else {
            throw new Error(data.message || 'Gagal memproses penyempurnaan teks.');
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menyempurnakan Teks',
            text: err.message || 'Terjadi kendala saat memproses teks.',
            customClass: { popup: 'rounded-2xl dark:bg-gray-900 dark:text-white' }
        });
    } finally {
        isEnhancing.value = false;
    }
};

// Generate Headlines Method
const generateHeadlines = async () => {
    const textToAnalyze = headlineSourceText.value.trim() || topic.value.trim();
    if (!textToAnalyze) {
        Swal.fire({
            icon: 'warning',
            title: 'Teks Kosong',
            text: 'Masukkan topik atau draf artikel untuk dianalisis judulnya.',
            customClass: { popup: 'rounded-2xl dark:bg-gray-900 dark:text-white' }
        });
        return;
    }

    isGeneratingHeadlines.value = true;
    try {
        const res = await fetch(route('news.ai.headlines'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                content: textToAnalyze,
                api_key: savedApiKey.value || null,
                provider: activeProvider.value,
            })
        });

        const data = await res.json();
        if (data.success && data.headlines) {
            headlinesList.value = data.headlines;
        } else {
            throw new Error(data.message || 'Gagal merekomendasikan judul.');
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Merekomendasikan Judul',
            text: err.message || 'Terjadi kesalahan.',
            customClass: { popup: 'rounded-2xl dark:bg-gray-900 dark:text-white' }
        });
    } finally {
        isGeneratingHeadlines.value = false;
    }
};

// Copy to Clipboard
const copyText = (text, label = 'Teks') => {
    navigator.clipboard.writeText(text);
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: `${label} berhasil disalin!`,
        showConfirmButton: false,
        timer: 1500
    });
};

// Apply to Form
const applyResult = (item = null) => {
    const payload = item || generatedResult.value;
    if (!payload) return;

    emit('apply', {
        title: payload.title || props.initialTitle,
        category: payload.category || category.value,
        content: payload.content || '',
        summary: payload.summary || '',
        tags: payload.tags || [],
    });

    emit('close');

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '✨ Data AI berhasil diterapkan ke form berita!',
        showConfirmButton: false,
        timer: 2000
    });
};

const applyEnhanced = () => {
    if (!enhancedResultText.value) return;
    emit('apply', {
        content: enhancedResultText.value
    });
    emit('close');
};

const applyHeadline = (headline) => {
    emit('apply', {
        title: headline
    });
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-md transition-all">
        <!-- Main Modal Container -->
        <div class="relative bg-white dark:bg-gray-900 rounded-3xl border border-orange-500/20 dark:border-orange-500/30 w-full max-w-4xl shadow-2xl shadow-orange-950/20 max-h-[92vh] flex flex-col overflow-hidden animate-scaleUp">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-orange-500/10 via-amber-500/5 to-transparent">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center text-lg shadow-md shadow-orange-500/30">
                        ✨
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                                Asisten AI Berita & Artikel MKT
                            </h3>
                            <span 
                                :class="[
                                    hasActiveKey 
                                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' 
                                        : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                    'text-[10px] font-bold px-2 py-0.5 rounded-full border flex items-center space-x-1'
                                ]"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :class="hasActiveKey ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                                <span>{{ hasActiveKey ? (savedApiKey ? 'Gemini API Aktif' : 'Server AI Siaga') : 'Mode Cerdas MKT' }}</span>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Bantu redaksi membuat, memoles, dan merancang judul artikel kemanusiaan 5W+1H secara instan.
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button
                        type="button"
                        @click="showSettingsDrawer = !showSettingsDrawer"
                        :class="[
                            showSettingsDrawer ? 'bg-orange-500 text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white bg-gray-100 dark:bg-gray-800',
                            'p-2 rounded-xl text-xs font-bold transition flex items-center space-x-1'
                        ]"
                        title="Pengaturan API AI"
                    >
                        <span>⚙️</span>
                        <span class="hidden sm:inline">Kunci API</span>
                    </button>
                    <button
                        type="button"
                        @click="emit('close')"
                        class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                    >
                        ✕
                    </button>
                </div>
            </div>

            <!-- Settings Drawer (Collapsible) -->
            <div 
                v-if="showSettingsDrawer" 
                class="px-6 py-4 bg-orange-50/60 dark:bg-gray-800/80 border-b border-orange-200 dark:border-orange-900/40 text-xs space-y-3 transition-all"
            >
                <div class="flex items-center justify-between">
                    <div class="font-bold text-gray-800 dark:text-gray-200 flex items-center space-x-2">
                        <span>🔑 Pengaturan API AI (Google Gemini / OpenAI)</span>
                        <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400">
                            (Opsional — Tersimpan aman di browser Anda)
                        </span>
                    </div>
                    <a 
                        href="https://aistudio.google.com/app/apikey" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="text-orange-600 hover:text-orange-700 dark:text-orange-400 font-bold underline flex items-center space-x-1"
                    >
                        <span>Dapatkan Kunci Gratis di Google AI Studio ↗</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Penyedia Layanan (Provider)</label>
                        <select v-model="activeProvider" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs py-2 px-3 focus:ring-orange-500">
                            <option value="gemini">Google Gemini (Gratis & Direkomendasikan)</option>
                            <option value="openai">OpenAI (GPT-4o Mini)</option>
                            <option value="groq">Groq (Llama 3.3)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kunci API (API Key)</label>
                        <div class="flex space-x-2">
                            <input 
                                v-model="savedApiKey" 
                                type="password" 
                                placeholder="Tempel AIzaSy... atau sk-..." 
                                class="flex-1 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs py-2 px-3 focus:ring-orange-500 font-mono"
                            />
                            <button 
                                type="button" 
                                @click="saveSettings" 
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl shadow-sm transition"
                            >
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                    💡 <em>Jika Anda tidak memiliki API Key, sistem tetap berfungsi penuh dengan <strong>Mesin Jurnalistik Cerdas MKT</strong> berformat 5W+1H untuk artikel kemanusiaan.</em>
                </p>
            </div>

            <!-- Navigation Sub-Tabs -->
            <div class="flex items-center space-x-1 px-6 pt-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/20 text-xs font-bold">
                <button
                    type="button"
                    @click="activeTab = 'generate'"
                    :class="[
                        activeTab === 'generate'
                            ? 'border-b-2 border-orange-500 text-orange-600 dark:text-orange-400 bg-white dark:bg-gray-900'
                            : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200',
                        'px-4 py-2.5 rounded-t-xl transition flex items-center space-x-1.5'
                    ]"
                >
                    <span>✍️</span>
                    <span>Buat Draf Berita Lengkap</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'enhance'"
                    :class="[
                        activeTab === 'enhance'
                            ? 'border-b-2 border-orange-500 text-orange-600 dark:text-orange-400 bg-white dark:bg-gray-900'
                            : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200',
                        'px-4 py-2.5 rounded-t-xl transition flex items-center space-x-1.5'
                    ]"
                >
                    <span>🪄</span>
                    <span>Poles & Kembangkan Teks</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'headlines'"
                    :class="[
                        activeTab === 'headlines'
                            ? 'border-b-2 border-orange-500 text-orange-600 dark:text-orange-400 bg-white dark:bg-gray-900'
                            : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200',
                        'px-4 py-2.5 rounded-t-xl transition flex items-center space-x-1.5'
                    ]"
                >
                    <span>💡</span>
                    <span>Ide & Rekomendasi Judul</span>
                </button>
            </div>

            <!-- Tab 1: Generate Draft Article -->
            <div v-if="activeTab === 'generate'" class="p-6 overflow-y-auto space-y-5 flex-1">
                
                <!-- Topic Input Box -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                            Topik atau Poin-Poin Berita yang Terjadi
                        </label>
                        <span class="text-[11px] text-gray-400">
                            Cukup tulis poin singkat, AI akan menyusun narasi 5W+1H
                        </span>
                    </div>
                    
                    <textarea 
                        v-model="topic" 
                        rows="3" 
                        placeholder="Contoh: Tim rescue MKT terjun ke lokasi banjir Maros untuk evakuasi 15 lansia dan salurkan 300 paket sembako bersama Basarnas dan PMI..."
                        class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 text-xs p-3 focus:ring-orange-500 focus:border-orange-500 dark:text-white"
                    ></textarea>

                    <!-- Quick Inspo Prompts -->
                    <div class="space-y-1">
                        <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Contoh Cepat (Klik untuk mencoba):</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="(p, i) in quickPrompts"
                                :key="i"
                                type="button"
                                @click="pickPrompt(p)"
                                class="text-[11px] px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-orange-100 dark:bg-gray-800 dark:hover:bg-orange-950/40 text-gray-600 dark:text-gray-300 hover:text-orange-700 dark:hover:text-orange-300 transition text-left truncate max-w-xs"
                            >
                                ⚡ {{ p.slice(0, 45) }}...
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Parameters Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori Berita</label>
                        <select v-model="category" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:ring-orange-500">
                            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Gaya Penulisan (Tone)</label>
                        <select v-model="tone" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:ring-orange-500">
                            <option value="jurnalistik">📰 Jurnalistik Resmi & Berimbang</option>
                            <option value="humanis">💖 Humanis & Menyentuh Empati</option>
                            <option value="darurat">🚨 Laporan Siaga & Tanggap Darurat</option>
                            <option value="edukatif">💡 Edukatif & Mitigasi Bencana</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Panjang Artikel</label>
                        <select v-model="length" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs py-2.5 px-3 focus:ring-orange-500">
                            <option value="singkat">Ringkas (2 - 3 Paragraf)</option>
                            <option value="standar">Standar (4 - 5 Paragraf)</option>
                            <option value="panjang">Mendalam & Lengkap (5 - 7 Paragraf)</option>
                        </select>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex justify-end pt-1">
                    <button
                        type="button"
                        @click="generateArticle"
                        :disabled="isGenerating || !topic.trim()"
                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 hover:from-orange-700 hover:to-amber-600 text-white font-bold text-xs shadow-lg shadow-orange-500/25 flex items-center space-x-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="isGenerating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="!isGenerating">✨ Buat Draf Artikel Sekarang</span>
                        <span v-else>Menyusun Artikel dengan AI...</span>
                    </button>
                </div>

                <!-- Generated Output Preview Area -->
                <div v-if="generatedResult" class="mt-4 p-5 rounded-3xl bg-orange-50/40 dark:bg-gray-800/60 border border-orange-200 dark:border-gray-700 space-y-4 animate-fadeIn">
                    
                    <!-- Header Info Bar -->
                    <div class="flex items-center justify-between pb-3 border-b border-orange-200/60 dark:border-gray-700">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-extrabold uppercase px-2.5 py-1 rounded-lg bg-orange-600 text-white">
                                {{ generatedResult.category }}
                            </span>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                Sumber: {{ generatedResult.source === 'gemini' ? 'Google Gemini 1.5 Flash' : (generatedResult.source === 'openai' ? 'OpenAI GPT' : 'Mesin Cerdas MKT') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button
                                type="button"
                                @click="copyText(generatedResult.content, 'Konten berita')"
                                class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-xs font-bold hover:text-orange-600 transition"
                            >
                                📋 Salin Teks
                            </button>
                            <button
                                type="button"
                                @click="applyResult()"
                                class="px-4 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow transition"
                            >
                                📥 Terapkan ke Form Berita
                            </button>
                        </div>
                    </div>

                    <!-- Notice banner if any -->
                    <div v-if="generatedResult.notice" class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 text-[11px] text-amber-800 dark:text-amber-300">
                        {{ generatedResult.notice }}
                    </div>

                    <!-- Proposed Title -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Judul yang Diusulkan</label>
                        <input 
                            v-model="generatedResult.title" 
                            type="text" 
                            class="w-full font-bold text-sm text-gray-900 dark:text-white rounded-xl bg-white dark:bg-gray-900 border border-orange-300 dark:border-gray-700 p-2.5"
                        />
                    </div>

                    <!-- Summary / Excerpt -->
                    <div v-if="generatedResult.summary">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Ringkasan Cuplikan (Excerpt)</label>
                        <p class="text-xs text-gray-700 dark:text-gray-300 italic bg-white/70 dark:bg-gray-900/70 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                            "{{ generatedResult.summary }}"
                        </p>
                    </div>

                    <!-- Article Body -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Isi Narasi Berita Lengkap</label>
                        <textarea 
                            v-model="generatedResult.content" 
                            rows="9" 
                            class="w-full text-xs text-gray-800 dark:text-gray-200 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3.5 leading-relaxed font-sans"
                        ></textarea>
                    </div>

                    <!-- Tags -->
                    <div v-if="generatedResult.tags && generatedResult.tags.length" class="flex items-center space-x-2">
                        <span class="text-[11px] font-bold text-gray-400">Rekomendasi Tag:</span>
                        <div class="flex flex-wrap gap-1">
                            <span 
                                v-for="tag in generatedResult.tags" 
                                :key="tag" 
                                class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-orange-100 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300"
                            >
                                #{{ tag }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Enhance & Polish Existing Content -->
            <div v-if="activeTab === 'enhance'" class="p-6 overflow-y-auto space-y-5 flex-1">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                        Draf Teks Berita yang Ingin Disempurnakan
                    </label>
                    <textarea 
                        v-model="enhanceContentText" 
                        rows="6" 
                        placeholder="Tempel atau ketik naskah artikel Anda di sini..."
                        class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 text-xs p-3.5 focus:ring-orange-500 focus:border-orange-500 dark:text-white"
                    ></textarea>
                </div>

                <!-- Action Selectors -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button
                        type="button"
                        @click="enhanceAction = 'polish'"
                        :class="[
                            enhanceAction === 'polish' 
                                ? 'bg-orange-500 text-white font-bold' 
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200',
                            'p-3 rounded-2xl text-xs text-center transition flex flex-col items-center justify-center space-y-1'
                        ]"
                    >
                        <span class="text-base">🪄</span>
                        <span>Perbaiki EYD & Tata Bahasa</span>
                    </button>

                    <button
                        type="button"
                        @click="enhanceAction = 'expand'"
                        :class="[
                            enhanceAction === 'expand' 
                                ? 'bg-orange-500 text-white font-bold' 
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200',
                            'p-3 rounded-2xl text-xs text-center transition flex flex-col items-center justify-center space-y-1'
                        ]"
                    >
                        <span class="text-base">📈</span>
                        <span>Kembangkan Lebih Detail</span>
                    </button>

                    <button
                        type="button"
                        @click="enhanceAction = 'shorten'"
                        :class="[
                            enhanceAction === 'shorten' 
                                ? 'bg-orange-500 text-white font-bold' 
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200',
                            'p-3 rounded-2xl text-xs text-center transition flex flex-col items-center justify-center space-y-1'
                        ]"
                    >
                        <span class="text-base">📉</span>
                        <span>Ringkas Jadi 2-3 Paragraf</span>
                    </button>

                    <button
                        type="button"
                        @click="enhanceAction = 'humanize'"
                        :class="[
                            enhanceAction === 'humanize' 
                                ? 'bg-orange-500 text-white font-bold' 
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200',
                            'p-3 rounded-2xl text-xs text-center transition flex flex-col items-center justify-center space-y-1'
                        ]"
                    >
                        <span class="text-base">💖</span>
                        <span>Poles Lebih Humanis & Empatik</span>
                    </button>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        @click="enhanceContent"
                        :disabled="isEnhancing || !enhanceContentText.trim()"
                        class="px-6 py-3 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-md transition disabled:opacity-50"
                    >
                        <span v-if="!isEnhancing">🪄 Poles Teks Sekarang</span>
                        <span v-else>Memproses Pemolesan...</span>
                    </button>
                </div>

                <!-- Enhance Result -->
                <div v-if="enhancedResultText" class="p-5 rounded-3xl bg-orange-50/40 dark:bg-gray-800/60 border border-orange-200 dark:border-gray-700 space-y-3 animate-fadeIn">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center space-x-1">
                            <span>✓</span>
                            <span>Hasil Teks yang Telah Disunting</span>
                        </span>
                        <div class="flex items-center space-x-2">
                            <button
                                type="button"
                                @click="copyText(enhancedResultText, 'Teks hasil poles')"
                                class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-xs font-bold"
                            >
                                📋 Salin
                            </button>
                            <button
                                type="button"
                                @click="applyEnhanced"
                                class="px-4 py-1.5 rounded-xl bg-orange-600 text-white text-xs font-bold"
                            >
                                📥 Terapkan ke Form
                            </button>
                        </div>
                    </div>

                    <textarea 
                        v-model="enhancedResultText" 
                        rows="8" 
                        class="w-full text-xs text-gray-800 dark:text-gray-200 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3.5 leading-relaxed"
                    ></textarea>
                </div>
            </div>

            <!-- Tab 3: Headline Suggestions -->
            <div v-if="activeTab === 'headlines'" class="p-6 overflow-y-auto space-y-5 flex-1">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                        Topik atau Cuplikan Konten yang Ingin Dicarikan Judul
                    </label>
                    <textarea 
                        v-model="headlineSourceText" 
                        rows="3" 
                        placeholder="Ketik topik atau tempel naskah artikel..."
                        class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 text-xs p-3.5 focus:ring-orange-500 focus:border-orange-500 dark:text-white"
                    ></textarea>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        @click="generateHeadlines"
                        :disabled="isGeneratingHeadlines || !headlineSourceText.trim()"
                        class="px-6 py-3 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-md transition disabled:opacity-50"
                    >
                        <span v-if="!isGeneratingHeadlines">💡 Berikan 5 Rekomendasi Judul</span>
                        <span v-else>Menganalisis Judul...</span>
                    </button>
                </div>

                <!-- Headlines List -->
                <div v-if="headlinesList && headlinesList.length" class="space-y-2.5 pt-2 animate-fadeIn">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-2">
                        Pilih salah satu judul di bawah ini untuk diterapkan:
                    </span>
                    <div 
                        v-for="(headline, idx) in headlinesList" 
                        :key="idx" 
                        class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-orange-500 dark:hover:border-orange-500 transition flex items-center justify-between group shadow-sm"
                    >
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-950 text-orange-600 dark:text-orange-400 font-bold text-xs flex items-center justify-center shrink-0">
                                {{ idx + 1 }}
                            </span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition">
                                {{ headline }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-2 shrink-0">
                            <button
                                type="button"
                                @click="copyText(headline, 'Judul')"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs"
                                title="Salin Judul"
                            >
                                📋
                            </button>
                            <button
                                type="button"
                                @click="applyHeadline(headline)"
                                class="px-3 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-sm transition"
                            >
                                Pilih Judul Ini
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950/40 flex items-center justify-between text-xs text-gray-400">
                <span>Yayasan Mitra Kemanusiaan Terpadu (MKT Indonesia)</span>
                <button type="button" @click="emit('close')" class="font-bold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes scaleUp {
    from {
        opacity: 0;
        transform: scale(0.96);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-scaleUp {
    animation: scaleUp 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.25s ease-out forwards;
}
</style>
