<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
    bmkgData: {
        type: Object,
        default: () => ({}),
    },
});

// Map initialization state
const mapInstance = ref(null);
const markers = ref([]);
const bmkgMarkers = ref([]);
const isLeafletLoaded = ref(false);
const selectedShakemap = ref(null);
const isShakemapOpen = ref(false);
const activeTab = ref('all'); // 'all', 'operations', 'bmkg'

// Latest earthquake computed
const latestEarthquake = computed(() => props.bmkgData?.latest || null);
const bmkgEarthquakeList = computed(() => {
    const list = [];
    const seen = new Set();

    if (latestEarthquake.value && latestEarthquake.value.latitude) {
        seen.add(`${latestEarthquake.value.latitude},${latestEarthquake.value.longitude}`);
        list.push({ ...latestEarthquake.value, is_latest: true });
    }

    const felts = props.bmkgData?.felt_earthquakes || [];
    const recents = props.bmkgData?.recent_m5 || [];

    [...felts, ...recents].forEach(eq => {
        if (!eq || !eq.latitude || !eq.longitude) return;
        const key = `${eq.latitude},${eq.longitude}`;
        if (!seen.has(key)) {
            seen.add(key);
            list.push(eq);
        }
    });

    return list;
});

// Form and Modal state
const isModalOpen = ref(false);
const editingEvent = ref(null);

const form = useForm({
    title: '',
    category: 'Banjir',
    location: '',
    latitude: '',
    longitude: '',
    severity: 'Sedang',
    status: 'Siaga',
    description: '',
    rescue_team_leader: '',
    victim_count: 0,
    date_occurred: new Date().toISOString().split('T')[0],
});

// Load Leaflet CDN Assets
onMounted(() => {
    if (window.L) {
        isLeafletLoaded.value = true;
        initMap();
    } else {
        // CSS
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        // JS
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => {
            isLeafletLoaded.value = true;
            initMap();
        };
        document.head.appendChild(script);
    }
});

const initMap = () => {
    nextTick(() => {
        const initialLat = latestEarthquake.value?.latitude || -2.5489;
        const initialLon = latestEarthquake.value?.longitude || 118.0149;
        const initialZoom = latestEarthquake.value ? 6 : 5;

        // Centered on Indonesia or Latest Earthquake
        const map = window.L.map('map-container').setView([initialLat, initialLon], initialZoom);
        mapInstance.value = map;

        // Dark/Light tile layers based on html dark class
        const isDark = document.documentElement.classList.contains('dark');
        const tileUrl = isDark 
            ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' 
            : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        const attribution = isDark
            ? '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

        window.L.tileLayer(tileUrl, { attribution }).addTo(map);

        // Map Click Listener to capture coordinates
        map.on('click', (e) => {
            form.latitude = parseFloat(e.latlng.lat.toFixed(6));
            form.longitude = parseFloat(e.latlng.lng.toFixed(6));
        });

        // Add markers
        renderMarkers();
    });
};

