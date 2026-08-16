<script setup>
import { ref, onMounted, nextTick } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    events: Array,
});

// Map initialization state
const mapInstance = ref(null);
const markers = ref([]);
const isLeafletLoaded = ref(false);

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
        // Centered on Indonesia
        const map = window.L.map('map-container').setView([-2.5489, 118.0149], 5);
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

    // Category Color Mapping for Circle Markers
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
        
        // Leaflet custom circle marker
        const marker = window.L.circleMarker([event.latitude, event.longitude], {
            radius: 10,
            fillColor: color,
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        });

        // Popup Content
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
};

const panToEvent = (event) => {
    if (mapInstance.value && event.latitude && event.longitude) {
        mapInstance.value.setView([event.latitude, event.longitude], 12, { animate: true });
        
        // Find corresponding marker and open popup
        const idx = props.events.findIndex(e => e.id === event.id);
        if (idx !== -1 && markers.value[idx]) {
            markers.value[idx].openPopup();
        }
    }
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
    <Head title="Peta Operasi Tanggap Bencana" />

    <AuthenticatedLayout>
        <template #header>
            <span>Peta Operasi Tanggap Bencana</span>
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
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Peta Operasi Tanggap Bencana</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">GIS Mapping & Live Monitoring Kejadian Bencana & Tim Evakuasi</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="openAddModal"
                    class="px-3.5 py-2 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center space-x-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>+ Laporkan Kejadian Bencana</span>
                </button>
            </div>
        </div>

        <!-- Main Workspace Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 h-[calc(100vh-12rem)] min-h-[500px]">
            
            <!-- Map Container (2 Columns) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden relative flex flex-col">
                <!-- Live Map -->
                <div id="map-container" class="flex-1 w-full h-full z-10"></div>
                
                <!-- Map overlay coordinates picker tip -->
                <div class="absolute bottom-4 left-4 z-20 bg-white/95 dark:bg-gray-950/95 backdrop-blur border border-gray-100 dark:border-gray-800 px-3 py-1.5 rounded-lg shadow-md text-[10px] text-gray-500 max-w-xs transition-opacity pointer-events-none">
                    💡 Klik di mana saja pada peta untuk menyalin koordinat secara otomatis ke form input.
                </div>
            </div>

            <!-- Operations list (1 Column) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm p-6 flex flex-col justify-between overflow-hidden">
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-pulse"></span>
                            <span>Titik Operasi Aktif</span>
                        </h3>
                        <button
                            @click="openAddModal"
                            class="px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-xl transition-colors shadow-sm"
                        >
                            + Lapor Titik
                        </button>
                    </div>

                    <!-- List Scroll area -->
                    <div class="space-y-3 overflow-y-auto max-h-[calc(100vh-23rem)] pr-1 scrollbar-thin">
                        <div
                            v-for="e in events"
                            :key="e.id"
                            class="p-4 bg-gray-50/50 dark:bg-gray-800/30 hover:bg-gray-100 dark:hover:bg-gray-800/50 border border-gray-100 dark:border-gray-800/60 rounded-xl cursor-pointer transition-all duration-150 flex justify-between items-start"
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
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ e.title }}</h4>
                                </div>
                                <p class="text-xs text-gray-400 truncate">📍 {{ e.location }}</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-[10px] font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded">
                                        {{ e.status }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ e.victim_count }} Jiwa
                                    </span>
                                </div>
                            </div>

                            <!-- Action dropdown buttons -->
                            <div class="flex items-center space-x-1 shrink-0">
                                <button
                                    @click.stop="openEditModal(e)"
                                    class="p-1 rounded text-gray-400 hover:text-brand-500 hover:bg-white dark:hover:bg-gray-850"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <button
                                    @click.stop="deleteEvent(e.id)"
                                    class="p-1 rounded text-gray-400 hover:text-red-500 hover:bg-white dark:hover:bg-gray-850"
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
                        <input v-model="form.title" type="text" placeholder="Banjir Luapan Ciliwung" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Kategori</label>
                            <select v-model="form.category" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" required>
                                <option value="Banjir">Banjir</option>
                                <option value="Longsor">Tanah Longsor</option>
                                <option value="Gempa">Gempa Bumi</option>
                                <option value="Erupsi">Erupsi Gunung Api</option>
                                <option value="Kebakaran">Kebakaran</option>
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
                            <input v-model="form.latitude" type="number" step="any" placeholder="-6.2244" class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-brand-600 dark:text-brand-400 uppercase mb-1">Longitude</label>
                            <input v-model="form.longitude" type="number" step="any" placeholder="106.8622" class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 text-xs focus:border-brand-500 focus:outline-none" required />
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
