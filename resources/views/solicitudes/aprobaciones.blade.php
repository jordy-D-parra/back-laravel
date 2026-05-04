@extends('layouts.dashboard')

@section('title', 'Aprobar Solicitudes')

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">

    {{-- Cabecera --}}
    <div style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 28px; font-weight: 300;">📋 Aprobar Solicitudes</h1>
        <p style="margin: 8px 0 0 0; color: #6c757d;">Revisa y gestiona las solicitudes de préstamo</p>
    </div>

    {{-- Pestañas --}}
    <div style="display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid #e9ecef;">
        <button onclick="cambiarPestana('pendientes')" id="tab-pendientes" style="padding: 12px 24px; background: none; border: none; border-bottom: 3px solid #4361ee; color: #4361ee; font-weight: 600; cursor: pointer;">⏳ Pendientes</button>
        <button onclick="cambiarPestana('aprobadas')" id="tab-aprobadas" style="padding: 12px 24px; background: none; border: none; border-bottom: 3px solid transparent; color: #6c757d; cursor: pointer;">✅ Aprobadas</button>
        <button onclick="cambiarPestana('prestamos')" id="tab-prestamos" style="padding: 12px 24px; background: none; border: none; border-bottom: 3px solid transparent; color: #6c757d; cursor: pointer;">📦 Préstamos Activos</button>
        <button onclick="cambiarPestana('historial')" id="tab-historial" style="padding: 12px 24px; background: none; border: none; border-bottom: 3px solid transparent; color: #6c757d; cursor: pointer;">📜 Historial</button>
    </div>

    {{-- Contenido Pestaña: Pendientes --}}
    <div id="contenido-pendientes">
        <div id="solicitudesPendientesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
            {{-- Se llena con JS --}}
        </div>
    </div>

    {{-- Contenido Pestaña: Aprobadas --}}
    <div id="contenido-aprobadas" style="display: none;">
        <div id="solicitudesAprobadasContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;"></div>
    </div>

    {{-- Contenido Pestaña: Préstamos Activos --}}
    <div id="contenido-prestamos" style="display: none;">
        <div id="prestamosActivosContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;"></div>
    </div>

    {{-- Contenido Pestaña: Historial --}}
    <div id="contenido-historial" style="display: none;">
        <div id="historialContainer"></div>
    </div>
</div>

{{-- MODAL PARA APROBAR Y ASIGNAR EQUIPOS --}}
<div id="modalAprobar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background: white; width: 95%; max-width: 1000px; max-height: 90vh; border-radius: 20px; overflow-y: auto;">
        <div style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); padding: 20px 24px; position: sticky; top: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; color: white;">📝 Aprobar y Asignar Equipos</h3>
                <button onclick="cerrarModalAprobar()" style="background: none; border: none; color: white; font-size: 28px; cursor: pointer;">&times;</button>
            </div>
        </div>
        <div style="padding: 24px;" id="modalAprobarBody">
            {{-- Contenido dinámico --}}
        </div>
    </div>
</div>

{{-- MODAL PARA EXTENDER PRÉSTAMO --}}
<div id="modalExtender" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div style="background: white; width: 90%; max-width: 500px; border-radius: 20px;">
        <div style="background: #2a9d8f; padding: 20px 24px;">
            <h3 style="margin: 0; color: white;">📅 Extender Préstamo</h3>
        </div>
        <div style="padding: 24px;">
            <label>Nueva fecha de devolución:</label>
            <input type="date" id="nuevaFechaDevolucion" style="width: 100%; padding: 10px; margin-top: 10px; border: 1px solid #dee2e6; border-radius: 8px;">
            <label style="margin-top: 15px;">Motivo de extensión:</label>
            <textarea id="motivoExtension" rows="2" style="width: 100%; padding: 10px; margin-top: 10px; border: 1px solid #dee2e6; border-radius: 8px;"></textarea>
            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <button onclick="cerrarModalExtender()" style="flex: 1; padding: 10px; background: #e9ecef; border: none; border-radius: 8px;">Cancelar</button>
                <button onclick="confirmarExtension()" style="flex: 1; padding: 10px; background: #2a9d8f; color: white; border: none; border-radius: 8px;">Extender</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PARA DEVOLUCIÓN PARCIAL --}}
