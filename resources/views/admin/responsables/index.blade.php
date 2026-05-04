@extends('layouts.dashboard')

@section('title', 'Gestión de Responsables')

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
                                    <path d="M20 7h-4.18A3 3 0 0 0 14 5.18V4a2 2 0 0 0-2-2"/>
                                    <path d="M4 7h16v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z"/>
                                    <circle cx="12" cy="14" r="2"/>
                                </svg>
                                Gestión de Responsables
                            </h2>
                            <p class="text-white-50 mb-0">Administra las personas responsables de instituciones y departamentos</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="abrirModalCrear()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Nuevo Responsable
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
                            <label class="form-label text-muted small">Buscar responsable</label>
                            <div class="position-relative">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre, documento, email..." style="padding-left: 36px; border-radius: 10px; background: white;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Tipo</label>
                            <select id="tipoFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todos</option>
                                <option value="interno">Interno</option>
                                <option value="externo">Externo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Institución / Departamento</label>
                            <select id="entidadFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button id="limpiarFiltros" class="btn" style="background: #6c757d; color: white; border-radius: 10px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 4px;">
                                    <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted small">Mostrando <span id="resultadosCount">0</span> responsables</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de responsables -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-0 overflow-auto">
                    <table class="table table-hover mb-0" style="min-width: 1100px;">
                        <thead style="background: #f8f9fc;">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold">ID</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Nombre</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Tipo</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Entidad</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Documento</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Teléfono</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Email</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Dirección</th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">Acciones</th>
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

<!-- MODAL CREAR RESPONSABLE -->
<div id="modalCrear" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nuevo Responsable
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCrear()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formCrearResponsable">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre Completo</label>
                            <input type="text" name="nombre" id="nombreResponsable" class="form-control" placeholder="Ej: Juan Carlos Pérez" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo</label>
                            <select name="tipo" id="tipoResponsable" class="form-select" style="border-radius: 10px; background: white;" required>
                                <option value="interno">Interno (Departamento)</option>
                                <option value="externo">Externo (Institución)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="entidadContainer">
                            <label class="form-label fw-semibold" id="entidadLabel">Departamento</label>
                            <select name="entidad_id" id="entidadSelect" class="form-select" style="border-radius: 10px; background: white;" required>
                                <option value="">Seleccione un departamento</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Documento (Cédula/RIF)</label>
                            <input type="text" name="documento" id="documentoResponsable" class="form-control" placeholder="V-12345678" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="tel" name="telefono" id="telefonoResponsable" class="form-control" placeholder="0412-1234567" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="emailResponsable" class="form-control" placeholder="correo@ejemplo.com" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cargo / Departamento</label>
                            <input type="text" name="departamento" id="departamentoResponsable" class="form-control" placeholder="Director, Jefe, etc." style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección</label>
                            <textarea name="direccion" id="direccionResponsable" rows="2" class="form-control" placeholder="Dirección completa" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalCrear()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="guardarResponsable()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Guardar Responsable</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR RESPONSABLE -->
