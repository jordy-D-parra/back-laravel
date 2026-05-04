@extends('layouts.dashboard')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="h4 mb-3">👥 Gestión de Usuarios</h2>
                        <p class="text-muted mb-0">Administra los usuarios, sus roles y estados en el sistema</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="openCreateUserModal()">➕ Nuevo Usuario</button>
                        <div class="text-center">
                            <div style="font-size: 2rem;">👥</div>
                            <small class="text-muted">Total: {{ $users->count() }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buscador/Filtro -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Buscar por nombre o cédula</label>
                        <input type="text" id="searchUser" class="form-control" placeholder="Buscar usuario...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Filtrar por rol</label>
                        <select id="filterRol" class="form-select">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->nombre }}">{{ ucfirst($rol->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Filtrar por estado</label>
                        <select id="filterEstado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="suspendido">Suspendido</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small">&nbsp;</label>
                        <button id="limpiarFiltros" class="btn btn-secondary w-100">Limpiar filtros</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr><th>ID</th><th>Nombre</th><th>Cédula</th><th>Rol Actual</th><th>Estado</th><th>Fecha Registro</th><th>Acciones</th></tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @foreach($users as $user)
                            <tr data-id="{{ $user->id }}" data-nombre="{{ strtolower($user->nombre . ' ' . $user->apellido) }}" data-cedula="{{ $user->cedula }}" data-rol="{{ $user->rol->nombre ?? '' }}" data-estado="{{ $user->estado_usuario }}">
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->nombre }} {{ $user->apellido }} @if($user->id === Auth::id()) <span class="badge bg-info ms-2">Tú</span>@endif</td>
                                <td>{{ $user->cedula }}</td>
                                <td>@if($user->rol)<span class="badge {{ match($user->rol->nombre){'super_admin'=>'bg-danger','admin'=>'bg-warning text-dark','worker'=>'bg-primary','user'=>'bg-success',default=>'bg-secondary'} }} fs-6 p-2">{{ match($user->rol->nombre){'super_admin'=>'👑','admin'=>'⚙️','worker'=>'🔧','user'=>'👤',default=>'❓'} }} {{ ucfirst($user->rol->nombre) }}</span>@else<span class="badge bg-danger fs-6 p-2">⚠️ SIN ROL</span>@endif</td>
                                <td><span class="badge bg-{{ match($user->estado_usuario){'activo'=>'success','pendiente'=>'warning','inactivo'=>'danger','suspendido'=>'secondary',default=>'secondary'} }} fs-6 p-2">{{ match($user->estado_usuario){'activo'=>'✅','pendiente'=>'⏳','inactivo'=>'❌','suspendido'=>'⚠️',default=>''} }} {{ ucfirst($user->estado_usuario) }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($user->fecha_solicitud)->format('d/m/Y') }}</td>
                                <td><div class="btn-group-vertical w-100"><button class="btn btn-sm btn-primary mb-1" onclick="openRoleModal({{ $user->id }}, '{{ addslashes($user->nombre . ' ' . $user->apellido) }}', '{{ $user->rol->nombre ?? 'user' }}')">🔄 Cambiar Rol</button><button class="btn btn-sm btn-info mb-1" onclick="openEstadoModal({{ $user->id }}, '{{ addslashes($user->nombre . ' ' . $user->apellido) }}', '{{ $user->estado_usuario }}')">📌 Cambiar Estado</button><button class="btn btn-sm btn-warning mb-1" onclick="openPasswordModal({{ $user->id }}, '{{ addslashes($user->nombre . ' ' . $user->apellido) }}')">🔒 Cambiar Pass</button>@if($user->id !== Auth::id())<button class="btn btn-sm btn-danger" onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->nombre . ' ' . $user->apellido) }}')">🗑️ Eliminar</button>@endif</div></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR USUARIO (SIN EMAIL) -->