<div id="modalDevolucion" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10002; justify-content: center; align-items: center;">
    <div style="background: white; width: 95%; max-width: 700px; border-radius: 20px;">
        <div style="background: #e63946; padding: 20px 24px;">
            <h3 style="margin: 0; color: white;">🔄 Devolución de Equipos</h3>
        </div>
        <div style="padding: 24px;" id="modalDevolucionBody">
            {{-- Contenido dinámico --}}
        </div>
    </div>
</div>

<style>
    .card-solicitud {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card-solicitud:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .estado-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .estado-pendiente { background: #ffc107; color: #333; }
    .estado-aprobada { background: #2e7d32; color: white; }
    .estado-prestamo { background: #4361ee; color: white; }
    .estado-devuelto { background: #6c757d; color: white; }
    .prioridad-urgente { background: #e63946; color: white; }
    .prioridad-alta { background: #f4a261; color: white; }
    .prioridad-normal { background: #2a9d8f; color: white; }
    .prioridad-baja { background: #6c757d; color: white; }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
let solicitudActual = null;
let prestamoActual = null;
let currentTab = 'pendientes';

// Datos simulados (esto vendrá del backend)
let solicitudesPendientes = [
    {
        id: 1,
        solicitante: "Juan Pérez",
        cedula: "V-12345678",
        fecha_solicitud: "2024-01-15",
        fecha_requerida: "2024-01-20",
        prioridad: "alta",
        justificacion: "Proyecto de investigación - Necesito equipos para el laboratorio",
        items: [
            { tipo: "activo", nombre: "Dell Latitude 3420", cantidad: 2, disponibles: 5 },
            { tipo: "periferico", nombre: "Mouse Logitech", cantidad: 3, disponibles: 8 }
        ]
    },
    {
        id: 2,
        solicitante: "María García",
        cedula: "V-87654321",
        fecha_solicitud: "2024-01-16",
        fecha_requerida: "2024-01-18",
        prioridad: "urgente",
        justificacion: "Reunión con clientes importantes",
        items: [
            { tipo: "activo", nombre: "Proyector Epson", cantidad: 1, disponibles: 2 },
            { tipo: "periferico", nombre: "Webcam Logitech", cantidad: 1, disponibles: 6 }
        ]
    }
];

let solicitudesAprobadas = [];
let prestamosActivos = [
    {
        id: 1,
        solicitud_id: 1,
        solicitante: "Carlos Rodríguez",
        fecha_prestamo: "2024-01-10",
        fecha_devolucion_estimada: "2024-01-20",
        estado: "activo",
        items: [
            { tipo: "activo", nombre: "Laptop HP", serial: "HP-001", devuelto: false },
            { tipo: "activo", nombre: "Laptop HP", serial: "HP-002", devuelto: false },
            { tipo: "periferico", nombre: "Mouse", serial: "M-001", devuelto: true }
        ]
    }
];

function cambiarPestana(tab) {
    currentTab = tab;

    // Ocultar todos los contenidos
    document.getElementById('contenido-pendientes').style.display = 'none';
    document.getElementById('contenido-aprobadas').style.display = 'none';
    document.getElementById('contenido-prestamos').style.display = 'none';
    document.getElementById('contenido-historial').style.display = 'none';

    // Mostrar el seleccionado
    document.getElementById(`contenido-${tab}`).style.display = 'block';

    // Actualizar estilos de pestañas
    const tabs = ['pendientes', 'aprobadas', 'prestamos', 'historial'];
    tabs.forEach(t => {
        const btn = document.getElementById(`tab-${t}`);
        if (t === tab) {
            btn.style.borderBottomColor = '#4361ee';
            btn.style.color = '#4361ee';
        } else {
            btn.style.borderBottomColor = 'transparent';
            btn.style.color = '#6c757d';
        }
    });

    // Cargar datos según la pestaña
    if (tab === 'pendientes') cargarSolicitudesPendientes();
    if (tab === 'aprobadas') cargarSolicitudesAprobadas();
    if (tab === 'prestamos') cargarPrestamosActivos();
    if (tab === 'historial') cargarHistorial();
}

function cargarSolicitudesPendientes() {
    const container = document.getElementById('solicitudesPendientesContainer');

    if (solicitudesPendientes.length === 0) {
        container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 60px;"><div style="font-size: 48px;">✅</div><p>No hay solicitudes pendientes</p></div>`;
        return;
    }

    let html = '';
    for (const s of solicitudesPendientes) {
        const prioridadClass = s.prioridad === 'urgente' ? 'prioridad-urgente' : s.prioridad === 'alta' ? 'prioridad-alta' : s.prioridad === 'normal' ? 'prioridad-normal' : 'prioridad-baja';

        html += `
            <div class="card-solicitud" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e9ecef; background: #fafbfc;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="font-size: 16px;">Solicitud #${s.id}</strong>
                            <div style="font-size: 12px; color: #6c757d;">${s.solicitante} • ${s.cedula}</div>
                        </div>
                        <span class="estado-badge ${prioridadClass}">${s.prioridad.toUpperCase()}</span>
                    </div>
                </div>
                <div style="padding: 16px 20px;">
                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 12px; color: #6c757d;">📅 Fecha Requerida</div>
                        <div>${new Date(s.fecha_requerida).toLocaleDateString()}</div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 12px; color: #6c757d;">📝 Justificación</div>
                        <div style="font-size: 13px;">${s.justificacion.substring(0, 80)}${s.justificacion.length > 80 ? '...' : ''}</div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 12px; color: #6c757d;">📦 Items Solicitados</div>
                        ${s.items.map(item => `<div style="font-size: 13px;">• ${item.cantidad}x ${item.nombre} (Disponibles: ${item.disponibles})</div>`).join('')}
                    </div>
                </div>
                <div style="padding: 16px 20px; background: #fafbfc; border-top: 1px solid #e9ecef; display: flex; gap: 10px;">
                    <button onclick="abrirModalAprobar(${s.id})" style="flex: 1; padding: 8px; background: #2a9d8f; color: white; border: none; border-radius: 8px; cursor: pointer;">✅ Aprobar</button>
                    <button onclick="rechazarSolicitud(${s.id})" style="flex: 1; padding: 8px; background: #e63946; color: white; border: none; border-radius: 8px; cursor: pointer;">❌ Rechazar</button>
                    <button onclick="ponerEnEspera(${s.id})" style="flex: 1; padding: 8px; background: #ffc107; color: #333; border: none; border-radius: 8px; cursor: pointer;">⏳ En Espera</button>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
}

function abrirModalAprobar(id) {
    solicitudActual = solicitudesPendientes.find(s => s.id === id);
    if (!solicitudActual) return;

    const modalBody = document.getElementById('modalAprobarBody');

    let itemsHtml = '';
    for (let i = 0; i < solicitudActual.items.length; i++) {
        const item = solicitudActual.items[i];
        itemsHtml += `
            <div style="background: #f8f9fa; border-radius: 12px; padding: 15px; margin-bottom: 15px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px; align-items: center;">
                    <div><strong>${item.nombre}</strong><br><small>${item.tipo === 'activo' ? 'Activo' : 'Periférico'}</small></div>
                    <div>Solicitado: ${item.cantidad}</div>
                    <div>
                        <label>Asignar:</label>
                        <input type="number" id="asignar_${i}" value="${item.cantidad}" min="0" max="${item.disponibles}" style="width: 80px; padding: 6px; border-radius: 6px; border: 1px solid #dee2e6;">
                        <br><small>Disponible: ${item.disponibles}</small>
                    </div>
                </div>
            </div>
        `;
    }

    modalBody.innerHTML = `
        <div style="margin-bottom: 20px;">
            <h4>Solicitud #${solicitudActual.id} - ${solicitudActual.solicitante}</h4>
            <p><strong>Justificación:</strong> ${solicitudActual.justificacion}</p>
        </div>

        <h4>📦 Asignar Equipos</h4>
        <div id="itemsAsignacion">
            ${itemsHtml}
        </div>

        <div style="margin-top: 20px;">
            <label>Observaciones de aprobación:</label>
            <textarea id="observacionesAprobacion" rows="2" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px; margin-top: 5px;"></textarea>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
            <button onclick="cerrarModalAprobar()" style="padding: 10px 20px; background: #e9ecef; border: none; border-radius: 8px;">Cancelar</button>
            <button onclick="confirmarAprobacion()" style="padding: 10px 24px; background: #2a9d8f; color: white; border: none; border-radius: 8px;">✅ Confirmar Aprobación</button>
        </div>
    `;

    document.getElementById('modalAprobar').style.display = 'flex';
}

function confirmarAprobacion() {
    // Obtener cantidades asignadas
    const itemsAsignados = [];
    for (let i = 0; i < solicitudActual.items.length; i++) {
        const input = document.getElementById(`asignar_${i}`);
        const cantidadAsignada = parseInt(input.value) || 0;
        itemsAsignados.push({
            nombre: solicitudActual.items[i].nombre,
            solicitado: solicitudActual.items[i].cantidad,
            asignado: cantidadAsignada
        });
    }

    // Mover solicitud a aprobadas
    solicitudesPendientes = solicitudesPendientes.filter(s => s.id !== solicitudActual.id);
    solicitudesAprobadas.push({
        ...solicitudActual,
        fecha_aprobacion: new Date().toISOString(),
        items_asignados: itemsAsignados
    });

    cerrarModalAprobar();
    cargarSolicitudesPendientes();
    cargarSolicitudesAprobadas();

    alert('✅ Solicitud aprobada exitosamente');
}

function rechazarSolicitud(id) {
    const motivo = prompt('Motivo del rechazo:');
    if (motivo) {
        solicitudesPendientes = solicitudesPendientes.filter(s => s.id !== id);
        cargarSolicitudesPendientes();
        alert(`❌ Solicitud rechazada. Motivo: ${motivo}`);
    }
}

function ponerEnEspera(id) {
    alert('⏳ Solicitud puesta en espera. Se notificará al solicitante.');
}

function cargarSolicitudesAprobadas() {
    const container = document.getElementById('solicitudesAprobadasContainer');

    if (solicitudesAprobadas.length === 0) {
        container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 60px;"><div style="font-size: 48px;">📋</div><p>No hay solicitudes aprobadas pendientes de préstamo</p></div>`;
        return;
    }

    let html = '';
    for (const s of solicitudesAprobadas) {
        html += `
            <div class="card-solicitud" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="padding: 16px 20px; background: #2e7d32; color: white;">
                    <strong>Solicitud #${s.id} - Aprobada</strong>
                    <div style="font-size: 12px;">${new Date(s.fecha_aprobacion).toLocaleString()}</div>
                </div>
                <div style="padding: 16px 20px;">
                    <p><strong>Solicitante:</strong> ${s.solicitante}</p>
                    <p><strong>Items asignados:</strong></p>
                    ${s.items_asignados.map(item => `<div>• ${item.asignado}/${item.solicitado} x ${item.nombre}</div>`).join('')}
                </div>
                <div style="padding: 16px 20px; background: #fafbfc; border-top: 1px solid #e9ecef;">
                    <button onclick="realizarPrestamo(${s.id})" style="width: 100%; padding: 10px; background: #4361ee; color: white; border: none; border-radius: 8px; cursor: pointer;">📦 Realizar Préstamo</button>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
}

function realizarPrestamo(id) {
    const solicitud = solicitudesAprobadas.find(s => s.id === id);
    if (!solicitud) return;

    const fechaDevolucion = prompt('Fecha estimada de devolución (YYYY-MM-DD):', new Date(Date.now() + 7*24*60*60*1000).toISOString().split('T')[0]);
    if (fechaDevolucion) {
        prestamosActivos.push({
            id: prestamosActivos.length + 1,
            solicitud_id: solicitud.id,
            solicitante: solicitud.solicitante,
            fecha_prestamo: new Date().toISOString().split('T')[0],
            fecha_devolucion_estimada: fechaDevolucion,
            estado: 'activo',
            items: solicitud.items_asignados.map(item => ({
                nombre: item.nombre,
                devuelto: false
            }))
        });

        solicitudesAprobadas = solicitudesAprobadas.filter(s => s.id !== id);
        cargarSolicitudesAprobadas();
        cargarPrestamosActivos();
        alert('✅ Préstamo registrado exitosamente');
    }
}

function cargarPrestamosActivos() {
    const container = document.getElementById('prestamosActivosContainer');

    if (prestamosActivos.length === 0) {
        container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 60px;"><div style="font-size: 48px;">📦</div><p>No hay préstamos activos</p></div>`;
        return;
    }

    let html = '';
    for (const p of prestamosActivos) {
        const totalItems = p.items.length;
        const devueltos = p.items.filter(i => i.devuelto).length;

        html += `
            <div class="card-solicitud" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="padding: 16px 20px; background: #4361ee; color: white;">
                    <strong>Préstamo #${p.id}</strong>
                    <div style="font-size: 12px;">${p.solicitante}</div>
                </div>
                <div style="padding: 16px 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <div><span style="font-size: 12px; color: #6c757d;">📅 Préstamo:</span><br>${p.fecha_prestamo}</div>
                        <div><span style="font-size: 12px; color: #6c757d;">📅 Devolución:</span><br>${p.fecha_devolucion_estimada}</div>
                    </div>
                    <div style="background: #f8f9fa; border-radius: 12px; padding: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>📦 Progreso devolución:</span>
                            <strong>${devueltos}/${totalItems} equipos</strong>
                        </div>
                        <div style="background: #e9ecef; border-radius: 10px; height: 8px; overflow: hidden;">
                            <div style="background: #2a9d8f; width: ${(devueltos/totalItems)*100}%; height: 100%;"></div>
                        </div>
                    </div>
                </div>
                <div style="padding: 16px 20px; background: #fafbfc; border-top: 1px solid #e9ecef; display: flex; gap: 10px;">
                    <button onclick="abrirDevolucion(${p.id})" style="flex: 1; padding: 8px; background: #e63946; color: white; border: none; border-radius: 8px; cursor: pointer;">🔄 Devolución</button>
                    <button onclick="abrirExtender(${p.id})" style="flex: 1; padding: 8px; background: #2a9d8f; color: white; border: none; border-radius: 8px; cursor: pointer;">📅 Extender</button>
                    <button onclick="generarActaPrestamo(${p.id})" style="flex: 1; padding: 8px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer;">📄 Acta</button>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
}

function abrirExtender(id) {
    prestamoActual = prestamosActivos.find(p => p.id === id);
    if (!prestamoActual) return;

    document.getElementById('nuevaFechaDevolucion').value = prestamoActual.fecha_devolucion_estimada;
    document.getElementById('motivoExtension').value = '';
    document.getElementById('modalExtender').style.display = 'flex';
}

function confirmarExtension() {
    const nuevaFecha = document.getElementById('nuevaFechaDevolucion').value;
    const motivo = document.getElementById('motivoExtension').value;

    if (nuevaFecha && prestamoActual) {
        prestamoActual.fecha_devolucion_estimada = nuevaFecha;
        cerrarModalExtender();
        cargarPrestamosActivos();
        alert(`✅ Préstamo extendido hasta ${nuevaFecha}\nMotivo: ${motivo || 'No especificado'}`);
    }
}

function abrirDevolucion(id) {
    prestamoActual = prestamosActivos.find(p => p.id === id);
    if (!prestamoActual) return;

    const modalBody = document.getElementById('modalDevolucionBody');

    let itemsHtml = '';
    for (let i = 0; i < prestamoActual.items.length; i++) {
        const item = prestamoActual.items[i];
        itemsHtml += `
            <div style="background: #f8f9fa; border-radius: 12px; padding: 12px; margin-bottom: 10px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="devuelto_${i}" ${item.devuelto ? 'checked disabled' : ''}>
                    <span><strong>${item.nombre}</strong> - ${item.devuelto ? '✅ Devuelto' : '⏳ Pendiente'}</span>
                </label>
            </div>
        `;
    }

    modalBody.innerHTML = `
        <h4>Préstamo #${prestamoActual.id} - ${prestamoActual.solicitante}</h4>
        <div id="itemsDevolucion">
            ${itemsHtml}
        </div>
        <div style="margin-top: 20px;">
            <label>Observaciones de devolución:</label>
            <textarea id="observacionesDevolucion" rows="2" style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 8px; margin-top: 5px;"></textarea>
        </div>
        <div style="margin-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
            <button onclick="cerrarModalDevolucion()" style="padding: 10px 20px; background: #e9ecef; border: none; border-radius: 8px;">Cancelar</button>
            <button onclick="confirmarDevolucion()" style="padding: 10px 24px; background: #e63946; color: white; border: none; border-radius: 8px;">Confirmar Devolución</button>
        </div>
    `;

    document.getElementById('modalDevolucion').style.display = 'flex';
}

function confirmarDevolucion() {
    const itemsDevueltos = [];
    for (let i = 0; i < prestamoActual.items.length; i++) {
        const checkbox = document.getElementById(`devuelto_${i}`);
        if (checkbox && checkbox.checked) {
            itemsDevueltos.push(prestamoActual.items[i].nombre);
            prestamoActual.items[i].devuelto = true;
        }
    }

    const todosDevueltos = prestamoActual.items.every(i => i.devuelto);

    if (todosDevueltos) {
        // Mover a historial
        prestamosActivos = prestamosActivos.filter(p => p.id !== prestamoActual.id);
    }

    cerrarModalDevolucion();
    cargarPrestamosActivos();

    alert(`✅ Devolución registrada\nEquipos devueltos: ${itemsDevueltos.length}/${prestamoActual.items.length}`);
}

function generarActaPrestamo(id) {
    alert('📄 Generando acta de préstamo...\n\nSe descargará un PDF con:\n- Datos del solicitante\n- Equipos prestados\n- Fechas del préstamo\n- Firmas digitales');
}

function cargarHistorial() {
    const container = document.getElementById('historialContainer');
    container.innerHTML = `
        <div style="background: white; border-radius: 16px; padding: 20px; text-align: center;">
            <div style="font-size: 48px;">📜</div>
            <p>Historial de préstamos finalizados</p>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px;">ID</th>
                        <th style="padding: 12px;">Solicitante</th>
                        <th style="padding: 12px;">Fecha Préstamo</th>
                        <th style="padding: 12px;">Fecha Devolución</th>
                        <th style="padding: 12px;">Estado</th>
                        <th style="padding: 12px;">Acta</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #6c757d;">No hay préstamos en el historial</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function cerrarModalAprobar() {
    document.getElementById('modalAprobar').style.display = 'none';
}

function cerrarModalExtender() {
    document.getElementById('modalExtender').style.display = 'none';
}

function cerrarModalDevolucion() {
    document.getElementById('modalDevolucion').style.display = 'none';
}

// Inicializar
cargarSolicitudesPendientes();
</script>
@endsection
