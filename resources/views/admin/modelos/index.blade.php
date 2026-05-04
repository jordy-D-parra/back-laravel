@extends('layouts.dashboard')

@section('title', 'Gestión de Modelos')

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
                                Gestión de Modelos
                            </h2>
                            <p class="text-white-50 mb-0">Administra los modelos de equipos, sus marcas y categorías</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="abrirModalCrear()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 10px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="display: inline-block; margin-right: 6px;">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Nuevo Modelo
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
                            <label class="form-label text-muted small">Buscar modelo</label>
                            <div class="position-relative">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre..." style="padding-left: 36px; border-radius: 10px; background: white;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Marca</label>
                            <select id="marcaFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todas las marcas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Categoría</label>
                            <select id="categoriaFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todas las categorías</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Estado</label>
                            <select id="estadoFilter" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
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
                        <span class="text-muted small">Mostrando <span id="resultadosCount">0</span> modelos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de modelos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" style="background: white;">
                <div class="card-body p-0 overflow-auto">
                    <table class="table table-hover mb-0" style="min-width: 1000px;">
                        <thead style="background: #f8f9fc;">
                            <tr>
                                <th class="px-4 py-3 text-muted small fw-semibold">ID</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Modelo</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Marca</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Categoría</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Descripción</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Especificaciones</th>
                                <th class="px-4 py-3 text-muted small fw-semibold">Estado</th>
                                <th class="px-4 py-3 text-muted small fw-semibold text-center">Componentes</th>
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

<!-- MODAL CREAR MODELO -->
<div id="modalCrear" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nuevo Modelo
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCrear()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formCrearModelo">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Marca</label>
                            <select name="marca_id" id="marcaId" class="form-select" style="border-radius: 10px; background: white;" required>
                                <option value="">Seleccione una marca</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select name="categoria_id" id="categoriaId" class="form-select" style="border-radius: 10px; background: white;" required>
                                <option value="">Seleccione una categoría</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre del Modelo</label>
                            <input type="text" name="nombre" id="nombreModelo" class="form-control" placeholder="Ej: ProBook 450, Latitude 3420" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" id="descripcionModelo" rows="2" class="form-control" placeholder="Descripción del modelo" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Especificaciones Técnicas</label>
                            <textarea name="especificaciones" id="especificacionesModelo" rows="3" class="form-control" placeholder="Procesador, RAM, almacenamiento, etc." style="border-radius: 10px; background: white;"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="activo" id="activoModelo" class="form-select" style="border-radius: 10px; background: white;">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" onclick="cerrarModalCrear()" class="btn btn-light px-4" style="border-radius: 10px;">Cancelar</button>
                        <button type="button" onclick="guardarModelo()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Guardar Modelo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR MODELO -->
<div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                    </svg>
                    Editar Modelo
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalEditar()"></button>
            </div>
            <div class="modal-body p-4" style="background: white;">
                <form id="formEditarModelo">
                    <input type="hidden" name="id" id="editId">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Marca</label>
                            <select name="marca_id" id="editMarcaId" class="form-select" style="border-radius: 10px; background: white;" required>
                                <option value="">Seleccione una marca</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select name="categoria_id" id="editCategoriaId" class="form-select" style="border-radius: 10px; background: white;" required>
                                <option value="">Seleccione una categoría</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre del Modelo</label>
                            <input type="text" name="nombre" id="editNombre" class="form-control" style="border-radius: 10px; background: white;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" id="editDescripcion" rows="2" class="form-control" style="border-radius: 10px; background: white;"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Especificaciones Técnicas</label>
                            <textarea name="especificaciones" id="editEspecificaciones" rows="3" class="form-control" style="border-radius: 10px; background: white;"></textarea>
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
                        <button type="button" onclick="actualizarModelo()" class="btn px-4 text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px;">Actualizar Modelo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VER COMPONENTES -->
<div id="modalComponentes" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10002; justify-content: center; align-items: center;">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content rounded-4 border-0" style="background: white;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 20px 20px 0 0; padding: 20px 24px;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 2v2M12 20v2M22 12h-2M4 12H2"/>
                    </svg>
                    Componentes del Modelo
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
// Datos de ejemplo (marcas)
let marcas = [
    { id: 1, nombre: 'HP' },
    { id: 2, nombre: 'Dell' },
    { id: 3, nombre: 'Samsung' },
    { id: 4, nombre: 'Epson' },
    { id: 5, nombre: 'Lenovo' },
    { id: 6, nombre: 'Apple' },
    { id: 7, nombre: 'LG' },
    { id: 8, nombre: 'Brother' }
];

