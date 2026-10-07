@extends('layouts.dashboard')

@section('title', 'Dashboard')

@php
    // ============================================================
    // LÓGICA COMPLETA DEL DASHBOARD
    // ============================================================

    use App\Models\Usuario;
    use App\Models\Trabajador;
    use App\Models\Solicitud;
    use App\Models\Prestamo;
    use App\Models\FichaSoporte;

    // ============ SALUDO SEGÚN LA HORA ============
    $hora = now()->hour;
    if ($hora < 12) {
        $saludo = 'Buenos días';
    } elseif ($hora < 19) {
        $saludo = 'Buenas tardes';
    } else {
        $saludo = 'Buenas noches';
    }

    // ============ FRASE ALEATORIA DEL DÍA ============
    $frases = [
        ['texto' => 'Un inventario bien gestionado es la base de una institución eficiente.', 'autor' => 'Sistema de Gestión de Inventario'],
        ['texto' => 'Cada equipo registrado es un paso más hacia la excelencia administrativa.', 'autor' => 'Departamento de Informática'],
        ['texto' => 'La organización es el reflejo de un trabajo bien hecho.', 'autor' => 'Gobernación del Estado Yaracuy'],
        ['texto' => 'Gestionar los recursos con responsabilidad es servir al pueblo con excelencia.', 'autor' => 'Sistema de Gestión'],
        ['texto' => 'Un préstamo devuelto a tiempo mantiene el flujo de trabajo sin interrupciones.', 'autor' => 'Módulo de Préstamos'],
        ['texto' => 'La tecnología avanza cuando se administra con criterio y orden.', 'autor' => 'Dirección de Informática'],
        ['texto' => 'Cada solicitud atendida es una muestra de compromiso institucional.', 'autor' => 'Módulo de Solicitudes'],
        ['texto' => 'Un soporte técnico oportuno previene fallas y mantiene la operatividad.', 'autor' => 'Módulo de Soporte Técnico'],
        ['texto' => 'El orden en el inventario es el reflejo del orden en la gestión.', 'autor' => 'Sistema de Inventario'],
        ['texto' => 'Registrar bien es cuidar el patrimonio de todos los yaracuyanos.', 'autor' => 'Gobernación del Estado Yaracuy'],
        ['texto' => 'La información bien organizada se convierte en conocimiento útil.', 'autor' => 'Sistema de Gestión'],
        ['texto' => 'Un sistema limpio y actualizado es un sistema confiable.', 'autor' => 'Departamento de Informática'],
        ['texto' => 'Cuidar los equipos es cuidar la inversión del estado.', 'autor' => 'Sistema de Inventario'],
        ['texto' => 'La responsabilidad compartida garantiza el éxito de cualquier sistema.', 'autor' => 'Módulo de Préstamos'],
        ['texto' => 'Cada registro correcto evita futuros dolores de cabeza.', 'autor' => 'Sistema de Gestión'],
        ['texto' => 'Trabajar en equipo con información clara multiplica los resultados.', 'autor' => 'Gobernación del Estado Yaracuy'],
        ['texto' => 'La tecnología al servicio del pueblo requiere orden y compromiso.', 'autor' => 'Dirección de Informática'],
        ['texto' => 'Gestionar con transparencia fortalece la confianza institucional.', 'autor' => 'Sistema de Gestión'],
        ['texto' => 'Mantener el inventario actualizado es mantener el control.', 'autor' => 'Módulo de Inventario'],
        ['texto' => 'Un buen registro hoy evita problemas mañana.', 'autor' => 'Sistema de Gestión de Inventario'],
        ['texto' => 'Atender cada reporte con prontitud es servir con calidad.', 'autor' => 'Módulo de Soporte Técnico'],
        ['texto' => 'El éxito del sistema depende del compromiso de cada usuario.', 'autor' => 'Gobernación del Estado Yaracuy'],
        ['texto' => 'La previsión y el orden son las mejores herramientas de gestión.', 'autor' => 'Sistema de Préstamos'],
        ['texto' => 'Cada equipo bien administrado rinde el doble de su valor.', 'autor' => 'Sistema de Inventario'],
        ['texto' => 'Ser ordenado es la mejor manera de optimizar los recursos.', 'autor' => 'Departamento de Informática'],
        ['texto' => 'La mejora continua empieza por registrar correctamente cada acción.', 'autor' => 'Sistema de Gestión'],
        ['texto' => 'Los datos bien gestionados son la base de las buenas decisiones.', 'autor' => 'Módulo de Reportes'],
        ['texto' => 'Tener control es tener la capacidad de mejorar.', 'autor' => 'Sistema de Gestión'],
        ['texto' => 'La excelencia administrativa empieza por pequeños detalles.', 'autor' => 'Gobernación del Estado Yaracuy'],
        ['texto' => 'Un sistema ordenado refleja una institución organizada.', 'autor' => 'Dirección de Informática'],
    ];
    $fraseDelDia = $frases[now()->dayOfYear % count($frases)];

    // ============ USUARIO ACTUAL ============
    $usuario = Auth::user();
    $trabajador = $usuario->trabajador;
    $rolNombre = $usuario->rol?->nombre ?? 'Sin rol';
    $rolDisplay = match($rolNombre) {
        'admin' => 'Administrador',
        'super_admin' => 'Super Administrador',
        'ingeniero' => 'Ingeniero',
        'tecnico' => 'Técnico',
        'secretaria' => 'Secretaria',
        default => ucfirst($rolNombre),
    };

    // ============ ESTADÍSTICAS (filtradas por permisos) ============
    $usuariosActivos = $usuario->hasPermission('ver-usuarios')
        ? Usuario::where('status', 'activo')->count()
        : null;

    $totalTrabajadores = $usuario->hasPermission('ver-trabajadores')
        ? Trabajador::count()
        : null;

    $solicitudesPendientes = $usuario->hasPermission('ver-solicitudes')
        ? Solicitud::where('estado_solicitud', 'pendiente')->count()
        : null;

    $prestamosActivos = $usuario->hasPermission('ver-prestamos')
        ? Prestamo::whereIn('estado', ['entregado', 'extendido'])->count()
        : null;

    $fichasEnProceso = $usuario->hasPermission('ver-fichas-soporte')
        ? FichaSoporte::where('estado', 'en_proceso')->count()
        : null;

    $pendientesCambio = $usuario->hasPermission('ver-usuarios')
        ? Usuario::where('must_change_password', true)->count()
        : null;
