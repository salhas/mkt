<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    pdfUrl: {
        type: String,
        default: '/docs/syarat-dan-ketentuan-kemitraan-mkt.pdf'
    },
    downloadUrl: {
        type: String,
        default: '/syarat-ketentuan-kemitraan/download'
    },
    mktProfile: {
        type: Object,
        default: () => ({})
    }
});

const copied = ref(false);

const printDocument = () => {
    window.print();
};

const copyShareLink = () => {
    if (typeof window !== 'undefined') {
        navigator.clipboard.writeText(window.location.href);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2500);
    }
};

const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};
</script>

<template>
    <Head title="Syarat & Ketentuan Kemitraan dan Kolaborasi - Yayasan MKT Indonesia" />

    <PublicLayout>
        <div class="min-h-screen bg-slate-50/60 dark:bg-slate-950/80 py-8 sm:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- Breadcrumbs & Meta Tag -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                    <nav class="flex items-center space-x-2 text-slate-500 dark:text-slate-400">
                        <Link :href="route('home')" class="hover:text-blue-600 dark:hover:text-blue-400">Beranda</Link>
                        <span>/</span>
                        <Link :href="route('public.partners')" class="hover:text-blue-600 dark:hover:text-blue-400">Mitra & Relawan</Link>
                        <span>/</span>
                        <span class="text-blue-600 dark:text-blue-400 font-bold">Syarat & Ketentuan</span>
                    </nav>

                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            🛡️ DOKUMEN RESMI MKT INDONESIA
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            🟢 AKTIF & BERLAKU
                        </span>
                    </div>
                </div>

                <!-- Modern Hero Banner -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-6 sm:p-10 text-white shadow-2xl border border-blue-800/40">
                    <div class="absolute -right-12 -top-12 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute right-1/4 -bottom-16 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 max-w-3xl space-y-4">
                        <span class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-md text-blue-200 text-xs font-black tracking-wider uppercase border border-white/15">
                            REGULASI KEMITRAAN &bull; NO: SK-KMT/MKT-INDONESIA/2026
                        </span>

                        <h1 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                            Syarat & Ketentuan Kemitraan dan Kolaborasi MKT Indonesia
                        </h1>

                        <p class="text-sm sm:text-base text-blue-100/90 leading-relaxed font-medium">
                            Pedoman hak, kewajiban, tata kelola, dan prinsip dasar kolaborasi kemanusiaan multipihak bersama MKT Indonesia.
                        </p>

                        <!-- Action Toolbar -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a 
                                :href="downloadUrl" 
                                target="_blank"
                                class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-blue-600/30 active:scale-95 transition-all flex items-center space-x-2 border border-blue-400/30"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <span>Unduh Berkas PDF Resmi</span>
                            </a>

                            <button 
                                type="button" 
                                @click="printDocument"
                                class="px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm backdrop-blur-md border border-white/20 transition-all active:scale-95 flex items-center space-x-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                <span>Cetak Dokumen</span>
                            </button>

                            <button 
                                type="button" 
                                @click="copyShareLink"
                                class="px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm backdrop-blur-md border border-white/20 transition-all active:scale-95 flex items-center space-x-2"
                            >
                                <span>{{ copied ? '✅ Tautan Disalin!' : '🔗 Salin Tautan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- Left Sidebar: Sticky Quick Navigation -->
                    <aside class="hidden lg:block lg:col-span-4 sticky top-24 space-y-4">
                        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Daftar Isi Dokumen
                            </h3>

                            <nav class="space-y-1 text-xs font-semibold">
                                <button @click="scrollToSection('pasal-a')" class="w-full text-left px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center space-x-2">
                                    <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-[10px] font-black flex items-center justify-center shrink-0">A</span>
                                    <span>Ketentuan Umum</span>
                                </button>
                                <button @click="scrollToSection('pasal-b')" class="w-full text-left px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center space-x-2">
                                    <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-[10px] font-black flex items-center justify-center shrink-0">B</span>
                                    <span>Persyaratan Mitra</span>
                                </button>
                                <button @click="scrollToSection('pasal-c')" class="w-full text-left px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center space-x-2">
                                    <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-[10px] font-black flex items-center justify-center shrink-0">C</span>
                                    <span>Prinsip Kemitraan (7 Pilar)</span>
                                </button>
                                <button @click="scrollToSection('pasal-d')" class="w-full text-left px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center space-x-2">
                                    <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-[10px] font-black flex items-center justify-center shrink-0">D</span>
                                    <span>Komitmen Mitra</span>
                                </button>
                                <button @click="scrollToSection('pasal-e')" class="w-full text-left px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center space-x-2">
                                    <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-[10px] font-black flex items-center justify-center shrink-0">E</span>
                                    <span>Penggunaan Data & Informasi</span>
                                </button>
                                <button @click="scrollToSection('pasal-f')" class="w-full text-left px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center space-x-2">
                                    <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-[10px] font-black flex items-center justify-center shrink-0">F</span>
                                    <span>Verifikasi & Persetujuan</span>
                                </button>
                                <button @click="scrollToSection('pasal-g')" class="w-full text-left px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all flex items-center space-x-2">
                                    <span class="w-5 h-5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 text-[10px] font-black flex items-center justify-center shrink-0">G</span>
                                    <span>Penolakan & Pengakhiran</span>
                                </button>
                            </nav>
                        </div>

                        <!-- Card Download PDF Box -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-900 dark:to-blue-950/40 rounded-3xl p-5 border border-blue-200/70 dark:border-blue-900/50 space-y-3">
                            <span class="text-3xl">📄</span>
                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Berkas PDF Asli</h4>
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                Dokumen PDF dilengkapi stempel verifikasi dan kop resmi Yayasan MKT Indonesia (format cetak A4).
                            </p>
                            <a 
                                :href="downloadUrl" 
                                target="_blank"
                                class="inline-flex items-center space-x-2 w-full justify-center px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition-all"
                            >
                                <span>Unduh PDF Resmi</span>
                            </a>
                        </div>
                    </aside>

                    <!-- Right Column: Modern Aesthetic Document Card -->
                    <main class="lg:col-span-8 space-y-6">
                        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-200/80 dark:border-slate-800 shadow-xl space-y-8">
                            
                            <!-- Header Kop Surat Yayasan MKT Berbasis Database -->
                            <div class="pb-6 border-b-2 border-blue-900 dark:border-blue-600 relative">
                                <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-4">
                                    <img :src="mktProfile?.logo || '/images/mkt_logo.png'" alt="Logo Yayasan MKT" class="w-20 h-20 object-contain shrink-0" />
                                    <div class="space-y-1">
                                        <h2 class="text-base sm:text-lg font-black text-slate-950 dark:text-white uppercase tracking-wide">
                                            {{ mktProfile?.name || 'Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu)' }}
                                        </h2>
                                        <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                            Lembaga Kemanusiaan, Kebencanaan & Filantropi Terpadu
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
                                            Sekretariat: {{ mktProfile?.address || 'Perumahan Insignia Oasis The Premiere Blok B1-11 No 7 Kec. Moncongloe Kab. Maros' }} &bull; Telp: {{ mktProfile?.phone || '+62 812-3456-7890' }} &bull; Email: {{ mktProfile?.email || 'admin@mktindonesia.org' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="absolute -bottom-1.5 left-0 right-0 h-0.5 bg-amber-500"></div>
                            </div>

                            <!-- Title of Terms -->
                            <div class="text-center py-2 space-y-1 border-b border-slate-100 dark:border-slate-800 pb-4">
                                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white uppercase tracking-wide">
                                    SYARAT & KETENTUAN KEMITRAAN DAN KOLABORASI
                                </h2>
                                <p class="text-xs text-blue-600 dark:text-blue-400 font-bold">
                                    NOMOR REGULASI: SK-KMT/MKT-INDONESIA/2026/REV-01
                                </p>
                            </div>

                            <!-- SECTION A: KETENTUAN UMUM -->
                            <section id="pasal-a" class="space-y-3 pt-2">
                                <div class="flex items-center space-x-3">
                                    <span class="w-7 h-7 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">A</span>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Ketentuan Umum</h3>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-3 leading-relaxed pl-10">
                                    <p>
                                        MKT Indonesia membuka kesempatan kepada organisasi, lembaga, komunitas, perguruan tinggi, dunia usaha (CSR), lembaga filantropi, kelompok relawan, serta pihak lain yang memiliki kepedulian dan/atau kapasitas di bidang kemanusiaan, kebencanaan, sosial, pendidikan, teknologi, kesehatan, dan bidang terkait untuk membangun kemitraan dan kolaborasi.
                                    </p>
                                    <p>
                                        Kemitraan dengan MKT Indonesia tidak mengubah identitas, struktur, kewenangan, maupun independensi organisasi mitra. Setiap pihak tetap menjalankan fungsi dan kewenangannya sesuai dengan ketentuan internal serta peraturan yang berlaku.
                                    </p>
                                    <div class="p-3.5 rounded-2xl bg-blue-50/80 dark:bg-blue-950/30 border-l-4 border-blue-600 text-blue-900 dark:text-blue-300 text-xs italic font-semibold">
                                        MKT Indonesia berperan sebagai penghubung dan fasilitator kolaborasi, dengan prinsip:
                                        <div class="not-italic font-black text-sm text-blue-950 dark:text-white mt-1">
                                            "Menghubungkan potensi, memperkuat kolaborasi, dan menghasilkan dampak kemanusiaan yang lebih besar."
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- SECTION B: PERSYARATAN MITRA -->
                            <section id="pasal-b" class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center space-x-3">
                                    <span class="w-7 h-7 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">B</span>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Persyaratan Mitra</h3>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-2.5 leading-relaxed pl-10">
                                    <p class="font-bold text-slate-800 dark:text-slate-200">
                                        Lembaga/organisasi yang mengajukan registrasi kemitraan wajib:
                                    </p>
                                    <ul class="space-y-2">
                                        <li class="flex items-start space-x-2.5">
                                            <span class="text-emerald-500 font-bold shrink-0">✓</span>
                                            <span><strong>Identitas Jelas:</strong> Memiliki identitas dan keberadaan organisasi yang jelas, baik sebagai lembaga berbadan hukum maupun komunitas/kelompok yang dapat dipertanggungjawabkan keberadaannya.</span>
                                        </li>
                                        <li class="flex items-start space-x-2.5">
                                            <span class="text-emerald-500 font-bold shrink-0">✓</span>
                                            <span><strong>Kapasitas Relevan:</strong> Memiliki kegiatan, kapasitas, pengalaman, jaringan, sumber daya, atau kompetensi yang relevan dengan bidang kemanusiaan, kebencanaan, sosial, pendidikan, kesehatan, teknologi, lingkungan, atau bidang lain yang dapat mendukung misi kolaborasi MKT.</span>
                                        </li>
                                        <li class="flex items-start space-x-2.5">
                                            <span class="text-emerald-500 font-bold shrink-0">✓</span>
                                            <span><strong>Penunjukan Narahubung:</strong> Menunjuk narahubung / Person in Charge (PIC) resmi yang dapat dihubungi untuk kepentingan komunikasi dan koordinasi.</span>
                                        </li>
                                        <li class="flex items-start space-x-2.5">
                                            <span class="text-emerald-500 font-bold shrink-0">✓</span>
                                            <span><strong>Keabsahan Data:</strong> Memberikan informasi yang benar, lengkap, dan dapat dipertanggungjawabkan pada saat proses registrasi.</span>
                                        </li>
                                        <li class="flex items-start space-x-2.5">
                                            <span class="text-emerald-500 font-bold shrink-0">✓</span>
                                            <span><strong>Kesediaan Verifikasi:</strong> Bersedia melakukan verifikasi data atau klarifikasi tambahan apabila diperlukan oleh MKT Indonesia.</span>
                                        </li>
                                        <li class="flex items-start space-x-2.5">
                                            <span class="text-emerald-500 font-bold shrink-0">✓</span>
                                            <span><strong>Integritas Profesional:</strong> Bersedia menjalankan kemitraan berdasarkan prinsip profesionalitas, transparansi, akuntabilitas, kesetaraan, inklusivitas, dan saling menghormati.</span>
                                        </li>
                                        <li class="flex items-start space-x-2.5">
                                            <span class="text-emerald-500 font-bold shrink-0">✓</span>
                                            <span><strong>Kepatuhan Hukum:</strong> Tidak menggunakan kemitraan dengan MKT Indonesia untuk kegiatan yang bertentangan dengan hukum, kepentingan kemanusiaan, atau prinsip-prinsip dasar organisasi.</span>
                                        </li>
                                    </ul>
                                </div>
                            </section>

                            <!-- SECTION C: PRINSIP KEMITRAAN (7 PILAR) -->
                            <section id="pasal-c" class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center space-x-3">
                                    <span class="w-7 h-7 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">C</span>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Prinsip Kemitraan (7 Pilar)</h3>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-3 leading-relaxed pl-10">
                                    <p>
                                        Setiap kemitraan dan kolaborasi MKT Indonesia dilaksanakan secara konsisten berdasarkan 7 (tujuh) prinsip dasar:
                                    </p>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                                            <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-black">
                                                <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-[10px]">1</span>
                                                <span>Kesetaraan</span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                                Setiap mitra memiliki posisi saling menghormati dan tidak terdapat hubungan subordinasi kecuali disepakati khusus dalam suatu kegiatan.
                                            </p>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                                            <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-black">
                                                <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-[10px]">2</span>
                                                <span>Independensi</span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                                Setiap organisasi tetap mempertahankan identitas, tata kelola, visi misi, dan kewenangannya masing-masing.
                                            </p>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                                            <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-black">
                                                <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-[10px]">3</span>
                                                <span>Transparansi</span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                                Informasi program, sumber daya, kontribusi, dan pelaksanaan kegiatan disampaikan secara terbuka sesuai kebutuhan dan kesepakatan.
                                            </p>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                                            <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-black">
                                                <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-[10px]">4</span>
                                                <span>Akuntabilitas</span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                                Setiap pihak bertanggung jawab atas peran, amanah, sumber daya, dan kegiatan yang menjadi tanggung jawabnya.
                                            </p>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                                            <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-black">
                                                <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-[10px]">5</span>
                                                <span>Non-diskriminasi & Inklusivitas</span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                                Kolaborasi dilakukan dengan menghormati keberagaman serta tidak membedakan latar belakang individu maupun organisasi.
                                            </p>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                                            <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 font-black">
                                                <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-[10px]">6</span>
                                                <span>Kepentingan Kemanusiaan</span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                                Kemitraan diarahkan untuk memberikan manfaat nyata bagi masyarakat dan memperkuat kapasitas kemanusiaan.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/70 dark:border-amber-900/40 text-amber-900 dark:text-amber-300">
                                        <div class="font-bold flex items-center space-x-2 mb-0.5">
                                            <span class="w-5 h-5 rounded-full bg-amber-200 dark:bg-amber-800 text-amber-950 dark:text-white flex items-center justify-center text-[10px]">7</span>
                                            <span>Tidak Mengambil Alih Kewenangan</span>
                                        </div>
                                        <p class="text-[11px] leading-relaxed">
                                            MKT Indonesia maupun mitra tidak diperkenankan menggunakan mekanisme kolaborasi untuk mengambil alih kewenangan resmi pihak lain.
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <!-- SECTION D: KOMITMEN MITRA -->
                            <section id="pasal-d" class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center space-x-3">
                                    <span class="w-7 h-7 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">D</span>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Komitmen Mitra</h3>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-2.5 leading-relaxed pl-10">
                                    <p class="font-bold text-slate-800 dark:text-slate-200">
                                        Dengan melakukan registrasi, lembaga/organisasi menyatakan bersedia:
                                    </p>
                                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                        <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex items-start space-x-2">
                                            <span class="text-blue-600 font-black">•</span>
                                            <span>Menjaga nama baik dan reputasi kemitraan;</span>
                                        </li>
                                        <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex items-start space-x-2">
                                            <span class="text-blue-600 font-black">•</span>
                                            <span>Menghormati aturan dan kebijakan masing-masing pihak;</span>
                                        </li>
                                        <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex items-start space-x-2">
                                            <span class="text-blue-600 font-black">•</span>
                                            <span>Memberikan informasi yang benar dan tidak menyesatkan;</span>
                                        </li>
                                        <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex items-start space-x-2">
                                            <span class="text-blue-600 font-black">•</span>
                                            <span>Melaksanakan komitmen yang telah disepakati bersama;</span>
                                        </li>
                                        <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex items-start space-x-2">
                                            <span class="text-blue-600 font-black">•</span>
                                            <span>Menjaga kerahasiaan informasi yang dinyatakan bersifat terbatas;</span>
                                        </li>
                                        <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex items-start space-x-2">
                                            <span class="text-blue-600 font-black">•</span>
                                            <span>Tidak menyalahgunakan nama, logo, data, jaringan, maupun fasilitas MKT Indonesia;</span>
                                        </li>
                                        <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex items-start space-x-2">
                                            <span class="text-blue-600 font-black">•</span>
                                            <span>Tidak mengatasnamakan MKT Indonesia tanpa persetujuan resmi;</span>
                                        </li>
                                        <li class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex items-start space-x-2">
                                            <span class="text-blue-600 font-black">•</span>
                                            <span>Melaporkan atau mengkomunikasikan hal-hal penting yang memengaruhi pelaksanaan kolaborasi;</span>
                                        </li>
                                    </ul>
                                </div>
                            </section>

                            <!-- SECTION E: PENGGUNAAN DATA DAN INFORMASI -->
                            <section id="pasal-e" class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center space-x-3">
                                    <span class="w-7 h-7 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">E</span>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Penggunaan Data dan Informasi</h3>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-3 leading-relaxed pl-10">
                                    <p>
                                        Data yang diberikan melalui formulir registrasi kemitraan digunakan untuk:
                                    </p>
                                    <ul class="list-disc pl-5 space-y-1 text-[11px]">
                                        <li>Proses identifikasi dan verifikasi calon mitra;</li>
                                        <li>Pemetaan potensi, kompetensi, sumber daya armada/personel, dan jejaring kolaborasi;</li>
                                        <li>Komunikasi cepat dan koordinasi taktis kemitraan;</li>
                                        <li>Penyusunan program kolaborasi serta dokumentasi ekosistem kemanusiaan MKT Indonesia.</li>
                                    </ul>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        MKT Indonesia akan menggunakan dan mengelola data sesuai dengan tujuan pengumpulan serta ketentuan peraturan perundang-undangan (UU Perlindungan Data Pribadi). Persetujuan khusus akan dimintakan apabila data akan digunakan untuk kepentingan publikasi atau promosi terbuka di luar registrasi.
                                    </p>
                                </div>
                            </section>

                            <!-- SECTION F: VERIFIKASI & PERSETUJUAN KEMITRAAN -->
                            <section id="pasal-f" class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center space-x-3">
                                    <span class="w-7 h-7 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">F</span>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Verifikasi dan Persetujuan Kemitraan</h3>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-3 leading-relaxed pl-10">
                                    <p>
                                        Pengisian formulir registrasi tidak secara otomatis berarti bahwa lembaga/organisasi telah menjadi mitra resmi MKT Indonesia. Setiap registrasi akan melalui proses terpadu:
                                    </p>

                                    <!-- Visual Pipeline Workflow -->
                                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 overflow-x-auto">
                                        <div class="flex items-center justify-between min-w-[550px] text-center">
                                            <div class="px-3 py-2 rounded-xl bg-blue-600 text-white font-bold text-[11px] shadow-sm">
                                                1. Registrasi
                                            </div>
                                            <span class="text-slate-400 font-bold">&rarr;</span>
                                            <div class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold text-[11px] border">
                                                2. Verifikasi Data
                                            </div>
                                            <span class="text-slate-400 font-bold">&rarr;</span>
                                            <div class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold text-[11px] border">
                                                3. Klarifikasi PIC
                                            </div>
                                            <span class="text-slate-400 font-bold">&rarr;</span>
                                            <div class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold text-[11px] border">
                                                4. Uji Kesesuaian
                                            </div>
                                            <span class="text-slate-400 font-bold">&rarr;</span>
                                            <div class="px-3 py-2 rounded-xl bg-emerald-600 text-white font-bold text-[11px] shadow-sm">
                                                5. Persetujuan Resmi
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-[11px]">
                                        Kemitraan dapat diwujudkan melalui: kolaborasi program, pertukaran pengetahuan, pelatihan kapasitas, dukungan logistik/armada, pengembangan data/teknologi, penelitian, hingga kegiatan sosial kebencanaan. Untuk kerja sama strategis, para pihak dapat menuangkannya dalam <strong>MoU, PKS, MoA, atau dokumen kesepakatan bersama</strong>.
                                    </p>
                                </div>
                            </section>

                            <!-- SECTION G: PENOLAKAN ATAU PENGAKHIRAN KEMITRAAN -->
                            <section id="pasal-g" class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center space-x-3">
                                    <span class="w-7 h-7 rounded-xl bg-rose-600 text-white font-black text-xs flex items-center justify-center shrink-0">G</span>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Penolakan atau Pengakhiran Kemitraan</h3>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-3 leading-relaxed pl-10">
                                    <p>
                                        MKT Indonesia berhak menolak, menunda, membatasi, atau mengakhiri proses kemitraan apabila ditemukan:
                                    </p>
                                    <ul class="list-disc pl-5 space-y-1 text-[11px]">
                                        <li>Informasi yang tidak benar atau menyesatkan;</li>
                                        <li>Kegiatan organisasi yang bertentangan dengan hukum atau prinsip kemanusiaan;</li>
                                        <li>Penyalahgunaan nama atau identitas MKT Indonesia;</li>
                                        <li>Pelanggaran terhadap kesepakatan kemitraan;</li>
                                        <li>Tindakan yang dapat merugikan masyarakat, mitra, atau reputasi MKT Indonesia;</li>
                                        <li>Konflik kepentingan yang tidak dapat dikelola secara wajar.</li>
                                    </ul>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Pengakhiran kemitraan tidak menghapus kewajiban para pihak yang masih harus diselesaikan berdasarkan kesepakatan sebelumnya.
                                    </p>
                                </div>
                            </section>

                            <!-- Signature & Digital Stamp Block -->
                            <div class="pt-8 border-t-2 border-slate-200 dark:border-slate-800 flex justify-end">
                                <div class="w-72 p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 text-center space-y-2">
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Ditetapkan di: Makassar, Indonesia
                                    </div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white uppercase">
                                        {{ mktProfile?.name ? 'PENGURUS ' + mktProfile.name : 'PENGURUS YAYASAN MKT INDONESIA' }}
                                    </div>
                                    <div class="py-2">
                                        <span class="inline-block px-3 py-1 rounded-full border border-dashed border-blue-600 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-wider bg-blue-50 dark:bg-blue-950/40">
                                            ✓ Tervalidasi Resmi Sistem
                                        </span>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                        Divisi Kerjasama & Kemitraan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Registration CTA Card -->
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="space-y-1 text-center sm:text-left">
                                <h3 class="text-lg sm:text-xl font-black">Siap Membangun Sinergi Kemanusiaan?</h3>
                                <p class="text-xs sm:text-sm text-blue-100">
                                    Daftarkan lembaga, instansi, atau komunitas Anda ke dalam portal resmi kemitraan MKT.
                                </p>
                            </div>
                            <Link 
                                :href="route('home')"
                                class="px-6 py-3.5 rounded-2xl bg-white text-blue-700 hover:bg-blue-50 font-black text-xs sm:text-sm shadow-md active:scale-95 transition-all shrink-0"
                            >
                                Buka Formulir Registrasi Mitra
                            </Link>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
