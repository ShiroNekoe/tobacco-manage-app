<?php

use App\Http\Controllers\CertificateController;
use App\Livewire\Admin\AdminTracking;
use App\Livewire\Admin\BatchManagement;
use App\Livewire\Admin\MasterDataManagement;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Login;
use App\Livewire\Customer\CustomerDashboard;
use App\Livewire\Karyawan\WeighingSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', Login::class)->name('login');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Protected Routes (4-Role MES System)
Route::middleware(['auth'])->group(function () {
    // Role-Based Root Navigation Redirect
    Route::get('/', function () {
        $user = Auth::user();
        if ($user) {
            if ($user->isCustomer()) {
                return redirect()->route('customer.dashboard');
            }
            if ($user->isAdmin() || $user->isSupervisor()) {
                return redirect()->route('admin.batches');
            }
        }
        return redirect()->route('karyawan.weighing');
    })->name('dashboard');

    // 1. Karyawan / Worker Route (Restricted to /karyawan/weighing)
    Route::middleware(['role:karyawan,operator,worker,admin,supervisor'])->group(function () {
        Route::get('/karyawan/weighing', WeighingSheet::class)->name('karyawan.weighing');
    });

    // 2. Admin & Supervisor Management Routes
    Route::middleware(['role:admin,supervisor'])->group(function () {
        Route::get('/admin/batches', BatchManagement::class)->name('admin.batches');
        Route::get('/admin/tracking', AdminTracking::class)->name('admin.tracking');
        Route::get('/admin/master-data', MasterDataManagement::class)->name('admin.master-data');
    });

    // Admin User Management Route
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/users', UserManagement::class)->name('admin.users');
    });

    // 3. Customer Portal Route
    Route::middleware(['role:customer,admin,supervisor'])->group(function () {
        Route::get('/customer/dashboard', CustomerDashboard::class)->name('customer.dashboard');

        // Customer REST API Endpoints with Server-side Tenant Isolation
        Route::prefix('api/customer')->group(function () {
            Route::get('/batches', [\App\Http\Controllers\Api\CustomerPortalApiController::class, 'indexBatches'])->name('api.customer.batches');
            Route::get('/batches/{batchId}', [\App\Http\Controllers\Api\CustomerPortalApiController::class, 'showBatch'])->name('api.customer.batch.show');
            Route::get('/batches/{batchId}/receiving-reconciliation', [\App\Http\Controllers\Api\CustomerPortalApiController::class, 'receivingReconciliation'])->name('api.customer.batch.reconciliation');
            Route::get('/batches/{batchId}/process-balance', [\App\Http\Controllers\Api\CustomerPortalApiController::class, 'processBalance'])->name('api.customer.batch.balance');
            Route::get('/performance/summary', [\App\Http\Controllers\Api\CustomerPortalApiController::class, 'performanceSummary'])->name('api.customer.performance.summary');
            Route::get('/performance/trend', [\App\Http\Controllers\Api\CustomerPortalApiController::class, 'performanceTrend'])->name('api.customer.performance.trend');
        });
    });

    // Process Certificate View & PDF Download Routes (Supervisor Approval Gated for Customer)
    Route::get('/certificate/{batch}', [CertificateController::class, 'show'])->name('certificate.show');
    Route::get('/certificate/{batch}/pdf', [CertificateController::class, 'downloadPdf'])->name('certificate.pdf');
});
