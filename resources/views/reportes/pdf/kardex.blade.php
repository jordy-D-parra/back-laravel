@extends('reportes.pdf.layout')

@section('contenido')

{{-- INFO DEL ACTIVO --}}
<table class="pdf-table">
    <thead>
        <tr>
            <th colspan="4" style="background: #1e3c72; font-size: 9pt; padding: 6px 10px;">
                🔧 {{ $activo->serial }} — {{ $activo->modelo?->marca?->nombre }} {{ $activo->modelo?->nombre }}
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="width: 25%;"><strong>Categoría:</strong><br>{{ $activo->modelo?->categoria?->nombre ?? '—' }}</td>
            <td style="width: 25%;"><strong>Estado:</strong><br>{{ $activo->estatus?->descripcion ?? '—' }}</td>
            <td style="width: 25%;"><strong>Ubicación:</strong><br>{{ $activo->ubicacion ?? '—' }}</td>
            <td style="width: 25%;"><strong>Institución:</strong><br>{{ $activo->institucion?->nombre ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>Departamento:</strong><br>{{ $activo->departamento?->nombre ?? '—' }}</td>
            <td><strong>Responsable:</strong><br>{{ $activo->responsable?->nombre ?? '—' }}</td>
            <td><strong>Fecha Adquisición:</strong><br>{{ $activo->fecha_adquisicion?->format('d/m/Y') ?? '—' }}</td>
            <td><strong>Fin Garantía:</strong><br>{{ $activo->fecha_fin_garantia?->format('d/m/Y') ?? '—' }}</td>
        </tr>
    </tbody>
</table>

{{-- COMPONENTES --}}
<div class="pdf-section-title">Componentes ({{ $activo->componentes->count() }})</div>
@if($activo->componentes->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 20%;">Tipo</th>
            <th style="width: 15%;">Marca</th>
            <th style="width: 20%;">Modelo</th>
            <th style="width: 15%;">Serial</th>
            <th style="width: 15%;">Capacidad</th>
            <th style="width: 15%;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activo->componentes as $c)
        <tr>
            <td class="fw-bold">{{ $c->tipo }}</td>
            <td>{{ $c->marca ?? '—' }}</td>
            <td>{{ $c->modelo ?? '—' }}</td>
            <td>{{ $c->serial ?? '—' }}</td>
            <td>{{ $c->capacidad ?? '—' }}</td>
            <td><span class="pdf-badge pdf-badge-gray">{{ ucfirst(str_replace('_', ' ', $c->estado)) }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">Sin componentes registrados.</div>
@endif

{{-- HISTORIAL DE PRÉSTAMOS --}}
<div class="pdf-section-title">Historial de Préstamos ({{ $prestamos->count() }})</div>
@if($prestamos->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 12%;">Código</th>
            <th style="width: 12%;">F. Préstamo</th>
            <th style="width: 12%;">F. Dev. Esperada</th>
            <th style="width: 12%;">F. Dev. Real</th>
            <th style="width: 18%;">Destino</th>
            <th style="width: 18%;">Responsable</th>
            <th style="width: 10%;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prestamos as $p)
        <tr>
            <td class="fw-bold">{{ $p->codigo }}</td>
            <td>{{ $p->fecha_prestamo?->format('d/m/Y') }}</td>
            <td>{{ $p->fecha_devolucion_esperada?->format('d/m/Y') }}</td>
            <td>{{ $p->fecha_devolucion_real?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $p->destino_nombre }}</td>
            <td>{{ $p->responsableReceptor?->nombre ?? '—' }}</td>
            <td><span class="pdf-badge pdf-badge-blue">{{ $p->estado }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">Sin historial de préstamos.</div>
@endif

{{-- HISTORIAL DE SOPORTE --}}
<div class="pdf-section-title">Historial de Soporte Técnico ({{ $fichas->count() }})</div>
@if($fichas->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 8%;">#</th>
            <th style="width: 15%;">F. Ingreso</th>
            <th style="width: 15%;">F. Salida</th>
            <th style="width: 20%;">Técnico</th>
            <th style="width: 12%;">Estado</th>
            <th style="width: 30%;">Diagnóstico</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fichas as $f)
        <tr>
            <td class="fw-bold">{{ $f->id }}</td>
            <td>{{ $f->fecha_ingreso?->format('d/m/Y') }}</td>
            <td>{{ $f->fecha_salida?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $f->tecnico_nombre ?? '—' }}</td>
            <td>
                @php
                    $b = match($f->estado) {
                        'finalizado' => 'pdf-badge-green',
                        'en_proceso' => 'pdf-badge-orange',
                        default => 'pdf-badge-gray',
                    };
                @endphp
                <span class="pdf-badge {{ $b }}">{{ $f->estado_label }}</span>
            </td>
            <td><small>{{ Str::limit($f->diagnostico ?? '—', 80) }}</small></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">Sin historial de soporte técnico.</div>
@endif

@endsection