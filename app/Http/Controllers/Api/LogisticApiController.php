<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logistic;
use Illuminate\Http\Request;

class LogisticApiController extends Controller
{
    public function index(Request $request)
    {
        $logistics = Logistic::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'count' => count($logistics),
            'data' => $logistics
        ]);
    }
}
