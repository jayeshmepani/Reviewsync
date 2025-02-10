<?php

use App\Http\Controllers\{
    AuthController,
    BusinessController,
    GoogleAuthController,
    ProfileController,
    ReviewController,
    AIController,
    SuperAdminController,
    SubscriptionController,
    PaymentController
};
use App\Http\Middleware\{HandleCors, SuperAdminMiddleware};
use App\Models\Location;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
Route::get('/login/google', [GoogleAuthController::class, 'redirectToGoogleSignIn'])->name('login.google');
Route::get('/login/google/callback', [GoogleAuthController::class, 'handleGoogleSignInCallback']);

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        Route::get('/signup', [AuthController::class, 'showSignupForm'])->name('signup');
        Route::post('/signup', [AuthController::class, 'signUp'])->name('signup.submit');
        Route::get('/register', [AuthController::class, 'showSignupForm'])->name('register');
        Route::post('/register', [AuthController::class, 'signUp'])->name('register.submit');

        // Forgot Password Routes
        Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Superadmin Routes
Route::prefix('superadmin')->middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/admin-dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::get('/users', [SuperAdminController::class, 'users'])->name('superadmin.users');
    Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('superadmin.users.store');
    Route::get('/users/{id}/edit', [SuperAdminController::class, 'editUser'])->name('superadmin.users.edit');
    Route::put('/users/{id}', [SuperAdminController::class, 'updateUser'])->name('superadmin.users.update');
    Route::get('/users/{id}/data', [SuperAdminController::class, 'viewUserData'])->name('superadmin.users.data');
    Route::delete('/users/{id}', [SuperAdminController::class, 'deleteUser'])->name('superadmin.users.delete');
    Route::delete('/users/{id}/data', [SuperAdminController::class, 'deleteLocation'])->name('superadmin.users.data.delete');
    Route::get('/profile', [SuperAdminController::class, 'profile'])->name('superadmin.profile');
});
Route::get('/superadmin', [AuthController::class, 'createSuperAdmin']);

// Protected Routes
Route::middleware(['auth', HandleCors::class])->group(function () {

    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('choose-plan', [SubscriptionController::class, 'choosePlan'])->name('subscription.choose');
    Route::post('/get-plan-details', [SubscriptionController::class, 'getPlanDetails']);

    Route::post('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

    // AI Routes
    Route::prefix('api/reviews/{reviewId}')->group(function () {
        Route::get('/ai-replies', [AIController::class, 'fetchAIReplies']);
        Route::get('/stored-replies', [AIController::class, 'getStoredReplies']);
    });

    Route::get('/ai/check-reply-status/{reviewId}', [AIController::class, 'checkReplyStatus']);

    Route::get('/aigeneration', [AIController::class, 'index'])->name('aigeneration.form');

    // Dashboard
    Route::get('/dashboard', [BusinessController::class, 'showDashboard'])->name('dashboard');

    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'profile'])->name('profile');
        Route::post('/rename', [ProfileController::class, 'rename'])->name('profile.rename');
        Route::post('/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update_password');
        Route::post('/update-email', [ProfileController::class, 'updateEmail'])->name('profile.update_email');
        Route::post('/update-contact', [ProfileController::class, 'updateContact'])->name('profile.update_contact');
    });

    // Business Routes
    Route::prefix('businesses')->group(function () {
        Route::get('/sync', [BusinessController::class, 'showSyncOptions'])->name('business.sync.options');
        Route::post('/sync', [BusinessController::class, 'sync'])->name('business.sync');
        Route::get('/', [ProfileController::class, 'businessProfile'])->name('businesses');
        Route::get('/search', [ProfileController::class, 'search'])->name('businesses.search');
        Route::delete('/{id}', [BusinessController::class, 'destroy'])->name('businesses.delete');


        // Review Routes
        Route::prefix('{id}/reviews')->group(function () {
            Route::get('/', [ReviewController::class, 'index'])->name('businesses.reviews');
            Route::get('/search', [ReviewController::class, 'search'])->name('review.search');
            Route::post('/{reviewId}/reply', [ReviewController::class, 'replyToReview'])->name('reviews.reply');
            Route::put('/{reviewId}/reply', [ReviewController::class, 'updateReply'])->name('reviews.reply.update'); // <-- Added update route
            Route::delete('/{reviewId}/reply', [ReviewController::class, 'deleteReply'])->name('reviews.reply.delete');
        });

    });

    // Reviews
    Route::get('/review/{location}', function ($locationId) {
        $location = Location::find($locationId);
        return view('review_page', ['location' => $location]);
    })->name('review_page');
    Route::post('/api/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

