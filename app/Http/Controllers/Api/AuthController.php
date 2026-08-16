<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * User Login Endpoint (For Mobile Flutter Client)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kombinasi email dan kata sandi salah.',
            ], 401);
        }

        $deviceName = $request->input('device_name', 'Flutter_Mobile_Device');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Mobile User Registration (Volunteers / Donors)
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:50',
            'role' => 'nullable|string|in:relawan,donatur,medis,mitra',
            'blood_type' => 'nullable|string|max:10',
        ]);

        $role = $request->input('role', 'relawan');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'email_verified_at' => now(),
        ]);

        if ($role === 'relawan') {
            Volunteer::updateOrCreate(
                ['email' => $user->email],
                [
                    'name' => $user->name,
                    'phone' => $request->phone,
                    'blood_type' => $request->blood_type ?? 'O',
                    'role' => 'Relawan Rescuer',
                    'status' => 'Aktif',
                    'registered_at' => now()->toDateString(),
                ]
            );
        }

        $token = $user->createToken('Flutter_Mobile_Device')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi pengguna berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ], 201);
    }

    /**
     * Get Authenticated User Profile & Role Data
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $volunteerProfile = Volunteer::where('email', $user->email)->first();

        return response()->json([
            'success' => true,
            'user' => $user,
            'volunteer_profile' => $volunteerProfile,
        ]);
    }

    /**
     * Logout & Revoke API Token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi login berhasil diakhiri.',
        ]);
    }
}
