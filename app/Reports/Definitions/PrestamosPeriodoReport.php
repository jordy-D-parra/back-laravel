<?php

namespace App\Reports\Definitions;

use App\Models\Prestamo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class PrestamosPeriodoReport extends BaseReport
{
    public function key(): string            { return "prestamos-periodo"; }
    public function title(): string          { return "Prestamos por Periodo"; }
    public function description(): string    { return "Prestamos otorgados en un rango de fechas con estadisticas y destino."; }
    public function category(): string       { return "prestamos"; }
    public function icon(): string           { return "package"; }
    public function pdfOrientation(): string { return "portrait"; }

    public function filters(): array
    {
        return [
            "rango" => [
                "type"     => "daterange",
                "label"    => "Rango de fechas",
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
                    ""          => "Todos",
                    "pendiente" => "Pendiente",
                    "aprobado"  => "Aprobado",
                    "entregado" => "Entregado",
                    "devuelto"  => "Devuelto",
                    "extendido" => "Extendido",
                    "cancelado" => "Cancelado",
                    "rechazado" => "Rechazado",
                ],
            ],
            "departamento_id" => [
                "type"   => "select",
                "label"  => "Departamento",
                "source" => "departamentos",
            ],
            "institucion_id" => [
                "type"   => "select",
                "label"  => "Institucion",
                "source" => "instituciones",
            ],
        ];
    }

    public function query(array $params): Builder
    {
        $from = $params["rango"]["from"] ?? null;
        $to   = $params["rango"]["to"]   ?? null;

        return Prestamo::query()
            ->with([
                "departamento:id,nombre",
                "institucion:id,nombre",
                "responsableReceptor:id,nombre",
            ])
            ->withCount("detalles")
            ->when($from, fn($q, $v) => $q->whereDate("fecha_prestamo", ">=", $v))
            ->when($to,   fn($q, $v) => $q->whereDate("fecha_prestamo", "<=", $v))
            ->when($params["estado"] ?? null,          fn($q, $v) => $q->where("estado", $v))
            ->when($params["departamento_id"] ?? null, fn($q, $v) => $q->where("departamento_id", $v))
            ->when($params["institucion_id"] ?? null,  fn($q, $v) => $q->where("institucion_id", $v))
            ->orderByDesc("fecha_prestamo");
    }

    public function columns(): array
    {
        return [
            ["key" => "codigo",                     "label" => "Codigo",       "width" => "12%"],
            ["key" => "fecha_prestamo",             "label" => "F. Prestamo",  "width" => "10%", "format" => "date"],
            ["key" => "destino_nombre",             "label" => "Destino",      "width" => "22%"],
            ["key" => "responsableReceptor.nombre", "label" => "Responsable",  "width" => "18%"],
            ["key" => "fecha_devolucion_esperada",  "label" => "F. Devolucion", "width" => "10%", "format" => "date"],
            ["key" => "detalles_count",             "label" => "Items",        "width" => "8%",  "align" => "center"],
            ["key" => "estado",                     "label" => "Estado",       "width" => "10%", "format" => "badge"],
            ["key" => "dias_restantes",             "label" => "Dias rest.",   "width" => "10%", "align" => "center"],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            "Total prestamos" => (clone $q)->count(),
            "Activos"         => (clone $q)->whereIn("estado", ["entregado", "extendido"])->count(),
            "Devueltos"       => (clone $q)->where("estado", "devuelto")->count(),
            "Vencidos"        => (clone $q)
                                    ->whereIn("estado", ["entregado", "extendido"])
                                    ->whereNull("fecha_devolucion_real")
                                    ->whereDate("fecha_devolucion_esperada", "<", today())
                                    ->count(),
        ];
    }

    public function signatures(): array
    {
        return [
            ["role" => "Entrega", "nombre" => "Responsable de Informatica"],
            ["role" => "Revisa",  "nombre" => "Supervisor"],
        ];
    }
}
