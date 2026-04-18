@extends('layouts.dashboard')

@section('title', 'Soporte para Equipo Externo')

@section('content')
<div style="max-width: 700px; margin: 0 auto; padding: 20px;">

    <div style="margin-bottom: 20px;">
        <a href="{{ route('soporte.index') }}" style="color: #4361ee; text-decoration: none;">← Volver al listado</a>
    </div>

    {{-- Mostrar errores de validación --}}
    @if($errors->any())
        <div style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Mostrar mensaje de éxito --}}
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mostrar mensaje de error --}}
    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            {{ session('error') }}
        </div>
    @endif

    <div style="background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); padding: 24px 30px;">
            <h2 style="margin: 0; color: white;">📱 Equipo Externo</h2>
            <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.8);">Registrar equipo no perteneciente al inventario</p>
        </div>

        <div style="padding: 30px;">
            <form action="{{ route('soporte.externo.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Nombre del Equipo *</label>
                    <input type="text" name="equipo_nombre" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;" placeholder="Ej: Laptop Dell, Impresora HP..." value="{{ old('equipo_nombre') }}">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Serial</label>
                        <input type="text" name="serial" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;" placeholder="Número de serie" value="{{ old('serial') }}">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Marca</label>
                        <input type="text" name="marca" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;" placeholder="Marca del equipo" value="{{ old('marca') }}">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Modelo</label>
                        <input type="text" name="modelo" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;" placeholder="Modelo" value="{{ old('modelo') }}">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Diagnóstico / Descripción del problema *</label>
                    <textarea name="diagnostico" rows="4" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;" placeholder="Describa el problema del equipo...">{{ old('diagnostico') }}</textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Observaciones adicionales</label>
                    <textarea name="observaciones" rows="3" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px;" placeholder="Información adicional...">{{ old('observaciones') }}</textarea>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <a href="{{ route('soporte.index') }}" style="padding: 12px 24px; background: #e9ecef; color: #495057; text-decoration: none; border-radius: 12px;">Cancelar</a>
                    <button type="submit" style="padding: 12px 32px; background: #6c757d; color: white; border: none; border-radius: 12px; cursor: pointer;">📋 Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection