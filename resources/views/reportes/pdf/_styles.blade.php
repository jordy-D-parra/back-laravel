<style>
    @page { size: {{ $orientacion ?? 'letter portrait' }}; margin: 12mm 10mm 15mm 10mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', 'Times New Roman', serif; font-size: 9pt; color: #1a1a1a; line-height: 1.35; }

    /* HEADER */
    .pdf-header { width: 100%; border-collapse: collapse; border-bottom: 2.5px solid #1e3c72; margin-bottom: 10px; }
    .pdf-header td { vertical-align: middle; padding-bottom: 5px; }
    .pdf-header .td-logo { width: 90px; text-align: center; }
    .pdf-header .td-logo img { height: 48px; width: auto; }
    .pdf-header .td-logo .logo-label { display: block; font-size: 5.5pt; font-weight: bold; color: #1e3c72; text-transform: uppercase; margin-top: 1px; line-height: 1.05; }
    .pdf-header .td-center { text-align: center; padding: 0 8px; }
    .pdf-header .titulo-pais { font-size: 8.5pt; font-weight: bold; color: #1e3c72; text-transform: uppercase; letter-spacing: 0.6px; }
    .pdf-header .titulo-gobierno { font-size: 10pt; font-weight: bold; color: #1e3c72; text-transform: uppercase; letter-spacing: 0.5px; }
    .pdf-header .titulo-depto { font-size: 8pt; font-weight: bold; color: #1e3c72; text-transform: uppercase; }
    .pdf-header .titulo-reporte { font-size: 11.5pt; font-weight: bold; color: #1e3c72; text-transform: uppercase; letter-spacing: 1.5px; padding: 3px 18px; border-top: 1.5px solid #1e3c72; border-bottom: 1.5px solid #1e3c72; display: inline-block; margin-top: 6px; }

    /* META */
    .pdf-meta { width: 100%; border-collapse: collapse; font-size: 8pt; color: #475569; margin-bottom: 8px; }
    .pdf-meta td { padding: 2px 0; }
    .pdf-meta .td-right { text-align: right; }

    /* FILTROS */
    .pdf-filtros { background: #f8fafc; border-left: 3px solid #1e3c72; border-radius: 4px; padding: 5px 10px; font-size: 8pt; color: #475569; margin-bottom: 8px; }
    .pdf-filtros strong { color: #1e3c72; }

    /* STATS */
    .pdf-stats { width: 100%; border-collapse: separate; border-spacing: 4px 0; margin-bottom: 10px; }
    .pdf-stats td { background: #f8fafc; border-radius: 6px; padding: 7px 4px; text-align: center; border-left: 3px solid #1e3c72; }
    .pdf-stats .num { font-size: 13pt; font-weight: bold; color: #1e3c72; display: block; line-height: 1.1; }
    .pdf-stats .lbl { font-size: 6.5pt; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.4px; }
    .pdf-stats .stat-green { border-left-color: #10b981; }
    .pdf-stats .stat-green .num { color: #065f46; }
    .pdf-stats .stat-orange { border-left-color: #f59e0b; }
    .pdf-stats .stat-orange .num { color: #92400e; }
    .pdf-stats .stat-red { border-left-color: #ef4444; }
    .pdf-stats .stat-red .num { color: #991b1b; }
    .pdf-stats .stat-purple { border-left-color: #8b5cf6; }
    .pdf-stats .stat-purple .num { color: #5b21b6; }
    .pdf-stats .stat-blue { border-left-color: #3b82f6; }
    .pdf-stats .stat-blue .num { color: #1e40af; }

    /* SECCIONES */
    .pdf-section-title { font-size: 9pt; font-weight: bold; color: #1e3c72; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin: 10px 0 6px 0; }

    /* TABLAS */
    .pdf-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 8px; }
    .pdf-table thead th { background: #1e3c72; color: white; padding: 4px 6px; text-align: left; font-size: 6.8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #1e3c72; }
    .pdf-table tbody td { padding: 3.5px 6px; border: 1px solid #e9ecef; vertical-align: top; }
    .pdf-table tbody tr:nth-child(even) { background: #f8fafc; }
    .pdf-table .text-center { text-align: center; }
    .pdf-table .text-end { text-align: right; }
    .pdf-table .fw-bold { font-weight: bold; }
    .pdf-table .text-muted { color: #64748b; }

    /* BADGES */
    .pdf-badge { display: inline-block; padding: 1.5px 6px; border-radius: 8px; font-size: 6.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2px; white-space: nowrap; }
    .pdf-badge-green { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .pdf-badge-orange { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
    .pdf-badge-red { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
    .pdf-badge-blue { background: #dbeafe; color: #1e40af; border: 1px solid #3b82f6; }
    .pdf-badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .pdf-badge-purple { background: #ede9fe; color: #5b21b6; border: 1px solid #8b5cf6; }

    /* VACÍO */
    .pdf-empty { text-align: center; padding: 20px; color: #94a3b8; background: #f8fafc; border-radius: 6px; font-size: 9pt; }

    /* FIRMAS */
    .pdf-firmas { width: 100%; border-collapse: collapse; margin-top: 40px; }
    .pdf-firmas td { width: 50%; text-align: center; vertical-align: bottom; padding: 0 30px; }
    .pdf-firmas .linea { border-top: 1.3px solid #000; width: 100%; height: 1px; margin-bottom: 3px; }
    .pdf-firmas .nombre { font-weight: bold; font-size: 8.5pt; color: #1e3c72; text-transform: uppercase; }
    .pdf-firmas .cargo { font-size: 7pt; color: #555; text-transform: uppercase; margin-top: 1px; }
    .pdf-firmas .rol { font-size: 7pt; font-weight: bold; color: #1e3c72; letter-spacing: 1px; text-transform: uppercase; margin-top: 2px; }

    /* FOOTER */
    .pdf-footer { position: fixed; bottom: -8mm; left: 0; right: 0; text-align: center; font-size: 6.5pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 3px; }

    /* UTILIDADES */
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .text-muted { color: #64748b; }
    .fw-bold { font-weight: bold; }
    .mt-2 { margin-top: 8px; }
    .mt-3 { margin-top: 12px; }
    .mb-2 { margin-bottom: 8px; }
    .mb-3 { margin-bottom: 12px; }
</style>