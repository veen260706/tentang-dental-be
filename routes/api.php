<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\PatientController;
use App\Http\Controllers\Api\Admin\RontgenController;
use App\Http\Controllers\Api\Admin\PromoController as AdminPromoController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Api\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Api\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Api\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Api\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Api\Public\ArticleController;
use App\Http\Controllers\Api\Public\DoctorController;
use App\Http\Controllers\Api\Public\FaqController;
use App\Http\Controllers\Api\Public\GalleryController;
use App\Http\Controllers\Api\Public\PromoController;
use App\Http\Controllers\Api\Public\ReservationController;
use App\Http\Controllers\Api\Public\ServiceController;
use App\Http\Controllers\Api\Public\TestimonialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/promos', [PromoController::class, 'index']);
Route::get('/promos/{id}', [PromoController::class, 'show']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);

Route::get('/galleries', [GalleryController::class, 'index']);

Route::get('/doctors', [DoctorController::class, 'index']);

Route::get('/testimonials', [TestimonialController::class, 'index']);

Route::get('/faqs', [FaqController::class, 'index']);

Route::post('/reservations/new-patient', [ReservationController::class, 'storeNewPatient']);
Route::post('/reservations/existing-patient', [ReservationController::class, 'storeExistingPatient']);
Route::post('/reservations/check-patient', [ReservationController::class, 'checkPatient']);


Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        // Auth management
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        
        Route::middleware('role:registration')->group(function () {
            // CMS Management
            Route::apiResource('promos', AdminPromoController::class);
            Route::apiResource('services', AdminServiceController::class);
            Route::apiResource('articles', AdminArticleController::class);
            Route::apiResource('galleries', AdminGalleryController::class);
            Route::apiResource('doctors', AdminDoctorController::class);
            Route::apiResource('testimonials', AdminTestimonialController::class);
            Route::apiResource('faqs', AdminFaqController::class);
            
            // Dashboard & Analytics
            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/dashboard/reservation-stats', [DashboardController::class, 'reservationStats']);
            Route::get('/dashboard/service-analytics', [DashboardController::class, 'serviceAnalytics']);
            
            // Reservation Management
            Route::get('/reservations', [AdminReservationController::class, 'index']);
            Route::get('/reservations/{id}', [AdminReservationController::class, 'show']);
            Route::put('/reservations/{id}', [AdminReservationController::class, 'update']);
            Route::delete('/reservations/{id}', [AdminReservationController::class, 'destroy']);
            
            // Patient Management
            Route::get('/patients', [PatientController::class, 'index']);
            Route::get('/patients/{id}', [PatientController::class, 'show']);
            Route::put('/patients/{id}', [PatientController::class, 'update']);
            Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
            
            // Rontgen (View-only for registration role)
            Route::get('/rontgens', [RontgenController::class, 'index']);
            Route::get('/rontgens/{id}', [RontgenController::class, 'show']);
        });
        
        Route::middleware('role:rontgen')->group(function () {
            // Patient Data (View-only)
            Route::get('/patients', [PatientController::class, 'index']);
            Route::get('/patients/{id}', [PatientController::class, 'show']);
            
            // Rontgen Management
            Route::get('/rontgens', [RontgenController::class, 'index']);
            Route::post('/rontgens', [RontgenController::class, 'store']);
            Route::get('/rontgens/{id}', [RontgenController::class, 'show']);
            Route::put('/rontgens/{id}', [RontgenController::class, 'update']);
            Route::delete('/rontgens/{id}', [RontgenController::class, 'destroy']);
        });
    });
});
