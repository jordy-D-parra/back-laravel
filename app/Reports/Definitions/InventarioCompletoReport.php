<?php

namespace App\Reports\Definitions;

use App\Models\Activo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class InventarioCompletoReport extends BaseReport
{
    public function key(): string            { return "inventario-completo"; }
    public function title(): string          { return "Inventario Completo"; }
    public function description(): string    { return "Activos con sus componentes anidados, agrupados por categoria, con estado y responsable."; }
    public function category(): string       { return "inventario"; }
    public function icon(): string           { return "monitor"; }
    public function pdfOrientation(): string { return "landscape"; }

    public function filters(): array
    {
        return [
            "categoria_id" => [
                "type"    => "select",
                "label"   => "Categoria",
                "source"  => null,
                "options" => $this->categoriasOpciones(),
            ],
            "estatus" => [
                "type"    => "select",
                "label"   => "Estatus",
                "options" => [
                    ""              => "Todos",
                    "Disponible"    => "Disponible",
                    "Prestado"      => "Prestado",
                    "En reparacion" => "En Reparacion",
                    "Desechado"     => "Desechado",
                    "En bodega"     => "En Bodega",
                ],
            ],
            "institucion_id" => [
                "type"   => "select",
                "label"  => "Institucion",
                "source" => "instituciones",
            ],
        ];
    }

    protected function categoriasOpciones(): array
    {
        $categorias = \App\Models\Categoria::where("activo", true)
            ->orderBy("nombre")
            ->get(["id", "nombre"]);

        $opciones = ["" => "Todas las categorias"];
        foreach ($categorias as $c) {
            $opciones[$c->id] = $c->nombre;
        }
        return $opciones;
    }

    public function query(array $params): Builder
    {
        return Activo::query()
            ->with([
                "modelo.marca:id,nombre",
                "modelo.categoria:id,nombre",
                "estatus:id,descripcion",
                "institucion:id,nombre",
                "departamento:id,nombre",
                "responsable:id,nombre",
                "componentes:id,activo_id,tipo,marca,modelo,serial,capacidad,estado",
            ])
            ->when($params["categoria_id"] ?? null, fn($q, $v) =>
                $q->whereHas("modelo", fn($sq) => $sq->where("categoria_id", $v))
            )
            ->when($params["estatus"] ?? null, fn($q, $v) =>
                $q->whereHas("estatus", fn($sq) => $sq->where("descripcion", $v))
            )
            ->when($params["institucion_id"] ?? null, fn($q, $v) =>
                $q->where("institucion_id", $v)
            )
            ->orderBy("institucion_id")
            ->orderBy("serial");
    }

    public function columns(): array
    {
        return [
            ["key" => "serial",                     "label" => "Serial Activo", "width" => "12%"],
            ["key" => "modelo.marca.nombre",        "label" => "Marca", "width" => "9%"],
            ["key" => "modelo.nombre",              "label" => "Modelo", "width" => "13%"],
            ["key" => "modelo.categoria.nombre",    "label" => "Categoria", "width" => "10%"],
            ["key" => "estatus.descripcion",        "label" => "Estatus", "width" => "10%", "format" => "badge"],
            ["key" => "institucion.nombre",         "label" => "Institucion", "width" => "15%"],
            ["key" => "responsable.nombre",         "label" => "Responsable", "width" => "14%"],
            ["key" => "componentes", "label" => "Componentes", "width" => "17%", "align" => "left"],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            "Total activos"       => (clone $q)->count(),
            "Total componentes"   => \App\Models\Componente::whereHas("activo", function ($sq) use ($params) {
                // Re-aplica filtros al contar componentes
                $sq->whereHas("estatus", function ($s2) use ($params) {
                    if (!empty($params["estatus"])) {
                        $s2->where("descripcion", $params["estatus"]);
                    }
                });
                if (!empty($params["institucion_id"])) {
                    $sq->where("institucion_id", $params["institucion_id"]);
                }
                if (!empty($params["categoria_id"])) {
                    $sq->whereHas("modelo", fn($m) => $m->where("categoria_id", $params["categoria_id"]));
                }
            })->count(),
            "Disponibles"         => (clone $q)->whereHas("estatus", fn($sq) => $sq->where("descripcion", "Disponible"))->count(),
            "Prestados"           => (clone $q)->whereHas("estatus", fn($sq) => $sq->where("descripcion", "Prestado"))->count(),
            "En reparacion"       => (clone $q)->whereHas("estatus", fn($sq) => $sq->where("descripcion", "En reparacion"))->count(),
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
