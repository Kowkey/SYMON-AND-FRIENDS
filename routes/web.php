<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (guest will be redirected to /login, auth user to /dashboard)
Route::redirect('/', '/dashboard');

// Guest-only routes (Requirement 2: Logged-in users cannot access login or registration)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated-only routes (Requirement 2: Guests cannot access main page without logging in)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Eloquent relationship management routes (Protected by Post/Redirect/Get pattern)
    Route::post('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/dashboard/posts', [DashboardController::class, 'storePost'])->name('posts.store');
    Route::post('/dashboard/roles', [DashboardController::class, 'syncRoles'])->name('roles.sync');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
