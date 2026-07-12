document.addEventListener(`DOMContentLoaded`,function(){let e=document.getElementById(`toggleSidebar`),t=document.getElementById(`sidebar`),n=document.getElementById(`mainContent`);e&&t&&n&&(localStorage.getItem(`sidebarCollapsed`)===`true`&&(t.classList.add(`collapsed`),n.classList.add(`expanded`)),e.addEventListener(`click`,()=>{t.classList.toggle(`collapsed`),n.classList.toggle(`expanded`),localStorage.setItem(`sidebarCollapsed`,t.classList.contains(`collapsed`))})),document.querySelectorAll(`.submenu-toggle`).forEach(e=>{let t=e.closest(`.has-submenu`),n=t?t.querySelector(`.submenu`):null,r=t?t.getAttribute(`data-submenu`):null;if(!(!t||!n)){if(n.querySelector(`.nav-link.active`)&&(e.classList.add(`expanded`),n.classList.add(`expanded`)),r){let t=localStorage.getItem(`submenu_`+r);t===`expanded`?(e.classList.add(`expanded`),n.classList.add(`expanded`)):t===`collapsed`&&(e.classList.remove(`expanded`),n.classList.remove(`expanded`))}e.addEventListener(`click`,function(t){t.preventDefault(),t.stopPropagation(),n.classList.contains(`expanded`)?(e.classList.remove(`expanded`),n.classList.remove(`expanded`),r&&localStorage.setItem(`submenu_`+r,`collapsed`)):(e.classList.add(`expanded`),n.classList.add(`expanded`),r&&localStorage.setItem(`submenu_`+r,`expanded`))})}});function r(){let e=new Date,t=document.getElementById(`currentDate`),n=document.getElementById(`currentTime`),r=document.getElementById(`currentDay`);t&&(t.textContent=e.toLocaleDateString(`es-ES`,{year:`numeric`,month:`long`,day:`numeric`})),n&&(n.textContent=e.toLocaleTimeString(`es-ES`,{hour:`2-digit`,minute:`2-digit`,second:`2-digit`})),r&&(r.textContent=[`Domingo`,`Lunes`,`Martes`,`Miércoles`,`Jueves`,`Viernes`,`Sábado`][e.getDay()])}r(),setInterval(r,1e3),window.innerWidth<=768&&document.querySelectorAll(`.nav-link`).forEach(e=>{e.addEventListener(`click`,()=>{t&&t.classList.remove(`mobile-open`)})})});var e=class{constructor(e){this.container=document.getElementById(e),this.currentDate=new Date,this.selectedDate=null,this.holidays=this.getHolidays(),this.events=this.getEvents(),this.render()}getHolidays(){return{"01-01":{name:`Año Nuevo`,type:`holiday`},"01-06":{name:`Día de Reyes`,type:`holiday`},"02-02":{name:`Virgen de la Candelaria`,type:`holiday`},"03-19":{name:`Día de San José`,type:`holiday`},"04-19":{name:`Declaración de Independencia`,type:`holiday`},"05-01":{name:`Día del Trabajador`,type:`holiday`},"06-24":{name:`Batalla de Carabobo`,type:`holiday`},"07-05":{name:`Día de la Independencia`,type:`holiday`},"07-24":{name:`Natalicio de Simón Bolívar`,type:`holiday`},"10-12":{name:`Día de la Resistencia Indígena`,type:`holiday`},"12-24":{name:`Nochebuena`,type:`holiday`},"12-25":{name:`Navidad`,type:`holiday`},"12-31":{name:`Fin de Año`,type:`holiday`}}}getEvents(){let e=new Date,t=new Date(e);t.setDate(e.getDate()+2);let n=new Date(e);return n.setDate(e.getDate()+5),[{date:this.formatDate(e),title:`Reunión de equipo`,type:`meeting`},{date:this.formatDate(t),title:`Mantenimiento preventivo`,type:`work`},{date:this.formatDate(n),title:`Entrega de informes`,type:`work`}]}formatDate(e){return`${e.getFullYear()}-${String(e.getMonth()+1).padStart(2,`0`)}-${String(e.getDate()).padStart(2,`0`)}`}isHoliday(e){let t=`${String(e.getMonth()+1).padStart(2,`0`)}-${String(e.getDate()).padStart(2,`0`)}`;return this.holidays[t]||null}isWeekend(e){let t=e.getDay();return t===0||t===6}getEventsForDate(e){let t=this.formatDate(e);return this.events.filter(e=>e.date===t)}render(){if(!this.container){console.error(`Contenedor del calendario no encontrado`);return}let e=this.currentDate.getFullYear(),t=this.currentDate.getMonth(),n=new Date(e,t,1).getDay();n=n===0?6:n-1;let r=new Date(e,t+1,0).getDate(),i=new Date(e,t,0).getDate(),a=`
            <div class="calendar-card">
                <div class="calendar-header">
                    <div class="calendar-title">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <div>
                            <h4>Calendario de Actividades</h4>
                            <p>Días feriados y eventos importantes</p>
                        </div>
                    </div>
                    <div class="calendar-nav">
                        <button class="calendar-nav-btn" onclick="if(window.calendar) window.calendar.prevMonth()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                            Anterior
                        </button>
                        <button class="calendar-nav-btn" onclick="if(window.calendar) window.calendar.nextMonth()">
                            Siguiente
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="calendar-grid">
                    <div class="calendar-month-year">
                        ${[`Enero`,`Febrero`,`Marzo`,`Abril`,`Mayo`,`Junio`,`Julio`,`Agosto`,`Septiembre`,`Octubre`,`Noviembre`,`Diciembre`][t]} ${e}
                    </div>
                    <div class="calendar-weekdays">
                        ${[`Lun`,`Mar`,`Mié`,`Jue`,`Vie`,`Sáb`,`Dom`].map(e=>`<div class="calendar-weekday">${e}</div>`).join(``)}
                    </div>
                    <div class="calendar-days">
        `;for(let e=0;e<n;e++){let t=i-n+e+1;a+=`<div class="calendar-day other-month">
                <div class="day-number">${t}</div>
                <div class="day-events"></div>
            </div>`}let o=new Date,s=o.getMonth()===t&&o.getFullYear()===e;for(let n=1;n<=r;n++){let r=new Date(e,t,n),i=s&&o.getDate()===n,c=this.isHoliday(r),l=this.isWeekend(r),u=this.getEventsForDate(r),d=``;i&&(d+=` today`),c&&(d+=` holiday`),l&&!c&&(d+=` weekend`);let f=``;c&&(f+=`<div class="event-badge event-holiday">🎉 ${c.name}</div>`),u.forEach(e=>{let t=e.type===`meeting`?`event-meeting`:`event-work`,n=e.type===`meeting`?`👥`:`🔧`;f+=`<div class="event-badge ${t}">${n} ${e.title}</div>`}),a+=`
                <div class="calendar-day${d}" onclick="if(window.calendar) window.calendar.showDayEvents(${e}, ${t}, ${n})">
                    <div class="day-number">${n}</div>
                    <div class="day-events">${f}</div>
                </div>
            `}let c=42-(n+r);for(let e=1;e<=c;e++)a+=`<div class="calendar-day other-month">
                <div class="day-number">${e}</div>
                <div class="day-events"></div>
            </div>`;a+=`
                    </div>
                    <div class="calendar-legend">
                        <div class="legend-item">
                            <div class="legend-color today"></div>
                            <span>Hoy</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color holiday"></div>
                            <span>Día Feriado</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color weekend"></div>
                            <span>Fin de Semana</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #28a745;"></div>
                            <span>Evento Laboral</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #17a2b8;"></div>
                            <span>Reunión</span>
                        </div>
                    </div>
                </div>
            </div>
        `,this.container.innerHTML=a}showDayEvents(e,t,n){let r=new Date(e,t,n),i=this.isHoliday(r),a=this.isWeekend(r),o=this.getEventsForDate(r),s=`
            <div class="event-modal-content">
                <h5 class="mb-3" style="color: #1e3c72;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 8px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    </svg>
                    ${r.toLocaleDateString(`es-ES`,{weekday:`long`,year:`numeric`,month:`long`,day:`numeric`})}
                </h5>
                <div class="event-list">
        `;i&&(s+=`
                <div class="event-item" style="padding: 10px; border-bottom: 1px solid #e9ecef;">
                    <span style="background: #fd7e14; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem;">Feriado</span>
                    <span style="margin-left: 10px;">${i.name}</span>
                    <span style="margin-left: auto; font-size: 0.7rem; color: #6c757d;">🎉 Día no laborable</span>
                </div>
            `),a&&!i&&(s+=`
                <div class="event-item" style="padding: 10px; border-bottom: 1px solid #e9ecef;">
                    <span style="background: #6c757d; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem;">Fin de Semana</span>
                    <span style="margin-left: 10px;">Descanso</span>
                    <span style="margin-left: auto; font-size: 0.7rem; color: #6c757d;">🏖️ Día no laborable</span>
                </div>
            `),o.length===0&&!i&&!a&&(s+=`
                <div class="text-center text-muted py-4">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4M12 16h.01"/>
                    </svg>
                    <p class="mt-2">No hay eventos programados para este día</p>
                </div>
            `),o.forEach(e=>{let t=e.type===`meeting`?`background: #17a2b8;`:`background: #28a745;`,n=e.type===`meeting`?`👥`:`🔧`;s+=`
                <div class="event-item" style="padding: 10px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; gap: 10px;">
                    <span style="${t} color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem;">${n} Evento</span>
                    <span>${e.title}</span>
                </div>
            `}),s+=`
                </div>
            </div>
        `;let c=document.getElementById(`calendarEventModal`);c&&c.remove();let l=document.createElement(`div`);l.className=`modal fade`,l.id=`calendarEventModal`,l.setAttribute(`tabindex`,`-1`),l.setAttribute(`aria-hidden`,`true`),l.innerHTML=`
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <h5 class="modal-title">Eventos del día</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${s}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        `,document.body.appendChild(l),new bootstrap.Modal(l).show(),l.addEventListener(`hidden.bs.modal`,function(){l.remove()})}prevMonth(){this.currentDate.setMonth(this.currentDate.getMonth()-1),this.render()}nextMonth(){this.currentDate.setMonth(this.currentDate.getMonth()+1),this.render()}};document.addEventListener(`DOMContentLoaded`,function(){console.log(`Inicializando calendario...`),document.getElementById(`elegantCalendar`)?(window.calendar=new e(`elegantCalendar`),console.log(`Calendario inicializado correctamente`)):console.error(`No se encontró el elemento con id "elegantCalendar"`)});