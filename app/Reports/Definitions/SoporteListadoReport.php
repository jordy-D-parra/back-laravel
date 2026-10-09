<?php

namespace App\Reports\Definitions;

use App\Models\FichaSoporte;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class SoporteListadoReport extends BaseReport
{
    public function key(): string            { return "soporte-listado"; }
    public function title(): string          { return "Listado de Fichas de Soporte"; }
    public function description(): string    { return "Fichas de soporte técnico con los mismos filtros de la pantalla"; }
    public function category(): string       { return "soporte"; }
    public function icon(): string           { return "tool"; }
    public function pdfOrientation(): string { return "landscape"; }

    public function requiredPermission(): ?string
    {
        return 'ver-fichas-soporte';
    }

    public function filters(): array
{
    return [
        'buscar' => [
            'type'  => 'text',
            'label' => 'Buscar',
        ],
        'estado' => [
            'type'    => 'select',
            'label'   => 'Estado',
            'options' => [
                ''            => 'Todos',
                'en_proceso'  => 'En Proceso',
                'aceptada'    => 'Aceptada',
                'finalizado'  => 'Finalizado',
                'rechazada'   => 'Rechazada',
                'cancelada'   => 'Cancelada',
            ],
        ],
        // ✅ CAMBIO: usar "text" en lugar de "date" para que sean opcionales
        'fecha_desde' => [
            'type'  => 'text',   // ← ANTES ERA 'date'
            'label' => 'Ingreso desde',
        ],
        'fecha_hasta' => [
            'type'  => 'text',   // ← ANTES ERA 'date'
            'label' => 'Ingreso hasta',
        ],
    ];
}

    public function query(array $params): Builder
    {
        return FichaSoporte::query()
            ->with([
                'activo.modelo.marca:id,nombre',
                'activo.modelo.categoria:id,nombre',
                'activo.institucion:id,nombre',
                'tecnico.trabajador:id,nombre,apellido',
            ])
            ->withCount('detalles')
            ->conFiltros($params)
            ->orderByDesc('fecha_ingreso');
    }

    public function columns(): array
    {
        return [
            ["key" => "id",                        "label" => "N",            "width" => "5%",  "align" => "center"],
            ["key" => "fecha_ingreso",             "label" => "F. Ingreso",   "width" => "12%", "format" => "date"],
            ["key" => "activo.serial",             "label" => "Activo",       "width" => "13%"],
            ["key" => "activo.modelo.marca.nombre", "label" => "Marca",       "width" => "10%"],
            ["key" => "activo.institucion.nombre", "label" => "Institución",  "width" => "15%"],
            ["key" => "tecnico_nombre",            "label" => "Técnico",      "width" => "14%"],
            ["key" => "usuario_reporta_nombre",    "label" => "Reporta",      "width" => "13%"],
            ["key" => "estado",                    "label" => "Estado",       "width" => "10%", "align" => "center", "format" => "badge"],
            ["key" => "detalles_count",            "label" => "Comps.",       "width" => "8%",  "align" => "center"],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            "Total"        => (clone $q)->count(),
            "En proceso"   => (clone $q)->where("estado", "en_proceso")->count(),
            "Finalizadas"  => (clone $q)->where("estado", "finalizado")->count(),
            "Con técnico"  => (clone $q)->whereNotNull("tecnico_id")->count(),
            "Sin técnico"  => (clone $q)->whereNull("tecnico_id")->count(),
        ];
    }

    public function signatures(): array
    {
        return [
            ["role" => "Elabora", "nombre" => "Departamento de Informática"],
            ["role" => "Revisa",  "nombre" => "Supervisor"],
        ];
    }
}