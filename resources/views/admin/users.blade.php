@extends('layouts.dashboard')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">👥 Gestión de Usuarios</h2>
                        <p class="text-muted mb-0">Administra los usuarios y sus roles en el sistema</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 2rem;">👥</div>
                        <small class="text-muted">Total: {{ $users->count() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabla de usuarios -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol Actual</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    {{ $user->name }}
                                    @if($user->id === Auth::id())
                                        <span class="badge bg-info ms-2">Tú</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{!! $user->role_badge !!}</td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <!-- Botón cambiar rol - usa data attributes -->
                                    <button type="button"
                                            class="btn btn-sm btn-primary mb-1 w-100"
                                            onclick="openRoleModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->role }}')">
                                        🔄 Cambiar Rol
                                    </button>

                                    <!-- Botón cambiar contraseña -->
                                    <button type="button"
                                            class="btn btn-sm btn-warning w-100"
                                            onclick="openPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                        🔒 Cambiar Pass
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Rol (MODIFICADO) -->
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="roleModalTitle">Cambiar Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="roleForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="roleUserId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar nuevo rol</label>
                        <select class="form-select" name="role" id="roleSelect" required>
                            <option value="user">👤 Usuario Base - Solo ver dashboard y perfil</option>
                            <option value="worker">🔧 Trabajador - Acceso a funciones operativas</option>
                            <option value="super_admin">👑 Super Admin - Acceso total al sistema</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>📌 Descripción de roles:</strong><br>
                            • <strong>Usuario Base:</strong> Solo puede ver dashboard y su perfil.<br>
                            • <strong>Trabajador:</strong> Puede realizar tareas operativas.<br>
                            • <strong>Super Admin:</strong> Control total del sistema.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña (sin cambios) -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="passwordModalTitle">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="passwordForm" action="{{ route('admin.reset-password') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="passwordUserId">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" name="new_password" id="new_password" required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Función para abrir modal de cambio de rol
    function openRoleModal(userId, userName, currentRole) {
        document.getElementById('roleModalTitle').innerHTML = 'Cambiar Rol - ' + userName;
        document.getElementById('roleUserId').value = userId;

        // Establecer la acción del formulario con el ID del usuario
        const form = document.getElementById('roleForm');
        form.action = '/admin/change-role/' + userId;  // URL directa

        // Seleccionar el rol actual en el select
        const select = document.getElementById('roleSelect');
        for(let i = 0; i < select.options.length; i++) {
            if(select.options[i].value === currentRole) {
                select.options[i].selected = true;
                break;
            }
        }

        // Abrir modal usando Bootstrap 5
        var modal = new bootstrap.Modal(document.getElementById('roleModal'));
        modal.show();
    }

    // Función para abrir modal de cambio de contraseña
    function openPasswordModal(userId, userName) {
        document.getElementById('passwordModalTitle').innerHTML = 'Cambiar Contraseña - ' + userName;
        document.getElementById('passwordUserId').value = userId;
        document.getElementById('new_password').value = '';

        // Abrir modal usando Bootstrap 5
        var modal = new bootstrap.Modal(document.getElementById('passwordModal'));
        modal.show();
    }
</script>
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
        border: none;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .btn-sm {
        font-size: 0.8rem;
    }

    .table th, .table td {
        vertical-align: middle;
    }
</style>
@endsection