@endphp

@section('styles')
<style>
/* ============================================
   ESTILOS DEL DASHBOARD
   ============================================ */

/* ---- Tarjeta de bienvenida ---- */
.welcome-card {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

.welcome-card::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: 10%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 50%;
}

.welcome-card .card-body {
    position: relative;
    z-index: 1;
}

.welcome-card .welcome-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.welcome-card .welcome-icon svg {
    stroke: white;
    width: 28px;
    height: 28px;
}

/* ---- Frase motivacional ---- */
.frase-motivacional {
    background: rgba(255, 255, 255, 0.1);
    border-left: 4px solid #f6c23e;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-top: 1.25rem;
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    position: relative;
    animation: fadeInFrase 0.6s ease-out;
}

@keyframes fadeInFrase {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.frase-motivacional .frase-quote {
    position: absolute;
    top: 8px;
    right: 16px;
    font-size: 3rem;
    color: rgba(255, 255, 255, 0.1);
    font-family: Georgia, serif;
    line-height: 1;
    pointer-events: none;
}

.frase-motivacional .frase-texto {
    color: white;
    font-size: 0.95rem;
    font-style: italic;
    line-height: 1.6;
    margin: 0;
    position: relative;
    z-index: 1;
    padding-right: 30px;
}

.frase-motivacional .frase-autor {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.5rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
}

.frase-motivacional .frase-autor svg {
    width: 12px;
    height: 12px;
    stroke: #f6c23e;
    fill: none;
}

/* ---- Tarjetas de estadísticas ---- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    border: 1px solid rgba(30, 60, 114, 0.06);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    cursor: default;
}

.stat-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(30, 60, 114, 0.12);
    border-color: rgba(30, 60, 114, 0.15);
}

.stat-card-modern .stat-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.stat-card-modern .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.stat-card-modern:hover .stat-icon {
    transform: scale(1.05) rotate(-3deg);
}

.stat-card-modern .stat-icon.blue   { background: rgba(30, 60, 114, 0.1);  color: #1e3c72; }
.stat-card-modern .stat-icon.green  { background: rgba(34, 197, 94, 0.1);  color: #22c55e; }
.stat-card-modern .stat-icon.orange { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.stat-card-modern .stat-icon.red    { background: rgba(239, 68, 68, 0.1);  color: #ef4444; }
.stat-card-modern .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.stat-card-modern .stat-icon.cyan   { background: rgba(6, 182, 212, 0.1);  color: #06b6d4; }

.stat-card-modern .stat-icon svg {
    width: 24px;
    height: 24px;
    stroke: currentColor;
    stroke-width: 1.8;
    fill: none;
}

.stat-card-modern .stat-badge {
    font-size: 0.6rem;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 20px;
    background: rgba(30, 60, 114, 0.08);
    color: #1e3c72;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card-modern .stat-badge.up   { background: rgba(34, 197, 94, 0.12);  color: #16a34a; }
.stat-card-modern .stat-badge.down { background: rgba(239, 68, 68, 0.12);  color: #dc2626; }

.stat-card-modern .stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
    margin-bottom: 2px;
}

.stat-card-modern .stat-label {
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card-modern .stat-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 0 0 16px 16px;
}

.stat-card-modern .stat-bar.blue   { background: linear-gradient(90deg, #1e3c72, #2a5298); }
.stat-card-modern .stat-bar.green  { background: linear-gradient(90deg, #22c55e, #4ade80); }
.stat-card-modern .stat-bar.orange { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.stat-card-modern .stat-bar.red    { background: linear-gradient(90deg, #ef4444, #f87171); }
.stat-card-modern .stat-bar.purple { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
.stat-card-modern .stat-bar.cyan   { background: linear-gradient(90deg, #06b6d4, #22d3ee); }

/* ---- Animaciones de entrada ---- */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

.stat-card-modern {
    animation: fadeInUp 0.5s ease forwards;
}

.stat-card-modern:nth-child(1) { animation-delay: 0.05s; }
.stat-card-modern:nth-child(2) { animation-delay: 0.1s; }
.stat-card-modern:nth-child(3) { animation-delay: 0.15s; }
.stat-card-modern:nth-child(4) { animation-delay: 0.2s; }
.stat-card-modern:nth-child(5) { animation-delay: 0.25s; }
.stat-card-modern:nth-child(6) { animation-delay: 0.3s; }

/* ---- Sección de información del sistema ---- */
.info-institucional {
    background: white;
    border-radius: 16px;
    border: 1px solid rgba(30, 60, 114, 0.06);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    margin-top: 1.5rem;
}

.info-institucional .info-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(90deg, #f8fafc 0%, #eef2f8 100%);
}

.info-institucional .info-header h5 {
    font-weight: 600;
    color: #0f172a;
    margin: 0;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-institucional .info-header h5 svg {
    stroke: #1e3c72;
}

.info-institucional .info-body {
    padding: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 0;
    border-bottom: 1px solid #f8fafc;
    transition: all 0.2s ease;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item:hover {
    background: #f8fafc;
    margin: 0 -1.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.info-item .info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(30, 60, 114, 0.08);
    color: #1e3c72;
}

.info-item .info-icon svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    stroke-width: 2;
    fill: none;
}

.info-item .info-content { flex: 1; }

.info-item .info-label {
    font-size: 0.72rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: 2px;
}

.info-item .info-value {
    font-size: 0.9rem;
    color: #0f172a;
    font-weight: 500;
}

/* ---- Responsive ---- */
@media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: 1fr; }
    .welcome-card .welcome-icon { width: 44px; height: 44px; }
    .frase-motivacional .frase-texto { font-size: 0.85rem; }
}
</style>
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- ========================================== --}}
    {{-- TARJETA DE BIENVENIDA --}}
    {{-- ========================================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="welcome-card">
                <div class="card-body p-4">
                    <div class="welcome-icon">
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h2 class="text-white mb-0">
                        ¡{{ $saludo }}, {{ $trabajador->nombre ?? $usuario->usuario }} {{ $trabajador->apellido ?? '' }}!
                    </h2>

                    {{-- ===== FRASE ALEATORIA DEL DÍA ===== --}}
                    <div class="frase-motivacional">
                        <span class="frase-quote">"</span>
                        <p class="frase-texto">{{ $fraseDelDia['texto'] }}</p>
                        <div class="frase-autor">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            {{ $fraseDelDia['autor'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- ESTADÍSTICAS (filtradas por permisos) --}}
    {{-- ========================================== --}}
    <div class="stats-grid mt-4">

        @if(!is_null($usuariosActivos))
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon blue">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <span class="stat-badge up">Activos</span>
            </div>
            <div class="stat-number">{{ $usuariosActivos }}</div>
            <div class="stat-label">Usuarios Activos</div>
            <div class="stat-bar blue"></div>
        </div>
        @endif

        @if(!is_null($totalTrabajadores))
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon green">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                </div>
                <span class="stat-badge">Total</span>
            </div>
            <div class="stat-number">{{ $totalTrabajadores }}</div>
            <div class="stat-label">Trabajadores Registrados</div>
            <div class="stat-bar green"></div>
        </div>
        @endif

        @if(!is_null($solicitudesPendientes))
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon orange">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 7l-10 7L2 7"/>
                    </svg>
                </div>
                <span class="stat-badge {{ $solicitudesPendientes > 0 ? 'up' : '' }}">Pendientes</span>
            </div>
            <div class="stat-number" style="color: {{ $solicitudesPendientes > 0 ? '#f59e0b' : '#0f172a' }}">
                {{ $solicitudesPendientes }}
            </div>
            <div class="stat-label">Solicitudes Pendientes</div>
            <div class="stat-bar orange"></div>
        </div>
        @endif

        @if(!is_null($prestamosActivos))
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon purple">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <span class="stat-badge up">Activos</span>
            </div>
            <div class="stat-number">{{ $prestamosActivos }}</div>
            <div class="stat-label">Préstamos en Curso</div>
            <div class="stat-bar purple"></div>
        </div>
        @endif

        @if(!is_null($fichasEnProceso))
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon cyan">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <span class="stat-badge {{ $fichasEnProceso > 0 ? 'up' : '' }}">En Proceso</span>
            </div>
            <div class="stat-number" style="color: {{ $fichasEnProceso > 0 ? '#06b6d4' : '#0f172a' }}">
                {{ $fichasEnProceso }}
            </div>
            <div class="stat-label">Fichas de Soporte</div>
            <div class="stat-bar cyan"></div>
        </div>
        @endif

        @if(!is_null($pendientesCambio))
        <div class="stat-card-modern">
            <div class="stat-top">
                <div class="stat-icon red">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                </div>
                <span class="stat-badge {{ $pendientesCambio > 0 ? 'down' : '' }}">
                    {{ $pendientesCambio > 0 ? 'Atención' : '✓' }}
                </span>
            </div>
            <div class="stat-number" style="color: {{ $pendientesCambio > 0 ? '#ef4444' : '#0f172a' }}">
                {{ $pendientesCambio }}
            </div>
            <div class="stat-label">Pendientes Cambio Clave</div>
            <div class="stat-bar red"></div>
        </div>
        @endif

    </div>

    {{-- ========================================== --}}
    {{-- INFORMACIÓN DE LA CUENTA --}}
    {{-- ========================================== --}}
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="info-institucional">
                <div class="info-header">
                    <h5>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Información de tu Cuenta
                    </h5>
                </div>
                <div class="info-body">

                    <div class="info-item">
                        <div class="info-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Usuario</div>
                            <div class="info-value">{{ $usuario->usuario }}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Rol en el Sistema</div>
                            <div class="info-value">{{ $rolDisplay }}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Correo Electrónico</div>
                            <div class="info-value">
                                {{ $trabajador->email ?? 'No registrado' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Último Acceso</div>
                            <div class="info-value">
                                {{ $usuario->ultimo_login ? $usuario->ultimo_login->format('d/m/Y H:i') : 'Primera vez' }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection