@extends('layouts.dashboard')

@section('title', 'Soporte Técnico')

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">

    {{-- Cabecera --}}
    <div style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="margin: 0; font-size: 28px; font-weight: 300;">🔧 Soporte Técnico</h1>
                <p style="margin: 8px 0 0 0; color: #6c757d;">Gestión de mantenimiento y reparación de equipos</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('soporte.externo') }}" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-weight: 500;">
                    📱 Equipo Externo
                </a>
                <a href="{{ route('soporte.create') }}" style="padding: 10px 20px; background: #4361ee; color: white; text-decoration: none; border-radius: 8px; font-weight: 500;">
                    + Nueva Ficha
                </a>
            </div>
        </div>
    </div>

    {{-- Tarjetas de estadísticas --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="font-size: 32px;">⏳</div>
            <div style="font-size: 24px; font-weight: bold; margin: 10px 0;">{{ $estadisticas['pendientes'] ?? 0 }}</div>
            <div style="color: #6c757d;">Pendientes</div>
        </div>
        <div style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="font-size: 32px;">🔍</div>
            <div style="font-size: 24px; font-weight: bold; margin: 10px 0;">{{ $estadisticas['en_proceso'] ?? 0 }}</div>
            <div style="color: #6c757d;">En Proceso</div>
        </div>
        <div style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="font-size: 32px;">✅</div>
            <div style="font-size: 24px; font-weight: bold; margin: 10px 0;">{{ $estadisticas['completados'] ?? 0 }}</div>
            <div style="color: #6c757d;">Completados</div>
        </div>
        <div style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="font-size: 32px;">📅</div>
            <div style="font-size: 24px; font-weight: bold; margin: 10px 0;">{{ $estadisticas['total_mes'] ?? 0 }}</div>
            <div style="color: #6c757d;">Este Mes</div>
        </div>
    </div>

    {{-- Lista de fichas de soporte --}}
    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <div style="padding: 20px; border-bottom: 1px solid #e9ecef; background: #f8f9fa;">
            <h3 style="margin: 0;">📋 Fichas de Soporte</h3>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: left;">ID</th>
                        <th style="padding: 12px; text-align: left;">Equipo</th>
                        <th style="padding: 12px; text-align: left;">Técnico</th>
                        <th style="padding: 12px; text-align: left;">Fecha Ingreso</th>
                        <th style="padding: 12px; text-align: left;">Estado</th>
                        <th style="padding: 12px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fichas as $ficha)
                    <tr style="border-bottom: 1px solid #e9ecef;">
                        <td style="padding: 12px;">#{{ $ficha->id }}</td>
                        <td style="padding: 12px;">
                            @if($ficha->activo)
                                {{ $ficha->activo->serial }} - {{ $ficha->activo->marca_modelo }}
                            @else
                                <span style="color: #6c757d;">Equipo Externo</span>
                            @endif
                        </td>
                        <td style="padding: 12px;">{{ $ficha->tecnico->name ?? 'No asignado' }}</td>
                        <td style="padding: 12px;">{{ $ficha->fecha_ingreso->format('d/m/Y') }}</td>
                        <td style="padding: 12px;">
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
                            <span style="background: {{ $estadoColors[$ficha->estado] ?? '#6c757d' }}20; color: {{ $estadoColors[$ficha->estado] ?? '#6c757d' }}; padding: 4px 10px; border-radius: 20px; font-size: 12px;">
                                {{ $estadoTextos[$ficha->estado] ?? $ficha->estado }}
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="{{ route('soporte.show', $ficha) }}" style="background: #17a2b8; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px;">
                                👁️ Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #6c757d;">
                            <div style="font-size: 48px;">📭</div>
                            <p>No hay fichas de soporte registradas</p>
                            <a href="{{ route('soporte.create') }}" style="color: #4361ee;">Crear primera ficha</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 20px; border-top: 1px solid #e9ecef;">
            {{ $fichas->links() }}
        </div>
    </div>
</div>
@endsection
