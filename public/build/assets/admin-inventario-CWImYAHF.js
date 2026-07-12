var e=[],t=1,n=10,r=[],i=1,a=10,o=[],s=[],c=null,l={ver:`<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>`,editar:`<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,eliminar:`<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>`,cambiarEstado:`<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>`,plus:`<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>`,check:`<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`};document.addEventListener(`DOMContentLoaded`,function(){console.log(`Módulo de inventario cargado`),S().then(()=>{N(),I()}),T(),document.getElementById(`formActivo`).addEventListener(`submit`,function(e){e.preventDefault(),F()}),document.getElementById(`formComponente`).addEventListener(`submit`,function(e){e.preventDefault(),L()}),document.getElementById(`btnConfirmarEliminar`).addEventListener(`click`,function(){B()});var e=document.getElementById(`buscarActivos`);e&&e.addEventListener(`input`,function(){t=1,k()});var n=document.getElementById(`filtroEstadoActivos`);n&&n.addEventListener(`change`,function(){t=1,k()});var r=document.getElementById(`buscarComponentes`);r&&r.addEventListener(`input`,function(){i=1,A()});var a=document.getElementById(`filtroTipoComponentes`);a&&a.addEventListener(`change`,function(){i=1,A()});var o=document.getElementById(`filtroEstadoComponentes`);o&&o.addEventListener(`change`,function(){i=1,A()});var s=document.getElementById(`activo_serial`);s&&s.addEventListener(`blur`,function(){w()}),document.addEventListener(`click`,function(e){var t=document.getElementById(`modeloDropdown`),n=document.getElementById(`activo_modelo_buscar`);t&&n&&e.target!==n&&!t.contains(e.target)&&(t.style.display=`none`)}),document.getElementById(`btnConfirmarCambioEstado`)?.addEventListener(`click`,function(){R()})});function u(){var e=document.querySelector(`meta[name="csrf-token"]`);return e?e.getAttribute(`content`):``}function d(e){if(!e)return``;var t=document.createElement(`div`);return t.textContent=e,t.innerHTML}function f(e,t){t||=`success`;var n={success:`#1e7e34`,error:`#c5221f`,warning:`#f6c23e`,info:`#1e3c72`},r=document.createElement(`div`);r.style.cssText=`position:fixed;top:20px;right:20px;z-index:10000;background:`+n[t]+`;color:white;padding:12px 20px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:slideIn 0.3s ease-out;cursor:pointer;`,r.textContent=e,document.body.appendChild(r),setTimeout(function(){r.remove()},3e3)}function p(e){return{en_bodega:`badge-en-bodega`,instalado:`badge-instalado`,prestado:`badge-prestado`,en_reparacion:`badge-en-reparacion`,desechado:`badge-desechado`}[e]||`bg-secondary text-white`}function m(e){return{en_bodega:`En Bodega`,instalado:`Instalado`,prestado:`Prestado`,en_reparacion:`En Reparación`,desechado:`Desechado`}[e]||e}function h(e){return e?new Date(e)<new Date:!1}function g(e){switch(e){case`Disponible`:return`#28a745`;case`Prestado`:return`#ffc107`;case`En reparación`:return`#fd7e14`;case`Desechado`:return`#dc3545`;case`En bodega`:return`#6c757d`;default:return`#1e3c72`}}function _(e){switch(e){case`instalado`:return`componente-estado-instalado`;case`en_bodega`:return`componente-estado-bodega`;case`prestado`:return`componente-estado-prestado`;case`en_reparacion`:return`componente-estado-reparacion`;case`desechado`:return`componente-estado-desechado`;default:return`componente-estado-default`}}function v(e){switch(e){case`instalado`:return`Instalado`;case`en_bodega`:return`En Bodega`;case`prestado`:return`Prestado`;case`en_reparacion`:return`En Reparación`;case`desechado`:return`Desechado`;default:return e||`N/A`}}function y(e){if(!e||e.length===0)return`<div class="text-center py-4 text-muted"><i class="fas fa-info-circle"></i> No hay componentes instalados</div>`;for(var t=`<div class="componentes-grid">`,n=0;n<e.length;n++){var r=e[n],i=_(r.estado),a=v(r.estado);t+=`
            <div class="componente-card">
                <div class="componente-card-header">
                    <div class="componente-tipo">
                        <i class="fas fa-microchip"></i> ${d(r.tipo)}
                    </div>
                    <span class="componente-estado ${i}">${a}</span>
                </div>
                <div class="componente-card-body">
                    <div class="componente-info">
                        <span class="componente-label">Marca:</span>
                        <span class="componente-value">${d(r.marca||`N/A`)}</span>
                    </div>
                    ${r.serial?`
                    <div class="componente-info">
                        <span class="componente-label">Serial:</span>
                        <span class="componente-value componente-serial">${d(r.serial)}</span>
                    </div>
                    `:``}
                    ${r.capacidad?`
                    <div class="componente-info">
                        <span class="componente-label">Capacidad:</span>
                        <span class="componente-value">${d(r.capacidad)}</span>
                    </div>
                    `:``}
                </div>
            </div>
        `}return t+=`</div>`,t}function b(e){if(!e||e.length===0)return`<div class="text-center py-4 text-muted"><i class="fas fa-info-circle"></i> No hay componentes definidos para este modelo</div>`;for(var t=`<div class="componentes-modelo-grid">`,n=0;n<e.length;n++){var r=e[n];t+=`
            <div class="componente-modelo-card">
                <div class="componente-modelo-tipo">
                    <i class="fas fa-cog"></i> ${d(r.tipo)}
                </div>
                <div class="componente-modelo-descripcion">
                    ${d(r.descripcion)}
                </div>
                ${r.capacidad?`<div class="componente-modelo-capacidad"><i class="fas fa-tachometer-alt"></i> ${d(r.capacidad)}</div>`:``}
            </div>
        `}return t+=`</div>`,t}function x(){document.getElementById(`detalle-activo-styles`)||document.head.insertAdjacentHTML(`beforeend`,`
        <style id="detalle-activo-styles">
            .detalle-activo-moderno {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
            }
            
            .detalle-seccion {
                background: #ffffff;
                border-radius: 16px;
                padding: 1rem;
                border: 1px solid #e9ecef;
            }
            
            .detalle-seccion-titulo {
                font-size: 0.85rem;
                font-weight: 600;
                color: #1e3c72;
                margin-bottom: 1rem;
                padding-bottom: 0.5rem;
                border-bottom: 2px solid #eef2f6;
                display: flex;
                align-items: center;
            }
            
            .detalle-seccion-titulo i {
                color: #1e3c72;
            }
            
            .detalle-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .detalle-item {
                padding: 0.5rem;
                background: #f8f9fc;
                border-radius: 12px;
                transition: all 0.2s ease;
            }
            
            .detalle-item:hover {
                background: #eef3fc;
                transform: translateY(-1px);
            }
            
            .detalle-label {
                font-size: 0.65rem;
                text-transform: uppercase;
                color: #6c757d;
                letter-spacing: 0.5px;
                margin-bottom: 0.25rem;
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }
            
            .detalle-label i {
                font-size: 0.7rem;
                color: #1e3c72;
            }
            
            .detalle-valor {
                font-weight: 600;
                color: #1a1a1a;
                font-size: 0.9rem;
                word-break: break-word;
            }
            
            .detalle-observaciones {
                background: #f8f9fc;
                padding: 1rem;
                border-radius: 12px;
                font-size: 0.85rem;
                color: #495057;
                line-height: 1.5;
            }
            
            .nav-tabs-componentes {
                border-bottom: 2px solid #e9ecef;
                margin-bottom: 0;
            }
            
            .nav-tabs-componentes .nav-link {
                border: none;
                background: transparent;
                padding: 0.6rem 1.2rem;
                font-weight: 500;
                color: #6c757d;
                position: relative;
                transition: all 0.2s ease;
            }
            
            .nav-tabs-componentes .nav-link:hover {
                color: #1e3c72;
                background: #f8f9fc;
            }
            
            .nav-tabs-componentes .nav-link.active {
                color: #1e3c72;
                background: transparent;
            }
            
            .nav-tabs-componentes .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                right: 0;
                height: 2px;
                background: #1e3c72;
                border-radius: 2px;
            }
            
            .badge-componentes {
                background: #e9ecef;
                color: #495057;
                padding: 0.15rem 0.5rem;
                border-radius: 20px;
                font-size: 0.65rem;
                margin-left: 0.5rem;
            }
            
            .componentes-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 1rem;
            }
            
            .componente-card {
                background: #ffffff;
                border: 1px solid #e9ecef;
                border-radius: 12px;
                overflow: hidden;
                transition: all 0.2s ease;
            }
            
            .componente-card:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                transform: translateY(-2px);
            }
            
            .componente-card-header {
                padding: 0.75rem 1rem;
                background: #f8f9fc;
                border-bottom: 1px solid #e9ecef;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .componente-tipo {
                font-weight: 600;
                color: #1e3c72;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .componente-tipo i {
                font-size: 0.9rem;
            }
            
            .componente-estado {
                padding: 0.2rem 0.6rem;
                border-radius: 20px;
                font-size: 0.65rem;
                font-weight: 600;
            }
            
            .componente-estado-instalado {
                background: #d4edda;
                color: #155724;
            }
            
            .componente-estado-bodega {
                background: #e2e3e5;
                color: #383d41;
            }
            
            .componente-estado-prestado {
                background: #fff3cd;
                color: #856404;
            }
            
            .componente-estado-reparacion {
                background: #f8d7da;
                color: #721c24;
            }
            
            .componente-card-body {
                padding: 0.75rem 1rem;
            }
            
            .componente-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.5rem;
                font-size: 0.8rem;
            }
            
            .componente-label {
                color: #6c757d;
            }
            
            .componente-value {
                font-weight: 500;
                color: #1a1a1a;
            }
            
            .componente-serial {
                font-family: 'Courier New', monospace;
                font-size: 0.75rem;
            }
            
            .componentes-modelo-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 1rem;
            }
            
            .componente-modelo-card {
                background: #f8f9fc;
                border-radius: 12px;
                padding: 1rem;
                text-align: center;
                transition: all 0.2s ease;
                border: 1px solid #e9ecef;
            }
            
            .componente-modelo-card:hover {
                background: #eef3fc;
                transform: translateY(-2px);
            }
            
            .componente-modelo-tipo {
                font-weight: 700;
                color: #1e3c72;
                margin-bottom: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }
            
            .componente-modelo-descripcion {
                font-size: 0.75rem;
                color: #6c757d;
                margin-bottom: 0.5rem;
            }
            
            .componente-modelo-capacidad {
                font-size: 0.7rem;
                color: #28a745;
                background: #d4edda;
                display: inline-block;
                padding: 0.2rem 0.6rem;
                border-radius: 20px;
            }
            
            .detalle-acciones .btn-editar-detalle {
                background: #1e3c72;
                border: none;
                color: white;
                padding: 0.5rem 1.2rem;
                border-radius: 30px;
                font-size: 0.8rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            
            .detalle-acciones .btn-editar-detalle:hover {
                background: #2a5298;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
            }
            
            .detalle-acciones .btn-cerrar-detalle {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                color: #495057;
                padding: 0.5rem 1.2rem;
                border-radius: 30px;
                font-size: 0.8rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            
            .detalle-acciones .btn-cerrar-detalle:hover {
                background: #e9ecef;
                border-color: #ced4da;
            }
            
            .badge-garantia-vencida {
                background: #f8d7da;
                color: #721c24;
                padding: 0.2rem 0.5rem;
                border-radius: 20px;
                font-size: 0.7rem;
            }
            
            .badge-garantia-vigente {
                background: #d4edda;
                color: #155724;
                padding: 0.2rem 0.5rem;
                border-radius: 20px;
                font-size: 0.7rem;
            }
            
            @media (max-width: 768px) {
                .detalle-grid {
                    grid-template-columns: 1fr;
                }
                
                .componentes-grid,
                .componentes-modelo-grid {
                    grid-template-columns: 1fr;
                }
                
                .nav-tabs-componentes .nav-link {
                    padding: 0.4rem 0.8rem;
                    font-size: 0.75rem;
                }
            }
        </style>
    `)}window.cerrarModalDetalleManual=function(){var e=document.getElementById(`modalDetalle`),t=bootstrap.Modal.getInstance(e);t&&t.hide()};function S(){return fetch(`/admin/estatus-list`,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success){s=e.data;var t=document.getElementById(`filtroEstadoActivos`);return t&&(t.innerHTML=`<option value="">Todos los estados</option>`,s.forEach(function(e){t.innerHTML+=`<option value="`+e.descripcion+`">`+e.descripcion+`</option>`})),s}return[]})}function C(e,t,n){if(e<=1)return``;var r=`<div class="pagination-bar"><div class="pagination-info">Página `+t+` de `+e+`</div><div class="pagination-btns">`;r+=`<button class="pagination-btn`+(t===1?` disabled`:``)+`" onclick="window.cambiarPaginaInv('`+n+`',`+(t-1)+`)">«</button>`;for(var i=1;i<=e;i++)i===1||i===e||i>=t-1&&i<=t+1?r+=`<button class="pagination-btn`+(i===t?` active`:``)+`" onclick="window.cambiarPaginaInv('`+n+`',`+i+`)">`+i+`</button>`:(i===t-2||i===t+2)&&(r+=`<span class="pagination-ellipsis">...</span>`);return r+=`<button class="pagination-btn`+(t===e?` disabled`:``)+`" onclick="window.cambiarPaginaInv('`+n+`',`+(t+1)+`)">»</button>`,r+=`</div></div>`,r}window.cambiarPaginaInv=function(e,n){e===`activos`?(t=n,k()):e===`componentes`&&(i=n,A())};function w(){var e=document.getElementById(`activo_serial`).value.trim(),t=document.getElementById(`activoId`).value,n=document.getElementById(`serialFeedback`);if(!e){n&&(n.innerHTML=``);return}n||(n=document.createElement(`div`),n.id=`serialFeedback`,n.style.cssText=`font-size:0.75rem;margin-top:4px;`,document.getElementById(`activo_serial`).parentNode.appendChild(n)),n.innerHTML=`<span class="text-muted">Verificando...</span>`,fetch(`/admin/activos?buscar=`+encodeURIComponent(e),{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(r){r.success&&(r.data.some(function(n){return n.serial===e&&n.id!=t})?(n.innerHTML=`<span class="text-danger">Este serial ya existe</span>`,document.getElementById(`activo_serial`).style.borderColor=`#dc3545`):(n.innerHTML=`<span class="text-success">Serial disponible</span>`,document.getElementById(`activo_serial`).style.borderColor=`#28a745`))})}function T(){fetch(`/admin/equipos/modelos`,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){e.success&&(o=e.data)}),fetch(`/admin/estatus-list`,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success){var t=document.getElementById(`activo_id_estatus`);if(t){t.innerHTML=`<option value="">Seleccionar...</option>`,e.data.forEach(function(e){t.innerHTML+=`<option value="`+e.id+`">`+d(e.descripcion)+`</option>`});var n=e.data.find(function(e){return e.descripcion===`Disponible`});n&&(t.value=n.id)}}}).catch(function(){var e=document.getElementById(`activo_id_estatus`);e&&(e.innerHTML=`<option value="">Seleccionar...</option><option value="1" selected>Disponible</option>`)}),fetch(`/admin/instituciones`,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success){var t=document.getElementById(`activo_institucion_id`),n=document.getElementById(`comp_institucion_id`);if(t){t.innerHTML=`<option value="">Seleccionar...</option>`,e.data.forEach(function(e){t.innerHTML+=`<option value="`+e.id+`" data-representante="`+d(e.representante||``)+`">`+d(e.nombre)+`</option>`});var r=e.data.find(function(e){return e.nombre.toLowerCase().indexOf(`gobernacion`)>=0||e.nombre.toLowerCase().indexOf(`informatica`)>=0});r&&(t.value=r.id,E(r.id,`activo_departamento_id`,r.representante),D(r.id,`activo_responsable_id`,r.representante)),t.addEventListener(`change`,function(){var e=this.options[this.selectedIndex].getAttribute(`data-representante`)||``;E(this.value,`activo_departamento_id`,e),D(this.value,`activo_responsable_id`,e)})}if(n){n.innerHTML=`<option value="">Seleccionar...</option>`,e.data.forEach(function(e){n.innerHTML+=`<option value="`+e.id+`" data-representante="`+d(e.representante||``)+`">`+d(e.nombre)+`</option>`});var r=e.data.find(function(e){return e.nombre.toLowerCase().indexOf(`gobernacion`)>=0||e.nombre.toLowerCase().indexOf(`informatica`)>=0});r&&(n.value=r.id,D(r.id,`comp_responsable_id`,r.representante)),n.addEventListener(`change`,function(){var e=this.options[this.selectedIndex].getAttribute(`data-representante`)||``;D(this.value,`comp_responsable_id`,e)})}}})}function E(e,t,n){var r=document.getElementById(t);!r||!e||fetch(`/admin/departamentos/por-institucion/`+e,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(t){if(t.success){r.innerHTML=`<option value="">Sin departamento</option>`,t.data.forEach(function(e){r.innerHTML+=`<option value="`+e.id+`" data-representante="`+d(e.representante||``)+`">`+d(e.nombre)+`</option>`});var i=t.data.find(function(e){var t=e.nombre.toLowerCase();return t.indexOf(`informatica`)>=0||t.indexOf(`sistemas`)>=0||t.indexOf(`ti`)>=0});!i&&t.data.length>0&&(i=t.data[0]),i&&(r.value=i.id,D(e,`activo_responsable_id`,i.representante||n||``)),r.addEventListener(`change`,function(){D(e,`activo_responsable_id`,this.options[this.selectedIndex].getAttribute(`data-representante`)||n||``)})}})}function D(e,t,n){var r=document.getElementById(t);!r||!e||fetch(`/admin/responsables?institucion_id=`+e,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success&&(r.innerHTML=`<option value="">Seleccionar...</option>`,e.data.forEach(function(e){r.innerHTML+=`<option value="`+e.id+`">`+d(e.nombre)+` - `+d(e.cargo||`Sin cargo`)+`</option>`}),n&&e.data.length>0)){var t=e.data.find(function(e){return e.nombre.toLowerCase().indexOf(n.toLowerCase())>=0});r.value=t?t.id:e.data[0].id}})}window.filtrarModelos=function(){var e=document.getElementById(`activo_modelo_buscar`),t=document.getElementById(`modeloDropdown`),n=e.value.toLowerCase();if(!o.length){t.style.display=`none`;return}var r=o.filter(function(e){return((e.marca?e.marca.nombre+` `:``)+e.nombre+` `+(e.categoria?e.categoria.nombre:``)).toLowerCase().indexOf(n)>=0}).slice(0,10);r.length===0?t.innerHTML=`<div class="list-group-item text-muted small">No se encontraron modelos</div>`:t.innerHTML=r.map(function(e){return`<a href="#" class="list-group-item list-group-item-action py-2 px-3" onclick="seleccionarModelo(`+e.id+`, '`+d(e.marca?e.marca.nombre+` `:``)+d(e.nombre)+`', '`+d(e.marca?e.marca.nombre:``)+`', '`+d(e.categoria?e.categoria.nombre:``)+`'); return false;"><strong>`+d(e.marca?e.marca.nombre+` `:``)+d(e.nombre)+`</strong><small class="d-block text-muted">`+d(e.categoria?e.categoria.nombre:``)+`</small></a>`}).join(``),t.style.display=`block`},window.seleccionarModelo=function(e,t,n,r){document.getElementById(`activo_modelo_id`).value=e,document.getElementById(`activo_modelo_buscar`).value=t,document.getElementById(`modeloDropdown`).style.display=`none`;var i=document.getElementById(`modeloInfoBadges`);i.innerHTML=`<span class="badge bg-primary-dark">`+d(n)+`</span> <span class="badge bg-secondary">`+d(r)+`</span>`,window.cargarComponentesDelModelo()},window.cargarComponentesDelModelo=function(){var e=document.getElementById(`activo_modelo_id`).value,t=document.getElementById(`componentesActivoContainer`);if(!e){t.innerHTML=`<p class="text-muted text-center py-3">Seleccione un modelo para cargar sus componentes.</p>`;return}t.innerHTML=`<p class="text-center py-3 text-muted">Cargando componentes...</p>`,fetch(`/admin/equipos/modelos/`+e+`/componentes`,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success&&e.data&&e.data.length>0){var n=``;e.data.forEach(function(e,t){n+=O(e,t,!1)}),n+=`<hr><div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="equipoCompleto" checked><label class="form-check-label" for="equipoCompleto">El equipo llegó con todos los componentes</label></div><div class="alert alert-warning py-2 px-3" id="alertaComponentes" style="display:none;font-size:0.85rem;">Hay componentes requeridos sin completar. Verifique los datos antes de guardar.</div>`,t.innerHTML=n}else t.innerHTML=`<p class="text-muted text-center py-3">Este modelo no tiene componentes definidos. Puede guardar el activo sin componentes.</p>`}).catch(function(){t.innerHTML=`<p class="text-danger text-center py-3">Error al cargar componentes.</p>`})};function O(e,t,n){var r=n?e.id:``,i=n?`comp_existente_`+t:`comp_nuevo_`+t;return`<div class="componente-activo-item border rounded p-3 mb-2 bg-light `+(n?`border-primary`:``)+`" data-comp-id="`+r+`" data-tipo="`+d(e.tipo)+`" data-requerido="true"><div class="d-flex justify-content-between align-items-center mb-2"><div><strong>`+d(e.tipo)+` - `+d(e.descripcion)+`</strong>`+(e.capacidad?`<span class="badge bg-secondary ms-2">`+d(e.capacidad)+`</span>`:``)+`<span class="badge bg-success ms-1" style="font-size:0.65rem;">Requerido</span></div><div class="d-flex gap-1"><button type="button" class="btn btn-sm btn-outline-primary-dark" onclick="duplicarComponente(this)" title="Agregar otro igual">`+l.plus+`</button>`+(n?``:`<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.componente-activo-item').remove(); verificarAlertasComponentes()" title="Eliminar">`+l.eliminar+`</button>`)+`</div></div><input type="hidden" name="`+i+`[modelo_componente_id]" value="`+(e.modelo_componente_id||e.id||``)+`"><input type="hidden" name="`+i+`[tipo]" value="`+d(e.tipo)+`"><input type="hidden" name="`+i+`[descripcion]" value="`+d(e.descripcion)+`"><input type="hidden" name="`+i+`[capacidad]" value="`+d(e.capacidad||``)+`">`+(n?`<input type="hidden" name="`+i+`[id]" value="`+e.id+`">`:``)+`<div class="row"><div class="col-md-3 mb-2"><label class="form-label small">Marca</label><input type="text" class="form-control form-control-sm comp-marca" name="`+i+`[marca]" value="`+d(e.marca||``)+`" placeholder="Kingston, Samsung..."></div><div class="col-md-3 mb-2"><label class="form-label small">Serial</label><input type="text" class="form-control form-control-sm comp-serial" name="`+i+`[serial]" value="`+d(e.serial||``)+`" placeholder="ABC123"></div><div class="col-md-2 mb-2"><label class="form-label small">Capacidad Real</label><input type="text" class="form-control form-control-sm" name="`+i+`[capacidad_real]" value="`+d(e.capacidad_real||e.capacidad||``)+`" placeholder="8GB"></div><div class="col-md-2 mb-2"><label class="form-label small">Estado</label><select class="form-select form-select-sm comp-estado" name="`+i+`[estado]"><option value="instalado" selected>Instalado</option><option value="en_bodega">En Bodega</option></select></div><div class="col-md-2 mb-2 d-flex align-items-end"><div class="form-check"><input class="form-check-input comp-check" type="checkbox" checked onchange="verificarAlertasComponentes()"><label class="form-check-label small">Registrado</label></div></div></div></div>`}window.duplicarComponente=function(e){var t=e.closest(`.componente-activo-item`),n=t.cloneNode(!0);n.querySelectorAll(`input[type="text"]`).forEach(function(e){e.value=``}),n.querySelectorAll(`.comp-marca`).forEach(function(e){e.value=``}),n.querySelectorAll(`.comp-serial`).forEach(function(e){e.value=``}),n.querySelectorAll(`.comp-check`).forEach(function(e){e.checked=!0}),n.querySelector(`select.comp-estado`).value=`instalado`,n.classList.remove(`border-primary`),n.querySelectorAll(`input, select`).forEach(function(e){var t=e.name;t&&(t=t.replace(/comp_existente_\d+/,`comp_nuevo_`+Date.now()),e.name=t)});var r=n.querySelector(`button[onclick*="duplicarComponente"]`);r&&r.remove(),t.parentNode.insertBefore(n,t.nextSibling),verificarAlertasComponentes()},window.verificarAlertasComponentes=function(){var e=document.querySelectorAll(`#componentesActivoContainer .componente-activo-item`),t=!1;e.forEach(function(e){var n=e.querySelector(`.comp-check`);n&&!n.checked&&(t=!0)});var n=document.getElementById(`alertaComponentes`);n&&(n.style.display=t?`block`:`none`)};function k(){var t=document.getElementById(`buscarActivos`)?document.getElementById(`buscarActivos`).value.toLowerCase():``,n=document.getElementById(`filtroEstadoActivos`)?document.getElementById(`filtroEstadoActivos`).value:``;j(e.filter(function(e){var r=!t||e.serial&&e.serial.toLowerCase().indexOf(t)>=0||e.modelo&&e.modelo.nombre&&e.modelo.nombre.toLowerCase().indexOf(t)>=0||e.modelo&&e.modelo.marca&&e.modelo.marca.nombre&&e.modelo.marca.nombre.toLowerCase().indexOf(t)>=0,i=!n||e.estatus&&e.estatus.descripcion===n;return r&&i}))}function A(){var e=document.getElementById(`buscarComponentes`)?document.getElementById(`buscarComponentes`).value.toLowerCase():``,t=document.getElementById(`filtroTipoComponentes`)?document.getElementById(`filtroTipoComponentes`).value:``,n=document.getElementById(`filtroEstadoComponentes`)?document.getElementById(`filtroEstadoComponentes`).value:``,i=[];r.forEach(function(e){e.tipo&&i.indexOf(e.tipo)<0&&i.push(e.tipo)});var a=document.getElementById(`filtroTipoComponentes`);a&&a.options.length<=1&&(a.innerHTML=`<option value="">Todos los tipos</option>`,i.sort().forEach(function(e){a.innerHTML+=`<option value="`+e+`">`+e+`</option>`})),M(r.filter(function(r){var i=!e||r.tipo&&r.tipo.toLowerCase().indexOf(e)>=0||r.marca&&r.marca.toLowerCase().indexOf(e)>=0||r.serial&&r.serial.toLowerCase().indexOf(e)>=0,a=!t||r.tipo===t,o=!n||r.estado===n;return i&&a&&o}))}function j(e){var r=document.getElementById(`tablaActivos`);if(r){var i=Math.ceil(e.length/n),a=(t-1)*n,o=e.slice(a,a+n);if(o.length===0){r.innerHTML=`<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron activos</td></tr>`;return}for(var s=typeof authUserHasPermission<`u`?authUserHasPermission(`cambiar-estatus-activo`):!0,c=``,u=0;u<o.length;u++){var f=o[u],p=``;f.fecha_fin_garantia&&(p=h(f.fecha_fin_garantia)?`<span class="badge badge-garantia-vencida ms-1">Vencida</span>`:`<span class="badge badge-garantia-vigente ms-1">Vigente</span>`);var m=f.componentes?f.componentes.length:0,g=f.estatus?f.estatus.descripcion:`N/A`,_=f.estatus?f.estatus.color_badge:`secondary`,v=f.estatus?f.estatus.es_terminal:!1,y=s&&!v;c+=`<tr><td><strong>`+d(f.serial)+`</strong>`+p+`</td><td>`+d(f.modelo?f.modelo.nombre:`N/A`)+(m>0?` <span class="badge bg-info text-dark">`+m+`</span>`:``)+`</td><td>`+d(f.modelo&&f.modelo.marca?f.modelo.marca.nombre:`N/A`)+`</td><td><span class="badge bg-`+_+`">`+d(g)+`</span></td><td>`+d(f.ubicacion||(f.institucion?f.institucion.nombre:`N/A`))+`</td><td class="text-end"><button class="btn btn-sm btn-outline-primary-dark" onclick="verActivo(`+f.id+`)" title="Ver detalle">`+l.ver+`</button> `+(window.authUserHasPermission&&authUserHasPermission(`editar-activo`)?`<button class="btn btn-sm btn-outline-primary-dark" onclick="editarActivo(`+f.id+`)" title="Editar">`+l.editar+`</button> `:``)+(y?`<button class="btn btn-sm btn-cambiar-estado" onclick="abrirModalCambiarEstado(`+f.id+`, '`+d(f.serial)+`', '`+g+`', `+(f.estatus?f.estatus.id:`null`)+`)" title="Cambiar estado">`+l.cambiarEstado+`</button> `:``)+(window.authUserHasPermission&&authUserHasPermission(`eliminar-activo`)?`<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarActivo(`+f.id+`)" title="Eliminar">`+l.eliminar+`</button>`:``)+`</td></tr>`}c+=`<tr><td colspan="6">`+C(i,t,`activos`)+`</td></tr>`,r.innerHTML=c}}function M(e){var t=document.getElementById(`tablaComponentes`);if(t){var n=Math.ceil(e.length/a),r=(i-1)*a,o=e.slice(r,r+a);if(o.length===0){t.innerHTML=`<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron componentes</td></tr>`;return}for(var s=``,c=0;c<o.length;c++){var u=o[c];s+=`<tr><td><strong>`+d(u.tipo)+`</strong></td><td>`+d(u.marca||`N/A`)+`</td><td>`+d(u.serial||`N/A`)+`</td><td>`+d(u.capacidad||`N/A`)+`</td><td><span class="badge `+p(u.estado)+`">`+m(u.estado)+`</span></td><td>`+(u.activo?`<a href="#" onclick="verActivo(`+u.activo.id+`); return false;" class="text-decoration-none">`+d(u.activo.serial)+`</a>`:`—`)+`</td><td class="text-end">`+(window.authUserHasPermission&&authUserHasPermission(`editar-componente`)?`<button class="btn btn-sm btn-outline-primary-dark" onclick="editarComponente(`+u.id+`)" title="Editar">`+l.editar+`</button> `:``)+(window.authUserHasPermission&&authUserHasPermission(`eliminar-componente`)?`<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarComponente(`+u.id+`)" title="Eliminar">`+l.eliminar+`</button>`:``)+`</td></tr>`}s+=`<tr><td colspan="7">`+C(n,i,`componentes`)+`</td></tr>`,t.innerHTML=s}}function N(){fetch(`/admin/activos`,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(n){n.success&&(e=n.data,t=1,k())})}window.abrirModalActivo=function(e){var t=new bootstrap.Modal(document.getElementById(`modalActivo`));document.getElementById(`formActivo`).reset(),document.getElementById(`activoId`).value=``,document.getElementById(`activo_modelo_id`).value=``,document.getElementById(`activo_modelo_buscar`).value=``,document.getElementById(`modeloDropdown`).style.display=`none`,document.getElementById(`modeloInfoBadges`).innerHTML=``,document.getElementById(`modalActivoLabel`).textContent=`Nuevo Activo`,document.getElementById(`componentesActivoContainer`).innerHTML=`<p class="text-muted text-center py-3">Seleccione un modelo para cargar sus componentes.</p>`,document.getElementById(`activo_serial`).style.borderColor=``;var n=document.getElementById(`serialFeedback`);n&&(n.innerHTML=``),T(),e&&(document.getElementById(`modalActivoLabel`).textContent=`Editar Activo`,document.getElementById(`activoId`).value=e,fetch(`/admin/activos/`+e,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success){var t=e.data;if(document.getElementById(`activo_serial`).value=t.serial||``,document.getElementById(`activo_modelo_id`).value=t.modelo_id||``,document.getElementById(`activo_modelo_buscar`).value=t.modelo?(t.modelo.marca?t.modelo.marca.nombre+` `:``)+t.modelo.nombre:``,document.getElementById(`activo_id_estatus`).value=t.id_estatus||``,document.getElementById(`activo_institucion_id`).value=t.institucion_id||``,document.getElementById(`activo_responsable_id`).value=t.responsable_id||``,document.getElementById(`activo_ubicacion`).value=t.ubicacion||``,document.getElementById(`activo_fecha_adquisicion`).value=t.fecha_adquisicion||``,document.getElementById(`activo_fecha_fin_garantia`).value=t.fecha_fin_garantia||``,document.getElementById(`activo_vida_util_anos`).value=t.vida_util_anos||``,document.getElementById(`activo_observaciones`).value=t.observaciones||``,t.modelo&&(document.getElementById(`modeloInfoBadges`).innerHTML=`<span class="badge bg-primary-dark">`+d(t.modelo.marca?t.modelo.marca.nombre:``)+`</span> <span class="badge bg-secondary">`+d(t.modelo.categoria?t.modelo.categoria.nombre:``)+`</span>`),t.componentes&&t.componentes.length>0){var n=``;t.componentes.forEach(function(e,t){e.modelo_componente_id=e.modelo_componente_id,n+=O(e,t,!0)}),n+=`<hr><div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="equipoCompleto" checked><label class="form-check-label" for="equipoCompleto">El equipo tiene todos los componentes</label></div><div class="alert alert-warning py-2 px-3" id="alertaComponentes" style="display:none;font-size:0.85rem;">Hay componentes sin verificar.</div>`,document.getElementById(`componentesActivoContainer`).innerHTML=n}else window.cargarComponentesDelModelo()}})),t.show(),setTimeout(function(){document.getElementById(`activo_serial`).focus()},500)},window.editarActivo=function(e){window.abrirModalActivo(e)},window.verActivo=function(e){fetch(`/admin/activos/`+e,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success){var t=e.data,n=t.fecha_adquisicion?new Date(t.fecha_adquisicion).toLocaleDateString(`es-ES`,{year:`numeric`,month:`long`,day:`numeric`}):`No registrada`,r=t.fecha_fin_garantia?new Date(t.fecha_fin_garantia).toLocaleDateString(`es-ES`,{year:`numeric`,month:`long`,day:`numeric`}):`No registrada`,i=t.fecha_fin_garantia&&new Date(t.fecha_fin_garantia)<new Date?`<span class="badge-garantia-vencida ms-2"><i class="fas fa-exclamation-triangle"></i> Vencida</span>`:t.fecha_fin_garantia?`<span class="badge-garantia-vigente ms-2"><i class="fas fa-check-circle"></i> Vigente</span>`:``,a=``;switch(t.estatus?.descripcion){case`Disponible`:a=`<i class="fas fa-check-circle"></i> `;break;case`Prestado`:a=`<i class="fas fa-hand-holding"></i> `;break;case`En reparación`:a=`<i class="fas fa-tools"></i> `;break;case`Desechado`:a=`<i class="fas fa-trash-alt"></i> `;break;default:a=`<i class="fas fa-circle"></i> `}x();var o=`
                <div class="detalle-activo-moderno">
                    <div class="detalle-header-moderno" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); margin: -1.5rem -1.5rem 1.5rem -1.5rem; padding: 1.5rem; border-radius: 12px 12px 0 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-microchip" style="font-size: 1.8rem; color: #ffcd3c;"></i>
                                    <h4 class="mb-0 text-white">${d(t.serial)}</h4>
                                </div>
                                <p class="mb-0 text-white-50">
                                    <i class="fas fa-tag me-1"></i> ${d(t.modelo?.marca?.nombre||`N/A`)} ${d(t.modelo?.nombre||`N/A`)}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-folder me-1"></i> ${d(t.modelo?.categoria?.nombre||`N/A`)}
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge-estado-detalle" style="background: ${g(t.estatus?.descripcion)}; color: white; padding: 0.5rem 1rem; border-radius: 30px; font-size: 0.8rem;">
                                    ${a} ${d(t.estatus?.descripcion||`N/A`)}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="detalle-seccion">
                                <h6 class="detalle-seccion-titulo">
                                    <i class="fas fa-info-circle me-2"></i>Información General
                                </h6>
                                <div class="detalle-grid">
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-barcode"></i> Número de Serie</div>
                                        <div class="detalle-valor">${d(t.serial)}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-building"></i> Institución</div>
                                        <div class="detalle-valor">${d(t.institucion?.nombre||`N/A`)}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-map-marker-alt"></i> Ubicación</div>
                                        <div class="detalle-valor">${d(t.ubicacion||`No especificada`)}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-user"></i> Responsable</div>
                                        <div class="detalle-valor">${d(t.responsable?.nombre||`No asignado`)}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-seccion">
                                <h6 class="detalle-seccion-titulo">
                                    <i class="fas fa-calendar-alt me-2"></i>Información de Adquisición
                                </h6>
                                <div class="detalle-grid">
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-shopping-cart"></i> Fecha Adquisición</div>
                                        <div class="detalle-valor">${n}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-shield-alt"></i> Fin de Garantía</div>
                                        <div class="detalle-valor">${r} ${i}</div>
                                    </div>
                                    <div class="detalle-item">
                                        <div class="detalle-label"><i class="fas fa-hourglass-half"></i> Vida Útil Estimada</div>
                                        <div class="detalle-valor">${t.vida_util_anos?t.vida_util_anos+` años`:`No especificada`}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    ${t.observaciones?`
                    <div class="detalle-seccion mb-4">
                        <h6 class="detalle-seccion-titulo">
                            <i class="fas fa-sticky-note me-2"></i>Observaciones
                        </h6>
                        <div class="detalle-observaciones">
                            ${d(t.observaciones)}
                        </div>
                    </div>
                    `:``}

                    <div class="detalle-seccion">
                        <ul class="nav nav-tabs nav-tabs-componentes" id="componentesTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="instalados-tab" data-bs-toggle="tab" data-bs-target="#instalados" type="button" role="tab">
                                    <i class="fas fa-microchip me-1"></i> Componentes Instalados
                                    <span class="badge-componentes">${t.componentes?t.componentes.length:0}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="modelo-tab" data-bs-toggle="tab" data-bs-target="#modelo" type="button" role="tab">
                                    <i class="fas fa-cube me-1"></i> Componentes del Modelo
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div class="tab-pane fade show active" id="instalados" role="tabpanel">
                                ${y(t.componentes)}
                            </div>
                            <div class="tab-pane fade" id="modelo" role="tabpanel">
                                <div id="detalleCompModeloContent" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Cargando componentes del modelo...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="detalle-acciones mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-end gap-2">
                            ${window.authUserHasPermission&&authUserHasPermission(`editar-activo`)?`<button class="btn btn-editar-detalle" onclick="editarActivo(${t.id}); bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide();">
                                    <i class="fas fa-edit"></i> Editar Activo
                                </button>`:``}
                            <button class="btn btn-cerrar-detalle" onclick="cerrarModalDetalleManual()">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            `;document.getElementById(`modalDetalleLabel`).textContent=`Detalle del Activo`,document.getElementById(`detalleContenido`).innerHTML=o,new bootstrap.Modal(document.getElementById(`modalDetalle`)).show(),t.modelo_id&&fetch(`/admin/equipos/modelos/`+t.modelo_id+`/componentes`,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success&&e.data&&e.data.length>0){var t=b(e.data);document.getElementById(`detalleCompModeloContent`).innerHTML=t}else document.getElementById(`detalleCompModeloContent`).innerHTML=`<div class="text-center py-4 text-muted"><i class="fas fa-info-circle"></i> Este modelo no tiene componentes definidos</div>`}).catch(function(){document.getElementById(`detalleCompModeloContent`).innerHTML=`<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Error al cargar componentes</div>`})}})};function P(){var e=[];return document.querySelectorAll(`#componentesActivoContainer .componente-activo-item`).forEach(function(t){var n=t.querySelectorAll(`input, select`),r={};n.forEach(function(e){var t=e.name;if(t){var n=t.match(/comp_(?:existente|nuevo)_\d+\[(\w+)\]/);n&&(r[n[1]]=e.type===`checkbox`?e.checked:e.value)}}),r.tipo&&(r.activo_id=document.getElementById(`activoId`).value,r.institucion_id=document.getElementById(`activo_institucion_id`).value,r.responsable_id=document.getElementById(`activo_responsable_id`).value,r.ubicacion=r.estado===`en_bodega`?`Bodega Central`:document.getElementById(`activo_ubicacion`).value,e.push(r))}),e}function F(){var e=document.getElementById(`activoId`).value,t=e?`/admin/activos/`+e:`/admin/activos`,n=new FormData(document.getElementById(`formActivo`));e&&n.append(`_method`,`PUT`);var r=P(),i=document.getElementById(`equipoCompleto`)?document.getElementById(`equipoCompleto`).checked:!0;fetch(t,{method:`POST`,headers:{"X-CSRF-TOKEN":u()},body:n}).then(function(e){return e.json()}).then(function(t){if(t.success){var n=e||(t.data?t.data.id:null);if(n&&r.length>0){var a=r.map(function(e){e.activo_id=n;var t=e.id?`/admin/componentes/`+e.id:`/admin/componentes`;return(e.id?`PUT`:`POST`)==`PUT`&&(e._method=`PUT`),fetch(t,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":u(),Accept:`application/json`},body:JSON.stringify(e)}).then(function(e){return e.json()})});Promise.all(a).then(function(){var e=`Activo guardado con `+r.length+` componentes. `;e+=i?`Equipo marcado como completo.`:`Equipo marcado como incompleto.`,f(e,`success`)}).catch(function(){f(`Activo guardado. Revisar componentes`,`warning`)})}else f(t.message||`Activo guardado`,`success`);bootstrap.Modal.getInstance(document.getElementById(`modalActivo`)).hide(),N(),I()}else f(t.message||`Error al guardar`,`error`)})}function I(){fetch(`/admin/componentes`,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){e.success&&(r=e.data,i=1,A())})}window.abrirModalComponente=function(e){var t=new bootstrap.Modal(document.getElementById(`modalComponente`));document.getElementById(`formComponente`).reset(),document.getElementById(`componenteId`).value=``,document.getElementById(`modalComponenteLabel`).textContent=`Nuevo Componente`,T(),e&&(document.getElementById(`modalComponenteLabel`).textContent=`Editar Componente`,document.getElementById(`componenteId`).value=e,fetch(`/admin/componentes/`+e,{headers:{Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){if(e.success){var t=e.data;document.getElementById(`comp_tipo`).value=t.tipo||``,document.getElementById(`comp_marca`).value=t.marca||``,document.getElementById(`comp_serial`).value=t.serial||``,document.getElementById(`comp_capacidad`).value=t.capacidad||``,document.getElementById(`comp_estado`).value=t.estado||``,document.getElementById(`comp_institucion_id`).value=t.institucion_id||``,document.getElementById(`comp_responsable_id`).value=t.responsable_id||``,document.getElementById(`comp_ubicacion`).value=t.ubicacion||``,document.getElementById(`comp_observaciones`).value=t.observaciones||``}})),t.show(),setTimeout(function(){document.getElementById(`comp_tipo`).focus()},500)},window.editarComponente=function(e){window.abrirModalComponente(e)};function L(){var e=document.getElementById(`componenteId`).value,t=e?`/admin/componentes/`+e:`/admin/componentes`,n=new FormData(document.getElementById(`formComponente`));e&&n.append(`_method`,`PUT`),fetch(t,{method:`POST`,headers:{"X-CSRF-TOKEN":u()},body:n}).then(function(e){return e.json()}).then(function(e){e.success?(bootstrap.Modal.getInstance(document.getElementById(`modalComponente`)).hide(),f(e.message,`success`),I()):f(e.message||`Error`,`error`)})}window.abrirModalCambiarEstado=function(e,t,n,r){c={id:e,estadoActualId:r},document.getElementById(`estadoSerial`).textContent=t,document.getElementById(`estadoActual`).textContent=n;var i=document.getElementById(`nuevoEstadoSelect`);i.innerHTML=`<option value="">Seleccionar estado...</option>`;var a=s.filter(function(e){return!e.es_terminal&&e.descripcion!==n});if(a.length===0)i.innerHTML+=`<option value="" disabled>No hay estados disponibles</option>`,document.getElementById(`btnConfirmarCambioEstado`).disabled=!0;else{document.getElementById(`btnConfirmarCambioEstado`).disabled=!1;for(var o=0;o<a.length;o++){var l=a[o];i.innerHTML+=`<option value="`+l.id+`">`+d(l.descripcion)+`</option>`}}new bootstrap.Modal(document.getElementById(`modalCambiarEstado`)).show()};function R(){if(c){var e=document.getElementById(`nuevoEstadoSelect`).value;if(!e){f(`Seleccione un estado`,`warning`);return}var t=s.find(function(t){return t.id==e});fetch(`/admin/activos/`+c.id,{method:`PUT`,headers:{"X-CSRF-TOKEN":u(),Accept:`application/json`,"Content-Type":`application/json`},body:JSON.stringify({id_estatus:e})}).then(function(e){return e.json()}).then(function(e){bootstrap.Modal.getInstance(document.getElementById(`modalCambiarEstado`)).hide(),e.success?(f(`Estado cambiado a `+(t?t.descripcion:``),`success`),N()):f(e.message||`Error al cambiar estado`,`error`),c=null}).catch(function(e){console.error(`Error:`,e),f(`Error de conexión`,`error`),c=null})}}var z=null;window.confirmarEliminarActivo=function(e){z={tipo:`activo`,id:e},document.getElementById(`deleteNombre`).textContent=`Activo #`+e,new bootstrap.Modal(document.getElementById(`modalEliminar`)).show()},window.confirmarEliminarComponente=function(e){z={tipo:`componente`,id:e},document.getElementById(`deleteNombre`).textContent=`Componente #`+e,new bootstrap.Modal(document.getElementById(`modalEliminar`)).show()};function B(){if(z){var e=`/admin/`+z.tipo+`s/`+z.id;fetch(e,{method:`DELETE`,headers:{"X-CSRF-TOKEN":u(),Accept:`application/json`}}).then(function(e){return e.json()}).then(function(e){bootstrap.Modal.getInstance(document.getElementById(`modalEliminar`)).hide(),e.success?(f(e.message,`success`),z.tipo===`activo`?N():I()):f(e.message||`Error`,`error`),z=null})}}