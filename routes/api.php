<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\JournalController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\TherapistController;
use App\Http\Controllers\API\FocusPresetController;   // -> Controller baru untuk preset
use App\Http\Controllers\API\FocusSessionController;  // -> Controller baru untuk sesi fokus

// --- RUTE PUBLIK (Bisa diakses tanpa login) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// --- RUTE TERPROTEKSI (Wajib bawa Token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // --- FITUR PASIEN ---
    Route::get('/patients/therapists', [PatientController::class, 'getTherapists']); // Lihat daftar psikolog
    Route::post('/patients/appointments', [PatientController::class, 'bookSchedule']); // Booking jadwal
    Route::post('/patients/profile', [PatientController::class, 'updateProfile']);     // ✨ UBAH KE POST: Edit profil pasien (mendukung foto)

    // --- FITUR TERAPIS ---
    Route::post('/therapists/profile', [TherapistController::class, 'updateProfile']);         // ✨ UBAH KE POST: Edit profil terapis (mendukung foto)
    Route::get('/therapists/appointments', [TherapistController::class, 'getAppointments']);   // Lihat jadwal masuk
    Route::patch('/therapists/appointments/{id}/status', [TherapistController::class, 'updateAppointmentStatus']); // Terima/Tolak jadwal

    // --- FITUR JURNAL (Sudah disesuaikan dengan Swagger) ---
    Route::get('/journal/dates/entries', [JournalController::class, 'datesWithEntries']);
    Route::apiResource('journal', JournalController::class);

    // --- FITUR ARTIKEL ---
    Route::get('/articles', [ArticleController::class, 'index']);       // Pasien & Terapis bisa lihat semua artikel
    Route::get('/articles/{id}', [ArticleController::class, 'show']);   // Pasien & Terapis bisa baca detail artikel
    Route::post('/articles', [ArticleController::class, 'store']);      // Hanya Terapis

    // --- FITUR FOCUS MODE (Preset & Session) ---
    Route::apiResource('focus-preset', FocusPresetController::class);
    
    Route::prefix('focus-session')->group(function () {
        Route::post('/start', [FocusSessionController::class, 'start']);
        Route::post('/{id}/end', [FocusSessionController::class, 'end']);
        Route::get('/ongoing', [FocusSessionController::class, 'ongoing']);
        Route::get('/history', [FocusSessionController::class, 'history']);
        Route::get('/stats', [FocusSessionController::class, 'stats']);
    });

    // --- FITUR QUIZ ---
    Route::get('/quizzes', [QuizController::class, 'index']);           // Lihat daftar kuis
    Route::post('/quizzes/submit', [QuizController::class, 'submit']);  // Kirim hasil kuis

    // --- FITUR CHAT ---
    Route::get('/chat/{receiver_id}', [ChatController::class, 'getChatHistory']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);

    // --- FITUR VIDEO ---
    Route::get('/videos', [VideoController::class, 'index']);           // List video
    Route::get('/videos/{id}', [VideoController::class, 'show']);       // Detail 1 video
    
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');