<?php

namespace App\Reports;

use App\Reports\Contracts\ReportInterface;

class ReportRegistry
{
    protected static array $reports = [
        // ============ PRÉSTAMOS ============
        "prestamos-periodo"     => \App\Reports\Definitions\PrestamosPeriodoReport::class,
        "prestamos-vencidos"    => \App\Reports\Definitions\PrestamosVencidosReport::class,
        "prestamos-listado"     => \App\Reports\Definitions\PrestamosListadoReport::class,

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
}