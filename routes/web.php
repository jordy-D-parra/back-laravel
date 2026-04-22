<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\AprobacionController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\ReporteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirigir la raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// ========== RUTAS DE AUTENTICACIÓN ==========
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== RUTAS PARA RECUPERACIÓN DE CONTRASEÑA ==========
Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Recuperación por preguntas de seguridad (AJAX)
Route::post('/password/verify-cedula', [AuthController::class, 'verifyEmailForRecovery'])->name('password.verify-cedula');
Route::post('/password/verify-answers', [AuthController::class, 'verifyAnswers'])->name('password.verify-answers');
Route::get('/password/reset-form', [AuthController::class, 'showResetPasswordForm'])->name('password.reset-form');
Route::post('/password/reset', [AuthController::class, 'resetPasswordByQuestions'])->name('password.reset-by-questions');

// ========== DASHBOARD ==========
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// ========== PERFIL DE USUARIO ==========
Route::middleware(['auth'])->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/update', [ProfileController::class, 'updateInfo'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/security', [ProfileController::class, 'updateSecurity'])->name('profile.security');
});

// ========== NOTIFICACIONES ==========
Route::middleware(['auth'])->prefix('notificaciones')->group(function () {
    Route::get('/', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get('/contador', [NotificacionController::class, 'contadorNoLeidas'])->name('notificaciones.contador');
    Route::post('/{id}/leer', [NotificacionController::class, 'marcarComoLeida'])->name('notificaciones.leer');
    Route::post('/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcar-todas');
    Route::delete('/{id}', [NotificacionController::class, 'destroy'])->name('notificaciones.destroy');
});

// ========== PANEL DE ADMINISTRACIÓN ==========
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/users', [AuthController::class, 'adminUsers'])->name('admin.users');
    Route::put('/change-role/{id}', [AuthController::class, 'changeUserRole'])->name('admin.change-role')->middleware('role:super_admin,admin');
    Route::put('/change-status', [AuthController::class, 'changeUserStatus'])->name('admin.change-status')->middleware('role:super_admin,admin');
    Route::post('/reset-password', [AuthController::class, 'adminResetPassword'])->name('admin.reset-password')->middleware('role:super_admin,admin');
    Route::post('/activate-pending', [AuthController::class, 'activatePendingUsers'])->name('admin.activate-pending')->middleware('role:super_admin,admin');
    Route::get('/user-stats', [AuthController::class, 'getUserStats'])->name('admin.user-stats')->middleware('role:super_admin,admin');
    Route::get('/api/users', [AuthController::class, 'getUsersList'])->name('admin.api.users')->middleware('role:super_admin,admin');
    Route::get('/api/users/{id}', [AuthController::class, 'getUserDetails'])->name('admin.api.user')->middleware('role:super_admin,admin');
});

// ========== AUDITORÍA Y SESIONES ==========
Route::middleware(['auth', 'role:super_admin'])->prefix('audit')->group(function () {
    Route::get('/logs', [AuditController::class, 'index'])->name('audit.logs');
    Route::get('/sessions', [AuditController::class, 'sessions'])->name('audit.sessions');
    Route::get('/sessions/clear/{userId?}', [AuditController::class, 'clearSessions'])->name('audit.sessions.clear');
    Route::get('/user/{userId}', [AuditController::class, 'userActivity'])->name('audit.user-activity');
});