// Datos de ejemplo (categorías)
let categorias = [
    { id: 1, nombre: 'Laptop' },
    { id: 2, nombre: 'Desktop' },
    { id: 3, nombre: 'Monitor' },
    { id: 4, nombre: 'Impresora' },
    { id: 5, nombre: 'Proyector' },
    { id: 6, nombre: 'Tablet' },
    { id: 7, nombre: 'Servidor' },
    { id: 8, nombre: 'Switch' },
    { id: 9, nombre: 'Router' },
    { id: 10, nombre: 'Almacenamiento' }
];

// Datos de ejemplo (modelos con categorías y componentes)
let modelos = [
    { id: 1, marca_id: 1, categoria_id: 1, nombre: 'ProBook 450', descripcion: 'Laptop empresarial', especificaciones: 'Intel Core i5, 8GB RAM, 256GB SSD', activo: true, componentes: [{ id: 1, nombre: 'Batería', tipo: 'Batería', serial: 'BAT-HP-001', estado: 'disponible' }, { id: 2, nombre: 'Cargador', tipo: 'Cargador', serial: 'CHG-HP-001', estado: 'disponible' }] },
    { id: 2, marca_id: 1, categoria_id: 1, nombre: 'EliteBook 840', descripcion: 'Laptop premium', especificaciones: 'Intel Core i7, 16GB RAM, 512GB SSD', activo: true, componentes: [{ id: 3, nombre: 'Batería', tipo: 'Batería', serial: 'BAT-HP-002', estado: 'disponible' }, { id: 4, nombre: 'Cargador', tipo: 'Cargador', serial: 'CHG-HP-002', estado: 'disponible' }, { id: 5, nombre: 'Adaptador USB-C', tipo: 'Adaptador', serial: 'ADP-HP-001', estado: 'disponible' }] },
    { id: 3, marca_id: 1, categoria_id: 1, nombre: 'Pavilion 15', descripcion: 'Laptop de consumo', especificaciones: 'AMD Ryzen 5, 8GB RAM, 512GB SSD', activo: true, componentes: [{ id: 6, nombre: 'Batería', tipo: 'Batería', serial: 'BAT-HP-003', estado: 'disponible' }] },
    { id: 4, marca_id: 2, categoria_id: 1, nombre: 'Latitude 3420', descripcion: 'Laptop empresarial', especificaciones: 'Intel Core i5, 8GB RAM, 256GB SSD', activo: true, componentes: [{ id: 7, nombre: 'Batería', tipo: 'Batería', serial: 'BAT-DELL-001', estado: 'disponible' }, { id: 8, nombre: 'Cargador', tipo: 'Cargador', serial: 'CHG-DELL-001', estado: 'disponible' }] },
    { id: 5, marca_id: 2, categoria_id: 1, nombre: 'XPS 13', descripcion: 'Ultrabook premium', especificaciones: 'Intel Core i7, 16GB RAM, 1TB SSD', activo: true, componentes: [{ id: 9, nombre: 'Batería', tipo: 'Batería', serial: 'BAT-DELL-002', estado: 'disponible' }, { id: 10, nombre: 'Cargador USB-C', tipo: 'Cargador', serial: 'CHG-DELL-002', estado: 'disponible' }, { id: 11, nombre: 'Docking Station', tipo: 'Docking', serial: 'DOCK-DELL-001', estado: 'asignado' }] },
    { id: 6, marca_id: 2, categoria_id: 2, nombre: 'OptiPlex 3080', descripcion: 'Desktop de oficina', especificaciones: 'Intel Core i3, 4GB RAM, 1TB HDD', activo: false, componentes: [] },
    { id: 7, marca_id: 3, categoria_id: 3, nombre: 'Odyssey G7', descripcion: 'Monitor gaming', especificaciones: '27 pulgadas, 240Hz, QHD', activo: true, componentes: [{ id: 12, nombre: 'Cable DisplayPort', tipo: 'Cable', serial: 'CBL-SAM-001', estado: 'disponible' }, { id: 13, nombre: 'Cable HDMI', tipo: 'Cable', serial: 'CBL-SAM-002', estado: 'disponible' }, { id: 14, nombre: 'Fuente de poder', tipo: 'Fuente', serial: 'PSU-SAM-001', estado: 'disponible' }] },
    { id: 8, marca_id: 3, categoria_id: 3, nombre: 'T55', descripcion: 'Monitor curvo', especificaciones: '24 pulgadas, 75Hz, Full HD', activo: true, componentes: [{ id: 15, nombre: 'Cable HDMI', tipo: 'Cable', serial: 'CBL-SAM-003', estado: 'disponible' }] },
    { id: 9, marca_id: 4, categoria_id: 4, nombre: 'EcoTank L3150', descripcion: 'Impresora multifuncional', especificaciones: 'WiFi, tanque de tinta', activo: true, componentes: [{ id: 16, nombre: 'Cable USB', tipo: 'Cable', serial: 'CBL-EPS-001', estado: 'disponible' }, { id: 17, nombre: 'Cartucho de tinta', tipo: 'Cartucho', serial: 'INK-EPS-001', estado: 'disponible' }] },
    { id: 10, marca_id: 4, categoria_id: 4, nombre: 'WorkForce Pro', descripcion: 'Impresora empresarial', especificaciones: 'Alta velocidad, dúplex', activo: false, componentes: [] },
    { id: 11, marca_id: 5, categoria_id: 1, nombre: 'ThinkPad T14', descripcion: 'Laptop empresarial', especificaciones: 'Intel Core i7, 16GB RAM, 512GB SSD', activo: true, componentes: [{ id: 18, nombre: 'Batería', tipo: 'Batería', serial: 'BAT-LEN-001', estado: 'disponible' }, { id: 19, nombre: 'Cargador', tipo: 'Cargador', serial: 'CHG-LEN-001', estado: 'disponible' }] },
    { id: 12, marca_id: 5, categoria_id: 1, nombre: 'IdeaPad 3', descripcion: 'Laptop de consumo', especificaciones: 'AMD Ryzen 3, 4GB RAM, 128GB SSD', activo: true, componentes: [{ id: 20, nombre: 'Batería', tipo: 'Batería', serial: 'BAT-LEN-002', estado: 'disponible' }] },
    { id: 13, marca_id: 5, categoria_id: 1, nombre: 'Legion 5', descripcion: 'Laptop gaming', especificaciones: 'AMD Ryzen 7, 16GB RAM, 512GB SSD, RTX 3060', activo: true, componentes: [{ id: 21, nombre: 'Batería', tipo: 'Batería', serial: 'BAT-LEN-003', estado: 'disponible' }, { id: 22, nombre: 'Cargador 230W', tipo: 'Cargador', serial: 'CHG-LEN-002', estado: 'disponible' }, { id: 23, nombre: 'Mouse gaming', tipo: 'Mouse', serial: 'MOU-LEN-001', estado: 'disponible' }] },
    { id: 14, marca_id: 6, categoria_id: 6, nombre: 'iPad Air 5ta Gen', descripcion: 'Tableta digital', especificaciones: '10.9 pulgadas, M1 chip', activo: true, componentes: [{ id: 24, nombre: 'Cargador USB-C', tipo: 'Cargador', serial: 'CHG-APP-001', estado: 'disponible' }] },
    { id: 15, marca_id: 7, categoria_id: 3, nombre: 'UltraGear 27', descripcion: 'Monitor gaming', especificaciones: '27 pulgadas, 144Hz, 1ms', activo: true, componentes: [] }
];

