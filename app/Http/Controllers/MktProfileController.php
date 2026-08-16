<?php

namespace App\Http\Controllers;

use App\Models\MktProfile;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MktProfileController extends Controller
{
    public function index()
    {
        $profile = MktProfile::first();
        return Inertia::render('MktProfile/Index', [
            'profile' => $profile
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'logo' => 'nullable|string',
            'bank_accounts' => 'nullable|array',
        ]);

        $profile = MktProfile::first();
        if (!$profile) {
            $profile = new MktProfile();
        }

        $profile->fill($request->all());
        $profile->save();

        return redirect()->back()->with('success', 'Profil Lembaga MKT berhasil diperbarui.');
    }
}
