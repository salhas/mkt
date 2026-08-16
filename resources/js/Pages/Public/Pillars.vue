<script setup>
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import pillarPreImg from '../../../images/pillar_pre.jpg';
import pillarDuringImg from '../../../images/pillar_during.jpg';
import pillarPostImg from '../../../images/pillar_post.jpg';

const activePillar = ref('during');

const pillars = [
    {
        id: 'pre',
        title: '1. Pra-Bencana (Mitigasi & Kesiapsiagaan)',
        tagline: 'Mencegah Risiko, Memetakan Kerentanan, Melatih Kesiapan Warga',
        badge: 'Tahap Pencegahan (Mitigasi)',
        badgeColor: 'bg-blue-600',
        coverImg: pillarPreImg,
        desc: 'Fase pra-bencana berfokus pada pembangunan ketahanan masyarakat sebelum musibah terjadi melalui edukasi simulasi evakuasi, penyusunan database relawan donor darah, pemetaan daerah rawan bencana (Desa Tangguh Bencana), serta sertifikasi keahlian teknis personel Rescue SAR.',
        activities: [
            { icon: '🗺️', title: 'Pemetaan GIS Rawan Bencana', desc: 'Identifikasi titik banjir, lereng longsor, dan patahan gempa menggunakan koordinat geospasial.' },
            { icon: '👨‍🏫', title: 'Sosialisasi & Edukasi Kesiapsiagaan Warga', desc: 'Pelatihan simulasi gempa dan pertolongan pertama di sekolah, pondok pesantren, dan permukiman.' },
            { icon: '🩸', title: 'Pendataan Bank Darah Mandiri', desc: 'Perekrutan relawan pendonor darah on-call per kecamatan untuk kesiapan musibah massal.' },
            { icon: '📦', title: 'Logistik Penyangga Darurat', desc: 'Penyimpanan stok tenda darurat, perahu karet RIB, pelampung, genset, dan paket medis di posko.' }
        ]
    },
    {
        id: 'during',
        title: '2. Saat Bencana (Tanggap Darurat & Penyelamatan)',
        tagline: 'Respon Cepat 24 Jam, Evakuasi Korban, Bantuan Medis & Dapur Lapangan',
        badge: 'Tahap Penyelamatan (Emergency)',
        badgeColor: 'bg-rose-600',
        coverImg: pillarDuringImg,
        desc: 'Fase tanggap darurat diaktifkan seketika musibah terjadi. Melalui Pusat Komando Pusdalops MKT, tim Search and Rescue (SAR) diterjunkan bersama Basarnas dan BPBD untuk evakuasi korban, pendirian posko kesehatan darurat, dan penyaluran makanan siap saji dari Dapur Umum MKT.',
        activities: [
            { icon: '🚨', title: 'Aktivasi Tim SAR & Evakuasi Cepat', desc: 'Penyisiran lokasi bencana menggunakan perahu karet, perahu motor, dan drone pencari korban.' },
            { icon: '🏥', title: 'Posko Kesehatan & Medis Lapangan', desc: 'Pelayanan dokter dan perawat darurat untuk penanganan luka, trauma, dan rujukan ke rumah sakit.' },
            { icon: '🍲', title: 'Dapur Umum & Distribusi Makanan Siap Saji', desc: 'Penyediaan ribuan porsi makanan hangat higienis untuk para pengungsi dan relawan bertugas.' },
            { icon: '📻', title: 'Komunikasi Radio Darurat & Koordinasi', desc: 'Sinergi frekuensi HT radio kebencanaan bersama instansi komando pemerintah (Basarnas/BPBD).' }
        ]
    },
    {
        id: 'post',
        title: '3. Pasca-Bencana (Pemulihan & Rekonstruksi)',
        tagline: 'Rehabilitasi Sarana, Trauma Healing, Stimulus Ekonomi Penyintas',
        badge: 'Tahap Pemulihan (Recovery)',
        badgeColor: 'bg-emerald-600',
        coverImg: pillarPostImg,
        desc: 'Fase pasca-bencana memastikan para penyintas dapat bangkit kembali dengan mandiri. MKT menyalurkan bantuan stimulus pembangunan hunian sementara (huntara), trauma healing untuk anak-anak dan lansia, pembersihan fasilitas ibadah/sekolah, serta audit laporan penyaluran donasi publik.',
        activities: [
            { icon: '🧘', title: 'Dukungan Psikososial (Trauma Healing)', desc: 'Pendampingan psikologis untuk anak-anak dan keluarga penyintas pasca kehilangan kerabat/tempat tinggal.' },
            { icon: '🏫', title: 'Rehabilitasi Fasilitas Umum & MCK', desc: 'Pembersihan material lumpur sisa banjir dan pembangunan sarana air bersih serta sanitasi darurat.' },
            { icon: '🔨', title: 'Penyaluran Stimulus Material Rekonstruksi', desc: 'Bantuan semen, atap seng, dan perkakas pertukangan untuk perbaikan hunian keluarga rentan.' },
            { icon: '📊', title: 'Laporan Keuangan & Akuntabilitas Publik', desc: 'Penerbitan jurnal dan neraca donasi resmi yang dapat diakses publik dan donatur filantropi.' }
        ]
    }
];
</script>

