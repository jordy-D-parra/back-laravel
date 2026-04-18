@extends('layouts.dashboard')

@section('title', 'Nueva Ficha de Soporte')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">

    <div style="margin-bottom: 20px;">
        <a href="{{ route('soporte.index') }}" style="color: #4361ee; text-decoration: none;">← Volver al listado</a>
    </div>

    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            {{ session('error') }}
        </div>
    @endif

    <div style="background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); padding: 24px 30px;">
            <h2 style="margin: 0; color: white;">🔧 Nueva Ficha de Soporte</h2>
            <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.8);">Registrar equipo para mantenimiento o reparación</p>
        </div>

        <div style="padding: 30px;">
            <form action="{{ route('soporte.store') }}" method="POST" id="formSoporte">
                @csrf

                {{-- Campo: Equipo con Serial - Enviamos el serial_id directamente --}}
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                        📟 Seleccione el equipo y serial *
                    </label>
                    <select name="serial_id" id="serial_id" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px; font-size: 14px;">
                        <option value="">Seleccione un equipo...</option>
                        @foreach($activos as $activo)
                            @if($activo->seriales_disponibles->count() > 0)
                                <optgroup label="📟 {{ $activo->marca_modelo }} @if($activo->capacidad)({{ $activo->capacidad }})@endif">
                                    @foreach($activo->seriales_disponibles as $serial)
                                        <option value="{{ $serial->id }}" data-serial="{{ $serial->serial }}" data-activo-id="{{ $activo->id }}">
                                            🔑 Serial: {{ $serial->serial }} ✅ Disponible
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <option value="sin_serial_{{ $activo->id }}" data-serial="" data-activo-id="{{ $activo->id }}" data-sin-serial="true">
                                    📦 {{ $activo->marca_modelo }} @if($activo->capacidad)({{ $activo->capacidad }})@endif - Stock: {{ $activo->cantidad }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    
                    {{-- Información del serial seleccionado --}}
                    <div id="infoSerialContainer" style="margin-top: 12px; display: none;">
                        <div style="background: #e7f3ff; border-left: 4px solid #4361ee; padding: 12px; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 20px;">🔑</span>
                                <div>
                                    <strong style="color: #4361ee;">Serial asignado:</strong><br>
                                    <span id="serialSeleccionadoTexto" style="font-family: monospace; font-size: 16px; font-weight: bold;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @error('serial_id')
                        <small style="color: #dc3545;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Campo oculto para el activo_id --}}
                <input type="hidden" name="activo_id" id="activo_id_hidden" value="">

                {{-- Campo: Técnico Asignado --}}
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">👨‍🔧 Técnico Asignado</label>
                    <select name="tecnico_id" id="tecnico_id" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px; font-size: 14px;">
                        <option value="">Seleccione un técnico (Admin o Super Admin)</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ old('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                @if($tecnico->rol && $tecnico->rol->nombre == 'super_admin')
                                    👑 
                                @elseif($tecnico->rol && $tecnico->rol->nombre == 'admin')
                                    ⚙️ 
                                @endif
                                {{ $tecnico->nombre }} {{ $tecnico->apellido }} - {{ $tecnico->rol->nombre ?? 'Sin rol' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Campo: Diagnóstico --}}
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                        🔍 Diagnóstico / Descripción del problema *
                    </label>
                    <textarea name="diagnostico" rows="4" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px; font-size: 14px;" placeholder="Describa detalladamente el problema del equipo...">{{ old('diagnostico') }}</textarea>
                    @error('diagnostico')
                        <small style="color: #dc3545;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Campo: Observaciones --}}
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">📝 Observaciones adicionales</label>
                    <textarea name="observaciones" rows="3" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 12px; font-size: 14px;" placeholder="Información adicional que pueda ser útil...">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <small style="color: #dc3545;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Checkbox de confirmación --}}
                <div id="checkboxContainer" style="background: #f8f9fa; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: none;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="confirmar_serial" style="width: 18px; height: 18px;">
                        <label for="confirmar_serial" style="margin: 0; font-weight: 500;">
                            Confirmo que el equipo físico coincide con el serial seleccionado
                        </label>
                    </div>
                </div>

                {{-- Botones --}}
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <a href="{{ route('soporte.index') }}" style="padding: 12px 24px; background: #e9ecef; color: #495057; text-decoration: none; border-radius: 12px; font-weight: 500;">Cancelar</a>
                    <button type="submit" id="btnSubmit" disabled style="padding: 12px 32px; background: #4361ee; color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: 500;">
                        📋 Crear Ficha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const selectSerial = document.getElementById('serial_id');
    const infoSerialContainer = document.getElementById('infoSerialContainer');
    const serialSeleccionadoTexto = document.getElementById('serialSeleccionadoTexto');
    const checkboxContainer = document.getElementById('checkboxContainer');
    const confirmarSerial = document.getElementById('confirmar_serial');
    const btnSubmit = document.getElementById('btnSubmit');
    const activoIdHidden = document.getElementById('activo_id_hidden');

    function actualizarInformacion() {
        const selectedOption = selectSerial.options[selectSerial.selectedIndex];
        
        if (!selectSerial.value) {
            infoSerialContainer.style.display = 'none';
            checkboxContainer.style.display = 'none';
            btnSubmit.disabled = true;
            activoIdHidden.value = '';
            return;
        }
        
        const serial = selectedOption.getAttribute('data-serial');
        const activoId = selectedOption.getAttribute('data-activo-id');
        const sinSerial = selectedOption.getAttribute('data-sin-serial') === 'true';
        
        // Guardar el activo_id en el campo oculto
        activoIdHidden.value = activoId;
        
        if (sinSerial) {
            serialSeleccionadoTexto.textContent = 'Sin serial individual (equipo con stock)';
            infoSerialContainer.style.display = 'block';
            checkboxContainer.style.display = 'none';
            confirmarSerial.checked = false;
            confirmarSerial.disabled = true;
            btnSubmit.disabled = false;
        } else if (serial) {
            serialSeleccionadoTexto.textContent = serial;
            infoSerialContainer.style.display = 'block';
            checkboxContainer.style.display = 'block';
            confirmarSerial.checked = false;
            confirmarSerial.disabled = false;
            btnSubmit.disabled = true;
        } else {
            infoSerialContainer.style.display = 'none';
            checkboxContainer.style.display = 'none';
            btnSubmit.disabled = true;
        }
    }
    
    if (selectSerial) {
        selectSerial.addEventListener('change', actualizarInformacion);
    }
    
    if (confirmarSerial) {
        confirmarSerial.addEventListener('change', function() {
            btnSubmit.disabled = !this.checked;
        });
    }
    
    actualizarInformacion();
</script>

<style>
    #btnSubmit:disabled {
        background: #ccc;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    select, textarea {
        transition: all 0.3s ease;
    }
    
    select:focus, textarea:focus {
        border-color: #4361ee !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }
</style>
@endsection