<div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>
                    Editar Responsable
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEditar()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formEditarResponsable">
                    <input type="hidden" name="id" id="editId">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre Completo</label>
                            <input type="text" name="nombre" id="editNombre" class="form-control" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo</label>
                            <select name="tipo" id="editTipo" class="form-select" style="border-radius: 10px; background: white;" required>
                                <option value="interno">Interno (Departamento)</option>
                                <option value="externo">Externo (Institución)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="editEntidadContainer">
                            <label class="form-label fw-semibold" id="editEntidadLabel">Departamento</label>
                            <select name="entidad_id" id="editEntidadSelect" class="form-select" style="border-radius: 10px; background: white;" required>
                                <option value="">Seleccione una entidad</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Documento</label>
                            <input type="text" name="documento" id="editDocumento" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="tel" name="telefono" id="editTelefono" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cargo / Departamento</label>
                            <input type="text" name="departamento" id="editDepartamento" class="form-control" style="border-radius: 10px; background: white;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección</label>
                            <textarea name="direccion" id="editDireccion" rows="2" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalEditar()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="actualizarResponsable()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Actualizar Responsable</button>
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
    .badge-tipo { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .btn:active { transform: scale(0.98); }
    .card { background: white !important; }

    .btn-accion-editar { background: rgba(255, 193, 7, 0.1); color: #8a6300; border: 1px solid rgba(255, 193, 7, 0.3); }
    .btn-accion-editar:hover { background: #ffc107; color: #1e3c72; border-color: #ffc107; }
    .btn-accion-eliminar { background: rgba(220, 53, 69, 0.1); color: #8b1a24; border: 1px solid rgba(220, 53, 69, 0.3); }
    .btn-accion-eliminar:hover { background: #dc3545; color: white; border-color: #dc3545; }
</style>

<script>
// Datos de ejemplo (departamentos e instituciones)
let departamentos = [
    { id: 1, nombre: 'Tecnología de la Información' },
    { id: 2, nombre: 'Recursos Humanos' },
    { id: 3, nombre: 'Administración' },
    { id: 4, nombre: 'Finanzas' },
    { id: 5, nombre: 'Logística' }
];

let instituciones = [
    { id: 1, nombre: 'Universidad Nacional Experimental' },
    { id: 2, nombre: 'Hospital General del Este' },
    { id: 3, nombre: 'Colegio San José' },
    { id: 4, nombre: 'Ministerio de Educación' },
    { id: 5, nombre: 'Empresa SoftSolutions' }
];

// Datos de ejemplo (responsables)
let responsables = [
    { id: 1, nombre: 'Juan Carlos Pérez', tipo: 'interno', entidad_id: 1, entidad_nombre: 'Tecnología de la Información', documento: 'V-12345678', telefono: '0412-1234567', email: 'juan.perez@empresa.com', departamento: 'Director de TI', direccion: 'Oficina 301, Torre Principal' },
    { id: 2, nombre: 'María González', tipo: 'externo', entidad_id: 1, entidad_nombre: 'Universidad Nacional Experimental', documento: 'V-87654321', telefono: '0414-7654321', email: 'maria.gonzalez@une.edu.ve', departamento: 'Coordinadora de Préstamos', direccion: 'Campus Universitario, Bloque A' },
    { id: 3, nombre: 'Carlos Rodríguez', tipo: 'interno', entidad_id: 2, entidad_nombre: 'Recursos Humanos', documento: 'V-11223344', telefono: '0424-9876543', email: 'carlos.rodriguez@empresa.com', departamento: 'Jefe de RH', direccion: 'Piso 2, Oficina 205' },
    { id: 4, nombre: 'Ana Martínez', tipo: 'externo', entidad_id: 2, entidad_nombre: 'Hospital General del Este', documento: 'V-55667788', telefono: '0416-5555555', email: 'ana.martinez@hospital.gob.ve', departamento: 'Directora Administrativa', direccion: 'Av. Principal, Edificio Administrativo' },
    { id: 5, nombre: 'Luis Mendoza', tipo: 'externo', entidad_id: 4, entidad_nombre: 'Ministerio de Educación', documento: 'V-99887766', telefono: '0212-3334444', email: 'lmendoza@mppe.gob.ve', departamento: 'Coordinador de Logística', direccion: 'Centro Simón Bolívar, Torre Norte' }
];

let responsablesFiltrados = [...responsables];
let currentPage = 1;
const itemsPorPagina = 8;

// Cargar selects de entidades según el tipo
function cargarSelectEntidades(tipo, selectId, defaultValue = null) {
    const select = document.getElementById(selectId);
    if (!select) return;

    let options = '<option value="">Seleccione una entidad</option>';
    let entidades = tipo === 'interno' ? departamentos : instituciones;

    for (const entidad of entidades) {
        const selected = (defaultValue == entidad.id) ? 'selected' : '';
        options += `<option value="${entidad.id}" ${selected}>${escapeHtml(entidad.nombre)}</option>`;
    }
    select.innerHTML = options;
}

// Actualizar label del select según el tipo
function actualizarLabelEntidad(tipo, isEdit = false) {
    const labelId = isEdit ? 'editEntidadLabel' : 'entidadLabel';
    const label = document.getElementById(labelId);
    if (label) {
        label.textContent = tipo === 'interno' ? 'Departamento' : 'Institución';
    }
}

// Manejar cambio de tipo en el formulario de creación
document.getElementById('tipoResponsable')?.addEventListener('change', function() {
    const tipo = this.value;
    actualizarLabelEntidad(tipo, false);
    cargarSelectEntidades(tipo, 'entidadSelect');
});

// Manejar cambio de tipo en el formulario de edición
document.getElementById('editTipo')?.addEventListener('change', function() {
    const tipo = this.value;
    actualizarLabelEntidad(tipo, true);
    cargarSelectEntidades(tipo, 'editEntidadSelect');
});

// Cargar filtro de entidades
function cargarFiltroEntidades() {
    const filterSelect = document.getElementById('entidadFilter');
    if (!filterSelect) return;

    let options = '<option value="">Todas</option>';
    // Agregar departamentos
    for (const depto of departamentos) {
        options += `<option value="departamento_${depto.id}">${escapeHtml(depto.nombre)} (Departamento)</option>`;
    }
    // Agregar instituciones
    for (const inst of instituciones) {
        options += `<option value="institucion_${inst.id}">${escapeHtml(inst.nombre)} (Institución)</option>`;
    }
    filterSelect.innerHTML = options;
}

function renderizarTabla() {
    const start = (currentPage - 1) * itemsPorPagina;
    const end = start + itemsPorPagina;
    const responsablesPagina = responsablesFiltrados.slice(start, end);
    const tbody = document.getElementById('tablaBody');
    const resultadosCount = document.getElementById('resultadosCount');

    resultadosCount.innerText = responsablesFiltrados.length;

    if (responsablesFiltrados.length === 0) {
        tbody.innerHTML = `<table><td colspan="9" class="text-center py-5 text-muted" style="background: white;">No hay responsables registrados<br><button onclick="abrirModalCrear()" class="btn btn-sm mt-2" style="background: #1e3c72; color: white; border-radius: 8px;"> Registrar primer responsable</button></td></tr>`;
        document.getElementById('paginacionContainer').innerHTML = '';
        return;
    }

    let html = '';
    for (const r of responsablesPagina) {
        let tipoColor = r.tipo === 'interno' ? '#0d47a1' : '#4a148c';
        let tipoBg = r.tipo === 'interno' ? '#e3f2fd' : '#f3e5f5';
        let tipoTexto = r.tipo === 'interno' ? 'Interno' : 'Externo';

        html += `
            <tr>
                <td class="px-4 py-3 fw-semibold" style="color: #1a1a2e;">#${r.id}</td>
                <td class="px-4 py-3"><span class="badge-fecha" style="background: #e3f2fd; color: #0d47a1; font-weight: 600;">${escapeHtml(r.nombre)}</span></td>
                <td class="px-4 py-3"><span class="badge-tipo" style="background: ${tipoBg}; color: ${tipoColor};">${tipoTexto}</span></td>
                <td class="px-4 py-3"><span class="badge-fecha" style="background: ${r.tipo === 'interno' ? '#fff8e1' : '#e8f5e9'}; color: ${r.tipo === 'interno' ? '#b26a00' : '#1b5e20'};">${escapeHtml(r.entidad_nombre)}</span></td>
                <td class="px-4 py-3" style="color: #495057;">${escapeHtml(r.documento || '-')}</td>
                <td class="px-4 py-3" style="color: #495057;">${escapeHtml(r.telefono || '-')}</td>
                <td class="px-4 py-3" style="color: #495057;">${escapeHtml(r.email || '-')}</td>
                <td class="px-4 py-3" style="color: #495057; max-width: 200px;">${escapeHtml(r.direccion || '-')}</td>
                <td class="px-4 py-3 text-center"><div class="d-flex gap-2 justify-content-center">
                    <button onclick="editarResponsable(${r.id})" class="btn btn-sm btn-accion-editar" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/></svg> Editar</button>
                    <button onclick="eliminarResponsable(${r.id})" class="btn btn-sm btn-accion-eliminar" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/></svg> Eliminar</button>
                </div></td>
            </tr>
        `;
    }
    tbody.innerHTML = html;
    renderizarPaginacion();
}

function renderizarPaginacion() {
    const totalPages = Math.ceil(responsablesFiltrados.length / itemsPorPagina);
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
    const tipo = document.getElementById('tipoFilter').value;
    const entidadFilter = document.getElementById('entidadFilter').value;

    responsablesFiltrados = responsables.filter(r => {
        if (searchTerm && !r.nombre.toLowerCase().includes(searchTerm) && !(r.documento || '').toLowerCase().includes(searchTerm)) return false;
        if (tipo && r.tipo !== tipo) return false;
        if (entidadFilter) {
            const [tipoEntidad, id] = entidadFilter.split('_');
            const entidadNombre = tipoEntidad === 'departamento' ? 'interno' : 'externo';
            if (r.tipo !== entidadNombre) return false;
        }
        return true;
    });
    currentPage = 1;
    renderizarTabla();
}

function escapeHtml(text) { if (!text) return ''; const div = document.createElement('div'); div.appendChild(document.createTextNode(text)); return div.innerHTML; }

function abrirModalCrear() {
    document.getElementById('formCrearResponsable').reset();
    document.getElementById('tipoResponsable').value = 'interno';
    actualizarLabelEntidad('interno', false);
    cargarSelectEntidades('interno', 'entidadSelect');
    document.getElementById('modalCrear').style.display = 'flex';
}
function cerrarModalCrear() { document.getElementById('modalCrear').style.display = 'none'; }

function guardarResponsable() {
    const nombre = document.getElementById('nombreResponsable').value;
    const tipo = document.getElementById('tipoResponsable').value;
    const entidadId = document.getElementById('entidadSelect').value;
    if (!nombre) { alert('El nombre del responsable es requerido'); return; }
    if (!entidadId) { alert('Debe seleccionar una entidad'); return; }
    alert('Funcionalidad en desarrollo. El responsable se guardará en la base de datos próximamente.');
    cerrarModalCrear();
}

function editarResponsable(id) {
    const responsable = responsables.find(r => r.id === id);
    if (responsable) {
        document.getElementById('editId').value = responsable.id;
        document.getElementById('editNombre').value = responsable.nombre;
        document.getElementById('editTipo').value = responsable.tipo;
        document.getElementById('editDocumento').value = responsable.documento || '';
        document.getElementById('editTelefono').value = responsable.telefono || '';
        document.getElementById('editEmail').value = responsable.email || '';
        document.getElementById('editDepartamento').value = responsable.departamento || '';
        document.getElementById('editDireccion').value = responsable.direccion || '';

        actualizarLabelEntidad(responsable.tipo, true);
        cargarSelectEntidades(responsable.tipo, 'editEntidadSelect', responsable.entidad_id);

        document.getElementById('modalEditar').style.display = 'flex';
    }
}

function cerrarModalEditar() { document.getElementById('modalEditar').style.display = 'none'; }

function actualizarResponsable() {
    alert('Funcionalidad en desarrollo. El responsable se actualizará en la base de datos próximamente.');
    cerrarModalEditar();
}

function eliminarResponsable(id) {
    if (confirm('¿Está seguro de que desea eliminar este responsable?')) {
        alert('Funcionalidad en desarrollo. El responsable se eliminará de la base de datos próximamente.');
    }
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
document.getElementById('tipoFilter').addEventListener('change', aplicarFiltros);
document.getElementById('entidadFilter').addEventListener('change', aplicarFiltros);
document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('tipoFilter').value = '';
    document.getElementById('entidadFilter').value = '';
    responsablesFiltrados = [...responsables];
    currentPage = 1;
    renderizarTabla();
});

document.getElementById('modalCrear')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalCrear(); });
document.getElementById('modalEditar')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalEditar(); });

// Inicializar
cargarFiltroEntidades();
renderizarTabla();
</script>
@endsection