<template>
    <PublicLayout 
        title="3 Pilar Siklus Kebencanaan - Yayasan MKT Indonesia"
        description="Pelajari 3 Tahap Penanggulangan Risiko Bencana Terpadu: Pra-Bencana, Tanggap Darurat Bencana, dan Pasca-Bencana."
    >
        <template #default="{ openCtaModal }">
            
            <!-- HEADER -->
            <section class="py-14 sm:py-20 bg-gradient-to-b from-orange-500/10 via-amber-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800 text-center space-y-4">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                        🛡️ SIKLUS MANAJEMEN RISIKO BENCANA
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                        3 Pilar Penanggulangan Bencana Terpadu
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Pendekatan holistik Yayasan MKT Indonesia dalam mengawal seluruh siklus kebencanaan dari pencegahan hingga pemulihan mandiri.
                    </p>
                </div>
            </section>

            <!-- PILLAR SWITCHER TABS -->
            <section class="py-8 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-20 z-30 shadow-xs">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-center space-x-2 sm:space-x-4 overflow-x-auto pb-1">
                        <button
                            v-for="p in pillars"
                            :key="p.id"
                            @click="activePillar = p.id"
                            :class="[
                                activePillar === p.id 
                                    ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/30 border-orange-600' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-orange-50 border-slate-200 dark:border-slate-700',
                                'px-4 sm:px-6 py-2.5 sm:py-3 rounded-2xl border font-black text-xs sm:text-sm shrink-0 transition-all'
                            ]"
                        >
                            <span>{{ p.title }}</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- PILLAR DETAILS -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
                    <div 
                        v-for="pillar in pillars.filter(item => item.id === activePillar)" 
                        :key="pillar.id"
                        class="space-y-12 animate-fadeIn"
                    >
                        <!-- Top Banner Card -->
                        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xl">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                                <div class="lg:col-span-6 p-8 sm:p-12 space-y-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase text-white tracking-wider" :class="pillar.badgeColor">
                                        {{ pillar.badge }}
                                    </span>
                                    <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white leading-tight">
                                        {{ pillar.title }}
                                    </h2>
                                    <p class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                                        {{ pillar.tagline }}
                                    </p>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                                        {{ pillar.desc }}
                                    </p>
                                    <div class="pt-2 flex flex-wrap gap-3">
                                        <button @click="openCtaModal('relawan')" class="px-6 py-3 rounded-2xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 transition">
                                            Gabung Aksi di Fase Ini
                                        </button>
                                        <button @click="openCtaModal('donatur')" class="px-6 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white font-bold text-xs hover:bg-slate-200 transition">
                                            Dukung Donasi Program
                                        </button>
                                    </div>
                                </div>
                                <div class="lg:col-span-6 h-[380px] lg:h-full min-h-[340px] relative bg-slate-900">
                                    <img :src="pillar.coverImg" :alt="pillar.title" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-slate-950/60 via-transparent to-transparent"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Activities Grid -->
                        <div class="space-y-6">
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white text-center">
                                Komponen Utama & Aksi Lapangan: {{ pillar.title }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div
                                    v-for="act in pillar.activities"
                                    :key="act.title"
                                    class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2 flex items-start space-x-4 hover:border-orange-500/40 transition-all"
                                >
                                    <span class="text-3xl p-3 bg-orange-500/10 rounded-2xl shrink-0">{{ act.icon }}</span>
                                    <div class="space-y-1">
                                        <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ act.title }}</h4>
                                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ act.desc }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
