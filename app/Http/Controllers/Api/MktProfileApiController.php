<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MktProfile;
use Illuminate\Http\Request;

class MktProfileApiController extends Controller
{
    public function index()
    {
        $profile = MktProfile::first();

        return response()->json([
            'success' => true,
            'data' => $profile ?: [
                'name' => 'Yayasan MKT Indonesia',
                'description' => 'Mitra Kemanusiaan Terpadu Indonesia',
                'address' => 'Perumahan Insignia Oasis Blok B1-11 No 7, Kota Makassar, Sulawesi Selatan',
                'phone' => '+62 812-3456-7890',
                'email' => 'info@mkt.or.id',
                'vision' => 'Menjadi lembaga kemanusiaan terdepan dalam penanggulangan bencana.',
                'mission' => 'Aksi evakuasi, donasi transparan, dan relawan terpadu.',
            ]
        ]);
    }
}
