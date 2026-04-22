@extends('layouts.dashboard')

@section('title', 'Mis Solicitudes de Préstamo')

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">

    {{-- Cabecera --}}
    <div style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h2 style="margin: 0 0 5px 0; color: #1a1a2e;">📋 Mis Solicitudes de Préstamo</h2>
                <p style="margin: 0; color: #6c757d;">Gestiona tus solicitudes de préstamo de equipos</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button onclick="abrirBandejaCorreos()" style="position: relative; padding: 10px 20px; background: #2a9d8f; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                    📧 Correos
                    <span id="notificacionCorreos" style="background: #e63946; color: white; font-size: 11px; padding: 2px 6px; border-radius: 20px; position: absolute; top: -8px; right: -8px; display: none;">0</span>
                </button>
                <button onclick="abrirModalCrear()" style="padding: 10px 20px; background: #4361ee; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                    + Nueva Solicitud
                </button>
            </div>
        </div>
    </div>

    {{-- Mensajes --}}
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            {{ session('error') }}
        </div>
    @endif

    {{-- BARRA DE FILTROS --}}
    <div style="background: #ffffff; border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div style="flex: 2; min-width: 200px;">
                <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 5px;">🔍 Buscar</label>
                <input type="text" id="searchInput" placeholder="ID, institución o justificación..."
                       style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px;">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 5px;">📌 Estado</label>
                <select id="estadoFilter" style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 5px;">⚡ Prioridad</label>
                <select id="prioridadFilter" style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                    <option value="">Todas</option>
                    <option value="baja">Baja</option>
                    <option value="normal">Normal</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 5px;">📅 Desde</label>
                <input type="date" id="fechaDesde" style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
            </div>
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 5px;">📅 Hasta</label>
                <input type="date" id="fechaHasta" style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
            </div>
            <div>
                <button id="limpiarFiltros" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer;">🗑️ Limpiar</button>
            </div>
        </div>
        <div style="margin-top: 15px; font-size: 13px; color: #6c757d;">
            Mostrando <span id="resultadosCount">0</span> solicitudes
        </div>
    </div>

    {{-- Grid de Tarjetas --}}
    <div id="tarjetasContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 24px;"></div>

    {{-- Paginación --}}
    <div id="paginacionContainer" style="margin-top: 30px; display: flex; justify-content: center;"></div>
</div>