const renderMarkers = () => {
    if (!mapInstance.value || !window.L) return;

    // Clear old markers
    markers.value.forEach(m => mapInstance.value.removeLayer(m));
    markers.value = [];
    bmkgMarkers.value.forEach(m => mapInstance.value.removeLayer(m));
    bmkgMarkers.value = [];

    // 1. Render MKT Operation Events (if not filtered out)
    if (activeTab.value === 'all' || activeTab.value === 'operations') {
        const getCategoryColor = (severity) => {
            switch (severity) {
                case 'Darurat': return '#ef4444'; // Red
                case 'Tinggi': return '#f97316'; // Orange
                case 'Sedang': return '#f59e0b'; // Amber
                default: return '#10b981'; // Emerald
            }
        };

        props.events.forEach(event => {
            if (!event.latitude || !event.longitude) return;

            const color = getCategoryColor(event.severity);
            
            const marker = window.L.circleMarker([event.latitude, event.longitude], {
                radius: 10,
                fillColor: color,
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.85
            });

            const popupContent = `
                <div class="p-1 font-sans text-gray-800 dark:text-gray-100 max-w-[220px]">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] uppercase font-bold text-gray-400">${event.category}</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-800">${event.severity}</span>
                    </div>
                    <h4 class="font-bold text-sm text-gray-900 mb-1">${event.title}</h4>
                    <p class="text-xs text-gray-500 mb-1">📍 ${event.location}</p>
                    <div class="text-[11px] text-gray-600 mb-2">
                        <div>Pimpinan: <strong>${event.rescue_team_leader || '-'}</strong></div>
                        <div>Pengungsi: <strong>${event.victim_count} Jiwa</strong></div>
                    </div>
                    <p class="text-xs text-gray-500 line-clamp-2">${event.description || ''}</p>
                </div>
            `;

            marker.bindPopup(popupContent).addTo(mapInstance.value);
            markers.value.push(marker);
        });
    }

    // 2. Render Live BMKG Earthquakes (if not filtered out)
    if (activeTab.value === 'all' || activeTab.value === 'bmkg') {
        bmkgEarthquakeList.value.forEach((eq, idx) => {
            if (!eq.latitude || !eq.longitude) return;

            const isLatest = eq.is_latest || idx === 0;
            const mag = parseFloat(eq.magnitude) || 0;
            const magColor = mag >= 5.0 ? '#ef4444' : mag >= 4.0 ? '#f97316' : '#10b981';

            // Seismic wave circle
            const seismicCircle = window.L.circle([eq.latitude, eq.longitude], {
                radius: Math.min(Math.max(mag * 8000, 15000), 70000),
                color: magColor,
                fillColor: magColor,
                fillOpacity: isLatest ? 0.15 : 0.08,
                weight: isLatest ? 2 : 1,
            }).addTo(mapInstance.value);
            bmkgMarkers.value.push(seismicCircle);

            // Custom Icon / Circle Marker with Magnitude Number
            const eqIcon = window.L.divIcon({
                className: 'bmkg-marker-wrapper',
                html: `
                    <div style="
                        background-color: ${magColor};
                        color: #ffffff;
                        font-weight: 900;
                        font-size: 11px;
                        width: ${isLatest ? '34px' : '28px'};
                        height: ${isLatest ? '34px' : '28px'};
                        border-radius: 50%;
                        border: 2px solid #ffffff;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
                        ${isLatest ? 'animation: pulse 2s infinite;' : ''}
                    ">
                        ${mag.toFixed(1)}
                    </div>
                `,
                iconSize: [isLatest ? 34 : 28, isLatest ? 34 : 28],
                iconAnchor: [isLatest ? 17 : 14, isLatest ? 17 : 14],
            });

            const marker = window.L.marker([eq.latitude, eq.longitude], { icon: eqIcon });

            const popupContent = `
                <div class="p-2 font-sans text-gray-800 dark:text-gray-100 max-w-[260px]">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded ${isLatest ? 'bg-red-500 text-white' : 'bg-orange-100 text-orange-800 font-bold'}">
                            ${isLatest ? '🔴 GEMPA TERKINI BMKG' : 'GEMPA DIRASAKAN'}
                        </span>
                        <span class="text-[11px] font-black" style="color: ${magColor};">${mag.toFixed(1)} SR</span>
                    </div>
                    <h4 class="font-extrabold text-sm text-gray-900 leading-tight mb-1">${eq.region}</h4>
                    <div class="space-y-1 text-xs text-gray-600 my-2">
                        <div>🕒 Waktu: <strong>${eq.tanggal} ${eq.jam}</strong></div>
                        <div>🌊 Kedalaman: <strong>${eq.depth}</strong></div>
                        <div>⚠️ Potensi: <strong class="${eq.potential?.toLowerCase().includes('tidak') ? 'text-emerald-600' : 'text-red-600'}">${eq.potential || 'Tidak berpotensi tsunami'}</strong></div>
                        ${eq.felt ? `<div>📍 Dirasakan: <strong>${eq.felt}</strong></div>` : ''}
                    </div>
                    ${eq.shakemap_url ? `
                        <div class="mt-2 pt-2 border-t border-gray-100">
                            <a href="${eq.shakemap_url}" target="_blank" class="inline-flex items-center text-xs font-bold text-blue-600 hover:underline">
                                🗺️ Lihat Shakemap Peta Guncangan BMKG &rarr;
                            </a>
                        </div>
                    ` : ''}
                </div>
            `;

            marker.bindPopup(popupContent).addTo(mapInstance.value);
            bmkgMarkers.value.push(marker);
        });
    }
};

