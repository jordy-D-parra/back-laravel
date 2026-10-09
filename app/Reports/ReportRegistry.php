<?php

namespace App\Reports;

use App\Reports\Contracts\ReportInterface;
use App\Reports\Contracts\IndividualReportInterface;

class ReportRegistry
{
    protected static array $reports = [
        // ============ PRÉSTAMOS ============
        'prestamos-listado'      => \App\Reports\Definitions\PrestamosListadoReport::class,
        'prestamos-proceso'      => \App\Reports\Definitions\PrestamosProcesoReport::class,
        'prestamos-vencidos'     => \App\Reports\Definitions\PrestamosVencidosReport::class,
        'prestamos-terminados'   => \App\Reports\Definitions\PrestamosTerminadosReport::class,
        'prestamos-periodo'      => \App\Reports\Definitions\PrestamosPeriodoReport::class,

        // ============ INVENTARIO ============
        "inventario-listado"    => \App\Reports\Definitions\InventarioListadoReport::class,
        "inventario-completo"   => \App\Reports\Definitions\InventarioCompletoReport::class,
        "componentes-listado"   => \App\Reports\Definitions\ComponentesListadoReport::class,
        "activos-por-entidad"   => \App\Reports\Definitions\ActivosPorEntidadReport::class,

        // ============ SOLICITUDES ============
        "solicitudes-periodo"   => \App\Reports\Definitions\SolicitudesReport::class,
        "solicitudes-listado"   => \App\Reports\Definitions\SolicitudesListadoReport::class,

        // ============ SOPORTE ============
        "soporte-periodo"       => \App\Reports\Definitions\SoporteReport::class,
        "soporte-listado"       => \App\Reports\Definitions\SoporteListadoReport::class,

        // ============ USUARIOS ============
        "usuarios"              => \App\Reports\Definitions\UsuariosReport::class,

        // ============ AUDITORÍA ============
        "auditoria"             => \App\Reports\Definitions\AuditoriaReport::class,
    ];

    /**
     * Reportes individuales (de un solo registro).
     * Aqui se iran agregando los demas (solicitud, prestamo, soporte).
     */
    protected static array $individualReports = [
        'activo-individual' => \App\Reports\Definitions\ActivoIndividualReport::class,
        'solicitud-individual' => \App\Reports\Definitions\SolicitudIndividualReport::class,
        'prestamo-individual'   => \App\Reports\Definitions\PrestamoIndividualReport::class,
    ];

    // ============================================================
    // REPORTES DE LISTADO
    // ============================================================
    public static function make(string $key): ReportInterface
    {
        abort_unless(isset(self::$reports[$key]), 404, "Reporte " . $key . " no encontrado");
        return app(self::$reports[$key]);
    }

    public static function has(string $key): bool
    {
        return isset(self::$reports[$key]);
    }

    public static function all(): array
    {
        $catalogo = [];

        foreach (self::$reports as $key => $class) {
            $report = app($class);

            $catalogo[$report->category()][$key] = [
                "key"         => $key,
                "title"       => $report->title(),
                "description" => $report->description(),
                "icon"        => $report->icon(),
            ];
        }

        return $catalogo;
    }

    public static function keys(): array
    {
        return array_keys(self::$reports);
    }

    // ============================================================
    // REPORTES INDIVIDUALES
    // ============================================================
    public static function makeIndividual(string $key): IndividualReportInterface
    {
        abort_unless(isset(self::$individualReports[$key]), 404, "Reporte individual " . $key . " no encontrado");
        return app(self::$individualReports[$key]);
    }

    public static function hasIndividual(string $key): bool
    {
        return isset(self::$individualReports[$key]);
    }
}