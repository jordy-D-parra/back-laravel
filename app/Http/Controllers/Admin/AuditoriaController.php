<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('ver-auditoria')) {
            abort(403, 'No tienes permiso para ver la bitácora de auditoría');
        }

        $query = Auditoria::with('usuario.trabajador');

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'ILIKE', "%{$buscar}%")
                  ->orWhere('usuario_nombre', 'ILIKE', "%{$buscar}%")
                  ->orWhere('modulo', 'ILIKE', "%{$buscar}%")
                  ->orWhere('accion', 'ILIKE', "%{$buscar}%")
                  ->orWhere('ip_address', 'ILIKE', "%{$buscar}%");
            });
        }

        // Filtros
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $auditoria = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Datos para filtros
        $modulos = Auditoria::distinct()->pluck('modulo')->filter()->sort()->values();
        $acciones = Auditoria::distinct()->pluck('accion')->filter()->sort()->values();

        // Estadísticas
        $totalRegistros = Auditoria::count();
        $hoy = Auditoria::whereDate('created_at', today())->count();
        $semana = Auditoria::whereBetween('created_at', [now()->startOfWeek(), now()])->count();
        $mes = Auditoria::whereMonth('created_at', now()->month)->count();

        return view('admin.auditoria.index', compact(
            'auditoria',
            'modulos',
            'acciones',
            'totalRegistros',
            'hoy',
            'semana',
            'mes'
        ));
    }

    public function show(Auditoria $auditorium)
    {
        if (!auth()->user()->hasPermission('ver-auditoria')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $auditorium->load('usuario.trabajador');
        
        // Formatear los datos para una visualización profesional
        $data = $this->formatearDatosAuditoria($auditorium);
        
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Formatear los datos de auditoría para vista profesional
     */
    private function formatearDatosAuditoria(Auditoria $auditorium): array
    {
        $datosOriginales = $auditorium->datos_originales;
        $datosNuevos = $auditorium->datos_nuevos;
        
        $camposFormateados = [];
        
        // Mapeo de nombres de campos a etiquetas legibles
        $etiquetasCampos = [
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'serial' => 'Número de Serie',
            'modelo_id' => 'Modelo',
            'categoria_id' => 'Categoría',
            'marca_id' => 'Marca',
            'id_estatus' => 'Estado',
            'institucion_id' => 'Institución',
            'departamento_id' => 'Departamento',
            'responsable_id' => 'Responsable',
            'ubicacion' => 'Ubicación',
            'fecha_adquisicion' => 'Fecha de Adquisición',
            'fecha_fin_garantia' => 'Fin de Garantía',
            'vida_util_anos' => 'Vida Útil (años)',
            'observaciones' => 'Observaciones',
            'especificaciones_tecnicas' => 'Especificaciones Técnicas',
            'agrupacion' => 'Agrupación',
            'fecha_instalacion' => 'Fecha de Instalación',
            'fecha_retiro' => 'Fecha de Retiro',
            'tipo' => 'Tipo',
            'tipo_item' => 'Tipo de Item',
            'modelo_componente_id' => 'Modelo de Componente',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'capacidad' => 'Capacidad',
            'especificaciones' => 'Especificaciones',
            'estado' => 'Estado',
            'activo_id' => 'Activo',
            'fecha_prestamo' => 'Fecha de Préstamo',
            'fecha_devolucion_esperada' => 'Fecha Devolución Esperada',
            'fecha_devolucion_real' => 'Fecha Devolución Real',
            'codigo' => 'Código',
            'tipo_prestamo' => 'Tipo de Préstamo',
            'tipo_solicitante' => 'Tipo de Solicitante',
            'prioridad' => 'Prioridad',
            'justificacion' => 'Justificación',
            'fecha_solicitud' => 'Fecha de Solicitud',
            'fecha_requerida' => 'Fecha Requerida',
            'fecha_fin_estimada' => 'Fecha Fin Estimada',
            'estado_solicitud' => 'Estado de Solicitud',
            'aprobado_por' => 'Aprobado por',
            'fecha_aprobacion' => 'Fecha de Aprobación',
            'tecnico_nombre' => 'Técnico',
            'usuario_reporta_nombre' => 'Usuario que Reporta',
            'diagnostico' => 'Diagnóstico',
            'trabajo_realizado' => 'Trabajo Realizado',
            'fecha_ingreso' => 'Fecha de Ingreso',
            'fecha_salida' => 'Fecha de Salida',
            'usuario' => 'Usuario',
            'password' => 'Contraseña',
            'must_change_password' => 'Cambiar Contraseña',
            'status' => 'Estado',
            'ultimo_login' => 'Último Login',
            'trabajador_id' => 'Trabajador',
            'rol_id' => 'Rol',
            'cedula' => 'Cédula',
            'apellido' => 'Apellido',
            'departamento' => 'Departamento',
            'cargo' => 'Cargo',
            'especialidad' => 'Especialidad',
            'telefono' => 'Teléfono',
            'email' => 'Email',
            'direccion' => 'Dirección',
            'documento' => 'Documento',
            'representante' => 'Representante',
            'informacion' => 'Información',
            'activo' => 'Activo',
            'representante_nombre' => 'Nombre del Representante',
            'representante_documento' => 'Documento del Representante',
            'representante_telefono' => 'Teléfono del Representante',
            'representante_email' => 'Email del Representante',
            'representante_cargo' => 'Cargo del Representante',
            'representante_direccion' => 'Dirección del Representante',
            'oficio_adjunto' => 'Oficio Adjunto',
            'cantidad' => 'Cantidad',
            'estado_entrega' => 'Estado de Entrega',
            'estado_devolucion' => 'Estado de Devolución',
            'observaciones' => 'Observaciones',
        ];

        // Procesar datos originales
        if ($datosOriginales) {
            foreach ($datosOriginales as $campo => $valor) {
                if (isset($etiquetasCampos[$campo])) {
                    $camposFormateados[] = [
                        'campo' => $campo,
                        'etiqueta' => $etiquetasCampos[$campo],
                        'valor_original' => $this->formatearValor($valor),
                        'valor_nuevo' => $datosNuevos && isset($datosNuevos[$campo]) ? $this->formatearValor($datosNuevos[$campo]) : null,
                        'cambio' => $datosNuevos && isset($datosNuevos[$campo]) && $valor != $datosNuevos[$campo]
                    ];
                }
            }
        }

        // Procesar datos nuevos solos (para creaciones)
        if ($datosNuevos && !$datosOriginales) {
            foreach ($datosNuevos as $campo => $valor) {
                if (isset($etiquetasCampos[$campo])) {
                    $camposFormateados[] = [
                        'campo' => $campo,
                        'etiqueta' => $etiquetasCampos[$campo],
                        'valor_original' => null,
                        'valor_nuevo' => $this->formatearValor($valor),
                        'cambio' => false
                    ];
                }
            }
        }

        // Obtener la acción legible
        $accionesLegibles = [
            'crear' => ['icono' => '➕', 'color' => '#28a745', 'texto' => 'Creación'],
            'editar' => ['icono' => '✏️', 'color' => '#1e3c72', 'texto' => 'Edición'],
            'eliminar' => ['icono' => '🗑️', 'color' => '#dc3545', 'texto' => 'Eliminación'],
            'cambio_estado' => ['icono' => '🔄', 'color' => '#f59e0b', 'texto' => 'Cambio de Estado'],
            'login' => ['icono' => '🔓', 'color' => '#0d6efd', 'texto' => 'Inicio de Sesión'],
            'logout' => ['icono' => '🔒', 'color' => '#6c757d', 'texto' => 'Cierre de Sesión'],
            'login_fallido' => ['icono' => '⚠️', 'color' => '#dc3545', 'texto' => 'Intento Fallido'],
        ];

        $accionInfo = $accionesLegibles[$auditorium->accion] ?? [
            'icono' => '📌', 
            'color' => '#6c757d', 
            'texto' => ucfirst($auditorium->accion)
        ];

        // Módulo legible
        $moduloLegible = ucfirst(str_replace('_', ' ', $auditorium->modulo));

        return [
            'id' => $auditorium->id,
            'uuid' => $auditorium->uuid,
            'fecha' => $auditorium->created_at->format('d/m/Y H:i:s'),
            'fecha_humana' => $auditorium->created_at->diffForHumans(),
            'usuario_nombre' => $auditorium->usuario_nombre ?? 'Sistema',
            'usuario' => $auditorium->usuario ? [
                'id' => $auditorium->usuario->id,
                'usuario' => $auditorium->usuario->usuario,
                'trabajador' => $auditorium->usuario->trabajador ? 
                    $auditorium->usuario->trabajador->nombre . ' ' . $auditorium->usuario->trabajador->apellido : null
            ] : null,
            'modulo' => $moduloLegible,
            'modulo_raw' => $auditorium->modulo,
            'accion' => $auditorium->accion,
            'accion_icono' => $accionInfo['icono'],
            'accion_color' => $accionInfo['color'],
            'accion_texto' => $accionInfo['texto'],
            'tabla_afectada' => $auditorium->tabla_afectada,
            'registro_id' => $auditorium->registro_id,
            'descripcion' => $auditorium->descripcion,
            'ip_address' => $auditorium->ip_address,
            'user_agent' => $auditorium->user_agent,
            'campos' => $camposFormateados,
            'tiene_cambios' => count($camposFormateados) > 0
        ];
    }

    /**
     * Formatear un valor para mostrar de forma legible
     */
    private function formatearValor($valor)
    {
        if (is_null($valor)) {
            return 'null';
        }

        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        if (is_array($valor) || is_object($valor)) {
            return json_encode($valor, JSON_PRETTY_PRINT);
        }

        if (is_numeric($valor) && strlen($valor) > 10) {
            return (string) $valor;
        }

        // Si parece una fecha, formatearla
        if (is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
            try {
                $fecha = new \DateTime($valor);
                return $fecha->format('d/m/Y');
            } catch (\Exception $e) {
                return $valor;
            }
        }

        if (strlen($valor) > 100) {
            return substr($valor, 0, 100) . '...';
        }

        return $valor;
    }

    public function destroy(Auditoria $auditorium)
    {
        if (!auth()->user()->hasPermission('administrar-auditoria')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $auditorium->delete();
        return response()->json(['success' => true, 'message' => 'Registro eliminado']);
    }

    public function limpiar(Request $request)
    {
        if (!auth()->user()->hasPermission('administrar-auditoria')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $dias = $request->input('dias', 30);
        $eliminados = Auditoria::where('created_at', '<', now()->subDays($dias))->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$eliminados} registros de auditoría de más de {$dias} días."
        ]);
    }
}