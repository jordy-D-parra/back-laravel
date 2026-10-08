@extends('reportes.pdf.layout')

@section('contenido')

{{-- STATS --}}
<table class="pdf-stats">
    <tr>
        <td><span class="num">{{ $stats['total'] }}</span><span class="lbl">Total</span></td>
        <td class="stat-green"><span class="num">{{ $stats['activos'] }}</span><span class="lbl">Activos</span></td>
        <td class="stat-red"><span class="num">{{ $stats['inactivos'] }}</span><span class="lbl">Inactivos</span></td>
        <td class="stat-orange"><span class="num">{{ $stats['pendientes_cambio'] }}</span><span class="lbl">Pend. Clave</span></td>
        <td class="stat-gray"><span class="num">{{ $stats['nunca_logeado'] }}</span><span class="lbl">Nunca Ingresó</span></td>
    </tr>
</table>

{{-- POR ROL --}}
@if($porRol->count() > 0)
<div class="pdf-section-title">Distribución por Rol</div>
<table class="pdf-table" style="width: 50%;">
    <thead>
        <tr>
            <th>Rol</th>
            <th class="text-center" style="width: 30%;">Cantidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach($porRol as $rol => $cantidad)
        <tr>
            <td>{{ ucfirst($rol) }}</td>
            <td class="text-center fw-bold">{{ $cantidad }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- DETALLE --}}
<div class="pdf-section-title">Detalle de Usuarios ({{ $usuarios->count() }})</div>
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 12%;">Usuario</th>
            <th style="width: 22%;">Nombre Completo</th>
            <th style="width: 12%;">Cédula</th>
            <th style="width: 12%;">Rol</th>
            <th style="width: 10%;" class="text-center">Estado</th>
            <th style="width: 12%;">Último Ingreso</th>
            <th style="width: 10%;" class="text-center">Clave</th>
        </tr>
    </thead>
    <tbody>
        @foreach($usuarios as $u)
        <tr>
            <td class="fw-bold">{{ $u->usuario }}</td>
            <td>{{ $u->trabajador ? $u->trabajador->nombre . ' ' . $u->trabajador->apellido : '—' }}</td>
            <td>{{ $u->trabajador?->cedula ?? '—' }}</td>
            <td>{{ ucfirst($u->rol?->nombre ?? 'Sin rol') }}</td>
            <td class="text-center">
                <span class="pdf-badge {{ $u->status === 'activo' ? 'pdf-badge-green' : 'pdf-badge-red' }}">
                    {{ $u->status }}
                </span>
            </td>
            <td>{{ $u->ultimo_login?->format('d/m/Y H:i') ?? 'Nunca' }}</td>
            <td class="text-center">
                <span class="pdf-badge {{ $u->must_change_password ? 'pdf-badge-orange' : 'pdf-badge-green' }}">
                    {{ $u->must_change_password ? 'Pendiente' : 'OK' }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection