@extends('layouts.dashboard')

@section('title', 'Trabajadores')

@section('styles')
@vite(['resources/css/admin-trabajadores.css'])
<style>
    /* ============================================================
       PAGINACIÓN ELEGANTE
       ============================================================ */
    .pagination-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        flex-wrap: wrap;
        gap: 0.75rem;
        background: white;
        border-top: 1px solid var(--border-light);
    }

    .pagination-info {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .pagination-btns {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
    }

    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 0.6rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--primary-dark);
        background: white;
        border: 1.5px solid var(--border-light);
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        cursor: pointer;
        user-select: none;
        line-height: 1;
    }

    .pagination-btn:hover:not(.disabled):not(.active) {
        background: var(--primary-lighter);
        border-color: var(--primary-dark);
        color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 60, 114, 0.15);
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-light));
        color: white;
        border-color: var(--primary-dark);
        box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
        font-weight: 700;
    }

    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    .pagination-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.85rem;
    }

    @media (max-width: 576px) {
        .pagination-bar {
            flex-direction: column;
            align-items: center;
        }
        .pagination-btn {
            min-width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">

    {{-- ========== HEADER CON GRADIENTE ========== --}}
    <div class="page-header">
        <div>
            <h4>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                </svg>
                Gestión de Trabajadores
            </h4>
            <p>Registro y administración del personal del departamento</p>
        </div>
    </div>

    {{-- ========== TARJETAS DE ESTADÍSTICAS ========== --}}
    <div class="stats-row">
        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $totalTrabajadores ?? 0 }}</div>
                <div class="stat-label">Total Trabajadores</div>
            </div>
            <div class="stat-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                </svg>
            </div>
        </div>

        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $conUsuario ?? 0 }}</div>
                <div class="stat-label">Con Usuario</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(30, 126, 52, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1e7e34" stroke-width="1.8">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
        </div>

        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $sinUsuario ?? 0 }}</div>
                <div class="stat-label">Sin Usuario</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(197, 34, 31, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c5221f" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
        </div>

        <div class="stat-card-mini">
            <div class="stat-info">
                <div class="stat-number">{{ $departamentos ?? 0 }}</div>
                <div class="stat-label">Departamentos</div>
            </div>
            <div class="stat-icon-circle" style="background: rgba(23, 162, 184, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="1.8">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M8 8h8M8 12h6M8 16h4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ========== BARRA DE FILTROS ========== --}}
    <div class="filters-bar">
        <div class="filtro-busqueda">
            <div class="input-group">
                <span class="input-group-text">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </span>
                <input type="text" class="form-control" id="buscarTrabajador"
                    placeholder="Buscar por nombre, apellido o cédula..."
                    value="{{ request('search') }}">
            </div>
        </div>

        @if(auth()->user()->hasPermission('crear-trabajador'))
        <button style="color: #fff" class="btn btn-primary-dark btn-accion"
            data-bs-toggle="modal" data-bs-target="#modalTrabajador">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Trabajador
        </button>
        @endif
    </div>

    {{-- ========== TABLA ========== --}}
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Especialidad</th>
                        <th>Usuario</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaTrabajadores">
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Cargando trabajadores...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginacionTrabajadores"></div>
    </div>
</div>

{{-- ========== MODAL CREAR/EDITAR TRABAJADOR ========== --}}
<div class="modal fade" id="modalTrabajador" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTrabajadorTitulo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    Nuevo Trabajador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTrabajador" method="POST" action="/admin/trabajadores">
                @csrf
                <input type="hidden" name="_method" id="trabajadorMethod" value="POST">
                <div class="modal-body px-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Cédula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trabajadorCedula" name="cedula" placeholder="V-12345678" required>
                            <div id="cedulaFeedback" class="cedula-feedback" style="font-size:0.75rem;margin-top:4px;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="text" class="form-control" id="trabajadorTelefono" name="telefono" placeholder="0412-1234567">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trabajadorNombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trabajadorApellido" name="apellido" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" class="form-control" id="trabajadorEmail" name="email" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Departamento <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="trabajadorDepartamento" name="departamento" value="Informática" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trabajadorCargo" name="cargo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Especialidad</label>
                            <input type="text" class="form-control" id="trabajadorEspecialidad" name="especialidad" placeholder="Ej: Redes, Circuitos, Soporte...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark" id="btnGuardarTrabajador" style="color: #fff;">Guardar Trabajador</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========== MODAL CONFIRMAR ELIMINACIÓN ========== --}}