{{-- ============================================ --}}
{{-- MODAL PARA CREAR SOLICITUD (Formulario completo) --}}
{{-- ============================================ --}}
<div id="modalCrear" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background: white; width: 90%; max-width: 900px; max-height: 90vh; border-radius: 20px; overflow-y: auto;">

        <div style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); padding: 20px 24px; position: sticky; top: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; color: white;">📝 Nueva Solicitud de Préstamo</h3>
                <button onclick="cerrarModalCrear()" style="background: none; border: none; color: white; font-size: 28px; cursor: pointer;">&times;</button>
            </div>
        </div>

        <div style="padding: 24px;">
            <form id="formCrearSolicitud" action="{{ route('solicitudes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Tipo Solicitante</label>
                        <select name="tipo_solicitante" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                            <option value="interno">👤 Interno</option>
                            <option value="externo">🌍 Externo</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Prioridad</label>
                        <select name="prioridad" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                            <option value="baja">🟢 Baja</option>
                            <option value="normal">🔵 Normal</option>
                            <option value="alta">🟠 Alta</option>
                            <option value="urgente">🔴 Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Fecha Requerida</label>
                        <input type="date" name="fecha_requerida" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Fecha Fin Estimada</label>
                        <input type="date" name="fecha_fin_estimada" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Justificación</label>
                    <textarea name="justificacion" rows="3" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;"></textarea>
                </div>

                <div style="margin-top: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Observaciones (opcional)</label>
                    <textarea name="observaciones" rows="2" style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;"></textarea>
                </div>

                <div style="margin-top: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Oficio Adjunto (PDF)</label>
                    <input type="file" name="oficio_adjunto" accept=".pdf,.doc,.docx" style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 8px;">
                </div>

                {{-- Items Solicitados --}}
                <div style="margin-top: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h4 style="margin: 0;">📦 Items Solicitados</h4>
                        <button type="button" id="add-item-modal" style="padding: 6px 12px; background: #e9ecef; border: none; border-radius: 8px; cursor: pointer;">+ Agregar</button>
                    </div>
                    <div id="items-container-modal">
                        <div class="item-card-modal" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 15px; margin-bottom: 15px;">
                            <div style="display: grid; grid-template-columns: 1fr 2fr 1fr auto; gap: 12px; align-items: end;">
                                <div>
                                    <label style="font-size: 12px;">Tipo</label>
                                    <select name="items[0][tipo_item]" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;">
                                        <option value="activo">💻 Activo</option>
                                        <option value="periferico">🖱️ Periférico</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 12px;">Item</label>
                                    <input type="text" name="items[0][item_id]" required placeholder="ID o nombre del item" style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;">
                                </div>
                                <div>
                                    <label style="font-size: 12px;">Cantidad</label>
                                    <input type="number" name="items[0][cantidad]" min="1" value="1" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;">
                                </div>
                                <div>
                                    <button type="button" class="remove-item-modal" style="background: none; border: none; color: #e63946; cursor: pointer; font-size: 20px;">🗑️</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <button type="button" onclick="cerrarModalCrear()" style="padding: 10px 24px; background: #e9ecef; border: none; border-radius: 8px; cursor: pointer;">Cancelar</button>
                    <button type="submit" style="padding: 10px 32px; background: #4361ee; color: white; border: none; border-radius: 8px; cursor: pointer;">📤 Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL BANDEJA DE CORREOS (Con formulario completo) --}}
{{-- ============================================ --}}
<div id="modalBandeja" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div style="background: white; width: 95%; max-width: 1300px; height: 90vh; border-radius: 24px; overflow: hidden; display: flex; flex-direction: column;">

        <div style="padding: 20px 24px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 20px;">📬 Bandeja de Correos</h2>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #6c757d;">Correos recibidos para solicitudes</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button onclick="revisarCorreosManual()" style="padding: 8px 16px; background: #1a1a2e; color: white; border: none; border-radius: 10px; cursor: pointer;">🔄 Revisar ahora</button>
                <button onclick="cerrarBandejaCorreos()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex: 1; overflow: hidden;">
            {{-- Lista de correos --}}
            <div style="width: 350px; border-right: 1px solid #e9ecef; overflow-y: auto; background: #fafbfc;">
                <div id="listaCorreosContainer" style="padding: 12px;"></div>
            </div>

            {{-- Panel derecho: Previsualización + Formulario completo --}}
            <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">

                {{-- Previsualización del correo --}}
                <div id="previewCorreo" style="padding: 20px; border-bottom: 1px solid #e9ecef; max-height: 35%; overflow-y: auto; background: #f8f9fa;">
                    <div style="text-align: center; color: #6c757d; padding: 30px;">
                        <span style="font-size: 40px;">📧</span>
                        <p>Selecciona un correo para ver su contenido</p>
                    </div>
                </div>

                {{-- Formulario completo para crear solicitud --}}
                <div style="flex: 1; overflow-y: auto; padding: 20px;">
                    <h3 style="margin: 0 0 15px 0; font-size: 16px;">Crear solicitud desde este correo</h3>

                    <form id="formSolicitudCorreo" action="{{ route('solicitudes.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="correo_origen" id="correoOrigen">

                        {{-- Grid 2 columnas --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Tipo Solicitante</label>
                                <select name="tipo_solicitante" id="formTipo" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                                    <option value="interno">👤 Interno</option>
                                    <option value="externo">🌍 Externo</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Prioridad</label>
                                <select name="prioridad" id="formPrioridad" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                                    <option value="baja">🟢 Baja</option>
                                    <option value="normal">🔵 Normal</option>
                                    <option value="alta">🟠 Alta</option>
                                    <option value="urgente">🔴 Urgente</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Fecha Requerida</label>
                                <input type="date" name="fecha_requerida" id="formFechaRequerida" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Fecha Fin Estimada</label>
                                <input type="date" name="fecha_fin_estimada" id="formFechaFin" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;">
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 6px;">Justificación</label>
                            <textarea name="justificacion" id="formJustificacion" rows="3" required style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;"></textarea>
                        </div>

                        <div style="margin-top: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 6px;">Observaciones (opcional)</label>
                            <textarea name="observaciones" id="formObservaciones" rows="2" style="width: 100%; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 8px;"></textarea>
                        </div>

                        <div style="margin-top: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 6px;">Oficio Adjunto (PDF)</label>
                            <input type="file" name="oficio_adjunto" id="formAdjunto" accept=".pdf,.doc,.docx" style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 8px;">
                        </div>

                        {{-- Items Solicitados --}}
                        <div style="margin-top: 30px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h4 style="margin: 0;">📦 Items Solicitados</h4>
                                <button type="button" id="add-item-correo" style="padding: 6px 12px; background: #e9ecef; border: none; border-radius: 8px; cursor: pointer;">+ Agregar</button>
                            </div>
                            <div id="items-container-correo">
                                <div class="item-card-correo" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 15px; margin-bottom: 15px;">
                                    <div style="display: grid; grid-template-columns: 1fr 2fr 1fr auto; gap: 12px; align-items: end;">
                                        <div>
                                            <label style="font-size: 12px;">Tipo</label>
                                            <select name="items[0][tipo_item]" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;">
                                                <option value="activo">💻 Activo</option>
                                                <option value="periferico">🖱️ Periférico</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label style="font-size: 12px;">Item</label>
                                            <input type="text" name="items[0][item_id]" required placeholder="ID o nombre del item" style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;">
                                        </div>
                                        <div>
                                            <label style="font-size: 12px;">Cantidad</label>
                                            <input type="number" name="items[0][cantidad]" min="1" value="1" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;">
                                        </div>
                                        <div>
                                            <button type="button" class="remove-item-correo" style="background: none; border: none; color: #e63946; cursor: pointer; font-size: 20px;">🗑️</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e9ecef;">
                            <button type="button" onclick="cerrarBandejaCorreos()" style="padding: 10px 24px; background: #e9ecef; border: none; border-radius: 8px; cursor: pointer;">Cancelar</button>
                            <button type="submit" style="padding: 10px 32px; background: #2a9d8f; color: white; border: none; border-radius: 8px; cursor: pointer;">📤 Crear Solicitud</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para ver items --}}