// ========== SOLICITUDES DE PRÉSTAMO ==========
Route::middleware(['auth'])->prefix('solicitudes')->group(function () {
    Route::get('/', [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/data', [SolicitudController::class, 'getData'])->name('solicitudes.data');
    Route::get('/create', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/', [SolicitudController::class, 'store'])->name('solicitudes.store');
    Route::get('/{solicitud}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::get('/{id}/items', [SolicitudController::class, 'getItemsJson'])->name('solicitudes.items.json');
    Route::post('/{solicitud}/approve', [SolicitudController::class, 'approve'])->name('solicitudes.approve');
    Route::post('/{solicitud}/reject', [SolicitudController::class, 'reject'])->name('solicitudes.reject');
    Route::post('/{solicitud}/cancel', [SolicitudController::class, 'cancel'])->name('solicitudes.cancel');
});

// ========== APROBACIONES Y PRÉSTAMOS (solo admin) ==========
Route::middleware(['auth', 'role:super_admin,admin'])->prefix('aprobaciones')->group(function () {
    Route::get('/', [AprobacionController::class, 'index'])->name('aprobaciones.index');
    Route::post('/{solicitud}/approve', [AprobacionController::class, 'approve'])->name('aprobaciones.approve');
    Route::post('/{solicitud}/reject', [AprobacionController::class, 'reject'])->name('aprobaciones.reject');
    Route::post('/{solicitud}/pending', [AprobacionController::class, 'pending'])->name('aprobaciones.pending');
    Route::post('/{prestamo}/extend', [AprobacionController::class, 'extendLoan'])->name('aprobaciones.extend');
    Route::post('/{prestamo}/partial-return', [AprobacionController::class, 'partialReturn'])->name('aprobaciones.partialReturn');
    Route::post('/{prestamo}/complete-return', [AprobacionController::class, 'completeReturn'])->name('aprobaciones.completeReturn');
    Route::get('/{prestamo}/acta-prestamo', [AprobacionController::class, 'generarActaPrestamo'])->name('aprobaciones.actaPrestamo');
    Route::get('/{prestamo}/acta-devolucion', [AprobacionController::class, 'generarActaDevolucion'])->name('aprobaciones.actaDevolucion');
    Route::post('/{solicitud}/realizar-prestamo', [AprobacionController::class, 'realizarPrestamo'])->name('aprobaciones.realizarPrestamo');
});

// ========== INVENTARIO ==========
Route::middleware(['auth'])->prefix('inventario')->name('inventario.')->group(function () {
    Route::middleware(['role:super_admin,admin'])->group(function () {
        Route::get('/', [ActivoController::class, 'index'])->name('index');
        Route::get('/data', [ActivoController::class, 'getData'])->name('data');
        Route::post('/', [ActivoController::class, 'store'])->name('store');
        Route::get('/{id}', [ActivoController::class, 'show'])->name('show');
        Route::put('/{id}', [ActivoController::class, 'update'])->name('update');
        Route::delete('/{id}', [ActivoController::class, 'destroy'])->name('destroy');
    });
});

// ========== SOPORTE / MANTENIMIENTO ==========
Route::middleware(['auth'])->prefix('soporte')->name('soporte.')->group(function () {
    Route::get('/', [SoporteController::class, 'index'])->name('index');
    Route::get('/create', [SoporteController::class, 'create'])->name('create');
    Route::post('/', [SoporteController::class, 'store'])->name('store');
    Route::get('/externo', [SoporteController::class, 'soporteExterno'])->name('externo');
    Route::post('/externo', [SoporteController::class, 'registrarExterno'])->name('externo.store');
    Route::get('/{ficha}', [SoporteController::class, 'show'])->name('show');
    Route::post('/{ficha}/asignar-tecnico', [SoporteController::class, 'asignarTecnico'])->name('asignarTecnico');
    Route::post('/{ficha}/trabajo', [SoporteController::class, 'actualizarTrabajo'])->name('actualizarTrabajo');
    Route::post('/{ficha}/completar', [SoporteController::class, 'completar'])->name('completar');
    Route::post('/{ficha}/componente', [SoporteController::class, 'agregarComponente'])->name('agregarComponente');
    Route::put('/detalle/{detalle}', [SoporteController::class, 'actualizarComponenteSalida'])->name('actualizarComponente');

    // API
    Route::get('/api/activo/{activo}/componentes', [SoporteController::class, 'getComponentes'])->name('api.componentes');
});

// ========== REPORTES Y EXPORTACIONES ==========
Route::middleware(['auth'])->prefix('reports')->group(function () {
    Route::get('/users', [ReportController::class, 'users'])->name('reports.users');
    Route::get('/solicitudes', [ReportController::class, 'solicitudes'])->name('reports.solicitudes');
    Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
});

// ========== REPORTES ==========
Route::middleware(['auth'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReporteController::class, 'index'])->name('index');

    // Reportes
    Route::post('/solicitudes/periodo', [ReporteController::class, 'solicitudesPeriodo'])->name('solicitudes.periodo');
    Route::post('/prestamos/activos', [ReporteController::class, 'prestamosActivos'])->name('prestamos.activos');
    Route::post('/inventario/general', [ReporteController::class, 'inventarioGeneral'])->name('inventario.general');
    Route::post('/equipos/disponibles', [ReporteController::class, 'equiposDisponibles'])->name('equipos.disponibles');
    Route::post('/soporte/periodo', [ReporteController::class, 'soportePeriodo'])->name('soporte.periodo');
    Route::post('/usuarios/activos', [ReporteController::class, 'usuariosActivos'])->name('usuarios.activos');
    Route::post('/resumen/ejecutivo', [ReporteController::class, 'resumenEjecutivo'])->name('resumen.ejecutivo');

    // Actas
    Route::post('/acta/prestamo', [ReporteController::class, 'actaPrestamo'])->name('acta.prestamo');
    Route::post('/acta/devolucion', [ReporteController::class, 'actaDevolucion'])->name('acta.devolucion');
});

// ========== RUTA DE PRUEBA ==========
Route::get('/test-sound', function () {
    return view('test-sound');
})->name('test.sound')->middleware('auth');
