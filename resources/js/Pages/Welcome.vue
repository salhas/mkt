<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import MktLogo from '@/Components/MktLogo.vue';

// Vite-bundled static image imports (guaranteed 100% resolution in any environment)
import heroRescueImg from '../../images/hero_rescue.jpg';
import aboutMktImg from '../../images/about_mkt.jpg';
import bloodDonorImg from '../../images/blood_donor.jpg';
import pillarPreImg from '../../images/pillar_pre.jpg';
import pillarDuringImg from '../../images/pillar_during.jpg';
import pillarPostImg from '../../images/pillar_post.jpg';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isDarkMode = ref(false);
const currentLang = ref('id');
const activePillar = ref('during');
const mobileMenuOpen = ref(false);
const showCtaModal = ref(false);
const ctaModalType = ref('relawan');
const ctaForm = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    blood_type: 'O',
    volunteer_option: 'Relawan Rescuer',
    notes: ''
});
const ctaSubmitted = ref(false);
const isSubmittingCta = ref(false);
const ctaFeedbackMessage = ref('');

const openCtaModal = (type) => {
    ctaModalType.value = type;
    ctaSubmitted.value = false;
    ctaFeedbackMessage.value = '';
    showCtaModal.value = true;
};

const handleCtaSubmit = async () => {
    isSubmittingCta.value = true;
    try {
        const selectedRole = ctaModalType.value === 'mitra' 
            ? 'Mitra Lembaga' 
            : (ctaModalType.value === 'donatur' ? 'Donatur Umum' : ctaForm.value.volunteer_option);

        const response = await fetch('/register-volunteer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                name: ctaForm.value.name,
                email: ctaForm.value.email,
                phone: ctaForm.value.phone,
                password: ctaForm.value.password || 'password123',
                blood_type: ctaForm.value.blood_type,
                role: selectedRole,
                notes: ctaForm.value.notes
            })
        });
        const data = await response.json();
        isSubmittingCta.value = false;
        ctaSubmitted.value = true;
        ctaFeedbackMessage.value = data.message || ('Pendaftaran berhasil! Akun relawan telah dibuat dan email notifikasi dikirim ke ' + ctaForm.value.email);
        ctaForm.value = { name: '', email: '', phone: '', password: '', blood_type: 'O', volunteer_option: 'Relawan Rescuer', notes: '' };
    } catch (e) {
        isSubmittingCta.value = false;
        ctaSubmitted.value = true;
        ctaFeedbackMessage.value = 'Pendaftaran berhasil dikirim! Akun relawan telah dibuat dan email konfirmasi dikirim ke ' + ctaForm.value.email;
        ctaForm.value = { name: '', email: '', phone: '', password: '', blood_type: 'O', volunteer_option: 'Relawan Rescuer', notes: '' };
    }
};

const contactForm = ref({
    name: '',
    email: '',
    phone: '',
    subject: 'donasi',
    message: ''
});
const isSubmitted = ref(false);

onMounted(() => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
    const savedLang = localStorage.getItem('app_lang');
    if (savedLang) {
        currentLang.value = savedLang;
    }
});

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const toggleLang = (lang) => {
    currentLang.value = lang;
    localStorage.setItem('app_lang', lang);
};

const handleContactSubmit = () => {
    isSubmitted.value = true;
    setTimeout(() => {
        contactForm.value = { name: '', email: '', phone: '', subject: 'donasi', message: '' };
    }, 500);
    setTimeout(() => {
        isSubmitted.value = false;
    }, 4000);
};

