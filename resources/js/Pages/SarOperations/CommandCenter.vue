<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    operations: Array,
    stats: Object,
});

const page = usePage();

// Live Digital Clock State
const currentTime = ref('');
const currentDate = ref('');
let clockTimer = null;

const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WITA';
    currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

// Selected Active Operation / Siaga Item for Specific Per-Item Focus
const selectedOpId = ref(props.operations && props.operations.length > 0 ? props.operations[0].id : null);

const selectedOp = computed(() => {
    if (!props.operations) return null;
    return props.operations.find(op => op.id === selectedOpId.value) || props.operations[0] || null;
});

// Fullscreen State
const isFullscreen = ref(false);
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
            isFullscreen.value = false;
        }
    }
};

// Leaflet Map state
const mapInstance = ref(null);
const markersGroup = ref(null);
const isLeafletLoaded = ref(false);

onMounted(() => {
    updateClock();
    clockTimer = setInterval(updateClock, 1000);

    if (window.L) {
        isLeafletLoaded.value = true;
        initMap();
    } else {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => {
            isLeafletLoaded.value = true;
            initMap();
        };
        document.head.appendChild(script);
    }
});

onUnmounted(() => {
    if (clockTimer) clearInterval(clockTimer);
    if (mapInstance.value) {
        mapInstance.value.remove();
    }
});

const initMap = () => {
    nextTick(() => {
        if (!window.L || !selectedOp.value) return;

        const defaultLat = parseFloat(selectedOp.value.latitude) || -5.147665;
        const defaultLng = parseFloat(selectedOp.value.longitude) || 119.432731;

        const map = window.L.map('command-map-container').setView([defaultLat, defaultLng], 12);

        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors | Command Center Pusdalops 727',
            maxZoom: 18
        }).addTo(map);

        mapInstance.value = map;
        renderSelectedItemMap();
    });
};

