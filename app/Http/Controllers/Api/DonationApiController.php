<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Donor;
use Illuminate\Http\Request;

class DonationApiController extends Controller
{
    public function index(Request $request)
    {
        $donations = Donation::with('donor')
            ->orderBy('donation_date', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $donations
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:10000',
            'payment_method' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $donor = Donor::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'type' => 'Personal',
                'status' => 'Aktif',
            ]
        );

        $donation = Donation::create([
            'donor_id' => $donor->id,
            'amount' => $validated['amount'],
            'donation_date' => now()->toDateString(),
            'payment_method' => $validated['payment_method'],
            'status' => 'Sukses',
            'description' => $validated['description'] ?? 'Donasi kemanusiaan via Mobile App',
            'reference_number' => 'TX-MOB-' . date('YmdHis') . '-' . rand(100, 999),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Donasi berhasil dikirim. Terima kasih atas kepedulian Anda!',
            'data' => $donation
        ], 201);
    }
}
