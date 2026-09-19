{{-- resources/views/components/user-avatar-menu.blade.php --}}
@php
    $user = Auth::user();
    $trabajador = $user->trabajador;
    $nombre = $trabajador?->nombre ?? $user->usuario;
    $apellido = $trabajador?->apellido ?? '';
    $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
    $rolNombre = $user->rol?->nombre ?? 'sin_rol';
    $fotoUrl = $user->foto_perfil_url;
@endphp

<div class="user-avatar-container">
    <button class="user-avatar-btn" id="userAvatarBtn" title="Mi perfil">
        <img src="{{ $fotoUrl }}" alt="Avatar" class="user-avatar-img" id="userAvatarImg">
    </button>

    {{-- Dropdown menú rápido --}}
    <div class="user-avatar-dropdown" id="userAvatarDropdown">
        <div class="avatar-dropdown-header">
            <img src="{{ $fotoUrl }}" alt="Avatar" class="avatar-dropdown-img" id="userAvatarImg2">
            <div class="avatar-dropdown-info">
                <strong>{{ $nombre }} {{ $apellido }}</strong>
                <small>{{ $user->usuario }}</small>
                <span class="avatar-role-badge role-{{ $rolNombre }}">
                    {{ ucfirst(str_replace('_', ' ', $rolNombre)) }}
                </span>
            </div>
        </div>

        <div class="avatar-dropdown-divider"></div>

        <button type="button" class="avatar-dropdown-item" onclick="abrirModalPerfil(event)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            Ver mi perfil
        </button>

        <div class="avatar-dropdown-divider"></div>

        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="avatar-dropdown-item text-danger w-100">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</div>