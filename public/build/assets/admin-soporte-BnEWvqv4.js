var e=[],t=1,n=1,r=10,i=0,a=null,o=null,s=[],c=document.querySelector(`meta[name="csrf-token"]`)?.content,l={search:``,estado:``};function u(e){if(!e)return``;let t=document.createElement(`div`);return t.textContent=e,t.innerHTML}function d(e,t){let n=document.getElementById(`notification-container`);n||(n=document.createElement(`div`),n.id=`notification-container`,n.style.cssText=`position: fixed; top: 20px; right: 20px; z-index: 9999; width: 320px;`,document.body.appendChild(n));let r={success:`#28a745`,error:`#dc3545`,warning:`#ffc107`,info:`#17a2b8`},i=document.createElement(`div`);i.style.cssText=`background: ${r[e]}; color: white; border-radius: 10px; padding: 12px 16px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; cursor: pointer; animation: slideIn 0.3s ease-out; z-index: 10000;`,i.innerHTML=`<span style="flex:1">${t}</span><span style="opacity:0.7; cursor:pointer;" onclick="this.parentElement.remove()">✕</span>`,n.appendChild(i),setTimeout(()=>{i.parentNode&&i.remove()},4e3)}var f=document.createElement(`style`);f.textContent=`
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`,document.head.appendChild(f);function p(){let t=e.length,n=e.filter(e=>e.estado===`en_proceso`).length,r=e.filter(e=>e.estado===`finalizado`).length;document.getElementById(`statsTotal`)&&(document.getElementById(`statsTotal`).textContent=t),document.getElementById(`statsEnProceso`)&&(document.getElementById(`statsEnProceso`).textContent=n),document.getElementById(`statsFinalizados`)&&(document.getElementById(`statsFinalizados`).textContent=r);let i=e.filter(e=>e.estado===`en_proceso`).length;document.getElementById(`statsEquiposReparacion`)&&(document.getElementById(`statsEquiposReparacion`).textContent=i),s=e.filter(e=>e.estado===`en_proceso`).map(e=>e.activo_id)}function m(){let t=document.getElementById(`tablaFichas`);if(!t)return;if(e.length===0){t.innerHTML=`<tr><td colspan="7" class="text-center py-4 text-muted">No hay fichas de soporte registradas</td></tr>`;return}let n=``;for(let t of e){let e=t.fecha_ingreso?new Date(t.fecha_ingreso).toLocaleDateString():`N/A`,r=t.fecha_salida?new Date(t.fecha_salida).toLocaleDateString():`—`,i=t.activo?`${t.activo.serial} - ${t.activo.modelo?.nombre||`N/A`}`:`N/A`,a=t.estado===`en_proceso`?`badge-estado-en-proceso`:`badge-estado-finalizado`,o=t.estado===`en_proceso`?`En Proceso`:`Finalizado`;n+=`<tr>
            <td class="px-3 py-2">${u(i)}</td>
            <td class="px-3 py-2">${u(t.tecnico_nombre||`—`)}</td>
            <td class="px-3 py-2">${u(t.usuario_reporta_nombre||`—`)}</td>
            <td class="px-3 py-2">${e}</td>
            <td class="px-3 py-2">${r}</td>
            <td class="px-3 py-2"><span class="${a}">${o}</span></td>
            <td class="px-3 py-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-primary-dark" onclick="verDetalle(${t.id})" title="Ver detalle">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                </button>`,t.estado===`en_proceso`&&(n+=`
                <button type="button" class="btn btn-sm btn-cerrar-ficha ms-1" onclick="abrirModalCerrarFicha(${t.id})" title="Cerrar ficha">
                    ✓ Cerrar
                </button>`),n+=`
                <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="confirmarEliminar(${t.id})" title="Eliminar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                </button>
            </td>
        </tr>`}t.innerHTML=n}function h(){let r=document.getElementById(`paginationContainer`);if(!r)return;if(n<=1){r.innerHTML=``;return}let a=`<li class="page-item ${t===1?`disabled`:``}"><a class="page-link" href="#" onclick="cambiarPagina(${t-1}); return false;">«</a></li>`,o=Math.max(1,t-2),s=Math.min(n,t+2);o>1&&(a+=`<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(1); return false;">1</a></li>`,o>2&&(a+=`<li class="page-item disabled"><span class="page-link">...</span></li>`));for(let e=o;e<=s;e++)a+=`<li class="page-item ${e===t?`active`:``}"><a class="page-link" href="#" onclick="cambiarPagina(${e}); return false;">${e}</a></li>`;s<n&&(s<n-1&&(a+=`<li class="page-item disabled"><span class="page-link">...</span></li>`),a+=`<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${n}); return false;">${n}</a></li>`),a+=`<li class="page-item ${t===n?`disabled`:``}"><a class="page-link" href="#" onclick="cambiarPagina(${t+1}); return false;">»</a></li>`,r.innerHTML=a;let c=document.getElementById(`paginationInfo`);c&&(c.innerHTML=`Mostrando ${e.length} de ${i} registros`)}async function g(a){try{let o=new URLSearchParams({page:a,per_page:r,buscar:l.search,estado:l.estado}),s=await fetch(`/admin/soporte?${o.toString()}`,{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`}});if(!s.ok)throw Error(`Error al cargar datos`);let c=await s.json();e=c.data||[],t=c.current_page||1,n=c.last_page||1,r=c.per_page||10,i=c.total||0,m(),p(),h()}catch(e){console.error(`Error:`,e),d(`error`,`No se pudieron cargar las fichas`),document.getElementById(`tablaFichas`).innerHTML=`<tr><td colspan="8" class="text-center py-4 text-danger">Error al cargar los datos</td></tr>`}}function _(){l={search:document.getElementById(`buscarFichas`)?.value||``,estado:document.getElementById(`filtroEstadoFichas`)?.value||``},t=1,g(1)}function v(){clearTimeout(a),a=setTimeout(()=>{_()},300)}function y(e){return s.includes(parseInt(e))}window.abrirModalCrearFicha=function(){document.getElementById(`formCrearFicha`).reset();let e=document.getElementById(`activoErrorMensaje`);e&&(e.style.display=`none`),new bootstrap.Modal(document.getElementById(`modalCrearFicha`)).show()},document.getElementById(`fichaActivoId`)?.addEventListener(`change`,function(){let e=this.value,t=document.getElementById(`activoErrorMensaje`),n=document.querySelector(`#formCrearFicha button[type="submit"]`);e&&y(e)?(t&&(t.style.display=`block`,t.innerHTML=`
                <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert" style="font-size: 0.8rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 5px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <strong>¡Activo no disponible!</strong> Este equipo ya tiene una ficha de soporte en proceso.
                    <button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>
                </div>
            `),n&&(n.disabled=!0),this.classList.add(`is-invalid`)):(t&&(t.style.display=`none`),n&&(n.disabled=!1),this.classList.remove(`is-invalid`),this.classList.add(`is-valid`))}),document.getElementById(`formCrearFicha`)?.addEventListener(`submit`,async function(e){e.preventDefault();let t=document.getElementById(`fichaActivoId`).value;if(y(t)){d(`error`,`Este activo ya tiene una ficha de soporte en proceso. No puede crear otra.`);return}let n=this.querySelector(`button[type="submit"]`),r=n.innerHTML;n.innerHTML=`<span class="spinner-border spinner-border-sm me-2"></span> Creando...`,n.disabled=!0;let i=new FormData(this);try{let e=await fetch(`/admin/soporte`,{method:`POST`,headers:{"X-CSRF-TOKEN":c,Accept:`application/json`},body:i}),t=await e.json();e.ok&&t.success?(d(`success`,t.message||`Ficha creada exitosamente`),bootstrap.Modal.getInstance(document.getElementById(`modalCrearFicha`)).hide(),g(1)):d(`error`,t.message||`Error al crear la ficha`)}catch(e){console.error(`Error:`,e),d(`error`,`Error de conexión al servidor`)}finally{n.innerHTML=r,n.disabled=!1}}),window.abrirModalCerrarFicha=async function(e){try{let t=await(await fetch(`/admin/soporte/${e}/componentes`)).json();if(t.success&&t.data){document.getElementById(`cerrarFichaId`).value=e;let n=``;for(let e of t.data)n+=`
                    <div class="componente-row border rounded p-3 mb-3" style="background: #f8f9fc;">
                        <input type="hidden" name="detalles[${e.id}][id]" value="${e.id}">
                        <div class="fw-bold mb-2" style="color: #1e3c72;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:5px;">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            ${u(e.componente_nombre)}
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Estado Salida</label>
                                <select name="detalles[${e.id}][estado_salida]" class="form-select form-select-sm">
                                    <option value="funcionando">✅ Funcionando</option>
                                    <option value="dañado">⚠️ Dañado</option>
                                    <option value="reemplazado">🔄 Reemplazado</option>
                                    <option value="reparado">🔧 Reparado</option>
                                    <option value="no_aplica">❌ No Aplica</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Observaciones</label>
                                <input type="text" name="detalles[${e.id}][observaciones]" class="form-control form-control-sm" placeholder="Observaciones del componente...">
                            </div>
                        </div>
                    </div>
                `;document.getElementById(`componentesContainer`).innerHTML=n,new bootstrap.Modal(document.getElementById(`modalCerrarFicha`)).show()}else d(`error`,`No se pudieron cargar los componentes`)}catch(e){console.error(`Error:`,e),d(`error`,`Error de conexión al cargar componentes`)}},document.getElementById(`formCerrarFicha`)?.addEventListener(`submit`,async function(e){e.preventDefault();let n=document.getElementById(`cerrarFichaId`).value,r=this.querySelector(`button[type="submit"]`),i=r.innerHTML;r.innerHTML=`<span class="spinner-border spinner-border-sm me-2"></span> Finalizando...`,r.disabled=!0;let a=new FormData(this);try{let e=await fetch(`/admin/soporte/${n}/close`,{method:`POST`,headers:{"X-CSRF-TOKEN":c,Accept:`application/json`},body:a}),r=await e.json();e.ok&&r.success?(d(`success`,r.message||`Ficha finalizada exitosamente`),bootstrap.Modal.getInstance(document.getElementById(`modalCerrarFicha`)).hide(),g(t)):d(`error`,r.message||`Error al finalizar la ficha`)}catch(e){console.error(`Error:`,e),d(`error`,`Error de conexión`)}finally{r.innerHTML=i,r.disabled=!1}}),window.verDetalle=async function(e){let t=document.getElementById(`detalleContenido`);t.innerHTML=`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando detalles...</p></div>`,new bootstrap.Modal(document.getElementById(`modalDetalle`)).show();try{let n=await(await fetch(`/admin/soporte/${e}`)).json();if(n.success&&n.data){let e=n.data,r=e.fecha_ingreso?new Date(e.fecha_ingreso).toLocaleString():`No registrada`,i=e.fecha_salida?new Date(e.fecha_salida).toLocaleString():`En proceso`,a=e.estado===`en_proceso`?`#fd7e14`:`#28a745`,o=e.estado===`en_proceso`?`🔧`:`✅`,s=``;e.detalles&&e.detalles.length>0&&(s=`
                    <div class="detalle-seccion mb-3">
                        <h6 class="fw-bold" style="color: #1e3c72;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                            </svg>
                            Componentes Revisados (${e.detalles.length})
                        </h6>
                        <div class="row">
                            ${e.detalles.map(e=>`
                                <div class="col-md-6 mb-2">
                                    <div class="border rounded p-2" style="background: #f8f9fc;">
                                        <strong>${u(e.componente_nombre)}</strong><br>
                                        ${e.estado_salida?`<span class="badge ${e.estado_salida===`funcionando`?`bg-success`:e.estado_salida===`reemplazado`?`bg-warning`:`bg-danger`} text-white">
                                                Salida: ${u(e.estado_salida)}
                                            </span>`:`<span class="badge bg-info">Ingreso: ${u(e.estado_ingreso||`N/A`)}</span>`}
                                        ${e.observaciones?`<br><small class="text-muted">${u(e.observaciones)}</small>`:``}
                                    </div>
                                </div>
                            `).join(``)}
                        </div>
                    </div>
                `);let c=`
                <div>
                    <div style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); margin: -1rem -1rem 1.5rem -1rem; padding: 1.5rem; border-radius: 12px 12px 0 0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 text-white">Ficha de Soporte #${e.id}</h4>
                                <p class="mb-0 text-white-50 mt-1">
                                    ${u(e.activo?.serial||`N/A`)} - ${u(e.activo?.modelo?.nombre||`N/A`)}
                                </p>
                            </div>
                            <span style="background: ${a}; color: white; padding: 0.5rem 1rem; border-radius: 30px;">
                                ${o} ${e.estado===`en_proceso`?`En Proceso`:`Finalizado`}
                            </span>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Técnico</label>
                                <div class="fw-semibold">${u(e.tecnico_nombre||`No asignado`)}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Usuario Reporta</label>
                                <div class="fw-semibold">${u(e.usuario_reporta_nombre||`No especificado`)}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Fecha Ingreso</label>
                                <div class="fw-semibold">${r}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Fecha Salida</label>
                                <div class="fw-semibold">${i}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Diagnóstico Inicial</label>
                        <div class="p-2 bg-light rounded">${u(e.diagnostico||`No registrado`)}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Trabajo Realizado</label>
                        <div class="p-2 bg-light rounded">${u(e.trabajo_realizado||`No registrado`)}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Observaciones</label>
                        <div class="p-2 bg-light rounded">${u(e.observaciones||`Sin observaciones`)}</div>
                    </div>
                    
                    ${s}
                </div>
            `;document.getElementById(`modalDetalleLabel`).textContent=`Detalle de Ficha de Soporte`,t.innerHTML=c}else t.innerHTML=`<div class="text-center text-danger py-4">Error al cargar detalle</div>`}catch(e){console.error(`Error:`,e),t.innerHTML=`<div class="text-center text-danger py-4">Error de conexión</div>`}},window.confirmarEliminar=function(e){o=e,document.getElementById(`deleteNombre`).textContent=`Ficha #${e}`,new bootstrap.Modal(document.getElementById(`modalEliminar`)).show()},document.getElementById(`btnConfirmarEliminar`)?.addEventListener(`click`,async function(){if(o)try{let e=await fetch(`/admin/soporte/${o}`,{method:`DELETE`,headers:{"X-CSRF-TOKEN":c,Accept:`application/json`}}),n=await e.json();bootstrap.Modal.getInstance(document.getElementById(`modalEliminar`)).hide(),e.ok&&n.success?(d(`success`,n.message),g(t)):d(`error`,n.message||`Error al eliminar`),o=null}catch(e){console.error(`Error:`,e),d(`error`,`Error de conexión`),o=null}}),document.getElementById(`limpiarFiltros`)?.addEventListener(`click`,function(){document.getElementById(`buscarFichas`).value=``,document.getElementById(`filtroEstadoFichas`).value=``,_()});function b(){document.getElementById(`buscarFichas`)?.addEventListener(`input`,v),document.getElementById(`filtroEstadoFichas`)?.addEventListener(`change`,_)}document.addEventListener(`DOMContentLoaded`,function(){console.log(`Módulo de fichas de soporte inicializado`),b(),g(1)});