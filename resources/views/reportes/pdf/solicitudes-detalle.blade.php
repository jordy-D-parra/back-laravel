@extends('reportes.pdf.layout')

@section('contenido')

{{-- STATS --}}
<table class="pdf-stats">
    <tr>
        <td><span class="num">{{ $stats['total'] ?? 0 }}</span><span class="lbl">Total</span></td>
        <td class="stat-orange"><span class="num">{{ $stats['pendientes'] ?? 0 }}</span><span class="lbl">Pendientes</span></td>
        <td class="stat-green"><span class="num">{{ $stats['aprobadas'] ?? 0 }}</span><span class="lbl">Aprobadas</span></td>
        <td class="stat-red"><span class="num">{{ $stats['rechazadas'] ?? 0 }}</span><span class="lbl">Rechazadas</span></td>
        <td class="stat-gray"><span class="num">{{ $stats['canceladas'] ?? 0 }}</span><span class="lbl">Canceladas</span></td>
        <td class="stat-purple"><span class="num">{{ $stats['urgentes'] ?? 0 }}</span><span class="lbl">Urgentes</span></td>
    </tr>
</table>

{{-- TIEMPO PROMEDIO --}}
@if(!empty($promedioDias) && $promedioDias > 0)
<div class="pdf-filtros" style="border-left-color: #10b981; background: #ecfdf5;">
    <strong>⏱️ Tiempo promedio de aprobación:</strong> {{ $promedioDias }} días
</div>
@endif

{{-- DETALLE --}}
<div class="pdf-section-title">Detalle de Solicitudes ({{ $solicitudes->count() }})</div>

@if($solicitudes->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 6%;">#</th>
            <th style="width: 10%;">Fecha</th>
            <th style="width: 20%;">Entidad</th>
            <th style="width: 15%;">Responsable</th>
            <th style="width: 10%;" class="text-center">Prioridad</th>
            <th style="width: 10%;" class="text-center">Estado</th>
            <th style="width: 8%;" class="text-center">Items</th>
            <th style="width: 11%;">F. Requerida</th>
            <th style="width: 10%;">F. Aprobación</th>
        </tr>
    </thead>
    <tbody>
        @foreach($solicitudes as $s)
        @php
            $pBadge = match($s->prioridad) {
                'urgente' => 'pdf-badge-red',
                'alta' => 'pdf-badge-orange',
                'normal' => 'pdf-badge-green',
                'baja' => 'pdf-badge-gray',
                default => 'pdf-badge-gray',
            };
            $eBadge = match($s->estado_solicitud) {
                'aprobada' => 'pdf-badge-green',
                'pendiente' => 'pdf-badge-orange',
                'rechazada' => 'pdf-badge-red',
                'cancelada' => 'pdf-badge-gray',
                default => 'pdf-badge-gray',
            };
            $entidad = $s->departamento?->nombre ?? $s->institucion?->nombre ?? '—';
        @endphp
        <tr>
            <td class="fw-bold">{{ $s->id }}</td>
            <td>{{ $s->fecha_solicitud?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $entidad }}</td>
            <td>{{ $s->responsable?->nombre ?? '—' }}</td>
            <td class="text-center"><span class="pdf-badge {{ $pBadge }}">{{ $s->prioridad }}</span></td>
            <td class="text-center"><span class="pdf-badge {{ $eBadge }}">{{ $s->estado_solicitud }}</span></td>
            <td class="text-center">{{ $s->detalles->count() }}</td>
            <td>{{ $s->fecha_requerida?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $s->fecha_aprobacion?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No se encontraron solicitudes con los filtros seleccionados.</div>
@endif

@endsection