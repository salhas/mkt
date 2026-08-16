<script setup>
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import pillarPreImg from '../../../images/pillar_pre.jpg';
import pillarDuringImg from '../../../images/pillar_during.jpg';
import pillarPostImg from '../../../images/pillar_post.jpg';

const props = defineProps({
    partners: Array,
});

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
            { icon: '👨‍🏫', title: 'Sosialisasi & Simulasi Sekolah/Warga', desc: 'Pelatihan simulasi gempa dan pertolongan pertama di sekolah, pondok pesantren, dan permukiman.' },
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

const defaultPartners = [
    {
        name: 'BASARNAS (Badan Nasional Pencarian & Pertolongan)',
        role: 'Instansi SAR Utama',
        category: 'Operasi SAR & Evakuasi',
        desc: 'Kolaborasi pengerahan rescuer bersertifikasi, perahu karet RIB, drone thermal, dan koordinasi rencana operasi SAR bersama.',
        icon: '⚓'
    },
    {
        name: 'BPBD RI (Badan Penanggulangan Bencana Daerah)',
        role: 'Pemerintah Daerah',
        category: 'Komando Darurat & Mitigasi',
        desc: 'Sinergi pemetaan wilayah rawan bencana, penyaluran logistik bantuan pengungsi, dan pendirian tenda darurat.',
        icon: '🏛️'
    },
    {
        name: 'PMI & Jaringan Unit Transfusi Darah (UTD)',
        role: 'Kemanusiaan Medis',
        category: 'Donor Darah & Kesehatan',
        desc: 'Mobilisasi database relawan donor darah on-demand MKT untuk mencukupi stok transfusi darurat di rumah sakit rujukan.',
        icon: '🩸'
    },
    {
        name: 'Rumah Sakit & Jaringan Medis Gawat Darurat',
        role: 'Layanan Kesehatan',
        category: 'Fasilitas Rujukan',
        desc: 'Penyediaan ambulans gawat darurat, perawatan korban luka bencana, dan posko pemeriksaan kesehatan gratis.',
        icon: '🏥'
    },
    {
        name: 'Konsorsium Lembaga Filantropi & CSR Korporasi',
        role: 'Mitra Filantropi',
        category: 'Penyalur Donasi & Program CSR',
        desc: 'Penyaluran dana CSR korporasi untuk pembangunan hunian sementara, trauma healing, dan stimulus ekonomi keluarga penyintas.',
        icon: '🤝'
    }
];

const synergyBenefits = [
    { title: 'Respons Satu Komando', desc: 'Meminimalkan tumpang tindih pengerahan tim lapangan saat situasi darurat bencana.', icon: '🎯' },
    { title: 'Efisiensi Logistik', desc: 'Penyaluran sembako dan obat tepat sasaran ke titik-titik pengungsian paling terisolir.', icon: '📦' },
    { title: 'Pertukaran Data Real-Time', desc: 'Sinkronisasi status korban, jumlah pengungsi, dan kebutuhan suplai darah medis.', icon: '📊' },
    { title: 'Akuntabilitas Donasi Bersama', desc: 'Pelaporan transparansi audit keuangan bagi mitra korporasi dan lembaga amal.', icon: '📑' }
];
</script>

