<?php

namespace App\Http\Controllers;

use App\Models\Volunteer;
use App\Models\Donation;
use App\Models\Logistic;
use App\Models\LogisticTransaction;
use App\Models\JournalItem;
use App\Services\BmkgWeatherService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, BmkgWeatherService $weatherService)
    {
        $user = $request->user();

        // 0. KHUSUS ROLE MITRA: Tampilkan data eksklusif lembaga mitra sendiri (Tanpa data yayasan MKT / lembaga lain)
        if ($user && $user->role === 'mitra') {
            $partner = $user->getPartner();

            $partnerMembers = $partner 
                ? Volunteer::where('partner_id', $partner->id)->orderBy('id', 'desc')->get() 
                : collect();

            $partnerStats = [
                'total' => $partner ? max($partnerMembers->count(), (int) $partner->personnel_count) : 0,
                'registered' => $partnerMembers->count(),
                'active' => $partnerMembers->where('status', 'Aktif')->count(),
                'rescue' => $partnerMembers->filter(fn($v) => str_contains($v->role, 'Rescue'))->count(),
                'donor' => $partnerMembers->filter(fn($v) => str_contains($v->role, 'Donor'))->count(),
                'medis' => $partnerMembers->filter(fn($v) => str_contains($v->role, 'Medis') || str_contains($v->role, 'Kesehatan'))->count(),
                'status' => $partner ? $partner->status : 'Aktif',
            ];

            $locationCode = $request->input('weather_loc', '73.71.01.1001');
            $weatherData = $weatherService->getWeather($locationCode);

            return Inertia::render('Dashboard', [
                'isPartner' => true,
                'partner' => $partner,
                'partnerStats' => $partnerStats,
                'recentPartnerMembers' => $partnerMembers->take(6)->values(),
                'weatherData' => $weatherData,
                'volunteerStats' => null,
                'donationStats' => null,
                'logisticStats' => null,
                'financialStats' => null,
                'recentDonations' => [],
                'recentVolunteers' => [],
                'recentLogistics' => [],
            ]);
        }

        // 1. Volunteer Stats
        $volunteerStats = [
            'total' => Volunteer::count(),
            'active' => Volunteer::where('status', 'Aktif')->count(),
            'rescue' => Volunteer::where('role', 'Tim Rescue')->count(),
            'blood_donor' => Volunteer::where('role', 'Donor Darah')->count(),
            'general' => Volunteer::where('role', 'Relawan Umum')->count(),
        ];

        // 2. Donation Stats
        $donationStats = [
            'total_amount' => Donation::where('status', 'Sukses')->sum('amount'),
            'total_count' => Donation::where('status', 'Sukses')->count(),
            'pending_count' => Donation::where('status', 'Pending')->count(),
        ];

        // 3. Logistic Stats
        $logisticStats = [
            'total_items' => Logistic::count(),
            'low_stock' => Logistic::where('quantity', '<', 10)->count(),
        ];

        // 4. Financial Summary from Journal Items
        $totalRevenue = JournalItem::whereHas('account', function($q) {
            $q->where('type', 'Revenue');
        })->where('type', 'Credit')->sum('amount');

        $totalExpense = JournalItem::whereHas('account', function($q) {
            $q->where('type', 'Expense');
        })->where('type', 'Debit')->sum('amount');

        $financialStats = [
            'revenue' => $totalRevenue,
            'expense' => $totalExpense,
            'balance' => $totalRevenue - $totalExpense,
        ];

        // 5. Recent Activity lists
        $recentDonations = Donation::with('donor')
            ->orderBy('donation_date', 'desc')
            ->limit(5)
            ->get();

        $recentVolunteers = Volunteer::orderBy('registered_at', 'desc')
            ->limit(5)
            ->get();

        $recentLogistics = LogisticTransaction::with('logistic')
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        // 6. BMKG Weather Forecast Data
        $locationCode = $request->input('weather_loc', '73.71.01.1001'); // Default Markas Pusat MKT (Makassar)
        $weatherData = $weatherService->getWeather($locationCode);

        return Inertia::render('Dashboard', [
            'volunteerStats' => $volunteerStats,
            'donationStats' => $donationStats,
            'logisticStats' => $logisticStats,
            'financialStats' => $financialStats,
            'recentDonations' => $recentDonations,
            'recentVolunteers' => $recentVolunteers,
            'recentLogistics' => $recentLogistics,
            'weatherData' => $weatherData,
        ]);
    }

    /**
     * API Endpoint for fetching BMKG weather via AJAX when user changes location dropdown
     */
    public function getWeather(Request $request, BmkgWeatherService $weatherService)
    {
        $locationCode = $request->input('code', '73.71.01.1001');
        $weatherData = $weatherService->getWeather($locationCode);

        return response()->json($weatherData);
    }
}
