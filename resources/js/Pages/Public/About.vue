<script setup>
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import aboutMktImg from '../../../images/about_mkt.jpg';

const props = defineProps({
    mktProfile: Object,
    partners: {
        type: Array,
        default: () => []
    },
    ecosystems: {
        type: Array,
        default: () => []
    }
});

const clusters = [
    {
        title: 'Klaster Pencarian & Penyelamatan (SAR)',
        partner: 'BASARNAS, Potensi SAR & Komunitas Rescue',
        desc: 'Koordinasi pengerahan rescuer bersertifikasi, drone thermal pemantau area bencana, perahu evakuasi banjir, dan peralatan vertical rescue.',
        icon: '🚑',
        badge: 'Operasi Siaga 24/7'
    },
    {
        title: 'Klaster Penanggulangan Bencana & Komando',
        partner: 'BPBD, Tagana & Tim Tanggap Darurat',
        desc: 'Sinkronisasi data peta risiko bencana, kaji cepat dampak (DIBI/BNPB), manajemen komando lapangan darurat, serta shelter pengungsian.',
        icon: '🛡️',
        badge: 'Mitigasi & Komando'
    },
    {
        title: 'Klaster Kesehatan & Darah Darurat',
        partner: 'PMI, Rumah Sakit Rujukan & Tenaga Medis',
        desc: 'Mobilisasi relawan donor darah berdasarkan rhesus/golongan darah, posko triage medis lapangan, serta armada ambulans rujukan cepat.',
        icon: '🩸',
        badge: 'Medis & Bank Darah'
    },
    {
        title: 'Klaster Logistik & Filantropi Terpadu',
        partner: 'Lembaga Filantropi, CSR Swasta & Komunitas',
        desc: 'Distribusi dapur umum, paket sembako dan sanitasi, perlengkapan bayi, serta pelaporan akuntansi donasi publik standar PSAK.',
        icon: '📦',
        badge: 'Audit & Akuntabel'
    }
];

const collaborationMissions = [
    {
        num: '01',
        title: 'Integrasi Komunikasi & Satu Komando Informasi',
        desc: 'Menghubungkan pusat kendali operasi (PUSDALOPS) lintas lembaga sehingga respon awal bencana tidak tumpang tindih.',
        icon: '📡'
    },
    {
        num: '02',
        title: 'Mobilisasi Siaga Pendonor Darah 24 Jam',
        desc: 'Menyediakan bank data pendonor darah darurat yang terhubung dengan PMI dan jejaring rumah sakit rekanan.',
        icon: '🩸'
    },
    {
        num: '03',
        title: 'Optimalisasi Armada & Logistik Penyelamatan',
        desc: 'Memetakan sebaran peralatan rescue (perahu karet, tenda, ambulans, genset) milik seluruh mitra lembaga untuk mobilisasi tercepat.',
        icon: '🚜'
    },
    {
        num: '04',
        title: 'Transparansi Dana Publik & Pemulihan Pasca-Bencana',
        desc: 'Mewujudkan akuntabilitas penyaluran bantuan publik yang teraudit serta pendampingan psikososial penyintas hingga tahap rehabilitasi.',
        icon: '🤝'
    }
];

const collaborationValues = [
    { title: 'Gotong Royong & Sinergi', desc: 'Menyatukan keahlian dan sumber daya setiap lembaga demi keselamatan sesama tanpa ego sektoral.', icon: '🤝' },
    { title: 'Kecepatan Respons (Speed)', desc: 'Siaga 24/7 merespons panggilan darurat bencana, kecelakaan berat, maupun kebutuhan transfusi darah langka.', icon: '⚡' },
    { title: 'Integritas & Akuntabilitas', desc: 'Laporan penyaluran donasi dan distribusi logistik dicatat secara terbuka, transparan, dan dapat diaudit.', icon: '🛡️' },
    { title: 'Kemanusiaan Universal', desc: 'Melayani dan menyelamatkan seluruh lapisan masyarakat tanpa memandang suku, agama, ras, dan latar belakang.', icon: '❤️' }
];
</script>

