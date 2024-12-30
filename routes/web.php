<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Middleware\HandleCors;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest'); // Show login form
Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('guest'); // Handle login form submission

Route::get('oauth2/callback', [GoogleOAuthController::class, 'callback'])->name('google.callback');

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes - Only Accessible After Login
Route::middleware(['auth', HandleCors::class])->group(function () {

    Route::get('locations', [GoogleController::class, 'locations']);

    Route::get('/dashboard', [BusinessController::class, 'showDashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'profile'])->name('Profile');

    Route::get('/business-info', [ProfileController::class, 'businessProfile'])->name('business-info');

    // Business Management Routes
    Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
    Route::delete('/businesses/{business}', [BusinessController::class, 'destroy'])->name('businesses.delete');

    // Review Routes
    Route::get('/businesses/{business}/reviews', [ReviewController::class, 'index'])->name('businesses.reviews');
    Route::post('businesses/{id}/reviews/{reviewId}/reply', [ReviewController::class, 'replyToReview'])->name('reviews.reply');
    Route::delete('businesses/{id}/reviews/{reviewId}/reply', [ReviewController::class, 'deleteReply'])->name('reviews.reply.delete');

    Route::get('/proxy-image', function (Request $request) {
        $url = urldecode($request->query('url'));
        $headers = get_headers($url, 1);

        if (strpos($headers[0], '200') !== false) {
            return response(file_get_contents($url))->header('Content-Type', $headers['Content-Type']);
        }

        return response('Failed to fetch image', 404);
    })->name('image.proxy');

    Route::post('/profile/rename', [ProfileController::class, 'rename'])->name('profile.rename');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update_password');
    Route::post('/profile/update-email', [ProfileController::class, 'updateEmail'])->name('profile.update_email');
    Route::post('/profile/update-contact', [ProfileController::class, 'updateContact'])->name('profile.update_contact');
});