const renderSelectedItemMap = () => {
    if (!mapInstance.value || !selectedOp.value || !window.L) return;

    // Clear previous markers
    if (markersGroup.value) {
        mapInstance.value.removeLayer(markersGroup.value);
    }

    const group = window.L.featureGroup();
    const op = selectedOp.value;
    const lat = parseFloat(op.latitude) || -5.147665;
    const lng = parseFloat(op.longitude) || 119.432731;
    const mainColor = op.type === 'Operasi SAR' ? '#e11d48' : '#f59e0b';

    // Main Incident Radar Marker
    const mainIcon = window.L.divIcon({
        className: 'command-main-marker',
        html: `
            <div style="position: relative;">
                <div style="position: absolute; width: 38px; height: 38px; background-color: ${mainColor}; opacity: 0.35; border-radius: 50%; animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
                <div style="background-color: ${mainColor}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 16px rgba(0,0,0,0.8); display: flex; items-center; justify-content: center;">
                    <div style="background-color: white; width: 10px; height: 10px; border-radius: 50%;"></div>
                </div>
            </div>
        `,
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    const mainMarker = window.L.marker([lat, lng], { icon: mainIcon }).addTo(group);
    mainMarker.bindPopup(`
        <div style="font-family: sans-serif; font-size: 12px; padding: 4px; color: #0f172a;">
            <strong style="color: ${mainColor}; font-size: 13px;">📍 LOKASI UTAMA GIAT: ${op.code}</strong>
            <p style="margin: 3px 0; font-weight: bold;">${op.title}</p>
            <p style="margin: 2px 0;">SMC: <strong>${op.commander_name || 'Danru MKT'}</strong></p>
            <p style="margin: 2px 0;">Status: <strong>${op.status} (${op.severity_level})</strong></p>
        </div>
    `).openPopup();

    // Coverage Radius Circle around main Incident
    window.L.circle([lat, lng], {
        color: mainColor,
        fillColor: mainColor,
        fillOpacity: 0.1,
        radius: 3000 // 3 KM radius
    }).addTo(group);

    // Plot Potensi SAR Team Pins specific to this item
    if (op.participations && op.participations.length > 0) {
        op.participations.forEach(part => {
            if (part.latitude && part.longitude) {
                const teamIcon = window.L.divIcon({
                    className: 'command-team-marker',
                    html: `
                        <div style="background-color: #0284c7; width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.6); display: flex; items-center; justify-content: center;">
                            <span style="color: white; font-size: 10px; font-weight: bold;">T</span>
                        </div>
                    `,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const teamMarker = window.L.marker([parseFloat(part.latitude), parseFloat(part.longitude)], { icon: teamIcon }).addTo(group);
                teamMarker.bindPopup(`
                    <div style="font-family: sans-serif; font-size: 11px; padding: 4px; color: #0f172a;">
                        <strong style="color: #0284c7; font-size: 12px;">👥 ${part.organization_name}</strong>
                        <p style="margin: 2px 0;">Danru: <strong>${part.commander_name}</strong> (${part.personnel_count} Personel)</p>
                        <p style="margin: 2px 0;">Status: <strong>${part.status}</strong></p>
                        <p style="margin: 2px 0;">ALUT: ${part.resources_deployed || '-'}</p>
                    </div>
                `);
            }
        });
    }

    group.addTo(mapInstance.value);
    markersGroup.value = group;

    mapInstance.value.flyTo([lat, lng], 12.5, {
        duration: 1.2
    });
};

watch(selectedOpId, () => {
    renderSelectedItemMap();
});

// Computed Specific Item Mobilization Breakdown
const itemMobilizationStats = computed(() => {
    if (!selectedOp.value) return { active: 0, onWay: 0, prep: 0, demobilized: 0 };

    let active = 0;
    let onWay = 0;
    let prep = 0;
    let demobilized = 0;

    const parts = selectedOp.value.participations || [];
    parts.forEach(p => {
        const s = p.status || '';
        const count = p.personnel_count || 0;
        if (s === 'Aktif Operasi Evakuasi') active += count;
        else if (s === 'Dalam Perjalanan') onWay += count;
        else if (s === 'Persiapan Mobilisasi') prep += count;
        else demobilized += count;
    });

    return { active, onWay, prep, demobilized };
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Command Center Pusdalops SAR 727 - Pemantauan Spesifik Giat" />

    <AuthenticatedLayout>
        <template #header>
            <span>Command Center Pusdalops SAR</span>
        </template>

        <!-- COMMAND CENTER HEADER BAR -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl mb-6 text-white relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center shrink-0 shadow-lg shadow-rose-600/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                            <h1 class="text-xl font-black tracking-tight text-white uppercase">Pusdalops Command Center SAR 727</h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                REAL-TIME ITEM MONITORING
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Pemantauan Spesifik & Taktis Per Kegiatan Operasi Respon Musibah / Siaga Kesiapsiagaan SAR
                        </p>
                    </div>
                </div>

                <!-- Clock & Screen Toggle Controls -->
                <div class="flex items-center space-x-4 shrink-0">
                    <div class="bg-slate-800/80 border border-slate-700/60 px-4 py-2 rounded-2xl text-right">
                        <span class="text-xs font-mono font-bold text-amber-400 block tracking-widest">{{ currentTime }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ currentDate }}</span>
                    </div>

                    <button
                        @click="toggleFullscreen"
                        class="p-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-2xl text-slate-300 hover:text-white transition-all shadow-md active:scale-95"
                        title="Mode Layar Penuh (Fullscreen Display)"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- PER-ITEM QUICK SELECTOR CAROUSEL BAR -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-xl mb-6 text-white">
            <div class="flex items-center justify-between mb-3 px-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center space-x-2">
                    <span>🎯 Pilih Kegiatan Operasi / Siaga SAR untuk Dipantau Spesifik:</span>
                </span>
                <span class="text-xs font-bold text-amber-400">
                    Total {{ operations.length }} Item Kegiatan Berlangsung
                </span>
            </div>

            <div class="flex items-center space-x-3 overflow-x-auto pb-2 scrollbar-thin">
                <button
                    v-for="op in operations"
                    :key="op.id"
                    @click="selectedOpId = op.id"
                    :class="[
                        selectedOpId === op.id
                            ? 'bg-gradient-to-r from-rose-600 to-amber-600 text-white shadow-lg shadow-rose-600/30 border-rose-400 scale-[1.02]'
                            : 'bg-slate-800/80 hover:bg-slate-800 text-slate-300 border-slate-700/80',
                        'px-4 py-3 rounded-2xl border text-left shrink-0 transition-all min-w-[280px] max-w-[340px] flex flex-col justify-between'
                    ]"
                >
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span :class="[op.type === 'Operasi SAR' ? 'bg-rose-950/80 text-rose-300 border-rose-700' : 'bg-amber-950/80 text-amber-300 border-amber-700', 'px-2 py-0.5 rounded-full text-[9px] font-black uppercase border']">
                                {{ op.type }}
                            </span>
                            <span class="font-mono text-[10px] font-extrabold opacity-90">{{ op.code }}</span>
                        </div>
                        <h4 class="font-bold text-xs leading-snug line-clamp-1 text-white">{{ op.title }}</h4>
                        <p class="text-[10px] opacity-80 mt-0.5 truncate">📍 {{ op.location }}</p>
                    </div>

                    <div class="flex justify-between items-center text-[10px] font-semibold mt-2.5 pt-2 border-t border-white/10">
                        <span>Status: <strong>{{ op.status }}</strong></span>
                        <span class="font-bold text-amber-300">{{ op.personnel_count }} Personel</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- MAIN ITEM DETAILED Tactical MONITORING SECTION -->
        <div v-if="selectedOp" class="space-y-6 mb-8">
            
            <!-- ITEM HEADER BANNER & METRICS FOR SELECTED GIAT -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 border border-slate-800 rounded-3xl p-6 shadow-2xl text-white">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-slate-800">
                    <div>
                        <div class="flex items-center space-x-2.5 mb-2">
                            <span :class="[selectedOp.type === 'Operasi SAR' ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white', 'px-3 py-1 rounded-full text-xs font-black uppercase shadow-sm']">
                                {{ selectedOp.type }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                Kode: {{ selectedOp.code }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">
                                {{ selectedOp.severity_level }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                {{ selectedOp.status }}
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-white leading-tight">{{ selectedOp.title }}</h2>
                        <p class="text-xs text-slate-400 mt-1">📍 Lokasi Posko Utama: <strong>{{ selectedOp.location }}</strong> (GPS: {{ selectedOp.latitude }}, {{ selectedOp.longitude }}) | Tanggal Giat: {{ formatDate(selectedOp.start_date) }}</p>
                    </div>

                    <div class="flex items-center space-x-3 shrink-0">
                        <div class="bg-slate-800/90 p-3 rounded-2xl border border-slate-700/70 text-center min-w-[130px]">
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">SMC / Komandan</span>
                            <span class="text-xs font-black text-amber-400 block mt-0.5 truncate">{{ selectedOp.commander_name || 'Danru MKT' }}</span>
                        </div>
                        <div class="bg-slate-800/90 p-3 rounded-2xl border border-slate-700/70 text-center min-w-[130px]">
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Personel Rescue</span>
                            <span class="text-xl font-black text-sky-400 block mt-0.5">{{ selectedOp.personnel_count }} Org</span>
                        </div>
                    </div>
                </div>

                <!-- SPECIFIC ITEM METRICS METERS (VICTIMS & MOBILIZATION STATUS) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 pt-5 text-center">
                    <!-- Victis Stats -->
                    <div class="bg-emerald-950/40 border border-emerald-800/50 p-3 rounded-2xl">
                        <span class="text-[10px] font-bold text-emerald-400 uppercase block">Selamat</span>
                        <span class="text-xl font-black text-emerald-400 mt-0.5 block">{{ selectedOp.victims_saved }} Jiwa</span>
                    </div>

                    <div class="bg-amber-950/40 border border-amber-800/50 p-3 rounded-2xl">
                        <span class="text-[10px] font-bold text-amber-400 uppercase block">Luka-Luka</span>
                        <span class="text-xl font-black text-amber-400 mt-0.5 block">{{ selectedOp.victims_injured }} Jiwa</span>
                    </div>

                    <div class="bg-rose-950/40 border border-rose-800/50 p-3 rounded-2xl">
                        <span class="text-[10px] font-bold text-rose-400 uppercase block">Meninggal</span>
                        <span class="text-xl font-black text-rose-400 mt-0.5 block">{{ selectedOp.victims_deceased }} Jiwa</span>
                    </div>

                    <div class="bg-slate-800/80 border border-slate-700/60 p-3 rounded-2xl">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Dalam Pencarian</span>
                        <span class="text-xl font-black text-slate-200 mt-0.5 block">{{ selectedOp.victims_missing }} Jiwa</span>
                    </div>

                    <!-- Mobilization Breakdown -->
                    <div class="bg-rose-500/10 border border-rose-500/30 p-3 rounded-2xl">
                        <span class="text-[10px] font-bold text-rose-300 uppercase block">🟢 Evakuasi Lapangan</span>
                        <span class="text-xl font-black text-rose-400 mt-0.5 block">{{ itemMobilizationStats.active }} Org</span>
                    </div>

                    <div class="bg-amber-500/10 border border-amber-500/30 p-3 rounded-2xl">
                        <span class="text-[10px] font-bold text-amber-300 uppercase block">🚚 Dalam Perjalanan</span>
                        <span class="text-xl font-black text-amber-400 mt-0.5 block">{{ itemMobilizationStats.onWay }} Org</span>
                    </div>

                    <div class="bg-sky-500/10 border border-sky-500/30 p-3 rounded-2xl">
                        <span class="text-[10px] font-bold text-sky-300 uppercase block">🟡 Persiapan Standby</span>
                        <span class="text-xl font-black text-sky-400 mt-0.5 block">{{ itemMobilizationStats.prep }} Org</span>
                    </div>

                    <div class="bg-teal-500/10 border border-teal-500/30 p-3 rounded-2xl">
                        <span class="text-[10px] font-bold text-teal-300 uppercase block">🏛️ Tim Potensi SAR</span>
                        <span class="text-xl font-black text-teal-400 mt-0.5 block">{{ selectedOp.participations ? selectedOp.participations.length : 0 }} Tim</span>
                    </div>
                </div>
            </div>

            <!-- TWO COLUMN TACTICAL LAYOUT FOR SELECTED GIAT (ITEM MAP & POTENSI SAR DEPLOYMENT DETAILS) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- LEFT COLUMN: ITEM SPECIFIC TACTICAL LEAFLET MAP (7 COLS) -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl overflow-hidden text-white space-y-3">
                        <div class="flex items-center justify-between px-1">
                            <span class="font-bold text-sm text-white flex items-center space-x-2">
                                <span>🗺️ Peta Taktis & Radius Penyisiran: {{ selectedOp.code }}</span>
                            </span>
                            <span class="text-xs text-emerald-400 font-mono font-bold">
                                Live Coordinates Active
                            </span>
                        </div>

                        <!-- LEAFLET MAP CONTAINER -->
                        <div class="rounded-2xl overflow-hidden border border-slate-800 relative">
                            <div id="command-map-container" class="h-[440px] w-full bg-slate-950 z-10"></div>
                            <div class="bg-slate-950 text-slate-300 p-2.5 text-[11px] flex justify-between items-center px-4 border-t border-slate-800">
                                <span>📍 Pulsing Marker Merah: Titik Musibah Utama | Marker Biru: Posko Keberangkatan Tim</span>
                                <a :href="`https://www.google.com/maps?q=${selectedOp.latitude},${selectedOp.longitude}`" target="_blank" class="text-amber-400 underline font-bold">Buka Google Maps ↗</a>
                            </div>
                        </div>

                        <!-- Equipment & Teams Deployed Summary for Selected Item -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div class="bg-slate-800/80 p-3 rounded-2xl border border-slate-700/60">
                                <span class="text-[10px] font-bold text-emerald-400 uppercase block">🟢 Tim Terlibat Lapangan (Deployed):</span>
                                <p class="text-xs font-medium text-slate-200 mt-1 leading-snug">{{ selectedOp.deployed_teams || 'Tim Rescue 727 MKT, BASARNAS, BPBD' }}</p>
                            </div>
                            <div class="bg-slate-800/80 p-3 rounded-2xl border border-slate-700/60">
                                <span class="text-[10px] font-bold text-amber-400 uppercase block">🟡 Tim Standby Persiapan Lapangan:</span>
                                <p class="text-xs font-medium text-slate-200 mt-1 leading-snug">{{ selectedOp.standby_teams || 'Tim Medis MKT, Relawan PMI' }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-800/60 p-3.5 rounded-2xl border border-slate-800 space-y-1 text-slate-300">
                            <span class="text-[10px] font-bold text-sky-400 uppercase block">🛠️ ALUT & Peralatan Utama Dikerahkan:</span>
                            <p class="text-xs font-medium text-white leading-relaxed">{{ selectedOp.equipment_used || 'Perahu Karet RIB, Alkon, Drone Thermal, Kit P3K Darurat' }}</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: REGISTERED POTENSI SAR TEAMS SPECIFIC TO THIS ITEM (5 COLS) -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl text-white space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div>
                                <h3 class="font-bold text-sm text-white flex items-center space-x-2">
                                    <span>👥 Tim Potensi SAR Terdaftar di Giat Ini</span>
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Rincian Danru, personel, kontak HT, dan ALUT disumbangkan</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-black bg-sky-500/20 text-sky-300 border border-sky-500/30">
                                {{ selectedOp.participations ? selectedOp.participations.length : 0 }} Tim
                            </span>
                        </div>

                        <!-- Participations List -->
                        <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                            <div
                                v-for="part in selectedOp.participations"
                                :key="part.id"
                                class="bg-slate-800/70 border border-slate-700/70 rounded-2xl p-4 space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-sky-400 flex items-center space-x-1.5">
                                        <span>🏢</span>
                                        <span>{{ part.organization_name }}</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        {{ part.status }}
                                    </span>
                                </div>

                                <div class="bg-slate-950/60 p-2.5 rounded-xl space-y-1 text-[11px] text-slate-300">
                                    <p>👤 <strong>Danru / Korlap:</strong> {{ part.commander_name }} ({{ part.contact_number }})</p>
                                    <p>👥 <strong>Personel Diterjunkan:</strong> <strong class="text-sky-400">{{ part.personnel_count }} Orang</strong></p>
                                    <p v-if="part.departure_location">📍 <strong>Posko Keberangkatan:</strong> {{ part.departure_location }}</p>
                                </div>

                                <div v-if="part.resources_deployed" class="bg-emerald-950/30 p-2.5 rounded-xl border border-emerald-800/40 text-[11px]">
                                    <span class="text-[9px] font-bold text-emerald-400 uppercase block">🛠️ ALUT & Sumber Daya Dikerahkan:</span>
                                    <p class="text-slate-200 font-medium leading-snug mt-0.5">{{ part.resources_deployed }}</p>
                                </div>
                            </div>

                            <div v-if="!selectedOp.participations || selectedOp.participations.length === 0" class="text-center p-8 bg-slate-800/30 border border-dashed border-slate-700 rounded-2xl">
                                <p class="text-xs text-slate-400 italic">Belum ada registrasi pendaftaran tim Potensi SAR untuk giat ini.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Chronology / Description Note -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl text-white space-y-2 text-xs">
                        <h4 class="font-bold text-slate-200 uppercase text-[11px] tracking-wider">📝 Kronologi Musibah & Catatan Lapangan:</h4>
                        <p class="text-slate-300 leading-relaxed bg-slate-800/50 p-3 rounded-2xl border border-slate-800 font-medium">
                            {{ selectedOp.description || 'Tidak ada catatan kronologi musibah.' }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
