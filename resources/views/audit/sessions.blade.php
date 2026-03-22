@extends('layouts.dashboard')

@section('title', 'Sesiones Activas')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">🟢 Sesiones Activas</h2>
                        <p class="text-muted mb-0">Usuarios actualmente conectados al sistema</p>
                    </div>
                    <div>
                        <span class="badge bg-success" style="font-size: 1.2rem;">
                            {{ $sessions->count() }} conectados
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                              <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Última Actividad</th>
                                <th>IP</th>
                                <th>Acciones</th>
                              </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                              <tr>
                                <td>
                                    <strong>{{ $session->name }}</strong><br>
                                    <small class="text-muted">{{ $session->email }}</small>
                                </td>
                                <td>{!! \App\Models\User::find($session->user_id)?->role_badge !!}</td>
                                <td>{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->format('d/m/Y H:i:s') }}</td>
                                <td><code>{{ $session->ip_address ?? 'N/A' }}</code></td>
                                <td>
                                    <button onclick="confirmClearSession({{ $session->user_id }})"
                                            class="btn btn-sm btn-danger">
                                        🚪 Cerrar sesión
                                    </button>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center">No hay sesiones activas</td>
                              </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button onclick="confirmClearAllSessions()" class="btn btn-warning">
                        🔒 Cerrar todas las sesiones (excepto la actual)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmClearSession(userId) {
    if(confirm('¿Estás seguro de que deseas cerrar la sesión de este usuario?')) {
        window.location.href = '/audit/sessions/clear/' + userId;
    }
}

function confirmClearAllSessions() {
    if(confirm('¿Estás seguro de que deseas cerrar TODAS las sesiones excepto la tuya?')) {
        window.location.href = '/audit/sessions/clear';
    }
}
</script>
@endsection
