<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PrimerRegistroController;
use App\Http\Controllers\Auth\CambiarPasswordController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\TrabajadorController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\EntidadController;
use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\ResponsableController;
use App\Http\Controllers\Admin\EquipoController;
use App\Http\Controllers\Admin\ModeloComponenteController;
use App\Http\Controllers\Admin\ActivoController;
use App\Http\Controllers\Admin\ComponenteController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\FichaSoporteController;
use App\Http\Controllers\Admin\PrestamoController;
use App\Models\Estatus;
use Illuminate\Http\Request;

// ==================== RUTA PRINCIPAL ====================
Route::get('/', function () {
    return redirect('/login');
});

// ==================== AUTENTICACIÓN ====================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Primer registro (solo cuando no hay usuarios)
Route::get('/primer-registro', [PrimerRegistroController::class, 'showForm'])->name('primer.registro');
Route::post('/primer-registro', [PrimerRegistroController::class, 'register']);

// Cambio de contraseña (usuarios autenticados)
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [CambiarPasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [CambiarPasswordController::class, 'change']);
});

// ==================== RUTAS PROTEGIDAS ====================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== ADMINISTRACIÓN (solo para rol admin) ====================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        // ========== 1. MAESTROS (Catálogos base del sistema) ==========

        // 1.1 Entidades (Instituciones, Departamentos, Responsables)
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

        // 1.2 Catálogo de Equipos (Marcas, Categorías, Modelos)
        Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');

        Route::prefix('equipos')->group(function () {
            // Marcas
            Route::get('/marcas', [EquipoController::class, 'getMarcas']);
            Route::post('/marcas', [EquipoController::class, 'storeMarca']);
            Route::get('/marcas/{id}', [EquipoController::class, 'showMarca']);
            Route::put('/marcas/{id}', [EquipoController::class, 'updateMarca']);
            Route::delete('/marcas/{id}', [EquipoController::class, 'deleteMarca']);
            Route::patch('/marcas/{id}/toggle', [EquipoController::class, 'toggleMarca']);

            // Categorías
            Route::get('/categorias', [EquipoController::class, 'getCategorias']);
            Route::post('/categorias', [EquipoController::class, 'storeCategoria']);
            Route::get('/categorias/{id}', [EquipoController::class, 'showCategoria']);
            Route::put('/categorias/{id}', [EquipoController::class, 'updateCategoria']);
            Route::delete('/categorias/{id}', [EquipoController::class, 'deleteCategoria']);
            Route::patch('/categorias/{id}/toggle', [EquipoController::class, 'toggleCategoria']);

            // Modelos
            Route::get('/modelos', [EquipoController::class, 'getModelos']);
            Route::post('/modelos', [EquipoController::class, 'storeModelo']);
            Route::get('/modelos/{id}', [EquipoController::class, 'showModelo']);
            Route::put('/modelos/{id}', [EquipoController::class, 'updateModelo']);
            Route::delete('/modelos/{id}', [EquipoController::class, 'deleteModelo']);
            Route::patch('/modelos/{id}/toggle', [EquipoController::class, 'toggleModelo']);

            // Componentes de modelo
            Route::get('/modelos/{modeloId}/componentes', [ModeloComponenteController::class, 'index']);
            Route::post('/modelos/{modeloId}/componentes', [ModeloComponenteController::class, 'store']);
            Route::get('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'show']);
            Route::put('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'update']);
            Route::delete('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'destroy']);

            // Listas para selects
            Route::get('/marcas-list', [EquipoController::class, 'getMarcasList']);
            Route::get('/categorias-list', [EquipoController::class, 'getCategoriasList']);
        });

        // ========== 2. GESTIÓN DE USUARIOS ==========

        // 2.1 Roles y Permisos
        Route::get('/roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/list', [App\Http\Controllers\Admin\RoleController::class, 'getRoles'])->name('roles.list');
        Route::get('/roles/{id}', [App\Http\Controllers\Admin\RoleController::class, 'show'])->name('roles.show');
        Route::post('/roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{id}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('/permisos/todos', [App\Http\Controllers\Admin\RoleController::class, 'getPermisos'])->name('permisos.todos');

        // 2.2 Trabajadores
        Route::resource('trabajadores', TrabajadorController::class)->except(['show']);
        Route::get('trabajadores/buscar-cedula/{cedula}', [TrabajadorController::class, 'buscarPorCedula'])->name('trabajadores.buscar-cedula');
        Route::get('trabajadores/{trabajador}/detalle', [TrabajadorController::class, 'show'])->name('trabajadores.show');

        // 2.3 Usuarios del Sistema
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::patch('usuarios/{usuario}/toggle-status', [UsuarioController::class, 'toggleStatus'])->name('usuarios.toggle-status');
        Route::patch('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');
        Route::get('usuarios/{usuario}/detalle', [UsuarioController::class, 'show'])->name('usuarios.show');

        // ========== 3. PROCESOS OPERATIVOS ==========

        // 3.1 Inventario (Activos y Componentes)
        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

        // API Activos
        Route::get('/activos', [ActivoController::class, 'index']);
        Route::post('/activos', [ActivoController::class, 'store']);
        Route::get('/activos/{activo}', [ActivoController::class, 'show']);
        Route::put('/activos/{activo}', [ActivoController::class, 'update']);
        Route::delete('/activos/{activo}', [ActivoController::class, 'destroy']);
        Route::patch('/activos/{activo}/toggle-status', [ActivoController::class, 'toggleStatus']);
        Route::get('/activos/por-modelo/{modeloId}', [ActivoController::class, 'porModelo']);

        // API Componentes
        Route::get('/componentes', [ComponenteController::class, 'index']);
        Route::post('/componentes', [ComponenteController::class, 'store']);
        Route::get('/componentes/{componente}', [ComponenteController::class, 'show']);
        Route::put('/componentes/{componente}', [ComponenteController::class, 'update']);
        Route::delete('/componentes/{componente}', [ComponenteController::class, 'destroy']);
        Route::patch('/componentes/{componente}/toggle-status', [ComponenteController::class, 'toggleStatus']);
        Route::get('/componentes/por-tipo/{tipo}', [ComponenteController::class, 'porTipo']);
        Route::get('/componentes/en-bodega', [ComponenteController::class, 'enBodega']);

        // 3.2 PRÉSTAMOS
        Route::get('prestamo/listar', [PrestamoController::class, 'listar'])->name('prestamo.listar');
        Route::get('prestamo/datos-form', [PrestamoController::class, 'datosForm'])->name('prestamo.datos-form');
        Route::post('prestamo/solicitud/{solicitud}/registrar', [PrestamoController::class, 'registrarDesdeSolicitud'])
            ->name('prestamo.registrar-desde-solicitud');
        Route::resource('prestamo', PrestamoController::class);

        // ========== 4. SOLICITUDES ==========
        Route::prefix('solicitudes')->name('solicitudes.')->group(function () {
            Route::get('/', [SolicitudController::class, 'index'])->name('index');
            Route::get('/{solicitud}/detalles', [SolicitudController::class, 'getDetalles'])->name('detalles');
            Route::post('/store', [SolicitudController::class, 'store'])->name('store');
            Route::post('/{solicitud}/update', [SolicitudController::class, 'update'])->name('update');
            Route::post('/{solicitud}/cancel', [SolicitudController::class, 'cancel'])->name('cancel');
            Route::post('/{solicitud}/approve', [SolicitudController::class, 'approve'])->name('approve');
            Route::post('/{solicitud}/reject', [SolicitudController::class, 'reject'])->name('reject');
        });

        // ========== FICHAS DE SOPORTE ==========
        Route::resource('soporte', \App\Http\Controllers\Admin\FichaSoporteController::class);
        Route::get('soporte/{id}/componentes', [\App\Http\Controllers\Admin\FichaSoporteController::class, 'getComponentesDetalle'])->name('soporte.componentes');
        Route::post('soporte/{id}/close', [\App\Http\Controllers\Admin\FichaSoporteController::class, 'close'])->name('soporte.close');

        // 3.4 Reportes (para futuro)
        // Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

        // ========== 5. UTILIDADES ==========
        Route::get('/estatus-list', function () {
            $estatus = Estatus::select('id', 'descripcion', 'color_badge')->orderBy('descripcion')->get();
            return response()->json(['success' => true, 'data' => $estatus]);
        });
    });

    // ==================== API PARA RESPONSABLES (FUERA DEL GRUPO ADMIN) ====================
    
    // API para obtener responsables de entidades (usado por el frontend)
    Route::get('/api/departamento/{id}/responsable', function ($id) {
        $departamento = App\Models\Departamento::with('responsables')->find($id);
        $responsable = $departamento ? $departamento->responsables->first() : null;
        return response()->json([
            'responsable' => $responsable ? [
                'id' => $responsable->id,
                'nombre' => $responsable->nombre,
                'departamento' => $responsable->cargo,
                'telefono' => $responsable->telefono,
                'email' => $responsable->email,
            ] : null
        ]);
    });

    Route::get('/api/institucion/{id}/responsable', function ($id) {
        $institucion = App\Models\Institucion::with('responsablesDirectos')->find($id);
        $responsable = $institucion ? $institucion->responsablesDirectos->first() : null;
        return response()->json([
            'responsable' => $responsable ? [
                'id' => $responsable->id,
                'nombre' => $responsable->nombre,
                'departamento' => $responsable->cargo,
                'telefono' => $responsable->telefono,
                'email' => $responsable->email,
            ] : null
        ]);
    });

    Route::post('/api/departamento/{id}/responsable', function (Request $request, $id) {
        $departamento = App\Models\Departamento::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'responsable_id' => 'nullable|exists:responsables,id'
        ]);

        if ($request->responsable_id) {
            $responsable = App\Models\Responsable::find($request->responsable_id);
            $responsable->update([
                'nombre' => $data['nombre'],
                'cargo' => $data['cargo'] ?? $responsable->cargo,
                'telefono' => $data['telefono'] ?? $responsable->telefono,
                'email' => $data['email'] ?? $responsable->email,
            ]);
        } else {
            $responsable = App\Models\Responsable::create([
                'nombre' => $data['nombre'],
                'cargo' => $data['cargo'] ?? 'Jefe de Departamento',
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'] ?? null,
                'activo' => true,
                'institucion_id' => $departamento->institucion_id,
                'departamento_id' => $departamento->id,
            ]);
        }

        return response()->json(['success' => true, 'responsable' => $responsable]);
    });

    Route::post('/api/institucion/{id}/responsable', function (Request $request, $id) {
        $institucion = App\Models\Institucion::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'responsable_id' => 'nullable|exists:responsables,id'
        ]);

        if ($request->responsable_id) {
            $responsable = App\Models\Responsable::find($request->responsable_id);
            $responsable->update([
                'nombre' => $data['nombre'],
                'cargo' => $data['cargo'] ?? $responsable->cargo,
                'telefono' => $data['telefono'] ?? $responsable->telefono,
                'email' => $data['email'] ?? $responsable->email,
            ]);
        } else {
            $responsable = App\Models\Responsable::create([
                'nombre' => $data['nombre'],
                'cargo' => $data['cargo'] ?? 'Representante',
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'] ?? null,
                'activo' => true,
                'institucion_id' => $institucion->id,
                'departamento_id' => null,
            ]);
        }

        return response()->json(['success' => true, 'responsable' => $responsable]);
    });
});