@extends('reportes.pdf.layout')

@section('contenido')

{{-- ============ STATS ============ --}}
@if(!empty($summary))
<table class="pdf-stats">
    <tr>
        @foreach($summary as $label => $value)
            <td>
                <span class="num">{{ is_numeric($value) ? number_format($value, 0, ',', '.') : $value }}</span>
                <span class="lbl">{{ $label }}</span>
            </td>
        @endforeach
    </tr>
</table>
@endif

{{-- ============ TABLA DE PRÉSTAMOS EN PROCESO ============ --}}
<div class="pdf-section-title">
    Préstamos en Proceso ({{ $filas->count() }})
</div>

@if($filas->count() > 0)
<table class="pdf-table">
    <thead>
        <tr>
            @foreach($columnas as $col)
                @php
                    $align = $col['align'] ?? 'left';
                    $alignStyle = $align === 'center' ? 'text-align:center;' : ($align === 'right' ? 'text-align:right;' : '');
                @endphp
                <th style="width: {{ $col['width'] ?? 'auto' }}; {{ $alignStyle }}">
                    {{ $col['label'] }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($filas as $fila)
            <tr>
                @foreach($columnas as $col)
                    @php
                        $valor = data_get($fila, $col['key']);
                        $formato = $col['format'] ?? null;
                        $align = $col['align'] ?? 'left';
                        $alignClass = $align === 'center' ? 'text-center' : ($align === 'right' ? 'text-end' : '');
                    @endphp
                    <td class="{{ $alignClass }}">
                        @if($formato === 'date' && $valor)
                            {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                        @elseif($formato === 'money' && is_numeric($valor))
                            {{ number_format($valor, 2, ',', '.') }}
                        @elseif($formato === 'badge' && $valor)
                            <span class="pdf-badge pdf-badge-gray">
                                {{ ucfirst(str_replace('_', ' ', $valor)) }}
                            </span>
                        @else
                            {{ $valor ?? '-' }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">
    No hay préstamos activos en este momento.
</div>
@endif

@endsection