<div class="modal fade" id="modalConfirmDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div id="deleteWarningUsuario" class="delete-warning" style="display:none;">
                    <p class="mb-1 fw-medium" style="color: var(--danger);">No se puede eliminar.</p>
                    <p class="mb-0 small text-muted">Este trabajador tiene un usuario vinculado. Debe eliminar el usuario primero.</p>
                </div>
                <div id="deleteConfirmText">
                    <p class="mb-1">Se eliminará permanentemente al trabajador:</p>
                    <p class="fw-bold" id="deleteTrabajadorNombre" style="color: var(--primary-dark);"></p>
                    <p class="small text-danger mb-0">Esta acción no se puede deshacer.</p>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-primary-dark" data-bs-dismiss="modal">Cancelar</button>
                <form id="formDelete" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Permanentemente</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ========== MODAL DETALLE ========== --}}
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Trabajador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <h6 style="color: var(--primary-dark); font-weight: 600; margin-bottom: 1rem;">Datos Personales</h6>
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Cédula</div><div class="detail-value" id="dtCedula">-</div></div>
                    <div class="detail-item"><div class="detail-label">Nombre Completo</div><div class="detail-value" id="dtNombre">-</div></div>
                    <div class="detail-item"><div class="detail-label">Email</div><div class="detail-value" id="dtEmail">-</div></div>
                    <div class="detail-item"><div class="detail-label">Teléfono</div><div class="detail-value" id="dtTelefono">-</div></div>
                    <div class="detail-item"><div class="detail-label">Departamento</div><div class="detail-value" id="dtDepartamento">-</div></div>
                    <div class="detail-item"><div class="detail-label">Cargo</div><div class="detail-value" id="dtCargo">-</div></div>
                    <div class="detail-item"><div class="detail-label">Especialidad</div><div class="detail-value" id="dtEspecialidad">-</div></div>
                    <div class="detail-item"><div class="detail-label">Registrado</div><div class="detail-value" id="dtCreado">-</div></div>
                </div>
                <hr>
                <h6 style="color: var(--primary-dark); font-weight: 600; margin-bottom: 1rem;">Usuario Vinculado</h6>
                <div id="dtInfoUsuario" class="detail-grid" style="display:none;"></div>
                <div id="dtSinUsuario" class="text-muted small">Sin usuario vinculado.</div>
                <button type="button" class="btn btn-primary-dark btn-sm mt-2" id="btnCrearUsuarioDesdeDetalle" style="display:none;">
                    Crear Usuario para este Trabajador
                </button>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal" style="color: #fff;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ========== CONTENEDOR DE NOTIFICACIONES TOAST ========== --}}
<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 99999; width: 340px;"></div>

{{-- ========== DATOS INICIALES (JSON para el JS) ========== --}}
<script>
    window.trabajadoresIniciales = @json($trabajadores->items() ?? []);
</script>

@endsection

@section('scripts')
@vite(['resources/js/admin-trabajadores.js'])

<script>
    // ============================================================
    // SISTEMA DE NOTIFICACIONES TOAST
    // ============================================================
    window.mostrarNotificacion = function(tipo, mensaje) {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const colores = {
            success: '#1e7e34',
            error: '#c5221f',
            warning: '#f6c23e',
            info: '#1e3c72'
        };

        const iconos = {
            success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>',
            error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${colores[tipo] || colores.info};
            color: white;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            animation: slideInRight 0.3s ease-out;
            cursor: pointer;
            white-space: pre-line;
            line-height: 1.4;
        `;

        toast.innerHTML = `
            <span style="flex-shrink:0; display:flex;">${iconos[tipo] || iconos.info}</span>
            <span style="flex:1;">${mensaje}</span>
        `;

        toast.addEventListener('click', () => {
            toast.style.transition = 'all 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        });

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.transition = 'all 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 4500);
    };

    // ============================================================
    // INYECTAR ESTILOS DE ANIMACIÓN
    // ============================================================
    (function() {
        if (document.getElementById('notif-styles')) return;
        const style = document.createElement('style');
        style.id = 'notif-styles';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    })();

    // ============================================================
    // DETECTAR MENSAJES FLASH DE LARAVEL (SESSION)
    // ============================================================
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            mostrarNotificacion('success', @json(session('success')));
        @endif

        @if(session('error'))
            mostrarNotificacion('error', @json(session('error')));
        @endif

        @if(session('warning'))
            mostrarNotificacion('warning', @json(session('warning')));
        @endif

        @if(session('info'))
            mostrarNotificacion('info', @json(session('info')));
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                mostrarNotificacion('error', @json($error));
            @endforeach
        @endif
    });
</script>
@endsection