<div id="modalItems" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10002; justify-content: center; align-items: center;">
    <div style="background: white; width: 90%; max-width: 500px; border-radius: 20px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
            <h3 style="margin: 0;">📦 Items de la Solicitud</h3>
            <button onclick="document.getElementById('modalItems').style.display='none'" style="background: none; border: none; font-size: 24px;">&times;</button>
        </div>
        <div id="modalItemsBody">Cargando...</div>
    </div>
</div>

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .tarjeta {
        animation: fadeInUp 0.3s ease;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .tarjeta:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .correo-item {
        transition: all 0.2s ease;
    }
    .correo-item:hover {
        background: #f1f3f5;
        transform: translateX(4px);
    }
</style>

<script>
// Datos de solicitudes
let todasLasSolicitudes = @json($solicitudes->items());
let solicitudesFiltradas = [...todasLasSolicitudes];
let currentPage = 1;
const itemsPorPagina = 6;

// Correos simulados
let correosPendientes = [
    {
        id: 1,
        from: "juan.perez@empresa.com",
        subject: "Solicitud de préstamo - Proyecto A",
        date: "2024-01-15",
        body: "Necesito equipos para el proyecto A. Fecha requerida: 20/01/2024. Prioridad: alta",
        extracted: { prioridad: "alta", fecha_requerida: "2024-01-20", justificacion: "Proyecto A - Necesito equipos urgentemente" }
    },
    {
        id: 2,
        from: "maria.garcia@empresa.com",
        subject: "URGENTE: Equipos para reunión",
        date: "2024-01-16",
        body: "Se requiere con urgencia computadoras para reunión con clientes. Fecha: 18/01/2024",
        extracted: { prioridad: "urgente", fecha_requerida: "2024-01-18", justificacion: "Reunión con clientes - Requiero equipos urgentemente" }
    }
];

// Renderizar tarjetas
function renderizarTarjetas() {
    const start = (currentPage - 1) * itemsPorPagina;
    const end = start + itemsPorPagina;
    const solicitudesPagina = solicitudesFiltradas.slice(start, end);
    const container = document.getElementById('tarjetasContainer');
    const resultadosCount = document.getElementById('resultadosCount');

    resultadosCount.innerText = solicitudesFiltradas.length;

    if (solicitudesFiltradas.length === 0) {
        container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 60px; background: #f8f9fa; border-radius: 16px;">
            <div style="font-size: 48px;">📭</div><p>No hay solicitudes</p>
            <button onclick="abrirModalCrear()" style="padding: 8px 20px; background: #4361ee; color: white; border: none; border-radius: 8px; cursor: pointer;">Crear primera solicitud</button>
        </div>`;
        document.getElementById('paginacionContainer').innerHTML = '';
        return;
    }

    let html = '';
    for (const s of solicitudesPagina) {
        let prioridadColor = s.prioridad === 'urgente' ? '#e63946' : s.prioridad === 'alta' ? '#f4a261' : s.prioridad === 'normal' ? '#2a9d8f' : '#6c757d';
        let estadoColor = s.estado_solicitud === 'pendiente' ? '#ffc107' : s.estado_solicitud === 'aprobada' ? '#2e7d32' : s.estado_solicitud === 'rechazada' ? '#e63946' : '#6c757d';

        html += `
            <div class="tarjeta" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e9ecef; background: #fafbfc;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="font-size: 18px;">Solicitud #${s.id}</strong>
                            <div style="font-size: 12px; color: #6c757d;">${new Date(s.fecha_solicitud).toLocaleDateString()}</div>
                        </div>
                        <span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; background: ${prioridadColor}; color: white;">${s.prioridad}</span>
                    </div>
                </div>
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                        <div><div style="font-size: 12px; color: #6c757d;">📅 Fecha Requerida</div><div>${new Date(s.fecha_requerida).toLocaleDateString()}</div></div>
                        <div><div style="font-size: 12px; color: #6c757d;">🏢 Institución</div><div>${s.institucion?.nombre || '-'}</div></div>
                        <div><div style="font-size: 12px; color: #6c757d;">📦 Items</div><div>${s.detalles?.length || 0} items</div></div>
                        <div><div style="font-size: 12px; color: #6c757d;">⏱️ Estado</div><div><span style="padding: 4px 10px; border-radius: 20px; font-size: 11px; background: ${estadoColor}20; color: ${estadoColor};">${s.estado_solicitud}</span></div></div>
                    </div>
                    ${s.justificacion ? `<div style="border-top: 1px solid #e9ecef; padding-top: 12px;"><div style="font-size: 12px; color: #6c757d;">📝 Justificación</div><div style="font-size: 13px;">${s.justificacion.substring(0, 100)}${s.justificacion.length > 100 ? '...' : ''}</div></div>` : ''}
                </div>
                <div style="padding: 16px 20px; background: #fafbfc; border-top: 1px solid #e9ecef; display: flex; gap: 10px;">
                    <button onclick="verItems(${s.id})" style="flex: 1; padding: 8px; background: #17a2b8; color: white; border: none; border-radius: 8px; cursor: pointer;">👁️ Ver Items</button>
                    ${s.estado_solicitud === 'pendiente' ? `
                        <form action="/solicitudes/${s.id}/cancel" method="POST" style="flex: 1;">
                            @csrf
                            <button type="submit" onclick="return confirm('¿Cancelar solicitud?')" style="width: 100%; padding: 8px; background: #e63946; color: white; border: none; border-radius: 8px; cursor: pointer;">✖ Cancelar</button>
                        </form>
                    ` : ''}
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
    renderizarPaginacion();
}

function renderizarPaginacion() {
    const totalPages = Math.ceil(solicitudesFiltradas.length / itemsPorPagina);
    const container = document.getElementById('paginacionContainer');

    if (totalPages <= 1) { container.innerHTML = ''; return; }

    let html = '<div style="display: flex; gap: 8px; flex-wrap: wrap;">';
    for (let i = 1; i <= totalPages; i++) {
        html += `<button onclick="cambiarPagina(${i})" style="padding: 8px 14px; border: 1px solid #dee2e6; background: ${i === currentPage ? '#4361ee' : 'white'}; color: ${i === currentPage ? 'white' : '#4361ee'}; border-radius: 8px; cursor: pointer;">${i}</button>`;
    }
    html += '</div>';
    container.innerHTML = html;
}

function cambiarPagina(page) {
    currentPage = page;
    renderizarTarjetas();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Filtros
function aplicarFiltros() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const estado = document.getElementById('estadoFilter').value;
    const prioridad = document.getElementById('prioridadFilter').value;
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;

    solicitudesFiltradas = todasLasSolicitudes.filter(s => {
        if (searchTerm && !s.id.toString().includes(searchTerm) && !(s.institucion?.nombre || '').toLowerCase().includes(searchTerm)) return false;
        if (estado && s.estado_solicitud !== estado) return false;
        if (prioridad && s.prioridad !== prioridad) return false;
        if (fechaDesde && new Date(s.fecha_requerida) < new Date(fechaDesde)) return false;
        if (fechaHasta && new Date(s.fecha_requerida) > new Date(fechaHasta)) return false;
        return true;
    });

    currentPage = 1;
    renderizarTarjetas();
}

// Modales
function abrirModalCrear() {
    document.getElementById('modalCrear').style.display = 'flex';
}

function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
}

function abrirBandejaCorreos() {
    document.getElementById('modalBandeja').style.display = 'flex';
    cargarListaCorreos();
}

function cerrarBandejaCorreos() {
    document.getElementById('modalBandeja').style.display = 'none';
}

function cargarListaCorreos() {
    const container = document.getElementById('listaCorreosContainer');
    if (correosPendientes.length === 0) {
        container.innerHTML = `<div style="text-align: center; padding: 40px;"><div style="font-size: 40px;">📭</div><p>No hay correos pendientes</p><button onclick="revisarCorreosManual()" style="margin-top: 10px; padding: 8px 16px; background: #1a1a2e; color: white; border: none; border-radius: 10px;">Revisar ahora</button></div>`;
        return;
    }

    let html = '';
    for (const correo of correosPendientes) {
        html += `
            <div class="correo-item" onclick="seleccionarCorreo(${correo.id})" style="padding: 15px; border-bottom: 1px solid #e9ecef; cursor: pointer;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                    <span>📧</span>
                    <strong style="flex: 1;">${correo.from}</strong>
                    <span style="font-size: 11px; color: #6c757d;">${correo.date}</span>
                </div>
                <div style="font-size: 13px; font-weight: 500;">${correo.subject}</div>
                <div style="font-size: 11px; color: #6c757d;">${correo.body.substring(0, 60)}...</div>
            </div>
        `;
    }
    container.innerHTML = html;
}

function seleccionarCorreo(id) {
    const correo = correosPendientes.find(c => c.id === id);
    if (!correo) return;

    // Previsualización
    document.getElementById('previewCorreo').innerHTML = `
        <div style="background: white; border-radius: 12px; padding: 16px;">
            <div style="margin-bottom: 8px;"><strong>De:</strong> ${correo.from}</div>
            <div style="margin-bottom: 8px;"><strong>Asunto:</strong> ${correo.subject}</div>
            <div style="margin-bottom: 8px;"><strong>Fecha:</strong> ${correo.date}</div>
            <div style="background: #f8f9fa; padding: 12px; border-radius: 10px; margin-top: 10px;">${correo.body}</div>
        </div>
    `;

    // Llenar formulario con datos extraídos
    document.getElementById('correoOrigen').value = correo.from;
    document.getElementById('formPrioridad').value = correo.extracted.prioridad;
    document.getElementById('formFechaRequerida').value = correo.extracted.fecha_requerida;
    document.getElementById('formJustificacion').value = correo.extracted.justificacion;

    // Calcular fecha fin estimada (7 días después)
    const fechaReq = new Date(correo.extracted.fecha_requerida);
    fechaReq.setDate(fechaReq.getDate() + 7);
    document.getElementById('formFechaFin').value = fechaReq.toISOString().split('T')[0];
}

function revisarCorreosManual() {
    const btn = event.target;
    btn.textContent = '⏳ Revisando...';
    btn.disabled = true;

    setTimeout(() => {
        const nuevoCorreo = {
            id: correosPendientes.length + 1,
            from: "nuevo@empresa.com",
            subject: "Nueva solicitud recibida",
            date: new Date().toISOString().split('T')[0],
            body: "Solicito equipos para el departamento de TI. Fecha requerida: próximo lunes",
            extracted: { prioridad: "normal", fecha_requerida: new Date(Date.now() + 3*24*60*60*1000).toISOString().split('T')[0], justificacion: "Solicitud desde el departamento de TI" }
        };
        correosPendientes.push(nuevoCorreo);
        btn.textContent = '🔄 Revisar ahora';
        btn.disabled = false;
        cargarListaCorreos();

        const notif = document.getElementById('notificacionCorreos');
        notif.style.display = 'inline-block';
        notif.textContent = correosPendientes.length;
    }, 1500);
}

function verItems(id) {
    const modal = document.getElementById('modalItems');
    const modalBody = document.getElementById('modalItemsBody');

    modal.style.display = 'flex';
    modalBody.innerHTML = '<div style="text-align: center; padding: 30px;"><div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #e9ecef; border-top-color: #4361ee; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; color: #6c757d;">Cargando items...</p></div>';

    fetch(`/solicitudes/${id}/items`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBody.innerHTML = `<div style="text-align: center; padding: 40px; color: #e63946;"><p>❌ ${data.error}</p></div>`;
                return;
            }

            if (data.length === 0) {
                modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><p style="color: #6c757d;">No hay items en esta solicitud</p></div>';
                return;
            }

            let html = '<table style="width: 100%; border-collapse: collapse;">';
            html += '<thead><tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;"><th style="padding: 12px; text-align: left;">Tipo</th><th style="padding: 12px; text-align: left;">Descripción</th><th style="padding: 12px; text-align: center;">Cantidad</th></tr></thead><tbody>';

            for (let i = 0; i < data.length; i++) {
                const item = data[i];
                const badgeText = (item.tipo_item === 'activo') ? '💻 Activo' : '🖱️ Periférico';
                const badgeStyle = (item.tipo_item === 'activo') ? 'background: #e3f2fd; color: #1976d2;' : 'background: #e8f5e9; color: #388e3c;';

                html += '<tr>';
                html += `<td style="padding: 10px;"><span style="padding: 4px 10px; border-radius: 20px; font-size: 12px; ${badgeStyle}">${badgeText}</span></td>`;
                html += `<td style="padding: 10px;">${escapeHtml(item.descripcion)}</td>`;
                html += `<td style="padding: 10px; text-align: center;"><strong>${item.cantidad_solicitada}</strong></td>`;
                html += '</tr>';
            }

            html += '</tbody></table>';
            modalBody.innerHTML = html;
        })
        .catch(error => {
            modalBody.innerHTML = `<div style="text-align: center; padding: 40px; color: #e63946;"><p>❌ Error al cargar los items</p></div>`;
            console.error('Error:', error);
        });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// Event listeners filtros
document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
document.getElementById('estadoFilter').addEventListener('change', aplicarFiltros);
document.getElementById('prioridadFilter').addEventListener('change', aplicarFiltros);
document.getElementById('fechaDesde').addEventListener('change', aplicarFiltros);
document.getElementById('fechaHasta').addEventListener('change', aplicarFiltros);
document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('estadoFilter').value = '';
    document.getElementById('prioridadFilter').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    solicitudesFiltradas = [...todasLasSolicitudes];
    currentPage = 1;
    renderizarTarjetas();
});

// Items dinámicos para el modal de crear normal
let itemCountModal = 1;
document.getElementById('add-item-modal')?.addEventListener('click', function() {
    const container = document.getElementById('items-container-modal');
    const newCard = document.createElement('div');
    newCard.className = 'item-card-modal';
    newCard.style.cssText = 'background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 15px; margin-bottom: 15px;';
    newCard.innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 2fr 1fr auto; gap: 12px; align-items: end;">
            <div><label style="font-size: 12px;">Tipo</label><select name="items[${itemCountModal}][tipo_item]" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;"><option value="activo">💻 Activo</option><option value="periferico">🖱️ Periférico</option></select></div>
            <div><label style="font-size: 12px;">Item</label><input type="text" name="items[${itemCountModal}][item_id]" required placeholder="ID o nombre del item" style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;"></div>
            <div><label style="font-size: 12px;">Cantidad</label><input type="number" name="items[${itemCountModal}][cantidad]" min="1" value="1" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;"></div>
            <div><button type="button" class="remove-item-modal" style="background: none; border: none; color: #e63946; cursor: pointer; font-size: 20px;">🗑️</button></div>
        </div>
    `;
    container.appendChild(newCard);
    itemCountModal++;
});

// Items dinámicos para el modal de correos
let itemCountCorreo = 1;
document.getElementById('add-item-correo')?.addEventListener('click', function() {
    const container = document.getElementById('items-container-correo');
    const newCard = document.createElement('div');
    newCard.className = 'item-card-correo';
    newCard.style.cssText = 'background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 15px; margin-bottom: 15px;';
    newCard.innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 2fr 1fr auto; gap: 12px; align-items: end;">
            <div><label style="font-size: 12px;">Tipo</label><select name="items[${itemCountCorreo}][tipo_item]" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;"><option value="activo">💻 Activo</option><option value="periferico">🖱️ Periférico</option></select></div>
            <div><label style="font-size: 12px;">Item</label><input type="text" name="items[${itemCountCorreo}][item_id]" required placeholder="ID o nombre del item" style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;"></div>
            <div><label style="font-size: 12px;">Cantidad</label><input type="number" name="items[${itemCountCorreo}][cantidad]" min="1" value="1" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px;"></div>
            <div><button type="button" class="remove-item-correo" style="background: none; border: none; color: #e63946; cursor: pointer; font-size: 20px;">🗑️</button></div>
        </div>
    `;
    container.appendChild(newCard);
    itemCountCorreo++;
});

// Eliminar items (para ambos modales)
document.addEventListener('click', function(e) {
    if(e.target.classList.contains('remove-item-modal') || e.target.parentElement?.classList?.contains('remove-item-modal')) {
        const btn = e.target.classList.contains('remove-item-modal') ? e.target : e.target.parentElement;
        const card = btn.closest('.item-card-modal');
        const items = document.querySelectorAll('#items-container-modal .item-card-modal');
        if(items.length > 1) {
            card.remove();
        } else {
            alert('Debe haber al menos un item');
        }
    }

    if(e.target.classList.contains('remove-item-correo') || e.target.parentElement?.classList?.contains('remove-item-correo')) {
        const btn = e.target.classList.contains('remove-item-correo') ? e.target : e.target.parentElement;
        const card = btn.closest('.item-card-correo');
        const items = document.querySelectorAll('#items-container-correo .item-card-correo');
        if(items.length > 1) {
            card.remove();
        } else {
            alert('Debe haber al menos un item');
        }
    }
});

// Cerrar modales al hacer clic fuera
document.getElementById('modalCrear')?.addEventListener('click', function(e) {
    if(e.target === this) cerrarModalCrear();
});
document.getElementById('modalBandeja')?.addEventListener('click', function(e) {
    if(e.target === this) cerrarBandejaCorreos();
});
document.getElementById('modalItems')?.addEventListener('click', function(e) {
    if(e.target === this) this.style.display = 'none';
});

// Inicializar
renderizarTarjetas();

// Notificación inicial
setTimeout(() => {
    if(correosPendientes.length > 0) {
        document.getElementById('notificacionCorreos').style.display = 'inline-block';
        document.getElementById('notificacionCorreos').textContent = correosPendientes.length;
    }
}, 500);
</script>
@endsection
