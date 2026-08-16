<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationMember;
use Illuminate\Http\Request;

class OrganizationMemberApiController extends Controller
{
    public function index(Request $request)
    {
        $members = OrganizationMember::where('status', 'Aktif')
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $chartData = [
            'pembina' => $members->where('tier', 'Dewan Pembina')->values(),
            'pengawas' => $members->where('tier', 'Dewan Pengawas')->values(),
            'pengurus' => $members->where('tier', 'Pengurus')->values(),
            'anggota' => $members->where('tier', 'Anggota')->values(),
        ];

        return response()->json([
            'success' => true,
            'count' => count($members),
            'structure' => $chartData,
            'all_members' => $members
        ]);
    }
}
