<?php

namespace App\Reports\Definitions;

use App\Models\Activo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class InventarioListadoReport extends BaseReport
{
    public function key(): string
    {
        return 'inventario-listado';
    }

    public function title(): string
    {
        return 'Listado de Inventario';
    }

    public function description(): string
    {
        return 'Listado filtrado de activos con los mismos filtros de la pantalla de inventario';
    }

    public function category(): string
    {
        return 'inventario';
    }

    public function icon(): string
    {
        return 'list';
    }

    public function pdfOrientation(): string
    {
        return 'landscape';
    }

    public function requiredPermission(): ?string
    {
        return 'ver-activos';
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
                    ''              => 'Todos',
                    'Disponible'    => 'Disponible',
                    'Prestado'      => 'Prestado',
                    'En reparación' => 'En Reparación',
                    'En bodega'     => 'En Bodega',
                    'Desechado'     => 'Desechado',
                    'Reservado'     => 'Reservado',
                ],
            ],
            'institucion_id' => [
                'type'   => 'select',
                'label'  => 'Institución',
                'source' => 'instituciones',
            ],
            'departamento_id' => [
                'type'   => 'select',
                'label'  => 'Departamento',
                'source' => 'departamentos',
            ],
        ];
    }

    public function query(array $params): Builder
    {
        return Activo::query()
            ->with([
                'modelo.marca:id,nombre',
                'modelo.categoria:id,nombre',
                'estatus:id,descripcion,color_badge',
                'institucion:id,nombre',
                'departamento:id,nombre',
                'responsable:id,nombre',
            ])
            ->conFiltros($params)
            ->orderByDesc('created_at');
    }

    public function columns(): array
    {
        return [
            ['key' => 'serial',                  'label' => 'Serial',       'width' => '12%'],
            ['key' => 'modelo.nombre',           'label' => 'Modelo',       'width' => '20%'],
            ['key' => 'modelo.marca.nombre',     'label' => 'Marca',        'width' => '14%'],
            ['key' => 'modelo.categoria.nombre', 'label' => 'Categoría',    'width' => '14%'],
            ['key' => 'estatus.descripcion',     'label' => 'Estado',       'width' => '12%', 'format' => 'badge'],
            ['key' => 'ubicacion',               'label' => 'Ubicación',    'width' => '18%'],
            ['key' => 'responsable.nombre',      'label' => 'Responsable',  'width' => '10%'],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            'Total'       => (clone $q)->count(),
            'Disponibles' => (clone $q)
                ->whereHas('estatus', fn($sq) => $sq->where('descripcion', 'Disponible'))
                ->count(),
            'Prestados'   => (clone $q)
                ->whereHas('estatus', fn($sq) => $sq->where('descripcion', 'Prestado'))
                ->count(),
            'En bodega'   => (clone $q)
                ->whereHas('estatus', fn($sq) => $sq->where('descripcion', 'En bodega'))
                ->count(),
        ];
    }

    public function signatures(): array
    {
        return [];
    }
}