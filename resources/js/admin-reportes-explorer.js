
/* ============================================================
   EXPLORADOR DE REPORTES - JS
   ============================================================ */

(function () {
    "use strict";

    const CFG = window.__REPORTES__;
    if (!CFG) {
        console.error("[Reportes] No se encontro window.__REPORTES__");
        return;
    }

    const state = {
        key: null,
        filters: {},
        page: 1,
    };

    const el = {
        sidebar:     document.getElementById("reportesSidebar"),
        sidebarNav:  document.getElementById("sidebarNav"),
        sidebarSearch: document.getElementById("sidebarSearch"),
        panelVacio:  document.getElementById("panelVacio"),
        panelCont:   document.getElementById("panelContenido"),
        titulo:      document.getElementById("reporteTitulo"),
        desc:        document.getElementById("reporteDesc"),
        codigo:      document.getElementById("reporteCodigo"),
        filtros:     document.getElementById("panelFiltros"),
        stats:       document.getElementById("panelStats"),
        tabla:       document.getElementById("panelTabla"),
        paginacion:  document.getElementById("panelPaginacion"),
        btnPdf:      document.getElementById("btnExportPdf"),
        btnXlsx:     document.getElementById("btnExportXlsx"),
        btnCsv:      document.getElementById("btnExportCsv"),
    };

    // ============================================================
    // HELPERS
    // ============================================================
    function escapeHtml(s) {
        if (s === null || s === undefined) return "";
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatValue(value, format) {
        if (value === null || value === undefined || value === "") return "-";

        if (format === "date") {
            try {
                const d = new Date(value);
                if (isNaN(d.getTime())) return value;
                const dd = String(d.getDate()).padStart(2, "0");
                const mm = String(d.getMonth() + 1).padStart(2, "0");
                const yy = d.getFullYear();
                return dd + "/" + mm + "/" + yy;
            } catch (e) { return value; }
        }

        if (format === "money") {
            const n = parseFloat(value);
            if (isNaN(n)) return value;
            return n.toLocaleString("es-VE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        return value;
    }

    function badgeClass(value) {
        const v = String(value).toLowerCase().trim();
        if (["disponible", "activo", "activa", "aprobada", "aprobado", "devuelto", "finalizado", "entregado"].includes(v)) {
            return "badge-estado badge-estado-disponible";
        }
        if (["pendiente", "prestado", "extendido", "en proceso", "en_proceso"].includes(v)) {
            return "badge-estado badge-estado-prestado";
        }
        if (["vencido", "rechazado", "rechazada", "desechado", "en reparacion", "en_reparacion"].includes(v)) {
            return "badge-estado badge-estado-en-reparacion";
        }
        if (["en bodega", "en_bodega", "reservado", "inactivo", "inactiva"].includes(v)) {
            return "badge-estado badge-estado-en-bodega";
        }
        return "badge-estado badge-estado-default";
    }

    // ============================================================
    // CARGAR REPORTE
    // ============================================================
    async function cargarReporte(key) {
        state.key = key;
        state.page = 1;

        marcarActivo(key);
        mostrarLoading();

        const url = CFG.urls.data.replace("__KEY__", key);
        const qs  = new URLSearchParams(flatParams());

        try {
            const res = await fetch(url + "?" + qs.toString(), {
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
            });

            if (!res.ok) throw new Error("HTTP " + res.status);

            const data = await res.json();

            if (!data.success) throw new Error(data.message || "Error al cargar reporte");

            renderHeader(data.report, data.codigo_documento);
            renderFiltros(data.filters, data.report.key);
            renderStats(data.summary);
            renderTabla(data.columns, data.rows);
            renderPaginacion(data.pagination);
            actualizarLinksExport(data.report.key);

        } catch (err) {
            console.error("[Reportes]", err);
            el.tabla.innerHTML =
                "<div class=\"tabla-vacia\">" +
                    "<div class=\"vacio-icon\">⚠️</div>" +
                    "<p>Error al cargar el reporte: " + escapeHtml(err.message) + "</p>" +
                "</div>";
        }
    }

    // ============================================================
    // HEADER
    // ============================================================
    function renderHeader(report, codigo) {
        el.panelVacio.style.display = "none";
        el.panelCont.style.display = "flex";

        el.titulo.textContent = report.title;
        el.desc.textContent = report.description;
        el.codigo.textContent = "Documento: " + codigo;
    }

    // ============================================================
    // FILTROS
    // ============================================================
    function renderFiltros(filters, key) {
        const partes = [];

        Object.keys(filters).forEach(function (name) {
            const f = filters[name];
            const current = state.filters[name];

            if (f.type === "daterange") {
                const from = (current && current.from) || (f.default && f.default.from) || "";
                const to   = (current && current.to)   || (f.default && f.default.to)   || "";

                if (!state.filters[name]) {
                    state.filters[name] = { from: from, to: to };
                }

                partes.push(
                    "<div class=\"filtro-item daterange\">" +
                        "<label>" + escapeHtml(f.label) + "</label>" +
                        "<div class=\"filtro-daterange-group\">" +
                            "<input type=\"date\" data-filter=\"" + name + "\" data-sub=\"from\" value=\"" + from + "\">" +
                            "<input type=\"date\" data-filter=\"" + name + "\" data-sub=\"to\" value=\"" + to + "\">" +
                        "</div>" +
                    "</div>"
                );
            } else if (f.type === "select") {
                const options = [];

                if (f.source) {
                    const fuentes = CFG.fuentes[f.source] || [];
                    options.push("<option value=\"\">Todos</option>");
                    fuentes.forEach(function (opt) {
                        const sel = (current == opt.value) ? "selected" : "";
                        options.push("<option value=\"" + opt.value + "\" " + sel + ">" + escapeHtml(opt.label) + "</option>");
                    });
                } else {
                    const opts = f.options || {};
                    Object.keys(opts).forEach(function (value) {
                        const sel = (current == value) ? "selected" : "";
                        options.push("<option value=\"" + value + "\" " + sel + ">" + escapeHtml(opts[value]) + "</option>");
                    });
                }

                partes.push(
                    "<div class=\"filtro-item\">" +
                        "<label>" + escapeHtml(f.label) + "</label>" +
                        "<select data-filter=\"" + name + "\">" +
                            options.join("") +
                        "</select>" +
                    "</div>"
                );
            } else if (f.type === "text") {
                partes.push(
                    "<div class=\"filtro-item\">" +
                        "<label>" + escapeHtml(f.label) + "</label>" +
                        "<input type=\"text\" data-filter=\"" + name + "\" value=\"" + escapeHtml(current || "") + "\">" +
                    "</div>"
                );
            } else if (f.type === "date") {
                partes.push(
                    "<div class=\"filtro-item\">" +
                        "<label>" + escapeHtml(f.label) + "</label>" +
                        "<input type=\"date\" data-filter=\"" + name + "\" value=\"" + (current || "") + "\">" +
                    "</div>"
                );
            }
        });

        partes.push(
            "<div class=\"filtro-acciones\">" +
                "<button type=\"button\" class=\"btn-aplicar\" id=\"btnAplicarFiltros\">Aplicar</button>" +
            "</div>"
        );

        el.filtros.innerHTML = partes.join("");

        // Listeners
        el.filtros.querySelectorAll("[data-filter]").forEach(function (input) {
            input.addEventListener("change", function () {
                const name = this.dataset.filter;
                const sub  = this.dataset.sub;

                if (sub) {
                    state.filters[name] = state.filters[name] || {};
                    state.filters[name][sub] = this.value;
                } else {
                    state.filters[name] = this.value;
                }
            });
        });

        document.getElementById("btnAplicarFiltros").addEventListener("click", function () {
            state.page = 1;
            recargar();
        });
    }

    function flatParams() {
        const params = {};

        Object.keys(state.filters).forEach(function (name) {
            const v = state.filters[name];

            if (v && typeof v === "object" && !Array.isArray(v)) {
                if (v.from) params[name + "[from]"] = v.from;
                if (v.to)   params[name + "[to]"]   = v.to;
            } else if (v !== null && v !== undefined && v !== "") {
                params[name] = v;
            }
        });

        params["page"] = state.page;
        return params;
    }

    function recargar() {
        if (!state.key) return;

        const url = CFG.urls.data.replace("__KEY__", state.key);
        const qs  = new URLSearchParams(flatParams());

        mostrarLoading();

        fetch(url + "?" + qs.toString(), {
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "same-origin",
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) throw new Error(data.message || "Error");

            renderStats(data.summary);
            renderTabla(data.columns, data.rows);
            renderPaginacion(data.pagination);
            actualizarLinksExport(state.key);
        })
        .catch(function (err) {
            console.error("[Reportes]", err);
            el.tabla.innerHTML =
                "<div class=\"tabla-vacia\">" +
                    "<div class=\"vacio-icon\">⚠️</div>" +
                    "<p>Error: " + escapeHtml(err.message) + "</p>" +
                "</div>";
        });
    }

    // ============================================================
    // STATS
    // ============================================================
    function renderStats(summary) {
        const keys = Object.keys(summary || {});

        if (!keys.length) {
            el.stats.innerHTML = "";
            el.stats.style.display = "none";
            return;
        }

        el.stats.style.display = "grid";
        el.stats.innerHTML = keys.map(function (label) {
            const val = summary[label];
            const display = (typeof val === "number") ? val.toLocaleString("es-VE") : val;
            return (
                "<div class=\"stat-card-explorer\">" +
                    "<div class=\"num\">" + escapeHtml(display) + "</div>" +
                    "<div class=\"lbl\">" + escapeHtml(label) + "</div>" +
                "</div>"
            );
        }).join("");
    }

    // ============================================================
    // TABLA
    // ============================================================
    function renderTabla(columns, rows) {
        if (!rows || !rows.length) {
            el.tabla.innerHTML =
                "<div class=\"tabla-vacia\">" +
                    "<div class=\"vacio-icon\">📭</div>" +
                    "<p>No hay registros con los filtros aplicados.</p>" +
                "</div>";
            return;
        }

        const thead = "<tr>" + columns.map(function (c) {
            const align = c.align || "left";
            const style = (c.width ? "width:" + c.width + ";" : "") +
                          (align === "center" ? "text-align:center;" : align === "right" ? "text-align:right;" : "");
            return "<th style=\"" + style + "\">" + escapeHtml(c.label) + "</th>";
        }).join("") + "</tr>";

        const tbody = rows.map(function (row) {
            return "<tr>" + columns.map(function (c) {
                let value = dataGet(row, c.key);
                const align = c.align || "left";
                const alignClass = align === "center" ? "text-center" : align === "right" ? "text-end" : "";

                let cell;
                if (c.format === "badge" && value) {
                    cell = "<span class=\"" + badgeClass(value) + "\">" + escapeHtml(value) + "</span>";
                } else {
                    cell = escapeHtml(formatValue(value, c.format));
                }

                return "<td class=\"" + alignClass + "\">" + cell + "</td>";
            }).join("") + "</tr>";
        }).join("");

        el.tabla.innerHTML = "<table class=\"tabla-reporte\"><thead>" + thead + "</thead><tbody>" + tbody + "</tbody></table>";
    }

    // ============================================================
    // PAGINACION
    // ============================================================
    function renderPaginacion(p) {
        if (!p || p.last_page <= 1) {
            el.paginacion.innerHTML =
                "<div class=\"paginacion-info\">" + (p ? p.total : 0) + " registro(s)</div>";
            return;
        }

        const current = p.current_page;
        const last    = p.last_page;

        const parts = [];

        parts.push(
            "<div class=\"paginacion-info\">Mostrando " +
            p.from + " a " + p.to + " de " + p.total + " registros</div>"
        );

        const btns = [];

        btns.push(buildBtn("«", current - 1, current === 1));

        let start = Math.max(1, current - 2);
        let end   = Math.min(last, current + 2);

        if (start > 1) {
            btns.push(buildBtn("1", 1, false));
            if (start > 2) btns.push("<span style=\"padding:0 0.4rem;color:#94a3b8;\">...</span>");
        }

        for (let i = start; i <= end; i++) {
            btns.push(buildBtn(String(i), i, false, i === current));
        }

        if (end < last) {
            if (end < last - 1) btns.push("<span style=\"padding:0 0.4rem;color:#94a3b8;\">...</span>");
            btns.push(buildBtn(String(last), last, false));
        }

        btns.push(buildBtn("»", current + 1, current === last));

        parts.push("<div class=\"paginacion-btns\">" + btns.join("") + "</div>");

        el.paginacion.innerHTML = parts.join("");
    }

    function buildBtn(label, targetPage, disabled, active) {
        const cls = "paginacion-btn" + (disabled ? " disabled" : "") + (active ? " active" : "");
        const dis = disabled ? "disabled" : "";
        return "<button type=\"button\" class=\"" + cls + "\" data-page=\"" + targetPage + "\" " + dis + ">" + label + "</button>";
    }

    // ============================================================
    // DELEGACION DE PAGINACION
    // ============================================================
    el.paginacion.addEventListener("click", function (e) {
        const btn = e.target.closest("[data-page]");
        if (!btn || btn.disabled || btn.classList.contains("disabled")) return;

        const page = parseInt(btn.dataset.page);
        if (!page || page === state.page) return;

        state.page = page;
        recargar();
    });

    // ============================================================
    // LINKS DE EXPORTACION
    // ============================================================
    function actualizarLinksExport(key) {
        const baseParams = new URLSearchParams(flatParams());
        baseParams.delete("page");

        const qs = baseParams.toString();

        el.btnPdf.href  = CFG.urls.export.replace("__KEY__", key).replace("__FORMAT__", "pdf")  + "?" + qs;
        el.btnXlsx.href = CFG.urls.export.replace("__KEY__", key).replace("__FORMAT__", "xlsx") + "?" + qs;
        el.btnCsv.href  = CFG.urls.export.replace("__KEY__", key).replace("__FORMAT__", "csv")  + "?" + qs;
    }

    // ============================================================
    // SIDEBAR - SELECCIONAR
    // ============================================================
    function marcarActivo(key) {
        document.querySelectorAll(".reporte-item").forEach(function (b) {
            b.classList.toggle("active", b.dataset.key === key);
        });
    }

    document.querySelectorAll(".reporte-item").forEach(function (btn) {
        btn.addEventListener("click", function () {
            cargarReporte(btn.dataset.key);
        });
    });

    // ============================================================
    // SIDEBAR - BUSCADOR
    // ============================================================
    el.sidebarSearch.addEventListener("input", function () {
        const q = this.value.toLowerCase().trim();

        document.querySelectorAll(".reporte-item").forEach(function (item) {
            const text = (item.dataset.title + " " + item.dataset.description);
            item.style.display = (!q || text.includes(q)) ? "" : "none";
        });

        document.querySelectorAll(".categoria-grupo").forEach(function (grupo) {
            const visibles = Array.from(grupo.querySelectorAll(".reporte-item"))
                .filter(function (i) { return i.style.display !== "none"; });
            grupo.classList.toggle("oculto", visibles.length === 0);
        });
    });

    // ============================================================
    // HELPERS INTERNOS
    // ============================================================
    function mostrarLoading() {
        el.tabla.innerHTML =
            "<div class=\"panel-loading\">" +
                "<div class=\"spinner\"></div>" +
                "<p>Cargando datos...</p>" +
            "</div>";
    }

    function dataGet(obj, path) {
        return path.split(".").reduce(function (o, k) {
            return (o && o[k] !== undefined) ? o[k] : null;
        }, obj);
    }

        // ============================================================
    // Renderizado especial para arrays (componentes, etc.)
    // ============================================================
    const _originalFormatValue = formatValue;
    formatValue = function (value, format) {
        if (Array.isArray(value)) {
            if (value.length === 0) return "-";
            return value.map(function (c) {
                const partes = [];
                if (c.tipo) partes.push(c.tipo);
                if (c.marca) partes.push(c.marca);
                if (c.capacidad) partes.push("(" + c.capacidad + ")");
                return partes.join(" ");
            }).join(" | ");
        }
        return _originalFormatValue(value, format);
    };

        // ============================================================
    // PROGRAMAR REPORTE POR CORREO
    // ============================================================
    document.getElementById("btnProgramarReporte")?.addEventListener("click", function () {
        if (!state.key) {
            alert("Selecciona un reporte primero");
            return;
        }

        // Rellenar datos
        document.getElementById("progReporteNombre").textContent = state.key;
        document.getElementById("progNombre").value = "";
        document.getElementById("progFrecuencia").value = "daily";
        document.getElementById("progHora").value = "08:00";
        document.getElementById("progFormato").value = "pdf";
        document.getElementById("progDestinatarios").value = "";
        toggleFrecuenciaCampos();

        const modal = new bootstrap.Modal(document.getElementById("modalProgramarReporte"));
        modal.show();
    });

    window.toggleFrecuenciaCampos = function () {
        const freq = document.getElementById("progFrecuencia").value;
        document.getElementById("progDiaSemanaWrap").style.display = (freq === "weekly") ? "block" : "none";
        document.getElementById("progDiaMesWrap").style.display = (freq === "monthly") ? "block" : "none";
    };

    document.getElementById("formProgramarReporte")?.addEventListener("submit", async function (e) {
        e.preventDefault();

        const btn = document.getElementById("btnGuardarProgramacion");
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = "...";

        // Parsear destinatarios
        const emailsRaw = document.getElementById("progDestinatarios").value;
        const emails = emailsRaw.split(",").map(x => x.trim()).filter(x => x.length > 0);

        const payload = {
            report_key: state.key,
            name: document.getElementById("progNombre").value,
            params: state.filters,
            frequency: document.getElementById("progFrecuencia").value,
            time: document.getElementById("progHora").value,
            format: document.getElementById("progFormato").value,
            recipients: emails,
            day_of_week: document.getElementById("progDiaSemana").value,
            day_of_month: document.getElementById("progDiaMes").value,
        };

        try {
            const res = await fetch("/admin/reportes/schedules", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json",
                },
                credentials: "same-origin",
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById("modalProgramarReporte")).hide();
                alert("✅ Reporte programado exitosamente");
            } else {
                let msg = data.message || "Error al programar";
                if (data.errors) msg += "\n" + Object.values(data.errors).flat().join("\n");
                alert("❌ " + msg);
            }
        } catch (err) {
            console.error(err);
            alert("❌ Error de conexion");
        } finally {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    });
})();