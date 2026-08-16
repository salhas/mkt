<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MktProfileController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\LogisticController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\DisasterMapController;
use App\Http\Controllers\MeetingArchiveController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SarOperationController;
use App\Http\Controllers\SarParticipationController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\NewsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Multi-Page Routes (Yayasan MKT Indonesia)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/profil', [PublicPageController::class, 'about'])->name('public.profile');
Route::get('/tentang-kami', [PublicPageController::class, 'about'])->name('public.about');
Route::get('/layanan', [PublicPageController::class, 'services'])->name('public.services');
Route::get('/berita', [PublicPageController::class, 'news'])->name('public.news');
Route::get('/berita/{slug}', [PublicPageController::class, 'newsDetail'])->name('public.news.show');
Route::get('/mitra', [PublicPageController::class, 'partners'])->name('public.partners');
Route::get('/pilar-kebencanaan', function() {
    return redirect()->route('public.partners');
})->name('public.pillars');
Route::get('/kontak', [PublicPageController::class, 'contact'])->name('public.contact');

// Public Volunteer Registration Route (with Email Notification)
Route::post('/register-volunteer', [VolunteerController::class, 'publicRegister'])->name('volunteers.public-register');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/weather', [DashboardController::class, 'getWeather'])->name('dashboard.weather');

    // News & Articles Management (Manajemen Berita & Artikel)
    Route::get('/news-management', [NewsController::class, 'index'])->name('news.index');
    Route::post('/news-management', [NewsController::class, 'store'])->name('news.store');
    Route::post('/news-management/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::patch('/news-management/{news}', [NewsController::class, 'update']);
    Route::delete('/news-management/{news}', [NewsController::class, 'destroy'])->name('news.destroy');

    // MKT Profile
    Route::get('/mkt-profile', [MktProfileController::class, 'index'])->name('mkt-profile.index');
    Route::post('/mkt-profile', [MktProfileController::class, 'update'])->name('mkt-profile.update');

    // Users Management (Webmaster & Administrator)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Management & Members (Manajemen Pengurus & Anggota)
    Route::get('/management', [OrganizationMemberController::class, 'index'])->name('management.index');
    Route::post('/management', [OrganizationMemberController::class, 'store'])->name('management.store');
    Route::post('/management/{member}', [OrganizationMemberController::class, 'update'])->name('management.update');
    Route::delete('/management/{member}', [OrganizationMemberController::class, 'destroy'])->name('management.destroy');

    // Meeting Archive (Arsip & Notulensi Rapat)
    Route::get('/meetings', [MeetingArchiveController::class, 'index'])->name('meetings.index');
    Route::post('/meetings', [MeetingArchiveController::class, 'store'])->name('meetings.store');
    Route::post('/meetings/{meeting}', [MeetingArchiveController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{meeting}', [MeetingArchiveController::class, 'destroy'])->name('meetings.destroy');

    // Mitra & Relawan (PMI, Rumah Sakit, Basarnas, BPBD, Rescue, Relawan)
    Route::get('/volunteers', [VolunteerController::class, 'index'])->name('volunteers.index');
    Route::post('/volunteers', [VolunteerController::class, 'store'])->name('volunteers.store');
    Route::patch('/volunteers/{volunteer}', [VolunteerController::class, 'update'])->name('volunteers.update');
    Route::delete('/volunteers/{volunteer}', [VolunteerController::class, 'destroy'])->name('volunteers.destroy');
    Route::post('/partners', [VolunteerController::class, 'storePartner'])->name('partners.store');
    Route::patch('/partners/{partner}', [VolunteerController::class, 'updatePartner'])->name('partners.update');
    Route::delete('/partners/{partner}', [VolunteerController::class, 'destroyPartner'])->name('partners.destroy');

    // Donors (Donatur) & Donations (Donasi)
    Route::get('/donors', [DonorController::class, 'index'])->name('donors.index');
    Route::post('/donors', [DonorController::class, 'storeDonor'])->name('donors.store');
    Route::patch('/donors/{donor}', [DonorController::class, 'updateDonor'])->name('donors.update');
    Route::post('/donations', [DonorController::class, 'storeDonation'])->name('donations.store');
    Route::patch('/donations/{donation}', [DonorController::class, 'updateDonation'])->name('donations.update');

    // Logistics (Logistik)
    Route::get('/logistics', [LogisticController::class, 'index'])->name('logistics.index');
    Route::post('/logistics/items', [LogisticController::class, 'storeItem'])->name('logistics.items.store');
    Route::patch('/logistics/items/{logistic}', [LogisticController::class, 'updateItem'])->name('logistics.items.update');
    Route::post('/logistics/transactions', [LogisticController::class, 'storeTransaction'])->name('logistics.transactions.store');

    // Finance & Reports (Keuangan & Jurnal)
    Route::get('/finance/coa', [FinanceController::class, 'indexCoa'])->name('finance.coa.index');
    Route::post('/finance/coa', [FinanceController::class, 'storeAccount'])->name('finance.coa.store');
    Route::patch('/finance/coa/{account}', [FinanceController::class, 'updateAccount'])->name('finance.coa.update');
    Route::delete('/finance/coa/{account}', [FinanceController::class, 'destroyAccount'])->name('finance.coa.destroy');
    Route::get('/finance/journal', [FinanceController::class, 'indexJournal'])->name('finance.journal.index');
    Route::post('/finance/journal', [FinanceController::class, 'storeJournal'])->name('finance.journal.store');
    Route::patch('/finance/journal/{journalEntry}', [FinanceController::class, 'updateJournal'])->name('finance.journal.update');
    Route::delete('/finance/journal/{journalEntry}', [FinanceController::class, 'destroyJournal'])->name('finance.journal.destroy');
    Route::get('/finance/ledger', [FinanceController::class, 'indexLedger'])->name('finance.ledger.index');
    Route::get('/finance/balance-sheet', [FinanceController::class, 'indexBalanceSheet'])->name('finance.balance-sheet.index');

    // Disaster Map (Peta Operasi)
    Route::get('/disaster-map', [DisasterMapController::class, 'index'])->name('disaster-map.index');
    Route::post('/disaster-map', [DisasterMapController::class, 'store'])->name('disaster-map.store');
    Route::patch('/disaster-map/{disasterEvent}', [DisasterMapController::class, 'update'])->name('disaster-map.update');
    Route::delete('/disaster-map/{disasterEvent}', [DisasterMapController::class, 'destroy'])->name('disaster-map.destroy');

    // Operasi & Siaga SAR
    Route::get('/sar-operations', [SarOperationController::class, 'index'])->name('sar-operations.index');
    Route::get('/sar-operations/command-center', [SarOperationController::class, 'commandCenter'])->name('sar-operations.command-center');
    Route::post('/sar-operations', [SarOperationController::class, 'store'])->name('sar-operations.store');
    Route::patch('/sar-operations/{sarOperation}', [SarOperationController::class, 'update'])->name('sar-operations.update');
    Route::delete('/sar-operations/{sarOperation}', [SarOperationController::class, 'destroy'])->name('sar-operations.destroy');

    // Partisipasi & Deployment Tim Potensi SAR
    Route::post('/sar-operations/{sarOperation}/participations', [SarParticipationController::class, 'store'])->name('sar-participations.store');
    Route::patch('/sar-participations/{sarParticipation}', [SarParticipationController::class, 'update'])->name('sar-participations.update');
    Route::delete('/sar-participations/{sarParticipation}', [SarParticipationController::class, 'destroy'])->name('sar-participations.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
