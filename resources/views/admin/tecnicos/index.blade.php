@extends('layouts.dashboard')

@section('title', 'Gestión de Técnicos')

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
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    <path d="M12 12v10"/>
                                    <circle cx="12" cy="12" r="4"/>
                                </svg>
                                Gestión de Técnicos
                            </h2>
                            <p class="text-white-50 mb-0">Personal de soporte técnico y mantenimiento</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="abrirModalCrear()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Nuevo Técnico
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de filtros -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Buscar</label>
                            <div class="position-relative">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text" id="searchInput" class="form-control" placeholder="Nombre, apellido, cédula..." style="padding-left: 36px; border-radius: 10px; background: white;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Estado</label>
                            <select id="estadoFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button id="limpiarFiltros" class="btn" style="background: #6c757d; color: white; border-radius: 10px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 4px;">
                                    <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                                </svg>
                                Limpiar Filtros
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted small">Mostrando <span id="resultadosCount">0</span> técnicos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de técnicos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-0 overflow-auto">
                    <table class="table table-hover mb-0" style="min-width: 1000px;">
                        <thead style="background: #f8f9fc;">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold">ID</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Técnico</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Cédula</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Departamento</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Cargo</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Email</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Teléfono</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Estado</th>
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

<!-- MODAL CREAR TÉCNICO (mismos campos que usuario, sin contraseña ni preguntas) -->
<div id="modalCrear" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Registrar Nuevo Técnico
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCrear()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formCrearTecnico">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Apellido</label>
                            <input type="text" class="form-control" name="apellido" required style="border-radius: 10px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Cédula</label>
                        <input type="text" class="form-control" name="cedula" placeholder="V-12345678" required style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Departamento</label>
                        <input type="text" class="form-control" name="departamento" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Cargo</label>
                        <input type="text" class="form-control" name="cargo" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Email</label>
                        <input type="email" class="form-control" name="email" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Teléfono</label>
                        <input type="tel" class="form-control" name="telefono" placeholder="0412-1234567" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Estado</label>
                        <select name="activo" class="form-select" style="border-radius: 10px;">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalCrear()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="guardarTecnico()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Guardar Técnico</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR TÉCNICO -->
<div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>
                    Editar Técnico
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEditar()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formEditarTecnico">
                    <input type="hidden" name="id" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nombre</label>
                            <input type="text" class="form-control" id="editNombre" style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Apellido</label>
                            <input type="text" class="form-control" id="editApellido" style="border-radius: 10px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Cédula</label>
                        <input type="text" class="form-control" id="editCedula" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Departamento</label>
                        <input type="text" class="form-control" id="editDepartamento" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Cargo</label>
                        <input type="text" class="form-control" id="editCargo" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Email</label>
                        <input type="email" class="form-control" id="editEmail" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Teléfono</label>
                        <input type="tel" class="form-control" id="editTelefono" style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Estado</label>
                        <select id="editActivo" class="form-select" style="border-radius: 10px;">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalEditar()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="actualizarTecnico()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VER COMPONENTES DEL TÉCNICO -->
<div id="modalComponentes" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10002; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Componentes del Técnico
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalComponentes()"></button>
            </div>
            <div class="modal-body p-4" id="modalComponentesBody" style="background: white;">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>

