<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    meetings: Object,
    filters: Object,
    stats: Object,
    categories: Array,
    statuses: Array,
    activeMembers: Array,
});

// View toggle
const viewMode = ref('grid'); // 'grid' or 'table'

// Filters state
const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || 'Semua');
const selectedStatus = ref(props.filters.status || 'Semua');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const handleFilter = () => {
    router.get(route('meetings.index'), {
        search: search.value,
        category: selectedCategory.value,
        status: selectedStatus.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedCategory.value = 'Semua';
    selectedStatus.value = 'Semua';
    startDate.value = '';
    endDate.value = '';
    handleFilter();
};

// Detail Modal State
const isDetailModalOpen = ref(false);
const activeMeeting = ref(null);

const openDetailModal = (meeting) => {
    activeMeeting.value = meeting;
    isDetailModalOpen.value = true;
};

// Form Modal State (Add / Edit)
const isFormModalOpen = ref(false);
const editingMeeting = ref(null);

// Lock Body Scroll when any Modal is open to prevent background scroll overlap (Scroll Bleed)
watch([isDetailModalOpen, isFormModalOpen], ([detailOpen, formOpen]) => {
    if (detailOpen || formOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

// Combobox Attendance State
const isComboboxDropdownOpen = ref(false);
const comboboxSearch = ref('');
const selectedAttendees = ref([]);

const filteredActiveMembers = computed(() => {
    const members = props.activeMembers || [];
    if (!comboboxSearch.value.trim()) return members;
    const query = comboboxSearch.value.toLowerCase();
    return members.filter(m =>
        m.name.toLowerCase().includes(query) ||
        (m.role && m.role.toLowerCase().includes(query)) ||
        (m.email && m.email.toLowerCase().includes(query))
    );
});

const toggleAttendee = (memberName) => {
    const idx = selectedAttendees.value.indexOf(memberName);
    if (idx > -1) {
        selectedAttendees.value.splice(idx, 1);
    } else {
        selectedAttendees.value.push(memberName);
    }
};

const removeAttendee = (memberName) => {
    const idx = selectedAttendees.value.indexOf(memberName);
    if (idx > -1) {
        selectedAttendees.value.splice(idx, 1);
    }
};

const addCustomAttendee = () => {
    const val = comboboxSearch.value.trim();
    if (val && !selectedAttendees.value.includes(val)) {
        selectedAttendees.value.push(val);
        comboboxSearch.value = '';
    }
};

const selectAllActiveMembers = () => {
    if (!props.activeMembers) return;
    props.activeMembers.forEach(m => {
        if (!selectedAttendees.value.includes(m.name)) {
            selectedAttendees.value.push(m.name);
        }
    });
};

const clearAllAttendees = () => {
    selectedAttendees.value = [];
};

const form = useForm({
    title: '',
    meeting_date: '',
    location: '',
    category: 'Rapat Koordinasi',
    leader: '',
    notewriter: '',
    attendees_str: '',
    agenda: '',
    summary: '',
    action_items: [],
    status: 'Selesai',
    attachment: null,
});

const openAddModal = () => {
    editingMeeting.value = null;
    form.reset();
    form.clearErrors();
    form.action_items = [
        { task: '', pic: '', deadline: '', completed: false }
    ];
    form.status = 'Selesai';
    form.category = 'Rapat Koordinasi';
    selectedAttendees.value = [];
    comboboxSearch.value = '';
    isComboboxDropdownOpen.value = false;
    isFormModalOpen.value = true;
};

const openEditModal = (m) => {
    editingMeeting.value = m;
    form.clearErrors();

    // Format datetime-local (YYYY-MM-DDTHH:mm)
    let formattedDate = '';
    if (m.meeting_date) {
        const d = new Date(m.meeting_date);
        const pad = (n) => n < 10 ? '0' + n : n;
        formattedDate = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    form.title = m.title || '';
    form.meeting_date = formattedDate;
    form.location = m.location || '';
    form.category = m.category || 'Rapat Koordinasi';
    form.leader = m.leader || '';
    form.notewriter = m.notewriter || '';
    form.agenda = m.agenda || '';
    form.summary = m.summary || '';
    form.action_items = Array.isArray(m.action_items) && m.action_items.length > 0
        ? JSON.parse(JSON.stringify(m.action_items))
        : [{ task: '', pic: '', deadline: '', completed: false }];
    form.status = m.status || 'Selesai';
    form.attachment = null;

    if (Array.isArray(m.attendees)) {
        selectedAttendees.value = [...m.attendees];
    } else if (typeof m.attendees === 'string' && m.attendees) {
        selectedAttendees.value = m.attendees.split(',').map(s => s.trim()).filter(Boolean);
    } else {
        selectedAttendees.value = [];
    }
    comboboxSearch.value = '';
    isComboboxDropdownOpen.value = false;

    isFormModalOpen.value = true;
};

const addActionItemRow = () => {
    form.action_items.push({ task: '', pic: '', deadline: '', completed: false });
};

const removeActionItemRow = (index) => {
    form.action_items.splice(index, 1);
};

const submitForm = () => {
    form.attendees_str = selectedAttendees.value.join(', ');

    const payload = {
        ...form.data(),
        attendees: selectedAttendees.value,
        action_items: JSON.stringify(form.action_items.filter(item => item.task.trim() !== ''))
    };

    if (editingMeeting.value) {
        form.transform(() => payload).post(route('meetings.update', editingMeeting.value.id), {
            onSuccess: () => {
                isFormModalOpen.value = false;
                if (activeMeeting.value && activeMeeting.value.id === editingMeeting.value.id) {
                    const updated = props.meetings.data.find(x => x.id === editingMeeting.value.id);
                    if (updated) activeMeeting.value = updated;
                }
            }
        });
    } else {
        form.transform(() => payload).post(route('meetings.store'), {
            onSuccess: () => {
                isFormModalOpen.value = false;
            }
        });
    }
};

const deleteMeeting = (m) => {
    if (confirm(`Apakah Anda yakin ingin menghapus arsip rapat "${m.title}"?`)) {
        router.delete(route('meetings.destroy', m.id), {
            onSuccess: () => {
                if (activeMeeting.value && activeMeeting.value.id === m.id) {
                    isDetailModalOpen.value = false;
                }
            }
        });
    }
};

const toggleActionItemStatusInModal = (index) => {
    if (!activeMeeting.value || !activeMeeting.value.action_items) return;
    const items = [...activeMeeting.value.action_items];
    items[index].completed = !items[index].completed;

    // Send update request to server
    router.post(route('meetings.update', activeMeeting.value.id), {
        title: activeMeeting.value.title,
        meeting_date: activeMeeting.value.meeting_date,
        location: activeMeeting.value.location,
        category: activeMeeting.value.category,
        leader: activeMeeting.value.leader,
        notewriter: activeMeeting.value.notewriter,
        attendees: JSON.stringify(activeMeeting.value.attendees || []),
        agenda: activeMeeting.value.agenda,
        summary: activeMeeting.value.summary,
        status: activeMeeting.value.status,
        action_items: JSON.stringify(items),
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const printMeetingSummary = () => {
    window.print();
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

const formatShortDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).format(date);
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Selesai':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
        case 'Terjadwal':
            return 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border-sky-200 dark:border-sky-800';
        case 'Draft':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800';
        case 'Diarsipkan':
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
        default:
            return 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border-brand-200 dark:border-brand-800';
    }
};

const getCategoryColor = (category) => {
    switch (category) {
        case 'Evaluasi Bencana':
            return 'text-rose-600 bg-rose-50 dark:bg-rose-950/30 dark:text-rose-400 border-rose-100 dark:border-rose-900/40';
        case 'Rapat Koordinasi':
            return 'text-brand-600 bg-brand-50 dark:bg-brand-950/30 dark:text-brand-400 border-brand-100 dark:border-brand-900/40';
        case 'Sosialisasi Donasi':
            return 'text-amber-600 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400 border-amber-100 dark:border-amber-900/40';
        case 'Rapat Pleno':
            return 'text-indigo-600 bg-indigo-50 dark:bg-indigo-950/30 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/40';
        default:
            return 'text-teal-600 bg-teal-50 dark:bg-teal-950/30 dark:text-teal-400 border-teal-100 dark:border-teal-900/40';
    }
};
</script>

<template>
    <Head title="Arsip & Notulensi Rapat - MKT Indonesia" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <span class="text-gray-400 dark:text-gray-500">Menu /</span>
                <span class="font-bold text-gray-800 dark:text-gray-100">Arsip & Notulensi Rapat</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Top Hero Banner & Actions -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-brand-500 via-amber-500 to-orange-500 p-6 md:p-8 rounded-3xl text-white shadow-xl shadow-brand-500/15 relative overflow-hidden">
                <!-- Background decorative elements -->
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-1/3 -top-10 w-40 h-40 bg-amber-300/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 space-y-1">
                    <div class="inline-flex items-center space-x-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium tracking-wide text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Dokumentasi Organisasi MKT</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Arsip Rapat & Notulensi Digital</h1>
                    <p class="text-brand-100 text-sm max-w-2xl leading-relaxed">
                        Pusat akses dokumen hasil keputusan rapat, daftar peserta, notulensi, dan pemantauan rencana tindak lanjut (action items) untuk seluruh pengurus dan relawan MKT.
                    </p>
                </div>

                <div class="relative z-10 shrink-0 flex items-center space-x-3">
                    <button
                        @click="openAddModal"
                        class="px-5 py-3 rounded-2xl bg-white text-brand-700 font-bold hover:bg-brand-50 transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-[1.02] flex items-center space-x-2 text-sm focus:outline-none"
                    >
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>+ Tambah Notulensi Rapat</span>
                    </button>
                </div>
            </div>

            <!-- Stats Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Stat Card 1 -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Arsip Rapat</p>
                            <h3 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 mt-1">{{ stats.totalMeetings }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/50 flex items-center justify-center text-brand-600 dark:text-brand-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Dokumen tersimpan aman di database MKT
                    </div>
                </div>

                <!-- Stat Card 2 -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Rapat Bulan Ini</p>
                            <h3 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 mt-1">{{ stats.thisMonthCount }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-amber-600 dark:text-amber-400 font-medium">
                        Aktivitas koordinasi terkini
                    </div>
                </div>

                <!-- Stat Card 3 -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Action Items (Tindak Lanjut)</p>
                            <h3 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 mt-1">
                                {{ stats.completedActionItems }} <span class="text-sm font-normal text-gray-400">/ {{ stats.totalActionItems }} Selesai</span>
                            </h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                        <div
                            class="bg-emerald-500 h-full rounded-full transition-all duration-500"
                            :style="{ width: stats.totalActionItems ? Math.round((stats.completedActionItems / stats.totalActionItems) * 100) + '%' : '0%' }"
                        ></div>
                    </div>
                </div>

                <!-- Stat Card 4 -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Aksesibilitas User</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">Publik Internal</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/50 flex items-center justify-center text-sky-600 dark:text-sky-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-sky-600 dark:text-sky-400 font-medium">
                        Dapat diakses oleh seluruh anggota & relawan
                    </div>
                </div>
            </div>

            <!-- Filters & Search Toolbar -->
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input
                            v-model="search"
                            @input="handleFilter"
                            type="text"
                            placeholder="Cari berdasarkan judul, pimpinan rapat, notulis, lokasi, atau notulensi..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                        />
                    </div>

                    <!-- Category & Status Filters -->
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model="selectedCategory"
                            @change="handleFilter"
                            class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-brand-500 transition"
                        >
                            <option value="Semua">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>

                        <select
                            v-model="selectedStatus"
                            @change="handleFilter"
                            class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-brand-500 transition"
                        >
                            <option value="Semua">Semua Status</option>
                            <option v-for="st in statuses" :key="st" :value="st">{{ st }}</option>
                        </select>

                        <button
                            v-if="search || selectedCategory !== 'Semua' || selectedStatus !== 'Semua' || startDate || endDate"
                            @click="clearFilters"
                            class="px-3 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 transition"
                        >
                            Reset Filter
                        </button>

                        <!-- View Toggle -->
                        <div class="flex items-center p-1 rounded-xl bg-gray-100 dark:bg-gray-800">
                            <button
                                @click="viewMode = 'grid'"
                                :class="[
                                    viewMode === 'grid' ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200',
                                    'p-2 rounded-lg transition-all focus:outline-none'
                                ]"
                                title="Tampilan Kartu Grid"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                                </svg>
                            </button>
                            <button
                                @click="viewMode = 'table'"
                                :class="[
                                    viewMode === 'table' ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200',
                                    'p-2 rounded-lg transition-all focus:outline-none'
                                ]"
                                title="Tampilan Tabel"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area: Grid or Table View -->
            <div v-if="meetings.data && meetings.data.length > 0">
                <!-- GRID VIEW -->
                <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                    <div
                        v-for="m in meetings.data"
                        :key="m.id"
                        class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden group hover:border-brand-200 dark:hover:border-brand-900/50"
                    >
                        <div class="p-6 space-y-4">
                            <!-- Category & Status Header -->
                            <div class="flex items-center justify-between gap-2">
                                <span :class="['px-3 py-1 rounded-full text-xs font-semibold border', getCategoryColor(m.category)]">
                                    {{ m.category }}
                                </span>
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-bold border', getStatusBadgeClass(m.status)]">
                                    {{ m.status }}
                                </span>
                            </div>

                            <!-- Title -->
                            <h2
                                @click="openDetailModal(m)"
                                class="text-lg font-bold text-gray-800 dark:text-gray-100 hover:text-brand-600 dark:hover:text-brand-400 cursor-pointer transition line-clamp-2"
                            >
                                {{ m.title }}
                            </h2>

                            <!-- Date & Location -->
                            <div class="space-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ formatDate(m.meeting_date) }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="truncate">{{ m.location || 'Lokasi Belum Ditentukan' }}</span>
                                </div>
                            </div>

                            <!-- Leaders & Notewriter -->
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-gray-800 text-xs">
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Pimpinan Rapat</span>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300 truncate block">{{ m.leader || '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Notulis</span>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300 truncate block">{{ m.notewriter || '-' }}</span>
                                </div>
                            </div>

                            <!-- Summary Preview -->
                            <div class="bg-gray-50 dark:bg-gray-800/60 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-800">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Ringkasan Keputusan:</span>
                                <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-3 leading-relaxed">
                                    {{ m.summary || 'Belum ada notulensi tertulis.' }}
                                </p>
                            </div>

                            <!-- Action Items Progress Bar -->
                            <div v-if="Array.isArray(m.action_items) && m.action_items.length > 0" class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 font-medium">Tindak Lanjut (Action Items)</span>
                                    <span class="font-bold text-brand-600 dark:text-brand-400">
                                        {{ m.action_items.filter(i => i.completed).length }} / {{ m.action_items.length }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
                                    <div
                                        class="bg-brand-500 h-full rounded-full transition-all duration-300"
                                        :style="{ width: Math.round((m.action_items.filter(i => i.completed).length / m.action_items.length) * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer & Actions -->
                        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <button
                                @click="openDetailModal(m)"
                                class="inline-flex items-center space-x-1.5 text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                            >
                                <span>Lihat Notulensi Lengkap</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>

                            <div class="flex items-center space-x-2">
                                <button
                                    @click="openEditModal(m)"
                                    class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30 rounded-xl transition"
                                    title="Edit Notulensi"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button
                                    @click="deleteMeeting(m)"
                                    class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition"
                                    title="Hapus Notulensi"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE VIEW -->
                <div v-else class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800/70 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th class="px-6 py-4">Judul Rapat</th>
                                    <th class="px-6 py-4">Tanggal & Lokasi</th>
                                    <th class="px-6 py-4">Kategori</th>
                                    <th class="px-6 py-4">Pimpinan / Notulis</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Tindak Lanjut</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-for="m in meetings.data" :key="m.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                    <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-100">
                                        <button @click="openDetailModal(m)" class="hover:text-brand-600 dark:hover:text-brand-400 text-left line-clamp-2">
                                            {{ m.title }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-xs space-y-1">
                                        <div class="font-medium text-gray-700 dark:text-gray-300">{{ formatShortDate(m.meeting_date) }}</div>
                                        <div class="text-gray-400 truncate max-w-xs">{{ m.location || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold border', getCategoryColor(m.category)]">
                                            {{ m.category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs space-y-0.5">
                                        <div class="font-medium text-gray-700 dark:text-gray-300"><span class="text-gray-400">P:</span> {{ m.leader || '-' }}</div>
                                        <div class="text-gray-500"><span class="text-gray-400">N:</span> {{ m.notewriter || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-2.5 py-1 rounded-full text-xs font-bold border', getStatusBadgeClass(m.status)]">
                                            {{ m.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <div v-if="Array.isArray(m.action_items) && m.action_items.length > 0">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">
                                                {{ m.action_items.filter(i => i.completed).length }}/{{ m.action_items.length }} Selesai
                                            </span>
                                        </div>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button
                                                @click="openDetailModal(m)"
                                                class="px-3 py-1.5 rounded-xl bg-brand-50 text-brand-600 hover:bg-brand-100 dark:bg-brand-950/40 dark:text-brand-300 text-xs font-semibold transition"
                                            >
                                                Detail
                                            </button>
                                            <button
                                                @click="openEditModal(m)"
                                                class="p-1.5 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button
                                                @click="deleteMeeting(m)"
                                                class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="meetings.links && meetings.links.length > 3" class="flex justify-center mt-8">
                    <div class="flex flex-wrap gap-1 bg-white dark:bg-gray-900 p-1.5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <button
                            v-for="(link, key) in meetings.links"
                            :key="key"
                            @click="link.url && router.get(link.url)"
                            :disabled="!link.url"
                            v-html="link.label"
                            :class="[
                                link.active ? 'bg-brand-500 text-white font-bold' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800',
                                'px-3.5 py-2 rounded-xl text-xs transition focus:outline-none disabled:opacity-40'
                            ]"
                        ></button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white dark:bg-gray-900 p-12 rounded-3xl border border-gray-100 dark:border-gray-800 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-brand-50 dark:bg-brand-950/40 text-brand-500 mx-auto flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Belum ada arsip rapat</h3>
                    <p class="text-sm text-gray-400 mt-1">Tidak ada dokumen notulensi yang cocok dengan kriteria pencarian Anda.</p>
                </div>
                <button
                    @click="openAddModal"
                    class="px-4 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm transition inline-flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Buat Arsip Notulensi Baru</span>
                </button>
            </div>
        </div>

        <!-- ================= DETAIL MODAL (DRAWER / VIEW NOTULENSI) ================= -->
        <div v-if="isDetailModalOpen && activeMeeting" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 backdrop-blur-sm" @click.self="isDetailModalOpen = false">
            <div class="bg-white dark:bg-gray-900 w-full max-w-3xl rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[85vh] my-auto">
                <!-- Modal Header (Fixed Top) -->
                <div class="p-6 bg-gradient-to-r from-brand-500/10 via-amber-500/5 to-transparent border-b border-gray-100 dark:border-gray-800 flex items-start justify-between shrink-0">
                    <div class="space-y-1 pr-4">
                        <div class="flex items-center space-x-2">
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold border', getCategoryColor(activeMeeting.category)]">
                                {{ activeMeeting.category }}
                            </span>
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold border', getStatusBadgeClass(activeMeeting.status)]">
                                {{ activeMeeting.status }}
                            </span>
                        </div>
                        <h2 class="text-xl font-extrabold text-gray-800 dark:text-gray-100">{{ activeMeeting.title }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ formatDate(activeMeeting.meeting_date) }} • {{ activeMeeting.location || 'Lokasi tidak dispesifikasikan' }}
                        </p>
                    </div>
                    <button @click="isDetailModalOpen = false" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body (Smooth Scrollable Area) -->
                <div class="p-6 overflow-y-auto space-y-6 flex-1 text-sm scrollbar-thin">
                    <!-- Officers & Participants -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Pimpinan Rapat</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ activeMeeting.leader || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Notulis</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ activeMeeting.notewriter || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Dibuat Oleh</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ activeMeeting.creator ? activeMeeting.creator.name : 'Sistem MKT' }}</span>
                        </div>
                    </div>

                    <!-- Attendees list -->
                    <div v-if="Array.isArray(activeMeeting.attendees) && activeMeeting.attendees.length > 0">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Daftar Kehadiran / Peserta</h4>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="(att, idx) in activeMeeting.attendees"
                                :key="idx"
                                class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-xs text-gray-700 dark:text-gray-300 font-medium"
                            >
                                👤 {{ att }}
                            </span>
                        </div>
                    </div>

                    <!-- Agenda -->
                    <div v-if="activeMeeting.agenda">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Agenda Rapat</h4>
                        <div class="p-4 rounded-2xl bg-brand-50/40 dark:bg-brand-950/20 border border-brand-100/50 dark:border-brand-900/30 text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                            {{ activeMeeting.agenda }}
                        </div>
                    </div>

                    <!-- Summary / Notulensi -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Notulensi & Hasil Keputusan</h4>
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-800 dark:text-gray-200 whitespace-pre-line leading-relaxed shadow-sm">
                            {{ activeMeeting.summary || 'Belum ada notulensi tertulis.' }}
                        </div>
                    </div>

                    <!-- Action Items / Checklist -->
                    <div v-if="Array.isArray(activeMeeting.action_items) && activeMeeting.action_items.length > 0">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rencana Tindak Lanjut (Action Items)</h4>
                            <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">
                                {{ activeMeeting.action_items.filter(i => i.completed).length }}/{{ activeMeeting.action_items.length }} Selesai
                            </span>
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="(item, idx) in activeMeeting.action_items"
                                :key="idx"
                                class="flex items-center justify-between p-3 rounded-xl border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition cursor-pointer"
                                @click="toggleActionItemStatusInModal(idx)"
                            >
                                <div class="flex items-center space-x-3">
                                    <input
                                        type="checkbox"
                                        :checked="item.completed"
                                        @change.stop="toggleActionItemStatusInModal(idx)"
                                        class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500 cursor-pointer"
                                    />
                                    <span :class="[item.completed ? 'line-through text-gray-400' : 'text-gray-800 dark:text-gray-200 font-medium']">
                                        {{ item.task }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3 text-xs">
                                    <span v-if="item.pic" class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                        PIC: {{ item.pic }}
                                    </span>
                                    <span v-if="item.deadline" class="text-gray-400">
                                        DL: {{ item.deadline }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachment -->
                    <div v-if="activeMeeting.attachment_path" class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-800 dark:text-gray-200">Dokumen Lampiran Notulensi</h5>
                                <p class="text-xs text-gray-500">Berkas pendukung atau foto dokumentasi rapat</p>
                            </div>
                        </div>
                        <a
                            :href="activeMeeting.attachment_path"
                            target="_blank"
                            class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs transition shadow-sm"
                        >
                            Unduh Dokumen
                        </a>
                    </div>
                </div>

                <!-- Modal Footer (Fixed Bottom) -->
                <div class="p-6 bg-gray-50/60 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                    <button
                        @click="printMeetingSummary"
                        class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-xs transition flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Cetak / Print Ringkasan</span>
                    </button>

                    <div class="flex items-center space-x-2">
                        <button
                            @click="isDetailModalOpen = false; openEditModal(activeMeeting)"
                            class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 font-semibold text-xs hover:bg-amber-100 transition"
                        >
                            Edit Notulensi
                        </button>
                        <button
                            @click="isDetailModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-xs hover:bg-gray-300 dark:hover:bg-gray-700 transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= FORM SLIDE-OVER MODAL (TAMBAH / EDIT NOTULENSI) ================= -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isFormModalOpen" class="fixed inset-0 z-50 overflow-hidden">
                <!-- Backdrop overlay -->
                <div
                    @click="isFormModalOpen = false"
                    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                ></div>

                <!-- Slide Over Panel Container -->
                <div class="fixed inset-y-0 right-0 max-w-full flex pl-10 z-10">
                    <form @submit.prevent="submitForm" class="w-screen max-w-2xl bg-white dark:bg-gray-900 shadow-2xl border-l border-gray-100 dark:border-gray-800 flex flex-col h-full overflow-hidden">
                        <!-- Form Header (Fixed Top) -->
                        <div class="px-6 py-5 bg-gradient-to-r from-brand-500 via-amber-500 to-orange-500 text-white flex items-center justify-between shrink-0 shadow-md">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-semibold text-white tracking-wide uppercase">Formulir Digital</span>
                                </div>
                                <h2 class="text-xl font-extrabold mt-0.5">{{ editingMeeting ? 'Edit Arsip Notulensi Rapat' : 'Buat Notulensi Rapat Baru' }}</h2>
                                <p class="text-xs text-brand-100">Lengkapi data pelaksanaan, peserta, agenda, notulensi & action items</p>
                            </div>
                            <button
                                type="button"
                                @click="isFormModalOpen = false"
                                class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition focus:outline-none"
                                title="Tutup Panel"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Form Content Body (Smooth Scrollable Container) -->
                        <div class="p-6 overflow-y-auto flex-1 space-y-5 text-sm scrollbar-thin">
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul Rapat <span class="text-rose-500">*</span></label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    placeholder="Contoh: Rapat Koordinasi Posko Tanggap Bencana"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                />
                                <span v-if="form.errors.title" class="text-xs text-rose-500 mt-1 block">{{ form.errors.title }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal & Waktu <span class="text-rose-500">*</span></label>
                                    <input
                                        v-model="form.meeting_date"
                                        type="datetime-local"
                                        required
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                    />
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori Rapat <span class="text-rose-500">*</span></label>
                                    <select
                                        v-model="form.category"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                    >
                                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Lokasi / Media Rapat</label>
                                    <input
                                        v-model="form.location"
                                        type="text"
                                        placeholder="Ruang Rapat MKT / Zoom Meeting"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                    />
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Status Rapat</label>
                                    <select
                                        v-model="form.status"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                    >
                                        <option v-for="st in statuses" :key="st" :value="st">{{ st }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Pimpinan Rapat</label>
                                    <input
                                        v-model="form.leader"
                                        type="text"
                                        placeholder="Nama Pimpinan / Chair"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                    />
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Notulis</label>
                                    <input
                                        v-model="form.notewriter"
                                        type="text"
                                        placeholder="Nama Notulis"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                    />
                                </div>
                            </div>

                            <!-- Combobox Daftar Peserta / Kehadiran -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300">Daftar Peserta / Kehadiran Rapat</label>
                                    <div class="flex items-center space-x-2 text-[11px]">
                                        <button
                                            type="button"
                                            @click="selectAllActiveMembers"
                                            class="text-brand-600 dark:text-brand-400 font-semibold hover:underline"
                                        >
                                            + Pilih Semua Aktif
                                        </button>
                                        <span class="text-gray-300">•</span>
                                        <button
                                            type="button"
                                            @click="clearAllAttendees"
                                            class="text-rose-500 font-semibold hover:underline"
                                        >
                                            Reset
                                        </button>
                                    </div>
                                </div>

                                <!-- Selected Attendees Badges Container -->
                                <div class="p-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/60 min-h-[52px] space-y-2">
                                    <div v-if="selectedAttendees.length > 0" class="flex flex-wrap gap-1.5">
                                        <span
                                            v-for="name in selectedAttendees"
                                            :key="name"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 text-white text-xs font-semibold shadow-sm transition"
                                        >
                                            <span>👤 {{ name }}</span>
                                            <button
                                                type="button"
                                                @click="removeAttendee(name)"
                                                class="hover:bg-black/20 p-0.5 rounded-full text-white/90 hover:text-white transition focus:outline-none"
                                                title="Hapus Peserta"
                                            >
                                                ✕
                                            </button>
                                        </span>
                                    </div>
                                    <p v-else class="text-xs text-gray-400 italic">Belum ada peserta yang dipilih. Cari dan pilih dari daftar anggota aktif MKT di bawah ini.</p>

                                    <!-- Combobox Search Input & Dropdown -->
                                    <div class="relative mt-2">
                                        <div class="relative flex items-center">
                                            <input
                                                v-model="comboboxSearch"
                                                type="text"
                                                @focus="isComboboxDropdownOpen = true"
                                                @keydown.enter.prevent="addCustomAttendee"
                                                placeholder="Cari anggota aktif atau ketik nama baru lalu tekan Enter..."
                                                class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-brand-500 transition"
                                            />
                                            <span class="absolute left-3 text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </span>
                                            <button
                                                type="button"
                                                @click="isComboboxDropdownOpen = !isComboboxDropdownOpen"
                                                class="absolute right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 focus:outline-none"
                                            >
                                                <svg :class="[isComboboxDropdownOpen ? 'rotate-180' : 'rotate-0', 'w-4 h-4 transition-transform duration-200']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Combobox Dropdown Options List -->
                                        <div
                                            v-if="isComboboxDropdownOpen"
                                            class="absolute left-0 right-0 mt-1 z-30 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-xl max-h-60 overflow-y-auto p-1.5 space-y-1 scrollbar-thin"
                                        >
                                            <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider flex justify-between items-center border-b border-gray-100 dark:border-gray-800">
                                                <span>Anggota & Relawan Aktif ({{ activeMembers ? activeMembers.length : 0 }})</span>
                                                <button type="button" @click="isComboboxDropdownOpen = false" class="text-brand-600 dark:text-brand-400 hover:underline">Selesai</button>
                                            </div>

                                            <div
                                                v-for="member in filteredActiveMembers"
                                                :key="member.id"
                                                @click="toggleAttendee(member.name)"
                                                :class="[
                                                    selectedAttendees.includes(member.name)
                                                        ? 'bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 font-semibold'
                                                        : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800/60',
                                                    'px-3 py-2 rounded-xl text-xs flex items-center justify-between cursor-pointer transition'
                                                ]"
                                            >
                                                <div class="flex items-center space-x-2.5 truncate">
                                                    <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-brand-400 to-amber-500 flex items-center justify-center font-bold text-white text-[10px] shrink-0">
                                                        {{ member.name.charAt(0) }}
                                                    </div>
                                                    <div class="truncate">
                                                        <span class="block truncate">{{ member.name }}</span>
                                                        <span class="text-[10px] text-gray-400 block truncate">{{ member.role }}</span>
                                                    </div>
                                                </div>
                                                <svg v-if="selectedAttendees.includes(member.name)" class="w-4 h-4 text-brand-600 dark:text-brand-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>

                                            <div v-if="filteredActiveMembers.length === 0" class="p-3 text-center text-xs text-gray-400">
                                                Tidak ada anggota aktif yang cocok.
                                            </div>

                                            <!-- Add Custom Attendee option -->
                                            <div v-if="comboboxSearch.trim() && !selectedAttendees.includes(comboboxSearch.trim())" class="pt-1 border-t border-gray-100 dark:border-gray-800">
                                                <button
                                                    type="button"
                                                    @click="addCustomAttendee"
                                                    class="w-full px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-xs font-semibold hover:bg-amber-100 text-left transition flex items-center space-x-2"
                                                >
                                                    <span>➕ Tambah "{{ comboboxSearch }}" sebagai peserta</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Agenda Rapat</label>
                                <textarea
                                    v-model="form.agenda"
                                    rows="3"
                                    placeholder="Rincian poin agenda pembahasan..."
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Notulensi / Ringkasan Hasil Keputusan</label>
                                <textarea
                                    v-model="form.summary"
                                    rows="5"
                                    placeholder="Tuliskan poin-poin penting hasil keputusan rapat..."
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                ></textarea>
                            </div>

                            <!-- Dynamic Action Items -->
                            <div class="bg-gray-50 dark:bg-gray-800/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-gray-800 dark:text-gray-200 text-xs uppercase tracking-wider">Rencana Tindak Lanjut (Action Items)</label>
                                    <button
                                        type="button"
                                        @click="addActionItemRow"
                                        class="px-2.5 py-1 rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400 text-xs font-bold hover:bg-brand-100 transition"
                                    >
                                        + Tambah Task
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <div
                                        v-for="(item, idx) in form.action_items"
                                        :key="idx"
                                        class="flex items-center space-x-2 bg-white dark:bg-gray-900 p-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm"
                                    >
                                        <input
                                            v-model="item.task"
                                            type="text"
                                            placeholder="Deskripsi tugas / instruksi"
                                            class="flex-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs p-2 text-gray-800 dark:text-gray-100"
                                        />
                                        <input
                                            v-model="item.pic"
                                            type="text"
                                            placeholder="PIC / Penanggung Jawab"
                                            class="w-32 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs p-2 text-gray-800 dark:text-gray-100"
                                        />
                                        <input
                                            v-model="item.deadline"
                                            type="date"
                                            class="w-32 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs p-2 text-gray-800 dark:text-gray-100"
                                        />
                                        <button
                                            type="button"
                                            @click="removeActionItemRow(idx)"
                                            class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition"
                                            title="Hapus Task"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Attachment Upload -->
                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Unggah Lampiran / Dokumen Pendukung</label>
                                <input
                                    type="file"
                                    @change="(e) => form.attachment = e.target.files[0]"
                                    accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                                    class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 dark:file:bg-brand-950 dark:file:text-brand-300 hover:file:bg-brand-100"
                                />
                            </div>
                        </div>

                        <!-- Submit Buttons Footer (Sticky Fixed Bottom) -->
                        <div class="px-6 py-4 bg-gray-50/90 dark:bg-gray-800/90 backdrop-blur-md border-t border-gray-100 dark:border-gray-800 flex items-center justify-end space-x-3 shrink-0">
                            <button
                                type="button"
                                @click="isFormModalOpen = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : (editingMeeting ? 'Simpan Perubahan' : 'Simpan Notulensi') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