<template>
    <PublicLayout 
        title="Profil Kolaborasi Mitra Peduli - Kolaborasi Lembaga Peduli Kemanusiaan"
        description="Profil Kolaborasi Mitra Peduli: Wadah kolaborasi dan sinergi antar-lembaga kemanusiaan, BASARNAS, BPBD, PMI, Rumah Sakit, dan relawan kebencanaan Indonesia."
    >
        <template #default="{ openCtaModal }">
            
            <!-- PAGE HEADER -->
            <section class="py-14 sm:py-20 bg-gradient-to-b from-orange-500/10 via-amber-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                        PROFIL & IDENTITAS KOLABORASI MITRA PEDULI
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                        Wadah Sinergi & Kolaborasi Lembaga Kemanusiaan
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-3xl mx-auto leading-relaxed">
                        <strong>Kolaborasi Mitra Peduli</strong> merupakan koalisi multipihak yang memadukan kekuatan lembaga pemerintah, tim pencarian dan penyelamatan (SAR), palang merah, tenaga medis, dan filantropi masyarakat demi respon kebencanaan yang cepat, terpadu, dan berdaya guna.
                    </p>
                </div>
            </section>

            <!-- COLLABORATION BACKGROUND & PHILOSOPHY -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        <div class="lg:col-span-6 space-y-6">
                            <div class="space-y-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400">
                                    LATAR BELAKANG PEMBENTUKAN KOLABORASI
                                </span>
                                <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white leading-tight">
                                    Dari Kerja Sendiri Menuju Kolaborasi Terpadu
                                </h2>
                            </div>
                            
                            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                                Bencana alam dan situasi darurat kemanusiaan tidak dapat diselesaikan oleh satu lembaga sendirian. Menyadari kebutuhan koordinasi lapangan yang cepat dan terstruktur, <strong>Kolaborasi Mitra Peduli</strong> diinisiasi sebagai meja koordinasi bersama (joint operational hub).
                            </p>

                            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                                Melalui kolaborasi ini, seluruh mitra dapat saling berbagi data riil potensi relawan, armada ambulans dan perahu evakuasi, ketersediaan kantong darah darurat, serta menyelaraskan alokasi logistik donasi agar tepat sasaran dan terhindar dari tumpang tindih.
                            </p>

                            <div class="p-4 rounded-2xl bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800/50 flex items-start gap-3">
                                <span class="text-2xl">🤝</span>
                                <div class="text-xs text-orange-900 dark:text-orange-200 leading-relaxed">
                                    <strong class="font-bold">Prinsip Kerja Kolaborasi:</strong> Setiap organisasi mitra tetap menjaga independensi kelembagaannya masing-masing, namun bergerak dalam satu irama komando terpadu ketika musibah terjadi.
                                </div>
                            </div>

                            <div class="pt-2 flex flex-wrap gap-4">
                                <button @click="openCtaModal('mitra')" class="px-6 py-3 rounded-2xl bg-orange-600 text-white font-bold text-xs sm:text-sm hover:bg-orange-700 transition shadow-lg shadow-orange-600/20">
                                    Bergabung Sebagai Lembaga Mitra
                                </button>
                                <button @click="openCtaModal('relawan')" class="px-6 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white font-bold text-xs sm:text-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                    Daftar Relawan Lintas Klaster
                                </button>
                            </div>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 group">
                                <img :src="aboutMktImg" alt="Sekretariat Kolaborasi Mitra Peduli" class="w-full h-[430px] object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent"></div>
                                <div class="absolute bottom-6 left-6 right-6 text-white space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-black bg-orange-600 text-white uppercase tracking-wider">SEKRETARIAT & PUSAT KOORDINASI</span>
                                        <span class="text-xs text-emerald-300 font-bold flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Siaga 24 Jam
                                        </span>
                                    </div>
                                    <h4 class="text-xl font-black">Sekretariat Kolaborasi Mitra Peduli</h4>
                                    <p class="text-xs text-slate-300">Pusat konsolidasi posko bersama, koordinasi logistik relawan, dan pangkalan respon cepat darurat bencana.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4 KLASTER KOLABORASI -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center space-y-3 max-w-2xl mx-auto">
                        <span class="px-3.5 py-1 rounded-full text-xs font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                            🧩 4 PILAR KLASTER KOLABORASI
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            Sinergi Lintas Sektor di Kolaborasi Mitra Peduli
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                            Setiap lembaga berkontribusi sesuai spesialisasi dan kompetensi intinya dalam skema terkoordinasi.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="cluster in clusters"
                            :key="cluster.title"
                            class="p-7 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-orange-500/40 hover:shadow-md transition-all space-y-4"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-3xl p-3 bg-orange-500/10 rounded-2xl">{{ cluster.icon }}</span>
                                <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ cluster.badge }}
                                </span>
                            </div>
                            <div class="space-y-1.5">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ cluster.title }}</h3>
                                <p class="text-xs font-bold text-orange-600 dark:text-orange-400">{{ cluster.partner }}</p>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ cluster.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- MITRA LEMBAGA ANGGOTA KOLABORASI -->
            <section v-if="partners && partners.length > 0" class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center space-y-3 max-w-2xl mx-auto">
                        <span class="px-3.5 py-1 rounded-full text-xs font-black bg-blue-500/10 text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                            🏛️ JEJARING LEMBAGA ANGGOTA
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            Mitra Kolaborator yang Telah Tergabung
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                            Bersama-sama membentuk perisai perlindungan kemanusiaan yang kokoh dan berkelanjutan.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="partner in partners"
                            :key="partner.id"
                            class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-4 hover:shadow-lg transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center text-white font-black text-lg shadow-md shrink-0">
                                    {{ partner.name ? partner.name.charAt(0) : 'M' }}
                                </div>
                                <div class="overflow-hidden">
                                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white truncate">
                                        {{ partner.name }}
                                    </h3>
                                    <span class="text-xs text-orange-600 dark:text-orange-400 font-medium">
                                        {{ partner.category || 'Mitra Kemanusiaan' }}
                                    </span>
                                </div>
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed">
                                {{ partner.description || 'Lembaga yang aktif berkontribusi dalam mitigasi bencana, penyelamatan korban, dan penyaluran bantuan kemanusiaan.' }}
                            </p>

                            <div class="pt-2 border-t border-slate-200 dark:border-slate-800/60 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1 font-medium">
                                    👥 {{ partner.volunteers_count || 0 }} Relawan
                                </span>
                                <span class="flex items-center gap-1 font-medium">
                                    🚜 {{ partner.equipments_count || 0 }} Armada
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- VISION & MISSION -->
            <section class="py-16 sm:py-20 bg-slate-50 dark:bg-slate-950 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
                    
                    <!-- Vision Box -->
                    <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 text-white text-center space-y-4 shadow-xl">
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-white/20 uppercase tracking-wider backdrop-blur-md">
                            🔭 VISI KOLABORASI MITRA PEDULI
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black max-w-4xl mx-auto leading-tight">
                            "Menjadi Wadah Kolaborasi Kemanusiaan Terdepan yang Mengintegrasikan Sinergi Lintas Lembaga Demi Terwujudnya Masyarakat yang Tanggap, Tangguh, dan Saling Menopang."
                        </h2>
                    </div>

                    <!-- Missions Grid -->
                    <div class="space-y-8">
                        <div class="text-center space-y-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                🎯 MISI BERSAMA KOLABORASI
                            </span>
                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                4 Misi Strategis Kolaborasi Mitra Peduli
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div
                                v-for="m in collaborationMissions"
                                :key="m.num"
                                class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 flex items-start space-x-4"
                            >
                                <span class="text-3xl p-3 bg-orange-500/10 rounded-2xl shrink-0">{{ m.icon }}</span>
                                <div class="space-y-1">
                                    <span class="text-xs font-mono font-extrabold text-orange-600 dark:text-orange-400">MISI {{ m.num }}</span>
                                    <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ m.title }}</h4>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ m.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- CORE VALUES -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div class="text-center space-y-2 max-w-2xl mx-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                            ⭐ NILAI-NILAI DASAR KOLABORASI
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                            Prinsip & Nilai Integritas Mitra Peduli
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div
                            v-for="val in collaborationValues"
                            :key="val.title"
                            class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center space-y-3 hover:border-orange-500/50 hover:shadow-lg transition-all"
                        >
                            <span class="text-4xl block">{{ val.icon }}</span>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ val.title }}</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ val.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA SECTION -->
            <section class="py-16 bg-gradient-to-r from-orange-600 to-amber-600 text-white">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                        Lembaga Anda Ingin Berkolaborasi Bersama Kami?
                    </h2>
                    <p class="text-sm sm:text-base text-orange-100 max-w-2xl mx-auto leading-relaxed">
                        Kami mengundang organisasi kerelawanan, instansi pemerintah, rumah sakit, dan lembaga filantropi untuk bersinergi di Kolaborasi Mitra Peduli. Mari kuatkan barisan kemanusiaan.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4 pt-2">
                        <button @click="openCtaModal('mitra')" class="px-8 py-3.5 rounded-2xl bg-white text-orange-700 font-extrabold text-sm hover:bg-orange-50 shadow-lg transition">
                            🤝 Daftarkan Lembaga Menjadi Mitra
                        </button>
                        <Link :href="route('public.contact')" class="px-8 py-3.5 rounded-2xl bg-orange-700/60 border border-white/20 text-white font-bold text-sm hover:bg-orange-700 transition">
                            Hubungi Sekretariat Kolaborasi
                        </Link>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
