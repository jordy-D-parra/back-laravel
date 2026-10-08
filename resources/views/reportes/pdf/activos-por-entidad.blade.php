@extends('reportes.pdf.layout')

@section('contenido')

{{-- STATS --}}
<table class="pdf-stats">
    <tr>
        <td><span class="num">{{ $stats['total_instituciones'] }}</span><span class="lbl">Instituciones</span></td>
        <td class="stat-blue"><span class="num">{{ $stats['total_departamentos'] }}</span><span class="lbl">Departamentos</span></td>
        <td class="stat-green"><span class="num">{{ $stats['total_activos'] }}</span><span class="lbl">Total Activos</span></td>
    </tr>
</table>

@foreach($instituciones as $inst)
<div class="pdf-section-title" style="background: #eef2f8; padding: 5px 8px; border-left: 4px solid #1e3c72; border-radius: 4px;">
    🏢 {{ $inst->nombre }}
    <span style="float: right; font-size: 7pt; font-weight: normal;">
        {{ $inst->departamentos->count() }} departamentos
    </span>
</div>

@if($inst->departamentos->count() > 0)
@foreach($inst->departamentos as $depto)
<div style="font-weight: bold; font-size: 8pt; color: #475569; margin: 6px 0 3px 10px;">
    📁 {{ $depto->nombre }}
    <span style="font-weight: normal; font-size: 7pt; color: #94a3b8;">
        ({{ $depto->activos->count() }} activos)
    </span>
</div>

@if($depto->activos->count() > 0)
<table class="pdf-table" style="margin-left: 10px; width: calc(100% - 10px);">
    <thead>
        <tr>
            <th style="width: 15%;">Serial</th>
            <th style="width: 20%;">Marca / Modelo</th>
            <th style="width: 15%;">Categoría</th>
            <th style="width: 12%;">Estado</th>
            <th style="width: 15%;">Ubicación</th>
            <th style="width: 23%;">Responsable</th>
        </tr>
    </thead>
    <tbody>
        @foreach($depto->activos as $activo)
        <tr>
            <td class="fw-bold">{{ $activo->serial }}</td>
            <td>{{ $activo->modelo?->marca?->nombre }} {{ $activo->modelo?->nombre }}</td>
            <td>{{ $activo->modelo?->categoria?->nombre ?? '—' }}</td>
            <td>
                @php
                    $b = match($activo->estatus?->descripcion) {
                        'Disponible' => 'pdf-badge-green',
                        'Prestado' => 'pdf-badge-orange',
                        'En reparación' => 'pdf-badge-red',
                        default => 'pdf-badge-gray',
                    };
                @endphp
                <span class="pdf-badge {{ $b }}">{{ $activo->estatus?->descripcion ?? 'N/A' }}</span>
            </td>
            <td>{{ $activo->ubicacion ?? '—' }}</td>
            <td>{{ $activo->responsable?->nombre ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div style="margin-left: 20px; font-size: 7pt; color: #94a3b8; font-style: italic; margin-bottom: 8px;">
    Sin activos asignados.
</div>
@endif
@endforeach
@else
<div style="margin-left: 10px; font-size: 7pt; color: #94a3b8; font-style: italic; margin-bottom: 8px;">
    Sin departamentos registrados.
</div>
@endif
@endforeach

@endsection