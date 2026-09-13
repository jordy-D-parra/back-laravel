@extends('layouts.dashboard')

@section('title', 'Gestión de Componentes')

@section('content')
<div class="container-fluid px-4">
    <!-- Cabecera -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="text-white mb-2 d-flex align-items-center gap-2">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M12 2v2M12 20v2M22 12h-2M4 12H2"/>
                                </svg>
                                Gestión de Componentes
                            </h2>
                            <p class="text-white-50 mb-0">Administra los componentes, repuestos y accesorios de los equipos</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="abrirModalCrear()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Nuevo Componente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success border-0 rounded-4" style="background: #d4edda; color: #155724; border-left: 4px solid #28a745;">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-danger border-0 rounded-4" style="background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545;">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Panel de filtros -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Buscar componente</label>
                            <div class="position-relative">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text" id="searchInput" class="form-control" placeholder="Buscar por tipo, serial..." style="padding-left: 36px; border-radius: 10px; background: white;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Estado</label>
                            <select id="estadoFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todos</option>
                                <option value="disponible">Disponible</option>
                                <option value="asignado">Asignado</option>
                                <option value="mantenimiento">En mantenimiento</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <button id="limpiarFiltros" class="btn" style="background: #6c757d; color: white; border-radius: 10px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 4px;">
                                    <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted small">Mostrando <span id="resultadosCount">0</span> componentes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de componentes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-0 overflow-auto">
                    <table class="table table-hover mb-0" style="min-width: 1000px;">
                        <thead style="background: #f8f9fc;">
                            <tr>
                                <th class="px-3 py-3 text-muted small fw-semibold">ID</th>
                                <th class="px-3 py-3 text-muted small fw-semibold">Tipo</th>
                                <th class="px-3 py-3 text-muted small fw-semibold">Serial</th>
                                <th class="px-3 py-3 text-muted small fw-semibold">Marca</th>
                                <th class="px-3 py-3 text-muted small fw-semibold">Modelo</th>
                                <th class="px-3 py-3 text-muted small fw-semibold">Capacidad</th>
                                <th class="px-3 py-3 text-muted small fw-semibold">Estado</th>
                                <th class="px-3 py-3 text-muted small fw-semibold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody" style="background: white;">
                            <!-- Datos dinámicos -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div id="paginacionContainer" class="d-flex justify-content-center"></div>
        </div>
    </div>
</div>

<!-- MODAL CREAR COMPONENTE -->
<div id="modalCrear" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 550px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nuevo Componente
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCrear()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formCrearComponente">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Componente</label>
                            <input type="text" name="tipo" id="tipoComponente" class="form-control" placeholder="Ej: Batería, Cargador, Cable, Mouse, Teclado, Fuente, Disco, Memoria RAM" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Serial / Número de serie</label>
                            <input type="text" name="serial" id="serialComponente" class="form-control" placeholder="Ej: SN-12345678" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Marca</label>
                            <input type="text" name="marca" id="marcaComponente" class="form-control" placeholder="Ej: HP, Dell, Samsung" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Modelo</label>
                            <input type="text" name="modelo" id="modeloComponente" class="form-control" placeholder="Modelo del componente" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Capacidad / Especificación</label>
                            <input type="text" name="capacidad" id="capacidadComponente" class="form-control" placeholder="Ej: 480GB, 16GB, 65W" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="estado" id="estadoComponente" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="disponible">Disponible</option>
                                <option value="asignado">Asignado</option>
                                <option value="mantenimiento">En mantenimiento</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Especificaciones Técnicas</label>
                            <textarea name="especificaciones" id="especificacionesComponente" rows="2" class="form-control" placeholder="Detalles adicionales del componente" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalCrear()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="guardarComponente()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Guardar Componente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR COMPONENTE -->
