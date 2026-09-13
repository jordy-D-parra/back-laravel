@extends('layouts.dashboard')

@section('title', 'Registro de Actividad')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">📋 Registro de Actividad</h2>
                        <p class="text-muted mb-0">Historial de acciones realizadas por los usuarios</p>
                    </div>
                    <div style="font-size: 2rem;">📊</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                              <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>Tabla</th>
                                <th>IP</th>
                              </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                              <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <strong>{{ $log->user_name ?? 'Sistema' }}</strong>
                                    @if($log->user)
                                        <br>
                                        <small class="text-muted">Cédula: {{ $log->user->cedula ?? '' }}</small>
                                    @endif
                                </td>
                                <td>{{ $log->user_role ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->action_color }}">
                                        {{ $log->action_icon }} {{ ucfirst(strtolower($log->operation)) }}
                                    </span>
                                </td>
                                <td>{{ $log->description ?? 'Sin descripción' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $log->table_name }}</span>
                                    @if($log->record_id)
                                        <br>
                                        <small class="text-muted">ID: {{ $log->record_id }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $log->ip_address ?? 'N/A' }}</code></td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="7" class="text-center">No hay registros de actividad</td>
                              </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

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

.table th, .table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection