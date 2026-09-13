// ===========================
// Dashboard Layout JavaScript
// ===========================

document.addEventListener('DOMContentLoaded', function() {

    // ===========================
    // Toggle Sidebar
    // ===========================
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    if (toggleBtn && sidebar && mainContent) {
        // Restaurar estado desde localStorage
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    // ===========================
    // Submenús desplegables
    // ===========================
    document.querySelectorAll('.submenu-toggle').forEach(toggle => {
        const parent = toggle.closest('.has-submenu');
        const submenu = parent ? parent.querySelector('.submenu') : null;
        const submenuId = parent ? parent.getAttribute('data-submenu') : null;

        if (!parent || !submenu) return;

        // Expandir automáticamente si hay un enlace activo dentro
        const hasActiveLink = submenu.querySelector('.nav-link.active');
        if (hasActiveLink) {
            toggle.classList.add('expanded');
            submenu.classList.add('expanded');
        }

        // Restaurar estado desde localStorage
        if (submenuId) {
            const savedState = localStorage.getItem('submenu_' + submenuId);
            if (savedState === 'expanded') {
                toggle.classList.add('expanded');
                submenu.classList.add('expanded');
            } else if (savedState === 'collapsed') {
                toggle.classList.remove('expanded');
                submenu.classList.remove('expanded');
            }
        }

        // Evento click para toggle
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const isExpanded = submenu.classList.contains('expanded');

            if (isExpanded) {
                toggle.classList.remove('expanded');
                submenu.classList.remove('expanded');
                if (submenuId) localStorage.setItem('submenu_' + submenuId, 'collapsed');
            } else {
                toggle.classList.add('expanded');
                submenu.classList.add('expanded');
                if (submenuId) localStorage.setItem('submenu_' + submenuId, 'expanded');
            }
        });
    });

    // ===========================
    // Fecha y hora en tiempo real
    // ===========================
    function updateDateTime() {
        const now = new Date();

        const dateElement = document.getElementById('currentDate');
        const timeElement = document.getElementById('currentTime');
        const dayElement = document.getElementById('currentDay');

        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        if (dayElement) {
            const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            dayElement.textContent = days[now.getDay()];
        }
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);

    // ===========================
    // Mobile sidebar
    // ===========================
    if (window.innerWidth <= 768) {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (sidebar) sidebar.classList.remove('mobile-open');
            });
        });
    }

});


