<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PrimerRegistroController;
use App\Http\Controllers\Auth\CambiarPasswordController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\TrabajadorController;
use App\Http\Controllers\Admin\EntidadController;
use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\ResponsableController;
use App\Http\Controllers\Admin\EquipoController;

// Rutas de autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Primer registro
Route::get('/primer-registro', [PrimerRegistroController::class, 'showForm'])->name('primer.registro');
Route::post('/primer-registro', [PrimerRegistroController::class, 'register']);

// Cambio de contraseña
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [CambiarPasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [CambiarPasswordController::class, 'change']);
});

// Dashboard y rutas protegidas
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin - todas las rutas de administración dentro de este grupo
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        // Usuarios
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::patch('usuarios/{usuario}/toggle-status', [UsuarioController::class, 'toggleStatus'])->name('usuarios.toggle-status');
        Route::patch('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');
        Route::get('usuarios/{usuario}/detalle', [UsuarioController::class, 'show'])->name('usuarios.show');

        // Trabajadores
        Route::resource('trabajadores', TrabajadorController::class)->except(['show']);
        Route::get('trabajadores/buscar-cedula/{cedula}', [TrabajadorController::class, 'buscarPorCedula'])->name('trabajadores.buscar-cedula');
        Route::get('trabajadores/{trabajador}/detalle', [TrabajadorController::class, 'show'])->name('trabajadores.show');

        // Entidades (vista unificada)
        Route::get('/entidades', [EntidadController::class, 'index'])->name('entidades.index');

        // API Instituciones
        Route::get('instituciones', [InstitucionController::class, 'index'])->name('instituciones.index');
        Route::post('instituciones', [InstitucionController::class, 'store'])->name('instituciones.store');
        Route::get('instituciones/{institucione}', [InstitucionController::class, 'show'])->name('instituciones.show');
        Route::put('instituciones/{institucione}', [InstitucionController::class, 'update'])->name('instituciones.update');
        Route::delete('instituciones/{institucione}', [InstitucionController::class, 'destroy'])->name('instituciones.destroy');
        Route::patch('instituciones/{institucione}/toggle-status', [InstitucionController::class, 'toggleStatus'])->name('instituciones.toggle-status');

        // API Departamentos
        Route::get('departamentos', [DepartamentoController::class, 'index'])->name('departamentos.index');
        Route::post('departamentos', [DepartamentoController::class, 'store'])->name('departamentos.store');
        Route::get('departamentos/{departamento}', [DepartamentoController::class, 'show'])->name('departamentos.show');
        Route::put('departamentos/{departamento}', [DepartamentoController::class, 'update'])->name('departamentos.update');
        Route::delete('departamentos/{departamento}', [DepartamentoController::class, 'destroy'])->name('departamentos.destroy');
        Route::patch('departamentos/{departamento}/toggle-status', [DepartamentoController::class, 'toggleStatus'])->name('departamentos.toggle-status');
        Route::get('departamentos/por-institucion/{institucionId}', [DepartamentoController::class, 'porInstitucion'])->name('departamentos.por-institucion');

        // API Responsables
        Route::get('responsables', [ResponsableController::class, 'index'])->name('responsables.index');
        Route::post('responsables', [ResponsableController::class, 'store'])->name('responsables.store');
        Route::get('responsables/{responsable}', [ResponsableController::class, 'show'])->name('responsables.show');
        Route::put('responsables/{responsable}', [ResponsableController::class, 'update'])->name('responsables.update');
        Route::delete('responsables/{responsable}', [ResponsableController::class, 'destroy'])->name('responsables.destroy');
        Route::patch('responsables/{responsable}/toggle-status', [ResponsableController::class, 'toggleStatus'])->name('responsables.toggle-status');
        
        // ==================== EQUIPOS (CATÁLOGO) ====================
        // Vista principal
        Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');
        
        // Rutas API para Marcas
        Route::prefix('equipos')->group(function () {
            // ===== MARCAS =====
            Route::get('/marcas', [EquipoController::class, 'getMarcas']);
            Route::post('/marcas', [EquipoController::class, 'storeMarca']);
            Route::get('/marcas/{id}', [EquipoController::class, 'showMarca']);
            Route::put('/marcas/{id}', [EquipoController::class, 'updateMarca']);
            Route::delete('/marcas/{id}', [EquipoController::class, 'deleteMarca']);
            Route::patch('/marcas/{id}/toggle', [EquipoController::class, 'toggleMarca']);
            
            // ===== CATEGORÍAS =====
            Route::get('/categorias', [EquipoController::class, 'getCategorias']);
            Route::post('/categorias', [EquipoController::class, 'storeCategoria']);
            Route::get('/categorias/{id}', [EquipoController::class, 'showCategoria']);
            Route::put('/categorias/{id}', [EquipoController::class, 'updateCategoria']);
            Route::delete('/categorias/{id}', [EquipoController::class, 'deleteCategoria']);
            Route::patch('/categorias/{id}/toggle', [EquipoController::class, 'toggleCategoria']);
            
            // ===== MODELOS =====
            Route::get('/modelos', [EquipoController::class, 'getModelos']);
            Route::post('/modelos', [EquipoController::class, 'storeModelo']);
            Route::get('/modelos/{id}', [EquipoController::class, 'showModelo']);
            Route::put('/modelos/{id}', [EquipoController::class, 'updateModelo']);
            Route::delete('/modelos/{id}', [EquipoController::class, 'deleteModelo']);
            Route::patch('/modelos/{id}/toggle', [EquipoController::class, 'toggleModelo']);
            
            // ===== LISTAS PARA SELECTS =====
            Route::get('/marcas-list', [EquipoController::class, 'getMarcasList']);
            Route::get('/categorias-list', [EquipoController::class, 'getCategoriasList']);
        });
    });
});