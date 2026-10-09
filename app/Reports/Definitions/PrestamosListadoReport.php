<?php

namespace App\Reports\Definitions;

use App\Models\Prestamo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class PrestamosListadoReport extends BaseReport
{
    public function key(): string
    {
        return 'prestamos-listado';
    }

    public function title(): string
    {
        return 'Listado de Préstamos';
    }

    public function description(): string
    {
        return 'Listado filtrado de préstamos con los mismos filtros de la pantalla de gestión';
    }

    public function category(): string
    {
        return 'prestamos';
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
        return 'ver-prestamos';
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
                    ''          => 'Todos',
                    'pendiente' => 'Pendiente',
                    'aprobado'  => 'Aprobado',
                    'entregado' => 'Entregado',
                    'devuelto'  => 'Devuelto',
                    'extendido' => 'Extendido',
                    'cancelado' => 'Cancelado',
                    'rechazado' => 'Rechazado',
                    'vencido'   => 'Vencido',
                ],
            ],
            'tipo' => [
                'type'    => 'select',
                'label'   => 'Tipo',
                'options' => [
                    ''           => 'Todos',
                    'equipo'     => 'Equipo',
                    'componente' => 'Componente',
                    'mixto'      => 'Mixto',
                ],
            ],
            'departamento_id' => [
                'type'   => 'select',
                'label'  => 'Departamento',
                'source' => 'departamentos',
            ],
            'institucion_id' => [
                'type'   => 'select',
                'label'  => 'Institución',
                'source' => 'instituciones',
            ],
        ];
    }

    public function query(array $params): Builder
    {
        return Prestamo::query()
            ->with([
                'departamento:id,nombre',
                'institucion:id,nombre',
                'responsableReceptor:id,nombre',
            ])
            ->conFiltros($params)
            ->orderByDesc('fecha_prestamo');
    }

    public function columns(): array
    {
        return [
            ['key' => 'codigo',                     'label' => 'Código',      'width' => '10%'],
            ['key' => 'fecha_prestamo',             'label' => 'F. Préstamo', 'width' => '9%',  'format' => 'date'],
            ['key' => 'destino_nombre',             'label' => 'Destino',     'width' => '20%'],
            ['key' => 'responsableReceptor.nombre', 'label' => 'Responsable', 'width' => '16%'],
            ['key' => 'fecha_devolucion_esperada',  'label' => 'F. Devolución','width' => '9%',  'format' => 'date'],
            ['key' => 'tipo_prestamo',              'label' => 'Tipo',        'width' => '8%'],
            ['key' => 'estado',                     'label' => 'Estado',      'width' => '10%', 'format' => 'badge'],
            ['key' => 'dias_restantes',             'label' => 'Días Rest.',  'width' => '8%',  'align' => 'center'],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            'Total'     => (clone $q)->count(),
            'Activos'   => (clone $q)->whereIn('estado', ['entregado', 'extendido'])->count(),
            'Vencidos'  => (clone $q)
                                ->whereIn('estado', ['entregado', 'extendido'])
                                ->where('fecha_devolucion_esperada', '<', now()->format('Y-m-d'))
                                ->count(),
            'Devueltos' => (clone $q)->where('estado', 'devuelto')->count(),
        ];
    }

    public function signatures(): array
    {
        return [];
    }
}