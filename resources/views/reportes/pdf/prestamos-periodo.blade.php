@extends('reportes.pdf.layout')

@section('contenido')

{{-- STATS --}}
@if($stats['total'] > 0)
<table class="pdf-stats">
    <tr>
        <td><span class="num">{{ $stats['total'] }}</span><span class="lbl">Total</span></td>
        <td class="stat-blue"><span class="num">{{ $stats['activos'] }}</span><span class="lbl">Activos</span></td>
        <td class="stat-red"><span class="num">{{ $stats['vencidos'] }}</span><span class="lbl">Vencidos</span></td>
        <td class="stat-green"><span class="num">{{ $stats['devueltos'] }}</span><span class="lbl">Devueltos</span></td>
        <td class="stat-orange"><span class="num">{{ $stats['pendientes'] }}</span><span class="lbl">Pendientes</span></td>
    </tr>
</table>
@endif

{{-- TOP DESTINOS --}}
@if($topDestinos->count() > 0)
<div class="pdf-section-title">Top Destinos con Más Préstamos</div>
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 60%;">Destino</th>
            <th style="width: 15%;" class="text-center">Préstamos</th>
            <th style="width: 20%;" class="text-center">% del Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($topDestinos as $destino => $cantidad)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $destino }}</td>
            <td class="text-center fw-bold">{{ $cantidad }}</td>
            <td class="text-center">
                {{ $stats['total'] > 0 ? round(($cantidad / $stats['total']) * 100, 1) : 0 }}%
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- DETALLE --}}
<div class="pdf-section-title">Detalle de Préstamos ({{ $prestamos->count() }})</div>

@if($prestamos->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 10%;">Código</th>
            <th style="width: 15%;">Fecha Préstamo</th>
            <th style="width: 20%;">Destino</th>
            <th style="width: 15%;">Responsable</th>
            <th style="width: 12%;">F. Devolución</th>
            <th style="width: 10%;" class="text-center">Items</th>
            <th style="width: 10%;" class="text-center">Estado</th>
            <th style="width: 8%;" class="text-center">Días</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prestamos as $p)
        @php
            $badgeClass = match($p->estado) {
                'aprobado' => 'pdf-badge-blue',
                'entregado' => 'pdf-badge-blue',
                'extendido' => 'pdf-badge-purple',
                'devuelto' => 'pdf-badge-green',
                'pendiente' => 'pdf-badge-orange',
                'cancelado', 'rechazado' => 'pdf-badge-gray',
                default => 'pdf-badge-gray',
            };
            if ($p->esta_vencido) $badgeClass = 'pdf-badge-red';
        @endphp
        <tr>
            <td class="fw-bold">{{ $p->codigo }}</td>
            <td>{{ $p->fecha_prestamo?->format('d/m/Y') }}</td>
            <td>{{ $p->destino_nombre }}</td>
            <td>{{ $p->responsableReceptor?->nombre ?? '—' }}</td>
            <td>{{ $p->fecha_devolucion_esperada?->format('d/m/Y') }}</td>
            <td class="text-center">{{ $p->detalles->count() }}</td>
            <td class="text-center">
                <span class="pdf-badge {{ $badgeClass }}">
                    {{ $p->esta_vencido ? 'Vencido' : ucfirst($p->estado) }}
                </span>
            </td>
            <td class="text-center">
                @if($p->fecha_devolucion_real)
                    —
                @else
                    {{ $p->dias_restantes }}
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No se encontraron préstamos en el período seleccionado.</div>
@endif

@endsection