<script setup>
import { ref, onMounted, reactive } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    meeting: Object,
    flash: Object,
});

const form = useForm({
    name: '',
    phone: '',
    email: '',
    institution: '',
    custom_institution: '',
    position: '',
    signature: '',
    notes: '',
});

// Canvas Signature
const signatureCanvas = ref(null);
const isDrawing = ref(false);
const hasSignature = ref(false);
let ctx = null;

const setupCanvas = () => {
    const canvas = signatureCanvas.value;
    if (!canvas) return;

    // Adjust for high-DPI displays
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;

    ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#1e293b'; // slate-800
};

const getCanvasCoordinates = (e) => {
    const canvas = signatureCanvas.value;
    if (!canvas) return { x: 0, y: 0 };
    const rect = canvas.getBoundingClientRect();

    if (e.touches && e.touches.length > 0) {
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top
        };
    }
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
};

const startDrawing = (e) => {
    if (!ctx) setupCanvas();
    isDrawing.value = true;
    const { x, y } = getCanvasCoordinates(e);
    ctx.beginPath();
    ctx.moveTo(x, y);
};

const draw = (e) => {
    if (!isDrawing.value || !ctx) return;
    e.preventDefault(); // Stop scrolling while signing
    const { x, y } = getCanvasCoordinates(e);
    ctx.lineTo(x, y);
    ctx.stroke();
    hasSignature.value = true;
};

const stopDrawing = () => {
    if (!isDrawing.value) return;
    isDrawing.value = false;
    if (signatureCanvas.value && hasSignature.value) {
        form.signature = signatureCanvas.value.toDataURL('image/png');
    }
};

const clearSignature = () => {
    if (!signatureCanvas.value || !ctx) return;
    const canvas = signatureCanvas.value;
    const dpr = window.devicePixelRatio || 1;
    ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
    hasSignature.value = false;
    form.signature = '';
};

// Preset instansi
const commonInstitutions = [
    'Yayasan MKT Indonesia (Internal)',
    'BASARNAS',
    'BPBD Prov / Kota / Kab',
    'Palang Merah Indonesia (PMI)',
    'TNI / POLRI',
    'Dinas Kesehatan / RS / Medis',
    'Organisasi Relawan / NGO',
    'Media Pers / Jurnalis',
    'Lainnya (Tulis Manual)'
];

const selectedInstitutionType = ref('');

const onInstitutionSelect = (val) => {
    if (val === 'Lainnya (Tulis Manual)') {
        form.institution = '';
    } else {
        form.institution = val;
    }
};

const showSubmittedCard = ref(Boolean(props.flash?.submittedData));

const submitAttendance = () => {
    if (selectedInstitutionType.value === 'Lainnya (Tulis Manual)' && form.custom_institution) {
        form.institution = form.custom_institution;
    }

    form.post(route('public.attendance.submit', props.meeting.attendance_token), {
        preserveScroll: true,
        onSuccess: () => {
            showSubmittedCard.value = true;
            clearSignature();
            form.reset();
        }
    });
};

const resetForAnotherAttendee = () => {
    showSubmittedCard.value = false;
    clearSignature();
    form.reset();
    selectedInstitutionType.value = '';
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch (e) {
        return dateStr;
    }
};

onMounted(() => {
    // Canvas setup delayed slightly to allow DOM bounding rect computation
    setTimeout(() => {
        setupCanvas();
    }, 200);

    window.addEventListener('resize', () => {
        if (!hasSignature.value) setupCanvas();
    });
});
</script>