// ==================== CALENDARIO CON DÍAS FERIADOS ====================
class ElegantCalendar {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.currentDate = new Date();
        this.selectedDate = null;
        this.holidays = this.getHolidays();
        this.events = this.getEvents();
        this.render();
    }

    getHolidays() {
        // Días feriados de Venezuela
        return {
            '01-01': { name: 'Año Nuevo', type: 'holiday' },
            '01-06': { name: 'Día de Reyes', type: 'holiday' },
            '02-02': { name: 'Virgen de la Candelaria', type: 'holiday' },
            '03-19': { name: 'Día de San José', type: 'holiday' },
            '04-19': { name: 'Declaración de Independencia', type: 'holiday' },
            '05-01': { name: 'Día del Trabajador', type: 'holiday' },
            '06-24': { name: 'Batalla de Carabobo', type: 'holiday' },
            '07-05': { name: 'Día de la Independencia', type: 'holiday' },
            '07-24': { name: 'Natalicio de Simón Bolívar', type: 'holiday' },
            '10-12': { name: 'Día de la Resistencia Indígena', type: 'holiday' },
            '12-24': { name: 'Nochebuena', type: 'holiday' },
            '12-25': { name: 'Navidad', type: 'holiday' },
            '12-31': { name: 'Fin de Año', type: 'holiday' }
        };
    }

    getEvents() {
        // Eventos adicionales - puedes modificarlos
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 2);
        const nextWeek = new Date(today);
        nextWeek.setDate(today.getDate() + 5);
        
        return [
            { date: this.formatDate(today), title: 'Reunión de equipo', type: 'meeting' },
            { date: this.formatDate(tomorrow), title: 'Mantenimiento preventivo', type: 'work' },
            { date: this.formatDate(nextWeek), title: 'Entrega de informes', type: 'work' },
        ];
    }

    formatDate(date) {
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }

    isHoliday(date) {
        const key = `${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        return this.holidays[key] || null;
    }

    isWeekend(date) {
        const day = date.getDay();
        return day === 0 || day === 6;
    }

    getEventsForDate(date) {
        const dateStr = this.formatDate(date);
        return this.events.filter(event => event.date === dateStr);
    }

    render() {
        if (!this.container) {
            console.error('Contenedor del calendario no encontrado');
            return;
        }

        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        const firstDayOfMonth = new Date(year, month, 1);
        let startDay = firstDayOfMonth.getDay();
        // Ajustar para que la semana comience el lunes (0 = domingo, 1 = lunes...)
        startDay = startDay === 0 ? 6 : startDay - 1;
        
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const prevMonthDays = new Date(year, month, 0).getDate();
        
        const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const weekdays = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        
        let calendarHTML = `
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
                        ${monthNames[month]} ${year}
                    </div>
                    <div class="calendar-weekdays">
                        ${weekdays.map(day => `<div class="calendar-weekday">${day}</div>`).join('')}
                    </div>
                    <div class="calendar-days">
        `;
        
        // Días del mes anterior
        for (let i = 0; i < startDay; i++) {
            const day = prevMonthDays - startDay + i + 1;
            calendarHTML += `<div class="calendar-day other-month">
                <div class="day-number">${day}</div>
                <div class="day-events"></div>
            </div>`;
        }
        
        // Días del mes actual
        const today = new Date();
        const isCurrentMonth = today.getMonth() === month && today.getFullYear() === year;
        
        for (let day = 1; day <= daysInMonth; day++) {
            const currentDate = new Date(year, month, day);
            const isToday = isCurrentMonth && today.getDate() === day;
            const holiday = this.isHoliday(currentDate);
            const isWeekend = this.isWeekend(currentDate);
            const dayEvents = this.getEventsForDate(currentDate);
            
            let dayClass = '';
            if (isToday) dayClass += ' today';
            if (holiday) dayClass += ' holiday';
            if (isWeekend && !holiday) dayClass += ' weekend';
            
            let eventsHTML = '';
            if (holiday) {
                eventsHTML += `<div class="event-badge event-holiday">🎉 ${holiday.name}</div>`;
            }
            dayEvents.forEach(event => {
                let eventClass = event.type === 'meeting' ? 'event-meeting' : 'event-work';
                let eventIcon = event.type === 'meeting' ? '👥' : '🔧';
                eventsHTML += `<div class="event-badge ${eventClass}">${eventIcon} ${event.title}</div>`;
            });
            
            calendarHTML += `
                <div class="calendar-day${dayClass}" onclick="if(window.calendar) window.calendar.showDayEvents(${year}, ${month}, ${day})">
                    <div class="day-number">${day}</div>
                    <div class="day-events">${eventsHTML}</div>
                </div>
            `;
        }
        
        // Días del mes siguiente
        const totalDaysShown = startDay + daysInMonth;
        const remainingDays = 42 - totalDaysShown;
        for (let day = 1; day <= remainingDays; day++) {
            calendarHTML += `<div class="calendar-day other-month">
                <div class="day-number">${day}</div>
                <div class="day-events"></div>
            </div>`;
        }
        
        calendarHTML += `
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
        `;
        
        this.container.innerHTML = calendarHTML;
    }
    
    showDayEvents(year, month, day) {
        const date = new Date(year, month, day);
        const holiday = this.isHoliday(date);
        const isWeekend = this.isWeekend(date);
        const dayEvents = this.getEventsForDate(date);
        
        let modalContent = `
            <div class="event-modal-content">
                <h5 class="mb-3" style="color: #1e3c72;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 8px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    </svg>
                    ${date.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                </h5>
                <div class="event-list">
        `;
        
        if (holiday) {
            modalContent += `
                <div class="event-item" style="padding: 10px; border-bottom: 1px solid #e9ecef;">
                    <span style="background: #fd7e14; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem;">Feriado</span>
                    <span style="margin-left: 10px;">${holiday.name}</span>
                    <span style="margin-left: auto; font-size: 0.7rem; color: #6c757d;">🎉 Día no laborable</span>
                </div>
            `;
        }
        
        if (isWeekend && !holiday) {
            modalContent += `
                <div class="event-item" style="padding: 10px; border-bottom: 1px solid #e9ecef;">
                    <span style="background: #6c757d; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem;">Fin de Semana</span>
                    <span style="margin-left: 10px;">Descanso</span>
                    <span style="margin-left: auto; font-size: 0.7rem; color: #6c757d;">🏖️ Día no laborable</span>
                </div>
            `;
        }
        
        if (dayEvents.length === 0 && !holiday && !isWeekend) {
            modalContent += `
                <div class="text-center text-muted py-4">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4M12 16h.01"/>
                    </svg>
                    <p class="mt-2">No hay eventos programados para este día</p>
                </div>
            `;
        }
        
        dayEvents.forEach(event => {
            let eventStyle = event.type === 'meeting' ? 'background: #17a2b8;' : 'background: #28a745;';
            let eventIcon = event.type === 'meeting' ? '👥' : '🔧';
            
            modalContent += `
                <div class="event-item" style="padding: 10px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; gap: 10px;">
                    <span style="${eventStyle} color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem;">${eventIcon} Evento</span>
                    <span>${event.title}</span>
                </div>
            `;
        });
        
        modalContent += `
                </div>
            </div>
        `;
        
        // Verificar si ya existe un modal abierto
        const existingModal = document.getElementById('calendarEventModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'calendarEventModal';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <h5 class="modal-title">Eventos del día</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${modalContent}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary-dark" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        modal.addEventListener('hidden.bs.modal', function() {
            modal.remove();
        });
    }
    
    prevMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        this.render();
    }
    
    nextMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        this.render();
    }
}

// Inicializar calendario cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando calendario...');
    const calendarContainer = document.getElementById('elegantCalendar');
    if (calendarContainer) {
        window.calendar = new ElegantCalendar('elegantCalendar');
        console.log('Calendario inicializado correctamente');
    } else {
        console.error('No se encontró el elemento con id "elegantCalendar"');
    }
});