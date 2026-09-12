<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ContactEnquiryController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\InstagramController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PostController;

Route::prefix('/public')->group(function () {

    Route::post('/creator/scrape-instagram', [InstagramController::class, 'scrapeInstagram']);

    Route::post('instagram-profile', [InstagramController::class, 'scrapeInstagram']);

    // ================= SETTINGS =================
    // Route::get('/settings/brand/', [BrandingController::class, 'show']);
    // Route::get('/settings/card-pricing/', [CardcomingPricingSettingController::class, 'index']);
    // Route::get('/organization/{slug}', [OrginazationController::class, 'showBySlug']);
    // Route::post('/meeting-request', [MeetingController::class, 'store']);
    // Route::get('/meeting-cancelled-by-client/{id}', [MeetingController::class, 'cancelByHost']);

});

Route::prefix('auth')->group(function () {

    // ================= AUTH =================

    Route::post('admin-register', [AuthController::class, 'admin_register']);
    Route::post('admin-login', [AuthController::class, 'login']);

    // Login with Orginazation
    Route::post('org-login', [AuthController::class, 'org_login']);

    // ================= PASSWORD / OTP =================
    Route::post('forgot-password', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::get('app-logo-settings', [SettingController::class, 'show']);

    Route::post('organization/forgot-password', [AuthController::class, 'OrgsendOtp']);

    // ================= GOOGLE OAUTH =================
    Route::get('google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);

});

Route::get('/list-jobs', [JobController::class, 'publicIndex']);

Route::get('/jobs/departments', [JobController::class, 'publicDepartments']);
Route::get('/jobs/types', [JobController::class, 'publicJobTypes']);
Route::get('/jobs/{id}', [JobController::class, 'publicShow']);
Route::post('/contact-enquiry', [ContactEnquiryController::class, 'store']);
Route::post('/jobs/{jobId}/apply', [JobApplicationController::class, 'store']);

// Route::prefix('admin')->middleware(['api'])->group(function () {
Route::prefix('admin')->middleware(['api', 'jwt.auth'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::post('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::patch('/posts/{id}/publish', [PostController::class, 'togglePublish']);
    Route::patch('/posts/{id}/featured', [PostController::class, 'toggleFeatured']);
    Route::post('/creator', [CreatorController::class, 'store']);
    Route::get('/creator/profile', [CreatorController::class, 'profile']);
    Route::post('/creator/update/{id}', [CreatorController::class, 'update']);
    Route::delete('/creator/{id}', [CreatorController::class, 'destroy']);
    Route::patch('/creator/toggle-publish/{id}', [CreatorController::class, 'togglePublish']);
    Route::patch('/creator/toggle-featured/{id}', [CreatorController::class, 'toggleFeatured']);
    Route::post('/jobs', [JobController::class, 'store']);
    Route::get('/job-applications', [JobApplicationController::class, 'index']);
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{id}', [JobController::class, 'show']);
    Route::post('/jobs/{id}', [JobController::class, 'update']);
    Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
    Route::patch('/jobs/{id}/toggle-publish', [JobController::class, 'togglePublish']);
    Route::patch('/jobs/{id}/toggle-featured', [JobController::class, 'toggleFeatured']);
});
