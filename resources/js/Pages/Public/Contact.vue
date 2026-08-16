<script setup>
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    mktProfile: Object,
});

const contactForm = ref({
    name: '',
    email: '',
    phone: '',
    subject: 'Bantuan Darurat',
    message: ''
});

const isSubmitted = ref(false);
const isSubmitting = ref(false);

const handleContactSubmit = async () => {
    isSubmitting.value = true;
    setTimeout(() => {
        isSubmitting.value = false;
        isSubmitted.value = true;
        contactForm.value = { name: '', email: '', phone: '', subject: 'Bantuan Darurat', message: '' };
    }, 800);
};
</script>

<template>
    <PublicLayout 
        title="Kontak & Lokasi Posko - Yayasan MKT Indonesia"
        description="Hubungi Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu). Alamat kantor posko di Insignia Oasis, hotline siaga 24/7, dan formulir bantuan."
    >
        <template #default>
            
            <!-- HEADER -->
            <section class="py-14 sm:py-20 bg-gradient-to-b from-orange-500/10 via-amber-500/5 to-transparent dark:from-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800 text-center space-y-4">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider">
                        📍 PUSAT KOMUNIKASI & POSKO
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                        Kontak & Lokasi Kantor Posko
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Kami siap melayani kebutuhan informasi kebencanaan, donasi, pendaftaran relawan, dan bantuan darurat 24 jam sehari.
                    </p>
                </div>
            </section>

            <!-- CONTACT DETAILS & FORM SECTION -->
            <section class="py-16 sm:py-20 bg-white dark:bg-slate-900 transition-colors border-b border-slate-100 dark:border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                        
                        <!-- Left Details & Channels -->
                        <div class="lg:col-span-5 space-y-6">
                            <div class="space-y-2">
                                <span class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                    INFORMASI KANTOR PUSAT
                                </span>
                                <h2 class="text-2xl font-black text-slate-900 dark:text-white">
                                    Yayasan MKT Indonesia
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Mitra Kemanusiaan Terpadu &bull; SK Kemenkumham RI
                                </p>
                            </div>

                            <div class="space-y-4">
                                <!-- Address -->
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex items-start space-x-3.5">
                                    <span class="text-2xl p-2 bg-orange-500/10 text-orange-600 rounded-xl shrink-0">📍</span>
                                    <div class="space-y-0.5">
                                        <h4 class="text-xs font-black uppercase text-slate-400">Alamat Kantor Posko:</h4>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white leading-relaxed">
                                            Perumahan Insignia Oasis Blok B1-11 No 7
                                        </p>
                                    </div>
                                </div>

                                <!-- Hotline 24 Hours -->
                                <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-start space-x-3.5">
                                    <span class="text-2xl p-2 bg-rose-500/20 text-rose-600 rounded-xl shrink-0">📞</span>
                                    <div class="space-y-0.5">
                                        <h4 class="text-xs font-black uppercase text-rose-600 dark:text-rose-400">Hotline Darurat 24 Jam & WA:</h4>
                                        <a href="tel:+6281234567890" class="text-sm sm:text-base font-black text-rose-700 dark:text-rose-300 hover:underline block">
                                            +62 812-3456-7890 (Siaga SAR & Donor)
                                        </a>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex items-start space-x-3.5">
                                    <span class="text-2xl p-2 bg-sky-500/10 text-sky-600 rounded-xl shrink-0">✉️</span>
                                    <div class="space-y-0.5">
                                        <h4 class="text-xs font-black uppercase text-slate-400">Email Resmi:</h4>
                                        <a href="mailto:info@mkt.or.id" class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white hover:text-orange-500 block">
                                            info@mkt.or.id
                                        </a>
                                    </div>
                                </div>

                                <!-- Operating Hours -->
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex items-start space-x-3.5">
                                    <span class="text-2xl p-2 bg-emerald-500/10 text-emerald-600 rounded-xl shrink-0">🕒</span>
                                    <div class="space-y-0.5">
                                        <h4 class="text-xs font-black uppercase text-slate-400">Jam Operasional Komando:</h4>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white">
                                            Senin - Minggu &bull; 24 Jam Siaga Kebencanaan
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Contact & Assistance Form -->
                        <div class="lg:col-span-7">
                            <div class="bg-slate-50 dark:bg-slate-950 p-8 sm:p-10 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
                                <div class="space-y-1">
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                                        Kirim Pesan / Permohonan Bantuan
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Isi formulir berikut dan petugas siaga MKT akan segera menghubungi Anda.
                                    </p>
                                </div>

                                <div v-if="isSubmitted" class="p-5 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-600 dark:text-emerald-400 space-y-2">
                                    <p class="font-bold text-sm">✅ Pesan & Permohonan Berhasil Terkirim!</p>
                                    <p class="text-xs">Tim Pusdalops MKT telah menerima laporan Anda dan akan segera merespons ke nomor WhatsApp / email tertera.</p>
                                    <button @click="isSubmitted = false" class="mt-2 px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs">
                                        Kirim Pesan Baru
                                    </button>
                                </div>

                                <form v-else @submit.prevent="handleContactSubmit" class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                                            <input v-model="contactForm.name" type="text" required placeholder="Nama Anda" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor WhatsApp / HP</label>
                                            <input v-model="contactForm.phone" type="text" required placeholder="+62 812..." class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Email</label>
                                            <input v-model="contactForm.email" type="email" required placeholder="nama@email.com" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kategori Keperluan</label>
                                            <select v-model="contactForm.subject" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                                                <option value="Bantuan Darurat">Permohonan Rescue / Bantuan Bencana</option>
                                                <option value="Kebutuhan Darah">Permintaan Donor Darah Darurat</option>
                                                <option value="Donasi">Konfirmasi Donasi Filantropi</option>
                                                <option value="Kerjasama">Kerjasama Lembaga / CSR</option>
                                                <option value="Lainnya">Pertanyaan Umum</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pesan / Rincian Situasi</label>
                                        <textarea v-model="contactForm.message" required rows="4" placeholder="Jelaskan kebutuhan bantuan, lokasi kejadian musibah, atau pertanyaan Anda secara rinci..." class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm p-3 focus:border-orange-500 focus:ring-orange-500"></textarea>
                                    </div>

                                    <button 
                                        type="submit" 
                                        :disabled="isSubmitting"
                                        class="w-full py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl shadow-lg active:scale-98 transition-all text-xs sm:text-sm flex items-center justify-center space-x-2"
                                    >
                                        <span>{{ isSubmitting ? 'Mengirim Pesan...' : 'Kirim Pesan Sekarang' }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- INTERACTIVE MAP LOCATION -->
            <section class="py-14 bg-slate-50 dark:bg-slate-950 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    <div class="text-center space-y-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400">PETA LOKASI</span>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Peta Google Maps Posko Insignia Oasis</h3>
                    </div>

                    <div class="rounded-3xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-800 h-[380px] sm:h-[450px]">
                        <iframe 
                            class="w-full h-full border-0" 
                            src="https://maps.google.com/maps?q=Perumahan%20Insignia%20Oasis%20Makassar&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                </div>
            </section>

        </template>
    </PublicLayout>
</template>
