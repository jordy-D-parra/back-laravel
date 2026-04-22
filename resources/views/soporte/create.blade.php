@extends('layouts.dashboard')

@section('title', 'Nueva Ficha de Soporte')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">

    <div style="margin-bottom: 20px;">
        <a href="{{ route('soporte.index') }}" style="color: #4361ee; text-decoration: none;">← Volver al listado</a>
    </div>

    <div style="background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); padding: 24px 30px;">
            <h2 style="margin: 0; color: white;">🔧 Nueva Ficha de Soporte</h2>
            <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.8);">Registrar equipo para mantenimiento</p>
        </div>

        <div style="padding: 30px;">
            <form action="{{ route('soporte.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Equipo *</label>
                    <select name="activo_id" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;">
                        <option value="">Seleccione un equipo...</option>
                        @foreach($activos as $activo)
                            <option value="{{ $activo->id }}">{{ $activo->serial }} - {{ $activo->marca_modelo }} (Stock: {{ $activo->cantidad }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Técnico Asignado</label>
                    <select name="tecnico_id" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;">
                        <option value="">Sin asignar</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}">{{ $tecnico->name }} - {{ $tecnico->email }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Diagnóstico / Descripción del problema *</label>
                    <textarea name="diagnostico" rows="4" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;" placeholder="Describa el problema del equipo..."></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Observaciones adicionales</label>
                    <textarea name="observaciones" rows="3" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;" placeholder="Información adicional..."></textarea>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <a href="{{ route('soporte.index') }}" style="padding: 12px 24px; background: #e9ecef; color: #495057; text-decoration: none; border-radius: 12px;">Cancelar</a>
                    <button type="submit" style="padding: 12px 32px; background: #4361ee; color: white; border: none; border-radius: 12px; cursor: pointer;">📋 Crear Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
