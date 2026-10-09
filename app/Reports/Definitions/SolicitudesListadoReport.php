<?php

namespace App\Reports\Definitions;

use App\Models\Solicitud;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class SolicitudesListadoReport extends BaseReport
{
    public function key(): string
    {
        return 'solicitudes-listado';
    }

    public function title(): string
    {
        return 'Listado de Solicitudes';
    }

    public function description(): string
    {
        return 'Listado filtrado de solicitudes con los mismos filtros de la pantalla de gestión';
    }

    public function category(): string
    {
        return 'solicitudes';
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
        return 'ver-solicitudes';
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
                    'aprobada'  => 'Aprobada',
                    'rechazada' => 'Rechazada',
                    'cancelada' => 'Cancelada',
                    'entregada' => 'Entregada',
                ],
            ],
            'prioridad' => [
                'type'    => 'select',
                'label'   => 'Prioridad',
                'options' => [
                    ''        => 'Todas',
                    'baja'    => 'Baja',
                    'normal'  => 'Normal',
                    'alta'    => 'Alta',
                    'urgente' => 'Urgente',
                ],
            ],
            'tipo_solicitante' => [
                'type'    => 'select',
                'label'   => 'Tipo Solicitante',
                'options' => [
                    ''        => 'Todos',
                    'interno' => 'Interno',
                    'externo' => 'Externo',
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
        return Solicitud::query()
            ->with([
                'departamento:id,nombre',
                'institucion:id,nombre',
                'responsable:id,nombre',
            ])
            ->withCount('detalles')
            ->conFiltros($params)
            ->orderByDesc('fecha_solicitud');
    }

    public function columns(): array
    {
        return [
            ['key' => 'id',                     'label' => 'N°',            'width' => '5%',  'align' => 'center'],
            ['key' => 'fecha_solicitud',        'label' => 'F. Solicitud',  'width' => '9%',  'format' => 'date'],
            ['key' => 'nombre_entidad',         'label' => 'Entidad',       'width' => '18%'],
            ['key' => 'responsable.nombre',     'label' => 'Responsable',   'width' => '14%'],
            ['key' => 'prioridad',              'label' => 'Prioridad',     'width' => '9%',  'align' => 'center', 'format' => 'badge'],
            ['key' => 'estado_solicitud',       'label' => 'Estado',        'width' => '9%',  'align' => 'center', 'format' => 'badge'],
            ['key' => 'detalles_count',         'label' => 'Items',         'width' => '6%',  'align' => 'center'],
            ['key' => 'fecha_requerida',        'label' => 'F. Requerida',  'width' => '9%',  'format' => 'date'],
            ['key' => 'fecha_fin_estimada',     'label' => 'F. Fin Est.',   'width' => '9%',  'format' => 'date'],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            'Total'      => (clone $q)->count(),
            'Pendientes' => (clone $q)->where('estado_solicitud', 'pendiente')->count(),
            'Aprobadas'  => (clone $q)->where('estado_solicitud', 'aprobada')->count(),
            'Rechazadas' => (clone $q)->where('estado_solicitud', 'rechazada')->count(),
            'Urgentes'   => (clone $q)->where('prioridad', 'urgente')->count(),
        ];
    }

    public function signatures(): array
    {
        return [];
    }
}