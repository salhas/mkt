<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import axios from 'axios';

const props = defineProps({
    meetings: Object,
    nextUpcomingMeeting: Object,
    filters: Object,
    stats: Object,
    categories: Array,
    statuses: Array,
    activeMembers: Array,
});

// View toggle
const viewMode = ref('grid'); // 'grid' or 'table'

// Active Tab: 'semua', 'agenda', 'arsip'
const activeTab = ref(props.filters?.tab || 'semua');

// Filters state
const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || 'Semua');
const selectedStatus = ref(props.filters?.status || 'Semua');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const switchTab = (tabName) => {
    activeTab.value = tabName;
    router.get(route('meetings.index'), {
        tab: tabName,
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

const handleFilter = () => {
    router.get(route('meetings.index'), {
        tab: activeTab.value,
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
const formMode = ref('agenda'); // 'agenda' | 'notulensi'

// QR Presensi & Kehadiran State
const isQrModalOpen = ref(false);
const selectedMeetingForQr = ref(null);
const qrCodeDataUrl = ref('');
const isGeneratingQr = ref(false);
const copiedLink = ref(false);
const activeQrTab = ref('qrcode'); // 'qrcode' | 'attendees'
const attendeesList = ref([]);
const isLoadingAttendees = ref(false);
const attendanceSearch = ref('');
const previewSignatureUrl = ref(null);

// Lock Body Scroll when any Modal is open to prevent background scroll overlap (Scroll Bleed)
watch([isDetailModalOpen, isFormModalOpen, isQrModalOpen], ([detailOpen, formOpen, qrOpen]) => {
    if (detailOpen || formOpen || qrOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

const filteredAttendees = computed(() => {
    if (!attendanceSearch.value.trim()) return attendeesList.value;
    const q = attendanceSearch.value.toLowerCase();
    return attendeesList.value.filter(a =>
        (a.name && a.name.toLowerCase().includes(q)) ||
        (a.phone && a.phone.toLowerCase().includes(q)) ||
        (a.institution && a.institution.toLowerCase().includes(q)) ||
        (a.position && a.position.toLowerCase().includes(q)) ||
        (a.email && a.email.toLowerCase().includes(q))
    );
});

const getAttendancePublicUrl = (meeting) => {
    if (!meeting || !meeting.attendance_token) return '';
    return route('public.attendance.show', meeting.attendance_token);
};

const openQrModal = async (meeting) => {
    selectedMeetingForQr.value = { ...meeting };
    isQrModalOpen.value = true;
    activeQrTab.value = 'qrcode';
    copiedLink.value = false;
    isGeneratingQr.value = true;

    try {
        const res = await axios.get(route('meetings.attendances.data', meeting.id));
        if (res.data?.success) {
            attendeesList.value = res.data.attendances || [];
            if (res.data.meeting) {
                selectedMeetingForQr.value.attendance_token = res.data.meeting.attendance_token;
                selectedMeetingForQr.value.is_attendance_open = res.data.meeting.is_attendance_open;
            }
        }

        const publicUrl = res.data?.meeting?.public_url || getAttendancePublicUrl(selectedMeetingForQr.value);

        qrCodeDataUrl.value = await QRCode.toDataURL(publicUrl, {
            width: 400,
            margin: 2,
            color: {
                dark: '#0f172a',
                light: '#ffffff',
            },
        });
    } catch (err) {
        console.error('Error generating QR code:', err);
    } finally {
        isGeneratingQr.value = false;
    }
};

const copyAttendanceLink = async () => {
    const url = getAttendancePublicUrl(selectedMeetingForQr.value);
    if (!url) return;
    try {
        await navigator.clipboard.writeText(url);
        copiedLink.value = true;
        setTimeout(() => {
            copiedLink.value = false;
        }, 2500);
    } catch (err) {
        const textarea = document.createElement('textarea');
        textarea.value = url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        copiedLink.value = true;
        setTimeout(() => {
            copiedLink.value = false;
        }, 2500);
    }
};

const downloadQrImage = () => {
    if (!qrCodeDataUrl.value) return;
    const a = document.createElement('a');
    a.href = qrCodeDataUrl.value;
    const cleanTitle = (selectedMeetingForQr.value?.title || 'Agenda')
        .replace(/[^a-zA-Z0-9]/g, '-')
        .substring(0, 30);
    a.download = `QR-Presensi-${cleanTitle}.png`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
};

const toggleMeetingAttendance = () => {
    if (!selectedMeetingForQr.value) return;
    router.patch(route('meetings.attendances.toggle', selectedMeetingForQr.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            selectedMeetingForQr.value.is_attendance_open = !selectedMeetingForQr.value.is_attendance_open;
        }
    });
};

const deleteAttendee = (attendee) => {
    if (!confirm(`Hapus catatan kehadiran untuk "${attendee.name}"?`)) return;
    router.delete(route('meetings.attendances.destroy', attendee.id), {
        preserveScroll: true,
        onSuccess: () => {
            attendeesList.value = attendeesList.value.filter(a => a.id !== attendee.id);
        }
    });
};

const exportAttendeesCsv = () => {
    if (!selectedMeetingForQr.value) return;
    window.location.href = route('meetings.attendances.export', selectedMeetingForQr.value.id);
};

const printQrStandee = () => {
    if (!selectedMeetingForQr.value) return;
    const meeting = selectedMeetingForQr.value;
    const printWindow = window.open('', '_blank', 'width=800,height=900');
    if (!printWindow) return;

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>QR Presensi - ${meeting.title || 'Agenda MKT'}</title>
            <style>
                @page { size: A4 portrait; margin: 15mm; }
                * { box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                    text-align: center;
                    padding: 20px;
                    color: #0f172a;
                    margin: 0;
                    background: #f8fafc;
                }
                .standee-container {
                    border: 4px solid #ea580c;
                    border-radius: 28px;
                    padding: 40px 30px;
                    max-width: 620px;
                    margin: 0 auto;
                    background: #ffffff;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
                }
                .logo-img { max-height: 80px; margin-bottom: 12px; }
                .org-title { font-size: 18px; font-weight: 900; color: #ea580c; text-transform: uppercase; letter-spacing: 2px; }
                .org-subtitle { font-size: 12px; color: #64748b; margin-top: 4px; font-weight: 500; }
                .divider { height: 2px; background: linear-gradient(to right, transparent, #ea580c, transparent); margin: 24px auto; width: 80%; }
                .category-badge { display: inline-block; background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; padding: 4px 16px; border-radius: 999px; font-size: 13px; font-weight: 800; text-transform: uppercase; margin-bottom: 14px; }
                .meeting-title { font-size: 24px; font-weight: 900; line-height: 1.35; margin: 0 0 16px 0; color: #0f172a; }
                .meta-box { background: #f8fafc; border-radius: 16px; padding: 14px 20px; font-size: 13px; color: #334155; margin-bottom: 24px; line-height: 1.6; border: 1px solid #e2e8f0; }
                .qr-frame { background: #ffffff; border: 2px dashed #ea580c; border-radius: 24px; padding: 20px; display: inline-block; margin-bottom: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
                .qr-frame img { width: 300px; height: 300px; display: block; margin: 0 auto; }
                .scan-headline { font-size: 17px; font-weight: 900; color: #ea580c; margin-bottom: 6px; letter-spacing: 0.5px; }
                .scan-subtext { font-size: 13px; color: #475569; max-width: 440px; margin: 0 auto; }
                .footer-text { margin-top: 32px; font-size: 11px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 16px; }
            </style>
        </head>
        <body>
            <div class="standee-container">
                <img src="/storage/logo/mkt.png" class="logo-img" onerror="this.style.display='none'" />
                <div class="org-title">Yayasan MKT Indonesia</div>
                <div class="org-subtitle">Mitra Kemanusiaan Terpadu • Siaga & Tanggap Bencana</div>
                <div class="divider"></div>
                <div class="category-badge">${meeting.category || 'Presensi Resmi'}</div>
                <h1 class="meeting-title">${meeting.title || 'Agenda Kegiatan'}</h1>
                <div class="meta-box">
                    📅 <strong>${formatDate(meeting.meeting_date)}</strong> &nbsp;•&nbsp; 📍 <strong>${meeting.location || 'Posko MKT Indonesia'}</strong>
                </div>
                <div class="qr-frame">
                    <img src="${qrCodeDataUrl.value}" />
                </div>
                <div class="scan-headline">📲 SCAN QR CODE UNTUK PRESENSI</div>
                <div class="scan-subtext">Arahkan kamera smartphone atau scan via WhatsApp untuk mengisi daftar hadir dan tanda tangan digital.</div>
                <div class="footer-text">Sistem E-Presensi & Arsip Digital Terpadu © Yayasan MKT Indonesia</div>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() { window.close(); }, 500);
                };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
};

const printAttendanceSheet = () => {
    if (!selectedMeetingForQr.value) return;
    const meeting = selectedMeetingForQr.value;
    const attendees = attendeesList.value || [];
    const printWindow = window.open('', '_blank', 'width=900,height=1000');
    if (!printWindow) return;

    const rowsHtml = attendees.length > 0
        ? attendees.map((a, idx) => `
            <tr>
                <td style="text-align: center; font-family: monospace;">${idx + 1}</td>
                <td><strong>${a.name}</strong></td>
                <td>${a.institution || '-'}</td>
                <td>${a.position || '-'}</td>
                <td style="font-family: monospace;">${a.phone || '-'}</td>
                <td style="text-align: center; vertical-align: middle;">
                    ${a.signature ? `<img src="${a.signature}" style="max-height: 38px; max-width: 90px; object-fit: contain;" />` : '<span style="color:#94a3b8; font-size: 11px;">-</span>'}
                </td>
            </tr>
        `).join('')
        : Array.from({ length: 15 }).map((_, idx) => `
            <tr style="height: 36px;">
                <td style="text-align: center; font-family: monospace;">${idx + 1}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        `).join('');

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Daftar Hadir - ${meeting.title || 'Agenda MKT'}</title>
            <style>
                @page { size: A4 portrait; margin: 15mm 12mm; }
                * { box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                    color: #0f172a;
                    margin: 0;
                    padding: 10px;
                    font-size: 12px;
                }
                .header {
                    display: flex;
                    align-items: center;
                    border-bottom: 2px solid #ea580c;
                    padding-bottom: 12px;
                    margin-bottom: 16px;
                }
                .logo-img { height: 60px; margin-right: 16px; }
                .org-info { flex: 1; }
                .org-name { font-size: 16px; font-weight: 900; color: #ea580c; text-transform: uppercase; letter-spacing: 1px; }
                .org-desc { font-size: 11px; color: #64748b; margin-top: 2px; }
                .doc-title { text-align: center; margin: 14px 0 4px 0; font-size: 16px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
                .meeting-title { text-align: center; font-size: 14px; font-weight: 800; color: #ea580c; margin-bottom: 16px; }
                .meta-table { width: 100%; margin-bottom: 16px; font-size: 12px; border-collapse: collapse; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
                .meta-table td { padding: 6px 10px; }
                .meta-table td.label { width: 120px; font-weight: 700; color: #475569; }
                .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
                .table-data th, .table-data td { border: 1px solid #cbd5e1; padding: 6px 8px; }
                .table-data th { background: #f1f5f9; font-weight: 800; text-align: left; text-transform: uppercase; font-size: 10px; color: #334155; }
                .footer { margin-top: 24px; display: flex; justify-content: space-between; font-size: 11px; color: #475569; }
                .sig-box { width: 220px; text-align: center; }
                .sig-space { height: 60px; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="/storage/logo/mkt.png" class="logo-img" onerror="this.style.display='none'" />
                <div class="org-info">
                    <div class="org-name">Yayasan MKT Indonesia</div>
                    <div class="org-desc">Mitra Kemanusiaan Terpadu • Penanggulangan & Siaga Bencana</div>
                </div>
            </div>
            <div class="doc-title">DAFTAR HADIR / PRESENSI KEGIATAN</div>
            <div class="meeting-title">${meeting.title || 'Agenda Kegiatan'}</div>
            <table class="meta-table">
                <tr>
                    <td class="label">Hari / Tanggal</td>
                    <td>: ${formatDate(meeting.meeting_date)}</td>
                    <td class="label">Kategori</td>
                    <td>: ${meeting.category || 'Rapat Koordinasi'}</td>
                </tr>
                <tr>
                    <td class="label">Waktu / Lokasi</td>
                    <td>: ${meeting.location || 'Posko MKT Indonesia'}</td>
                    <td class="label">Total Hadir</td>
                    <td>: ${attendees.length} Orang</td>
                </tr>
            </table>
            <table class="table-data">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">No</th>
                        <th>Nama Lengkap</th>
                        <th>Instansi / Lembaga</th>
                        <th>Jabatan / Peran</th>
                        <th>No. Kontak / HP</th>
                        <th style="width: 120px; text-align: center;">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml}
                </tbody>
            </table>
            <div class="footer">
                <div>Dicetak dari Sistem E-Presensi MKT: ${new Date().toLocaleString('id-ID')}</div>
                <div class="sig-box">
                    <div>Penanggung Jawab Acara,</div>
                    <div class="sig-space"></div>
                    <div style="font-weight: 700; text-decoration: underline;">${meeting.leader || '( ........................................ )'}</div>
                </div>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() { window.close(); }, 500);
                };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
};

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
    status: 'Terjadwal',
    attachment: null,
});

const openAddModal = (mode = 'agenda') => {
    editingMeeting.value = null;
    formMode.value = mode;
    form.reset();
    form.clearErrors();
    form.action_items = [
        { task: '', pic: '', deadline: '', completed: false }
    ];
    if (mode === 'agenda') {
        form.status = 'Terjadwal';
        form.category = 'Agenda Kegiatan / Baksos';
    } else {
        form.status = 'Selesai';
        form.category = 'Rapat Koordinasi';
    }
    selectedAttendees.value = [];
    comboboxSearch.value = '';
    isComboboxDropdownOpen.value = false;
    isFormModalOpen.value = true;
};

const openEditModal = (m, mode = 'normal') => {
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

    if (mode === 'write_notes') {
        form.status = 'Selesai';
        formMode.value = 'notulensi';
    } else {
        form.status = m.status || (m.summary ? 'Selesai' : 'Terjadwal');
        formMode.value = m.status === 'Terjadwal' ? 'agenda' : 'notulensi';
    }

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
    if (formMode.value === 'agenda') {
        form.leader = '';
        form.notewriter = '';
        selectedAttendees.value = [];
    }

    form.attendees_str = selectedAttendees.value.join(', ');

    const payload = {
        ...form.data(),
        attendees: selectedAttendees.value,
        action_items: JSON.stringify(form.action_items.filter(item => item.task && item.task.trim() !== ''))
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
    if (confirm(`Apakah Anda yakin ingin menghapus "${m.title}"?`)) {
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

const getCountdownInfo = (dateStr) => {
    if (!dateStr) return null;
    const target = new Date(dateStr);
    const now = new Date();
    // Compare day boundaries
    const targetDay = new Date(target.getFullYear(), target.getMonth(), target.getDate());
    const nowDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const diffTime = targetDay.getTime() - nowDay.getTime();
    const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return { label: 'Hari Ini', badgeClass: 'bg-emerald-500 text-white font-bold animate-pulse' };
    if (diffDays === 1) return { label: 'Besok', badgeClass: 'bg-amber-500 text-white font-bold' };
    if (diffDays > 1) return { label: `${diffDays} hari lagi`, badgeClass: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 font-semibold border border-sky-300 dark:border-sky-800' };
    if (diffDays === -1) return { label: 'Kemarin', badgeClass: 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300' };
    return { label: `${Math.abs(diffDays)} hari lalu`, badgeClass: 'bg-gray-100 text-gray-600 dark:bg-gray-800/80 dark:text-gray-400' };
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Selesai':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
        case 'Berlangsung':
            return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse';
        case 'Terjadwal':
            return 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border-sky-200 dark:border-sky-800';
        case 'Draft':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800';
        case 'Diarsipkan':
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
        case 'Dibatalkan':
            return 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200 dark:border-rose-900';
        default:
            return 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border-brand-200 dark:border-brand-800';
    }
};

const getCategoryColor = (category) => {
    switch (category) {
        case 'Agenda Kegiatan / Baksos':
            return 'text-purple-600 bg-purple-50 dark:bg-purple-950/30 dark:text-purple-400 border-purple-200 dark:border-purple-900/40';
        case 'Pelatihan & Siaga SAR':
            return 'text-orange-600 bg-orange-50 dark:bg-orange-950/30 dark:text-orange-400 border-orange-200 dark:border-orange-900/40';
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
    <Head title="Agenda & Arsip Rapat - MKT Indonesia" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <span class="text-gray-400 dark:text-gray-500">Menu /</span>
                <span class="font-bold text-gray-800 dark:text-gray-100">Agenda & Arsip Rapat</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Top Hero Banner & Actions -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 bg-gradient-to-r from-brand-600 via-amber-500 to-orange-500 p-6 md:p-8 rounded-3xl text-white shadow-xl shadow-brand-500/15 relative overflow-hidden">
                <!-- Background decorative elements -->
                <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute right-1/3 -top-10 w-44 h-44 bg-amber-300/20 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 space-y-2">
                    <div class="inline-flex items-center space-x-2 bg-white/20 backdrop-blur-md px-3.5 py-1 rounded-full text-xs font-medium tracking-wide text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM9 14h2v2H9v-2zm4 0h2v2h-2v-2z"></path>
                        </svg>
                        <span>Kalender Agenda & Dokumentasi Rapat Yayasan</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Agenda & Arsip Rapat</h1>
                    <p class="text-brand-100 text-sm max-w-2xl leading-relaxed">
                        Pusat perencanaan jadwal agenda kegiatan yayasan, koordinasi penanganan darurat bencana, serta arsip digital risalah notulensi dan tindak lanjut keputusan rapat MKT.
                    </p>
                </div>

                <div class="relative z-10 shrink-0 flex flex-wrap items-center gap-3">
                    <button
                        @click="openAddModal('agenda')"
                        class="px-5 py-3 rounded-2xl bg-white text-brand-700 font-bold hover:bg-brand-50 transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-[1.02] flex items-center space-x-2 text-sm focus:outline-none"
                    >
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>+ Jadwalkan Agenda Kegiatan</span>
                    </button>
                    <button
                        @click="openAddModal('notulensi')"
                        class="px-4 py-3 rounded-2xl bg-brand-800/40 hover:bg-brand-800/60 border border-white/20 text-white font-semibold transition-all duration-200 backdrop-blur-md flex items-center space-x-2 text-sm focus:outline-none"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>+ Catat Notulensi Selesai</span>
                    </button>
                </div>
            </div>

            <!-- Spotlight: Next Nearest Upcoming Agenda (If Available) -->
            <div
                v-if="nextUpcomingMeeting && activeTab !== 'arsip'"
                class="bg-gradient-to-r from-sky-50 via-brand-50/40 to-amber-50 dark:from-sky-950/20 dark:via-brand-950/20 dark:to-amber-950/20 border border-sky-200/80 dark:border-sky-900/50 rounded-3xl p-6 shadow-sm relative overflow-hidden"
            >
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
                    <div class="space-y-3 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-600 text-white">
                                <span>📌 Agenda Terdekat</span>
                            </span>
                            <span
                                v-if="getCountdownInfo(nextUpcomingMeeting.meeting_date)"
                                :class="['px-2.5 py-1 rounded-full text-xs', getCountdownInfo(nextUpcomingMeeting.meeting_date).badgeClass]"
                            >
                                {{ getCountdownInfo(nextUpcomingMeeting.meeting_date).label }}
                            </span>
                            <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold border', getCategoryColor(nextUpcomingMeeting.category)]">
                                {{ nextUpcomingMeeting.category }}
                            </span>
                        </div>

                        <div>
                            <h3
                                @click="openDetailModal(nextUpcomingMeeting)"
                                class="text-xl font-extrabold text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 cursor-pointer transition line-clamp-1"
                            >
                                {{ nextUpcomingMeeting.title }}
                            </h3>
                            <p v-if="nextUpcomingMeeting.agenda" class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-2 leading-relaxed">
                                {{ nextUpcomingMeeting.agenda }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex items-center space-x-1.5 font-medium text-gray-800 dark:text-gray-200">
                                <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ formatDate(nextUpcomingMeeting.meeting_date) }}</span>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>{{ nextUpcomingMeeting.location || 'Lokasi Belum Ditentukan' }}</span>
                            </div>
                            <div v-if="nextUpcomingMeeting.leader" class="flex items-center space-x-1.5">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>PIC / Pimpinan: {{ nextUpcomingMeeting.leader }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 shrink-0">
                        <button
                            @click="openQrModal(nextUpcomingMeeting)"
                            class="px-3.5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold transition shadow-sm flex items-center space-x-1.5"
                            title="QR Code Presensi & Absensi Peserta"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                            <span>QR Presensi</span>
                        </button>
                        <button
                            @click="openDetailModal(nextUpcomingMeeting)"
                            class="px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-100 border border-gray-200 dark:border-gray-700 text-xs font-bold transition shadow-sm"
                        >
                            Lihat Rundown
                        </button>
                        <button
                            @click="openEditModal(nextUpcomingMeeting, 'write_notes')"
                            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold transition shadow-md flex items-center space-x-1.5"
                        >
                            <span>✍️ Catat Notulensi</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Stat Card 1: Total Agenda & Arsip -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Agenda & Arsip</p>
                            <h3 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 mt-1">{{ stats.totalMeetings }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/50 flex items-center justify-center text-brand-600 dark:text-brand-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Keseluruhan rekaman rapat & kegiatan yayasan
                    </div>
                </div>

                <!-- Stat Card 2: Agenda Mendatang -->
                <div
                    @click="switchTab('agenda')"
                    class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all cursor-pointer hover:border-sky-300 dark:hover:border-sky-800 group"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider group-hover:text-sky-600 transition-colors">Agenda Mendatang</p>
                            <h3 class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-1">{{ stats.upcomingCount || 0 }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/50 flex items-center justify-center text-sky-600 dark:text-sky-400 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-sky-600 dark:text-sky-400 font-medium">
                        Klik untuk filter kegiatan terjadwal
                    </div>
                </div>

                <!-- Stat Card 3: Bulan Ini -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Aktivitas Bulan Ini</p>
                            <h3 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 mt-1">{{ stats.thisMonthCount }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-amber-600 dark:text-amber-400 font-medium">
                        Intensitas koordinasi periode berjalan
                    </div>
                </div>

                <!-- Stat Card 4: Action Items Progress -->
                <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tindak Lanjut (Action Items)</p>
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
            </div>

            <!-- Tab Segmented Controls -->
            <div class="flex flex-wrap items-center gap-2 p-1.5 bg-gray-100/80 dark:bg-gray-800/80 rounded-2xl max-w-fit">
                <button
                    @click="switchTab('semua')"
                    :class="[
                        activeTab === 'semua'
                            ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-sm font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium',
                        'px-4 py-2 rounded-xl text-xs transition-all focus:outline-none flex items-center space-x-2'
                    ]"
                >
                    <span>Semua Agenda & Arsip</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">{{ stats.totalMeetings }}</span>
                </button>

                <button
                    @click="switchTab('agenda')"
                    :class="[
                        activeTab === 'agenda'
                            ? 'bg-white dark:bg-gray-900 text-sky-600 dark:text-sky-400 shadow-sm font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium',
                        'px-4 py-2 rounded-xl text-xs transition-all focus:outline-none flex items-center space-x-2'
                    ]"
                >
                    <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Agenda Mendatang</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300 font-bold">{{ stats.upcomingCount || 0 }}</span>
                </button>

                <button
                    @click="switchTab('arsip')"
                    :class="[
                        activeTab === 'arsip'
                            ? 'bg-white dark:bg-gray-900 text-emerald-600 dark:text-emerald-400 shadow-sm font-bold'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium',
                        'px-4 py-2 rounded-xl text-xs transition-all focus:outline-none flex items-center space-x-2'
                    ]"
                >
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Arsip Notulensi Selesai</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-bold">{{ stats.completedCount || 0 }}</span>
                </button>
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
                            placeholder="Cari berdasarkan judul agenda, pimpinan/PIC, lokasi, atau isi bahasan..."
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
                                <div class="flex items-center space-x-2">
                                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold border', getCategoryColor(m.category)]">
                                        {{ m.category }}
                                    </span>
                                    <span
                                        v-if="m.status === 'Terjadwal' && getCountdownInfo(m.meeting_date)"
                                        :class="['px-2 py-0.5 rounded-full text-[11px]', getCountdownInfo(m.meeting_date).badgeClass]"
                                    >
                                        {{ getCountdownInfo(m.meeting_date).label }}
                                    </span>
                                </div>
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
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ formatDate(m.meeting_date) }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="truncate">{{ m.location || 'Lokasi Belum Ditentukan' }}</span>
                                </div>
                            </div>

                            <!-- Leaders & Notewriter / PIC -->
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-gray-800 text-xs">
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Pimpinan / PIC</span>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300 truncate block">{{ m.leader || '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Notulis / Admin</span>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300 truncate block">{{ m.notewriter || '-' }}</span>
                                </div>
                            </div>

                            <!-- Agenda or Summary Preview -->
                            <div v-if="m.status === 'Terjadwal' && m.agenda" class="bg-sky-50/60 dark:bg-sky-950/20 p-3.5 rounded-2xl border border-sky-100/70 dark:border-sky-900/30">
                                <span class="text-[10px] font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider block mb-1">Poin Agenda Kegiatan:</span>
                                <p class="text-xs text-gray-700 dark:text-gray-300 line-clamp-3 leading-relaxed">
                                    {{ m.agenda }}
                                </p>
                            </div>
                            <div v-else class="bg-gray-50 dark:bg-gray-800/60 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-800">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Ringkasan Notulensi / Keputusan:</span>
                                <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-3 leading-relaxed">
                                    {{ m.summary || (m.status === 'Terjadwal' ? 'Belum ada notulensi. Kegiatan belum dilaksanakan.' : 'Belum ada notulensi tertulis.') }}
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
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="openDetailModal(m)"
                                    class="inline-flex items-center space-x-1.5 text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                                >
                                    <span>{{ m.status === 'Terjadwal' ? 'Lihat Agenda' : 'Lihat Notulensi' }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>
                                <button
                                    v-if="m.status === 'Terjadwal'"
                                    @click="openEditModal(m, 'write_notes')"
                                    class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-[11px] font-bold transition"
                                    title="Catat Notulensi Kegiatan"
                                >
                                    ✍️ Catat Hasil
                                </button>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button
                                    @click="openQrModal(m)"
                                    class="p-2 text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-950/40 rounded-xl transition"
                                    title="QR Presensi & Absensi Peserta"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                    </svg>
                                </button>
                                <button
                                    @click="openEditModal(m)"
                                    class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30 rounded-xl transition"
                                    title="Edit Data"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button
                                    @click="deleteMeeting(m)"
                                    class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition"
                                    title="Hapus Data"
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
                                    <th class="px-6 py-4">Judul Agenda / Rapat</th>
                                    <th class="px-6 py-4">Jadwal & Lokasi</th>
                                    <th class="px-6 py-4">Kategori</th>
                                    <th class="px-6 py-4">Pimpinan / PIC</th>
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
                                        <div class="flex items-center space-x-1.5 font-medium text-gray-700 dark:text-gray-300">
                                            <span>{{ formatShortDate(m.meeting_date) }}</span>
                                            <span
                                                v-if="m.status === 'Terjadwal' && getCountdownInfo(m.meeting_date)"
                                                :class="['px-1.5 py-0.5 rounded text-[10px]', getCountdownInfo(m.meeting_date).badgeClass]"
                                            >
                                                {{ getCountdownInfo(m.meeting_date).label }}
                                            </span>
                                        </div>
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
                                                v-if="m.status === 'Terjadwal'"
                                                @click="openEditModal(m, 'write_notes')"
                                                class="px-2.5 py-1.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300 text-xs font-semibold transition"
                                                title="Catat Notulensi"
                                            >
                                                ✍️ Notulensi
                                            </button>
                                            <button
                                                @click="openQrModal(m)"
                                                class="p-1.5 text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 rounded-lg hover:bg-sky-50 dark:hover:bg-sky-950/40 transition"
                                                title="QR Presensi Peserta"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                                </svg>
                                            </button>
                                            <button
                                                @click="openEditModal(m)"
                                                class="p-1.5 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                                title="Edit"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button
                                                @click="deleteMeeting(m)"
                                                class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                                title="Hapus"
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ activeTab === 'agenda' ? 'Belum ada agenda kegiatan mendatang' : (activeTab === 'arsip' ? 'Belum ada arsip notulensi rapat' : 'Belum ada data agenda atau rapat') }}
                    </h3>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ activeTab === 'agenda' ? 'Jadwalkan agenda rapat koordinasi atau bakti sosial baru.' : 'Tidak ada dokumen yang cocok dengan filter pencarian Anda.' }}
                    </p>
                </div>
                <div class="flex items-center justify-center space-x-3 pt-2">
                    <button
                        @click="openAddModal('agenda')"
                        class="px-4 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm transition inline-flex items-center space-x-2 shadow-md"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Jadwalkan Agenda Baru</span>
                    </button>
                    <button
                        v-if="search || selectedCategory !== 'Semua' || selectedStatus !== 'Semua'"
                        @click="clearFilters"
                        class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-sm hover:bg-gray-200 transition"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= DETAIL MODAL (DRAWER / VIEW AGENDA & NOTULENSI) ================= -->
        <div v-if="isDetailModalOpen && activeMeeting" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 backdrop-blur-sm" @click.self="isDetailModalOpen = false">
            <div class="bg-white dark:bg-gray-900 w-full max-w-3xl rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[85vh] my-auto">
                <!-- Modal Header (Fixed Top) -->
                <div class="p-6 bg-gradient-to-r from-brand-500/10 via-amber-500/5 to-transparent border-b border-gray-100 dark:border-gray-800 flex items-start justify-between shrink-0">
                    <div class="space-y-1.5 pr-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold border', getCategoryColor(activeMeeting.category)]">
                                {{ activeMeeting.category }}
                            </span>
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold border', getStatusBadgeClass(activeMeeting.status)]">
                                {{ activeMeeting.status }}
                            </span>
                            <span
                                v-if="activeMeeting.status === 'Terjadwal' && getCountdownInfo(activeMeeting.meeting_date)"
                                :class="['px-2 py-0.5 rounded-full text-[11px]', getCountdownInfo(activeMeeting.meeting_date).badgeClass]"
                            >
                                {{ getCountdownInfo(activeMeeting.meeting_date).label }}
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
                    <!-- Informational Alert for Scheduled Agendas -->
                    <div v-if="activeMeeting.status === 'Terjadwal'" class="p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800/50 flex items-start space-x-3">
                        <svg class="w-5 h-5 text-sky-600 dark:text-sky-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-xs text-sky-800 dark:text-sky-300 space-y-1">
                            <p class="font-bold">Agenda Kegiatan Terjadwal</p>
                            <p>Kegiatan ini direncanakan berlangsung pada <strong>{{ formatDate(activeMeeting.meeting_date) }}</strong>. Setelah kegiatan terlaksana, Anda dapat langsung mencatat ringkasan notulensi dan action items dengan tombol di bawah.</p>
                        </div>
                    </div>

                    <!-- Officers & Participants -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Pimpinan / PIC Kegiatan</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ activeMeeting.leader || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Notulis / Admin</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ activeMeeting.notewriter || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Dibuat Oleh</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ activeMeeting.creator ? activeMeeting.creator.name : 'Sistem MKT' }}</span>
                        </div>
                    </div>

                    <!-- Attendees list -->
                    <div v-if="Array.isArray(activeMeeting.attendees) && activeMeeting.attendees.length > 0">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Daftar Peserta / Undangan Hadir ({{ activeMeeting.attendees.length }})</h4>
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

                    <!-- Agenda Pembahasan / Rundown Kegiatan -->
                    <div v-if="activeMeeting.agenda">
                        <h4 class="text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider mb-2 flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span>Poin Agenda / Rundown Acara</span>
                        </h4>
                        <div class="p-4 rounded-2xl bg-sky-50/50 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/40 text-gray-800 dark:text-gray-200 whitespace-pre-line leading-relaxed shadow-sm">
                            {{ activeMeeting.agenda }}
                        </div>
                    </div>

                    <!-- Summary / Notulensi -->
                    <div v-if="activeMeeting.summary || activeMeeting.status === 'Selesai'">
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
                                <p class="text-xs text-gray-500">Berkas pendukung atau foto dokumentasi rapat/kegiatan</p>
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
                        <span>Cetak / Cetak Ringkasan</span>
                    </button>

                    <div class="flex items-center space-x-2">
                        <button
                            @click="openQrModal(activeMeeting)"
                            class="px-3.5 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 dark:bg-sky-950/40 dark:text-sky-300 font-semibold text-xs transition flex items-center space-x-1.5"
                            title="QR Presensi Peserta"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                            <span>QR Presensi</span>
                        </button>
                        <button
                            v-if="activeMeeting.status === 'Terjadwal'"
                            @click="isDetailModalOpen = false; openEditModal(activeMeeting, 'write_notes')"
                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md transition"
                        >
                            ✍️ Selesaikan & Catat Notulensi
                        </button>
                        <button
                            @click="isDetailModalOpen = false; openEditModal(activeMeeting)"
                            class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 font-semibold text-xs hover:bg-amber-100 transition"
                        >
                            Edit Data
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

        <!-- ================= FORM SLIDE-OVER MODAL (TAMBAH / EDIT AGENDA & NOTULENSI) ================= -->
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
                                    <span class="text-xs text-white/80">• {{ formMode === 'agenda' ? 'Perencanaan Agenda' : 'Notulensi Keputusan' }}</span>
                                </div>
                                <h2 class="text-xl font-extrabold mt-0.5">
                                    {{ editingMeeting ? 'Edit Agenda / Notulensi Rapat' : (formMode === 'agenda' ? 'Jadwalkan Agenda Kegiatan Baru' : 'Catat Notulensi Rapat Baru') }}
                                </h2>
                                <p class="text-xs text-brand-100">Lengkapi waktu pelaksanaan, daftar peserta, rundown agenda, serta notulensi rapat</p>
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
                            <!-- Section 1: Informasi Pokok Agenda -->
                            <div class="space-y-4">
                                <div class="flex items-center space-x-2 pb-2 border-b border-gray-100 dark:border-gray-800">
                                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                    <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">1. Rincian Agenda & Pelaksanaan</h4>
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Judul Agenda / Rapat <span class="text-rose-500">*</span></label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        required
                                        placeholder="Contoh: Rapat Koordinasi Posko Tanggap Bencana Maros / Bakti Sosial Donor Darah"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                    />
                                    <span v-if="form.errors.title" class="text-xs text-rose-500 mt-1 block">{{ form.errors.title }}</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal & Waktu Pelaksanaan <span class="text-rose-500">*</span></label>
                                        <input
                                            v-model="form.meeting_date"
                                            type="datetime-local"
                                            required
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                        />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori Kegiatan / Rapat <span class="text-rose-500">*</span></label>
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
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Lokasi / Media Kegiatan</label>
                                        <input
                                            v-model="form.location"
                                            type="text"
                                            placeholder="Gedung Yayasan MKT / Posko Lapangan / Zoom Meeting"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                        />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Status Kegiatan</label>
                                        <select
                                            v-model="form.status"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                        >
                                            <option v-for="st in statuses" :key="st" :value="st">{{ st }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Pimpinan & Notulis (Hanya tampil pada Notulensi Rapat, disembunyikan pada Agenda Kegiatan Baru) -->
                                <div v-if="formMode !== 'agenda'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Pimpinan Rapat / Penanggung Jawab (PIC)</label>
                                        <input
                                            v-model="form.leader"
                                            type="text"
                                            placeholder="Nama Koordinator / Chair"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                        />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Notulis / Pencatat Acara</label>
                                        <input
                                            v-model="form.notewriter"
                                            type="text"
                                            placeholder="Nama Notulis"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Daftar Peserta / Undangan Hadir (Hanya tampil pada Notulensi Rapat, disembunyikan pada Agenda Kegiatan Baru) -->
                            <div v-if="formMode !== 'agenda'" class="space-y-2">
                                <div class="flex items-center space-x-2 pb-2 border-b border-gray-100 dark:border-gray-800">
                                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                    <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">2. Peserta / Undangan Hadir</h4>
                                </div>

                                <div class="flex items-center justify-between">
                                    <label class="text-xs text-gray-500">Pilih dari anggota aktif yayasan atau tambahkan peserta tamu:</label>
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
                                    <p v-else class="text-xs text-gray-400 italic">Belum ada peserta yang dipilih. Cari dan pilih dari anggota aktif MKT di bawah.</p>

                                    <!-- Combobox Search Input & Dropdown -->
                                    <div class="relative mt-2">
                                        <div class="relative flex items-center">
                                            <input
                                                v-model="comboboxSearch"
                                                type="text"
                                                @focus="isComboboxDropdownOpen = true"
                                                @keydown.enter.prevent="addCustomAttendee"
                                                placeholder="Ketik nama anggota atau peserta eksternal lalu tekan Enter..."
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
                                                <button type="button" @click="isComboboxDropdownOpen = false" class="text-brand-600 dark:text-brand-400 hover:underline">Tutup</button>
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

                            <!-- Section: Agenda & Rundown Pembahasan -->
                            <div class="space-y-4">
                                <div class="flex items-center space-x-2 pb-2 border-b border-gray-100 dark:border-gray-800">
                                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                    <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        {{ formMode === 'agenda' ? '2. Pokok Agenda & Rundown Kegiatan' : '3. Pokok Agenda & Rundown Pembahasan' }}
                                    </h4>
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Rincian Poin Agenda Pembahasan</label>
                                    <textarea
                                        v-model="form.agenda"
                                        rows="4"
                                        placeholder="1. Pembukaan & Doa&#10;2. Sosialisasi kesiapan personil / logistik&#10;3. Rencana penanganan bencana"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs leading-relaxed"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Section 4: Risalah Notulensi & Action Items (Hanya untuk Form Notulensi Rapat) -->
                            <div v-if="formMode !== 'agenda'" class="space-y-4">
                                <div class="flex items-center space-x-2 pb-2 border-b border-gray-100 dark:border-gray-800">
                                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                    <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">4. Risalah Notulensi & Rencana Tindak Lanjut</h4>
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Notulensi / Ringkasan Hasil Keputusan</label>
                                    <textarea
                                        v-model="form.summary"
                                        rows="4"
                                        placeholder="Tuliskan poin-poin keputusan rapat, kesepakatan tim, arahan ketua yayasan..."
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 p-2.5 focus:ring-2 focus:ring-brand-500 transition text-xs leading-relaxed"
                                    ></textarea>
                                </div>

                                <!-- Dynamic Action Items -->
                                <div class="bg-gray-50 dark:bg-gray-800/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="font-bold text-gray-800 dark:text-gray-200 text-xs uppercase tracking-wider">Tindak Lanjut Tugas (Action Items)</label>
                                        <button
                                            type="button"
                                            @click="addActionItemRow"
                                            class="px-2.5 py-1 rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400 text-xs font-bold hover:bg-brand-100 transition"
                                        >
                                            + Tambah Tugas
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
                                                placeholder="PIC"
                                                class="w-28 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs p-2 text-gray-800 dark:text-gray-100"
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
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Unggah Lampiran / Dokumen Pendukung (PDF, DOCX, Foto)</label>
                                    <input
                                        type="file"
                                        @change="(e) => form.attachment = e.target.files[0]"
                                        accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 dark:file:bg-brand-950 dark:file:text-brand-300 hover:file:bg-brand-100"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons Footer (Sticky Fixed Bottom) -->
                        <div class="px-6 py-4 bg-gray-50/90 dark:bg-gray-800/90 backdrop-blur-md border-t border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                            <div class="text-xs text-gray-400">
                                Status tersimpan: <strong class="text-gray-700 dark:text-gray-300">{{ form.status }}</strong>
                            </div>

                            <div class="flex items-center space-x-3">
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
                                    {{ form.processing ? 'Menyimpan...' : (editingMeeting ? 'Simpan Perubahan' : (form.status === 'Terjadwal' ? 'Simpan Agenda' : 'Simpan Notulensi')) }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- ================= QR CODE & PRESENSI ATTENDANCE MODAL ================= -->
        <div v-if="isQrModalOpen && selectedMeetingForQr" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div
                @click="isQrModalOpen = false"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
            ></div>

            <!-- Modal Dialog Container -->
            <div class="relative bg-white dark:bg-gray-900 w-full max-w-3xl rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 flex flex-col max-h-[92vh] overflow-hidden z-10">
                <!-- Modal Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-sky-600 via-brand-500 to-amber-500 text-white flex items-center justify-between shrink-0 shadow-md">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-inner">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-semibold text-white tracking-wide uppercase">
                                    {{ selectedMeetingForQr.category || 'Presensi Digital' }}
                                </span>
                                <span class="text-xs text-white/80">• E-Presensi Kehadiran</span>
                            </div>
                            <h3 class="text-base sm:text-lg font-black leading-snug truncate max-w-md mt-0.5">
                                {{ selectedMeetingForQr.title }}
                            </h3>
                        </div>
                    </div>
                    <button
                        @click="isQrModalOpen = false"
                        class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition focus:outline-none"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Tabs: QR Code Standee vs Live Attendees List -->
                <div class="flex border-b border-gray-100 dark:border-gray-800 px-6 pt-3 bg-gray-50/50 dark:bg-gray-800/40 shrink-0 gap-6">
                    <button
                        @click="activeQrTab = 'qrcode'"
                        :class="[
                            'pb-3 text-xs font-bold transition flex items-center space-x-2 border-b-2',
                            activeQrTab === 'qrcode'
                                ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                        ]"
                    >
                        <span>📲 QR Code & Cetak Meja</span>
                    </button>
                    <button
                        @click="activeQrTab = 'attendees'"
                        :class="[
                            'pb-3 text-xs font-bold transition flex items-center space-x-2 border-b-2',
                            activeQrTab === 'attendees'
                                ? 'border-brand-500 text-brand-600 dark:text-brand-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'
                        ]"
                    >
                        <span>👥 Daftar Hadir Peserta</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 font-extrabold border border-brand-200 dark:border-brand-800">
                            {{ attendeesList.length }}
                        </span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto flex-1 space-y-6">
                    <!-- TAB 1: QR CODE STANDEE -->
                    <div v-if="activeQrTab === 'qrcode'" class="space-y-6">
                        <!-- Toggle Status & Public URL Box -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                            <div class="md:col-span-7 space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Status Akses Presensi</span>
                                <div class="flex items-center space-x-2.5">
                                    <span :class="['w-2.5 h-2.5 rounded-full', selectedMeetingForQr.is_attendance_open ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500']"></span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ selectedMeetingForQr.is_attendance_open ? 'Presensi Terbuka (Aktif)' : 'Presensi Ditutup' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ selectedMeetingForQr.is_attendance_open ? 'Peserta & undangan dapat memindai QR code untuk presensi mandiri.' : 'Formulir presensi dinonaktifkan sementara.' }}
                                </p>
                            </div>
                            <div class="md:col-span-5 flex md:justify-end">
                                <button
                                    type="button"
                                    @click="toggleMeetingAttendance"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-1.5',
                                        selectedMeetingForQr.is_attendance_open
                                            ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-900'
                                            : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900'
                                    ]"
                                >
                                    <span>{{ selectedMeetingForQr.is_attendance_open ? '🔒 Tutup Presensi' : '🔓 Buka Presensi' }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- QR Preview & Download / Print Area -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                            <!-- QR Preview Box -->
                            <div class="flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 shadow-sm relative text-center">
                                <div v-if="isGeneratingQr" class="py-16 text-center space-y-3">
                                    <div class="w-8 h-8 mx-auto border-3 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
                                    <p class="text-xs text-gray-500">Menghasilkan QR Code presensi...</p>
                                </div>
                                <div v-else class="space-y-3">
                                    <div class="p-3 bg-white rounded-2xl shadow-md border border-gray-100 inline-block">
                                        <img
                                            :src="qrCodeDataUrl"
                                            alt="QR Code Presensi"
                                            class="w-56 h-56 object-contain block mx-auto rounded-lg"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-xs font-extrabold text-gray-800 dark:text-gray-100">
                                            Scan via Smartphone / WhatsApp
                                        </p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">
                                            Bebas login, langsung tanda tangan digital
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions & Share Options -->
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Tautan Langsung Presensi (Direct Link)
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <input
                                            type="text"
                                            readonly
                                            :value="getAttendancePublicUrl(selectedMeetingForQr)"
                                            class="flex-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs text-gray-600 dark:text-gray-300 font-mono select-all truncate"
                                        />
                                        <button
                                            type="button"
                                            @click="copyAttendanceLink"
                                            class="px-3.5 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs transition shrink-0"
                                        >
                                            {{ copiedLink ? '✓ Tersalin!' : 'Salin' }}
                                        </button>
                                    </div>
                                </div>

                                <div class="pt-2 space-y-2.5">
                                    <!-- Print Standee Button -->
                                    <button
                                        type="button"
                                        @click="printQrStandee"
                                        class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md transition flex items-center justify-center space-x-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                        <span>🖨️ Cetak Standee Meja Registrasi (A4)</span>
                                    </button>

                                    <!-- Download PNG Button -->
                                    <button
                                        type="button"
                                        @click="downloadQrImage"
                                        class="w-full py-2.5 px-4 rounded-2xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs transition flex items-center justify-center space-x-2 shadow-sm"
                                    >
                                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        <span>Unduh File Gambar QR (.PNG)</span>
                                    </button>

                                    <!-- Open Direct Page in New Tab -->
                                    <a
                                        :href="getAttendancePublicUrl(selectedMeetingForQr)"
                                        target="_blank"
                                        class="w-full py-2.5 px-4 rounded-2xl bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/50 text-sky-700 dark:text-sky-300 font-bold text-xs transition flex items-center justify-center space-x-2"
                                    >
                                        <span>↗ Buka Halaman Presensi di Tab Baru</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: LIVE ATTENDEES LIST -->
                    <div v-else class="space-y-4">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="relative w-full sm:w-64">
                                <input
                                    v-model="attendanceSearch"
                                    type="text"
                                    placeholder="Cari nama / instansi / no HP..."
                                    class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-3 py-2 text-xs text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                />
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                                <button
                                    type="button"
                                    @click="printAttendanceSheet"
                                    class="px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 font-bold text-xs transition shadow-sm flex items-center space-x-1.5"
                                    title="Cetak Format Lembar Daftar Hadir Resmi"
                                >
                                    <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    <span>Cetak Lembar Presensi</span>
                                </button>
                                <button
                                    type="button"
                                    @click="exportAttendeesCsv"
                                    :disabled="attendeesList.length === 0"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm flex items-center space-x-1.5 disabled:opacity-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>Export CSV / Excel</span>
                                </button>
                            </div>
                        </div>

                        <!-- Attendees Table -->
                        <div class="border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
                            <div class="overflow-x-auto max-h-96">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-semibold uppercase sticky top-0 z-10 border-b border-gray-100 dark:border-gray-800">
                                        <tr>
                                            <th class="px-4 py-3">No</th>
                                            <th class="px-4 py-3">Nama & Peran</th>
                                            <th class="px-4 py-3">Instansi / Lembaga</th>
                                            <th class="px-4 py-3">Kontak (HP/Email)</th>
                                            <th class="px-4 py-3">Paraf / TTD</th>
                                            <th class="px-4 py-3">Waktu Presensi</th>
                                            <th class="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-if="filteredAttendees.length === 0">
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                                Belum ada data presensi yang masuk.
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="(att, idx) in filteredAttendees"
                                            :key="att.id"
                                            class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition"
                                        >
                                            <td class="px-4 py-3 text-gray-400 font-mono">{{ idx + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-gray-800 dark:text-gray-200">{{ att.name }}</div>
                                                <div class="text-[11px] text-gray-400">{{ att.position || 'Peserta' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                                {{ att.institution || '-' }}
                                            </td>
                                            <td class="px-4 py-3 space-y-0.5">
                                                <div class="font-mono text-emerald-600 dark:text-emerald-400 font-medium">{{ att.phone }}</div>
                                                <div v-if="att.email" class="text-[10px] text-gray-400">{{ att.email }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div v-if="att.signature" class="cursor-pointer" @click="previewSignatureUrl = att.signature">
                                                    <img
                                                        :src="att.signature"
                                                        alt="TTD"
                                                        class="h-8 max-w-[80px] object-contain border border-gray-200 dark:border-gray-700 rounded bg-white p-0.5 hover:scale-110 transition"
                                                    />
                                                </div>
                                                <span v-else class="text-gray-400 italic text-[11px]">Tanpa TTD</span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                                {{ att.attended_at ? new Date(att.attended_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WITA' : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    @click="deleteAttendee(att)"
                                                    class="p-1 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition"
                                                    title="Hapus Peserta"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                    <span class="text-xs text-gray-400">
                        Total Kehadiran: <strong class="text-gray-800 dark:text-gray-200">{{ attendeesList.length }} Peserta</strong>
                    </span>
                    <button
                        type="button"
                        @click="isQrModalOpen = false"
                        class="px-5 py-2 rounded-xl bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs hover:bg-gray-300 dark:hover:bg-gray-700 transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Signature Preview Lightbox Modal -->
        <div v-if="previewSignatureUrl" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm" @click="previewSignatureUrl = null">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-2xl max-w-sm w-full space-y-4 text-center" @click.stop>
                <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Tanda Tangan Digital Peserta</h4>
                <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                    <img :src="previewSignatureUrl" alt="Tanda Tangan" class="w-full h-40 object-contain mx-auto" />
                </div>
                <button
                    type="button"
                    @click="previewSignatureUrl = null"
                    class="w-full py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs rounded-xl transition"
                >
                    Tutup Pratinjau
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
