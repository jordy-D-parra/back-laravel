@extends('layouts.dashboard')

@section('title', 'Gestión de Marcas')

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
                                    <path d="M8 8h8M8 12h6M8 16h4"/>
                                    <circle cx="12" cy="12" r="2"/>
                                </svg>
                                Gestión de Marcas
                            </h2>
                            <p class="text-white-50 mb-0">Administra las marcas de equipos y visualiza sus modelos</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="abrirModalCrear()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Nueva Marca
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
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Buscar marca</label>
                            <div class="position-relative">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre..." style="padding-left: 36px; border-radius: 10px; background: white;">
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
                                Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted small">Mostrando <span id="resultadosCount">0</span> marcas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de marcas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-0 overflow-auto">
                    <table class="table table-hover mb-0" style="min-width: 700px;">
                        <thead style="background: #f8f9fc;">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold">ID</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Nombre</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Descripción</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Estado</th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">Modelos</th>
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

<!-- MODAL CREAR MARCA -->
<div id="modalCrear" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nueva Marca
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCrear()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formCrearMarca">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre de la Marca</label>
                            <input type="text" name="nombre" id="nombreMarca" class="form-control" placeholder="Ej: HP, Dell, Samsung, Epson" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" id="descripcionMarca" rows="3" class="form-control" placeholder="Información adicional sobre la marca" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="activo" id="activoMarca" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalCrear()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="guardarMarca()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Guardar Marca</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR MARCA -->
<div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>
                    Editar Marca
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEditar()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formEditarMarca">
                    <input type="hidden" name="id" id="editId">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre de la Marca</label>
                            <input type="text" name="nombre" id="editNombre" class="form-control" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" id="editDescripcion" rows="3" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="activo" id="editActivo" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalEditar()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="actualizarMarca()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Actualizar Marca</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VER MODELOS -->