<template>
    <PublicLayout 
        title="Mitra & 3 Pilar Bencana - Yayasan MKT Indonesia"
        description="Sinergi Terpadu Yayasan MKT Indonesia bersama Basarnas, BPBD, PMI, Rumah Sakit, dan Penerapan 3 Pilar Siklus Kebencanaan."
    >
        <template #default="{ openCtaModal }">
            
            <!-- HEADER -->
            <section class="py-14 sm:py-20 bg-gradient-to-b from-blue-500/10 via-orange-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800 text-center space-y-4">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 uppercase tracking-wider">
                        🤝 SINERGI KEMITRAAN & 3 PILAR BENCANA
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                        Mitra & 3 Pilar Siklus Penanggulangan Bencana
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Kolaborasi strategis multi-pihak yang menyatukan seluruh tahapan: pencegahan (pra-bencana), penyelamatan (saat darurat), hingga pemulihan (pasca-bencana).
                    </p>
                </div>
            </section>

            <!-- SECTION: 3 PILAR KEBENCANAAN INTERACTIVE EXPLORER -->
            <section class="py-14 sm:py-16 bg-white dark:bg-slate-900 transition-colors border-b border-slate-200 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    
                    <div class="text-center space-y-2 max-w-2xl mx-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                            🛡️ 3 PILAR SIKLUS KEBENCANAAN
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                            Tahap Operasi Terpadu Yayasan MKT
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                            Pilih tahap kebencanaan di bawah ini untuk melihat strategi aksi lapangan dan sinergi bersama para mitra.
                        </p>
                    </div>

                    <!-- Pilar Tabs Switcher -->
                    <div class="flex items-center justify-center space-x-2 sm:space-x-4 overflow-x-auto pb-1">
                        <button
                            v-for="p in pillars"
                            :key="p.id"
                            @click="activePillar = p.id"
                            :class="[
                                activePillar === p.id 
                                    ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/30 border-orange-600 font-extrabold' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-orange-50 border-slate-200 dark:border-slate-700 font-semibold',
                                'px-4 sm:px-6 py-2.5 sm:py-3 rounded-2xl border text-xs sm:text-sm shrink-0 transition-all'
                            ]"
                        >
                            <span>{{ p.title }}</span>
                        </button>
                    </div>

                    <!-- Selected Pillar Card Details -->
                    <div 
                        v-for="pillar in pillars.filter(item => item.id === activePillar)" 
                        :key="pillar.id"
                        class="space-y-8 animate-fadeIn"
                    >
                        <div class="bg-slate-50 dark:bg-slate-950 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xl">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                                <div class="lg:col-span-6 p-8 sm:p-10 space-y-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase text-white tracking-wider" :class="pillar.badgeColor">
                                        {{ pillar.badge }}
                                    </span>
                                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white leading-tight">
                                        {{ pillar.title }}
                                    </h3>
                                    <p class="text-xs sm:text-sm font-semibold text-orange-600 dark:text-orange-400">
                                        {{ pillar.tagline }}
                                    </p>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                        {{ pillar.desc }}
                                    </p>
                                    <div class="pt-2 flex flex-wrap gap-3">
                                        <button @click="openCtaModal('relawan')" class="px-5 py-2.5 rounded-xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 transition shadow-md">
                                            Gabung Relawan Rescuer
                                        </button>
                                        <button @click="openCtaModal('donatur')" class="px-5 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white font-bold text-xs hover:bg-slate-100 transition">
                                            Dukung Donasi Fase Ini
                                        </button>
                                    </div>
                                </div>
                                <div class="lg:col-span-6 h-[320px] lg:h-full min-h-[300px] relative bg-slate-900">
                                    <img :src="pillar.coverImg" :alt="pillar.title" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-slate-950/60 via-transparent to-transparent"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Pillar Activities Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div
                                v-for="act in pillar.activities"
                                :key="act.title"
                                class="p-5 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1.5 flex items-start space-x-3.5 hover:border-orange-500/40 transition-all"
                            >
                                <span class="text-2xl p-2.5 bg-orange-500/10 rounded-2xl shrink-0">{{ act.icon }}</span>
                                <div class="space-y-0.5">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ act.title }}</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ act.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- SECTION: MITRA LEMBAGA -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center space-y-2 max-w-2xl mx-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                            🏛️ JARINGAN MITRA RESMI
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            Instansi & Mitra Kerjasama Terpadu
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div
                            v-for="p in defaultPartners"
                            :key="p.name"
                            class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-blue-500/40 transition-all duration-300 flex flex-col justify-between space-y-6 group"
                        >
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-4xl p-3 bg-slate-50 dark:bg-slate-950 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-800 group-hover:scale-110 transition-transform">
                                        {{ p.icon }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                        {{ p.category }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ p.name }}
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    {{ p.desc }}
                                </p>
                            </div>

                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 flex items-center space-x-1">
                                    <span>🛡️ Peran: {{ p.role }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: BENEFIT SINERGI -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center space-y-2 max-w-2xl mx-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                            ✨ MANFAAT SINERGI
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                            Mengapa Kolaborasi Terpadu Penting?
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div
                            v-for="b in synergyBenefits"
                            :key="b.title"
                            class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center space-y-3 hover:border-orange-500/40 transition-all"
                        >
                            <span class="text-3xl p-3 bg-orange-500/10 rounded-2xl inline-block">{{ b.icon }}</span>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ b.title }}</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ b.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: REGISTRATION CTA FOR PARTNER / CSR -->
            <section class="py-16 bg-slate-50 dark:bg-slate-950 transition-colors">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center space-y-6">
                    <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                        Tertarik Bermitra atau Menyalurkan CSR Perusahaan?
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl mx-auto">
                        Yayasan MKT Indonesia membuka ruang kolaborasi seluas-luasnya dengan instansi pemerintah, BUMN, swasta, dan organisasi sosial.
                    </p>
                    <div class="pt-2">
                        <button @click="openCtaModal('mitra')" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-2xl shadow-xl active:scale-95 transition text-sm sm:text-base">
                            🤝 Ajukan Kerjasama / Registrasi Mitra
                        </button>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
