<?php

use Inertia\Inertia;
use App\Models\Property;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Models\RentPayment;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth', 'verified')->group(function () {  
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProperties' => Property::count(),
                'occupiedRate' => Property::where('status', 'occupied')->count() / max(Property::count(), 1) * 100,
                'monthlyRevenue' => RentPayment::whereMonth('created_at', now()->month)->sum('amount'),
                'pendingPayments' => RentPayment::where('status', 'pending')->count(),
                'activeTenants' => User::where('status', 'active')->count(),
                'maintenanceRequests' => Maintenance::where('status', 'pending')->count(),
            ],
        ]);
    })->name('dashboard');

    Route::resource('tags', Admin\TagController::class);
});

require __DIR__.'/settings.php';
