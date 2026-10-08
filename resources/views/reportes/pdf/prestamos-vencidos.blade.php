@extends('reportes.pdf.layout')

@section('contenido')

{{-- VENCIDOS --}}
<div class="pdf-section-title" style="color: #991b1b;">
    ⚠️ Préstamos Vencidos ({{ $vencidos->count() }})
</div>

@if($vencidos->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 10%;">Código</th>
            <th style="width: 20%;">Destino</th>
            <th style="width: 20%;">Responsable</th>
            <th style="width: 12%;">F. Devolución</th>
            <th style="width: 10%;" class="text-center">Días Vencidos</th>
            <th style="width: 15%;">Contacto</th>
            <th style="width: 13%;" class="text-center">Items</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vencidos as $p)
        <tr>
            <td class="fw-bold">{{ $p->codigo }}</td>
            <td>{{ $p->destino_nombre }}</td>
            <td>{{ $p->responsableReceptor?->nombre ?? '—' }}</td>
            <td>{{ $p->fecha_devolucion_esperada?->format('d/m/Y') }}</td>
            <td class="text-center">
                <span class="pdf-badge pdf-badge-red">
                    {{ now()->startOfDay()->diffInDays($p->fecha_devolucion_esperada) }} días
                </span>
            </td>
            <td>
                @if($p->responsableReceptor?->telefono)
                    📞 {{ $p->responsableReceptor->telefono }}<br>
                @endif
                @if($p->responsableReceptor?->email)
                    ✉️ {{ $p->responsableReceptor->email }}
                @endif
            </td>
            <td class="text-center">{{ $p->detalles->count() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">✅ No hay préstamos vencidos actualmente.</div>
@endif

{{-- POR VENCER --}}
<div class="pdf-section-title" style="color: #92400e; margin-top: 15px;">
    ⏰ Préstamos Por Vencer (próximos 7 días) ({{ $porVencer->count() }})
</div>

@if($porVencer->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            <th style="width: 10%;">Código</th>
            <th style="width: 20%;">Destino</th>
            <th style="width: 20%;">Responsable</th>
            <th style="width: 12%;">F. Devolución</th>
            <th style="width: 10%;" class="text-center">Días Restantes</th>
            <th style="width: 15%;">Contacto</th>
            <th style="width: 13%;" class="text-center">Items</th>
        </tr>
    </thead>
    <tbody>
        @foreach($porVencer as $p)
        <tr>
            <td class="fw-bold">{{ $p->codigo }}</td>
            <td>{{ $p->destino_nombre }}</td>
            <td>{{ $p->responsableReceptor?->nombre ?? '—' }}</td>
            <td>{{ $p->fecha_devolucion_esperada?->format('d/m/Y') }}</td>
            <td class="text-center">
                <span class="pdf-badge pdf-badge-orange">
                    {{ max(0, now()->startOfDay()->diffInDays($p->fecha_devolucion_esperada, false)) }} días
                </span>
            </td>
            <td>
                @if($p->responsableReceptor?->telefono)
                    📞 {{ $p->responsableReceptor->telefono }}<br>
                @endif
                @if($p->responsableReceptor?->email)
                    ✉️ {{ $p->responsableReceptor->email }}
                @endif
            </td>
            <td class="text-center">{{ $p->detalles->count() }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">✅ No hay préstamos por vencer en los próximos 7 días.</div>
@endif

{{-- FIRMAS --}}
@if($vencidos->count() > 0 || $porVencer->count() > 0)
<table class="pdf-firmas">
    <tr>
        <td>
            <div class="linea"></div>
            <div class="nombre">Responsable de Informática</div>
            <div class="cargo">Dirección de Informática</div>
            <div class="rol">Revisado por</div>
        </td>
        <td>
            <div class="linea"></div>
            <div class="nombre">Supervisor</div>
            <div class="cargo">Gobernación del Estado Yaracuy</div>
            <div class="rol">Aprobado por</div>
        </td>
    </tr>
</table>
@endif

@endsection