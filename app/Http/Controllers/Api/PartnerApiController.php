<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::withCount('volunteers');

        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        $partners = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'count' => count($partners),
            'data' => $partners
        ]);
    }

    public function show($id)
    {
        $partner = Partner::with('volunteers')->find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'Data Mitra tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $partner
        ]);
    }
}
