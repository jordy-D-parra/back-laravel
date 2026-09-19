{{-- resources/views/components/user-profile-modal.blade.php --}}
@php
    $user = Auth::user();
    $trabajador = $user->trabajador;
    $fotoUrl = $user->foto_perfil_url;
@endphp

{{-- ============================================================ --}}
{{-- MODAL DE PERFIL (fuera del topbar para evitar stacking context) --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content perfil-modal-content">

            {{-- ===== HEADER ===== --}}
            <div class="perfil-modal-header">
                <div class="perfil-header-bg"></div>
                <div class="perfil-header-content">
                    <div class="perfil-header-left">
                        <div class="perfil-header-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="perfil-modal-title">Mi Perfil</h5>
                            <p class="perfil-modal-subtitle">Gestiona tu información personal</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            {{-- ===== BODY ===== --}}
            <div class="perfil-modal-body">

                {{-- ===== SECCIÓN FOTO ===== --}}
                <div class="perfil-foto-section">
                    <div class="perfil-foto-wrapper">
                        <img src="{{ $fotoUrl }}" alt="Foto de perfil" class="perfil-foto-img" id="perfilFotoImg">
                        <label for="perfilFotoInput" class="perfil-foto-overlay" title="Cambiar foto">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                        </label>
                        <input type="file" id="perfilFotoInput" accept="image/*" style="display:none;">
                    </div>
                    <div class="perfil-foto-actions">
                        <label for="perfilFotoInput" class="btn-perfil-upload">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            Subir foto
                        </label>
                        <button type="button" class="btn-perfil-delete" id="btnEliminarFoto" style="{{ $user->foto_perfil ? '' : 'display:none;' }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                            Eliminar
                        </button>
                    </div>
                    <small class="perfil-foto-help">JPG, PNG o WEBP. Máximo 2MB.</small>
                </div>

                <hr class="perfil-divider">

                {{-- ===== TABS ===== --}}
                <ul class="nav nav-tabs perfil-tabs" id="perfilTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-info" data-bs-toggle="tab" data-bs-target="#panel-info" type="button" role="tab">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Información
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-password" data-bs-toggle="tab" data-bs-target="#panel-password" type="button" role="tab">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Contraseña
                        </button>
                    </li>
                </ul>

                <div class="tab-content perfil-tab-content">

                    {{-- ===== TAB INFORMACIÓN ===== --}}
                    <div class="tab-pane fade show active" id="panel-info" role="tabpanel">

                        {{-- AVISO: si no tiene foto --}}
                        <div class="perfil-info-banner">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            <span>Los campos <strong>Nombre</strong> y <strong>Apellido</strong> son los que se muestran en el sistema.</span>
                        </div>

                        <form id="formPerfilInfo">
                            @csrf
                            @method('PUT')

                            {{-- ===== SECCIÓN: DATOS PERSONALES ===== --}}
                            <div class="perfil-seccion">
                                <h6 class="perfil-seccion-titulo">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    Datos Personales
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="perfil-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control perfil-input" name="nombre" id="perfil_nombre" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Apellido <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control perfil-input" name="apellido" id="perfil_apellido" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Cédula</label>
                                        <input type="text" class="form-control perfil-input" id="perfil_cedula" readonly disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Usuario</label>
                                        <input type="text" class="form-control perfil-input" id="perfil_usuario" readonly disabled>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== SECCIÓN: CONTACTO ===== --}}
                            <div class="perfil-seccion">
                                <h6 class="perfil-seccion-titulo">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                        <polyline points="22,6 12,13 2,6"/>
                                    </svg>
                                    Información de Contacto
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="perfil-label">Email</label>
                                        <input type="email" class="form-control perfil-input" name="email" id="perfil_email" placeholder="correo@ejemplo.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Teléfono</label>
                                        <input type="text" class="form-control perfil-input" name="telefono" id="perfil_telefono" placeholder="0412-1234567">
                                    </div>
                                </div>
                            </div>

                            {{-- ===== SECCIÓN: LABORAL ===== --}}
                            <div class="perfil-seccion">
                                <h6 class="perfil-seccion-titulo">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                    </svg>
                                    Información Laboral
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="perfil-label">Departamento</label>
                                        <input type="text" class="form-control perfil-input" name="departamento" id="perfil_departamento">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Cargo</label>
                                        <input type="text" class="form-control perfil-input" name="cargo" id="perfil_cargo">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Especialidad</label>
                                        <input type="text" class="form-control perfil-input" name="especialidad" id="perfil_especialidad" placeholder="Ej: Redes, Sistemas...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Rol en el sistema</label>
                                        <input type="text" class="form-control perfil-input" id="perfil_rol" readonly disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="perfil-form-actions">
                                <button type="button" class="btn-perfil-cancel" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn-perfil-save" id="btnGuardarInfo">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                        <polyline points="17 21 17 13 7 13 7 21"/>
                                        <polyline points="7 3 7 8 15 8"/>
                                    </svg>
                                    Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ===== TAB CONTRASEÑA ===== --}}
                    <div class="tab-pane fade" id="panel-password" role="tabpanel">

                        <div class="perfil-info-banner warning">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            <span>La contraseña debe tener mínimo 6 caracteres. Se recomienda usar mayúsculas, números y símbolos.</span>
                        </div>

                        <form id="formPerfilPassword">
                            @csrf
                            @method('PUT')

                            <div class="perfil-seccion">
                                <h6 class="perfil-seccion-titulo">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                    Cambiar Contraseña
                                </h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="perfil-label">Contraseña actual <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control perfil-input" name="current_password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Nueva contraseña <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control perfil-input" name="new_password" required minlength="6">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="perfil-label">Confirmar contraseña <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control perfil-input" name="new_password_confirmation" required minlength="6">
                                    </div>
                                </div>
                            </div>

                            <div class="perfil-form-actions">
                                <button type="button" class="btn-perfil-cancel" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn-perfil-save">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                    Cambiar contraseña
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>