let modelosFiltrados = [...modelos];
let currentPage = 1;
const itemsPorPagina = 8;

// Cargar marcas y categorías en los selects
function cargarSelects() {
    // Cargar marcas
    const marcaSelects = ['marcaId', 'editMarcaId', 'marcaFilter'];
    for (const selectId of marcaSelects) {
        const select = document.getElementById(selectId);
        if (select) {
            let options = '<option value="">Seleccione una marca</option>';
            for (const marca of marcas) {
                options += `<option value="${marca.id}">${escapeHtml(marca.nombre)}</option>`;
            }
            select.innerHTML = options;
        }
    }

    // Cargar categorías
    const categoriaSelects = ['categoriaId', 'editCategoriaId', 'categoriaFilter'];
    for (const selectId of categoriaSelects) {
        const select = document.getElementById(selectId);
        if (select) {
            let options = '<option value="">Seleccione una categoría</option>';
            for (const categoria of categorias) {
                options += `<option value="${categoria.id}">${escapeHtml(categoria.nombre)}</option>`;
            }
            select.innerHTML = options;
        }
    }
}

function renderizarTabla() {
    const start = (currentPage - 1) * itemsPorPagina;
    const end = start + itemsPorPagina;
    const modelosPagina = modelosFiltrados.slice(start, end);
    const tbody = document.getElementById('tablaBody');
    const resultadosCount = document.getElementById('resultadosCount');

    resultadosCount.innerText = modelosFiltrados.length;

    if (modelosFiltrados.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted" style="background: white;">No hay modelos registrados<br><button onclick="abrirModalCrear()" class="btn btn-sm mt-2" style="background: #1e3c72; color: white; border-radius: 8px;"> Registrar primer modelo</button></td></tr>`;
        document.getElementById('paginacionContainer').innerHTML = '';
        return;
    }

    let html = '';
    for (const m of modelosPagina) {
        const marca = marcas.find(marca => marca.id === m.marca_id);
        const categoria = categorias.find(cat => cat.id === m.categoria_id);
        const nombreMarca = marca ? marca.nombre : 'Marca no encontrada';
        const nombreCategoria = categoria ? categoria.nombre : 'Categoría no encontrada';
        let estadoColor = m.activo ? '#1b5e20' : '#8b1a24';
        let estadoBg = m.activo ? '#e8f5e9' : '#ffebee';
        let estadoTexto = m.activo ? 'Activo' : 'Inactivo';
        let totalComponentes = m.componentes?.length || 0;

        html += `
            <tr>
                <td class="px-4 py-3 fw-semibold" style="color: #1a1a2e;">#${m.id}</td>
                <td class="px-4 py-3"><span class="badge-fecha" style="background: #e3f2fd; color: #0d47a1; font-weight: 600;">${escapeHtml(m.nombre)}</span></td>
                <td class="px-4 py-3"><span class="badge-fecha" style="background: #f3e5f5; color: #4a148c; font-weight: 600;">${escapeHtml(nombreMarca)}</span></td>
                <td class="px-4 py-3"><span class="badge-fecha" style="background: #fff8e1; color: #b26a00; font-weight: 600;">${escapeHtml(nombreCategoria)}</span></td>
                <td class="px-4 py-3" style="color: #495057; max-width: 200px;">${escapeHtml(m.descripcion || '-')}</td>
                <td class="px-4 py-3" style="color: #495057; max-width: 250px;">${escapeHtml(m.especificaciones || '-')}</td>
                <td class="px-4 py-3"><span class="badge-estado" style="background: ${estadoBg}; color: ${estadoColor};">${estadoTexto}</span></td>
                <td class="px-4 py-3 text-center"><button onclick="verComponentes(${m.id})" class="btn btn-sm btn-accion-ver" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="12" cy="12" r="3"/></svg> ${totalComponentes}</button></td>
                <td class="px-4 py-3 text-center"><div class="d-flex gap-2 justify-content-center">
                    <button onclick="editarModelo(${m.id})" class="btn btn-sm btn-accion-editar" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/></svg> Editar</button>
                    <button onclick="eliminarModelo(${m.id})" class="btn btn-sm btn-accion-eliminar" style="border-radius: 8px; padding: 6px 12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M18 6v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6"/></svg> Eliminar</button>
                </div></td>
            </tr>
        `;
    }
    tbody.innerHTML = html;
    renderizarPaginacion();
}