<div class="modal fade" id="createUserModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title">➕ Crear Nuevo Usuario</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="createUserForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre" id="create_nombre" required></div></div>
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Apellido <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellido" id="create_apellido" required></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Cédula <span class="text-danger">*</span></label><input type="text" class="form-control" name="cedula" id="create_cedula" required maxlength="20"><small class="text-muted">Será el usuario para iniciar sesión</small></div></div>
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Departamento</label><input type="text" class="form-control" name="departamento" id="create_departamento"></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Cargo</label><input type="text" class="form-control" name="cargo" id="create_cargo"></div></div>
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Rol <span class="text-danger">*</span></label><select class="form-select" name="id_rol" id="create_rol" required><option value="">-- Seleccione un rol --</option>@foreach($roles as $rol)<option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}</option>@endforeach</select></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Estado <span class="text-danger">*</span></label><select class="form-select" name="estado_usuario" id="create_estado" required><option value="activo">✅ Activo</option><option value="pendiente">⏳ Pendiente</option><option value="inactivo">❌ Inactivo</option><option value="suspendido">⚠️ Suspendido</option></select></div></div>
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Contraseña <span class="text-danger">*</span></label><input type="password" class="form-control" name="password" id="create_password" required minlength="6"><small class="text-muted">Mínimo 6 caracteres</small></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Confirmar Contraseña <span class="text-danger">*</span></label><input type="password" class="form-control" name="password_confirmation" id="create_password_confirmation" required></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Pregunta 1 <span class="text-danger">*</span></label><select class="form-select" name="pregunta_seguridad_1" id="create_pregunta1" required><option value="">-- Seleccione --</option><option value="¿Nombre de tu primera mascota?">🐕 ¿Nombre de tu primera mascota?</option><option value="¿Nombre de tu madre soltera?">👩 ¿Nombre de tu madre soltera?</option><option value="¿Modelo de tu primer auto?">🚗 ¿Modelo de tu primer auto?</option><option value="¿Ciudad donde naciste?">🏙️ ¿Ciudad donde naciste?</option></select></div></div>
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Respuesta 1 <span class="text-danger">*</span></label><input type="text" class="form-control" name="respuesta_1" id="create_respuesta1" required></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Pregunta 2 <span class="text-danger">*</span></label><select class="form-select" name="pregunta_seguridad_2" id="create_pregunta2" required><option value="">-- Seleccione --</option><option value="¿Nombre de tu héroe favorito?">🦸 ¿Nombre de tu héroe favorito?</option><option value="¿Marca de tu primer celular?">📱 ¿Marca de tu primer celular?</option><option value="¿Nombre de tu primer profesor?">👨‍🏫 ¿Nombre de tu primer profesor?</option><option value="¿Tu comida favorita?">🍕 ¿Tu comida favorita?</option></select></div></div>
                        <div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Respuesta 2 <span class="text-danger">*</span></label><input type="text" class="form-control" name="respuesta_2" id="create_respuesta2" required></div></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-success" onclick="confirmarCrearUsuario()">✅ Crear Usuario</button></div>
        </div>
    </div>
</div>

<!-- MODAL CAMBIAR ROL -->
<div class="modal fade" id="roleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title" id="roleModalTitle">Cambiar Rol</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="roleUserId"><select class="form-select" id="roleSelect"><option value="">-- Seleccione --</option>@foreach($roles as $rol)<option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }}</option>@endforeach</select></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" onclick="confirmarCambioRol()">Guardar</button></div></div></div></div>

<!-- MODAL CAMBIAR ESTADO -->
<div class="modal fade" id="estadoModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-info text-white"><h5 class="modal-title" id="estadoModalTitle">Cambiar Estado</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="estadoUserId"><select class="form-select" id="estadoSelect"><option value="activo">✅ Activo</option><option value="pendiente">⏳ Pendiente</option><option value="inactivo">❌ Inactivo</option><option value="suspendido">⚠️ Suspendido</option></select></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-info" onclick="confirmarCambioEstado()">Guardar</button></div></div></div></div>

<!-- MODAL CAMBIAR CONTRASEÑA -->
<div class="modal fade" id="passwordModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-warning"><h5 class="modal-title" id="passwordModalTitle">Cambiar Contraseña</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="passwordUserId"><input type="password" class="form-control mb-2" id="new_password" placeholder="Nueva contraseña" required><input type="password" class="form-control" id="new_password_confirmation" placeholder="Confirmar contraseña" required></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-warning" onclick="confirmarCambioPassword()">Actualizar</button></div></div></div></div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="deleteUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Eliminar Usuario</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="deleteUserId"><p>¿Eliminar a <strong id="deleteUserName"></strong>? <strong class="text-danger">No se puede deshacer.</strong></p></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-danger" onclick="confirmarEliminarUsuario()">Eliminar</button></div></div></div></div>

<!-- NOTIFICACIONES TOAST -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999"><div id="notificationToast" class="toast align-items-center text-white border-0" role="alert"><div class="d-flex"><div class="toast-body" id="toastMessage">Mensaje</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div></div>

