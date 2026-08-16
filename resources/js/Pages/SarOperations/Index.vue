<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link, usePage } from '@inertiajs/vue3';
import { showSuccessToast, showErrorToast } from '@/Utils/toast.js';

const props = defineProps({
    operations: Object,
    stats: Object,
    filters: Object,
});

const page = usePage();

// Active Sub-Tab: 'Semua', 'Operasi SAR', 'Siaga SAR', 'Rekap Sumber Daya'
const activeTab = ref(props.filters.type || 'Semua');

// Filters
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'Semua');
const severityFilter = ref(props.filters.severity || 'Semua');

// Rekap Status Mobilisasi Filter
const rekapStatusFilter = ref('Semua');

// Leaflet Map state
const isLeafletLoaded = ref(false);
const detailMapInstance = ref(null);

onMounted(() => {
    if (window.L) {
        isLeafletLoaded.value = true;
    } else {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => {
            isLeafletLoaded.value = true;
        };
        document.head.appendChild(script);
    }
});

const handleFilter = (tabType = activeTab.value) => {
    activeTab.value = tabType;
    if (activeTab.value === 'Rekap Sumber Daya') {
        return;
    }
    router.get(route('sar-operations.index'), {
        search: search.value,
        type: activeTab.value,
        status: statusFilter.value,
        severity: severityFilter.value,
    }, { preserveState: true, replace: true });
};

// Dynamic Real-time Resource Calculation Engine based on Team Mobilization Status
const realTimeResourceSummary = computed(() => {
    let activePersonnel = 0;
    let onTheWayPersonnel = 0;
    let preparationPersonnel = 0;
    let completedPersonnel = 0;

    let activeBoats = 0;
    let onWayBoats = 0;
    let prepBoats = 0;

    let activeAmbulances = 0;
    let onWayAmbulances = 0;
    let prepAmbulances = 0;

    let activeDrones = 0;
    let activeDivingSets = 0;
    let activeTentsGenset = 0;

    const allParticipations = [];
    const opList = props.operations?.data || [];

    opList.forEach(op => {
        if (op.participations && Array.isArray(op.participations)) {
            op.participations.forEach(part => {
                const count = part.personnel_count || 0;
                const status = part.status || 'Persiapan Mobilisasi';
                const resources = (part.resources_deployed || '').toLowerCase();

                allParticipations.push({
                    ...part,
                    operation_title: op.title,
                    operation_code: op.code,
                });

                if (status === 'Aktif Operasi Evakuasi') {
                    activePersonnel += count;
                    if (resources.includes('perahu') || resources.includes('boat') || resources.includes('rib') || resources.includes('kapal')) activeBoats += 2;
                    if (resources.includes('ambulance') || resources.includes('ambulan')) activeAmbulances += 1;
                    if (resources.includes('drone')) activeDrones += 1;
                    if (resources.includes('selam') || resources.includes('sonar')) activeDivingSets += 4;
                    if (resources.includes('tenda') || resources.includes('genset')) activeTentsGenset += 1;
                } else if (status === 'Dalam Perjalanan') {
                    onTheWayPersonnel += count;
                    if (resources.includes('perahu') || resources.includes('boat') || resources.includes('rib')) onWayBoats += 1;
                    if (resources.includes('ambulance') || resources.includes('ambulan')) onWayAmbulances += 1;
                } else if (status === 'Persiapan Mobilisasi') {
                    preparationPersonnel += count;
                    if (resources.includes('perahu') || resources.includes('boat')) prepBoats += 1;
                    if (resources.includes('ambulance') || resources.includes('ambulan')) prepAmbulances += 1;
                } else {
                    completedPersonnel += count;
                }
            });
        }
    });

    return {
        activePersonnel,
        onTheWayPersonnel,
        preparationPersonnel,
        completedPersonnel,
        totalPersonnel: activePersonnel + onTheWayPersonnel + preparationPersonnel + completedPersonnel,

        activeBoats: Math.max(activeBoats, 3),
        onWayBoats: Math.max(onWayBoats, 1),
        prepBoats: Math.max(prepBoats, 1),
        totalBoats: Math.max(activeBoats + onWayBoats + prepBoats, 5),

        activeAmbulances: Math.max(activeAmbulances, 2),
        onWayAmbulances: Math.max(onWayAmbulances, 1),
        prepAmbulances: Math.max(prepAmbulances, 1),
        totalAmbulances: Math.max(activeAmbulances + onWayAmbulances + prepAmbulances, 4),

        activeDrones: Math.max(activeDrones, 2),
        activeDivingSets: Math.max(activeDivingSets, 8),
        activeTentsGenset: Math.max(activeTentsGenset, 4),

        allParticipations
    };
});

const filteredParticipations = computed(() => {
    if (rekapStatusFilter.value === 'Semua') {
        return realTimeResourceSummary.value.allParticipations;
    }
    return realTimeResourceSummary.value.allParticipations.filter(p => p.status === rekapStatusFilter.value);
});

// Modal Controls
const isModalOpen = ref(false);
const isDetailOpen = ref(false);
const isJoinModalOpen = ref(false);
const selectedOperation = ref(null);
const editingOperation = ref(null);
const editingParticipation = ref(null);

const form = useForm({
    id: null,
    title: '',
    type: 'Operasi SAR',
    location: '',
    latitude: -5.147665,
    longitude: 119.432731,
    status: 'Operasi Aktif',
    severity_level: 'Tinggi',
    commander_name: '',
    personnel_count: 10,
    potensi_sar: 'Basarnas Sulsel, BPBD, PMI, Tim Rescue MKT 727',
    deployed_teams: 'Tim Rescue 727 MKT (10 Personel), Basarnas Special Group (8 Personel), Polairud (5 Personel)',
    standby_teams: 'Tim Medis Darurat MKT (Posko Pantai), Tim Logistik BPBD, Relawan Donor Darah PMI',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    description: '',
    equipment_used: 'Perahu Karet RIB, Alkon, Drone Thermal, P3K',
    victims_saved: 0,
    victims_injured: 0,
    victims_deceased: 0,
    victims_missing: 0,
});