function renderizarPaginacion() {
    const totalPages = Math.ceil(modelosFiltrados.length / itemsPorPagina);
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
    const marcaId = document.getElementById('marcaFilter').value;
    const categoriaId = document.getElementById('categoriaFilter').value;
    const estado = document.getElementById('estadoFilter').value;

    modelosFiltrados = modelos.filter(m => {
        if (searchTerm && !m.nombre.toLowerCase().includes(searchTerm)) return false;
        if (marcaId && m.marca_id != marcaId) return false;
        if (categoriaId && m.categoria_id != categoriaId) return false;
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

function abrirModalCrear() {
    document.getElementById('formCrearModelo').reset();
    document.getElementById('modalCrear').style.display = 'flex';
}
function cerrarModalCrear() { document.getElementById('modalCrear').style.display = 'none'; }

function guardarModelo() {
    const marcaId = document.getElementById('marcaId').value;
    const categoriaId = document.getElementById('categoriaId').value;
    const nombre = document.getElementById('nombreModelo').value;
    if (!marcaId) { alert('Debe seleccionar una marca'); return; }
    if (!categoriaId) { alert('Debe seleccionar una categoría'); return; }
    if (!nombre) { alert('El nombre del modelo es requerido'); return; }
    alert('Funcionalidad en desarrollo. El modelo se guardará en la base de datos próximamente.');
    cerrarModalCrear();
}

function editarModelo(id) {
    const modelo = modelos.find(m => m.id === id);
    if (modelo) {
        document.getElementById('editId').value = modelo.id;
        document.getElementById('editMarcaId').value = modelo.marca_id;
        document.getElementById('editCategoriaId').value = modelo.categoria_id;
        document.getElementById('editNombre').value = modelo.nombre;
        document.getElementById('editDescripcion').value = modelo.descripcion || '';
        document.getElementById('editEspecificaciones').value = modelo.especificaciones || '';
        document.getElementById('editActivo').value = modelo.activo ? '1' : '0';
        document.getElementById('modalEditar').style.display = 'flex';
    }
}

function cerrarModalEditar() { document.getElementById('modalEditar').style.display = 'none'; }

function actualizarModelo() {
    alert('Funcionalidad en desarrollo. El modelo se actualizará en la base de datos próximamente.');
    cerrarModalEditar();
}

function eliminarModelo(id) {
    if (confirm('¿Está seguro de que desea eliminar este modelo?')) {
        alert('Funcionalidad en desarrollo. El modelo se eliminará de la base de datos próximamente.');
    }
}

function verComponentes(id) {
    const modelo = modelos.find(m => m.id === id);
    const modal = document.getElementById('modalComponentes');
    const modalBody = document.getElementById('modalComponentesBody');

    if (!modelo || !modelo.componentes || modelo.componentes.length === 0) {
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#adb5bd" stroke-width="1.5">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <p class="mt-3 text-muted">Este modelo no tiene componentes registrados</p>
                <small class="text-muted">Los componentes se gestionan desde el módulo de Componentes</small>
            </div>
        `;
    } else {
        let html = `
            <div class="mb-3 pb-2 border-bottom">
                <strong class="text-primary">${escapeHtml(modelo.nombre)}</strong> - ${modelo.componentes.length} componentes
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Componente</th><th>Tipo</th><th>Serial</th><th>Estado</th></thead>
                    </thead>
                    <tbody>
        `;
        for (const comp of modelo.componentes) {
            let estadoColor = comp.estado === 'disponible' ? '#1b5e20' : (comp.estado === 'asignado' ? '#b26a00' : '#8b1a24');
            let estadoBg = comp.estado === 'disponible' ? '#e8f5e9' : (comp.estado === 'asignado' ? '#fff8e1' : '#ffebee');
            let estadoTexto = comp.estado === 'disponible' ? 'Disponible' : (comp.estado === 'asignado' ? 'Asignado' : 'En mantenimiento');
            html += `<tr>
                <td><span class="badge-fecha" style="background: #e3f2fd; color: #0d47a1; font-weight: 600;">${escapeHtml(comp.nombre)}</span></td>
                <td class="text-muted">${escapeHtml(comp.tipo)}</td>
                <td><code class="small">${escapeHtml(comp.serial)}</code></td>
                <td><span class="badge-estado" style="background: ${estadoBg}; color: ${estadoColor};">${estadoTexto}</span></td>
            </tr>`;
        }
        html += `</tbody></table></div>`;
        modalBody.innerHTML = html;
    }
    modal.style.display = 'flex';
}

function cerrarModalComponentes() {
    document.getElementById('modalComponentes').style.display = 'none';
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
document.getElementById('marcaFilter').addEventListener('change', aplicarFiltros);
document.getElementById('categoriaFilter').addEventListener('change', aplicarFiltros);
document.getElementById('estadoFilter').addEventListener('change', aplicarFiltros);
document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.getElementById('marcaFilter').value = '';
    document.getElementById('categoriaFilter').value = '';
    document.getElementById('estadoFilter').value = '';
    modelosFiltrados = [...modelos];
    currentPage = 1;
    renderizarTabla();
});

document.getElementById('modalCrear')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalCrear(); });
document.getElementById('modalEditar')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalEditar(); });
document.getElementById('modalComponentes')?.addEventListener('click', function(e) { if(e.target === this) cerrarModalComponentes(); });

// Inicializar
cargarSelects();
renderizarTabla();
</script>
@endsection