<script>
// ========== FILTROS ==========
function aplicarFiltros() {
    const searchTerm = document.getElementById('searchUser').value.toLowerCase();
    const filterRol = document.getElementById('filterRol').value.toLowerCase();
    const filterEstado = document.getElementById('filterEstado').value.toLowerCase();
    const rows = document.querySelectorAll('#usersTableBody tr');
    rows.forEach(row => {
        const nombre = row.getAttribute('data-nombre') || '';
        const cedula = row.getAttribute('data-cedula') || '';
        const rol = row.getAttribute('data-rol') || '';
        const estado = row.getAttribute('data-estado') || '';
        let show = true;
        if (searchTerm && !nombre.includes(searchTerm) && !cedula.includes(searchTerm)) show = false;
        if (filterRol && !rol.includes(filterRol)) show = false;
        if (filterEstado && !estado.includes(filterEstado)) show = false;
        row.style.display = show ? '' : 'none';
    });
}

document.getElementById('searchUser')?.addEventListener('keyup', aplicarFiltros);
document.getElementById('filterRol')?.addEventListener('change', aplicarFiltros);
document.getElementById('filterEstado')?.addEventListener('change', aplicarFiltros);
document.getElementById('limpiarFiltros')?.addEventListener('click', () => {
    document.getElementById('searchUser').value = '';
    document.getElementById('filterRol').value = '';
    document.getElementById('filterEstado').value = '';
    aplicarFiltros();
});

// ========== NOTIFICACIONES ==========
let toastInstance = null;
function showNotification(message, type = 'success') {
    const toastEl = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toastMessage');
    if (!toastEl) return;
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    if (type === 'success') toastEl.classList.add('bg-success');
    else if (type === 'error') toastEl.classList.add('bg-danger');
    else if (type === 'warning') toastEl.classList.add('bg-warning');
    else toastEl.classList.add('bg-info');
    toastMessage.textContent = message;
    if (toastInstance) toastInstance.hide();
    toastInstance = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
    toastInstance.show();
}

// ========== ABRIR MODALES ==========
function openCreateUserModal() {
    document.getElementById('createUserForm').reset();
    new bootstrap.Modal(document.getElementById('createUserModal')).show();
}

function openRoleModal(userId, userName, currentRole) {
    document.getElementById('roleModalTitle').innerHTML = 'Cambiar Rol - ' + userName;
    document.getElementById('roleUserId').value = userId;
    const select = document.getElementById('roleSelect');
    for (let i = 0; i < select.options.length; i++) {
        if (currentRole && select.options[i].text.toLowerCase().includes(currentRole.toLowerCase())) {
            select.options[i].selected = true;
            break;
        }
    }
    new bootstrap.Modal(document.getElementById('roleModal')).show();
}

function openEstadoModal(userId, userName, currentEstado) {
    document.getElementById('estadoModalTitle').innerHTML = 'Cambiar Estado - ' + userName;
    document.getElementById('estadoUserId').value = userId;
    const select = document.getElementById('estadoSelect');
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value === currentEstado) {
            select.options[i].selected = true;
            break;
        }
    }
    new bootstrap.Modal(document.getElementById('estadoModal')).show();
}

function openPasswordModal(userId, userName) {
    document.getElementById('passwordModalTitle').innerHTML = 'Cambiar Contraseña - ' + userName;
    document.getElementById('passwordUserId').value = userId;
    document.getElementById('new_password').value = '';
    document.getElementById('new_password_confirmation').value = '';
    new bootstrap.Modal(document.getElementById('passwordModal')).show();
}

function openDeleteModal(userId, userName) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').innerHTML = userName;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}

