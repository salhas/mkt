<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MeetingArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_and_meeting_archive_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Meeting::create([
            'title' => 'Agenda Rapat Koordinasi Posko Bencana',
            'meeting_date' => now()->addDays(2),
            'location' => 'Posko Utama MKT',
            'category' => 'Agenda Kegiatan / Baksos',
            'leader' => 'Koordinator SAR',
            'notewriter' => 'Admin Tim',
            'agenda' => "1. Pembukaan\n2. Evaluasi Armada\n3. Pembagian Wilayah",
            'status' => 'Terjadwal',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('meetings.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Meetings/Index')
            ->has('meetings.data', 1)
            ->has('nextUpcomingMeeting')
            ->where('stats.upcomingCount', 1)
            ->where('filters.tab', 'semua')
        );
    }

    public function test_tab_agenda_filters_only_upcoming_or_scheduled(): void
    {
        $user = User::factory()->create();

        Meeting::create([
            'title' => 'Kegiatan Baksos Donor Darah',
            'meeting_date' => now()->addDays(3),
            'category' => 'Agenda Kegiatan / Baksos',
            'status' => 'Terjadwal',
            'created_by' => $user->id,
        ]);

        Meeting::create([
            'title' => 'Notulensi Evaluasi Banjir',
            'meeting_date' => now()->subDays(5),
            'category' => 'Evaluasi Bencana',
            'status' => 'Selesai',
            'summary' => 'Hasil evaluasi penanganan bencana.',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('meetings.index', ['tab' => 'agenda']));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Meetings/Index')
            ->has('meetings.data', 1)
            ->where('meetings.data.0.title', 'Kegiatan Baksos Donor Darah')
            ->where('filters.tab', 'agenda')
        );
    }

    public function test_tab_arsip_filters_only_completed_meetings(): void
    {
        $user = User::factory()->create();

        Meeting::create([
            'title' => 'Kegiatan Baksos Donor Darah',
            'meeting_date' => now()->addDays(3),
            'category' => 'Agenda Kegiatan / Baksos',
            'status' => 'Terjadwal',
            'created_by' => $user->id,
        ]);

        Meeting::create([
            'title' => 'Notulensi Evaluasi Banjir',
            'meeting_date' => now()->subDays(5),
            'category' => 'Evaluasi Bencana',
            'status' => 'Selesai',
            'summary' => 'Hasil evaluasi penanganan bencana.',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('meetings.index', ['tab' => 'arsip']));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Meetings/Index')
            ->has('meetings.data', 1)
            ->where('meetings.data.0.title', 'Notulensi Evaluasi Banjir')
            ->where('filters.tab', 'arsip')
        );
    }

    public function test_can_store_new_agenda_activity(): void
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Pelatihan Water Rescue & Siaga SAR Relawan',
            'meeting_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'location' => 'Danau Mawang Gowa',
            'category' => 'Pelatihan & Siaga SAR',
            'leader' => 'Instruktur Basarnas',
            'notewriter' => 'Sekretaris MKT',
            'agenda' => 'Praktik evakuasi korban perahu karet & pertolongan pertama',
            'status' => 'Terjadwal',
            'attendees' => ['Relawan 1', 'Relawan 2'],
            'action_items' => json_encode([
                ['task' => 'Siapkan pelampung dan tali', 'pic' => 'Logistik', 'deadline' => now()->addDays(5)->format('Y-m-d'), 'completed' => false]
            ]),
        ];

        $response = $this
            ->actingAs($user)
            ->post(route('meetings.store'), $payload);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('meetings', [
            'title' => 'Pelatihan Water Rescue & Siaga SAR Relawan',
            'category' => 'Pelatihan & Siaga SAR',
            'status' => 'Terjadwal',
        ]);
    }

    public function test_can_update_agenda_to_completed_with_notulensi(): void
    {
        $user = User::factory()->create();

        $meeting = Meeting::create([
            'title' => 'Rapat Kerja Bulanan Pengurus MKT',
            'meeting_date' => now()->format('Y-m-d H:i:s'),
            'category' => 'Rapat Koordinasi',
            'status' => 'Terjadwal',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('meetings.update', $meeting->id), [
                'title' => 'Rapat Kerja Bulanan Pengurus MKT',
                'meeting_date' => now()->format('Y-m-d H:i:s'),
                'category' => 'Rapat Koordinasi',
                'status' => 'Selesai',
                'summary' => 'Rapat menyetujui program bakti sosial dan kesiapan siaga cuaca ekstrem.',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'status' => 'Selesai',
            'summary' => 'Rapat menyetujui program bakti sosial dan kesiapan siaga cuaca ekstrem.',
        ]);
    }

    public function test_can_delete_meeting(): void
    {
        $user = User::factory()->create();

        $meeting = Meeting::create([
            'title' => 'Draft Jadwal Briefing',
            'meeting_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'category' => 'Internal Tim',
            'status' => 'Draft',
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('meetings.destroy', $meeting->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('meetings', [
            'id' => $meeting->id,
        ]);
    }
}
