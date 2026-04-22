@extends('layouts.dashboard')

@section('title', 'Ficha de Soporte #' . $ficha->id)

@section('content')
<div style="max-width: 1000px; margin: 0 auto; padding: 20px;">

    <div style="margin-bottom: 20px;">
        <a href="{{ route('soporte.index') }}" style="color: #4361ee; text-decoration: none;">← Volver al listado</a>
    </div>

    {{-- Header de la ficha --}}
    <div style="background: white; border-radius: 16px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap;">
            <div>
                <h2 style="margin: 0;">🔧 Ficha de Soporte #{{ $ficha->id }}</h2>
                <p style="color: #6c757d; margin: 5px 0 0;">
                    {{ $ficha->fecha_ingreso->format('d/m/Y H:i') }} -
                    Reportado por: {{ $ficha->usuarioReporta->name }}
                </p>
            </div>
            <div>
                @php
                    $estadoColors = [
                        'pendiente' => '#ffc107',
                        'en_proceso' => '#4361ee',
                        'completado' => '#2e7d32'
                    ];
                    $estadoTextos = [
                        'pendiente' => '⏳ Pendiente',
                        'en_proceso' => '🔧 En Proceso',
                        'completado' => '✅ Completado'
                    ];
                @endphp
                <span style="background: {{ $estadoColors[$ficha->estado] ?? '#6c757d' }}20; color: {{ $estadoColors[$ficha->estado] ?? '#6c757d' }}; padding: 8px 16px; border-radius: 20px; font-weight: bold;">
                    {{ $estadoTextos[$ficha->estado] ?? $ficha->estado }}
                </span>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        {{-- Información del equipo --}}
        <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <h3 style="margin: 0 0 15px 0;">📟 Información del Equipo</h3>
            @if($ficha->activo)
                <p><strong>Serial:</strong> {{ $ficha->activo->serial }}</p>
                <p><strong>Marca/Modelo:</strong> {{ $ficha->activo->marca_modelo }}</p>
                <p><strong>Ubicación:</strong> {{ $ficha->activo->ubicacion ?? 'No especificada' }}</p>
                <p><strong>Estado actual:</strong> {{ $ficha->activo->estatus->nombre ?? 'Desconocido' }}</p>
            @else
                <p style="color: #6c757d;">Equipo externo (no registrado en inventario)</p>
                <p><strong>Información:</strong> {{ $ficha->observaciones }}</p>
            @endif
        </div>

        {{-- Asignación técnica --}}
        <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <h3 style="margin: 0 0 15px 0;">👨‍🔧 Asignación Técnica</h3>
            @if($ficha->tecnico)
                <p><strong>Técnico:</strong> {{ $ficha->tecnico->name }}</p>
                <p><strong>Email:</strong> {{ $ficha->tecnico->email }}</p>
            @else
                <p style="color: #6c757d;">Sin técnico asignado</p>
                <form action="{{ route('soporte.asignarTecnico', $ficha) }}" method="POST" style="margin-top: 15px;">
                    @csrf
                    <select name="tecnico_id" required style="padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                        <option value="">Seleccionar técnico...</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" style="padding: 10px 20px; background: #4361ee; color: white; border: none; border-radius: 8px; cursor: pointer;">Asignar</button>
                </form>
            @endif
        </div>

        {{-- Diagnóstico --}}
        <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <h3 style="margin: 0 0 15px 0;">📝 Diagnóstico</h3>
            <p style="white-space: pre-wrap;">{{ $ficha->diagnostico ?? 'No registrado' }}</p>
        </div>

        {{-- Trabajo realizado --}}
        <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <h3 style="margin: 0 0 15px 0;">⚙️ Trabajo Realizado</h3>
            @if($ficha->estado == 'completado')
                <p style="white-space: pre-wrap;">{{ $ficha->trabajo_realizado ?? 'No registrado' }}</p>
            @else
                <form action="{{ route('soporte.actualizarTrabajo', $ficha) }}" method="POST">
                    @csrf
                    <textarea name="trabajo_realizado" rows="4" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;" placeholder="Describa el trabajo realizado...">{{ $ficha->trabajo_realizado }}</textarea>
                    <textarea name="observaciones" rows="2" style="width: 100%; margin-top: 10px; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;" placeholder="Observaciones...">{{ $ficha->observaciones }}</textarea>
                    <button type="submit" style="margin-top: 10px; padding: 10px 20px; background: #2a9d8f; color: white; border: none; border-radius: 8px; cursor: pointer;">Actualizar</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Componentes afectados --}}
    <div style="background: white; border-radius: 16px; margin-top: 20px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 15px 0;">🖥️ Componentes Afectados</h3>

        @if($ficha->detalles->count() > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 10px; text-align: left;">Componente</th>
                        <th style="padding: 10px; text-align: left;">Estado Ingreso</th>
                        <th style="padding: 10px; text-align: left;">Estado Salida</th>
                        <th style="padding: 10px; text-align: left;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ficha->detalles as $detalle)
                    <tr style="border-bottom: 1px solid #e9ecef;">
                        <td style="padding: 10px;">{{ $detalle->componente->tipo_componente }} - {{ $detalle->componente->marca }} {{ $detalle->componente->modelo }}</td>
                        <td style="padding: 10px;">{{ $detalle->estado_ingreso ?? '-' }}</td>
                        <td style="padding: 10px;">{{ $detalle->estado_salida ?? '-' }}</td>
                        <td style="padding: 10px;">{{ $detalle->observaciones ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #6c757d;">No hay componentes registrados</p>
        @endif
    </div>

    {{-- Botones de acción --}}
    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
        @if($ficha->estado != 'completado')
            <form action="{{ route('soporte.completar', $ficha) }}" method="POST" onsubmit="return confirm('¿Marcar esta ficha como completada?')">
                @csrf
                <button type="submit" style="padding: 12px 24px; background: #2e7d32; color: white; border: none; border-radius: 8px; cursor: pointer;">✅ Marcar como Completado</button>
            </form>
        @endif
    </div>
</div>
@endsection
