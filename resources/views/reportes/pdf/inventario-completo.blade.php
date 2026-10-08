@extends('reportes.pdf.layout')

@section('contenido')

{{-- STATS --}}
<table class="pdf-stats">
    <tr>
        <td><span class="num">{{ $stats['total_activos'] }}</span><span class="lbl">Activos</span></td>
        <td class="stat-blue"><span class="num">{{ $stats['total_componentes'] }}</span><span class="lbl">Componentes</span></td>
        <td class="stat-green"><span class="num">{{ $stats['disponibles'] }}</span><span class="lbl">Disponibles</span></td>
        <td class="stat-orange"><span class="num">{{ $stats['prestados'] }}</span><span class="lbl">Prestados</span></td>
        <td class="stat-red"><span class="num">{{ $stats['reparacion'] }}</span><span class="lbl">Reparación</span></td>
    </tr>
</table>

{{-- POR CATEGORÍA --}}
@if($porCategoria->count() > 0)
<div class="pdf-section-title">Resumen por Categoría</div>
<table class="pdf-table" style="width: 60%;">
    <thead>
        <tr>
            <th>Categoría</th>
            <th class="text-center" style="width: 25%;">Cantidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach($porCategoria as $cat => $cantidad)
        <tr>
            <td>{{ $cat }}</td>
            <td class="text-center fw-bold">{{ $cantidad }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ACTIVOS CON COMPONENTES --}}
<div class="pdf-section-title">Activos y sus Componentes ({{ $activos->count() }})</div>

@if($activos->count() > 0)
@foreach($activos as $activo)
    @php
        $estadoBadge = match($activo->estatus?->descripcion) {
            'Disponible' => 'pdf-badge-green',
            'Prestado' => 'pdf-badge-orange',
            'En reparación' => 'pdf-badge-red',
            'En bodega' => 'pdf-badge-gray',
            'Desechado' => 'pdf-badge-red',
            default => 'pdf-badge-gray',
        };
    @endphp

    <table class="pdf-table" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th colspan="2" style="background: #eef2f8; color: #1e3c72; font-size: 8pt; padding: 4px 8px;">
                    🔧 {{ $activo->serial }} — {{ $activo->modelo?->marca?->nombre }} {{ $activo->modelo?->nombre }}
                </th>
                <th colspan="2" style="background: #eef2f8; color: #1e3c72; font-size: 8pt; padding: 4px 8px; text-align: right;">
                    <span class="pdf-badge {{ $estadoBadge }}">{{ $activo->estatus?->descripcion ?? 'N/A' }}</span>
                </th>
            </tr>
            <tr>
                <th style="width: 25%;">Categoría</th>
                <th style="width: 25%;">Ubicación</th>
                <th style="width: 25%;">Institución</th>
                <th style="width: 25%;">Responsable</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $activo->modelo?->categoria?->nombre ?? '—' }}</td>
                <td>{{ $activo->ubicacion ?? '—' }}</td>
                <td>{{ $activo->institucion?->nombre ?? '—' }}</td>
                <td>{{ $activo->responsable?->nombre ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    @if($activo->componentes->count() > 0)
    <table class="pdf-table" style="margin-left: 10px; width: calc(100% - 10px); margin-bottom: 12px;">
        <thead>
            <tr style="background: #f1f5f9;">
                <th style="width: 20%; background: #64748b; border-color: #64748b;">Componente</th>
                <th style="width: 15%; background: #64748b; border-color: #64748b;">Marca</th>
                <th style="width: 15%; background: #64748b; border-color: #64748b;">Modelo</th>
                <th style="width: 15%; background: #64748b; border-color: #64748b;">Serial</th>
                <th style="width: 12%; background: #64748b; border-color: #64748b;">Capacidad</th>
                <th style="width: 12%; background: #64748b; border-color: #64748b;">Estado</th>
                <th style="width: 11%; background: #64748b; border-color: #64748b;">Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activo->componentes as $comp)
            <tr>
                <td class="fw-bold">{{ $comp->tipo }}</td>
                <td>{{ $comp->marca ?? '—' }}</td>
                <td>{{ $comp->modelo ?? '—' }}</td>
                <td>{{ $comp->serial ?? '—' }}</td>
                <td>{{ $comp->capacidad ?? '—' }}</td>
                <td>
                    @php
                        $cBadge = match($comp->estado) {
                            'instalado' => 'pdf-badge-blue',
                            'en_bodega' => 'pdf-badge-gray',
                            'prestado' => 'pdf-badge-orange',
                            'en_reparacion' => 'pdf-badge-red',
                            default => 'pdf-badge-gray',
                        };
                    @endphp
                    <span class="pdf-badge {{ $cBadge }}">{{ ucfirst(str_replace('_', ' ', $comp->estado)) }}</span>
                </td>
                <td>{{ $comp->ubicacion ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="margin-left: 10px; font-size: 7pt; color: #94a3b8; font-style: italic; margin-bottom: 12px;">
        Sin componentes registrados.
    </div>
    @endif
@endforeach
@else
<div class="pdf-empty">No se encontraron activos con los filtros seleccionados.</div>
@endif

@endsection