<style>
    .table tbody tr {
        transition: all 0.2s ease;
        animation: fadeInUp 0.25s ease;
        background: white;
    }
    .table tbody tr:hover {
        background-color: #f8f9fc !important;
        transform: scale(1.01);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .badge-estado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .btn:active { transform: scale(0.98); }
    .card { background: white !important; }
</style>

<script>
// Datos de ejemplo (técnicos)
let tecnicos = [
    {
        id: 1,
        nombre: 'Juan',
        apellido: 'Pérez',
        cedula: 'V-12345678',
        departamento: 'Tecnología de la Información',
        cargo: 'Técnico de Sistemas',
        email: 'juan.perez@tecnico.com',
        telefono: '0412-1234567',
        activo: true,
        componentes: [
            { id: 1, tipo: 'Disco SSD', marca: 'Kingston', modelo: 'A400', capacidad: '480GB', estado: 'asignado' }
        ]
    },
    {
        id: 2,
        nombre: 'María',
        apellido: 'González',
        cedula: 'V-87654321',
        departamento: 'Soporte Técnico',
        cargo: 'Técnica de Soporte',
        email: 'maria.gonzalez@tecnico.com',
        telefono: '0414-7654321',
        activo: true,
        componentes: [
            { id: 2, tipo: 'Memoria RAM', marca: 'Corsair', modelo: 'Vengeance', capacidad: '16GB', estado: 'asignado' },
            { id: 3, tipo: 'Disco SSD', marca: 'Samsung', modelo: 'Evo', capacidad: '1TB', estado: 'asignado' }
        ]
    },
    {
        id: 3,
        nombre: 'Carlos',
        apellido: 'Rodríguez',
        cedula: 'V-11223344',
        departamento: 'Redes',
        cargo: 'Técnico de Redes',
        email: 'carlos.rodriguez@tecnico.com',
        telefono: '0424-9876543',
        activo: false,
        componentes: []
    },
    {
        id: 4,
        nombre: 'Ana',
        apellido: 'Martínez',
        cedula: 'V-55667788',
        departamento: 'Mantenimiento',
        cargo: 'Técnica de Hardware',
        email: 'ana.martinez@tecnico.com',
        telefono: '0416-5555555',
        activo: true,
        componentes: []
    },
];

let tecnicosFiltrados = [...tecnicos];
let currentPage = 1;
const itemsPorPagina = 8;

function renderizarTabla() {
    const start = (currentPage - 1) * itemsPorPagina;
    const end = start + itemsPorPagina;
    const tecnicosPagina = tecnicosFiltrados.slice(start, end);
    const tbody = document.getElementById('tablaBody');
    const resultadosCount = document.getElementById('resultadosCount');

    resultadosCount.innerText = tecnicosFiltrados.length;

    if (tecnicosFiltrados.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted" style="background: white;">No hay técnicos registrados<br><button onclick="abrirModalCrear()" class="btn btn-sm mt-2" style="background: #1e3c72; color: white; border-radius: 8px;">Registrar técnico</button></td></tr>`;
        document.getElementById('paginacionContainer').innerHTML = '';
        return;
    }

    let html = '';
    for (const t of tecnicosPagina) {
        const nombreCompleto = `${t.nombre} ${t.apellido}`;
        let estadoColor = t.activo ? '#28a745' : '#dc3545';
        let estadoBg = t.activo ? '#d4edda' : '#f8d7da';
        let estadoTexto = t.activo ? 'Activo' : 'Inactivo';

        html += `
            <tr>
                <td class="px-4 py-3 fw-semibold" style="background: white;">#${t.id}</td>
                <td class="px-4 py-3" style="background: white;">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 35px; height: 35px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div>
                            <strong>${escapeHtml(nombreCompleto)}</strong>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3" style="background: white;">${t.cedula}</td>
                <td class="px-4 py-3" style="background: white;">${t.departamento || '-'}</td>
                <td class="px-4 py-3" style="background: white;">${t.cargo || '-'}</td>
                <td class="px-4 py-3" style="background: white;">${t.email || '-'}</td>
                <td class="px-4 py-3" style="background: white;">${t.telefono || '-'}</td>
                <td class="px-4 py-3" style="background: white;">
                    <span class="badge-estado" style="background: ${estadoBg}; color: ${estadoColor};">
                        ${estadoTexto}
                    </span>
                </td>
                <td class="px-4 py-3 text-center" style="background: white;">
                    <div class="d-flex gap-2 justify-content-center">
                        <button onclick="verComponentes(${t.id})" class="btn btn-sm" style="background: #17a2b8; color: white; border-radius: 8px;" title="Ver componentes">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                        <button onclick="editarTecnico(${t.id})" class="btn btn-sm" style="background: #ffc107; color: #1e3c72; border-radius: 8px;" title="Editar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                                <polyline points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                            </svg>
                        </button>
                        <button onclick="eliminarTecnico(${t.id})" class="btn btn-sm" style="background: #dc3545; color: white; border-radius: 8px;" title="Eliminar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/>
                            </svg>
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
    const totalPages = Math.ceil(tecnicosFiltrados.length / itemsPorPagina);
    const container = document.getElementById('paginacionContainer');

    if (totalPages <= 1) { container.innerHTML = ''; return; }

    let html = '<div class="pagination">';
    for (let i = 1; i <= totalPages; i++) {
        html += `<button onclick="cambiarPagina(${i})" class="btn btn-sm mx-1" style="border: 1px solid #dee2e6; background: ${i === currentPage ? '#1e3c72' : 'white'}; color: ${i === currentPage ? 'white' : '#1e3c72'}; border-radius: 8px;">${i}</button>`;
    }
    html += '</div>';
    container.innerHTML = html;
}

function cambiarPagina(page) {
    currentPage = page;
    renderizarTabla();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function aplicarFiltros() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const estado = document.getElementById('estadoFilter').value;

    tecnicosFiltrados = tecnicos.filter(t => {
        const nombreCompleto = `${t.nombre} ${t.apellido}`.toLowerCase();
        if (searchTerm && !nombreCompleto.includes(searchTerm) && !t.cedula.includes(searchTerm)) return false;
        if (estado) {
            const estadoBool = estado === 'activo';
            if (t.activo !== estadoBool) return false;
        }
        return true;
    });

    currentPage = 1;
    renderizarTabla();
}

function abrirModalCrear() {
    document.getElementById('formCrearTecnico').reset();
    document.getElementById('modalCrear').style.display = 'flex';
}

function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
}

function guardarTecnico() {
    alert('Funcionalidad en desarrollo. Los datos se guardarán en la base de datos próximamente.\n\nCampos: Nombre, Apellido, Cédula, Departamento, Cargo, Email, Teléfono');
    cerrarModalCrear();
}

function editarTecnico(id) {
    const tecnico = tecnicos.find(t => t.id === id);
    if (tecnico) {
        document.getElementById('editId').value = tecnico.id;
        document.getElementById('editNombre').value = tecnico.nombre;
        document.getElementById('editApellido').value = tecnico.apellido;
        document.getElementById('editCedula').value = tecnico.cedula;
        document.getElementById('editDepartamento').value = tecnico.departamento || '';
        document.getElementById('editCargo').value = tecnico.cargo || '';
        document.getElementById('editEmail').value = tecnico.email || '';
        document.getElementById('editTelefono').value = tecnico.telefono || '';
        document.getElementById('editActivo').value = tecnico.activo ? '1' : '0';
        document.getElementById('modalEditar').style.display = 'flex';
    }
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}

function actualizarTecnico() {
    alert('Funcionalidad en desarrollo. Los datos se actualizarán en la base de datos próximamente.');
    cerrarModalEditar();
}

function eliminarTecnico(id) {
    if (confirm('¿Está seguro de que desea desactivar este técnico?')) {
        alert('Funcionalidad en desarrollo. Los técnicos se desactivan desde esta pantalla próximamente.');
    }
}

function verComponentes(id) {
    const tecnico = tecnicos.find(t => t.id === id);
    const modal = document.getElementById('modalComponentes');
    const modalBody = document.getElementById('modalComponentesBody');

    if (tecnico.componentes && tecnico.componentes.length > 0) {
        let html = '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>Tipo</th><th>Marca</th><th>Modelo</th><th>Capacidad</th><th>Estado</th><th>Acción</th></tr></thead><tbody>';
        for (const comp of tecnico.componentes) {
            html += `<tr>
                <td>${comp.tipo}</td>
                <td>${comp.marca || '-'}</td>
                <td>${comp.modelo || '-'}</td>
                <td>${comp.capacidad || '-'}</td>
                <td><span class="badge" style="background: #17a2b8; color: white;">${comp.estado}</span></td>
                <td><button class="btn btn-sm btn-danger" onclick="alert('Devolver componente ${comp.tipo}')">Devolver</button></td>
            </tr>`;
        }
        html += '</tbody></table></div>';
        modalBody.innerHTML = html;
    } else {
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <p class="mt-3 text-muted">Este técnico no tiene componentes asignados</p>
                <button class="btn btn-sm btn-primary mt-2" style="border-radius: 8px;" onclick="alert('Asignar componente a ${tecnico.nombre} ${tecnico.apellido}')">Asignar Componente</button>
            </div>
        `;
    }
    modal.style.display = 'flex';
}

function cerrarModalComponentes() {
    document.getElementById('modalComponentes').style.display = 'none';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
document.getElementById('estadoFilter').addEventListener('change', aplicarFiltros);
document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('estadoFilter').value = '';
    tecnicosFiltrados = [...tecnicos];
    currentPage = 1;
    renderizarTabla();
});

// Cerrar modales al hacer clic fuera
document.getElementById('modalCrear')?.addEventListener('click', function(e) {
    if(e.target === this) cerrarModalCrear();
});
document.getElementById('modalEditar')?.addEventListener('click', function(e) {
    if(e.target === this) cerrarModalEditar();
});
document.getElementById('modalComponentes')?.addEventListener('click', function(e) {
    if(e.target === this) cerrarModalComponentes();
});

// Inicializar
renderizarTabla();
</script>
@endsection
