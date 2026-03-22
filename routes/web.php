<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Redirigir la raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas para recuperación de contraseña
Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Dashboard (requiere autenticación)
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// Recuperación por preguntas de seguridad
Route::post('/password/verify-email', [AuthController::class, 'verifyEmailForRecovery'])->name('password.verify-email');
Route::post('/password/verify-answers', [AuthController::class, 'verifyAnswers'])->name('password.verify-answers');
Route::get('/password/reset-form', [AuthController::class, 'showResetPasswordForm'])->name('password.reset-form');
Route::post('/password/reset', [AuthController::class, 'resetPasswordByQuestions'])->name('password.reset-by-questions');

// Panel de administración
Route::get('/admin/users', [AuthController::class, 'adminUsers'])->name('admin.users')->middleware('auth');
Route::post('/admin/reset-password', [AuthController::class, 'adminResetPassword'])->name('admin.reset-password')->middleware('auth');

// Perfil de usuario
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/update', [App\Http\Controllers\ProfileController::class, 'updateInfo'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/security', [App\Http\Controllers\ProfileController::class, 'updateSecurity'])->name('profile.security');
});

// ✅ Ruta CORREGIDA - Ahora incluye {id}
Route::put('/admin/change-role/{id}', [AuthController::class, 'changeUserRole'])->name('admin.change-role')->middleware('auth');

// Auditoría y sesiones (solo super_admin)
Route::middleware(['auth', 'role:super_admin'])->prefix('audit')->group(function () {
    Route::get('/logs', [App\Http\Controllers\AuditController::class, 'index'])->name('audit.logs');
    Route::get('/sessions', [App\Http\Controllers\AuditController::class, 'sessions'])->name('audit.sessions');
    Route::get('/sessions/clear/{userId?}', [App\Http\Controllers\AuditController::class, 'clearSessions'])->name('audit.sessions.clear');
    Route::get('/user/{userId}', [App\Http\Controllers\AuditController::class, 'userActivity'])->name('audit.user-activity');
});
