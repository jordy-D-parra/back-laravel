<?php

namespace App\Reports\Definitions;

use App\Models\Componente;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class ComponentesListadoReport extends BaseReport
{
    public function key(): string
    {
        return 'componentes-listado';
    }

    public function title(): string
    {
        return 'Listado de Componentes';
    }

    public function description(): string
    {
        return 'Listado filtrado de componentes con los mismos filtros de la pantalla de inventario';
    }

    public function category(): string
    {
        return 'inventario';
    }

    public function icon(): string
    {
        return 'cpu';
    }

    public function pdfOrientation(): string
    {
        return 'landscape';
    }

    public function requiredPermission(): ?string
    {
        return 'ver-componentes';
    }

    public function filters(): array
    {
        return [
            'buscar' => [
                'type'  => 'text',
                'label' => 'Buscar',
            ],
            'tipo' => [
                'type'    => 'select',
                'label'   => 'Tipo',
                'options' => $this->tiposOpciones(),
            ],
            'estado' => [
                'type'    => 'select',
                'label'   => 'Estado',
                'options' => [
                    ''              => 'Todos',
                    'en_bodega'     => 'En Bodega',
                    'instalado'     => 'Instalado',
                    'prestado'      => 'Prestado',
                    'en_reparacion' => 'En Reparación',
                    'desechado'     => 'Desechado',
                ],
            ],
        ];
    }

    protected function tiposOpciones(): array
    {
        $tipos = Componente::query()
            ->select('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo')
            ->filter()
            ->values();

        $opciones = ['' => 'Todos los tipos'];
        foreach ($tipos as $tipo) {
            $opciones[$tipo] = $tipo;
        }
        return $opciones;
    }

    public function query(array $params): Builder
    {
        return Componente::query()
            ->with([
                'activo:id,serial',
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
            ['key' => 'tipo',                'label' => 'Tipo',        'width' => '12%'],
            ['key' => 'marca',               'label' => 'Marca',       'width' => '12%'],
            ['key' => 'modelo',              'label' => 'Modelo',      'width' => '14%'],
            ['key' => 'serial',              'label' => 'Serial',      'width' => '12%'],
            ['key' => 'capacidad',           'label' => 'Capacidad',   'width' => '10%'],
            ['key' => 'estado',              'label' => 'Estado',      'width' => '12%', 'format' => 'badge'],
            ['key' => 'activo.serial',       'label' => 'Activo',      'width' => '12%'],
            ['key' => 'institucion.nombre',  'label' => 'Institución', 'width' => '16%'],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            'Total'         => (clone $q)->count(),
            'En bodega'     => (clone $q)->where('estado', 'en_bodega')->count(),
            'Instalados'    => (clone $q)->where('estado', 'instalado')->count(),
            'Prestados'     => (clone $q)->where('estado', 'prestado')->count(),
            'En reparación' => (clone $q)->where('estado', 'en_reparacion')->count(),
        ];
    }

    public function signatures(): array
    {
        return [];
    }
}