@extends('reportes.pdf.layout')

@section('contenido')

{{-- STATS --}}
<table class="pdf-stats">
    <tr>
        <td><span class="num">{{ $stats['total'] }}</span><span class="lbl">Total</span></td>
        <td class="stat-green"><span class="num">{{ $stats['creaciones'] }}</span><span class="lbl">Creaciones</span></td>
        <td class="stat-blue"><span class="num">{{ $stats['ediciones'] }}</span><span class="lbl">Ediciones</span></td>
        <td class="stat-red"><span class="num">{{ $stats['eliminaciones'] }}</span><span class="lbl">Eliminaciones</span></td>
        <td class="stat-purple"><span class="num">{{ $stats['logins'] }}</span><span class="lbl">Logins</span></td>
    </tr>
</table>

{{-- POR MÓDULO --}}
@if($porModulo->count() > 0)
<div class="pdf-section-title">Actividad por Módulo</div>
<table class="pdf-table" style="width: 60%;">
    <thead>
        <tr>
            <th>Módulo</th>
            <th class="text-center" style="width: 30%;">Registros</th>
        </tr>
    </thead>
    <tbody>
        @foreach($porModulo as $mod => $cantidad)
        <tr>
            <td>{{ ucfirst(str_replace('_', ' ', $mod)) }}</td>
            <td class="text-center fw-bold">{{ $cantidad }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- DETALLE --}}
<div class="pdf-section-title">Registros de Auditoría ({{ $registros->count() }})</div>
@if($registros->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 13%;">Fecha</th>
            <th style="width: 15%;">Usuario</th>
            <th style="width: 10%;">Módulo</th>
            <th style="width: 10%;">Acción</th>
            <th style="width: 32%;">Descripción</th>
            <th style="width: 15%;">IP</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $r)
        <tr>
            <td class="fw-bold">{{ $r->id }}</td>
            <td>{{ $r->created_at?->format('d/m/Y H:i') }}</td>
            <td>{{ $r->usuario_nombre ?? 'Sistema' }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $r->modulo)) }}</td>
            <td>
                @php
                    $b = match($r->accion) {
                        'crear' => 'pdf-badge-green',
                        'editar' => 'pdf-badge-blue',
                        'eliminar' => 'pdf-badge-red',
                        'login' => 'pdf-badge-purple',
                        default => 'pdf-badge-gray',
                    };
                @endphp
                <span class="pdf-badge {{ $b }}">{{ $r->accion }}</span>
            </td>
            <td><small>{{ Str::limit($r->descripcion ?? '—', 90) }}</small></td>
            <td>{{ $r->ip_address ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No se encontraron registros de auditoría.</div>
@endif

@endsection