const setTab = (tab) => {
    activeTab.value = tab;
    renderMarkers();
};

const panToEvent = (event) => {
    if (mapInstance.value && event.latitude && event.longitude) {
        mapInstance.value.setView([event.latitude, event.longitude], 12, { animate: true });
        
        const idx = props.events.findIndex(e => e.id === event.id);
        if (idx !== -1 && markers.value[idx]) {
            markers.value[idx].openPopup();
        }
    }
};

const panToEarthquake = (eq) => {
    if (mapInstance.value && eq.latitude && eq.longitude) {
        mapInstance.value.setView([eq.latitude, eq.longitude], 8, { animate: true });
        
        const idx = bmkgEarthquakeList.value.findIndex(e => e.latitude === eq.latitude && e.longitude === eq.longitude);
        if (idx !== -1 && bmkgMarkers.value[idx * 2 + 1]) {
            bmkgMarkers.value[idx * 2 + 1].openPopup();
        }
    }
};

const openShakemapModal = (shakemapUrl) => {
    selectedShakemap.value = shakemapUrl;
    isShakemapOpen.value = true;
};

// CRUD handlers
const openAddModal = () => {
    editingEvent.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (event) => {
    editingEvent.value = event;
    form.title = event.title;
    form.category = event.category;
    form.location = event.location;
    form.latitude = event.latitude;
    form.longitude = event.longitude;
    form.severity = event.severity;
    form.status = event.status;
    form.description = event.description;
    form.rescue_team_leader = event.rescue_team_leader;
    form.victim_count = event.victim_count;
    form.date_occurred = event.date_occurred;
    form.clearErrors();
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingEvent.value) {
        form.patch(route('disaster-map.update', editingEvent.value.id), {
            onSuccess: () => {
                isModalOpen.value = false;
                renderMarkers();
            }
        });
    } else {
        form.post(route('disaster-map.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                renderMarkers();
            }
        });
    }
};

const deleteEvent = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus titik operasi ini?')) {
        router.delete(route('disaster-map.destroy', id), {
            onSuccess: () => renderMarkers()
        });
    }
};
</script>

