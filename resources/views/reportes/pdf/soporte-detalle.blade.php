@extends('reportes.pdf.layout')

@section('contenido')

{{-- STATS --}}
<table class="pdf-stats">
    <tr>
        <td><span class="num">{{ $stats['total'] ?? 0 }}</span><span class="lbl">Total</span></td>
        <td class="stat-orange"><span class="num">{{ $stats['en_proceso'] ?? 0 }}</span><span class="lbl">En Proceso</span></td>
        <td class="stat-green"><span class="num">{{ $stats['finalizados'] ?? 0 }}</span><span class="lbl">Finalizados</span></td>
        <td class="stat-red"><span class="num">{{ $stats['vencidas'] ?? 0 }}</span><span class="lbl">Vencidas</span></td>
        <td class="stat-blue"><span class="num">{{ $stats['con_tecnico'] ?? 0 }}</span><span class="lbl">Con Técnico</span></td>
    </tr>
</table>

{{-- PROMEDIO --}}
@if(!empty($promedioDias) && $promedioDias > 0)
<div class="pdf-filtros" style="border-left-color: #10b981; background: #ecfdf5;">
    <strong>⏱️ Tiempo promedio de reparación:</strong> {{ $promedioDias }} días
</div>
@endif

{{-- POR TÉCNICO --}}
@if(isset($porTecnico) && $porTecnico->count() > 0)
<div class="pdf-section-title">Productividad por Técnico</div>
<table class="pdf-table" style="width: 70%;">
    <thead>
        <tr>
            <th>Técnico</th>
            <th class="text-center" style="width: 15%;">Total</th>
            <th class="text-center" style="width: 15%;">Finalizadas</th>
            <th class="text-center" style="width: 15%;">En Proceso</th>
        </tr>
    </thead>
    <tbody>
        @foreach($porTecnico as $tecnico => $dataTec)
        <tr>
            <td>{{ $tecnico }}</td>
            <td class="text-center fw-bold">{{ $dataTec['total'] ?? 0 }}</td>
            <td class="text-center"><span class="pdf-badge pdf-badge-green">{{ $dataTec['finalizadas'] ?? 0 }}</span></td>
            <td class="text-center"><span class="pdf-badge pdf-badge-orange">{{ $dataTec['en_proceso'] ?? 0 }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- DETALLE --}}
<div class="pdf-section-title">Detalle de Fichas ({{ $fichas->count() }})</div>

@if($fichas->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 6%;">#</th>
            <th style="width: 15%;">Activo</th>
            <th style="width: 15%;">Técnico</th>
            <th style="width: 12%;">Ingreso</th>
            <th style="width: 12%;">Salida</th>
            <th style="width: 10%;" class="text-center">Estado</th>
            <th style="width: 10%;" class="text-center">Días</th>
            <th style="width: 20%;">Diagnóstico</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fichas as $f)
        @php
            $badge = match($f->estado) {
                'finalizado' => 'pdf-badge-green',
                'en_proceso' => 'pdf-badge-orange',
                'aceptada' => 'pdf-badge-blue',
                'rechazada' => 'pdf-badge-red',
                default => 'pdf-badge-gray',
            };

            if ($f->fecha_salida && $f->fecha_ingreso) {
                $dias = $f->fecha_ingreso->diffInDays($f->fecha_salida);
            } elseif ($f->fecha_ingreso) {
                $dias = $f->fecha_ingreso->diffInDays(now());
            } else {
                $dias = 0;
            }

            $estadoLabel = match($f->estado) {
                'en_proceso' => 'En Proceso',
                'aceptada'   => 'Aceptada',
                'rechazada'  => 'Rechazada',
                'finalizado' => 'Finalizado',
                'cancelada'  => 'Cancelada',
                default      => ucfirst($f->estado),
            };
        @endphp
        <tr>
            <td class="fw-bold">{{ $f->id }}</td>
            <td>{{ $f->activo?->serial ?? 'Externo' }}</td>
            <td>{{ $f->tecnico_nombre ?? '—' }}</td>
            <td>{{ $f->fecha_ingreso?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $f->fecha_salida?->format('d/m/Y') ?? '—' }}</td>
            <td class="text-center"><span class="pdf-badge {{ $badge }}">{{ $estadoLabel }}</span></td>
            <td class="text-center">{{ $dias }}</td>
            <td><small>{{ Str::limit($f->diagnostico ?? '—', 60) }}</small></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No se encontraron fichas con los filtros seleccionados.</div>
@endif

@endsection