<div id="modalModelos" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10002; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 550px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M8 8h8M8 12h6M8 16h4"/>
                    </svg>
                    Modelos de la Marca
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalModelos()"></button>
            </div>
            <div class="modal-body p-4" id="modalModelosBody" style="background: white;">
                <!-- Contenido dinámico -->
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

    .btn-accion-ver { background: rgba(23, 162, 184, 0.1); color: #0c5c6e; border: 1px solid rgba(23, 162, 184, 0.3); }
    .btn-accion-ver:hover { background: #17a2b8; color: white; border-color: #17a2b8; }
    .btn-accion-editar { background: rgba(255, 193, 7, 0.1); color: #8a6300; border: 1px solid rgba(255, 193, 7, 0.3); }
    .btn-accion-editar:hover { background: #ffc107; color: #1e3c72; border-color: #ffc107; }
    .btn-accion-eliminar { background: rgba(220, 53, 69, 0.1); color: #8b1a24; border: 1px solid rgba(220, 53, 69, 0.3); }
    .btn-accion-eliminar:hover { background: #dc3545; color: white; border-color: #dc3545; }
</style>

<script>
// Datos de ejemplo (marcas con sus modelos)
let marcas = [
    {
        id: 1,
        nombre: 'HP',
        descripcion: 'Hewlett-Packard, equipos de computación',
        activo: true,
        modelos: [
            { id: 1, nombre: 'ProBook 450', descripcion: 'Laptop empresarial' },
            { id: 2, nombre: 'EliteBook 840', descripcion: 'Laptop premium' },
            { id: 3, nombre: 'Pavilion 15', descripcion: 'Laptop de consumo' }
        ]
    },
    {
        id: 2,
        nombre: 'Dell',
        descripcion: 'Computadoras y periféricos',
        activo: true,
        modelos: [
            { id: 4, nombre: 'Latitude 3420', descripcion: 'Laptop empresarial' },
            { id: 5, nombre: 'XPS 13', descripcion: 'Ultrabook premium' },
            { id: 6, nombre: 'OptiPlex 3080', descripcion: 'Desktop de oficina' }
        ]
    },
    {
        id: 3,
        nombre: 'Samsung',
        descripcion: 'Electrónica y monitores',
        activo: true,
        modelos: [
            { id: 7, nombre: 'Odyssey G7', descripcion: 'Monitor gaming' },
            { id: 8, nombre: 'T55', descripcion: 'Monitor curvo' }
        ]
    },
    {
        id: 4,
        nombre: 'Epson',
        descripcion: 'Impresoras y proyectores',
        activo: false,
        modelos: [
            { id: 9, nombre: 'EcoTank L3150', descripcion: 'Impresora multifuncional' },
            { id: 10, nombre: 'WorkForce Pro', descripcion: 'Impresora empresarial' }
        ]
    },
    {
        id: 5,
        nombre: 'Lenovo',
        descripcion: 'Laptops y equipos de oficina',
        activo: true,
        modelos: [
            { id: 11, nombre: 'ThinkPad T14', descripcion: 'Laptop empresarial' },
            { id: 12, nombre: 'IdeaPad 3', descripcion: 'Laptop de consumo' },
            { id: 13, nombre: 'Legion 5', descripcion: 'Laptop gaming' }
        ]
    },
];

let marcasFiltradas = [...marcas];
let currentPage = 1;
const itemsPorPagina = 8;

function renderizarTabla() {
    const start = (currentPage - 1) * itemsPorPagina;
    const end = start + itemsPorPagina;
    const marcasPagina = marcasFiltradas.slice(start, end);
    const tbody = document.getElementById('tablaBody');
    const resultadosCount = document.getElementById('resultadosCount');

    resultadosCount.innerText = marcasFiltradas.length;

    if (marcasFiltradas.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted" style="background: white;">No hay marcas registradas<br><button onclick="abrirModalCrear()" class="btn btn-sm mt-2" style="background: #1e3c72; color: white; border-radius: 8px;"> Registrar primera marca</button></td></tr>`;
        document.getElementById('paginacionContainer').innerHTML = '';
        return;
    }

    let html = '';
    for (const m of marcasPagina) {
        let estadoColor = m.activo ? '#1b5e20' : '#8b1a24';
        let estadoBg = m.activo ? '#e8f5e9' : '#ffebee';
        let estadoTexto = m.activo ? 'Activo' : 'Inactivo';
        let totalModelos = m.modelos?.length || 0;

        html += `
            <tr>
                <td class="px-4 py-3 fw-semibold" style="color: #1a1a2e;">#${m.id}</td>
                <td class="px-4 py-3"><span class="badge-fecha" style="background: #e3f2fd; color: #0d47a1; font-weight: 600;">${escapeHtml(m.nombre)}</span></td>
                <td class="px-4 py-3" style="color: #495057;">${escapeHtml(m.descripcion || '-')}</td>
                <td class="px-4 py-3"><span class="badge-estado" style="background: ${estadoBg}; color: ${estadoColor};">${estadoTexto}</span></td>
                <td class="px-4 py-3 text-center"><button onclick="verModelos(${m.id})" class="btn btn-sm btn-accion-ver" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="12" cy="12" r="3"/></svg> ${totalModelos} modelos</button></td>
                <td class="px-4 py-3 text-center"><div class="d-flex gap-2 justify-content-center">
                    <button onclick="editarMarca(${m.id})" class="btn btn-sm btn-accion-editar" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/></svg> Editar</button>
                    <button onclick="eliminarMarca(${m.id})" class="btn btn-sm btn-accion-eliminar" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/></svg> Eliminar</button>
                </div></td>
            </tr>
        `;
    }
    tbody.innerHTML = html;
    renderizarPaginacion();
}

function renderizarPaginacion() {
    const totalPages = Math.ceil(marcasFiltradas.length / itemsPorPagina);
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

    marcasFiltradas = marcas.filter(m => {
        if (searchTerm && !m.nombre.toLowerCase().includes(searchTerm)) return false;
        if (estado) {
            const estadoBool = estado === 'activo';
            if (m.activo !== estadoBool) return false;
        }
        return true;
    });
    currentPage = 1;
    renderizarTabla();
}

function escapeHtml(text) { if (!text) return ''; const div = document.createElement('div'); div.appendChild(document.createTextNode(text)); return div.innerHTML; }

function abrirModalCrear() { document.getElementById('formCrearMarca').reset(); document.getElementById('modalCrear').style.display = 'flex'; }
function cerrarModalCrear() { document.getElementById('modalCrear').style.display = 'none'; }

function guardarMarca() {
    const nombre = document.getElementById('nombreMarca').value;
    if (!nombre) { alert('El nombre de la marca es requerido'); return; }
    alert('Funcionalidad en desarrollo. La marca se guardará en la base de datos próximamente.');
    cerrarModalCrear();
}

function editarMarca(id) {
    const marca = marcas.find(m => m.id === id);
    if (marca) {
        document.getElementById('editId').value = marca.id;
        document.getElementById('editNombre').value = marca.nombre;
        document.getElementById('editDescripcion').value = marca.descripcion || '';
        document.getElementById('editActivo').value = marca.activo ? '1' : '0';
        document.getElementById('modalEditar').style.display = 'flex';
    }
}

function cerrarModalEditar() { document.getElementById('modalEditar').style.display = 'none'; }

function actualizarMarca() {
    alert('Funcionalidad en desarrollo. La marca se actualizará en la base de datos próximamente.');
    cerrarModalEditar();
}

function eliminarMarca(id) {
    if (confirm('¿Está seguro de que desea eliminar esta marca?')) {
        alert('Funcionalidad en desarrollo. La marca se eliminará de la base de datos próximamente.');
    }
}

function verModelos(id) {
    const marca = marcas.find(m => m.id === id);
    const modal = document.getElementById('modalModelos');
    const modalBody = document.getElementById('modalModelosBody');

    if (!marca || !marca.modelos || marca.modelos.length === 0) {
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M8 8h8M8 12h6M8 16h4"/>
                </svg>
                <p class="mt-3 text-muted">Esta marca no tiene modelos registrados</p>
                <small class="text-muted">Los modelos se gestionan desde el módulo de Modelos</small>
            </div>
        `;
    } else {
        let html = `
            <div class="mb-3 pb-2 border-bottom">
                <strong class="text-primary">${escapeHtml(marca.nombre)}</strong> - ${marca.modelos.length} modelos
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Modelo</th>
                            <th>Descripción</th>
                        </thead>
                    </thead>
                    <tbody>
        `;
        for (const modelo of marca.modelos) {
            html += `<tr>
                <td><span class="badge-fecha" style="background: #e3f2fd; color: #0d47a1; font-weight: 600;">${escapeHtml(modelo.nombre)}</span></td>
                <td class="text-muted">${escapeHtml(modelo.descripcion || '-')}</td>
            </tr>`;
        }
        html += `</tbody></table></div>`;
        modalBody.innerHTML = html;
    }

    modal.style.display = 'flex';
}

function cerrarModalModelos() {
    document.getElementById('modalModelos').style.display = 'none';
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
document.getElementById('estadoFilter').addEventListener('change', aplicarFiltros);
document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('estadoFilter').value = '';
    marcasFiltradas = [...marcas];
    currentPage = 1;
    renderizarTabla();
});

document.getElementById('modalCrear')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalCrear(); });
document.getElementById('modalEditar')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalEditar(); });
document.getElementById('modalModelos')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalModelos(); });

// Inicializar
renderizarTabla();
</script>
@endsection
