<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Models\Donation;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DonorController extends Controller
{
    public function index(Request $request)
    {
        $query = Donor::query()->withCount(['donations' => function($q) {
            $q->where('status', 'Sukses');
        }])->withSum(['donations' => function($q) {
            $q->where('status', 'Sukses');
        }], 'amount');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $donors = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        // Donations history
        $donationsQuery = Donation::with('donor');
        if ($request->filled('donation_search')) {
            $dSearch = $request->input('donation_search');
            $donationsQuery->where(function($q) use ($dSearch) {
                $q->where('reference_number', 'like', "%{$dSearch}%")
                  ->orWhere('description', 'like', "%{$dSearch}%")
                  ->orWhereHas('donor', function($dq) use ($dSearch) {
                      $dq->where('name', 'like', "%{$dSearch}%");
                  });
            });
        }
        if ($request->filled('donation_status')) {
            $donationsQuery->where('status', $request->input('donation_status'));
        }
        $donations = $donationsQuery->orderBy('donation_date', 'desc')->paginate(10, ['*'], 'donations_page')->withQueryString();

        return Inertia::render('Donors/Index', [
            'donors' => $donors,
            'donations' => $donations,
            'filters' => $request->only(['search', 'type', 'donation_search', 'donation_status'])
        ]);
    }

    public function storeDonor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:donors,email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'type' => 'required|string|max:50', // Personal, Lembaga
            'status' => 'required|string|max:50',
        ]);

        Donor::create($validated);

        return redirect()->back()->with('success', 'Donatur berhasil ditambahkan.');
    }

    public function updateDonor(Request $request, Donor $donor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:donors,email,' . $donor->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'type' => 'required|string|max:50',
            'status' => 'required|string|max:50',
        ]);

        $donor->update($validated);

        return redirect()->back()->with('success', 'Donatur berhasil diperbarui.');
    }

    public function storeDonation(Request $request)
    {
        $validated = $request->validate([
            'donor_id' => 'nullable|exists:donors,id',
            'amount' => 'required|numeric|min:1',
            'donation_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'status' => 'required|string|max:50', // Sukses, Pending, Gagal
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
        ]);

        if (empty($validated['reference_number'])) {
            $validated['reference_number'] = 'TX-' . date('Ymd') . '-' . rand(100, 999);
        }

        Donation::create($validated);

        return redirect()->back()->with('success', 'Donasi berhasil dicatat.');
    }

    public function updateDonation(Request $request, Donation $donation)
    {
        $validated = $request->validate([
            'donor_id' => 'nullable|exists:donors,id',
            'amount' => 'required|numeric|min:1',
            'donation_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $donation->update($validated);

        return redirect()->back()->with('success', 'Donasi berhasil diperbarui.');
    }
}