<div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 550px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>
                    Editar Componente
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEditar()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formEditarComponente">
                    <input type="hidden" name="id" id="editId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Componente</label>
                            <input type="text" name="tipo" id="editTipo" class="form-control" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Serial</label>
                            <input type="text" name="serial" id="editSerial" class="form-control" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Marca</label>
                            <input type="text" name="marca" id="editMarca" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Modelo</label>
                            <input type="text" name="modelo" id="editModelo" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Capacidad</label>
                            <input type="text" name="capacidad" id="editCapacidad" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="estado" id="editEstado" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="disponible">Disponible</option>
                                <option value="asignado">Asignado</option>
                                <option value="mantenimiento">En mantenimiento</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Especificaciones</label>
                            <textarea name="especificaciones" id="editEspecificaciones" rows="2" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalEditar()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="actualizarComponente()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Actualizar Componente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .table tbody tr { transition: all 0.2s ease; animation: fadeInUp 0.25s ease; background: white; }
    .table tbody tr:hover { background-color: #f8f9fc !important; transform: scale(1.01); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .badge-estado { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .btn:active { transform: scale(0.98); }
    .card { background: white !important; }

    .btn-accion-editar { background: rgba(255, 193, 7, 0.1); color: #8a6300; border: 1px solid rgba(255, 193, 7, 0.3); }
    .btn-accion-editar:hover { background: #ffc107; color: #1e3c72; border-color: #ffc107; }
    .btn-accion-eliminar { background: rgba(220, 53, 69, 0.1); color: #8b1a24; border: 1px solid rgba(220, 53, 69, 0.3); }
    .btn-accion-eliminar:hover { background: #dc3545; color: white; border-color: #dc3545; }

    .texto-info {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.3;
    }
</style>

<script>
// Datos de ejemplo (componentes)
let componentes = [
    { id: 1, tipo: 'Batería', marca: 'HP', modelo: 'HSTNN-LB6V', capacidad: '41Wh', serial: 'BAT-HP-001', estado: 'disponible', especificaciones: 'Batería original HP 3 celdas' },
    { id: 2, tipo: 'Cargador', marca: 'Dell', modelo: 'LA65NM170', capacidad: '65W', serial: 'CHG-DELL-001', estado: 'asignado', especificaciones: 'Cargador original Dell' },
    { id: 3, tipo: 'Cable', marca: 'Samsung', modelo: 'CC-100', capacidad: '1.5m', serial: 'CBL-SAM-001', estado: 'disponible', especificaciones: 'Cable USB-C a USB-C' },
    { id: 4, tipo: 'Disco SSD', marca: 'Kingston', modelo: 'SA400', capacidad: '480GB', serial: 'SSD-KIN-001', estado: 'disponible', especificaciones: 'SSD SATA 2.5 pulgadas' },
    { id: 5, tipo: 'Memoria RAM', marca: 'Corsair', modelo: 'Vengeance', capacidad: '16GB', serial: 'RAM-COR-001', estado: 'asignado', especificaciones: 'DDR4 3200MHz' },
    { id: 6, tipo: 'Mouse', marca: 'Logitech', modelo: 'M185', capacidad: 'Inalámbrico', serial: 'MOU-LOG-001', estado: 'disponible', especificaciones: 'Mouse óptico inalámbrico' },
    { id: 7, tipo: 'Teclado', marca: 'HP', modelo: 'K1500', capacidad: 'USB', serial: 'KEY-HP-001', estado: 'mantenimiento', especificaciones: 'Teclado básico con cable' },
    { id: 8, tipo: 'Fuente de poder', marca: 'EVGA', modelo: '500W', capacidad: '500W', serial: 'PSU-EVGA-001', estado: 'disponible', especificaciones: 'Fuente de poder 80 Plus' }
];

let componentesFiltrados = [...componentes];
let currentPage = 1;
const itemsPorPagina = 8;

function renderizarTabla() {
    const start = (currentPage - 1) * itemsPorPagina;
    const end = start + itemsPorPagina;
    const componentesPagina = componentesFiltrados.slice(start, end);
    const tbody = document.getElementById('tablaBody');
    const resultadosCount = document.getElementById('resultadosCount');

    resultadosCount.innerText = componentesFiltrados.length;

    if (componentesFiltrados.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted" style="background: white;">No hay componentes registrados<br><button onclick="abrirModalCrear()" class="btn btn-sm mt-2" style="background: #1e3c72; color: white; border-radius: 8px;"> Registrar primer componente</button></td></tr>`;
        document.getElementById('paginacionContainer').innerHTML = '';
        return;
    }

    let html = '';
    for (const c of componentesPagina) {
        let estadoColor = c.estado === 'disponible' ? '#1b5e20' : (c.estado === 'asignado' ? '#b26a00' : '#8b1a24');
        let estadoBg = c.estado === 'disponible' ? '#e8f5e9' : (c.estado === 'asignado' ? '#fff8e1' : '#ffebee');
        let estadoTexto = c.estado === 'disponible' ? 'Disponible' : (c.estado === 'asignado' ? 'Asignado' : 'Mantenimiento');

        let tipoColor = '#0d47a1';
        let tipoBg = '#e3f2fd';

        html += `
            <tr>
                <td class="px-3 py-3 fw-semibold" style="color: #1a1a2e;">#${c.id}</td>
                <td class="px-3 py-3">
                    <div class="fw-semibold" style="color: #1e3c72;">${escapeHtml(c.tipo)}</div>
                    <div class="texto-info">${escapeHtml(c.especificaciones || '')}</div>
                </td>
                <td class="px-3 py-3"><code class="small">${escapeHtml(c.serial)}</code></td>
                <td class="px-3 py-3">${escapeHtml(c.marca || '-')}</td>
                <td class="px-3 py-3">${escapeHtml(c.modelo || '-')}</td>
                <td class="px-3 py-3">${escapeHtml(c.capacidad || '-')}</td>
                <td class="px-3 py-3"><span class="badge-estado" style="background: ${estadoBg}; color: ${estadoColor};">${estadoTexto}</span></td>
                <td class="px-3 py-3 text-center">
                    <div class="d-flex gap-2 justify-content-center">
                        <button onclick="editarComponente(${c.id})" class="btn btn-sm btn-accion-editar" style="border-radius: 8px; padding: 5px 10px;" title="Editar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/></svg>
                        </button>
                        <button onclick="eliminarComponente(${c.id})" class="btn btn-sm btn-accion-eliminar" style="border-radius: 8px; padding: 5px 10px;" title="Eliminar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
    tbody.innerHTML = html;
    renderizarPaginacion();
}

function renderizarPaginacion() {
    const totalPages = Math.ceil(componentesFiltrados.length / itemsPorPagina);
    const container = document.getElementById('paginacionContainer');
    if (totalPages <= 1) { container.innerHTML = ''; return; }
    let html = '<div class="pagination d-flex gap-1">';
    for (let i = 1; i <= totalPages; i++) {
        html += `<button onclick="cambiarPagina(${i})" class="btn btn-sm" style="border: 1px solid #dee2e6; background: ${i === currentPage ? 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)' : 'white'}; color: ${i === currentPage ? 'white' : '#1e3c72'}; border-radius: 8px;">${i}</button>`;
    }
    html += '</div>';
    container.innerHTML = html;
}

function cambiarPagina(page) { currentPage = page; renderizarTabla(); window.scrollTo({ top: 0, behavior: 'smooth' }); }

function aplicarFiltros() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const estado = document.getElementById('estadoFilter').value;

    componentesFiltrados = componentes.filter(c => {
        if (searchTerm && !c.tipo.toLowerCase().includes(searchTerm) && !c.serial.toLowerCase().includes(searchTerm)) return false;
        if (estado && c.estado !== estado) return false;
        return true;
    });
    currentPage = 1;
    renderizarTabla();
}

function escapeHtml(text) { if (!text) return ''; const div = document.createElement('div'); div.appendChild(document.createTextNode(text)); return div.innerHTML; }

function abrirModalCrear() {
    document.getElementById('formCrearComponente').reset();
    document.getElementById('modalCrear').style.display = 'flex';
}
function cerrarModalCrear() { document.getElementById('modalCrear').style.display = 'none'; }

function guardarComponente() {
    const tipo = document.getElementById('tipoComponente').value;
    const serial = document.getElementById('serialComponente').value;
    if (!tipo) { alert('El tipo de componente es requerido'); return; }
    if (!serial) { alert('El serial del componente es requerido'); return; }
    alert('Funcionalidad en desarrollo. El componente se guardará en la base de datos próximamente.');
    cerrarModalCrear();
}

function editarComponente(id) {
    const componente = componentes.find(c => c.id === id);
    if (componente) {
        document.getElementById('editId').value = componente.id;
        document.getElementById('editTipo').value = componente.tipo;
        document.getElementById('editSerial').value = componente.serial;
        document.getElementById('editMarca').value = componente.marca || '';
        document.getElementById('editModelo').value = componente.modelo || '';
        document.getElementById('editCapacidad').value = componente.capacidad || '';
        document.getElementById('editEstado').value = componente.estado;
        document.getElementById('editEspecificaciones').value = componente.especificaciones || '';
        document.getElementById('modalEditar').style.display = 'flex';
    }
}

function cerrarModalEditar() { document.getElementById('modalEditar').style.display = 'none'; }

function actualizarComponente() {
    alert('Funcionalidad en desarrollo. El componente se actualizará en la base de datos próximamente.');
    cerrarModalEditar();
}

function eliminarComponente(id) {
    if (confirm('¿Está seguro de que desea eliminar este componente?')) {
        alert('Funcionalidad en desarrollo. El componente se eliminará de la base de datos próximamente.');
    }
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
document.getElementById('estadoFilter').addEventListener('change', aplicarFiltros);
document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('estadoFilter').value = '';
    componentesFiltrados = [...componentes];
    currentPage = 1;
    renderizarTabla();
});

document.getElementById('modalCrear')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalCrear(); });
document.getElementById('modalEditar')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalEditar(); });

// Inicializar
renderizarTabla();
</script>
@endsection