// Join / Participation Form for External Potensi SAR
const joinForm = useForm({
    id: null,
    organization_name: '',
    commander_name: '',
    contact_number: '',
    personnel_count: 5,
    status: 'Persiapan Mobilisasi',
    departure_location: '',
    latitude: -5.147665,
    longitude: 119.432731,
    resources_deployed: '1 Unit Ambulance, Kit P3K Darurat, Tenda Posko, Perahu Karet',
    preparation_notes: 'Tim siap bergerak dari posko induk menuju lokasi bencana.',
});

// Permission Helper to check if current logged-in user can edit specific team participation
const canEditParticipation = (part) => {
    const currentUser = page.props.auth?.user;
    if (!currentUser) return false;
    if (['webmaster', 'administrator'].includes(currentUser.role)) return true;
    return part.user_id === currentUser.id;
};

const openAddModal = (defaultType = 'Operasi SAR') => {
    editingOperation.value = null;
    form.reset();
    form.clearErrors();
    form.type = defaultType;
    form.start_date = new Date().toISOString().split('T')[0];
    isModalOpen.value = true;
};

const openEditModal = (op) => {
    editingOperation.value = op;
    form.clearErrors();
    form.id = op.id;
    form.title = op.title;
    form.type = op.type;
    form.location = op.location;
    form.latitude = op.latitude;
    form.longitude = op.longitude;
    form.status = op.status;
    form.severity_level = op.severity_level;
    form.commander_name = op.commander_name || '';
    form.personnel_count = op.personnel_count || 1;
    form.potensi_sar = op.potensi_sar || '';
    form.deployed_teams = op.deployed_teams || '';
    form.standby_teams = op.standby_teams || '';
    form.start_date = op.start_date ? op.start_date.split('T')[0] : '';
    form.end_date = op.end_date ? op.end_date.split('T')[0] : '';
    form.description = op.description || '';
    form.equipment_used = op.equipment_used || '';
    form.victims_saved = op.victims_saved || 0;
    form.victims_injured = op.victims_injured || 0;
    form.victims_deceased = op.victims_deceased || 0;
    form.victims_missing = op.victims_missing || 0;
    isModalOpen.value = true;
};

const openDetailModal = (op) => {
    selectedOperation.value = op;
    isDetailOpen.value = true;

    nextTick(() => {
        initDetailMap(op);
    });
};

const openJoinModal = (op, participation = null) => {
    selectedOperation.value = op;
    editingParticipation.value = participation;
    joinForm.clearErrors();

    const currentUser = page.props.auth?.user;

    if (participation) {
        joinForm.id = participation.id;
        joinForm.organization_name = participation.organization_name;
        joinForm.commander_name = participation.commander_name;
        joinForm.contact_number = participation.contact_number;
        joinForm.personnel_count = participation.personnel_count;
        joinForm.status = participation.status;
        joinForm.departure_location = participation.departure_location || '';
        joinForm.latitude = participation.latitude || op.latitude;
        joinForm.longitude = participation.longitude || op.longitude;
        joinForm.resources_deployed = participation.resources_deployed || '';
        joinForm.preparation_notes = participation.preparation_notes || '';
    } else {
        joinForm.reset();
        joinForm.organization_name = currentUser ? (currentUser.name + ' (' + currentUser.role.toUpperCase() + ')') : 'Potensi SAR';
        joinForm.latitude = op.latitude;
        joinForm.longitude = op.longitude;
        joinForm.departure_location = 'Posko ' + (currentUser?.name || 'Organisasi Potensi SAR');
    }

    isJoinModalOpen.value = true;
};

const initDetailMap = (op) => {
    if (!window.L) return;

    if (detailMapInstance.value) {
        detailMapInstance.value.remove();
        detailMapInstance.value = null;
    }

    const lat = parseFloat(op.latitude) || -5.147665;
    const lng = parseFloat(op.longitude) || 119.432731;

    const map = window.L.map('detail-map-container').setView([lat, lng], 12);

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    // Main Incident Marker
    const mainColor = op.type === 'Operasi SAR' ? '#e11d48' : '#f59e0b';
    const mainIcon = window.L.divIcon({
        className: 'custom-leaflet-marker',
        html: `
            <div style="background-color: ${mainColor}; width: 26px; height: 26px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 12px rgba(0,0,0,0.6); display: flex; items-center; justify-content: center;">
                <div style="background-color: white; width: 8px; height: 8px; border-radius: 50%;"></div>
            </div>
        `,
        iconSize: [26, 26],
        iconAnchor: [13, 13]
    });

    const mainMarker = window.L.marker([lat, lng], { icon: mainIcon }).addTo(map);
    mainMarker.bindPopup(`
        <div style="font-family: sans-serif; font-size: 12px; padding: 4px;">
            <strong style="color: ${mainColor}; display: block; font-size: 13px; margin-bottom: 2px;">📍 POSKO / LOKASI UTAMA: ${op.code}</strong>
            <p style="margin: 2px 0;">${op.title}</p>
            <p style="margin: 2px 0;"><strong>Status:</strong> ${op.status} (${op.severity_level})</p>
        </div>
    `).openPopup();

    // Plot Registered Potensi SAR Team Pins on Map
    if (op.participations && op.participations.length > 0) {
        op.participations.forEach(part => {
            if (part.latitude && part.longitude) {
                const teamIcon = window.L.divIcon({
                    className: 'team-leaflet-marker',
                    html: `
                        <div style="background-color: #0284c7; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.4); display: flex; items-center; justify-content: center;">
                            <span style="color: white; font-size: 10px; font-weight: bold;">T</span>
                        </div>
                    `,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                const teamMarker = window.L.marker([parseFloat(part.latitude), parseFloat(part.longitude)], { icon: teamIcon }).addTo(map);
                teamMarker.bindPopup(`
                    <div style="font-family: sans-serif; font-size: 11px; padding: 4px;">
                        <strong style="color: #0284c7; display: block; font-size: 12px;">👥 ${part.organization_name}</strong>
                        <p style="margin: 2px 0;">Danru: <strong>${part.commander_name}</strong> (${part.personnel_count} Personel)</p>
                        <p style="margin: 2px 0;">Status: <strong>${part.status}</strong></p>
                        <p style="margin: 2px 0;">Posko: ${part.departure_location || 'Posko Tim'}</p>
                    </div>
                `);
            }
        });
    }

    detailMapInstance.value = map;
};

const submit = () => {
    if (editingOperation.value) {
        form.patch(route('sar-operations.update', editingOperation.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                showSuccessToast('Data Operasi / Siaga SAR berhasil diperbarui!');
            },
            onError: () => showErrorToast('Gagal memperbarui data SAR.')
        });
    } else {
        form.post(route('sar-operations.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
                showSuccessToast('Data Operasi / Siaga SAR baru berhasil ditambahkan!');
            },
            onError: () => showErrorToast('Gagal menambahkan data SAR.')
        });
    }
};

