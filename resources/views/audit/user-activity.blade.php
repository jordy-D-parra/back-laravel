@extends('layouts.dashboard')

@section('title', 'Actividad de ' . $user->name)

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-3">📋 Actividad de {{ $user->name }}</h2>
                        <p class="text-muted mb-0">{{ $user->email }} - {!! $user->role_badge !!}</p>
                    </div>
                    <a href="{{ route('audit.logs') }}" class="btn btn-secondary">← Volver</a>
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
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>IP</th>
                              </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                              <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->action_color }}">
                                        {{ $log->action_icon }} {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td>{{ $log->description }}</td>
                                <td><code>{{ $log->ip_address }}</code></td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="4" class="text-center">No hay registros de actividad para este usuario</td>
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
@endsection