<template>
    <Head :title="`Presensi: ${meeting.title} - MKT Indonesia`">
        <meta name="description" content="Formulir absensi kehadiran digital kegiatan Yayasan MKT Indonesia." />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-950 to-orange-950/40 text-slate-100 flex flex-col justify-between p-3 sm:p-6 antialiased">
        <!-- Top Navigation / Branding Bar -->
        <header class="max-w-xl mx-auto w-full flex items-center justify-between py-3 border-b border-white/10 mb-4 sm:mb-6">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-orange-600 to-amber-500 p-0.5 shadow-lg shadow-orange-500/20 group-hover:scale-105 transition-transform flex items-center justify-center">
                    <img src="/storage/logo/mkt.png" alt="MKT Logo" class="w-full h-full object-contain rounded-lg p-1 bg-white" onerror="this.src='/favicon.ico'; this.classList.remove('bg-white')" />
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-black tracking-tight text-white group-hover:text-orange-400 transition-colors">
                        YAYASAN MKT INDONESIA
                    </h1>
                    <p class="text-[10px] text-orange-300/80 font-medium tracking-wide">
                        E-PRESENSI KEGIATAN & AGENDA RESMI
                    </p>
                </div>
            </a>
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/5 border border-white/10 text-[11px] text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Live Presensi</span>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="max-w-xl mx-auto w-full flex-1">
            <!-- Event Card Banner -->
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-5 mb-5 shadow-2xl relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-orange-500/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider uppercase bg-orange-500/20 text-orange-300 border border-orange-500/30">
                        {{ meeting.category || 'Agenda Kegiatan' }}
                    </span>
                    <span class="text-xs text-slate-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <b>{{ meeting.total_attended || 0 }}</b> Terdaftar Hadir
                    </span>
                </div>

                <h2 class="text-lg sm:text-xl font-black text-white leading-snug mb-3">
                    {{ meeting.title }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-300 pt-2 border-t border-white/10">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ formatDate(meeting.meeting_date) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="truncate">{{ meeting.location || 'Posko MKT Indonesia' }}</span>
                    </div>
                </div>
            </div>

            <!-- ATTENDANCE CLOSED STATE -->
            <div v-if="!meeting.is_attendance_open" class="bg-red-500/10 border border-red-500/30 rounded-2xl p-6 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-red-500/20 text-red-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-base font-bold text-white">Sesi Presensi Telah Ditutup</h3>
                <p class="text-xs text-slate-300 max-w-md mx-auto leading-relaxed">
                    Mohon maaf, presensi kehadiran untuk kegiatan ini telah dinonaktifkan atau ditutup oleh panitia / pimpinan acara.
                </p>
                <div class="pt-3">
                    <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-orange-400 hover:text-orange-300">
                        <span>← Kembali ke Halaman Utama</span>
                    </a>
                </div>
            </div>

            <!-- SUCCESS SUBMITTED CARD / PASS -->
            <div v-else-if="showSubmittedCard" class="bg-slate-900 border border-emerald-500/40 rounded-2xl p-6 shadow-2xl relative space-y-5 animate-fade-in">
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center ring-4 ring-emerald-500/10">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Presensi Berhasil Dicatat
                    </span>
                    <h3 class="text-lg font-black text-white">
                        Terima Kasih Atas Kehadiran Anda!
                    </h3>
                    <p class="text-xs text-slate-300">
                        Data kehadiran Anda telah tercatat secara resmi dalam sistem administrasi MKT Indonesia.
                    </p>
                </div>

                <!-- Digital Ticket / Receipt -->
                <div class="bg-slate-950/80 rounded-xl p-4 border border-white/10 space-y-2.5 text-xs">
                    <div class="flex justify-between pb-2 border-b border-white/5">
                        <span class="text-slate-400">Nama Peserta / Tamu:</span>
                        <span class="font-bold text-white text-right">{{ flash?.submittedData?.name || 'Tercatat' }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-white/5">
                        <span class="text-slate-400">Instansi / Lembaga:</span>
                        <span class="font-medium text-slate-200 text-right">{{ flash?.submittedData?.institution || '-' }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-white/5">
                        <span class="text-slate-400">Waktu Presensi:</span>
                        <span class="font-medium text-emerald-400 text-right">{{ flash?.submittedData?.attended_at || 'Baru Saja' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Agenda Kegiatan:</span>
                        <span class="font-semibold text-orange-300 text-right truncate max-w-[200px]">{{ meeting.title }}</span>
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <button 
                        type="button" 
                        @click="resetForAnotherAttendee"
                        class="w-full py-3 px-4 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs tracking-wide transition shadow-lg shadow-orange-600/30 flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Isi Presensi Peserta / Tamu Lainnya</span>
                    </button>
                    <a 
                        href="/" 
                        class="w-full py-2.5 px-4 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 font-semibold text-xs tracking-wide transition flex items-center justify-center"
                    >
                        Selesai & Ke Beranda
                    </a>
                </div>
            </div>

            <!-- ATTENDANCE FORM -->
            <div v-else class="bg-slate-900/90 backdrop-blur-xl border border-white/15 rounded-2xl p-5 sm:p-6 shadow-2xl space-y-5">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-4 rounded-full bg-orange-500"></span>
                        Formulir Presensi Kehadiran
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Silakan lengkapi identitas Anda di bawah ini sebagai bukti kehadiran kegiatan.
                    </p>
                </div>

                <form @submit.prevent="submitAttendance" class="space-y-4">
                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">
                            Nama Lengkap <span class="text-red-400">*</span>
                        </label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            required 
                            placeholder="Contoh: Dr. Budi Prasetyo, S.Sos"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition"
                        />
                        <p v-if="form.errors.name" class="text-[11px] text-red-400 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Nomor WhatsApp / HP -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">
                            Nomor WhatsApp / HP Aktif <span class="text-red-400">*</span>
                        </label>
                        <input 
                            v-model="form.phone" 
                            type="tel" 
                            required 
                            placeholder="Contoh: 081234567890"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition"
                        />
                        <p v-if="form.errors.phone" class="text-[11px] text-red-400 mt-1">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Instansi / Lembaga / Organisasi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">
                            Instansi / Lembaga / Organisasi
                        </label>
                        <select 
                            v-model="selectedInstitutionType" 
                            @change="onInstitutionSelect(selectedInstitutionType)"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition mb-2"
                        >
                            <option value="">-- Pilih Instansi / Lembaga --</option>
                            <option v-for="inst in commonInstitutions" :key="inst" :value="inst">
                                {{ inst }}
                            </option>
                        </select>

                        <!-- Custom manual input if 'Lainnya' selected or custom entry -->
                        <div v-if="selectedInstitutionType === 'Lainnya (Tulis Manual)'" class="animate-fade-in">
                            <input 
                                v-model="form.custom_institution" 
                                type="text" 
                                placeholder="Ketik nama instansi / organisasi Anda..."
                                class="w-full bg-slate-950 border border-orange-500/60 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                            />
                        </div>
                    </div>

                    <!-- Jabatan / Peran -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-200 mb-1">
                                Jabatan / Posisi
                            </label>
                            <input 
                                v-model="form.position" 
                                type="text" 
                                placeholder="Contoh: Relawan / Staf / Tamu"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-200 mb-1">
                                Email (Opsional)
                            </label>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                placeholder="nama@email.com"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition"
                            />
                        </div>
                    </div>

                    <!-- Tanda Tangan Digital (Canvas) -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-200">
                                Tanda Tangan Digital (Paraf)
                            </label>
                            <button 
                                v-if="hasSignature" 
                                type="button" 
                                @click="clearSignature"
                                class="text-[11px] text-rose-400 hover:text-rose-300 font-medium underline"
                            >
                                Hapus / Ulangi
                            </button>
                        </div>

                        <div class="relative bg-white rounded-xl border border-slate-600 overflow-hidden shadow-inner touch-none">
                            <canvas 
                                ref="signatureCanvas" 
                                @mousedown="startDrawing" 
                                @mousemove="draw" 
                                @mouseup="stopDrawing" 
                                @mouseleave="stopDrawing"
                                @touchstart.passive="startDrawing" 
                                @touchmove="draw" 
                                @touchend="stopDrawing"
                                class="w-full h-32 block cursor-crosshair"
                            ></canvas>
                            <div v-if="!hasSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center text-slate-400 text-xs italic">
                                Goreskan tanda tangan / paraf Anda di sini ✍️
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">
                            Gunakan jari (di smartphone) atau mouse (di laptop/PC) untuk menandatangani.
                        </p>
                    </div>

                    <!-- Catatan Tambahan (Opsional) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-200 mb-1">
                            Keterangan / Catatan Tambahan (Opsional)
                        </label>
                        <textarea 
                            v-model="form.notes" 
                            rows="2" 
                            placeholder="Tuliskan catatan kehadiran bila ada..."
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition"
                        ></textarea>
                    </div>

                    <!-- Tombol Kirim -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 active:scale-[0.99] text-white font-bold text-sm tracking-wide transition shadow-xl shadow-orange-600/30 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Menyimpan Presensi...' : 'Konfirmasi & Kirim Kehadiran' }}</span>
                    </button>
                </form>
            </div>
        </main>

        <!-- Footer -->
        <footer class="max-w-xl mx-auto w-full text-center py-4 border-t border-white/10 mt-6 space-y-1">
            <p class="text-[11px] text-slate-400">
                Sistem E-Presensi & Arsip Terpadu © {{ new Date().getFullYear() }} <span class="text-orange-400 font-semibold">Yayasan MKT Indonesia</span>
            </p>
            <p class="text-[10px] text-slate-500">
                Akses aman untuk peserta, mitra Basarnas, BPBD, PMI, dan tamu undangan.
            </p>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.25s ease-out forwards;
}
</style>