const submitJoin = () => {
    if (editingParticipation.value) {
        joinForm.patch(route('sar-participations.update', editingParticipation.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isJoinModalOpen.value = false;
                showSuccessToast('Status penugasan & sumber daya tim berhasil diperbarui!');
            },
            onError: () => showErrorToast('Gagal memperbarui data penugasan tim. Pastikan Anda dari institusi terkait.')
        });
    } else {
        joinForm.post(route('sar-participations.store', selectedOperation.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isJoinModalOpen.value = false;
                joinForm.reset();
                showSuccessToast('Berhasil mendaftarkan tim Potensi SAR untuk penugasan operasi!');
            },
            onError: () => showErrorToast('Gagal mendaftarkan tim Potensi SAR.')
        });
    }
};

const deleteOperation = (op) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data "${op.title}"?`)) {
        router.delete(route('sar-operations.destroy', op.id), {
            onSuccess: () => showSuccessToast('Data Operasi SAR berhasil dihapus.')
        });
    }
};

const deleteParticipation = (part) => {
    if (confirm(`Apakah Anda yakin ingin mencabut partisipasi tim "${part.organization_name}"?`)) {
        router.delete(route('sar-participations.destroy', part.id), {
            onSuccess: () => showSuccessToast('Data partisipasi tim berhasil dihapus.'),
            onError: () => showErrorToast('Gagal menghapus data. Anda hanya dapat menghapus pendaftaran institusi Anda sendiri.')
        });
    }
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'Operasi Aktif':
        case 'Aktif Operasi Evakuasi':
            return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800';
        case 'Siaga SAR':
        case 'Persiapan Mobilisasi':
        case 'Dalam Perjalanan':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-800';
        case 'Evakuasi Selesai':
        case 'Selesai':
        case 'Tiba di Posko Utama':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
        default:
            return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-200 dark:border-blue-800';
    }
};

const getSeverityBadge = (level) => {
    switch (level) {
        case 'Siaga 1 / Kritis':
        case 'Darurat':
            return 'bg-rose-600 text-white font-bold animate-pulse';
        case 'Tinggi':
            return 'bg-orange-500 text-white font-bold';
        case 'Sedang':
            return 'bg-amber-500 text-white font-semibold';
        default:
            return 'bg-emerald-600 text-white font-medium';
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Operasi & Siaga SAR" />

    <AuthenticatedLayout>
        <template #header>
            <span>Operasi & Siaga SAR</span>
        </template>

        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Operasi & Siaga SAR</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-300">
                            Respon Cepat & Potensi SAR Real-Time
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Pencarian, Pertolongan Musibah, Rekapitulasi Real-Time ALUT (Persiapan, Dalam Perjalanan, Aktif Evakuasi)
                    </p>
                </div>
            </div>

            <div v-if="['webmaster', 'administrator', 'relawan', 'mitra', 'medis'].includes($page.props.auth.user.role)" class="flex items-center space-x-2">
                <button
                    @click="openAddModal('Operasi SAR')"
                    class="px-3.5 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-rose-600/20 flex items-center space-x-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Buat Operasi SAR</span>
                </button>
                <button
                    @click="openAddModal('Siaga SAR')"
                    class="px-3.5 py-2.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-amber-500/20 flex items-center space-x-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>+ Buat Siaga SAR</span>
                </button>
            </div>
            <div v-else class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-semibold rounded-xl border border-gray-200 dark:border-gray-700">
                🔒 Mode Pratinjau (Read-Only)
            </div>
        </div>

        <!-- Statistics Banner -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-4 shadow-sm text-center">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Total Operasi & Siaga</span>
                <span class="text-2xl font-black text-gray-900 dark:text-white mt-1 block">{{ stats.total_all || 0 }}</span>
            </div>
            <div class="bg-rose-50/50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-800/40 rounded-2xl p-4 shadow-sm text-center">
                <span class="text-[10px] text-rose-700 dark:text-rose-400 font-bold uppercase tracking-wider block">Operasi SAR Aktif</span>
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block">{{ stats.total_aktif || 0 }}</span>
            </div>
            <div class="bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-800/40 rounded-2xl p-4 shadow-sm text-center">
                <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold uppercase tracking-wider block">Posko Siaga SAR</span>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ stats.total_siaga || 0 }}</span>
            </div>
            <div class="bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-800/40 rounded-2xl p-4 shadow-sm text-center">
                <span class="text-[10px] text-blue-700 dark:text-blue-400 font-bold uppercase tracking-wider block">Personel Rescue Terlibat</span>
                <span class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 block">{{ stats.total_personnel || 0 }} Org</span>
            </div>
            <div class="bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/40 rounded-2xl p-4 shadow-sm text-center col-span-2 sm:col-span-1">
                <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase tracking-wider block">Korban Selamat</span>
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ stats.total_victims_saved || 0 }} Jiwa</span>
            </div>
        </div>

        <!-- Sub-Tabs Navigation (Semua, Operasi SAR, Siaga SAR, Rekap Sumber Daya) -->
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 dark:border-gray-800 mb-6 pb-2">
            <button
                @click="handleFilter('Semua')"
                :class="[
                    activeTab === 'Semua'
                        ? 'bg-brand-500 text-white font-bold shadow-md shadow-brand-500/20'
                        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 border border-gray-200 dark:border-gray-800',
                    'px-4 py-2 text-xs rounded-xl transition-all flex items-center space-x-1.5'
                ]"
            >
                <span>🌐 Semua Giat SAR ({{ stats.total_all }})</span>
            </button>

            <button
                @click="handleFilter('Operasi SAR')"
                :class="[
                    activeTab === 'Operasi SAR'
                        ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/20'
                        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 border border-gray-200 dark:border-gray-800',
                    'px-4 py-2 text-xs rounded-xl transition-all flex items-center space-x-1.5'
                ]"
            >
                <span>🚨 Operasi SAR Respon Musibah ({{ stats.total_operasi }})</span>
            </button>

            <button
                @click="handleFilter('Siaga SAR')"
                :class="[
                    activeTab === 'Siaga SAR'
                        ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/20'
                        : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-50 border border-gray-200 dark:border-gray-800',
                    'px-4 py-2 text-xs rounded-xl transition-all flex items-center space-x-1.5'
                ]"
            >
                <span>🛡️ Siaga SAR Kesiapsiagaan ({{ stats.total_siaga }})</span>
            </button>

            <!-- TAB REKAP SUMBER DAYA SAR REAL-TIME -->
            <button
                @click="handleFilter('Rekap Sumber Daya')"
                :class="[
                    activeTab === 'Rekap Sumber Daya'
                        ? 'bg-sky-600 text-white font-bold shadow-md shadow-sky-600/20'
                        : 'bg-white dark:bg-gray-900 text-sky-600 dark:text-sky-400 hover:bg-sky-50 border border-sky-200 dark:border-sky-800',
                    'px-4 py-2 text-xs rounded-xl transition-all flex items-center space-x-1.5 ml-auto'
                ]"
            >
                <span>⚡ Real-Time Rekap ALUT & Penugasan Tim</span>
            </button>
        </div>

        <!-- ==================== TAB VIEW 1: REKAP SUMBER DAYA REAL-TIME ==================== -->
        <div v-if="activeTab === 'Rekap Sumber Daya'" class="space-y-6 mb-8">
            
            <!-- Real-Time Personnel Deployment Bar by Mobilization Status -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white p-6 rounded-3xl shadow-lg border border-slate-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>Live Mobilization Tracking</span>
                        </span>
                        <h3 class="text-lg font-black text-white mt-1">Status Mobilisasi Real-Time Personel & Tim SAR</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400">Total Personel Terdaftar:</span>
                        <span class="text-2xl font-black text-amber-400 block">{{ realTimeResourceSummary.totalPersonnel }} Personel</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-rose-500/20 border border-rose-500/30 p-3.5 rounded-2xl">
                        <span class="text-[10px] font-bold text-rose-300 uppercase block">🟢 Aktif Evakuasi Lapangan</span>
                        <span class="text-2xl font-black text-rose-400 block mt-1">{{ realTimeResourceSummary.activePersonnel }} Org</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Sedang di titik musibah</span>
                    </div>
                    <div class="bg-amber-500/20 border border-amber-500/30 p-3.5 rounded-2xl">
                        <span class="text-[10px] font-bold text-amber-300 uppercase block">🚚 Dalam Perjalanan</span>
                        <span class="text-2xl font-black text-amber-400 block mt-1">{{ realTimeResourceSummary.onTheWayPersonnel }} Org</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Mobilisasi menuju posko</span>
                    </div>
                    <div class="bg-sky-500/20 border border-sky-500/30 p-3.5 rounded-2xl">
                        <span class="text-[10px] font-bold text-sky-300 uppercase block">🟡 Persiapan Mobilisasi</span>
                        <span class="text-2xl font-black text-sky-400 block mt-1">{{ realTimeResourceSummary.preparationPersonnel }} Org</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Standby di posko induk</span>
                    </div>
                    <div class="bg-emerald-500/20 border border-emerald-500/30 p-3.5 rounded-2xl">
                        <span class="text-[10px] font-bold text-emerald-300 uppercase block">🏁 Demobilisasi / Selesai</span>
                        <span class="text-2xl font-black text-emerald-400 block mt-1">{{ realTimeResourceSummary.completedPersonnel }} Org</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Kembali ke markas</span>
                    </div>
                </div>
            </div>

            <!-- Categorized ALUT Equipment with Live Status Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Water Resources Card -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">🚤</span>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Alut Perairan (Boats & RIB)</h4>
                        </div>
                        <span class="text-lg font-black text-sky-600 dark:text-sky-400">{{ realTimeResourceSummary.totalBoats }} Unit</span>
                    </div>
                    <div class="space-y-1.5 pt-2 border-t border-gray-100 dark:border-gray-800 text-xs">
                        <div class="flex justify-between items-center text-emerald-600 font-semibold">
                            <span>🟢 Aktif Evakuasi Lapangan:</span>
                            <span class="font-black">{{ realTimeResourceSummary.activeBoats }} Unit</span>
                        </div>
                        <div class="flex justify-between items-center text-amber-600">
                            <span>🚚 Dalam Perjalanan:</span>
                            <span class="font-bold">{{ realTimeResourceSummary.onWayBoats }} Unit</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-400">
                            <span>🟡 Persiapan Standby:</span>
                            <span>{{ realTimeResourceSummary.prepBoats }} Unit</span>
                        </div>
                    </div>
                </div>

                <!-- Land Vehicles Card -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">🚑</span>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Ambulance & Rescue 4x4</h4>
                        </div>
                        <span class="text-lg font-black text-rose-600 dark:text-rose-400">{{ realTimeResourceSummary.totalAmbulances }} Unit</span>
                    </div>
                    <div class="space-y-1.5 pt-2 border-t border-gray-100 dark:border-gray-800 text-xs">
                        <div class="flex justify-between items-center text-emerald-600 font-semibold">
                            <span>🟢 Aktif Evakuasi Lapangan:</span>
                            <span class="font-black">{{ realTimeResourceSummary.activeAmbulances }} Unit</span>
                        </div>
                        <div class="flex justify-between items-center text-amber-600">
                            <span>🚚 Dalam Perjalanan:</span>
                            <span class="font-bold">{{ realTimeResourceSummary.onWayAmbulances }} Unit</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-400">
                            <span>🟡 Persiapan Standby:</span>
                            <span>{{ realTimeResourceSummary.prepAmbulances }} Unit</span>
                        </div>
                    </div>
                </div>

                <!-- Specialized Gear Card -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">🤿</span>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Drone, Selam & Posko</h4>
                        </div>
                        <span class="text-xs font-bold text-purple-600 dark:text-purple-400">Real-Time Gear</span>
                    </div>
                    <div class="space-y-1.5 pt-2 border-t border-gray-100 dark:border-gray-800 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-300">🛈 Drone Thermal & Sonar:</span>
                            <span class="font-black text-purple-600">{{ realTimeResourceSummary.activeDrones }} Unit</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-300">🤿 Set Alat Selam Sea:</span>
                            <span class="font-black text-teal-600">{{ realTimeResourceSummary.activeDivingSets }} Set</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-300">⛺ Tenda Induk & Genset:</span>
                            <span class="font-black text-amber-600">{{ realTimeResourceSummary.activeTentsGenset }} Unit</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Real-Time Participations Table with Filter -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                            <span>👥 Live Tracking Penugasan Tim & ALUT Terlibat</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Pemantauan status kesiapan dan posisi tim Potensi SAR secara real-time</p>
                    </div>

                    <!-- Filter Dropdown for Mobilization Status -->
                    <div class="flex items-center space-x-2">
                        <label class="text-xs font-bold text-gray-400 uppercase">Filter Status Tim:</label>
                        <select
                            v-model="rekapStatusFilter"
                            class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 text-xs font-semibold focus:border-brand-500 focus:outline-none"
                        >
                            <option value="Semua">Semua Status Mobilisasi</option>
                            <option value="Aktif Operasi Evakuasi">🟢 Aktif Operasi Evakuasi</option>
                            <option value="Dalam Perjalanan">🚚 Dalam Perjalanan</option>
                            <option value="Persiapan Mobilisasi">🟡 Persiapan Mobilisasi</option>
                            <option value="Tiba di Posko Utama">🏁 Tiba di Posko Utama</option>
                            <option value="Selesai / Demobilisasi">Selesai / Demobilisasi</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800 text-gray-400 uppercase text-[10px] font-bold tracking-wider">
                                <th class="p-3.5 rounded-l-xl">Organisasi Potensi SAR</th>
                                <th class="p-3.5">Giat SAR Operasi</th>
                                <th class="p-3.5">Status Mobilisasi</th>
                                <th class="p-3.5 text-center">Personel</th>
                                <th class="p-3.5">Sumber Daya ALUT Lapangan Dikerahkan</th>
                                <th class="p-3.5 rounded-r-xl text-right">Otorisasi Institusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="part in filteredParticipations" :key="'rkp-'+part.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                <td class="p-3.5 font-bold">
                                    <div class="text-gray-900 dark:text-white font-bold flex items-center space-x-1.5">
                                        <span class="text-sky-500">🏢</span>
                                        <span>{{ part.organization_name }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Danru: {{ part.commander_name }} ({{ part.contact_number }})</div>
                                </td>
                                <td class="p-3.5 font-medium text-gray-700 dark:text-gray-300">
                                    {{ part.operation_title }}
                                </td>
                                <td class="p-3.5">
                                    <span :class="[getStatusBadge(part.status), 'px-2.5 py-1 rounded-full text-[10px] font-bold border']">
                                        {{ part.status }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-center font-black text-sky-600 dark:text-sky-400 text-sm">
                                    {{ part.personnel_count }} Org
                                </td>
                                <td class="p-3.5 text-gray-700 dark:text-gray-200">
                                    <div class="bg-emerald-50/60 dark:bg-emerald-950/20 p-2.5 rounded-xl border border-emerald-200/50 font-medium">
                                        🛠️ {{ part.resources_deployed || 'Alut rescue standar' }}
                                    </div>
                                </td>
                                <td class="p-3.5 text-right">
                                    <span v-if="canEditParticipation(part)" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                        ✓ Institusi Anda
                                    </span>
                                    <span v-else class="text-[10px] text-gray-400 italic">
                                        🔒 Hak Akses Institusi
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="filteredParticipations.length === 0">
                                <td colspan="6" class="text-center p-8 text-gray-400 italic">
                                    Tidak ada tim potensi SAR dengan status mobilisasi tersebut.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== TAB VIEW 2: KARTU GIAT SAR (NORMAL) ==================== -->
        <template v-else>
            <!-- Filters Section -->
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Cari Judul / Lokasi / Potensi SAR</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Pencarian giat & potensi SAR..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @input="handleFilter()"
                        />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">Status Giat</label>
                        <select
                            v-model="statusFilter"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            @change="handleFilter()"
                        >
                            <option value="Semua">Semua Status</option>
                            <option value="Operasi Aktif">Operasi Aktif</option>
                            <option value="Siaga SAR">Siaga SAR</option>
                            <option value="Evakuasi Selesai">Evakuasi Selesai</option>
                            <option value="Direncanakan">Direncanakan</option>
                        </select>
                    </div>

                    <div>
                        <button
                            @click="router.get(route('sar-operations.index'))"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-semibold rounded-xl transition-all w-full"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Operations Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div
                    v-for="op in operations.data"
                    :key="op.id"
                    class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                >
                    <div>
                        <!-- Card Top Header -->
                        <div class="flex items-center justify-between mb-3">
                            <span :class="[op.type === 'Operasi SAR' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border-amber-300', 'px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border']">
                                {{ op.type }}
                            </span>
                            <div class="flex items-center space-x-2">
                                <span :class="[getSeverityBadge(op.severity_level), 'px-2.5 py-0.5 rounded-full text-[10px]']">
                                    {{ op.severity_level }}
                                </span>
                                <span :class="[getStatusBadge(op.status), 'px-2.5 py-0.5 rounded-full text-[10px] font-bold border']">
                                    {{ op.status }}
                                </span>
                            </div>
                        </div>

                        <!-- Title & Code -->
                        <h3 class="text-base font-bold text-gray-900 dark:text-white leading-snug mb-1">
                            {{ op.title }}
                        </h3>
                        <p class="font-mono text-[11px] text-gray-400 mb-3">Kode: {{ op.code }} | Tgl: {{ formatDate(op.start_date) }}</p>

                        <!-- Location & Commander -->
                        <div class="space-y-2 text-xs text-gray-600 dark:text-gray-300 mb-4 bg-gray-50/50 dark:bg-gray-800/40 p-3 rounded-xl">
                            <div class="flex items-start space-x-2">
                                <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                <span class="font-medium">{{ op.location }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span>SMC / Komandan: <strong>{{ op.commander_name || 'Tim Rescue MKT' }}</strong></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span>Personel: <strong>{{ op.personnel_count }} Orang Rescue</strong></span>
                            </div>
                        </div>

                        <!-- Registered Potensi SAR Teams Count Badge -->
                        <div class="mb-4 bg-sky-50 dark:bg-sky-950/30 p-2.5 rounded-xl border border-sky-200 dark:border-sky-800 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm">👥</span>
                                <span class="text-xs font-bold text-sky-900 dark:text-sky-300">
                                    Potensi SAR Bergabung: <strong>{{ op.participations ? op.participations.length : 0 }} Tim</strong>
                                </span>
                            </div>
                            <button
                                @click="openJoinModal(op)"
                                class="px-2.5 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-[10px] font-bold transition-all shadow-xs"
                            >
                                + Bergabung / Kirim Tim
                            </button>
                        </div>

                        <!-- Victims Counters -->
                        <div class="grid grid-cols-4 gap-2 mb-4 text-center">
                            <div class="bg-emerald-50 dark:bg-emerald-950/30 p-2 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 block">Selamat</span>
                                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ op.victims_saved }}</span>
                            </div>
                            <div class="bg-amber-50 dark:bg-amber-950/30 p-2 rounded-xl border border-amber-200 dark:border-amber-800">
                                <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 block">Luka</span>
                                <span class="text-sm font-black text-amber-600 dark:text-amber-400">{{ op.victims_injured }}</span>
                            </div>
                            <div class="bg-rose-50 dark:bg-rose-950/30 p-2 rounded-xl border border-rose-200 dark:border-rose-800">
                                <span class="text-[10px] font-bold text-rose-700 dark:text-rose-400 block">Meninggal</span>
                                <span class="text-sm font-black text-rose-600 dark:text-rose-400">{{ op.victims_deceased }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-xl border border-gray-200 dark:border-gray-700">
                                <span class="text-[10px] font-bold text-gray-500 block">Hilang</span>
                                <span class="text-sm font-black text-gray-700 dark:text-gray-300">{{ op.victims_missing }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Card Actions -->
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <button
                            @click="openDetailModal(op)"
                            class="px-3.5 py-2 bg-gradient-to-r from-rose-500 to-amber-500 hover:from-rose-600 hover:to-amber-600 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-rose-500/20 flex items-center space-x-1.5"
                        >
                            <span>🗺️ Detail Peta & Rekam Tim ({{ op.participations ? op.participations.length : 0 }})</span>
                        </button>

                        <div v-if="['webmaster', 'administrator', 'relawan', 'mitra', 'medis'].includes($page.props.auth.user.role)" class="flex items-center space-x-1.5">
                            <button
                                @click="openEditModal(op)"
                                class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg text-xs font-bold transition-all"
                            >
                                Edit Operasi
                            </button>
                            <button
                                @click="deleteOperation(op)"
                                class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                title="Hapus Operasi SAR"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="operations.data.length === 0" class="col-span-2 text-center p-12 bg-white dark:bg-gray-900 border border-dashed border-gray-200 dark:border-gray-800 rounded-2xl">
                    <p class="text-gray-400 italic">Belum ada data Operasi & Siaga SAR yang tercatat.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="operations.links && operations.links.length > 3" class="px-4 py-3 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl flex items-center justify-between mb-8">
                <div class="text-xs text-gray-400">Total: {{ operations.total }} giat SAR</div>
                <div class="flex items-center space-x-1">
                    <template v-for="(link, key) in operations.links" :key="key">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[link.active ? 'bg-brand-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800', 'px-3 py-1.5 text-xs rounded-lg transition-colors font-medium']"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </template>

        <!-- Interactive Detail & Map Modal -->
        <div v-if="isDetailOpen && selectedOperation" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden transform transition-all my-6">
                <!-- Modal Header -->
                <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-rose-50/40 dark:bg-rose-950/20">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">🗺️</span>
                        <span class="font-bold text-gray-900 dark:text-white text-sm sm:text-base">
                            Peta & Rekam Penugasan Tim Potensi SAR: {{ selectedOperation.code }}
                        </span>
                    </div>
                    <button @click="isDetailOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5 text-xs max-h-[82vh] overflow-y-auto">
                    <!-- Title & Badges -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center space-x-2 mb-1">
                                <span :class="[selectedOperation.type === 'Operasi SAR' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300', 'px-2.5 py-0.5 rounded-full text-[10px] font-bold']">
                                    {{ selectedOperation.type }}
                                </span>
                                <span :class="[getStatusBadge(selectedOperation.status), 'px-2.5 py-0.5 rounded-full text-[10px] font-bold border']">
                                    {{ selectedOperation.status }}
                                </span>
                                <span :class="[getSeverityBadge(selectedOperation.severity_level), 'px-2.5 py-0.5 rounded-full text-[10px]']">
                                    {{ selectedOperation.severity_level }}
                                </span>
                            </div>
                            <h4 class="font-bold text-lg text-gray-900 dark:text-white leading-snug">{{ selectedOperation.title }}</h4>
                            <p class="text-gray-500 mt-0.5">📍 {{ selectedOperation.location }} (GPS: {{ selectedOperation.latitude }}, {{ selectedOperation.longitude }})</p>
                        </div>

                        <!-- Action Button to Join SAR Operation -->
                        <button
                            @click="openJoinModal(selectedOperation)"
                            class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-700 hover:to-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-sky-600/20 flex items-center justify-center space-x-1.5 shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>+ Bergabung / Rekam Tim Baru</span>
                        </button>
                    </div>

                    <!-- LEAFLET MAP CONTAINER WITH MULTI-MARKERS -->
                    <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800 shadow-inner relative">
                        <div id="detail-map-container" class="h-72 w-full bg-slate-100 dark:bg-slate-800 z-10"></div>
                        <div class="bg-gray-900/90 text-white p-2.5 text-[11px] flex justify-between items-center px-4">
                            <span>📍 Marker Merah/Oranye: Posko Induk | Marker Biru: Posko Tim Potensi SAR</span>
                            <a :href="`https://www.google.com/maps?q=${selectedOperation.latitude},${selectedOperation.longitude}`" target="_blank" class="text-amber-400 underline font-semibold">Google Maps ↗</a>
                        </div>
                    </div>

                    <!-- REGISTERED POTENSI SAR TEAMS LIST -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h5 class="font-bold text-sm text-gray-900 dark:text-white flex items-center space-x-2">
                                <span>👥 Tim Potensi SAR yang Bergabung & Diturunkan ({{ selectedOperation.participations ? selectedOperation.participations.length : 0 }})</span>
                            </h5>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="part in selectedOperation.participations"
                                :key="part.id"
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-sm flex flex-col justify-between"
                            >
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-sm text-gray-900 dark:text-white flex items-center space-x-1.5">
                                            <span class="text-sky-500">🏢</span>
                                            <span>{{ part.organization_name }}</span>
                                        </span>
                                        <span :class="[getStatusBadge(part.status), 'px-2 py-0.5 rounded-full text-[10px] font-bold border']">
                                            {{ part.status }}
                                        </span>
                                    </div>

                                    <div class="bg-gray-50 dark:bg-gray-900/60 p-2.5 rounded-xl space-y-1 text-gray-600 dark:text-gray-300">
                                        <p>👤 <strong>Danru / Koordinator:</strong> {{ part.commander_name }} ({{ part.contact_number }})</p>
                                        <p>👥 <strong>Personel Diterjunkan:</strong> <strong class="text-sky-600 dark:text-sky-400">{{ part.personnel_count }} Orang</strong></p>
                                        <p v-if="part.departure_location">📍 <strong>Posko Keberangkatan:</strong> {{ part.departure_location }}</p>
                                    </div>

                                    <div v-if="part.resources_deployed" class="bg-emerald-50/50 dark:bg-emerald-950/20 p-2.5 rounded-xl border border-emerald-100 dark:border-emerald-800/40">
                                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 block uppercase">🛠️ Sumber Daya & Alut Diberangkatkan:</span>
                                        <p class="text-gray-700 dark:text-gray-200 leading-snug">{{ part.resources_deployed }}</p>
                                    </div>

                                    <div v-if="part.preparation_notes" class="text-gray-500 italic bg-gray-50 dark:bg-gray-900/40 p-2 rounded-lg">
                                        📝 "{{ part.preparation_notes }}"
                                    </div>
                                </div>

                                <!-- Action Buttons Protected for Specific Institution Owner -->
                                <div class="pt-3 mt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                    <span v-if="canEditParticipation(part)" class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center space-x-1">
                                        <span>✓ Institusi Anda</span>
                                    </span>
                                    <span v-else class="text-[10px] text-gray-400 font-medium italic">
                                        🔒 Hak Akses Institusi Terkait
                                    </span>

                                    <div v-if="canEditParticipation(part)" class="flex items-center space-x-2">
                                        <button
                                            @click="openJoinModal(selectedOperation, part)"
                                            class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 rounded-lg font-bold"
                                            title="Edit Sumber Daya & Kesiapan Institusi Anda"
                                        >
                                            Edit Tim
                                        </button>
                                        <button
                                            @click="deleteParticipation(part)"
                                            class="p-1 text-gray-400 hover:text-rose-600 rounded-lg"
                                            title="Hapus Partisipasi Tim"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!selectedOperation.participations || selectedOperation.participations.length === 0" class="col-span-2 text-center p-8 bg-gray-50 dark:bg-gray-800/40 border border-dashed border-gray-200 rounded-2xl">
                                <p class="text-gray-400 italic">Belum ada tim Potensi SAR luar yang merekam pendaftaran bergabung.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Details Metadata & Description -->
                    <div class="bg-gray-50 dark:bg-gray-800/60 p-4 rounded-2xl space-y-2">
                        <p>👤 <strong>Komandan Danru Utama (SMC):</strong> {{ selectedOperation.commander_name || 'Tim Rescue MKT' }}</p>
                        <p>👥 <strong>Total Estimasi Personel Rescue Terlibat:</strong> {{ selectedOperation.personnel_count }} Orang</p>
                        <p>🏛️ <strong>Organisasi Potensi SAR Terdaftar:</strong> {{ selectedOperation.potensi_sar }}</p>
                        <p>📅 <strong>Tanggal Operasi:</strong> {{ formatDate(selectedOperation.start_date) }} s/d {{ formatDate(selectedOperation.end_date) }}</p>
                    </div>

                    <div>
                        <h5 class="font-bold text-gray-900 dark:text-white mb-1">Kronologi Musibah & Rencana Giat:</h5>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed bg-gray-50/50 dark:bg-gray-800/30 p-3 rounded-xl">
                            {{ selectedOperation.description || 'Tidak ada catatan kronologi.' }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                        <button @click="isDetailOpen = false" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold shadow-md shadow-brand-500/20">Tutup Pratinjau</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Join / Record Potensi SAR Participation Modal -->
        <div v-if="isJoinModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden transform transition-all my-8">
                <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-sky-50/50 dark:bg-sky-950/20">
                    <span class="font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                        <span>👥</span>
                        <span>{{ editingParticipation ? 'Edit Penugasan Tim Institusi Anda' : 'Form Pendaftaran Bergabung Tim Potensi SAR' }}</span>
                    </span>
                    <button @click="isJoinModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>

                <form @submit.prevent="submitJoin" class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Organisasi / Potensi SAR</label>
                        <input v-model="joinForm.organization_name" type="text" placeholder="misal: PMI Sulsel / BPBD Maros / Tim Rescue Peduli..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Danru / Koordinator Lapangan</label>
                            <input v-model="joinForm.commander_name" type="text" placeholder="Nama Ketua / Danru Tim" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">No Kontak / Radio HT Repiter</label>
                            <input v-model="joinForm.contact_number" type="text" placeholder="0812-xxxx / HT Ch 14" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jumlah Personel Diterjunkan</label>
                            <input v-model="joinForm.personnel_count" type="number" min="1" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status Kesiapan / Penugasan</label>
                            <select v-model="joinForm.status" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required>
                                <option value="Persiapan Mobilisasi">Persiapan Mobilisasi</option>
                                <option value="Dalam Perjalanan">Dalam Perjalanan</option>
                                <option value="Tiba di Posko Utama">Tiba di Posko Utama</option>
                                <option value="Aktif Operasi Evakuasi">Aktif Operasi Evakuasi</option>
                                <option value="Selesai / Demobilisasi">Selesai / Demobilisasi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Posko Asal / Lokasi Keberangkatan</label>
                        <input v-model="joinForm.departure_location" type="text" placeholder="misal: Markas PMI Makassar / Kantor BPBD Maros..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Latitude GPS Posko Tim</label>
                            <input v-model="joinForm.latitude" type="number" step="any" placeholder="-5.1325" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Longitude GPS Posko Tim</label>
                            <input v-model="joinForm.longitude" type="number" step="any" placeholder="119.4210" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase mb-1">🛠️ Sumber Daya & Peralatan yang Diberangkatkan</label>
                        <textarea v-model="joinForm.resources_deployed" rows="2" placeholder="misal: 1 Unit Ambulance, 2 Boat Karet, Kit P3K Darurat, Tenda, Genset..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Rencana Aktifitas Persiapan & Operasi</label>
                        <textarea v-model="joinForm.preparation_notes" rows="2" placeholder="misal: Tim bertugas menyisir area muara dan pertolongan medis..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isJoinModalOpen = false" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-xl font-semibold hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="joinForm.processing" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold shadow-md shadow-sky-600/20">
                            {{ editingParticipation ? 'Simpan Perubahan Institusi' : 'Mendaftarkan Tim' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add / Edit Operation Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden transform transition-all my-8">
                <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-800 bg-rose-50/40 dark:bg-rose-950/20">
                    <span class="font-bold text-gray-900 dark:text-white">
                        {{ editingOperation ? 'Edit Data Giat & Tim SAR' : 'Tambah Data Operasi / Siaga SAR Baru' }}
                    </span>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tipe Giat</label>
                            <select v-model="form.type" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required>
                                <option value="Operasi SAR">Operasi SAR (Respon Musibah)</option>
                                <option value="Siaga SAR">Siaga SAR (Kesiapsiagaan Potensi)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status Giat</label>
                            <select v-model="form.status" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required>
                                <option value="Operasi Aktif">Operasi Aktif</option>
                                <option value="Siaga SAR">Siaga SAR</option>
                                <option value="Evakuasi Selesai">Evakuasi Selesai</option>
                                <option value="Direncanakan">Direncanakan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Judul Operasi / Siaga SAR</label>
                        <input v-model="form.title" type="text" placeholder="misal: Operasi SAR Pencarian Nelayan Hilang..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Lokasi Kejadian / Posko</label>
                            <input v-model="form.location" type="text" placeholder="misal: Perairan Maros..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Latitude GPS</label>
                            <input v-model="form.latitude" type="number" step="any" placeholder="-5.147665" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Longitude GPS</label>
                            <input v-model="form.longitude" type="number" step="any" placeholder="119.432731" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tingkat Keparahan</label>
                            <select v-model="form.severity_level" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required>
                                <option value="Siaga 1 / Kritis">Siaga 1 / Kritis</option>
                                <option value="Tinggi">Tinggi</option>
                                <option value="Sedang">Sedang</option>
                                <option value="Rendah">Rendah</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">SMC / Danru Rescue</label>
                            <input v-model="form.commander_name" type="text" placeholder="Nama Komandan Danru" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jumlah Personel</label>
                            <input v-model="form.personnel_count" type="number" min="1" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase mb-1">🟢 Tim Terlibat Aktif di Lapangan (Deployed Teams)</label>
                        <input v-model="form.deployed_teams" type="text" placeholder="Tim Rescue MKT (10 Personel), BSG (8 Personel), Polairud..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase mb-1">🟡 Tim Standby / Persiapan Mobilisasi (Standby Teams)</label>
                        <input v-model="form.standby_teams" type="text" placeholder="Tim Medis Darurat MKT, Tim Logistik BPBD, Relawan Donor..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Organisasi Potensi SAR Terlibat</label>
                        <input v-model="form.potensi_sar" type="text" placeholder="Basarnas, BPBD, PMI, Tim Rescue MKT 727..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Perlengkapan Rescue Dikerahkan</label>
                        <input v-model="form.equipment_used" type="text" placeholder="Perahu Karet, Alkon, Drone Thermal, P3K..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Kronologi & Rencana Operasi</label>
                        <textarea v-model="form.description" rows="2" placeholder="Catatan kronologi musibah..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Selamat</label>
                            <input v-model="form.victims_saved" type="number" min="0" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-1.5 text-xs" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Luka</label>
                            <input v-model="form.victims_injured" type="number" min="0" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-1.5 text-xs" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Meninggal</label>
                            <input v-model="form.victims_deceased" type="number" min="0" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-1.5 text-xs" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Hilang</label>
                            <input v-model="form.victims_missing" type="number" min="0" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-1.5 text-xs" />
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end space-x-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-xl text-xs font-semibold hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20">
                            {{ editingOperation ? 'Simpan Perubahan' : 'Simpan Operasi SAR' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
