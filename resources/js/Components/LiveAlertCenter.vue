<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const isOpen = ref(false);
const activeTab = ref('all'); // 'all', 'sar', 'disaster', 'others'
const isSoundEnabled = ref(false);
const isLoading = ref(false);
const lastUpdated = ref('');
const isBannerDismissed = ref(false);

const summary = ref({
    total_alerts: 0,
    total_critical: 0,
    active_sar_count: 0,
    active_disaster_count: 0,
    critical_logistics_count: 0,
    recent_donations_count: 0,
});

const headlineAlert = ref(null);
const alertData = ref({
    sar_operations: [],
    disaster_events: [],
    critical_logistics: [],
    recent_donations: [],
});

let pollTimer = null;
let lastKnownCriticalCount = 0;

// Web Audio API Emergency Synthesizer
const playAlertChime = (isCritical = false) => {
    if (!isSoundEnabled.value) return;
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();

        if (isCritical) {
            // Dual Emergency Beep for Critical SAR / Disaster
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.setValueAtTime(1174.66, ctx.currentTime + 0.15); // D6
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.35);
        } else {
            // Soft Harmonic Chime
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
            osc.frequency.exponentialRampToValueAtTime(783.99, ctx.currentTime + 0.2); // G5
            gain.gain.setValueAtTime(0.08, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
        }
    } catch (e) {
        console.warn('Audio Context trigger failed:', e);
    }
};

const toggleSound = () => {
    isSoundEnabled.value = !isSoundEnabled.value;
    localStorage.setItem('mkt_alert_sound', isSoundEnabled.value ? 'true' : 'false');
    if (isSoundEnabled.value) {
        playAlertChime(true);
    }
};