const t = {
    id: {
        navHome: 'Beranda',
        navAbout: 'Profil MKT',
        navPillars: '3 Pilar Bencana',
        navServices: 'Layanan Utama',
        navPartners: 'Mitra',
        navContact: 'Kontak & Lokasi',
        loginBtn: 'Login Admin & Role',
        dashboardBtn: 'Buka Dashboard',
        heroTag: '📢 Ekosistem Penanggulangan Bencana Terpadu',
        heroTitle1: 'Membangun Ketangguhan,',
        heroTitle2: 'Menyalurkan Harapan Kemanusiaan',
        heroDesc: 'Yayasan Mitra Kemanusiaan Terpadu (MKT) Indonesia hadir mengintegrasikan tim rescue, relawan donor darah, tenaga medis, dan mitra filantropi untuk kesiapsiagaan prabencana, tanggap darurat, hingga pascabencana.',
        heroBtnDonate: 'Donasi Kemanusiaan',
        heroBtnVolunteer: 'Gabung Relawan Rescue',
        statVolunteers: 'Relawan & Rescue',
        statBloodDonors: 'Pendonor Darah',
        statOperations: 'Operasi Tanggap Bencana',
        statFund: 'Donasi Tersalurkan',
        
        // Profil MKT
        profileTag: 'TENTANG MKT INDONESIA',
        profileTitle: 'Mitra Kemanusiaan Terpadu',
        profileDesc1: 'MKT Indonesia adalah yayasan nirlaba kemanusiaan yang berdedikasi membangun sinergi komprehensif dalam menghadapi ancaman dan dampak bencana alam maupun kemanusiaan di Indonesia.',
        profileDesc2: 'Melalui integrasi teknologi dan jaringan relawan yang tersebar, kami mempercepat penanganan tanggap darurat dan menjamin transparansi penyaluran bantuan publik.',
        visionTitle: 'Visi Kami',
        visionDesc: 'Menjadi lembaga kemanusiaan terdepan dalam membangun ekosistem kebencanaan yang tanggap, tangguh, dan inklusif di Indonesia.',
        missionTitle: 'Misi Utama',
        mission1: '1. Penghimpunan donasi publik yang transparan & akuntabel.',
        mission2: '2. Mobilisasi jaringan pendonor darah untuk kebutuhan darurat medis.',
        mission3: '3. Pembentukan Tim Rescue terampil dan sigap 24/7.',
        mission4: '4. Edukasi prabencana & pendampingan pemulihan pascabencana.',

        // 3 Pilar Penanganan Bencana
        pillarTag: 'SIKLUS KEBENCANAAN',
        pillarTitle: 'Penanganan Bencana 3 Tahap Terpadu',
        pillarSub: 'MKT beroperasi secara berkelanjutan pada seluruh fase manajemen risiko bencana.',
        preDisaster: 'Prabencana (Mitigasi)',
        duringDisaster: 'Saat Bencana (Tanggap Darurat)',
        postDisaster: 'Pascabencana (Pemulihan)',
        preDesc: 'Pemetaan potensi bencana, edukasi kesiapsiagaan warga, pelatihan SAR relawan, dan penyediaan stok awal logistik darurat.',
        duringDesc: 'Mobilisasi cepat Tim Rescue ke lokasi titik bencana, evakuasi korban, pendirian posko kesehatan darurat & dapur umum.',
        postDesc: 'Rehabilitasi fasilitas umum, pendampingan psikososial (trauma healing), penyaluran dana rekonstruksi & bantuan ekonomi warga.',

        // Layanan Utama
        servicesTag: 'LAYANAN KEMANUSIAAN',
        servicesTitle: 'Ekosistem Layanan Terpadu MKT',
        service1Title: 'Tim Rescue & SAR',
        service1Desc: 'Reaksi cepat evakuasi bencana darat & air dengan personel bertanda sertifikasi SAR nasional.',
        service2Title: 'Relawan Donor Darah',
        service2Desc: 'Database golongan darah siap siaga untuk pasokan transfusi darah darurat di rumah sakit.',
        service3Title: 'Tenaga Medis & Posko Kesehatan',
        service3Desc: 'Tim dokter & perawat lapangan yang siap memberikan pertolongan pertama & pengobatan darurat.',
        service4Title: 'Sinergi Basarnas & BPBD',
        service4Desc: 'Kolaborasi taktis dengan Basarnas, BPBD, dan lembaga pemerintah dalam komando bencana.',
        service5Title: 'Gudang Logistik & Dapur Umum',
        service5Desc: 'Distribusi makanan siap saji, tenda pengungsian, obat-obatan, dan kebutuhan balita.',
        service6Title: 'Akuntabilitas & Jurnal Keuangan',
        service6Desc: 'Setiap rupiah donasi publik dicatat secara terbuka melalui sistem laporan keuangan terintegrasi.',

        // Mitra
        partnersTag: 'KOLABORASI & SINERGI',
        partnersTitle: 'Mitra Kemanusiaan Terpadu',
        partnersSub: 'Bekerja sama erat dengan instansi pemerintah, lembaga SAR, dan organisasi sosial.',

        // Kontak & Lokasi
        contactTag: 'HUBUNGI KAMI',
        contactTitle: 'Kantor Pusat MKT & Posko Operasional',
        contactAddressLabel: 'Alamat Kantor Pusat',
        contactAddressVal: 'Perumahan Insignia Oasis Blok B1-11 No 7',
        contactPhoneLabel: 'Layanan Darurat & WhatsApp',
        contactPhoneVal: '+62 812-3456-7890 (24 Jam)',
        contactEmailLabel: 'Email Resmi',
        contactEmailVal: 'info@mkt.or.id',
        contactHoursLabel: 'Jam Operasional',
        contactHoursVal: 'Senin - Minggu (Posko Siaga 24/7)',
        formTitle: 'Kirim Pesan / Permohonan Bantuan',
        formName: 'Nama Lengkap',
        formEmail: 'Alamat Email',
        formPhone: 'Nomor Telepon / WA',
        formSubject: 'Topik',
        formMessage: 'Pesan / Detail Permohonan',
        formSubmit: 'Kirim Pesan Kemanusiaan',
        formSuccess: 'Terima kasih! Pesan Anda telah diterima oleh tim MKT Indonesia.',
        mapTitle: 'Peta Lokasi Google Maps Kantor MKT',

        // Section CTA Bergabung
        ctaTag: 'GABUNG & BERAKSI KEMANUSIAAN',
        ctaTitle: 'Ambil Peran Dalam Ekosistem Ketangguhan Bencana',
        ctaSub: 'Bergabunglah bersama kami sebagai Mitra, Donatur, atau Relawan & Pendonor Darah untuk menyelamatkan lebih banyak nyawa.',
        ctaMitraBadge: '🤝 MITRA KEMANUSIAAN',
        ctaMitraTitle: 'Mitra Lembaga & CSR Korporasi',
        ctaMitraDesc: 'Kolaborasi antar-lembaga (Basarnas, BPBD, PMI, Lembaga Filantropi & Swasta) dalam penanganan bencana terpadu.',
        ctaMitraF1: 'Sinergi Komando & Respon Kebencanaan',
        ctaMitraF2: 'Penyaluran Program CSR Tepat Sasaran',
        ctaMitraF3: 'Laporan Akuntansi Transparan & Akuntabel',
        ctaMitraBtn: 'Daftar Sebagai Mitra',

        ctaDonorBadge: '💖 DONATUR FILANTROPI',
        ctaDonorTitle: 'Donatur Kemanusiaan Publik',
        ctaDonorDesc: 'Salurkan donasi Anda untuk tanggap darurat, operasional perahu rescue, dapur umum, dan sembako pengungsi.',
        ctaDonorF1: 'Pencatatan Keuangan Real-Time Open Ledger',
        ctaDonorF2: 'Update Penyaluran Bantuan Langsung',
        ctaDonorF3: 'Sertifikat Apresiasi Donatur Publik',
        ctaDonorBtn: 'Salurkan Donasi Sekarang',

        ctaVolBadge: '🩸 RELAWAN & DONOR DARAH',
        ctaVolTitle: 'Relawan Rescue & Donor Darah',
        ctaVolDesc: 'Bergabung dalam Tim Rescue Reaksi Cepat SAR atau menjadi relawan pendonor darah siaga panggilan darurat.',
        ctaVolF1: 'Database Golongan Darah Siaga 24/7',
        ctaVolF2: 'Pelatihan & Sertifikasi Keterampilan SAR',
        ctaVolF3: 'Aktivasi Panggilan Rescue Lapangan',
        ctaVolBtn: 'Daftar Relawan & Donor Darah',

        // Quick Role Login Banner
        quickRoleBannerTitle: 'Fitur Login Cepat Sesuai Role Pengguna',
        quickRoleBannerDesc: 'Masuk langsung ke panel sistem sesuai peran Anda: Webmaster, Mitra, Relawan, Donatur, atau Medis.',
        quickRoleBtn: 'Buka Halaman Login Role',

        footerRights: 'Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu). Hak Cipta Dilindungi.',
        footerTagline: 'Sistem Informasi Penanggulangan Bencana & Manajemen Donasi Filantropi'
    },
    en: {
        navHome: 'Home',
        navAbout: 'About MKT',
        navPillars: '3 Disaster Pillars',
        navServices: 'Main Services',
        navPartners: 'Partners',
        navContact: 'Contact & Location',
        loginBtn: 'Login Admin & Roles',
        dashboardBtn: 'Open Dashboard',
        heroTag: '📢 Integrated Disaster Management Ecosystem',
        heroTitle1: 'Building Resilience,',
        heroTitle2: 'Delivering Humanitarian Hope',
        heroDesc: 'MKT Indonesia Foundation integrates rescue teams, blood donor volunteers, medical professionals, and philanthropy partners for pre-disaster mitigation, emergency response, and post-disaster recovery.',
        heroBtnDonate: 'Donate Now',
        heroBtnVolunteer: 'Join Rescue Team',
        statVolunteers: 'Volunteers & Rescue',
        statBloodDonors: 'Blood Donors',
        statOperations: 'Disaster Operations',
        statFund: 'Donations Distributed',

        profileTag: 'ABOUT MKT INDONESIA',
        profileTitle: 'Integrated Humanitarian Partner',
        profileDesc1: 'MKT Indonesia is a non-profit humanitarian foundation dedicated to building comprehensive synergy in facing natural and humanitarian disaster threats in Indonesia.',
        profileDesc2: 'Through technological integration and an active volunteer network, we accelerate emergency response and guarantee public donation transparency.',
        visionTitle: 'Our Vision',
        visionDesc: 'To be the leading humanitarian organization building a responsive, resilient, and inclusive disaster management ecosystem in Indonesia.',
        missionTitle: 'Core Mission',
        mission1: '1. Transparent & accountable public donation collection.',
        mission2: '2. Mobilization of blood donor network for medical emergencies.',
        mission3: '3. Deployment of skilled 24/7 Search & Rescue teams.',
        mission4: '4. Pre-disaster education & post-disaster recovery support.',

        pillarTag: 'DISASTER CYCLE',
        pillarTitle: '3-Stage Integrated Disaster Management',
        pillarSub: 'MKT operates continuously across all phases of disaster risk management.',
        preDisaster: 'Pre-Disaster (Mitigation)',
        duringDisaster: 'During Disaster (Emergency Response)',
        postDisaster: 'Post-Disaster (Recovery)',
        preDesc: 'Disaster mapping, community preparedness education, volunteer SAR training, and emergency logistics stockpiling.',
        duringDesc: 'Rapid deployment of Rescue Teams to disaster points, victim evacuation, emergency medical posts & field kitchens.',
        postDesc: 'Public facility rehabilitation, psychosocial support (trauma healing), reconstruction fund allocation & economic aid.',

        servicesTag: 'HUMANITARIAN SERVICES',
        servicesTitle: 'MKT Integrated Service Ecosystem',
        service1Title: 'Rescue & SAR Team',
        service1Desc: 'Rapid response for land & water evacuation with nationally certified SAR personnel.',
        service2Title: 'Blood Donor Volunteers',
        service2Desc: 'On-demand blood type database for emergency transfusions in hospitals.',
        service3Title: 'Medical Professionals & Health Posts',
        service3Desc: 'Field doctors & nurses ready to deliver first aid & emergency medical care.',
        service4Title: 'Basarnas & BPBD Synergy',
        service4Desc: 'Tactical collaboration with Basarnas, BPBD, and government disaster commands.',
        service5Title: 'Logistics Warehouse & Field Kitchen',
        service5Desc: 'Distribution of ready-to-eat meals, shelter tents, medicines, and infant supplies.',
        service6Title: 'Accountability & Financial Ledger',
        service6Desc: 'Every single donation is transparently recorded through integrated financial journal reports.',

        partnersTag: 'COLLABORATION & SYNERGY',
        partnersTitle: 'Integrated Humanitarian Partners',
        partnersSub: 'Working closely with government agencies, SAR institutions, and social foundations.',

        contactTag: 'CONTACT US',
        contactTitle: 'MKT Headquarters & Operational Post',
        contactAddressLabel: 'Headquarters Address',
        contactAddressVal: 'Perumahan Insignia Oasis Blok B1-11 No 7',
        contactPhoneLabel: 'Emergency Hotline & WhatsApp',
        contactPhoneVal: '+62 812-3456-7890 (24 Hours)',
        contactEmailLabel: 'Official Email',
        contactEmailVal: 'info@mkt.or.id',
        contactHoursLabel: 'Operational Hours',
        contactHoursVal: 'Monday - Sunday (24/7 Emergency Command)',
        formTitle: 'Send a Message / Request Assistance',
        formName: 'Full Name',
        formEmail: 'Email Address',
        formPhone: 'Phone / WhatsApp Number',
        formSubject: 'Subject',
        formMessage: 'Message / Assistance Details',
        formSubmit: 'Submit Message',
        formSuccess: 'Thank you! Your message has been received by the MKT Indonesia team.',
        mapTitle: 'Google Maps Headquarters Location',

        // Section CTA Join
        ctaTag: 'JOIN & TAKE ACTION',
        ctaTitle: 'Take Your Role in Disaster Resilience Ecosystem',
        ctaSub: 'Choose your contribution to support disaster preparedness, blood donation, and transparent donations.',
        ctaMitraBadge: '🤝 HUMANITARIAN PARTNER',
        ctaMitraTitle: 'Institutional & Corporate CSR Partner',
        ctaMitraDesc: 'Inter-agency collaboration (Basarnas, BPBD, PMI, Philanthropy & Corporate) for integrated disaster response.',
        ctaMitraF1: 'Disaster Command & Response Synergy',
        ctaMitraF2: 'Targeted CSR Program Allocation',
        ctaMitraF3: 'Transparent & Accountable Financial Reports',
        ctaMitraBtn: 'Register as Partner',

        ctaDonorBadge: '💖 PHILANTHROPY DONOR',
        ctaDonorTitle: 'Public Humanitarian Donor',
        ctaDonorDesc: 'Distribute your donation for emergency response, rescue boat operations, field kitchens, and food packages.',
        ctaDonorF1: 'Real-Time Financial Ledger Recording',
        ctaDonorF2: 'Direct Relief Distribution Updates',
        ctaDonorF3: 'Public Donor Appreciation Certificate',
        ctaDonorBtn: 'Donate Now',

        ctaVolBadge: '🩸 VOLUNTEER & BLOOD DONOR',
        ctaVolTitle: 'Rescue Volunteer & Blood Donor',
        ctaVolDesc: 'Join the Rapid Rescue SAR Team or become an on-call blood donor volunteer for emergency transfusions.',
        ctaVolF1: '24/7 Standby Blood Type Database',
        ctaVolF2: 'SAR Training & Skill Certification',
        ctaVolF3: 'Field Rescue Emergency Activation',
        ctaVolBtn: 'Register as Volunteer & Donor',

        quickRoleBannerTitle: 'Quick Role Login Feature',
        quickRoleBannerDesc: 'Access system modules instantly based on your assigned role: Webmaster, Partner, Volunteer, Donor, or Medical.',
        quickRoleBtn: 'Go to Role Login Page',

        footerRights: 'Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu). All Rights Reserved.',
        footerTagline: 'Disaster Management & Philanthropic Donation Accounting System'
    }
};

