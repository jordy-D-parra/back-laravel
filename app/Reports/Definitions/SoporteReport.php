<?php

namespace App\Reports\Definitions;

use App\Models\FichaSoporte;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class SoporteReport extends BaseReport
{
    public function key(): string            { return "soporte-periodo"; }
    public function title(): string          { return "Soporte Tecnico por Periodo"; }
    public function description(): string    { return "Fichas de soporte tecnico con productividad por tecnico y tiempo de reparacion."; }
    public function category(): string       { return "soporte"; }
    public function icon(): string           { return "tool"; }
    public function pdfOrientation(): string { return "landscape"; }

    public function filters(): array
    {
        return [
            "rango" => [
                "type"     => "daterange",
                "label"    => "Rango de ingreso",
                "required" => true,
                "default"  => [
                    "from" => now()->startOfMonth()->toDateString(),
                    "to"   => now()->toDateString(),
                ],
            ],
            "estado" => [
                "type"    => "select",
                "label"   => "Estado",
                "options" => [
                    ""            => "Todos",
                    "en_proceso"  => "En Proceso",
                    "aceptada"    => "Aceptada",
                    "finalizado"  => "Finalizado",
                    "rechazada"   => "Rechazada",
                    "cancelada"   => "Cancelada",
                ],
            ],
        ];
    }

    public function query(array $params): Builder
    {
        $from = $params["rango"]["from"] ?? null;
        $to   = $params["rango"]["to"]   ?? null;

        return FichaSoporte::query()
            ->with([
                "activo.modelo.marca:id,nombre",
                "activo.modelo.categoria:id,nombre",
                "activo.institucion:id,nombre",
                "tecnico.trabajador:id,nombre,apellido",
            ])
            ->withCount("detalles")
            ->when($from, fn($q, $v) => $q->whereDate("fecha_ingreso", ">=", $v))
            ->when($to,   fn($q, $v) => $q->whereDate("fecha_ingreso", "<=", $v))
            ->when($params["estado"] ?? null, fn($q, $v) => $q->where("estado", $v))
            ->orderByDesc("fecha_ingreso");
    }

    public function columns(): array
    {
        return [
            ["key" => "id",                         "label" => "N", "width" => "5%",  "align" => "center"],
            ["key" => "fecha_ingreso",              "label" => "F. Ingreso", "width" => "11%", "format" => "date"],
            ["key" => "activo.serial",              "label" => "Activo", "width" => "13%"],
            ["key" => "activo.institucion.nombre",  "label" => "Institucion", "width" => "16%"],
            ["key" => "tecnico_nombre",             "label" => "Tecnico", "width" => "14%"],
            ["key" => "usuario_reporta_nombre",     "label" => "Reporta", "width" => "14%"],
            ["key" => "fecha_requerida_entrega",    "label" => "F. Requerida", "width" => "10%", "format" => "date"],
            ["key" => "fecha_salida",               "label" => "F. Salida", "width" => "10%", "format" => "date"],
            ["key" => "estado",                     "label" => "Estado", "width" => "10%", "align" => "center", "format" => "badge"],
            ["key" => "detalles_count",             "label" => "Comps.", "width" => "7%", "align" => "center"],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            "Total fichas"     => (clone $q)->count(),
            "En proceso"       => (clone $q)->where("estado", "en_proceso")->count(),
            "Finalizadas"      => (clone $q)->where("estado", "finalizado")->count(),
            "Con tecnico"      => (clone $q)->whereNotNull("tecnico_id")->count(),
            "Sin tecnico"      => (clone $q)->whereNull("tecnico_id")->count(),
        ];
    }

    public function signatures(): array
    {
        return [
            ["role" => "Elabora", "nombre" => "Departamento de Informatica"],
            ["role" => "Revisa",  "nombre" => "Supervisor"],
        ];
    }
}