<template>
    <Head title="Peta Operasi Tanggap Bencana & BMKG Live" />

    <AuthenticatedLayout>
        <template #header>
            <span>Peta Operasi Tanggap Bencana & BMKG Live</span>
        </template>

        <!-- Dedicated Page Header Section -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Peta Operasi & BMKG Real-Time</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200">
                            LIVE TEWS
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Integrasi GIS Pemetaan Bencana, Titik Operasi SAR MKT & Data Gempa Bumi BMKG</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1 border border-gray-200 dark:border-gray-700 text-xs">
                    <button
                        @click="setTab('all')"
                        :class="[activeTab === 'all' ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 font-bold shadow-sm' : 'text-gray-500', 'px-3 py-1.5 rounded-lg transition-all']"
                    >
                        Semua Layer
                    </button>
                    <button
                        @click="setTab('operations')"
                        :class="[activeTab === 'operations' ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 font-bold shadow-sm' : 'text-gray-500', 'px-3 py-1.5 rounded-lg transition-all']"
                    >
                        Operasi MKT ({{ events.length }})
                    </button>
                    <button
                        @click="setTab('bmkg')"
                        :class="[activeTab === 'bmkg' ? 'bg-white dark:bg-gray-900 text-red-600 dark:text-red-400 font-bold shadow-sm' : 'text-gray-500', 'px-3 py-1.5 rounded-lg transition-all']"
                    >
                        Gempa BMKG ({{ bmkgEarthquakeList.length }})
                    </button>
                </div>
                <button
                    @click="openAddModal"
                    class="px-3.5 py-2 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center space-x-1.5 shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>+ Titik Operasi</span>
                </button>
            </div>
        </div>

        <!-- Real-Time AutoGempa BMKG Alert Banner -->
        <div
            v-if="latestEarthquake"
            class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-red-500/15 via-orange-500/10 to-amber-500/10 border border-red-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm"
        >
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-xl bg-red-500 text-white font-black text-sm flex flex-col items-center justify-center shrink-0 shadow-md animate-pulse">
                    <span>{{ Number(latestEarthquake.magnitude).toFixed(1) }}</span>
                    <span class="text-[9px] font-normal leading-none">SR</span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-red-500 text-white">
                            🔴 GEMPABUMI TERBARU BMKG
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ latestEarthquake.tanggal }} {{ latestEarthquake.jam }}
                        </span>
                    </div>
                    <h3 class="font-extrabold text-sm text-gray-900 dark:text-white mt-1">
                        {{ latestEarthquake.region }}
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                        Kedalaman: <strong>{{ latestEarthquake.depth }}</strong> • Status: 
                        <strong :class="latestEarthquake.potential?.toLowerCase().includes('tidak') ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 font-bold'">
                            {{ latestEarthquake.potential || 'Tidak Berpotensi Tsunami' }}
                        </strong>
                        <span v-if="latestEarthquake.felt" class="ml-1 text-amber-600 dark:text-amber-400">
                            • Dirasakan: {{ latestEarthquake.felt }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-2 shrink-0 self-end md:self-center">
                <button
                    @click="panToEarthquake(latestEarthquake)"
                    class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center space-x-1"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Fokus Pusat Gempa</span>
                </button>
                <button
                    v-if="latestEarthquake.shakemap_url"
                    @click="openShakemapModal(latestEarthquake.shakemap_url)"
                    class="px-3 py-1.5 bg-white dark:bg-gray-800 hover:bg-gray-50 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 text-xs font-bold rounded-xl transition shadow-sm"
                >
                    🗺️ Shakemap
                </button>
            </div>
        </div>

        <!-- Main Workspace Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 h-[calc(100vh-16rem)] min-h-[500px]">
            
            <!-- Map Container (2 Columns) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden relative flex flex-col">
                <!-- Live Map -->
                <div id="map-container" class="flex-1 w-full h-full z-10"></div>
                
                <!-- Map Legend Overlay -->
                <div class="absolute bottom-4 left-4 z-20 bg-white/95 dark:bg-gray-950/95 backdrop-blur border border-gray-100 dark:border-gray-800 px-3 py-2 rounded-xl shadow-lg text-[10px] text-gray-600 dark:text-gray-300 space-y-1">
                    <div class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-[9px] mb-1">Legenda Peta:</div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-red-500 border border-white inline-block"></span>
                        <span>Gempa BMKG M &ge; 5.0 / Darurat</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-orange-500 border border-white inline-block"></span>
                        <span>Gempa BMKG M 4.0 - 4.9 / Tinggi</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500 border border-white inline-block"></span>
                        <span>Gempa Sedang / Siaga</span>
                    </div>
                </div>
            </div>

            <!-- Operations & BMKG List (1 Column) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm p-6 flex flex-col justify-between overflow-hidden">
                <div>
                    <!-- Tab Switcher Inside Sidebar -->
                    <div class="flex border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 justify-between items-center">
                        <div class="flex space-x-2">
                            <button
                                @click="setTab('operations')"
                                :class="[activeTab === 'operations' || activeTab === 'all' ? 'text-brand-600 dark:text-brand-400 font-bold border-b-2 border-brand-500' : 'text-gray-400', 'text-xs pb-1 transition']"
                            >
                                Titik Operasi ({{ events.length }})
                            </button>
                            <button
                                @click="setTab('bmkg')"
                                :class="[activeTab === 'bmkg' ? 'text-red-600 dark:text-red-400 font-bold border-b-2 border-red-500' : 'text-gray-400', 'text-xs pb-1 transition']"
                            >
                                Gempa BMKG ({{ bmkgEarthquakeList.length }})
                            </button>
                        </div>
                    </div>

                    <!-- Scroll area: MKT Operations List -->
                    <div v-if="activeTab === 'all' || activeTab === 'operations'" class="space-y-3 overflow-y-auto max-h-[calc(100vh-25rem)] pr-1 scrollbar-thin">
                        <div
                            v-for="e in events"
                            :key="e.id"
                            class="p-3.5 bg-gray-50/60 dark:bg-gray-800/40 hover:bg-brand-50/40 dark:hover:bg-gray-800 border border-gray-100 dark:border-gray-800/60 rounded-xl cursor-pointer transition-all duration-150 flex justify-between items-start"
                            @click="panToEvent(e)"
                        >
                            <div class="space-y-1 truncate pr-2">
                                <div class="flex items-center space-x-1.5">
                                    <span
                                        :class="[
                                            e.severity === 'Darurat' ? 'bg-red-500' :
                                            e.severity === 'Tinggi' ? 'bg-orange-500' :
                                            e.severity === 'Sedang' ? 'bg-amber-500' :
                                            'bg-emerald-500',
                                            'w-2 h-2 rounded-full shrink-0'
                                        ]"
                                    ></span>
                                    <h4 class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ e.title }}</h4>
                                </div>
                                <p class="text-[11px] text-gray-400 truncate">📍 {{ e.location }}</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-[9px] font-bold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded">
                                        {{ e.status }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ e.victim_count }} Jiwa
                                    </span>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="flex items-center space-x-1 shrink-0">
                                <button
                                    @click.stop="openEditModal(e)"
                                    class="p-1 rounded text-gray-400 hover:text-brand-500 hover:bg-white dark:hover:bg-gray-800"
                                    title="Edit"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <button
                                    @click.stop="deleteEvent(e.id)"
                                    class="p-1 rounded text-gray-400 hover:text-red-500 hover:bg-white dark:hover:bg-gray-800"
                                    title="Hapus"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <p v-if="events.length === 0" class="text-xs text-gray-400 italic text-center py-6">
                            Belum ada laporan titik operasi.
                        </p>
                    </div>

                    <!-- Scroll area: BMKG Earthquake List -->
                    <div v-if="activeTab === 'bmkg'" class="space-y-3 overflow-y-auto max-h-[calc(100vh-25rem)] pr-1 scrollbar-thin">
                        <div
                            v-for="(eq, index) in bmkgEarthquakeList"
                            :key="index"
                            class="p-3 bg-red-50/30 dark:bg-gray-800/40 hover:bg-red-50/70 dark:hover:bg-gray-800 border border-red-100 dark:border-gray-800 rounded-xl cursor-pointer transition-all duration-150"
                            @click="panToEarthquake(eq)"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-2">
                                    <span
                                        :class="[
                                            Number(eq.magnitude) >= 5.0 ? 'bg-red-500' : 'bg-orange-500',
                                            'text-white text-[10px] font-black px-1.5 py-0.5 rounded shadow-sm'
                                        ]"
                                    >
                                        {{ Number(eq.magnitude).toFixed(1) }} SR
                                    </span>
                                    <span class="text-[10px] font-bold text-gray-500">
                                        {{ eq.jam }}
                                    </span>
                                </div>
                                <span v-if="eq.is_latest" class="text-[9px] font-black text-red-600 bg-red-100 dark:bg-red-950/60 px-1.5 py-0.2 rounded">
                                    TERBARU
                                </span>
                            </div>
                            <h5 class="font-bold text-xs text-gray-900 dark:text-white mt-1.5 truncate">
                                {{ eq.region }}
                            </h5>
                            <div class="flex items-center justify-between text-[10px] text-gray-500 mt-1">
                                <span>Kedalaman: {{ eq.depth }}</span>
                                <span v-if="eq.felt" class="text-orange-600 font-medium truncate max-w-[120px]">
                                    {{ eq.felt }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shakemap Image Modal -->
        <div v-if="isShakemapOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden">
                <div class="h-14 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800">
                    <span class="font-bold text-gray-900 dark:text-white">
                        Peta Guncangan BMKG (Shakemap)
                    </span>
                    <button @click="isShakemapOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 text-center">
                    <img :src="selectedShakemap" alt="BMKG Shakemap" class="max-h-[480px] w-auto mx-auto rounded-xl shadow border" />
                </div>
            </div>
        </div>

        <!-- Add/Edit Operation Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden">
                <div class="h-14 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-brand-50/30 dark:bg-brand-950/10">
                    <span class="font-bold text-gray-900 dark:text-white">
                        {{ editingEvent ? 'Edit Titik Operasi Bencana' : 'Lapor Titik Operasi Baru' }}
                    </span>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Kejadian Bencana</label>
                        <input v-model="form.title" type="text" placeholder="Banjir Luapan Sungai / Gempa Lokal" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Kategori</label>
                            <select v-model="form.category" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required>
                                <option value="Banjir">Banjir</option>
                                <option value="Gempa Bumi">Gempa Bumi</option>
                                <option value="Tanah Longsor">Tanah Longsor</option>
                                <option value="Kebakaran Hutan">Kebakaran Hutan</option>
                                <option value="Angin Puting Beliung">Angin Puting Beliung</option>
                                <option value="Tsunami">Tsunami</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Lokasi Wilayah</label>
                            <input v-model="form.location" type="text" placeholder="Kecamatan, Kabupaten" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>

                    <!-- Coordinates Lat/Lng picker inputs -->
                    <div class="grid grid-cols-2 gap-4 p-3 bg-brand-50/20 dark:bg-brand-950/10 border border-brand-100 dark:border-brand-950/50 rounded-xl">
                        <div>
                            <label class="block text-[10px] font-bold text-brand-600 dark:text-brand-400 uppercase mb-1">Latitude</label>
                            <input v-model="form.latitude" type="number" step="any" placeholder="-5.1477" class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-brand-600 dark:text-brand-400 uppercase mb-1">Longitude</label>
                            <input v-model="form.longitude" type="number" step="any" placeholder="119.4327" class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tingkat Keparahan</label>
                            <select v-model="form.severity" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required>
                                <option value="Rendah">Rendah (Warna Hijau)</option>
                                <option value="Sedang">Sedang (Warna Kuning)</option>
                                <option value="Tinggi">Tinggi (Warna Oranye)</option>
                                <option value="Darurat">Darurat (Warna Merah)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status Penanganan</label>
                            <select v-model="form.status" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required>
                                <option value="Siaga">Siaga</option>
                                <option value="Evakuasi">Evakuasi</option>
                                <option value="Pemulihan">Pemulihan</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pimpinan Tim Rescue</label>
                            <input v-model="form.rescue_team_leader" type="text" placeholder="Nama koordinator relawan" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jumlah Pengungsi (Jiwa)</label>
                            <input v-model="form.victim_count" type="number" min="0" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal Terjadi</label>
                        <input v-model="form.date_occurred" type="date" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Keterangan / Laporan Situasi</label>
                        <textarea v-model="form.description" rows="2" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 rounded-xl text-sm font-semibold hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-sm font-semibold shadow-md shadow-brand-500/20">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Leaflet map styles overriding */
.leaflet-container {
    background-color: transparent !important;
}
.leaflet-popup-content-wrapper {
    background: rgba(255, 255, 255, 0.95) !important;
    border-radius: 1rem !important;
    border: 1px solid rgba(229, 231, 235, 0.5) !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}
.dark .leaflet-popup-content-wrapper {
    background: rgba(17, 24, 39, 0.95) !important;
    border: 1px solid rgba(31, 41, 55, 0.8) !important;
}
.leaflet-popup-tip {
    background: rgba(255, 255, 255, 0.95) !important;
}
.dark .leaflet-popup-tip {
    background: rgba(17, 24, 39, 0.95) !important;
}
.leaflet-bar {
    border: none !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}
.leaflet-bar a {
    background-color: #ffffff !important;
    color: #4b5563 !important;
    border: 1px solid #f3f4f6 !important;
    border-bottom: none !important;
}
.dark .leaflet-bar a {
    background-color: #111827 !important;
    color: #d1d5db !important;
    border: 1px solid #1f2937 !important;
    border-bottom: none !important;
}
.leaflet-bar a:hover {
    background-color: #f97316 !important;
    color: #ffffff !important;
}
.dark .leaflet-bar a:hover {
    background-color: #ea580c !important;
    color: #ffffff !important;
}
.leaflet-bar a:first-child {
    border-top-left-radius: 0.5rem !important;
    border-top-right-radius: 0.5rem !important;
}
.leaflet-bar a:last-child {
    border-bottom-left-radius: 0.5rem !important;
    border-bottom-right-radius: 0.5rem !important;
    border-bottom: 1px solid #f3f4f6 !important;
}
.dark .leaflet-bar a:last-child {
    border-bottom: 1px solid #1f2937 !important;
}
</style>
