@extends('layouts.dashboard')

@section('title', 'Generador de Reportes')

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">

    {{-- Cabecera --}}
    <div style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 28px; font-weight: 300;">📊 Generador de Reportes</h1>
        <p style="margin: 8px 0 0 0; color: #6c757d;">Genera reportes en PDF del sistema</p>
    </div>

    {{-- Tarjetas de estadísticas rápidas --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #4361ee, #3a0ca3); color: white; border-radius: 12px; padding: 20px;">
            <div style="font-size: 32px;">📋</div>
            <div style="font-size: 28px; font-weight: bold;">{{ $stats['solicitudes_pendientes'] }}</div>
            <div>Solicitudes Pendientes</div>
        </div>
        <div style="background: linear-gradient(135deg, #2a9d8f, #1b6b62); color: white; border-radius: 12px; padding: 20px;">
            <div style="font-size: 32px;">📦</div>
            <div style="font-size: 28px; font-weight: bold;">{{ $stats['prestamos_activos'] }}</div>
            <div>Préstamos Activos</div>
        </div>
        <div style="background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; border-radius: 12px; padding: 20px;">
            <div style="font-size: 32px;">💻</div>
            <div style="font-size: 28px; font-weight: bold;">{{ $stats['equipos_disponibles'] }}</div>
            <div>Equipos Disponibles</div>
        </div>
        <div style="background: linear-gradient(135deg, #e63946, #b91c1c); color: white; border-radius: 12px; padding: 20px;">
            <div style="font-size: 32px;">🔧</div>
            <div style="font-size: 28px; font-weight: bold;">{{ $stats['soportes_pendientes'] }}</div>
            <div>Soportes Pendientes</div>
        </div>
    </div>

    {{-- Grid de reportes --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">

        {{-- Reporte 1: Solicitudes por período --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #4361ee; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📋</span>
                    <h3 style="margin: 0;">Solicitudes por Período</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <form action="{{ route('reportes.solicitudes.periodo') }}" method="POST" target="_blank">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Fecha Fin</label>
                        <input type="date" name="fecha_fin" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px;">Estado</label>
                        <select name="estado" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                            <option value="todos">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobada">Aprobada</option>
                            <option value="rechazada">Rechazada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <button type="submit" style="width: 100%; padding: 12px; background: #4361ee; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        📄 Generar PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Reporte 2: Préstamos Activos --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #2a9d8f; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📦</span>
                    <h3 style="margin: 0;">Préstamos Activos</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <p style="color: #6c757d; margin-bottom: 20px;">Reporte de todos los préstamos actualmente activos, incluyendo los vencidos.</p>
                <form action="{{ route('reportes.prestamos.activos') }}" method="POST" target="_blank">
                    @csrf
                    <button type="submit" style="width: 100%; padding: 12px; background: #2a9d8f; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        📄 Generar PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Reporte 3: Inventario General --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #2e7d32; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">💻</span>
                    <h3 style="margin: 0;">Inventario General</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <p style="color: #6c757d; margin-bottom: 20px;">Reporte completo de activos y periféricos con cantidades y ubicaciones.</p>
                <form action="{{ route('reportes.inventario.general') }}" method="POST" target="_blank">
                    @csrf
                    <button type="submit" style="width: 100%; padding: 12px; background: #2e7d32; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        📄 Generar PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Reporte 4: Equipos Disponibles --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #17a2b8; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">✅</span>
                    <h3 style="margin: 0;">Equipos Disponibles</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <p style="color: #6c757d; margin-bottom: 20px;">Inventario de equipos disponibles para préstamo inmediato.</p>
                <form action="{{ route('reportes.equipos.disponibles') }}" method="POST" target="_blank">
                    @csrf
                    <button type="submit" style="width: 100%; padding: 12px; background: #17a2b8; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        📄 Generar PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Reporte 5: Acta de Préstamo --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #6c757d; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📜</span>
                    <h3 style="margin: 0;">Acta de Préstamo</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px;">ID del Préstamo</label>
                    <input type="number" id="prestamo_id_acta" placeholder="Ej: 1" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                </div>
                <button onclick="generarActaPrestamo()" style="width: 100%; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    📄 Generar Acta
                </button>
            </div>
        </div>

        {{-- Reporte 6: Acta de Devolución --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #6c757d; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">🔄</span>
                    <h3 style="margin: 0;">Acta de Devolución</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px;">ID del Préstamo</label>
                    <input type="number" id="prestamo_id_devolucion" placeholder="Ej: 1" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                </div>
                <button onclick="generarActaDevolucion()" style="width: 100%; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    📄 Generar Acta
                </button>
            </div>
        </div>

        {{-- Reporte 7: Soporte/Mantenimiento --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #e63946; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">🔧</span>
                    <h3 style="margin: 0;">Soporte Técnico</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <form action="{{ route('reportes.soporte.periodo') }}" method="POST" target="_blank">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Fecha Fin</label>
                        <input type="date" name="fecha_fin" required style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px;">Estado</label>
                        <select name="estado" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
                            <option value="todos">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="completado">Completado</option>
                        </select>
                    </div>
                    <button type="submit" style="width: 100%; padding: 12px; background: #e63946; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        📄 Generar PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Reporte 8: Usuarios Activos --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #9b59b6; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">👥</span>
                    <h3 style="margin: 0;">Usuarios Activos</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <p style="color: #6c757d; margin-bottom: 20px;">Lista de todos los usuarios activos del sistema con sus roles.</p>
                <form action="{{ route('reportes.usuarios.activos') }}" method="POST" target="_blank">
                    @csrf
                    <button type="submit" style="width: 100%; padding: 12px; background: #9b59b6; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        📄 Generar PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Reporte 9: Resumen Ejecutivo --}}
        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="padding: 20px; background: #1a1a2e; color: white;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📈</span>
                    <h3 style="margin: 0;">Resumen Ejecutivo</h3>
                </div>
            </div>
            <div style="padding: 20px;">
                <p style="color: #6c757d; margin-bottom: 20px;">Dashboard completo con estadísticas generales del sistema.</p>
                <form action="{{ route('reportes.resumen.ejecutivo') }}" method="POST" target="_blank">
                    @csrf
                    <button type="submit" style="width: 100%; padding: 12px; background: #1a1a2e; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        📄 Generar PDF
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function generarActaPrestamo() {
    const prestamoId = document.getElementById('prestamo_id_acta').value;
    if (!prestamoId) {
        alert('Por favor ingrese el ID del préstamo');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("reportes.acta.prestamo") }}';
    form.target = '_blank';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'prestamo_id';
    input.value = prestamoId;
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function generarActaDevolucion() {
    const prestamoId = document.getElementById('prestamo_id_devolucion').value;
    if (!prestamoId) {
        alert('Por favor ingrese el ID del préstamo');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("reportes.acta.devolucion") }}';
    form.target = '_blank';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'prestamo_id';
    input.value = prestamoId;
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endsection