// ========== CREAR USUARIO ==========
async function confirmarCrearUsuario() {
    const data = {
        nombre: document.getElementById('create_nombre').value.trim(),
        apellido: document.getElementById('create_apellido').value.trim(),
        cedula: document.getElementById('create_cedula').value.trim(),
        departamento: document.getElementById('create_departamento').value,
        cargo: document.getElementById('create_cargo').value,
        id_rol: document.getElementById('create_rol').value,
        estado_usuario: document.getElementById('create_estado').value,
        password: document.getElementById('create_password').value,
        password_confirmation: document.getElementById('create_password_confirmation').value,
        pregunta_seguridad_1: document.getElementById('create_pregunta1').value,
        respuesta_1: document.getElementById('create_respuesta1').value.trim(),
        pregunta_seguridad_2: document.getElementById('create_pregunta2').value,
        respuesta_2: document.getElementById('create_respuesta2').value.trim(),
        _token: '{{ csrf_token() }}'
    };

    if (!data.nombre || !data.apellido || !data.cedula || !data.id_rol || !data.password) {
        showNotification('Complete todos los campos obligatorios', 'warning');
        return;
    }
    if (data.password.length < 6) {
        showNotification('La contraseña debe tener al menos 6 caracteres', 'warning');
        return;
    }
    if (data.password !== data.password_confirmation) {
        showNotification('Las contraseñas no coinciden', 'warning');
        return;
    }

    const modalEl = document.getElementById('createUserModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    const btn = modalEl.querySelector('.btn-success');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando...';

    try {
        const response = await fetch('{{ route("admin.users.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': data._token },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            modal.hide();
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(result.message || 'Error al crear usuario', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        showNotification('Error: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ========== CAMBIAR CONTRASEÑA ==========
async function confirmarCambioPassword() {
    const userId = document.getElementById('passwordUserId').value;
    const newPassword = document.getElementById('new_password').value;
    const newPasswordConfirmation = document.getElementById('new_password_confirmation').value;

    if (!newPassword) {
        showNotification('Ingrese la nueva contraseña', 'warning');
        return;
    }
    if (newPassword.length < 6) {
        showNotification('Mínimo 6 caracteres', 'warning');
        return;
    }
    if (newPassword !== newPasswordConfirmation) {
        showNotification('Las contraseñas no coinciden', 'warning');
        return;
    }

    const modalEl = document.getElementById('passwordModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    const btn = modalEl.querySelector('.btn-warning');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Actualizando...';

    try {
        const response = await fetch('{{ route("admin.reset-password") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ user_id: userId, new_password: newPassword, new_password_confirmation: newPasswordConfirmation })
        });
        const result = await response.json();
        if (result.success) {
            modal.hide();
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(result.message || 'Error al cambiar la contraseña', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        showNotification('Error: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ========== CAMBIAR ROL ==========
async function confirmarCambioRol() {
    const userId = document.getElementById('roleUserId').value;
    const roleId = document.getElementById('roleSelect').value;

    if (!roleId) {
        showNotification('Seleccione un rol', 'warning');
        return;
    }

    const modalEl = document.getElementById('roleModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    const btn = modalEl.querySelector('.btn-primary');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

    try {
        const response = await fetch(`/admin/change-role/${userId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id_rol: roleId })
        });
        const result = await response.json();
        if (result.success) {
            modal.hide();
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(result.message || 'Error al cambiar el rol', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        showNotification('Error de conexión: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ========== CAMBIAR ESTADO ==========
async function confirmarCambioEstado() {
    const userId = document.getElementById('estadoUserId').value;
    const estado = document.getElementById('estadoSelect').value;

    if (!estado) {
        showNotification('Seleccione un estado', 'warning');
        return;
    }

    const modalEl = document.getElementById('estadoModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    const btn = modalEl.querySelector('.btn-info');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

    try {
        const response = await fetch('/admin/change-status', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ user_id: userId, estado_usuario: estado })
        });
        const result = await response.json();
        if (result.success) {
            modal.hide();
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(result.message || 'Error al cambiar el estado', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        showNotification('Error de conexión: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ========== ELIMINAR USUARIO ==========
async function confirmarEliminarUsuario() {
    const userId = document.getElementById('deleteUserId').value;

    if (!confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')) {
        return;
    }

    const modalEl = document.getElementById('deleteUserModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    const btn = modalEl.querySelector('.btn-danger');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';

    try {
        const response = await fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await response.json();
        if (result.success) {
            modal.hide();
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(result.message || 'Error al eliminar usuario', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        showNotification('Error de conexión: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ========== ASIGNAR ROL URGENTE ==========
function asignarRolUrgente(userId, userName) {
    const modalHtml = `
        <div class="modal fade" id="rolUrgenteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">🚨 Asignar Rol - ${userName}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning"><strong>⚠️ Este usuario NO tiene rol asignado!</strong></div>
                        <select class="form-select" id="rolUrgenteSelect">
                            <option value="">-- Seleccione un rol --</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" onclick="confirmarAsignacionRolUrgente(${userId})">✅ Asignar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    const existing = document.getElementById('rolUrgenteModal');
    if (existing) existing.remove();
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    new bootstrap.Modal(document.getElementById('rolUrgenteModal')).show();
}

async function confirmarAsignacionRolUrgente(userId) {
    const roleId = document.getElementById('rolUrgenteSelect')?.value;
    if (!roleId) {
        showNotification('Seleccione un rol', 'warning');
        return;
    }
    const modalEl = document.getElementById('rolUrgenteModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    try {
        const response = await fetch(`/admin/change-role/${userId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id_rol: roleId })
        });
        const result = await response.json();
        if (result.success) {
            modal.hide();
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(result.message || 'Error al asignar rol', 'error');
        }
    } catch (error) {
        showNotification('Error de conexión: ' + error.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', aplicarFiltros);
</script>
@endsection
