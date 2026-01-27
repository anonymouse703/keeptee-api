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

    Route::resource('amenities', Admin\AmenityController::class);

    Route::resource('contact-messages', Admin\ContactMessageController::class);

    Route::resource('email-logs', Admin\EmailLogController::class);

    Route::resource('leases', Admin\LeaseController::class);
    Route::controller(Admin\LeaseController::class)->group(function () {
        Route::put('leases/{lease}/set-active', 'setActiveStatus')
            ->name('leases.set-active');
        Route::put('leases/{lease}/set-ended', 'setEndedStatus')
            ->name('leases.set-ended');
        Route::put('leases/{lease}/set-terminated', 'setTerminatedStatus')
            ->name('leases.set-terminated');
    });

    Route::resource('maintenances', Admin\MaintenanceController::class);
    Route::controller(Admin\MaintenanceController::class)->group(function () {
        Route::put('maintenances/{maintenance}/set-pending', 'setPendingStatus')
            ->name('maintenances.set-pending');
        Route::put('maintenances/{maintenance}/set-progress', 'setProgressStatus')
            ->name('maintenances.set-progress');
        Route::put('maintenances/{maintenance}/set-completed', 'setCompletedStatus')
            ->name('maintenances.set-completed');
    });

    Route::resource('properties', Admin\PropertyController::class);
    Route::controller(Admin\PropertyController::class)->group(function () {
        Route::put('properties/{property}/set-featured', 'setFeatured')
            ->name('properties.set-featured');
        Route::put('properties/{property}/toggle-status', 'toggleStatus')
            ->name('properties.toggle-status');
        Route::delete('/property-images/{image}', 'destroyImage')
            ->name('property-images.destroy');

    });

    Route::resource('properties-inquiries', Admin\PropertyInquiryController::class);
    Route::controller(Admin\PropertyInquiryController::class)->group(function () {
        Route::put('properties-inquiries/{propertyInquiry}/set-pending', 'setPendingStatus')
            ->name('properties-inquiries.set-pending');
        Route::put('properties-inquiries/{propertyInquiry}/set-approved', 'setApprovedStatus')
            ->name('properties-inquiries.set-approved');
        Route::put('properties-inquiries/{propertyInquiry}/set-cancelled', 'setCancelledStatus')
            ->name('properties-inquiries.set-cancelled');
    });

    Route::resource('rent-payments', Admin\RentPaymentController::class);
    Route::controller(Admin\RentPaymentController::class)->group(function () {
        Route::put('rent-payments/{rentPayment}/set-pending', 'setPendingStatus')
            ->name('rent-payments.set-pending');
        Route::put('rent-payments/{rentPayment}/set-paid', 'setPaidStatus')
            ->name('rent-payments.set-paid');
        Route::put('rent-payments/{rentPayment}/set-overdue', 'setOverdueStatus')
            ->name('rent-payments.set-overdue');
    });

    Route::resource('reviews', Admin\ReviewController::class);

    Route::resource('tags', Admin\TagController::class);
    Route::controller(Admin\TagController::class)->group(function () {
        Route::put('tags/{tag}/toggle-status', 'toggleStatus')
            ->name('tags.toggle-status');
    });

    Route::resource('tenants', Admin\TenantController::class);

    Route::resource('users', Admin\UserController::class);
    Route::controller(Admin\UserController::class)->group(function () {
        Route::put('users/{user}/toggle-status', 'toggleStatus')
            ->name('users.toggle-status');
    });
});

require __DIR__.'/settings.php';
