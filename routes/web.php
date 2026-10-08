<?php

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
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\UbicacionController;
use App\Http\Controllers\ProfileController;
use App\Models\Estatus;

// ==================== RUTA PRINCIPAL ====================
Route::get('/', fn() => redirect('/login'));

// ==================== AUTENTICACIÓN ====================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/primer-registro', [PrimerRegistroController::class, 'showForm'])->name('primer.registro');
Route::post('/primer-registro', [PrimerRegistroController::class, 'register']);

Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [CambiarPasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [CambiarPasswordController::class, 'change']);
});

// ==================== RUTAS PROTEGIDAS ====================
Route::middleware(['auth', 'prevent-back-history'])->group(function () {

    // ========== DASHBOARD ==========
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ========== CALENDARIO ==========
    Route::prefix('calendario')->name('calendario.')->middleware('permission:ver-calendario')->group(function () {
        Route::get('/', [CalendarioController::class, 'index'])->name('index');
        Route::get('/eventos', [CalendarioController::class, 'getEventos'])->name('eventos');
        Route::get('/evento', [CalendarioController::class, 'getEventoDetalle'])->name('evento.detalle');
    });

    // ========== PERFIL ==========
    Route::prefix('perfil')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/data', [ProfileController::class, 'getData'])->name('data');
        Route::put('/info', [ProfileController::class, 'updateInfo'])->name('update');
        Route::post('/foto', [ProfileController::class, 'updateFoto'])->name('foto.update');
        Route::delete('/foto', [ProfileController::class, 'deleteFoto'])->name('foto.delete');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::put('/security', [ProfileController::class, 'updateSecurity'])->name('security');
    });

    // ==================== ADMINISTRACIÓN ====================
    Route::prefix('admin')->middleware(['auditoria'])->name('admin.')->group(function () {

        // ========== AUDITORÍA ==========
        Route::middleware('permission:ver-auditoria')->group(function () {
            Route::post('auditoria/limpiar', [AuditoriaController::class, 'limpiar'])->name('auditoria.limpiar');
            Route::resource('auditoria', AuditoriaController::class)->only(['index', 'show', 'destroy']);
        });

        // ============================================================
        // MAESTROS
        // ============================================================

        // ------ ENTIDADES (vista unificada) ------
        Route::get('/entidades', [EntidadController::class, 'index'])
            ->middleware('permission:ver-instituciones,ver-departamentos,ver-responsables')
            ->name('entidades.index');

        // ------ INSTITUCIONES ------
        Route::middleware('permission:ver-instituciones')->group(function () {
            Route::get('instituciones', [InstitucionController::class, 'index'])->name('instituciones.index');
            Route::get('instituciones/{institucione}', [InstitucionController::class, 'show'])->name('instituciones.show');
        });
        Route::middleware('permission:crear-institucion')->group(function () {
            Route::post('instituciones', [InstitucionController::class, 'store'])->name('instituciones.store');
        });
        Route::middleware('permission:editar-institucion')->group(function () {
            Route::put('instituciones/{institucione}', [InstitucionController::class, 'update'])->name('instituciones.update');
            Route::patch('instituciones/{institucione}/toggle-status', [InstitucionController::class, 'toggleStatus'])->name('instituciones.toggle-status');
        });
        Route::middleware('permission:eliminar-institucion')->group(function () {
            Route::delete('instituciones/{institucione}', [InstitucionController::class, 'destroy'])->name('instituciones.destroy');
        });

        // ------ DEPARTAMENTOS ------
        Route::get('departamentos/por-institucion/{institucionId}', [DepartamentoController::class, 'porInstitucion'])->name('departamentos.por-institucion');
        Route::middleware('permission:ver-departamentos')->group(function () {
            Route::get('departamentos', [DepartamentoController::class, 'index'])->name('departamentos.index');
            Route::get('departamentos/{departamento}', [DepartamentoController::class, 'show'])->name('departamentos.show');
        });
        Route::middleware('permission:crear-departamento')->group(function () {
            Route::post('departamentos', [DepartamentoController::class, 'store'])->name('departamentos.store');
        });
        Route::middleware('permission:editar-departamento')->group(function () {
            Route::put('departamentos/{departamento}', [DepartamentoController::class, 'update'])->name('departamentos.update');
            Route::patch('departamentos/{departamento}/toggle-status', [DepartamentoController::class, 'toggleStatus'])->name('departamentos.toggle-status');
        });
        Route::middleware('permission:eliminar-departamento')->group(function () {
            Route::delete('departamentos/{departamento}', [DepartamentoController::class, 'destroy'])->name('departamentos.destroy');
        });

        // ------ RESPONSABLES ------
        Route::middleware('permission:ver-responsables')->group(function () {
            Route::get('responsables', [ResponsableController::class, 'index'])->name('responsables.index');
            Route::get('responsables/{responsable}', [ResponsableController::class, 'show'])->name('responsables.show');
        });
        Route::middleware('permission:crear-responsable')->group(function () {
            Route::post('responsables', [ResponsableController::class, 'store'])->name('responsables.store');
        });
        Route::middleware('permission:editar-responsable')->group(function () {
            Route::put('responsables/{responsable}', [ResponsableController::class, 'update'])->name('responsables.update');
            Route::patch('responsables/{responsable}/toggle-status', [ResponsableController::class, 'toggleStatus'])->name('responsables.toggle-status');
        });
        Route::middleware('permission:eliminar-responsable')->group(function () {
            Route::delete('responsables/{responsable}', [ResponsableController::class, 'destroy'])->name('responsables.destroy');
        });

        // ============================================================
        // CATÁLOGO DE EQUIPOS
        // ============================================================
        Route::get('/equipos', [EquipoController::class, 'index'])
            ->middleware('permission:ver-marcas,ver-categorias-equipos,ver-modelos')
            ->name('equipos.index');

        Route::prefix('equipos')->group(function () {
            // MARCAS
            Route::get('/marcas-list', [EquipoController::class, 'getMarcasList']);
            Route::get('/marcas', [EquipoController::class, 'getMarcas'])->middleware('permission:ver-marcas');
            Route::post('/marcas', [EquipoController::class, 'storeMarca'])->middleware('permission:crear-marca');
            Route::get('/marcas/{id}', [EquipoController::class, 'showMarca'])->middleware('permission:ver-marcas');
            Route::put('/marcas/{id}', [EquipoController::class, 'updateMarca'])->middleware('permission:editar-marca');
            Route::delete('/marcas/{id}', [EquipoController::class, 'deleteMarca'])->middleware('permission:eliminar-marca');
            Route::patch('/marcas/{id}/toggle', [EquipoController::class, 'toggleMarca'])->middleware('permission:editar-marca');

            // CATEGORÍAS
            Route::get('/categorias-list', [EquipoController::class, 'getCategoriasList']);
            Route::get('/categorias-por-marca/{marcaId}', [EquipoController::class, 'getCategoriasPorMarca']);
            Route::get('/categorias', [EquipoController::class, 'getCategorias'])->middleware('permission:ver-categorias-equipos');
            Route::post('/categorias', [EquipoController::class, 'storeCategoria'])->middleware('permission:crear-categoria-equipo');
            Route::get('/categorias/{id}', [EquipoController::class, 'showCategoria'])->middleware('permission:ver-categorias-equipos');
            Route::put('/categorias/{id}', [EquipoController::class, 'updateCategoria'])->middleware('permission:editar-categoria-equipo');
            Route::delete('/categorias/{id}', [EquipoController::class, 'deleteCategoria'])->middleware('permission:eliminar-categoria-equipo');
            Route::patch('/categorias/{id}/toggle', [EquipoController::class, 'toggleCategoria'])->middleware('permission:editar-categoria-equipo');

            // MODELOS
            Route::get('/modelos', [EquipoController::class, 'getModelos'])->middleware('permission:ver-modelos');
            Route::post('/modelos', [EquipoController::class, 'storeModelo'])->middleware('permission:crear-modelo');
            Route::get('/modelos/{id}', [EquipoController::class, 'showModelo'])->middleware('permission:ver-modelos');
            Route::put('/modelos/{id}', [EquipoController::class, 'updateModelo'])->middleware('permission:editar-modelo');
            Route::delete('/modelos/{id}', [EquipoController::class, 'deleteModelo'])->middleware('permission:eliminar-modelo');
            Route::patch('/modelos/{id}/toggle', [EquipoController::class, 'toggleModelo'])->middleware('permission:editar-modelo');

            // COMPONENTES POR MODELO
            Route::get('/modelos/{modeloId}/componentes', [ModeloComponenteController::class, 'index'])->middleware('permission:ver-modelos');
            Route::post('/modelos/{modeloId}/componentes', [ModeloComponenteController::class, 'store'])->middleware('permission:editar-modelo');
            Route::get('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'show'])->middleware('permission:ver-modelos');
            Route::put('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'update'])->middleware('permission:editar-modelo');
            Route::delete('/modelos/{modeloId}/componentes/{id}', [ModeloComponenteController::class, 'destroy'])->middleware('permission:eliminar-modelo');
        });

        // ============================================================
        // GESTIÓN DE USUARIOS
        // ============================================================

        // ------ ROLES ------
        Route::middleware('permission:ver-roles')->group(function () {
            Route::get('/roles/list', [RoleController::class, 'getRoles'])->name('roles.list');
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
            Route::get('/permisos/todos', [RoleController::class, 'getPermisos'])->name('permisos.todos');
        });
        Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:crear-rol')->name('roles.store');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:editar-rol')->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:eliminar-rol')->name('roles.destroy');

        // ------ TRABAJADORES ------
        Route::get('trabajadores/buscar-cedula/{cedula}', [TrabajadorController::class, 'buscarPorCedula'])->middleware('permission:ver-trabajadores')->name('trabajadores.buscar-cedula');
        Route::get('/trabajadores/{trabajador}/detalle', [TrabajadorController::class, 'show'])->middleware('permission:ver-trabajadores')->name('trabajadores.show');
        Route::middleware('permission:ver-trabajadores')->group(function () {
            Route::get('/trabajadores', [TrabajadorController::class, 'index'])->name('trabajadores.index');
        });
        Route::post('/trabajadores', [TrabajadorController::class, 'store'])->middleware('permission:crear-trabajador')->name('trabajadores.store');
        Route::put('/trabajadores/{trabajador}', [TrabajadorController::class, 'update'])->middleware('permission:editar-trabajador')->name('trabajadores.update');
        Route::delete('/trabajadores/{trabajador}', [TrabajadorController::class, 'destroy'])->middleware('permission:eliminar-trabajador')->name('trabajadores.destroy');

        // ------ USUARIOS ------
        Route::middleware('permission:ver-usuarios')->group(function () {
            Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
            Route::get('usuarios/{usuario}/detalle', [UsuarioController::class, 'show'])->name('usuarios.show');
        });
        Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('permission:crear-usuario')->name('usuarios.store');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->middleware('permission:editar-usuario')->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->middleware('permission:eliminar-usuario')->name('usuarios.destroy');
        Route::patch('usuarios/{usuario}/toggle-status', [UsuarioController::class, 'toggleStatus'])->middleware('permission:activar-desactivar-usuario')->name('usuarios.toggle-status');
        Route::patch('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->middleware('permission:resetear-password-usuario')->name('usuarios.reset-password');

        // ============================================================
        // INVENTARIO
        // ============================================================
        Route::get('/inventario', [InventarioController::class, 'index'])
            ->middleware('permission:ver-activos,ver-componentes')
            ->name('inventario.index');

        // Activos - DATA (JSON)
        Route::get('/inventario/data', function (Request $request) {
            $query = \App\Models\Activo::with(['modelo.marca', 'modelo.categoria', 'estatus', 'institucion', 'responsable']);

            if ($request->filled('search')) {
                $buscar = $request->search;
                $query->where(function ($q) use ($buscar) {
                    $q->where('serial', 'ILIKE', "%{$buscar}%")
                      ->orWhere('ubicacion', 'ILIKE', "%{$buscar}%")
                      ->orWhereHas('modelo', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"))
                      ->orWhereHas('modelo.marca', fn($q2) => $q2->where('nombre', 'ILIKE', "%{$buscar}%"));
                });
            }

            if ($request->filled('id_estatus')) {
                $query->where('id_estatus', $request->id_estatus);
            }

            return response()->json($query->orderBy('created_at', 'desc')->paginate(10));
        })->middleware('permission:ver-activos')->name('inventario.data');

        // Componentes - DATA (JSON)
        Route::get('/componentes/data', function (Request $request) {
            $query = \App\Models\Componente::with(['activo', 'institucion', 'responsable']);

            if ($request->filled('search')) {
                $buscar = $request->search;
                $query->where(function ($q) use ($buscar) {
                    $q->where('tipo', 'ILIKE', "%{$buscar}%")
                      ->orWhere('marca', 'ILIKE', "%{$buscar}%")
                      ->orWhere('modelo', 'ILIKE', "%{$buscar}%")
                      ->orWhere('serial', 'ILIKE', "%{$buscar}%");
                });
            }

            if ($request->filled('tipo')) $query->where('tipo', $request->tipo);
            if ($request->filled('estado')) $query->where('estado', $request->estado);

            return response()->json($query->orderBy('created_at', 'desc')->paginate(10));
        })->middleware('permission:ver-componentes')->name('componentes.data');

        // Activos - CRUD
        Route::get('/activos/por-modelo/{modeloId}', [ActivoController::class, 'porModelo'])->middleware('permission:ver-activos')->name('activos.por-modelo');
        Route::middleware('permission:ver-activos')->group(function () {
            Route::get('/activos', [ActivoController::class, 'index'])->name('inventario.index');
            Route::get('/activos/{activo}', [ActivoController::class, 'show'])->name('inventario.show');
        });
        Route::post('/activos', [ActivoController::class, 'store'])->middleware('permission:crear-activo')->name('inventario.store');
        Route::put('/activos/{activo}', [ActivoController::class, 'update'])->middleware('permission:editar-activo')->name('inventario.update');
        Route::delete('/activos/{activo}', [ActivoController::class, 'destroy'])->middleware('permission:eliminar-activo')->name('inventario.destroy');
        Route::patch('/activos/{activo}/toggle-status', [ActivoController::class, 'toggleStatus'])->middleware('permission:cambiar-estatus-activo')->name('inventario.toggle-status');

        // Componentes - CRUD
        Route::get('/componentes/disponibles', [ComponenteController::class, 'disponibles'])->middleware('permission:ver-componentes')->name('componentes.disponibles');
        Route::get('/componentes/en-bodega', [ComponenteController::class, 'enBodega'])->middleware('permission:ver-componentes');
        Route::get('/componentes/por-tipo/{tipo}', [ComponenteController::class, 'porTipo'])->middleware('permission:ver-componentes');
        Route::get('/componentes', [ComponenteController::class, 'index'])->middleware('permission:ver-componentes');
        Route::post('/componentes', [ComponenteController::class, 'store'])->middleware('permission:crear-componente');
        Route::get('/componentes/{componente}', [ComponenteController::class, 'show'])->middleware('permission:ver-componentes');
        Route::put('/componentes/{componente}', [ComponenteController::class, 'update'])->middleware('permission:editar-componente');
        Route::delete('/componentes/{componente}', [ComponenteController::class, 'destroy'])->middleware('permission:eliminar-componente');
        Route::patch('/componentes/{componente}/toggle-status', [ComponenteController::class, 'toggleStatus'])->middleware('permission:editar-componente');

        // ============================================================
        // PRÉSTAMOS
        // ============================================================
        Route::prefix('prestamos')->name('prestamos.')->group(function () {
            Route::middleware('permission:ver-prestamos')->group(function () {
                Route::get('/listar', [PrestamoController::class, 'listar'])->name('listar');
                Route::get('/buscar-responsable', [PrestamoController::class, 'buscarResponsableDestino'])->name('buscar-responsable');
                Route::get('/buscar-items', [PrestamoController::class, 'buscarItems'])->name('buscar-items');
                Route::get('/para-prestamo', [PrestamoController::class, 'paraPrestamo'])->name('para-prestamo');
                Route::get('/', [PrestamoController::class, 'index'])->name('index');
                Route::get('/{prestamo}', [PrestamoController::class, 'show'])->name('show');
            });

            Route::post('/', [PrestamoController::class, 'store'])->middleware('permission:crear-prestamo')->name('store');
            Route::put('/{prestamo}', [PrestamoController::class, 'update'])->middleware('permission:editar-prestamo')->name('update');

            Route::post('/{prestamo}/aprobar', [PrestamoController::class, 'aprobar'])->middleware('permission:aprobar-prestamo')->name('aprobar');
            Route::post('/{prestamo}/rechazar', [PrestamoController::class, 'rechazar'])->middleware('permission:aprobar-prestamo')->name('rechazar');
            Route::post('/{prestamo}/entregar', [PrestamoController::class, 'entregar'])->middleware('permission:editar-prestamo')->name('entregar');
            Route::post('/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->middleware('permission:devolver-prestamo')->name('devolver');
            Route::post('/{prestamo}/cancelar', [PrestamoController::class, 'cancelar'])->middleware('permission:cancelar-prestamo')->name('cancelar');
            Route::post('/{prestamo}/extender', [PrestamoController::class, 'extender'])->middleware('permission:extender-prestamo')->name('extender');
        });

        // ============================================================
        // SOLICITUDES
        // ============================================================
        Route::prefix('solicitudes')->name('solicitudes.')->group(function () {
            // CORREOS
            Route::prefix('correos')->name('correos.')->middleware('permission:aprobar-solicitudes')->group(function () {
                Route::get('/lista', [SolicitudController::class, 'correosIndex'])->name('index');
                Route::get('/contador', [SolicitudController::class, 'correosContador'])->name('contador');
                Route::post('/revisar', [SolicitudController::class, 'correosRevisar'])->name('revisar');
                Route::get('/{id}', [SolicitudController::class, 'correoShow'])->where('id', '[0-9]+')->name('show');
                Route::post('/{id}/convertir', [SolicitudController::class, 'correoConvertir'])->where('id', '[0-9]+')->name('convertir');
            });

            Route::middleware('permission:ver-solicitudes')->group(function () {
                Route::get('/', [SolicitudController::class, 'index'])->name('index');
                Route::get('/pendientes-prestamo', [SolicitudController::class, 'paraPrestamo'])->name('pendientes-prestamo');
                Route::get('/no-leidas', [SolicitudController::class, 'noLeidasAdmin'])->name('no-leidas');
                Route::get('/{solicitud}/detalles', [SolicitudController::class, 'getDetalles'])->where('solicitud', '[0-9]+')->name('detalles');
            });

            Route::post('/store', [SolicitudController::class, 'store'])->middleware('permission:crear-solicitud')->name('store');
            Route::post('/{solicitud}/update', [SolicitudController::class, 'update'])->middleware('permission:editar-solicitud')->where('solicitud', '[0-9]+')->name('update');
            Route::delete('/{solicitud}', [SolicitudController::class, 'destroy'])->middleware('permission:eliminar-solicitud')->where('solicitud', '[0-9]+')->name('destroy');
            Route::post('/{solicitud}/cancel', [SolicitudController::class, 'cancel'])->middleware('permission:cancelar-solicitud')->where('solicitud', '[0-9]+')->name('cancel');
            Route::post('/{solicitud}/approve', [SolicitudController::class, 'approve'])->middleware('permission:aprobar-solicitudes')->where('solicitud', '[0-9]+')->name('approve');
            Route::post('/{solicitud}/reject', [SolicitudController::class, 'reject'])->middleware('permission:aprobar-solicitudes')->where('solicitud', '[0-9]+')->name('reject');
            Route::post('/{solicitud}/leer', [SolicitudController::class, 'marcarLeida'])->middleware('permission:aprobar-solicitudes')->where('solicitud', '[0-9]+')->name('leer');
        });

        // ============================================================
        // SOPORTE TÉCNICO
        // ============================================================
        Route::prefix('soporte')->name('soporte.')->group(function () {
            // CORREOS
            Route::prefix('correos')->name('correos.')->middleware('permission:ver-fichas-soporte')->group(function () {
                Route::get('/lista', [FichaSoporteController::class, 'correosIndex'])->name('index');
                Route::get('/contador', [FichaSoporteController::class, 'correosContador'])->name('contador');
                Route::post('/revisar', [FichaSoporteController::class, 'correosRevisar'])->name('revisar');
                Route::get('/{id}', [FichaSoporteController::class, 'correoShow'])->where('id', '[0-9]+')->name('show');
                Route::post('/{id}/convertir', [FichaSoporteController::class, 'correoConvertir'])->middleware('permission:crear-ficha-soporte')->where('id', '[0-9]+')->name('convertir');
            });

            Route::middleware('permission:ver-fichas-soporte')->group(function () {
                Route::get('/', [FichaSoporteController::class, 'index'])->name('index');
                Route::get('/{id}', [FichaSoporteController::class, 'show'])->where('id', '[0-9]+')->name('show');
                Route::get('/{id}/componentes', [FichaSoporteController::class, 'getComponentesDetalle'])->where('id', '[0-9]+')->name('componentes');
            });

            Route::post('/', [FichaSoporteController::class, 'store'])->middleware('permission:crear-ficha-soporte')->name('store');
            Route::post('/equipo-externo', [FichaSoporteController::class, 'storeEquipoExterno'])->middleware('permission:crear-ficha-soporte')->name('equipo-externo');
            Route::put('/{id}', [FichaSoporteController::class, 'update'])->middleware('permission:editar-ficha-soporte')->where('id', '[0-9]+')->name('update');
            Route::delete('/{id}', [FichaSoporteController::class, 'destroy'])->middleware('permission:eliminar-ficha-soporte')->where('id', '[0-9]+')->name('destroy');
            Route::post('/{id}/close', [FichaSoporteController::class, 'close'])->middleware('permission:cerrar-ficha-soporte')->where('id', '[0-9]+')->name('close');
        });

        // ============================================================
        // ACTAS
        // ============================================================
        Route::prefix('actas')->name('actas.')->middleware('permission:ver-prestamos')->group(function () {
            Route::get('/generar', [ActaEntregaController::class, 'generarDesdePrestamo'])->name('generar');
            Route::get('/imprimir/{id}', [ActaEntregaController::class, 'imprimir'])->name('imprimir');
            Route::get('/devolucion/generar', [ActaDevolucionController::class, 'generarDesdePrestamo'])->name('devolucion.generar');
            Route::get('/devolucion/imprimir/{id}', [ActaDevolucionController::class, 'imprimir'])->name('devolucion.imprimir');
        });

        // ============================================================
        // REPORTES
        // ============================================================
        Route::prefix('reportes')->name('reportes.')->middleware('permission:ver-reportes')->group(function () {

            // ---------- PÁGINA CENTRAL ----------
            Route::get('/', [ReporteController::class, 'index'])->name('index');

            // ---------- ENDPOINTS AJAX (devuelven JSON) ----------
            Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario');
            Route::get('/solicitudes', [ReporteController::class, 'solicitudes'])->name('solicitudes');
            Route::get('/soporte', [ReporteController::class, 'soporte'])->name('soporte');

            // ---------- REPORTES IMPRIMIBLES (devuelven VISTAS/PDF) ----------
            Route::get('/prestamos', [ReporteController::class, 'prestamos'])->name('prestamos');
            Route::get('/prestamos-vencidos', [ReporteController::class, 'prestamosVencidos'])->name('prestamos-vencidos');
            Route::get('/inventario-completo', [ReporteController::class, 'inventarioCompleto'])->name('inventario-completo');
            Route::get('/solicitudes-detalle', [ReporteController::class, 'solicitudesDetalle'])->name('solicitudes-detalle');
            Route::get('/soporte-detalle', [ReporteController::class, 'soporteDetalle'])->name('soporte-detalle');
            Route::get('/activos-por-entidad', [ReporteController::class, 'activosPorEntidad'])->name('activos-por-entidad');
            Route::get('/kardex/{activoId}', [ReporteController::class, 'kardex'])->where('activoId', '[0-9]+')->name('kardex');
            Route::get('/usuarios', [ReporteController::class, 'usuarios'])->name('usuarios');
            Route::get('/auditoria', [ReporteController::class, 'auditoria'])->name('auditoria');

            // ---------- EXPORTACIONES ----------
            Route::get('/exportar-pdf', [ReporteController::class, 'exportarPdf'])->middleware('permission:exportar-reportes')->name('exportar.pdf');
            Route::get('/inventario/exportar-excel', [ReporteInventarioController::class, 'exportarExcel'])->middleware('permission:exportar-reportes')->name('inventario.exportar-excel');
        });

        // ============================================================
        // UTILIDADES (sin permiso específico, solo auth)
        // ============================================================
        Route::get('/estatus-list', function () {
            $estatus = Estatus::select('id', 'descripcion', 'color_badge')->orderBy('descripcion')->get();
            return response()->json(['success' => true, 'data' => $estatus]);
        });

        // UBICACIONES
        Route::get('/ubicaciones/estados', [UbicacionController::class, 'getEstados'])->name('ubicaciones.estados');
        Route::get('/ubicaciones/estados/{estadoId}/municipios', [UbicacionController::class, 'getMunicipios'])->name('ubicaciones.municipios');
        Route::get('/ubicaciones/municipios/{municipioId}/parroquias', [UbicacionController::class, 'getParroquias'])->name('ubicaciones.parroquias');

        // ============================================================
        // NOTIFICACIONES
        // ============================================================
        Route::prefix('notificaciones')->name('notificaciones.')->middleware('permission:ver-notificaciones')->group(function () {
            Route::post('/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasComoLeidas'])->name('marcar-todas');
            Route::get('/no-leidas', [NotificacionController::class, 'obtenerNoLeidas'])->name('no-leidas');
            Route::get('/', [NotificacionController::class, 'index'])->name('index');
            Route::post('/{id}/leer', [NotificacionController::class, 'leer'])->where('id', '[0-9]+')->name('leer');
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
            })->where('id', '[0-9]+')->name('detalle');
        });

        // ============================================================
        // API RESPONSABLES (sin permiso, solo auth)
        // ============================================================
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

        // ============================================================
        // API TÉCNICOS
        // ============================================================
        Route::prefix('api')->group(function () {
            Route::get('/tecnicos', function (Request $request) {
                $search = $request->get('search');

                $query = App\Models\Usuario::whereHas('rol', function ($q) {
                    $q->whereIn('nombre', ['tecnico', 'admin', 'ingeniero']);
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

                return response()->json($query->limit(15)->get());
            });

            Route::get('/tecnicos/{id}', function ($id) {
                $tecnico = App\Models\Usuario::whereHas('rol', function ($q) {
                    $q->whereIn('nombre', ['tecnico', 'admin', 'ingeniero']);
                })->with('trabajador')->find($id);

                if (!$tecnico) {
                    return response()->json(['success' => false, 'message' => 'Técnico no encontrado'], 404);
                }

                return response()->json(['success' => true, 'data' => $tecnico]);
            });
        });
    });
});