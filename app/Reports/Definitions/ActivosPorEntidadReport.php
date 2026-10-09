<?php

namespace App\Reports\Definitions;

use App\Models\Activo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class ActivosPorEntidadReport extends BaseReport
{
    public function key(): string            { return "activos-por-entidad"; }
    public function title(): string          { return "Activos por Entidad"; }
    public function description(): string    { return "Listado de activos agrupados por institucion y departamento."; }
    public function category(): string       { return "inventario"; }
    public function icon(): string           { return "building"; }
    public function pdfOrientation(): string { return "landscape"; }

    public function filters(): array
    {
        return [
            "institucion_id" => [
                "type"   => "select",
                "label"  => "Institucion",
                "source" => "instituciones",
            ],
            "estatus" => [
                "type"    => "select",
                "label"   => "Estatus",
                "options" => [
                    ""                => "Todos",
                    "Disponible"      => "Disponible",
                    "Prestado"        => "Prestado",
                    "En reparacion"   => "En Reparacion",
                    "Desechado"       => "Desechado",
                    "En bodega"       => "En Bodega",
                ],
            ],
        ];
    }

    public function query(array $params): Builder
    {
        return Activo::query()
            ->with([
                "modelo.marca:id,nombre",
                "modelo.categoria:id,nombre",
                "estatus:id,descripcion,color_badge",
                "institucion:id,nombre",
                "departamento:id,nombre",
                "responsable:id,nombre",
            ])
            ->when($params["institucion_id"] ?? null, fn($q, $v) => $q->where("institucion_id", $v))
            ->when($params["estatus"] ?? null, fn($q, $v) =>
                $q->whereHas("estatus", fn($sq) => $sq->where("descripcion", $v))
            )
            ->orderBy("institucion_id")
            ->orderBy("departamento_id")
            ->orderBy("serial");
    }

    public function columns(): array
    {
        return [
            ["key" => "institucion.nombre",     "label" => "Institucion", "width" => "18%"],
            ["key" => "departamento.nombre",    "label" => "Departamento", "width" => "16%"],
            ["key" => "serial",                 "label" => "Serial", "width" => "12%"],
            ["key" => "modelo.marca.nombre",    "label" => "Marca", "width" => "10%"],
            ["key" => "modelo.nombre",          "label" => "Modelo", "width" => "14%"],
            ["key" => "modelo.categoria.nombre", "label" => "Categoria", "width" => "10%"],
            ["key" => "estatus.descripcion",    "label" => "Estatus", "width" => "10%", "format" => "badge"],
            ["key" => "responsable.nombre",     "label" => "Responsable", "width" => "10%"],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            "Total activos"      => (clone $q)->count(),
            "Instituciones"      => (clone $q)->distinct("institucion_id")->count("institucion_id"),
            "Departamentos"      => (clone $q)->whereNotNull("departamento_id")->distinct("departamento_id")->count("departamento_id"),
            "Disponibles"        => (clone $q)->whereHas("estatus", fn($sq) => $sq->where("descripcion", "Disponible"))->count(),
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
