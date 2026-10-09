<?php

namespace App\Reports\Definitions;

use App\Models\Solicitud;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class SolicitudesReport extends BaseReport
{
    public function key(): string            { return "solicitudes-periodo"; }
    public function title(): string          { return "Solicitudes por Periodo"; }
    public function description(): string    { return "Solicitudes de prestamo registradas en un rango de fechas, con estado, prioridad y tiempo de respuesta."; }
    public function category(): string       { return "solicitudes"; }
    public function icon(): string           { return "file-text"; }
    public function pdfOrientation(): string { return "landscape"; }

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
                    ""           => "Todos",
                    "pendiente"  => "Pendiente",
                    "aprobada"   => "Aprobada",
                    "rechazada"  => "Rechazada",
                    "cancelada"  => "Cancelada",
                    "entregada"  => "Entregada",
                ],
            ],
            "prioridad" => [
                "type"    => "select",
                "label"   => "Prioridad",
                "options" => [
                    ""        => "Todas",
                    "baja"    => "Baja",
                    "normal"  => "Normal",
                    "alta"    => "Alta",
                    "urgente" => "Urgente",
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

        return Solicitud::query()
            ->with([
                "usuario.trabajador:id,nombre,apellido,cedula",
                "departamento:id,nombre",
                "institucion:id,nombre",
                "responsable:id,nombre",
            ])
            ->withCount("detalles")
            ->when($from, fn($q, $v) => $q->whereDate("fecha_solicitud", ">=", $v))
            ->when($to,   fn($q, $v) => $q->whereDate("fecha_solicitud", "<=", $v))
            ->when($params["estado"] ?? null,          fn($q, $v) => $q->where("estado_solicitud", $v))
            ->when($params["prioridad"] ?? null,       fn($q, $v) => $q->where("prioridad", $v))
            ->when($params["departamento_id"] ?? null, fn($q, $v) => $q->where("departamento_id", $v))
            ->when($params["institucion_id"] ?? null,  fn($q, $v) => $q->where("institucion_id", $v))
            ->orderByDesc("fecha_solicitud");
    }

    public function columns(): array
    {
        return [
            ["key" => "id",                     "label" => "N", "width" => "5%",  "align" => "center"],
            ["key" => "fecha_solicitud",        "label" => "F. Solicitud", "width" => "10%", "format" => "date"],
            ["key" => "nombre_entidad",         "label" => "Entidad", "width" => "20%"],
            ["key" => "responsable.nombre",     "label" => "Responsable", "width" => "16%"],
            ["key" => "prioridad",              "label" => "Prioridad", "width" => "10%", "align" => "center", "format" => "badge"],
            ["key" => "estado_solicitud",       "label" => "Estado", "width" => "10%", "align" => "center", "format" => "badge"],
            ["key" => "detalles_count",         "label" => "Items", "width" => "7%",  "align" => "center"],
            ["key" => "fecha_requerida",        "label" => "F. Requerida", "width" => "10%", "format" => "date"],
            ["key" => "fecha_aprobacion",       "label" => "F. Aprobacion", "width" => "12%", "format" => "date"],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            "Total"        => (clone $q)->count(),
            "Pendientes"   => (clone $q)->where("estado_solicitud", "pendiente")->count(),
            "Aprobadas"    => (clone $q)->where("estado_solicitud", "aprobada")->count(),
            "Rechazadas"   => (clone $q)->where("estado_solicitud", "rechazada")->count(),
            "Urgentes"     => (clone $q)->where("prioridad", "urgente")->count(),
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