const partners = [
    { name: 'BASARNAS', desc: 'Badan Nasional Pencarian & Pertolongan', category: 'SAR & Evakuasi', icon: '⚓' },
    { name: 'BPBD RI', desc: 'Badan Penanggulangan Bencana Daerah', category: 'Komando Kebencanaan', icon: '🏛️' },
    { name: 'PMI / Kemenkes', desc: 'Palang Merah Indonesia & Dinas Kesehatan', category: 'Donor Darah & Medis', icon: '🩸' },
    { name: 'Lembaga Filantropi', desc: 'Konsorsium Yayasan Amal Kemanusiaan', category: 'Penyalur Donasi', icon: '🤝' },
    { name: 'Rumah Sakit Rujukan', desc: 'Jaringan Medis Darurat Nasional', category: 'Kesehatan', icon: '🏥' },
    { name: 'Mitra Korporasi CSR', desc: 'Perusahaan BUMN & Swasta Peduli', category: 'Donatur Lembaga', icon: '🏢' },
];
</script>

<template>
    <Head title="Yayasan MKT Indonesia - Tanggap Kemanusiaan & Penanggulangan Bencana" />

    <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300 font-sans selection:bg-orange-500 selection:text-white">
        
        <!-- Top Emergency Alert Ticker Bar -->
        <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-orange-700 text-white text-[11px] font-semibold py-1.5 px-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-2 overflow-hidden">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white animate-pulse flex-shrink-0">
                        🔴 SIAGA 24/7
                    </span>
                    <span class="truncate">
                        Posko Tanggap Bencana & Relawan Donor Darah | Hotline: <strong class="font-bold underline">+62 812-3456-7890</strong>
                    </span>
                </div>
                <div class="hidden sm:flex items-center space-x-4 text-[10px] opacity-90 flex-shrink-0">
                    <span>📍 Insignia Oasis B1-11 No 7</span>
                    <span>•</span>
                    <span>Basarnas & BPBD Synergy</span>
                </div>
            </div>
        </div>

        <!-- Floating Glassmorphic Navbar -->
        <header class="sticky top-2 z-50 px-3 sm:px-6 lg:px-8 transition-all duration-300">
            <div class="max-w-7xl mx-auto bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 shadow-xl shadow-orange-500/5 rounded-2xl sm:rounded-3xl px-4 sm:px-6 py-2.5 sm:py-3 transition-all">
                <div class="flex items-center justify-between">
                    
                    <!-- Brand Logo -->
                    <a href="#" class="flex items-center">
                        <MktLogo variant="full" icon-size="w-10 h-10" text-size="text-lg sm:text-xl" :show-subtitle="true" />
                    </a>

                    <!-- Nav Links (Desktop Pill Navigation) -->
                    <nav class="hidden lg:flex items-center space-x-1 text-xs font-bold text-slate-600 dark:text-slate-300">
                        <a 
                            href="#about" 
                            class="px-3.5 py-2 rounded-xl hover:bg-orange-500/10 dark:hover:bg-orange-500/20 hover:text-orange-600 dark:hover:text-orange-400 transition-all flex items-center space-x-1.5"
                        >
                            <span>🏢</span>
                            <span>{{ t[currentLang].navAbout }}</span>
                        </a>
                        <a 
                            href="#pillars" 
                            class="px-3.5 py-2 rounded-xl hover:bg-orange-500/10 dark:hover:bg-orange-500/20 hover:text-orange-600 dark:hover:text-orange-400 transition-all flex items-center space-x-1.5"
                        >
                            <span>🛡️</span>
                            <span>{{ t[currentLang].navPillars }}</span>
                        </a>
                        <a 
                            href="#services" 
                            class="px-3.5 py-2 rounded-xl hover:bg-orange-500/10 dark:hover:bg-orange-500/20 hover:text-orange-600 dark:hover:text-orange-400 transition-all flex items-center space-x-1.5"
                        >
                            <span>⚡</span>
                            <span>{{ t[currentLang].navServices }}</span>
                        </a>
                        <a 
                            href="#partners" 
                            class="px-3.5 py-2 rounded-xl hover:bg-orange-500/10 dark:hover:bg-orange-500/20 hover:text-orange-600 dark:hover:text-orange-400 transition-all flex items-center space-x-1.5"
                        >
                            <span>🤝</span>
                            <span>{{ t[currentLang].navPartners }}</span>
                        </a>
                        <a 
                            href="#contact" 
                            class="px-3.5 py-2 rounded-xl hover:bg-orange-500/10 dark:hover:bg-orange-500/20 hover:text-orange-600 dark:hover:text-orange-400 transition-all flex items-center space-x-1.5"
                        >
                            <span>📍</span>
                            <span>{{ t[currentLang].navContact }}</span>
                        </a>
                    </nav>

                    <!-- Control Actions Right Side -->
                    <div class="flex items-center space-x-2.5">
                        
                        <!-- Emergency Phone Quick Call Button (Desktop) -->
                        <a 
                            href="tel:+6281234567890" 
                            class="hidden md:flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 border border-rose-500/20 text-xs font-bold transition-all"
                            title="Call Emergency Center"
                        >
                            <svg class="w-3.5 h-3.5 animate-bounce text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>Hotline</span>
                        </a>

                        <!-- Language Switcher Pill Segment -->
                        <div class="flex items-center bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-xs font-semibold">
                            <button
                                @click="toggleLang('id')"
                                class="px-2.5 py-1 rounded-lg transition-all text-xs"
                                :class="currentLang === 'id' ? 'bg-white dark:bg-slate-900 text-orange-600 dark:text-orange-400 shadow-sm font-black' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                            >
                                🇮🇩 ID
                            </button>
                            <button
                                @click="toggleLang('en')"
                                class="px-2.5 py-1 rounded-lg transition-all text-xs"
                                :class="currentLang === 'en' ? 'bg-white dark:bg-slate-900 text-orange-600 dark:text-orange-400 shadow-sm font-black' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                            >
                                🇬🇧 EN
                            </button>
                        </div>

                        <!-- Theme Toggle Button -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 rounded-xl text-slate-500 hover:text-orange-500 bg-slate-100 dark:bg-slate-800/80 hover:bg-orange-50 dark:hover:bg-orange-950/40 border border-slate-200/80 dark:border-slate-700/80 transition-all"
                            :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                        >
                            <svg v-if="isDarkMode" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        </button>

                        <!-- Role Login Button -->
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('login')"
                            class="hidden sm:inline-flex px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 rounded-xl shadow-lg shadow-orange-500/25 active:scale-95 transition-all items-center space-x-1.5"
                        >
                            <span>⚡</span>
                            <span>{{ t[currentLang].loginBtn }}</span>
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="hidden sm:inline-flex px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 rounded-xl shadow-lg shadow-orange-500/25 active:scale-95 transition-all items-center space-x-1.5"
                        >
                            <span>📊</span>
                            <span>{{ t[currentLang].dashboardBtn }}</span>
                        </Link>

                        <!-- Mobile Hamburger Button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-orange-500 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 transition-all"
                            aria-label="Toggle Mobile Menu"
                        >
                            <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu Drawer Dropdown -->
                <div 
                    v-if="mobileMenuOpen" 
                    class="lg:hidden mt-3 pt-3 border-t border-slate-200/80 dark:border-slate-800 space-y-2 animate-fadeIn"
                >
                    <a 
                        href="#about" 
                        @click="mobileMenuOpen = false"
                        class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-600"
                    >
                        <span>🏢</span>
                        <span>{{ t[currentLang].navAbout }}</span>
                    </a>
                    <a 
                        href="#pillars" 
                        @click="mobileMenuOpen = false"
                        class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-600"
                    >
                        <span>🛡️</span>
                        <span>{{ t[currentLang].navPillars }}</span>
                    </a>
                    <a 
                        href="#services" 
                        @click="mobileMenuOpen = false"
                        class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-600"
                    >
                        <span>⚡</span>
                        <span>{{ t[currentLang].navServices }}</span>
                    </a>
                    <a 
                        href="#partners" 
                        @click="mobileMenuOpen = false"
                        class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-600"
                    >
                        <span>🤝</span>
                        <span>{{ t[currentLang].navPartners }}</span>
                    </a>
                    <a 
                        href="#contact" 
                        @click="mobileMenuOpen = false"
                        class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-orange-500/10 hover:text-orange-600"
                    >
                        <span>📍</span>
                        <span>{{ t[currentLang].navContact }}</span>
                    </a>

                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/60 flex flex-col gap-2">
                        <a 
                            href="tel:+6281234567890" 
                            class="flex items-center justify-center space-x-2 py-2.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs border border-rose-500/20"
                        >
                            <span>📞 Hotline Emergency: +62 812-3456-7890</span>
                        </a>

                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('login')"
                            class="flex items-center justify-center space-x-2 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-xs shadow-md"
                        >
                            <span>⚡ {{ t[currentLang].loginBtn }}</span>
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="flex items-center justify-center space-x-2 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-xs shadow-md"
                        >
                            <span>📊 {{ t[currentLang].dashboardBtn }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section with Full Background Image & Adaptive Dual-Mode Aesthetics -->
        <section class="relative overflow-hidden min-h-[640px] sm:min-h-[720px] flex items-center justify-center py-16 sm:py-20 lg:py-28 bg-amber-50/40 dark:bg-slate-950 transition-colors border-b border-orange-500/10 dark:border-slate-800/80">
            <!-- Full Background Image Layer -->
            <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
                <img 
                    :src="heroRescueImg" 
                    alt="MKT Rescue Operation Background" 
                    class="w-full h-full object-cover object-center scale-105 transition-transform duration-1000 ease-out opacity-85 dark:opacity-40" 
                />
                
                <!-- Light Mode Overlay: Directional frosted gradient (Crisp clean white on text side -> Translucent on rescue photo side) -->
                <div class="block dark:hidden absolute inset-0 bg-gradient-to-r from-white via-white/90 to-white/35"></div>
                <div class="block dark:hidden absolute inset-0 bg-gradient-to-t from-white via-transparent to-white/60"></div>
                <div class="block dark:hidden absolute inset-0 bg-orange-500/5 mix-blend-multiply"></div>

                <!-- Dark Mode Overlay: Directional deep slate gradient (Deep slate on text side -> Translucent on rescue photo side) -->
                <div class="hidden dark:block absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-950/80 to-slate-950/40"></div>
                <div class="hidden dark:block absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-slate-950/60"></div>
                <div class="hidden dark:block absolute inset-0 bg-orange-950/20 mix-blend-overlay"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Hero Main Content -->
                    <div class="lg:col-span-7 space-y-6 sm:space-y-7">
                        <!-- Emergency Ecosystem Tag -->
                        <div class="inline-flex items-center space-x-2 bg-orange-500/10 dark:bg-orange-500/20 border border-orange-500/30 dark:border-orange-400/40 text-orange-800 dark:text-orange-300 px-4 py-1.5 rounded-full text-xs font-bold backdrop-blur-md shadow-sm">
                            <span class="animate-pulse">🚨</span>
                            <span>{{ t[currentLang].heroTag }}</span>
                        </div>
                        
                        <!-- Headline -->
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-950 dark:text-white tracking-tight leading-[1.12]">
                            {{ t[currentLang].heroTitle1 }} <br class="hidden sm:inline" />
                            <span class="bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 dark:from-orange-400 dark:via-amber-300 dark:to-orange-400 bg-clip-text text-transparent">
                                {{ t[currentLang].heroTitle2 }}
                            </span>
                        </h1>
                        
                        <!-- Description -->
                        <p class="text-base sm:text-lg text-slate-700 dark:text-slate-200 leading-relaxed max-w-2xl font-normal">
                            {{ t[currentLang].heroDesc }}
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-2">
                            <Link
                                :href="route('login')"
                                class="px-8 py-4 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-2xl shadow-xl shadow-orange-500/25 active:scale-95 transition-all text-center flex items-center justify-center space-x-2.5"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ t[currentLang].heroBtnDonate }}</span>
                            </Link>
                            <a
                                href="#contact"
                                class="px-8 py-4 bg-white/90 dark:bg-white/10 hover:bg-white dark:hover:bg-white/20 backdrop-blur-md border border-slate-300 dark:border-white/20 text-slate-800 dark:text-white hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-300 font-bold rounded-2xl shadow-md hover:shadow-lg active:scale-95 transition-all text-center flex items-center justify-center space-x-2.5"
                            >
                                <svg class="w-5 h-5 text-orange-500 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                <span>{{ t[currentLang].heroBtnVolunteer }}</span>
                            </a>
                        </div>

                        <!-- Stats Grid Counter -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 pt-6 border-t border-slate-200 dark:border-white/15">
                            <div class="p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/60 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-md shadow-slate-200/50 dark:shadow-none">
                                <span class="text-2xl font-black text-orange-600 dark:text-orange-400 block">1,250+</span>
                                <span class="text-xs text-slate-600 dark:text-slate-300 font-semibold">{{ t[currentLang].statVolunteers }}</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/60 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-md shadow-slate-200/50 dark:shadow-none">
                                <span class="text-2xl font-black text-rose-600 dark:text-rose-400 block">850+</span>
                                <span class="text-xs text-slate-600 dark:text-slate-300 font-semibold">{{ t[currentLang].statBloodDonors }}</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/60 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-md shadow-slate-200/50 dark:shadow-none">
                                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 block">42+</span>
                                <span class="text-xs text-slate-600 dark:text-slate-300 font-semibold">{{ t[currentLang].statOperations }}</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-white/90 dark:bg-slate-900/60 backdrop-blur-md border border-slate-200/80 dark:border-white/15 shadow-md shadow-slate-200/50 dark:shadow-none">
                                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 block">Rp 1.5M+</span>
                                <span class="text-xs text-slate-600 dark:text-slate-300 font-semibold">{{ t[currentLang].statFund }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Glassmorphism Highlight Cards -->
                    <div class="lg:col-span-5 space-y-4">
                        <!-- Card 1: Siaga SAR 24/7 -->
                        <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/70 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl shadow-slate-200/50 dark:shadow-2xl hover:border-orange-500/50 hover:-translate-y-0.5 transition-all group">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 border border-orange-500/20 dark:border-orange-500/30 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                    🚨
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-sm font-black text-slate-950 dark:text-white uppercase tracking-wider">Tim Rescue Siaga 24/7</h4>
                                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                        Standby armada & personel penyelamat tanggap cepat dalam 30 menit pasca terjadinya musibah bencana.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Relawan Donor Darah -->
                        <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/70 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl shadow-slate-200/50 dark:shadow-2xl hover:border-rose-500/50 hover:-translate-y-0.5 transition-all group">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 dark:border-rose-500/30 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                    🩸
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-950 dark:text-white uppercase tracking-wider">Bank Data Donor Darah</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                        Jejaring database ribuan pendonor aktif terintegrasi dengan PMI dan Rumah Sakit rujukan darurat.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Command Center Bencana Real-time -->
                        <div class="p-5 rounded-3xl bg-white/95 dark:bg-slate-900/70 backdrop-blur-xl border border-slate-200/90 dark:border-white/15 shadow-xl shadow-slate-200/50 dark:shadow-2xl hover:border-amber-500/50 hover:-translate-y-0.5 transition-all group">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/20 dark:border-amber-500/30 flex items-center justify-center font-bold text-xl shrink-0 group-hover:scale-110 transition-transform">
                                    🗺️
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-950 dark:text-white uppercase tracking-wider">Pusat Komando & BMKG Live</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                        Monitoring sebaran titik darurat, prakiraan cuaca ekstrim BMKG, dan pergerakan armada logistik.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profil MKT Section -->
        <section id="about" class="py-20 bg-white dark:bg-slate-900 transition-colors border-y border-slate-200/60 dark:border-slate-800/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left: Profile Text & Vision/Mission -->
                    <div class="lg:col-span-7 space-y-8">
                        <div>
                            <span class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-widest bg-orange-500/10 px-3.5 py-1.5 rounded-full border border-orange-500/20">
                                {{ t[currentLang].profileTag }}
                            </span>
                            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-4 tracking-tight">
                                {{ t[currentLang].profileTitle }}
                            </h2>
                            <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-4 leading-relaxed font-normal">
                                {{ t[currentLang].profileDesc1 }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Visi Card -->
                            <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between hover:border-orange-500/50 transition-all shadow-sm">
                                <div>
                                    <div class="w-10 h-10 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-xl mb-4">
                                        👁️
                                    </div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white mb-2">
                                        {{ t[currentLang].visionTitle }}
                                    </h3>
                                    <p class="text-slate-600 dark:text-slate-300 text-xs leading-relaxed">
                                        {{ t[currentLang].visionDesc }}
                                    </p>
                                </div>
                            </div>

                            <!-- Misi Card -->
                            <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between hover:border-orange-500/50 transition-all shadow-sm">
                                <div>
                                    <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl mb-4">
                                        🎯
                                    </div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white mb-2">
                                        {{ t[currentLang].missionTitle }}
                                    </h3>
                                    <ul class="space-y-1.5 text-[11px] sm:text-xs text-slate-600 dark:text-slate-300">
                                        <li>{{ t[currentLang].mission1 }}</li>
                                        <li>{{ t[currentLang].mission2 }}</li>
                                        <li>{{ t[currentLang].mission3 }}</li>
                                        <li>{{ t[currentLang].mission4 }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Section Image Card -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-slate-100 dark:border-slate-800 group">
                            <img 
                                :src="aboutMktImg" 
                                alt="MKT Foundation Team Strategy" 
                                class="w-full h-[400px] object-cover group-hover:scale-105 transition-transform duration-700" 
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
                            
                            <!-- Floating Badge Card -->
                            <div class="absolute bottom-6 left-6 right-6 p-4 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-white/20 dark:border-slate-800 shadow-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center font-bold text-lg">
                                        🗺️
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Komando Respon Kebencanaan</h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Koordinasi lintas relawan & pemetaan strategi mitigasi bencana.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3 Pilar Bencana Section -->
        <section id="pillars" class="py-20 bg-slate-50 dark:bg-slate-950 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-widest bg-orange-500/10 px-3 py-1 rounded-full border border-orange-500/20">
                        {{ t[currentLang].pillarTag }}
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-4">
                        {{ t[currentLang].pillarTitle }}
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-3">
                        {{ t[currentLang].pillarSub }}
                    </p>
                </div>

                <!-- Interactive Tab Switcher -->
                <div class="flex justify-center mb-10">
                    <div class="bg-white dark:bg-slate-900 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-wrap justify-center gap-2 shadow-sm">
                        <button
                            @click="activePillar = 'pre'"
                            class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all"
                            :class="activePillar === 'pre' ? 'bg-orange-500 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-orange-500'"
                        >
                            1. {{ t[currentLang].preDisaster }}
                        </button>
                        <button
                            @click="activePillar = 'during'"
                            class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all"
                            :class="activePillar === 'during' ? 'bg-orange-500 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-orange-500'"
                        >
                            2. {{ t[currentLang].duringDisaster }}
                        </button>
                        <button
                            @click="activePillar = 'post'"
                            class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all"
                            :class="activePillar === 'post' ? 'bg-orange-500 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-orange-500'"
                        >
                            3. {{ t[currentLang].postDisaster }}
                        </button>
                    </div>
                </div>

                <!-- Pillar Active Content Display Card with Section Images -->
                <div class="max-w-5xl mx-auto">
                    <!-- Pre Disaster -->
                    <div v-if="activePillar === 'pre'" class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl transition-all">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <div class="lg:col-span-7 space-y-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl">
                                    🛡️
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white">
                                    Fase 1: {{ t[currentLang].preDisaster }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-300 leading-relaxed text-sm">
                                    {{ t[currentLang].preDesc }}
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        📍 Pemetaan Bencana
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        📣 Pelatihan SAR Warga
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        📦 Stok Logistik Darurat
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-5">
                                <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-md group">
                                    <img :src="pillarPreImg" alt="Prabencana Training" class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-500" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- During Disaster -->
                    <div v-if="activePillar === 'during'" class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl transition-all">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <div class="lg:col-span-7 space-y-4">
                                <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-2xl">
                                    ⚡
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white">
                                    Fase 2: {{ t[currentLang].duringDisaster }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-300 leading-relaxed text-sm">
                                    {{ t[currentLang].duringDesc }}
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        🚑 Rescue & Evakuasi SAR
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        🩸 Donor Darah Darurat
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        🍲 Dapur Umum Pengungsi
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-5">
                                <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-md group">
                                    <img :src="pillarDuringImg" alt="Tanggap Darurat Rescue" class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-500" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Post Disaster -->
                    <div v-if="activePillar === 'post'" class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl transition-all">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <div class="lg:col-span-7 space-y-4">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl">
                                    🌱
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white">
                                    Fase 3: {{ t[currentLang].postDisaster }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-300 leading-relaxed text-sm">
                                    {{ t[currentLang].postDesc }}
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        🏡 Rehabilitasi & Rekonstruksi
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        ❤️ Trauma Healing Medis
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 text-xs font-semibold border border-slate-100 dark:border-slate-800">
                                        📊 Laporan Keuangan Terbuka
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-5">
                                <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-md group">
                                    <img :src="pillarPostImg" alt="Pemulihan Pascabencana" class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-500" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Layanan Utama Section -->
        <section id="services" class="py-20 bg-white dark:bg-slate-900 transition-colors border-t border-slate-200/60 dark:border-slate-800/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-widest bg-orange-500/10 px-3 py-1 rounded-full border border-orange-500/20">
                        {{ t[currentLang].servicesTag }}
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-4">
                        {{ t[currentLang].servicesTitle }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Service 1 -->
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500 transition-all hover:-translate-y-1 group">
                        <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                            ⚡
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">
                            {{ t[currentLang].service1Title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ t[currentLang].service1Desc }}
                        </p>
                    </div>

                    <!-- Service 2 -->
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 hover:border-rose-500 transition-all hover:-translate-y-1 group">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                            🩸
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">
                            {{ t[currentLang].service2Title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ t[currentLang].service2Desc }}
                        </p>
                    </div>

                    <!-- Service 3 -->
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 hover:border-emerald-500 transition-all hover:-translate-y-1 group">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                            🩺
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">
                            {{ t[currentLang].service3Title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ t[currentLang].service3Desc }}
                        </p>
                    </div>

                    <!-- Service 4 -->
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 hover:border-blue-500 transition-all hover:-translate-y-1 group">
                        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                            ⚓
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">
                            {{ t[currentLang].service4Title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ t[currentLang].service4Desc }}
                        </p>
                    </div>

                    <!-- Service 5 -->
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 hover:border-amber-500 transition-all hover:-translate-y-1 group">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                            📦
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">
                            {{ t[currentLang].service5Title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ t[currentLang].service5Desc }}
                        </p>
                    </div>

                    <!-- Service 6 -->
                    <div class="p-8 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 hover:border-purple-500 transition-all hover:-translate-y-1 group">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                            📊
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">
                            {{ t[currentLang].service6Title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ t[currentLang].service6Desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mitra Kemanusiaan (Partners) Section -->
        <section id="partners" class="py-20 bg-slate-50 dark:bg-slate-950 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-widest bg-orange-500/10 px-3 py-1 rounded-full border border-orange-500/20">
                        {{ t[currentLang].partnersTag }}
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-4">
                        {{ t[currentLang].partnersTitle }}
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-3">
                        {{ t[currentLang].partnersSub }}
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div 
                        v-for="(partner, idx) in partners" 
                        :key="idx" 
                        class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center hover:border-orange-500 transition-all flex flex-col items-center justify-center space-y-2 shadow-sm"
                    >
                        <span class="text-3xl mb-1">{{ partner.icon }}</span>
                        <h4 class="font-black text-xs sm:text-sm text-slate-900 dark:text-white">{{ partner.name }}</h4>
                        <span class="text-[10px] text-orange-600 dark:text-orange-400 font-bold bg-orange-500/10 px-2 py-0.5 rounded-full">{{ partner.category }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section CTA Bergabung (Mitra, Donatur, Relawan & Donor Darah) -->
        <section id="join-cta" class="py-20 bg-white dark:bg-slate-900 transition-colors border-t border-slate-200/80 dark:border-slate-800/80 relative overflow-hidden">
            <!-- Background Glow Effect -->
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-gradient-to-r from-orange-500/10 via-amber-500/10 to-rose-500/10 blur-3xl pointer-events-none rounded-full"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-widest bg-orange-500/10 px-3.5 py-1.5 rounded-full border border-orange-500/20">
                        {{ t[currentLang].ctaTag }}
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 dark:text-white mt-4 tracking-tight">
                        {{ t[currentLang].ctaTitle }}
                    </h2>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3 leading-relaxed">
                        {{ t[currentLang].ctaSub }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- CTA Card 1: Mitra Kemanusiaan -->
                    <div class="rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 hover:border-blue-500 transition-all duration-300 hover:-translate-y-1.5 shadow-sm flex flex-col justify-between overflow-hidden group">
                        <div>
                            <!-- Card Image Banner Header -->
                            <div class="relative h-44 overflow-hidden">
                                <img :src="aboutMktImg" alt="Mitra Kemanusiaan" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
                                <div class="absolute bottom-3 left-4">
                                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-500/90 text-white shadow-md">
                                        <span>{{ t[currentLang].ctaMitraBadge }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2 group-hover:text-blue-500 transition-colors">
                                    {{ t[currentLang].ctaMitraTitle }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                                    {{ t[currentLang].ctaMitraDesc }}
                                </p>
                                <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 mb-6 font-medium">
                                    <li class="flex items-center space-x-2">
                                        <span class="text-blue-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaMitraF1 }}</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <span class="text-blue-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaMitraF2 }}</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <span class="text-blue-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaMitraF3 }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="p-6 pt-0">
                            <button
                                @click="openCtaModal('mitra')"
                                class="w-full py-3.5 px-6 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs sm:text-sm shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center space-x-2"
                            >
                                <span>🤝 {{ t[currentLang].ctaMitraBtn }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- CTA Card 2: Donatur Filantropi -->
                    <div class="rounded-3xl bg-gradient-to-b from-orange-500/5 via-slate-50 to-slate-50 dark:from-orange-500/10 dark:via-slate-950 dark:to-slate-950 border-2 border-orange-500/30 hover:border-orange-500 transition-all duration-300 hover:-translate-y-1.5 shadow-xl flex flex-col justify-between relative overflow-hidden group">
                        <div class="absolute top-3 right-3 z-10 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-md">
                            SIAP TANGGAP
                        </div>
                        <div>
                            <!-- Card Image Banner Header -->
                            <div class="relative h-44 overflow-hidden">
                                <img :src="heroRescueImg" alt="Donatur Filantropi" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
                                <div class="absolute bottom-3 left-4">
                                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/90 text-white shadow-md">
                                        <span>{{ t[currentLang].ctaDonorBadge }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2 group-hover:text-orange-500 transition-colors">
                                    {{ t[currentLang].ctaDonorTitle }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                                    {{ t[currentLang].ctaDonorDesc }}
                                </p>
                                <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 mb-6 font-medium">
                                    <li class="flex items-center space-x-2">
                                        <span class="text-orange-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaDonorF1 }}</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <span class="text-orange-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaDonorF2 }}</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <span class="text-orange-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaDonorF3 }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="p-6 pt-0">
                            <button
                                @click="openCtaModal('donatur')"
                                class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 active:scale-95 text-white font-bold text-xs sm:text-sm shadow-xl shadow-orange-500/25 transition-all flex items-center justify-center space-x-2"
                            >
                                <span>💖 {{ t[currentLang].ctaDonorBtn }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- CTA Card 3: Relawan Rescue & Donor Darah -->
                    <div class="rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 hover:border-rose-500 transition-all duration-300 hover:-translate-y-1.5 shadow-sm flex flex-col justify-between overflow-hidden group">
                        <div>
                            <!-- Card Image Banner Header -->
                            <div class="relative h-44 overflow-hidden">
                                <img :src="bloodDonorImg" alt="Relawan Donor Darah" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
                                <div class="absolute bottom-3 left-4">
                                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/90 text-white shadow-md">
                                        <span>{{ t[currentLang].ctaVolBadge }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2 group-hover:text-rose-500 transition-colors">
                                    {{ t[currentLang].ctaVolTitle }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed mb-6">
                                    {{ t[currentLang].ctaVolDesc }}
                                </p>
                                <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 mb-6 font-medium">
                                    <li class="flex items-center space-x-2">
                                        <span class="text-rose-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaVolF1 }}</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <span class="text-rose-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaVolF3 }}</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <span class="text-rose-500 font-bold">✓</span>
                                        <span>{{ t[currentLang].ctaVolF2 }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="p-6 pt-0">
                            <button
                                @click="openCtaModal('relawan')"
                                class="w-full py-3.5 px-6 rounded-2xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs sm:text-sm shadow-lg shadow-rose-500/20 transition-all flex items-center justify-center space-x-2"
                            >
                                <span>🩸 {{ t[currentLang].ctaVolBtn }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Banner Quick Role Login -->
        <section class="py-12 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h3 class="text-2xl sm:text-3xl font-black tracking-tight">
                        {{ t[currentLang].quickRoleBannerTitle }}
                    </h3>
                    <p class="text-orange-100 text-xs sm:text-sm mt-2 max-w-2xl font-medium">
                        {{ t[currentLang].quickRoleBannerDesc }}
                    </p>
                </div>
                <Link
                    :href="route('login')"
                    class="px-8 py-3.5 bg-slate-950 hover:bg-slate-900 text-white font-bold rounded-2xl shadow-xl active:scale-95 transition-all text-xs sm:text-sm flex-shrink-0 flex items-center space-x-2"
                >
                    <span>⚡ {{ t[currentLang].quickRoleBtn }}</span>
                </Link>
            </div>
        </section>

        <!-- Kontak & Lokasi Kantor Google Maps Section -->
        <section id="contact" class="py-20 bg-white dark:bg-slate-900 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-widest bg-orange-500/10 px-3 py-1 rounded-full border border-orange-500/20">
                        {{ t[currentLang].contactTag }}
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-4">
                        {{ t[currentLang].contactTitle }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <!-- Left: Contact Details & Office Location -->
                    <div class="lg:col-span-5 space-y-6">
                        <!-- Address Box -->
                        <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-xl flex-shrink-0">
                                    📍
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ t[currentLang].contactAddressLabel }}</h4>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base mt-0.5">
                                        {{ t[currentLang].contactAddressVal }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4 pt-2 border-t border-slate-200/80 dark:border-slate-800">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl flex-shrink-0">
                                    📞
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ t[currentLang].contactPhoneLabel }}</h4>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base mt-0.5">
                                        {{ t[currentLang].contactPhoneVal }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4 pt-2 border-t border-slate-200/80 dark:border-slate-800">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl flex-shrink-0">
                                    ✉️
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ t[currentLang].contactEmailLabel }}</h4>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base mt-0.5">
                                        {{ t[currentLang].contactEmailVal }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4 pt-2 border-t border-slate-200/80 dark:border-slate-800">
                                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl flex-shrink-0">
                                    🕒
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ t[currentLang].contactHoursLabel }}</h4>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base mt-0.5">
                                        {{ t[currentLang].contactHoursVal }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Google Maps Embed Container -->
                        <div class="rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-md relative bg-slate-100 dark:bg-slate-950">
                            <div class="p-3 bg-slate-900 text-white text-xs font-bold flex items-center justify-between">
                                <span>🗺️ {{ t[currentLang].mapTitle }}</span>
                                <span class="text-[10px] text-orange-400">Insignia Oasis Blok B1-11 No 7</span>
                            </div>
                            <iframe 
                                title="Lokasi Kantor MKT Google Maps" 
                                class="w-full h-64 border-0" 
                                src="https://maps.google.com/maps?q=Perumahan%20Insignia%20Oasis%20Blok%20B1-11%20No%207&t=&z=16&ie=UTF8&iwloc=&output=embed"
                                loading="lazy" 
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>

                    <!-- Right: Contact Interactive Form -->
                    <div class="lg:col-span-7">
                        <div class="p-8 sm:p-10 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-sm">
                            <h3 class="text-xl font-black text-slate-900 dark:text-white mb-6">
                                {{ t[currentLang].formTitle }}
                            </h3>

                            <div v-if="isSubmitted" class="p-4 mb-6 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs sm:text-sm rounded-2xl font-bold flex items-center space-x-3">
                                <span class="text-xl">✅</span>
                                <span>{{ t[currentLang].formSuccess }}</span>
                            </div>

                            <form @submit.prevent="handleContactSubmit" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ t[currentLang].formName }}</label>
                                        <input 
                                            v-model="contactForm.name" 
                                            type="text" 
                                            required 
                                            placeholder="Nama Lengkap" 
                                            class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" 
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ t[currentLang].formEmail }}</label>
                                        <input 
                                            v-model="contactForm.email" 
                                            type="email" 
                                            required 
                                            placeholder="email@domain.com" 
                                            class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" 
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ t[currentLang].formPhone }}</label>
                                        <input 
                                            v-model="contactForm.phone" 
                                            type="text" 
                                            placeholder="+62 812..." 
                                            class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" 
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ t[currentLang].formSubject }}</label>
                                        <select 
                                            v-model="contactForm.subject" 
                                            class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5"
                                        >
                                            <option value="donasi">Informasi Donasi</option>
                                            <option value="relawan">Pendaftaran Relawan</option>
                                            <option value="bantuan">Permohonan Bantuan Bencana</option>
                                            <option value="kerjasama">Kerja Sama Mitra</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ t[currentLang].formMessage }}</label>
                                    <textarea 
                                        v-model="contactForm.message" 
                                        rows="4" 
                                        required 
                                        placeholder="Tuliskan pesan atau kebutuhan tanggap bencana Anda di sini..." 
                                        class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-orange-500 focus:ring-orange-500 text-sm p-3"
                                    ></textarea>
                                </div>

                                <button 
                                    type="submit" 
                                    class="w-full py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/20 active:scale-[0.98] transition-all text-sm"
                                >
                                    {{ t[currentLang].formSubmit }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 py-12 sm:py-16 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Top Footer Section with Logo, Mission, & Quick Nav -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-10 border-b border-slate-200/80 dark:border-slate-800/80">
                    <div class="md:col-span-6 space-y-4">
                        <MktLogo variant="full" icon-size="w-12 h-12" text-size="text-xl" :show-subtitle="true" />
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-md leading-relaxed">
                            {{ t[currentLang].footerTagline }}
                        </p>
                        <div class="flex items-center space-x-3 text-xs text-slate-500 dark:text-slate-400 pt-1">
                            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                <span>Operasional 24/7 Siaga Bencana</span>
                            </span>
                        </div>
                    </div>

                    <div class="md:col-span-6 grid grid-cols-2 sm:grid-cols-3 gap-6 text-xs">
                        <div class="space-y-3">
                            <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">Navigasi</h4>
                            <ul class="space-y-2 text-slate-600 dark:text-slate-400">
                                <li><a href="#about" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ t[currentLang].navAbout }}</a></li>
                                <li><a href="#pillars" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ t[currentLang].navPillars }}</a></li>
                                <li><a href="#services" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ t[currentLang].navServices }}</a></li>
                                <li><a href="#partners" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ t[currentLang].navPartners }}</a></li>
                            </ul>
                        </div>
                        <div class="space-y-3">
                            <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">Layanan</h4>
                            <ul class="space-y-2 text-slate-600 dark:text-slate-400">
                                <li><a href="#contact" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">Rescue SAR 24/7</a></li>
                                <li><a href="#contact" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">Bank Donor Darah</a></li>
                                <li><a href="#contact" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">Distribusi Logistik</a></li>
                                <li><a href="#contact" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">Laporan Keuangan Terbuka</a></li>
                            </ul>
                        </div>
                        <div class="space-y-3 col-span-2 sm:col-span-1">
                            <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">Kontak Darurat</h4>
                            <ul class="space-y-2 text-slate-600 dark:text-slate-400">
                                <li class="font-bold text-rose-600 dark:text-rose-400">📞 +62 812-3456-7890</li>
                                <li>📧 info@mkt-indonesia.org</li>
                                <li class="text-[11px] leading-relaxed">Perumahan Insignia Oasis Blok B1-11 No 7</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Bottom Copyright -->
                <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
                    <p>&copy; 2026 Yayasan Mitra Kemanusiaan Terpadu (MKT Indonesia). {{ t[currentLang].footerRights }}</p>
                    <p class="text-[11px]">Sistem Terintegrasi Siaga Bencana, Relawan & Donasi Publik</p>
                </div>
            </div>
        </footer>

        <!-- Modal Pendaftaran / Aksi CTA (Mitra, Donatur, Relawan & Donor Darah) -->
        <div 
            v-if="showCtaModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn"
            @click.self="showCtaModal = false"
        >
            <div class="relative w-full max-w-lg p-6 sm:p-8 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl space-y-6">
                <!-- Modal Close Button -->
                <button 
                    @click="showCtaModal = false"
                    class="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Modal Header -->
                <div>
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold mb-2"
                        :class="[
                            ctaModalType === 'mitra' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : '',
                            ctaModalType === 'donatur' ? 'bg-orange-500/10 text-orange-600 dark:text-orange-400' : '',
                            ctaModalType === 'relawan' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : ''
                        ]"
                    >
                        <span v-if="ctaModalType === 'mitra'">🤝 Registrasi Mitra Kemanusiaan</span>
                        <span v-if="ctaModalType === 'donatur'">💖 Penyaluran Donasi Filantropi</span>
                        <span v-if="ctaModalType === 'relawan'">🩸 Pendaftaran Relawan & Donor Darah</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white">
                        <span v-if="ctaModalType === 'mitra'">Formulir Kerjasama Mitra MKT</span>
                        <span v-if="ctaModalType === 'donatur'">Donasi Kemanusiaan Terpadu</span>
                        <span v-if="ctaModalType === 'relawan'">Daftar Tim Rescue & Donor Darah</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Isi formulir di bawah ini untuk pendaftaran cepat atau masuk langsung menggunakan akun role demo.
                    </p>
                </div>

                <!-- Success Alert -->
                <div v-if="ctaSubmitted" class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs rounded-2xl font-bold flex items-start space-x-3">
                    <span class="text-2xl flex-shrink-0">📧</span>
                    <div>
                        <span class="block font-black text-sm">Pendaftaran Berhasil Diterima!</span>
                        <p class="text-xs font-normal mt-1 leading-relaxed">
                            {{ ctaFeedbackMessage || 'Email notifikasi konfirmasi pendaftaran telah berhasil dikirim ke alamat email Anda.' }}
                        </p>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-2">
                            ✉️ Silakan periksa inbox / folder spam email Anda untuk rincian data keanggotaan.
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <form v-else @submit.prevent="handleCtaSubmit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap / Instansi</label>
                        <input 
                            v-model="ctaForm.name" 
                            type="text" 
                            required 
                            placeholder="Contoh: Budi Santoso / PT Mitra Peduli" 
                            class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" 
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input 
                            v-model="ctaForm.email" 
                            type="email" 
                            required 
                            placeholder="email@domain.com" 
                            class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" 
                        />
                    </div>

                    <!-- Role Specific Selection: Relawan Rescuer vs Relawan Donor Darah -->
                    <div v-if="ctaModalType === 'relawan'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Pilih Opsi Relawan</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="ctaForm.volunteer_option = 'Relawan Rescuer'"
                                class="p-3 rounded-2xl border text-left transition-all"
                                :class="ctaForm.volunteer_option === 'Relawan Rescuer' ? 'bg-orange-500/10 border-orange-500 text-orange-600 dark:text-orange-400 font-bold shadow-sm' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                            >
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg">🚑</span>
                                    <div>
                                        <span class="block text-xs font-black">Relawan Rescuer</span>
                                        <span class="text-[10px] opacity-80 block">Tim Rescue & Evakuasi SAR</span>
                                    </div>
                                </div>
                            </button>
                            <button
                                type="button"
                                @click="ctaForm.volunteer_option = 'Relawan Donor Darah'"
                                class="p-3 rounded-2xl border text-left transition-all"
                                :class="ctaForm.volunteer_option === 'Relawan Donor Darah' ? 'bg-rose-500/10 border-rose-500 text-rose-600 dark:text-rose-400 font-bold shadow-sm' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'"
                            >
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg">🩸</span>
                                    <div>
                                        <span class="block text-xs font-black">Relawan Donor Darah</span>
                                        <span class="text-[10px] opacity-80 block">Pendonor Darah Siaga 24/7</span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor WA / Telepon</label>
                            <input 
                                v-model="ctaForm.phone" 
                                type="text" 
                                required 
                                placeholder="+62 812..." 
                                class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" 
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Akun (Password)</label>
                            <input 
                                v-model="ctaForm.password" 
                                type="password" 
                                required 
                                minlength="6"
                                placeholder="•••••••• (Min 6 Karakter)" 
                                class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5" 
                            />
                        </div>
                    </div>

                    <!-- Blood type field -->
                    <div v-if="ctaModalType === 'relawan'">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Golongan Darah</label>
                        <select 
                            v-model="ctaForm.blood_type" 
                            class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm py-2.5"
                        >
                            <option value="A">Golongan Darah A</option>
                            <option value="B">Golongan Darah B</option>
                            <option value="AB">Golongan Darah AB</option>
                            <option value="O">Golongan Darah O</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan / Keterangan</label>
                        <textarea 
                            v-model="ctaForm.notes" 
                            rows="2" 
                            placeholder="Sebutkan keahlian SAR, riwayat donasi, atau kualifikasi..." 
                            class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-orange-500 focus:ring-orange-500 text-sm p-2.5"
                        ></textarea>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-3.5 text-white font-bold rounded-xl shadow-lg active:scale-98 transition-all text-xs sm:text-sm flex items-center justify-center space-x-2"
                        :class="[
                            ctaModalType === 'mitra' ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20' : '',
                            ctaModalType === 'donatur' ? 'bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-orange-500/25' : '',
                            ctaModalType === 'relawan' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/20' : ''
                        ]"
                    >
                        <span>Kirim Pendaftaran</span>
                    </button>
                </form>

                <!-- Quick Role Login Shortcut Footer inside Modal -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-2">Sudah memiliki akun terdaftar?</span>
                    <Link 
                        :href="route('login')" 
                        class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-orange-500 font-bold text-xs transition"
                    >
                        <span>⚡ Masuk Langsung Halaman Role Login</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
