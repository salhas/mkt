<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MktProfileApiController;
use App\Http\Controllers\Api\PartnerApiController;
use App\Http\Controllers\Api\VolunteerApiController;
use App\Http\Controllers\Api\DisasterEventApiController;
use App\Http\Controllers\Api\DonationApiController;
use App\Http\Controllers\Api\LogisticApiController;
use App\Http\Controllers\Api\OrganizationMemberApiController;
use App\Http\Controllers\Api\MeetingApiController;
use App\Http\Controllers\Api\FinanceApiController;
use App\Http\Controllers\Api\BmkgApiController;
use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\SarOperationApiController;
use App\Http\Controllers\Api\AlertApiController;

/*
|--------------------------------------------------------------------------
| API Routes for Mobile Flutter Client (Yayasan MKT Ekosistem Bencana)
|--------------------------------------------------------------------------
| Base URL: /api/v1/
*/

Route::prefix('v1')->group(function () {

    // --- PUBLIC AUTH ENDPOINTS ---
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // --- PUBLIC DATA ENDPOINTS (Flutter App Dashboard & Public Feed) ---
    Route::get('/mkt-profile', [MktProfileApiController::class, 'index']);
    Route::get('/partners', [PartnerApiController::class, 'index']);
    Route::get('/partners/{id}', [PartnerApiController::class, 'show']);
    
    Route::get('/volunteers', [VolunteerApiController::class, 'index']);
    Route::get('/volunteers/{id}', [VolunteerApiController::class, 'show']);
    Route::post('/volunteers/register', [VolunteerApiController::class, 'publicRegister']);
    Route::patch('/volunteers/{id}/verify', [VolunteerApiController::class, 'verify']);

    Route::get('/disaster-events', [DisasterEventApiController::class, 'index']);
    Route::get('/disaster-events/{id}', [DisasterEventApiController::class, 'show']);

    Route::get('/sar-operations', [SarOperationApiController::class, 'index']);
    Route::get('/sar-operations/{id}', [SarOperationApiController::class, 'show']);
    
    Route::get('/donations', [DonationApiController::class, 'index']);
    Route::post('/donations', [DonationApiController::class, 'store']);

    Route::get('/logistics', [LogisticApiController::class, 'index']);
    Route::get('/management', [OrganizationMemberApiController::class, 'index']);
    Route::get('/management/{id}', [OrganizationMemberApiController::class, 'show']);

    Route::get('/meetings', [MeetingApiController::class, 'index']);
    Route::get('/meetings/{id}', [MeetingApiController::class, 'show']);

    // --- NEWS & ARTICLES ENDPOINTS ---
    Route::get('/news', [NewsApiController::class, 'index']);
    Route::get('/news/{id}', [NewsApiController::class, 'show']);

    // --- BMKG WEATHER & CLIMATE INTEGRATION ENDPOINTS ---
    Route::get('/bmkg/weather', [BmkgApiController::class, 'getWeather']);

    // --- REAL-TIME EMERGENCY ALERTS & NOTIFICATIONS (FLUTTER CLIENT & COMMAND CENTER) ---
    Route::get('/alerts/live', [AlertApiController::class, 'getLiveAlerts']);

    // --- FINANCIAL JOURNAL ENDPOINTS ---
    Route::get('/finance/accounts', [FinanceApiController::class, 'accounts']);
    Route::get('/finance/journals', [FinanceApiController::class, 'index']);
    Route::get('/finance/journals/{id}', [FinanceApiController::class, 'show']);

    // --- PROTECTED API ENDPOINTS (SANCTUM BEARER TOKEN AUTHENTICATED) ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        
        // Organization Management (Pengurus MKT) Endpoints
        Route::post('/management', [OrganizationMemberApiController::class, 'store']);
        Route::match(['put', 'post'], '/management/{id}', [OrganizationMemberApiController::class, 'update']);
        Route::patch('/management/{id}/status', [OrganizationMemberApiController::class, 'updateStatus']);
        Route::delete('/management/{id}', [OrganizationMemberApiController::class, 'destroy']);

        // Volunteer Management & Verification Endpoints
        Route::post('/volunteers', [VolunteerApiController::class, 'store']);
        Route::match(['put', 'patch'], '/volunteers/{id}', [VolunteerApiController::class, 'update']);
        Route::delete('/volunteers/{id}', [VolunteerApiController::class, 'destroy']);

        // Report Disaster & Meeting Management from Flutter Mobile App
        Route::post('/disaster-events', [DisasterEventApiController::class, 'store']);
        Route::post('/sar-operations', [SarOperationApiController::class, 'store']);
        Route::post('/meetings', [MeetingApiController::class, 'store']);

        // News CRUD Endpoints (Protected for Webmaster, Admin, Staff)
        Route::post('/news', [NewsApiController::class, 'store']);
        Route::put('/news/{id}', [NewsApiController::class, 'update']);
        Route::delete('/news/{id}', [NewsApiController::class, 'destroy']);

        // Financial Journal CRUD Endpoints (Finance Role Protected)
        Route::post('/finance/journals', [FinanceApiController::class, 'store']);
        Route::put('/finance/journals/{id}', [FinanceApiController::class, 'update']);
        Route::delete('/finance/journals/{id}', [FinanceApiController::class, 'destroy']);
    });
});