const fetchLiveAlerts = async () => {
    try {
        isLoading.value = true;
        const res = await fetch(route('alerts.live'), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (res.ok) {
            const result = await res.json();
            if (result.success) {
                summary.value = result.summary || summary.value;
                headlineAlert.value = result.headline_alert || null;
                alertData.value = result.data || alertData.value;
                lastUpdated.value = result.formatted_time || 'Baru saja';

                // If new critical alerts arrived, trigger sound chime
                if (summary.value.total_critical > lastKnownCriticalCount && lastKnownCriticalCount !== 0) {
                    playAlertChime(true);
                }
                lastKnownCriticalCount = summary.value.total_critical;
            }
        }
    } catch (err) {
        console.error('Failed to sync live alerts:', err);
    } finally {
        isLoading.value = false;
    }
};

const handleVisibilityChange = () => {
    if (document.hidden) {
        if (pollTimer) clearInterval(pollTimer);
    } else {
        fetchLiveAlerts();
        pollTimer = setInterval(fetchLiveAlerts, 25000);
    }
};

onMounted(() => {
    // Restore sound preference
    isSoundEnabled.value = localStorage.getItem('mkt_alert_sound') === 'true';

    fetchLiveAlerts();
    pollTimer = setInterval(fetchLiveAlerts, 25000);
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});

// Computed list based on active tab
const filteredList = computed(() => {
    if (activeTab.value === 'sar') {
        return alertData.value.sar_operations || [];
    }
    if (activeTab.value === 'disaster') {
        return alertData.value.disaster_events || [];
    }
    if (activeTab.value === 'others') {
        return [
            ...(alertData.value.critical_logistics || []),
            ...(alertData.value.recent_donations || [])
        ];
    }
    // 'all' tab combines sorted
    return [
        ...(alertData.value.sar_operations || []),
        ...(alertData.value.disaster_events || []),
        ...(alertData.value.critical_logistics || []),
        ...(alertData.value.recent_donations || [])
    ];
});
</script>

<template>
    <div class="relative">
        <!-- TOP EMERGENCY BANNER TICKER (Only when critical SAR / Disaster is active) -->
        <Teleport to="body">
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-full opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-full opacity-0"
            >
                <div
                    v-if="headlineAlert && !isBannerDismissed"
                    class="fixed top-0 inset-x-0 z-50 bg-gradient-to-r from-red-600 via-rose-600 to-orange-600 text-white shadow-lg text-xs py-2 px-4 flex items-center justify-between border-b border-white/20 print:hidden"
                >
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <span class="flex h-2.5 w-2.5 relative shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                        </span>
                        <span class="font-extrabold uppercase px-2 py-0.5 rounded bg-black/25 text-[10px] tracking-wider shrink-0">
                            {{ headlineAlert.badge }}
                        </span>
                        <div class="truncate font-medium flex items-center space-x-2">
                            <span class="font-bold text-white truncate">{{ headlineAlert.title }}</span>
                            <span class="hidden md:inline text-white/80">• {{ headlineAlert.details }}</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 shrink-0 ml-3">
                        <Link
                            :href="headlineAlert.url"
                            class="px-2.5 py-1 rounded-lg bg-white text-red-700 font-bold hover:bg-white/90 transition shadow-sm text-[11px]"
                        >
                            {{ headlineAlert.action_label }} &rarr;
                        </Link>
                        <button
                            @click="isBannerDismissed = true"
                            class="p-1 rounded text-white/80 hover:text-white hover:bg-white/10 transition"
                            title="Tutup Banner"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </transition>
        </Teleport>

        <!-- TOPBAR NOTIFICATION BELL BUTTON -->
        <button
            @click="isOpen = !isOpen"
            class="relative p-1.5 sm:p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50/50 dark:hover:bg-brand-950/20 focus:outline-none transition-all duration-150"
            aria-label="Pusat Peringatan & Notifikasi Siaga"
            title="Pusat Peringatan & Notifikasi Siaga"
        >
            <svg
                :class="[
                    summary.total_critical > 0 ? 'animate-bounce text-red-500' : '',
                    'w-5 h-5 transition-transform'
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>

            <!-- Pulsing Badge Indicator -->
            <span
                v-if="summary.total_alerts > 0"
                :class="[
                    summary.total_critical > 0
                        ? 'bg-red-500 text-white animate-pulse ring-2 ring-red-300 dark:ring-red-900'
                        : 'bg-brand-500 text-white',
                    'absolute -top-1 -right-1 text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow-sm'
                ]"
            >
                {{ summary.total_critical > 0 ? summary.total_critical : summary.total_alerts }}
            </span>
        </button>

        <!-- BACKDROP OVERLAY FOR DROPDOWN -->
        <div
            v-if="isOpen"
            @click="isOpen = false"
            class="fixed inset-0 z-40 bg-transparent"
        ></div>

        <!-- NOTIFICATION CENTER DROPDOWN PANEL -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95 -translate-y-2"
            enter-to-class="transform opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="transform opacity-100 scale-100 translate-y-0"
            leave-to-class="transform opacity-0 scale-95 -translate-y-2"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2.5 w-[330px] sm:w-[420px] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-2xl z-50 overflow-hidden flex flex-col max-h-[85vh]"
            >
                <!-- PANEL HEADER -->
                <div class="p-4 bg-gradient-to-r from-orange-500/10 via-amber-500/5 to-transparent border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-orange-500/15 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Pusdalops Live Alerts</h3>
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            </div>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                {{ lastUpdated ? lastUpdated : 'Sinkronisasi real-time...' }}
                            </p>
                        </div>
                    </div>

                    <!-- SOUND ALERT TOGGLE BUTTON -->
                    <div class="flex items-center space-x-1.5">
                        <button
                            @click="toggleSound"
                            :class="[
                                isSoundEnabled
                                    ? 'bg-orange-50 text-orange-600 dark:bg-orange-950/40 dark:text-orange-400 border border-orange-200 dark:border-orange-800'
                                    : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500',
                                'p-1.5 rounded-lg text-xs font-semibold flex items-center space-x-1 transition'
                            ]"
                            :title="isSoundEnabled ? 'Alarm Suara: Aktif (Klik untuk Mute)' : 'Alarm Suara: Mute (Klik untuk Aktifkan)'"
                        >
                            <svg v-if="isSoundEnabled" class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path>
                            </svg>
                            <span class="text-[10px] hidden sm:inline">{{ isSoundEnabled ? 'Alarm ON' : 'MUTE' }}</span>
                        </button>

                        <!-- Refresh Trigger -->
                        <button
                            @click="fetchLiveAlerts"
                            :disabled="isLoading"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                            title="Segarkan Data"
                        >
                            <svg :class="['w-4 h-4', isLoading ? 'animate-spin text-brand-500' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- CATEGORY TABS -->
                <div class="px-3 pt-2 pb-1 border-b border-gray-100 dark:border-gray-800 flex space-x-1 overflow-x-auto bg-gray-50/50 dark:bg-gray-900/50">
                    <button
                        @click="activeTab = 'all'"
                        :class="[
                            activeTab === 'all'
                                ? 'bg-brand-500 text-white font-bold shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-200/60 dark:hover:bg-gray-800',
                            'px-2.5 py-1 rounded-lg text-xs transition shrink-0 flex items-center space-x-1'
                        ]"
                    >
                        <span>Semua</span>
                        <span class="text-[10px] px-1 rounded-full bg-white/20 ml-1">{{ summary.total_alerts }}</span>
                    </button>

                    <button
                        @click="activeTab = 'sar'"
                        :class="[
                            activeTab === 'sar'
                                ? 'bg-rose-600 text-white font-bold shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-200/60 dark:hover:bg-gray-800',
                            'px-2.5 py-1 rounded-lg text-xs transition shrink-0 flex items-center space-x-1'
                        ]"
                    >
                        <span>Operasi SAR</span>
                        <span v-if="summary.active_sar_count > 0" class="text-[10px] px-1 rounded-full bg-rose-500 text-white ml-1">
                            {{ summary.active_sar_count }}
                        </span>
                    </button>

                    <button
                        @click="activeTab = 'disaster'"
                        :class="[
                            activeTab === 'disaster'
                                ? 'bg-amber-600 text-white font-bold shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-200/60 dark:hover:bg-gray-800',
                            'px-2.5 py-1 rounded-lg text-xs transition shrink-0 flex items-center space-x-1'
                        ]"
                    >
                        <span>Bencana</span>
                        <span v-if="summary.active_disaster_count > 0" class="text-[10px] px-1 rounded-full bg-amber-500 text-white ml-1">
                            {{ summary.active_disaster_count }}
                        </span>
                    </button>

                    <button
                        @click="activeTab = 'others'"
                        :class="[
                            activeTab === 'others'
                                ? 'bg-blue-600 text-white font-bold shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-200/60 dark:hover:bg-gray-800',
                            'px-2.5 py-1 rounded-lg text-xs transition shrink-0 flex items-center space-x-1'
                        ]"
                    >
                        <span>Logistik & Donasi</span>
                    </button>
                </div>

                <!-- ALERTS FEED LIST -->
                <div class="overflow-y-auto max-h-[380px] p-2 space-y-2 divide-y divide-gray-50 dark:divide-gray-800/40">
                    <div v-if="filteredList.length === 0" class="py-8 text-center text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-xs font-semibold">Tidak ada peringatan aktif saat ini</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Seluruh status posko & logistik terkendali.</p>
                    </div>

                    <template v-for="(item, idx) in filteredList" :key="item.type + '-' + (item.id || idx)">
                        <!-- SAR OPERATION ALERT ITEM -->
                        <Link
                            v-if="item.type === 'sar_operation'"
                            :href="item.url"
                            @click="isOpen = false"
                            class="block p-2.5 rounded-xl hover:bg-rose-50/50 dark:hover:bg-rose-950/20 transition-all border border-transparent hover:border-rose-200 dark:hover:border-rose-900/50"
                        >
                            <div class="flex items-start space-x-2.5">
                                <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300">
                                            {{ item.code }} • {{ item.severity }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">{{ item.created_at }}</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white mt-1 truncate">
                                        {{ item.title }}
                                    </h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                        📍 {{ item.location }} • Komandan: {{ item.commander }} ({{ item.personnel_count }} Pers)
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <!-- DISASTER EVENT ALERT ITEM -->
                        <Link
                            v-else-if="item.type === 'disaster_event'"
                            :href="item.url"
                            @click="isOpen = false"
                            class="block p-2.5 rounded-xl hover:bg-amber-50/50 dark:hover:bg-amber-950/20 transition-all border border-transparent hover:border-amber-200 dark:hover:border-amber-900/50"
                        >
                            <div class="flex items-start space-x-2.5">
                                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                            {{ item.category }} • {{ item.status }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">{{ item.created_at }}</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white mt-1 truncate">
                                        {{ item.title }}
                                    </h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                        📍 {{ item.location }} • Korban: {{ item.victim_count }} Jiwa
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <!-- LOGISTIC STOCK LOW ITEM -->
                        <Link
                            v-else-if="item.type === 'logistic_alert'"
                            :href="item.url"
                            @click="isOpen = false"
                            class="block p-2.5 rounded-xl hover:bg-orange-50/50 dark:hover:bg-orange-950/20 transition-all border border-transparent hover:border-orange-200 dark:hover:border-orange-900/50"
                        >
                            <div class="flex items-start space-x-2.5">
                                <div class="w-8 h-8 rounded-xl bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300">
                                            Logistik Kritis
                                        </span>
                                        <span class="text-[10px] text-gray-400">{{ item.created_at }}</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white mt-1 truncate">
                                        {{ item.title }}
                                    </h4>
                                    <p class="text-[11px] text-red-600 dark:text-red-400 font-semibold truncate mt-0.5">
                                        Sisa: {{ item.quantity }} {{ item.unit }} • Perlu Restock Segera
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <!-- RECENT DONATION ITEM -->
                        <Link
                            v-else-if="item.type === 'donation_received'"
                            :href="item.url"
                            @click="isOpen = false"
                            class="block p-2.5 rounded-xl hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 transition-all border border-transparent hover:border-emerald-200 dark:hover:border-emerald-900/50"
                        >
                            <div class="flex items-start space-x-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                            Donasi Publik
                                        </span>
                                        <span class="text-[10px] text-gray-400">{{ item.created_at }}</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white mt-1 truncate">
                                        Rp {{ Number(item.amount).toLocaleString('id-ID') }}
                                    </h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                        Dari: {{ item.donor_name }} via {{ item.payment_method }}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </template>
                </div>

                <!-- FOOTER QUICK ACTIONS -->
                <div class="p-3 bg-gray-50 dark:bg-gray-950/60 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <Link
                        :href="route('sar-operations.command-center')"
                        @click="isOpen = false"
                        class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline flex items-center space-x-1"
                    >
                        <span>Command Center Pusdalops</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </Link>

                    <Link
                        :href="route('disaster-map.index')"
                        @click="isOpen = false"
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                    >
                        Peta Bencana &rarr;
                    </Link>
                </div>
            </div>
        </transition>
    </div>
</template>
