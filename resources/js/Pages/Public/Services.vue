<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import bloodDonorImg from '../../../images/blood_donor.jpg';
import heroRescueImg from '../../../images/hero_rescue.jpg';

const services = [
    {
        icon: '🚨',
        title: 'Tim Search & Rescue (SAR)',
        badge: 'Operasi Lapangan 24 Jam',
        desc: 'Respon cepat evakuasi korban bencana darat dan perairan dengan armada perahu karet RIB, drone pencari thermal, dan rescuer bersertifikasi resmi.',
        features: ['Evakuasi Banjir & Longsor', 'Penyisiran Korban Tenggelam', 'Peralatan Alkon & Perahu RIB', 'Siaga Panggilan Darurat 24/7']
    },
    {
        icon: '🩸',
        title: 'Jaringan Relawan Donor Darah',
        badge: 'Transfusi Siaga On-Demand',
        desc: 'Basis data digital ribuan relawan pendonor darah aktif yang siap dihubungi kapan saja saat rumah sakit atau pasien darurat membutuhkan suplai.',
        features: ['Database Golongan A, B, AB, O', 'Filter Lokasi & Rhesus Darah', 'Aksi Donor Darah Massal', 'Koordinasi Cepat Unit Transfusi PMI']
    },
    {
        icon: '🏥',
        title: 'Posko Medis & Tenaga Kesehatan',
        badge: 'Pelayanan Darurat',
        desc: 'Penyediaan dokter, perawat, dan obat-obatan esensial di lokasi terdampak musibah untuk penanganan luka korban dan trauma fisik.',
        features: ['Dokter & Perawat Siaga', 'Pemeriksaan Kesehatan Gratis', 'Pemberian Obat & Vitamin', 'Rujukan Ambulans Gawat Darurat']
    },
    {
        icon: '📦',
        title: 'Gudang Logistik & Dapur Umum',
        badge: 'Ketahanan Pangan Pengungsi',
        desc: 'Pengelolaan pasokan bantuan sembako, makanan siap saji bergizi hangat, tenda pengungsian, pakaian layak, dan perlengkapan bayi/lansia.',
        features: ['Distribusi Paket Sembako', 'Dapur Umum 1000 Porsi/Hari', 'Penyediaan Tenda & Selimut', 'Stok Popok & Makanan Bayi']
    },
    {
        icon: '🤝',
        title: 'Sinergi Komando Basarnas & BPBD',
        badge: 'Koordinasi Antar-Lembaga',
        desc: 'Kolaborasi taktis dengan Basarnas, Badan Penanggulangan Bencana Daerah (BPBD), TNI/Polri, dan komunitas rescue dalam kendali operasi satu pintu.',
        features: ['Penyelarasan Frekuensi Radio HT', 'Rapat Koordinasi Taktis SAR', 'Penyusunan Rencana Operasi Bersama', 'Pelaporan Data Korban Terpadu']
    },
    {
        icon: '📑',
        title: 'Akuntabilitas Donasi & Jurnal Finansial',
        badge: 'Transparansi Penuh',
        desc: 'Sistem pencatatan akuntansi jurnal keuangan MKT yang terotomasi, memungkinkan setiap donatur memantau langsung penyaluran dana kemanusiaan.',
        features: ['Pencatatan COA Kode Akun', 'Laporan Neraca & Buku Besar', 'Bukti Kuitansi & Dokumentasi', 'Audit Berkala Terbuka']
    }
];
</script>

<template>
    <PublicLayout 
        title="Layanan Kemanusiaan - Yayasan MKT Indonesia"
        description="6 Layanan Terpadu Yayasan MKT Indonesia: Tim SAR Rescue, Jaringan Donor Darah, Medis, Logistik, Sinergi Basarnas BPBD, dan Akuntabilitas."
    >
        <template #default="{ openCtaModal }">
            
            <!-- HEADER -->
            <section class="py-14 sm:py-20 bg-gradient-to-b from-orange-500/10 via-amber-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800 text-center space-y-4">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                        ⚡ EKOSISTEM LAYANAN TERPADU
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                        Layanan Kemanusiaan Yayasan MKT
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Solusi terintegrasi dalam manajemen darurat bencana, penyelamatan jiwa, hingga pertanggungjawaban dana filantropi.
                    </p>
                </div>
            </section>

            <!-- SERVICES GRID -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div
                            v-for="svc in services"
                            :key="svc.title"
                            class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-orange-500/50 transition-all duration-300 flex flex-col justify-between space-y-6 group"
                        >
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-4xl p-3 bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-800 group-hover:scale-110 transition-transform">
                                        {{ svc.icon }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">
                                        {{ svc.badge }}
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                    {{ svc.title }}
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    {{ svc.desc }}
                                </p>
                            </div>

                            <div class="space-y-2 pt-4 border-t border-slate-200/80 dark:border-slate-800">
                                <h4 class="text-[11px] font-black uppercase tracking-wider text-slate-400">Fitur & Kemampuan:</h4>
                                <ul class="space-y-1 text-xs text-slate-700 dark:text-slate-300">
                                    <li v-for="feat in svc.features" :key="feat" class="flex items-center space-x-2">
                                        <span class="text-emerald-500 font-bold">&check;</span>
                                        <span>{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CALL FOR ACTION BANNER -->
            <section class="py-16 bg-slate-50 dark:bg-slate-950 transition-colors">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center space-y-6">
                    <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                        Butuh Dukungan Operasi SAR atau Transfusi Darah Darurat?
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl mx-auto">
                        Hubungi Hotline 24 Jam Pusdalops MKT atau ajukan permohonan bantuan secara langsung melalui portal resmi.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                        <a href="tel:+6281234567890" class="px-8 py-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-2xl shadow-lg active:scale-95 transition text-sm">
                            📞 Hubungi Hotline: 0812-3456-7890
                        </a>
                        <button @click="openCtaModal('relawan')" class="px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-2xl shadow-lg active:scale-95 transition text-sm">
                            🚑 Daftar Relawan Rescuer / Medis
                        </button>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
