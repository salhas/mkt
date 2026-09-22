<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import QRCode from 'qrcode';

const props = defineProps({
    location: {
        type: String,
        default: 'Makassar',
    },
    docNumber: {
        type: String,
        default: '',
    },
    signer1Title: {
        type: String,
        default: '',
    },
    signer1Name: {
        type: String,
        default: '',
    },
    signer1Nip: {
        type: String,
        default: '',
    },
    signer2Title: {
        type: String,
        default: '',
    },
    signer2Name: {
        type: String,
        default: '',
    },
    signer2Nip: {
        type: String,
        default: '',
    },
});

const page = usePage();

const leaders = computed(() => page?.props?.organizationLeaders || {});

const chairmanName = computed(() => {
    return props.signer1Name || leaders.value?.chairman?.name || 'Salman Hasmin, ST';
});

const chairmanTitle = computed(() => {
    return props.signer1Title || 'Ketua Pengurus Yayasan MKT';
});

const chairmanNip = computed(() => {
    return props.signer1Nip || leaders.value?.chairman?.nip || 'NIP/KTA: MKT-PG-001';
});

const treasurerName = computed(() => {
    return props.signer2Name || leaders.value?.treasurer?.name || 'Sarif, ST';
});

const treasurerTitle = computed(() => {
    return props.signer2Title || leaders.value?.treasurer?.title || 'Bendahara Umum';
});

const treasurerNip = computed(() => {
    return props.signer2Nip || leaders.value?.treasurer?.nip || 'NIP/KTA: MKT-PG-003';
});

const todayDateFormatted = new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
});

// Dynamic QR Code Generation for Document Validation
const qrCodeDataUrl = ref('');

const validationCode = computed(() => {
    if (props.docNumber && props.docNumber !== 'Double-Entry Verified') {
        return props.docNumber;
    }
    const year = new Date().getFullYear();
    const month = String(new Date().getMonth() + 1).padStart(2, '0');
    return `MKT-VAL-${year}${month}-FIN88`;
});

const securityHash = computed(() => {
    return `MKT-FIN-${new Date().getFullYear()}-VERIFIED`;
});

const generateQr = async () => {
    try {
        const payload = `https://mkt.or.id/verify-report?doc=${encodeURIComponent(validationCode.value)}&auth=Salman+Hasmin&org=Yayasan+MKT`;
        qrCodeDataUrl.value = await QRCode.toDataURL(payload, {
            width: 160,
            margin: 1,
            color: {
                dark: '#0f172a',
                light: '#ffffff'
            }
        });
    } catch (err) {
        console.error('Failed to generate QR Code:', err);
    }
};

onMounted(() => {
    generateQr();
});

watch(() => props.docNumber, () => {
    generateQr();
});
</script>

<template>
    <div class="mt-8 pt-4 border-t border-slate-200 text-gray-900 break-inside-avoid">
        <!-- Date location header -->
        <div class="text-right text-[10px] text-gray-700 font-medium mb-4">
            {{ location }}, {{ todayDateFormatted }}
        </div>

        <!-- Two column signature grid -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs">
            <!-- Left Column: Mengetahui (Ketua Pengurus) -->
            <div>
                <p class="font-bold text-gray-800 text-[11px]">Mengetahui,</p>
                <p class="text-[10px] text-gray-600 mb-16">{{ chairmanTitle }}</p>
                <p class="font-bold text-gray-900 border-b border-gray-400 inline-block px-4 pb-0.5 min-w-[180px]">
                    ( {{ chairmanName }} )
                </p>
                <p class="text-[9px] text-gray-500 mt-1">{{ chairmanNip }}</p>
            </div>

            <!-- Right Column: Dibuat Oleh (Bendahara) -->
            <div>
                <p class="font-bold text-gray-800 text-[11px]">Dibuat & Divalidasi Oleh,</p>
                <p class="text-[10px] text-gray-600 mb-16">{{ treasurerTitle }}</p>
                <p class="font-bold text-gray-900 border-b border-gray-400 inline-block px-4 pb-0.5 min-w-[180px]">
                    ( {{ treasurerName }} )
                </p>
                <p class="text-[9px] text-gray-500 mt-1">{{ treasurerNip }}</p>
            </div>
        </div>

        <!-- Bottom Footer with QR Code Validation in Bottom-Left Corner -->
        <div class="mt-8 pt-4 border-t-2 border-slate-200 flex items-center justify-between gap-4 text-slate-800">
            <!-- Sudut Kiri Bawah: QR Code & Validasi Dokumen Digital -->
            <div class="flex items-center space-x-3 text-left">
                <!-- QR Code Box -->
                <div class="w-16 h-16 p-1 bg-white border border-slate-300 rounded-lg shrink-0 flex items-center justify-center shadow-xs">
                    <img 
                        v-if="qrCodeDataUrl" 
                        :src="qrCodeDataUrl" 
                        alt="QR Code Validasi Dokumen" 
                        class="w-full h-full object-contain"
                    />
                    <div v-else class="text-[8px] text-slate-400 font-mono">QR Code</div>
                </div>

                <!-- Validation Meta Information -->
                <div class="leading-tight">
                    <div class="flex items-center space-x-1.5 font-black text-[9.5px] text-slate-900 uppercase tracking-tight">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        <span>Dokumen Digital Sah & Terverifikasi</span>
                    </div>
                    <p class="font-mono text-[8.5px] text-amber-800 font-bold mt-0.5">
                        No. Validasi: {{ validationCode }}
                    </p>
                    <p class="text-[8px] text-slate-500 mt-0.5 max-w-[260px]">
                        Pindai QR Code untuk verifikasi keaslian dokumen resmi Yayasan MKT.
                    </p>
                </div>
            </div>

            <!-- Sudut Kanan Bawah: Security Stamp & Disclaimer -->
            <div class="text-right text-[8.5px] text-slate-500 leading-tight shrink-0">
                <p class="font-bold text-slate-700">Sistem Informasi Akuntansi Yayasan MKT</p>
                <p class="font-mono text-[8px] text-slate-600 mt-0.5">Security Hash: {{ securityHash }}</p>
                <p class="italic text-[7.5px] text-slate-400 mt-0.5">Dihasilkan secara terkomputerisasi tanpa stempel basah.</p>
            </div>
        </div>
    </div>
</template>
