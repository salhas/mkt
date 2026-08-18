<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SarOperation;
use App\Models\DisasterEvent;
use App\Models\Logistic;
use App\Models\Donation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AlertApiController extends Controller
{
    /**
     * Get aggregated real-time emergency alerts & live notification feed.
     */
    public function getLiveAlerts(Request $request)
    {
        // 1. Urgent & Active SAR Operations
        $activeSar = SarOperation::whereIn('status', ['Operasi Aktif', 'Siaga SAR'])
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($op) {
                return [
                    'id' => $op->id,
                    'type' => 'sar_operation',
                    'category' => $op->type, // Operasi SAR or Siaga SAR
                    'title' => $op->title,
                    'code' => $op->code,
                    'location' => $op->location,
                    'severity' => $op->severity_level, // Rendah, Sedang, Tinggi, Siaga 1
                    'status' => $op->status,
                    'commander' => $op->commander_name ?? 'Pusdalops 727',
                    'personnel_count' => $op->personnel_count,
                    'created_at' => $op->created_at ? $op->created_at->diffForHumans() : 'Baru saja',
                    'raw_time' => $op->created_at ? $op->created_at->toIso8601String() : null,
                    'url' => route('sar-operations.index'),
                    'is_critical' => in_array($op->severity_level, ['Tinggi', 'Siaga 1', 'Kritis']) || $op->status === 'Operasi Aktif',
                ];
            });

        // 2. Active Disaster Emergency Events
        $activeDisasters = DisasterEvent::whereIn('status', ['Siaga', 'Evakuasi', 'Tanggap Darurat'])
            ->orWhereIn('severity', ['Darurat', 'Tinggi', 'Kritis'])
            ->orderBy('date_occurred', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'type' => 'disaster_event',
                    'category' => $event->category,
                    'title' => $event->title,
                    'location' => $event->location,
                    'severity' => $event->severity,
                    'status' => $event->status,
                    'victim_count' => $event->victim_count,
                    'created_at' => $event->created_at ? $event->created_at->diffForHumans() : 'Baru saja',
                    'raw_time' => $event->created_at ? $event->created_at->toIso8601String() : null,
                    'url' => route('disaster-map.index'),
                    'is_critical' => in_array($event->severity, ['Darurat', 'Tinggi', 'Kritis']) || $event->status === 'Evakuasi',
                ];
            });

        // 3. Low / Critical Logistics Stock
        $criticalLogistics = Logistic::where('quantity', '<=', 10)
            ->orderBy('quantity', 'asc')
            ->take(3)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'logistic_alert',
                    'title' => "Stok Menipis: {$item->item_name}",
                    'category' => $item->category,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'created_at' => $item->updated_at ? $item->updated_at->diffForHumans() : 'Hari ini',
                    'url' => route('logistics.index'),
                    'is_critical' => $item->quantity <= 3,
                ];
            });

        // 4. Recent Public Donations (Transparansi Masuk)
        $recentDonations = Donation::with('donor')
            ->orderBy('id', 'desc')
            ->take(4)
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'type' => 'donation_received',
                    'title' => 'Donasi Baru Diterima',
                    'donor_name' => $donation->donor ? $donation->donor->name : 'Hamba Allah',
                    'amount' => $donation->amount,
                    'payment_method' => $donation->payment_method,
                    'created_at' => $donation->created_at ? $donation->created_at->diffForHumans() : 'Baru saja',
                    'url' => route('donors.index'),
                    'is_critical' => false,
                ];
            });

        // Calculate Critical Items count
        $criticalSarCount = $activeSar->where('is_critical', true)->count();
        $criticalDisasterCount = $activeDisasters->where('is_critical', true)->count();
        $criticalLogisticsCount = $criticalLogistics->where('is_critical', true)->count();

        $totalCritical = $criticalSarCount + $criticalDisasterCount + $criticalLogisticsCount;
        $totalAlerts = $activeSar->count() + $activeDisasters->count() + $criticalLogistics->count() + $recentDonations->count();

        // Check if there is a primary top headline banner alert
        $headlineAlert = null;
        if ($activeSar->where('is_critical', true)->isNotEmpty()) {
            $topOp = $activeSar->where('is_critical', true)->first();
            $headlineAlert = [
                'type' => 'sar',
                'badge' => 'SIAGA DARURAT SAR',
                'title' => "{$topOp['title']} - {$topOp['location']}",
                'details' => "Komandan: {$topOp['commander']} | {$topOp['personnel_count']} Personel Dikerahkan",
                'url' => route('sar-operations.command-center'),
                'action_label' => 'Buka Command Center',
                'level' => 'danger'
            ];
        } elseif ($activeDisasters->where('is_critical', true)->isNotEmpty()) {
            $topDisaster = $activeDisasters->where('is_critical', true)->first();
            $headlineAlert = [
                'type' => 'disaster',
                'badge' => 'TANGGAP BENCANA',
                'title' => "{$topDisaster['title']} ({$topDisaster['location']})",
                'details' => "Status: {$topDisaster['status']} | Korban/Terdampak: {$topDisaster['victim_count']} Jiwa",
                'url' => route('disaster-map.index'),
                'action_label' => 'Pantau Peta',
                'level' => 'warning'
            ];
        }

        return response()->json([
            'success' => true,
            'timestamp' => Carbon::now()->toIso8601String(),
            'formatted_time' => Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm:ss') . ' WITA',
            'summary' => [
                'total_alerts' => $totalAlerts,
                'total_critical' => $totalCritical,
                'active_sar_count' => $activeSar->count(),
                'active_disaster_count' => $activeDisasters->count(),
                'critical_logistics_count' => $criticalLogistics->count(),
                'recent_donations_count' => $recentDonations->count(),
            ],
            'headline_alert' => $headlineAlert,
            'data' => [
                'sar_operations' => $activeSar,
                'disaster_events' => $activeDisasters,
                'critical_logistics' => $criticalLogistics,
                'recent_donations' => $recentDonations,
            ]
        ]);
    }
}
