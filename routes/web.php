<?php

// routes/web.php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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
use App\Http\Controllers\Admin\CalendarioController;
use App\Http\Controllers\Admin\ActaEntregaController;
use App\Http\Controllers\Admin\ActaDevolucionController;
use App\Http\Controllers\Admin\NotificacionController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\ReporteInventarioController;
use App\Http\Controllers\Admin\UbicacionController;
use App\Models\Estatus;

// ==================== RUTA PRINCIPAL ====================

Route::get('/', function () {
    return redirect('/login');
});

// ==================== AUTENTICACIÓN ====================

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

// ==================== RUTAS PROTEGIDAS ====================

Route::middleware(['auth', 'prevent-back-history'])->group(function () {

    // ========== DASHBOARD ==========
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ========== CALENDARIO ==========
    Route::prefix('calendario')->name('calendario.')->group(function () {
        Route::get('/', [CalendarioController::class, 'index'])->name('index');
        Route::get('/eventos', [CalendarioController::class, 'getEventos'])->name('eventos');
        Route::get('/evento', [CalendarioController::class, 'getEventoDetalle'])->name('evento.detalle');
    });

    // ==================== ADMINISTRACIÓN ====================
    Route::prefix('admin')->middleware(['auditoria'])->name('admin.')->group(function () {

        // ========== AUDITORÍA ==========
        // ⚠️ Ruta específica ANTES del resource
        Route::post('auditoria/limpiar', [AuditoriaController::class, 'limpiar'])->name('auditoria.limpiar');
        Route::resource('auditoria', AuditoriaController::class)->only(['index', 'show', 'destroy']);

        // ========== 1. MAESTROS ==========

        // ------ ENTIDADES (vista unificada) ------
        Route::get('/entidades', [EntidadController::class, 'index'])->name('entidades.index');

        // ------ INSTITUCIONES ------
        Route::get('instituciones', [InstitucionController::class, 'index'])->name('instituciones.index');
        Route::post('instituciones', [InstitucionController::class, 'store'])->name('instituciones.store');
        Route::get('instituciones/{institucione}', [InstitucionController::class, 'show'])->name('instituciones.show');
        Route::put('instituciones/{institucione}', [InstitucionController::class, 'update'])->name('instituciones.update');
        Route::delete('instituciones/{institucione}', [InstitucionController::class, 'destroy'])->name('instituciones.destroy');
        Route::patch('instituciones/{institucione}/toggle-status', [InstitucionController::class, 'toggleStatus'])->name('instituciones.toggle-status');

        // ------ DEPARTAMENTOS ------
        // ⚠️ Ruta específica ANTES de {departamento}
        Route::get('departamentos/por-institucion/{institucionId}', [DepartamentoController::class, 'porInstitucion'])->name('departamentos.por-institucion');
        Route::get('departamentos', [DepartamentoController::class, 'index'])->name('departamentos.index');
        Route::post('departamentos', [DepartamentoController::class, 'store'])->name('departamentos.store');
        Route::get('departamentos/{departamento}', [DepartamentoController::class, 'show'])->name('departamentos.show');
        Route::put('departamentos/{departamento}', [DepartamentoController::class, 'update'])->name('departamentos.update');
        Route::delete('departamentos/{departamento}', [DepartamentoController::class, 'destroy'])->name('departamentos.destroy');
        Route::patch('departamentos/{departamento}/toggle-status', [DepartamentoController::class, 'toggleStatus'])->name('departamentos.toggle-status');

        // ------ RESPONSABLES ------
        Route::get('responsables', [ResponsableController::class, 'index'])->name('responsables.index');
        Route::post('responsables', [ResponsableController::class, 'store'])->name('responsables.store');
        Route::get('responsables/{responsable}', [ResponsableController::class, 'show'])->name('responsables.show');
        Route::put('responsables/{responsable}', [ResponsableController::class, 'update'])->name('responsables.update');
        Route::delete('responsables/{responsable}', [ResponsableController::class, 'destroy'])->name('responsables.destroy');
        Route::patch('responsables/{responsable}/toggle-status', [ResponsableController::class, 'toggleStatus'])->name('responsables.toggle-status');

        // ========== 1.2 CATÁLOGO DE EQUIPOS ==========
        Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');

        Route::prefix('equipos')->group(function () {

            // ------ MARCAS ------
            // ⚠️ marcas-list ANTES de marcas/{id}
            Route::get('/marcas-list', [EquipoController::class, 'getMarcasList']);
            Route::get('/marcas', [EquipoController::class, 'getMarcas']);
            Route::post('/marcas', [EquipoController::class, 'storeMarca']);
            Route::get('/marcas/{id}', [EquipoController::class, 'showMarca']);
            Route::put('/marcas/{id}', [EquipoController::class, 'updateMarca']);
            Route::delete('/marcas/{id}', [EquipoController::class, 'deleteMarca']);
            Route::patch('/marcas/{id}/toggle', [EquipoController::class, 'toggleMarca']);

            // ------ CATEGORÍAS ------
            // ⚠️ Rutas específicas ANTES de categorias/{id}
            Route::get('/categorias-list', [EquipoController::class, 'getCategoriasList']);
            Route::get('/categorias-por-marca/{marcaId}', [EquipoController::class, 'getCategoriasPorMarca']);
            Route::get('/categorias', [EquipoController::class, 'getCategorias']);
            Route::post('/categorias', [EquipoController::class, 'storeCategoria']);
            Route::get('/categorias/{id}', [EquipoController::class, 'showCategoria']);
            Route::put('/categorias/{id}', [EquipoController::class, 'updateCategoria']);
            Route::delete('/categorias/{id}', [EquipoController::class, 'deleteCategoria']);
            Route::patch('/categorias/{id}/toggle', [EquipoController::class, 'toggleCategoria']);

            // ------ MODELOS ------
            Route::get('/modelos', [EquipoController::class, 'getModelos']);
            Route::post('/modelos', [EquipoController::class, 'storeModelo']);
            Route::get('/modelos/{id}', [EquipoController::class, 'showModelo']);
            Route::put('/modelos/{id}', [EquipoController::class, 'updateModelo']);
            Route::delete('/modelos/{id}', [EquipoController::class, 'deleteModelo']);
            Route::patch('/modelos/{id}/toggle', [EquipoController::class, 'toggleModelo']);

            // ------ COMPONENTES POR MODELO ------
            Route::get('/modelos/{modeloId}/componentes', [ModeloComponenteController::class, 'index']);
            Route::post('/modelos/{modeloId}/componentes', [ModeloComponenteController::class, 'store']);
            Route::get('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'show']);
            Route::put('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'update']);
            Route::delete('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'destroy']);
        });

        // ========== 2. GESTIÓN DE USUARIOS ==========

        // ------ ROLES ------
        // ⚠️ /roles/list ANTES de /roles/{id}
        Route::get('/roles/list', [RoleController::class, 'getRoles'])->name('roles.list');
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('/permisos/todos', [RoleController::class, 'getPermisos'])->name('permisos.todos');

        // ------ TRABAJADORES ------
       // ============ TRABAJADORES ============
// ⚠️ Rutas específicas ANTES del resource
 // Ruta personalizada para buscar por cédula (debe ir ANTES del resource)
    Route::get('trabajadores/buscar-cedula/{cedula}', [TrabajadorController::class, 'buscarPorCedula'])
        ->name('trabajadores.buscar-cedula');
    Route::get('/trabajadores/{trabajador}/detalle', [TrabajadorController::class, 'show'])
    ->name('trabajadores.show'); 
    // Resource con parameter correcto
    Route::resource('trabajadores', TrabajadorController::class)
        ->parameters(['trabajadores' => 'trabajador']);

        // ------ USUARIOS ------
        // ⚠️ Rutas específicas ANTES del resource
        Route::patch('usuarios/{usuario}/toggle-status', [UsuarioController::class, 'toggleStatus'])->name('usuarios.toggle-status');
        Route::patch('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');
        Route::get('usuarios/{usuario}/detalle', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::resource('usuarios', UsuarioController::class)->except(['show']);

        // ========== 3. PROCESOS OPERATIVOS ==========

        // ------ INVENTARIO ------
        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

        // ------ ACTIVOS ------
        // ⚠️ Rutas específicas ANTES de /activos/{activo}
        Route::get('/activos/por-modelo/{modeloId}', [ActivoController::class, 'porModelo']);
        Route::get('/activos', [ActivoController::class, 'index']);
        Route::post('/activos', [ActivoController::class, 'store']);
        Route::get('/activos/{activo}', [ActivoController::class, 'show']);
        Route::put('/activos/{activo}', [ActivoController::class, 'update']);
        Route::delete('/activos/{activo}', [ActivoController::class, 'destroy']);
        Route::patch('/activos/{activo}/toggle-status', [ActivoController::class, 'toggleStatus']);

        // ------ COMPONENTES ------
        // ⚠️ Rutas específicas PRIMERO, {componente} AL FINAL
        Route::get('/componentes/disponibles', [ComponenteController::class, 'disponibles'])->name('componentes.disponibles');
        Route::get('/componentes/en-bodega', [ComponenteController::class, 'enBodega']);
        Route::get('/componentes/por-tipo/{tipo}', [ComponenteController::class, 'porTipo']);
        Route::get('/componentes', [ComponenteController::class, 'index']);
        Route::post('/componentes', [ComponenteController::class, 'store']);
        Route::get('/componentes/{componente}', [ComponenteController::class, 'show']);
        Route::put('/componentes/{componente}', [ComponenteController::class, 'update']);
        Route::delete('/componentes/{componente}', [ComponenteController::class, 'destroy']);
        Route::patch('/componentes/{componente}/toggle-status', [ComponenteController::class, 'toggleStatus']);

        // ------ PRÉSTAMOS ------
        Route::prefix('prestamos')->name('prestamos.')->group(function () {

            // Rutas específicas PRIMERO
            Route::get('/listar', [PrestamoController::class, 'listar'])->name('listar');
            Route::get('/buscar-responsable', [PrestamoController::class, 'buscarResponsableDestino'])->name('buscar-responsable');
            Route::get('/buscar-items', [PrestamoController::class, 'buscarItems'])->name('buscar-items');
            Route::get('/para-prestamo', [PrestamoController::class, 'paraPrestamo'])->name('para-prestamo');

            // CRUD
            Route::get('/', [PrestamoController::class, 'index'])->name('index');
            Route::post('/', [PrestamoController::class, 'store'])->name('store');
            Route::get('/{prestamo}', [PrestamoController::class, 'show'])->name('show');
            Route::put('/{prestamo}', [PrestamoController::class, 'update'])->name('update');

            // Acciones
            Route::post('/{prestamo}/aprobar', [PrestamoController::class, 'aprobar'])->name('aprobar');
            Route::post('/{prestamo}/rechazar', [PrestamoController::class, 'rechazar'])->name('rechazar');
            Route::post('/{prestamo}/entregar', [PrestamoController::class, 'entregar'])->name('entregar');
            Route::post('/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('devolver');
            Route::post('/{prestamo}/cancelar', [PrestamoController::class, 'cancelar'])->name('cancelar');
            Route::post('/{prestamo}/extender', [PrestamoController::class, 'extender'])->name('extender');
        });

        // ------ SOLICITUDES ------
        Route::prefix('solicitudes')->name('solicitudes.')->group(function () {

            // ⚠️ Correos (ANTES de /{solicitud})
            Route::get('/correos/lista', [SolicitudController::class, 'correosIndex'])->name('correos.index');
            Route::get('/correos/contador', [SolicitudController::class, 'correosContador'])->name('correos.contador');
            Route::post('/correos/revisar', [SolicitudController::class, 'correosRevisar'])->name('correos.revisar');
            Route::get('/correos/{id}', [SolicitudController::class, 'correoShow'])->name('correos.show');
            Route::delete('/correos/{id}', [SolicitudController::class, 'correoDestroy'])->name('correos.destroy');
            Route::post('/correos/{id}/convertir', [SolicitudController::class, 'correoConvertir'])->name('correos.convertir');

            // ⚠️ Rutas específicas ANTES de /{solicitud}
            Route::get('/pendientes-prestamo', [SolicitudController::class, 'paraPrestamo'])->name('pendientes-prestamo');
            Route::post('/store', [SolicitudController::class, 'store'])->name('store');

            // CRUD
            Route::get('/', [SolicitudController::class, 'index'])->name('index');
            Route::get('/{solicitud}/detalles', [SolicitudController::class, 'getDetalles'])->name('detalles');
            Route::post('/{solicitud}/update', [SolicitudController::class, 'update'])->name('update');
            Route::post('/{solicitud}/cancel', [SolicitudController::class, 'cancel'])->name('cancel');
            Route::post('/{solicitud}/approve', [SolicitudController::class, 'approve'])->name('approve');
            Route::post('/{solicitud}/reject', [SolicitudController::class, 'reject'])->name('reject');
        });

        // ------ SOPORTE TÉCNICO ------
        // Correos de soporte (ANTES del resource)
        Route::prefix('soporte/correos')->name('soporte.correos.')->group(function () {
            Route::get('/lista', [FichaSoporteController::class, 'correosIndex'])->name('index');
            Route::get('/contador', [FichaSoporteController::class, 'correosContador'])->name('contador');
            Route::post('/revisar', [FichaSoporteController::class, 'correosRevisar'])->name('revisar');
            Route::get('/{id}', [FichaSoporteController::class, 'correoShow'])->name('show');
            Route::post('/{id}/convertir', [FichaSoporteController::class, 'correoConvertir'])->name('convertir');
        });

        // Rutas específicas de soporte (ANTES del resource)
        Route::post('soporte/equipo-externo', [FichaSoporteController::class, 'storeEquipoExterno'])->name('soporte.equipo-externo');
        Route::get('soporte/{id}/componentes', [FichaSoporteController::class, 'getComponentesDetalle'])->name('soporte.componentes');
        Route::post('soporte/{id}/close', [FichaSoporteController::class, 'close'])->name('soporte.close');

        // Resource al final
        Route::resource('soporte', FichaSoporteController::class);

        // ------ ACTAS ------
        Route::prefix('actas')->name('actas.')->group(function () {

            // Acta de Entrega
            Route::get('/generar', [ActaEntregaController::class, 'generarDesdePrestamo'])->name('generar');
            Route::get('/imprimir/{id}', [ActaEntregaController::class, 'imprimir'])->name('imprimir');

            // Acta de Devolución
            Route::get('/devolucion/generar', [ActaDevolucionController::class, 'generarDesdePrestamo'])->name('devolucion.generar');
            Route::get('/devolucion/imprimir/{id}', [ActaDevolucionController::class, 'imprimir'])->name('devolucion.imprimir');
        });

        // ========== 4. REPORTES ==========
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/inventario', [ReporteInventarioController::class, 'index'])->name('inventario');
            Route::get('/inventario/exportar-pdf', [ReporteInventarioController::class, 'exportarPdf'])->name('inventario.exportar-pdf');
            Route::get('/inventario/exportar-excel', [ReporteInventarioController::class, 'exportarExcel'])->name('inventario.exportar-excel');
        });

        // ========== 5. UTILIDADES ==========
        Route::get('/estatus-list', function () {
            $estatus = Estatus::select('id', 'descripcion', 'color_badge')->orderBy('descripcion')->get();
            return response()->json(['success' => true, 'data' => $estatus]);
        });

        // ------ UBICACIONES ------
        Route::get('/ubicaciones/estados', [UbicacionController::class, 'getEstados'])->name('ubicaciones.estados');
        Route::get('/ubicaciones/estados/{estadoId}/municipios', [UbicacionController::class, 'getMunicipios'])->name('ubicaciones.municipios');
        Route::get('/ubicaciones/municipios/{municipioId}/parroquias', [UbicacionController::class, 'getParroquias'])->name('ubicaciones.parroquias');

        // ------ NOTIFICACIONES ------
        Route::prefix('notificaciones')->name('notificaciones.')->group(function () {

            // ⚠️ Rutas específicas PRIMERO
            Route::post('/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasComoLeidas'])->name('marcar-todas');
            Route::get('/no-leidas', [NotificacionController::class, 'obtenerNoLeidas'])->name('no-leidas');

            // CRUD
            Route::get('/', [NotificacionController::class, 'index'])->name('index');
            Route::post('/{id}/leer', [NotificacionController::class, 'marcarComoLeida'])->name('leer');

            Route::get('/{id}/detalle', function ($id) {
                $notificacion = App\Models\Notificacion::findOrFail($id);
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $notificacion->id,
                        'titulo' => $notificacion->titulo,
                        'mensaje' => $notificacion->mensaje,
                        'tipo' => $notificacion->tipo,
                        'url' => $notificacion->url,
                        'leida' => $notificacion->leida,
                        'fecha_envio' => $notificacion->fecha_envio->toISOString(),
                    ]
                ]);
            })->name('detalle');
        });

        // ========== API PARA RESPONSABLES ==========

        // --- GET: Obtener responsable de departamento ---
        Route::get('/api/departamento/{id}/responsable', function ($id) {
            $departamento = App\Models\Departamento::with('responsables')->find($id);
            $responsable = $departamento ? $departamento->responsables->first() : null;

            return response()->json([
                'responsable' => $responsable ? [
                    'id' => $responsable->id,
                    'nombre' => $responsable->nombre,
                    'documento' => $responsable->documento,
                    'cargo' => $responsable->cargo,
                    'telefono' => $responsable->telefono,
                    'email' => $responsable->email,
                    'direccion' => $responsable->direccion,
                ] : null
            ]);
        });

        // --- GET: Obtener responsable de institución ---
        Route::get('/api/institucion/{id}/responsable', function ($id) {
            $institucion = App\Models\Institucion::with('responsablesDirectos')->find($id);
            $responsable = $institucion ? $institucion->responsablesDirectos->first() : null;

            return response()->json([
                'responsable' => $responsable ? [
                    'id' => $responsable->id,
                    'nombre' => $responsable->nombre,
                    'documento' => $responsable->documento,
                    'cargo' => $responsable->cargo,
                    'telefono' => $responsable->telefono,
                    'email' => $responsable->email,
                    'direccion' => $responsable->direccion,
                ] : null
            ]);
        });

        // --- POST: Actualizar/Crear responsable de departamento ---
        Route::post('/api/departamento/{id}/responsable', function (Request $request, $id) {
            $departamento = App\Models\Departamento::findOrFail($id);

            $data = $request->validate([
                'nombre' => 'required|string|max:150',
                'documento' => 'nullable|string|max:50',
                'cargo' => 'nullable|string|max:100',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'direccion' => 'nullable|string|max:300',
                'responsable_id' => 'nullable|exists:responsables,id'
            ]);

            if ($request->responsable_id) {
                $responsable = App\Models\Responsable::find($request->responsable_id);
                $responsable->update([
                    'nombre' => $data['nombre'],
                    'documento' => $data['documento'] ?? $responsable->documento,
                    'cargo' => $data['cargo'] ?? $responsable->cargo,
                    'telefono' => $data['telefono'] ?? $responsable->telefono,
                    'email' => $data['email'] ?? $responsable->email,
                    'direccion' => $data['direccion'] ?? $responsable->direccion,
                ]);
            } else {
                $responsable = App\Models\Responsable::create([
                    'nombre' => $data['nombre'],
                    'documento' => $data['documento'] ?? null,
                    'cargo' => $data['cargo'] ?? 'Jefe de Departamento',
                    'telefono' => $data['telefono'] ?? null,
                    'email' => $data['email'] ?? null,
                    'direccion' => $data['direccion'] ?? null,
                    'activo' => true,
                    'institucion_id' => $departamento->institucion_id,
                    'departamento_id' => $departamento->id,
                ]);
            }

            return response()->json(['success' => true, 'responsable' => $responsable]);
        });

        // --- POST: Actualizar/Crear responsable de institución ---
        Route::post('/api/institucion/{id}/responsable', function (Request $request, $id) {
            $institucion = App\Models\Institucion::findOrFail($id);

            $data = $request->validate([
                'nombre' => 'required|string|max:150',
                'documento' => 'nullable|string|max:50',
                'cargo' => 'nullable|string|max:100',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'direccion' => 'nullable|string|max:300',
                'responsable_id' => 'nullable|exists:responsables,id'
            ]);

            if ($request->responsable_id) {
                $responsable = App\Models\Responsable::find($request->responsable_id);
                $responsable->update([
                    'nombre' => $data['nombre'],
                    'documento' => $data['documento'] ?? $responsable->documento,
                    'cargo' => $data['cargo'] ?? $responsable->cargo,
                    'telefono' => $data['telefono'] ?? $responsable->telefono,
                    'email' => $data['email'] ?? $responsable->email,
                    'direccion' => $data['direccion'] ?? $responsable->direccion,
                ]);
            } else {
                $responsable = App\Models\Responsable::create([
                    'nombre' => $data['nombre'],
                    'documento' => $data['documento'] ?? null,
                    'cargo' => $data['cargo'] ?? 'Representante',
                    'telefono' => $data['telefono'] ?? null,
                    'email' => $data['email'] ?? null,
                    'direccion' => $data['direccion'] ?? null,
                    'activo' => true,
                    'institucion_id' => $institucion->id,
                    'departamento_id' => null,
                ]);
            }

            return response()->json(['success' => true, 'responsable' => $responsable]);
        });
    });

    // ==================== API PARA TÉCNICOS (SOPORTE) ====================
    Route::prefix('admin/api')->group(function () {
        Route::get('/tecnicos', function (Request $request) {
            $search = $request->get('search');
            $query = App\Models\Usuario::whereHas('rol', function ($q) {
                $q->where('nombre', 'tecnico');
            })->with('trabajador');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('usuario', 'like', "%{$search}%")
                      ->orWhereHas('trabajador', function ($tq) use ($search) {
                          $tq->where('cedula', 'like', "%{$search}%")
                             ->orWhere('nombre', 'like', "%{$search}%")
                             ->orWhere('apellido', 'like', "%{$search}%");
                      });
                });
            }

            $tecnicos = $query->limit(15)->get();
            return response()->json($tecnicos);
        });

        Route::get('/tecnicos/{id}', function ($id) {
            $tecnico = App\Models\Usuario::whereHas('rol', function ($q) {
                $q->where('nombre', 'tecnico');
            })->with('trabajador')->find($id);

            if (!$tecnico) {
                return response()->json(['success' => false, 'message' => 'Técnico no encontrado'], 404);
            }

            return response()->json(['success' => true, 'data' => $tecnico]);
        });
    });
});