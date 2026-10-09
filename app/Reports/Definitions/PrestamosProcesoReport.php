<?php

namespace App\Reports\Definitions;

use App\Models\Prestamo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class PrestamosProcesoReport extends BaseReport
{
    public function key(): string            { return 'prestamos-proceso'; }
    public function title(): string          { return 'Préstamos en Proceso'; }
    public function description(): string    { return 'Préstamos activos (aprobados, entregados y extendidos) que aún no han sido devueltos.'; }
    public function category(): string       { return 'prestamos'; }
    public function icon(): string           { return 'clock'; }
    public function pdfOrientation(): string { return 'landscape'; }

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
                'responsableReceptor:id,nombre,telefono,email',
            ])
            ->withCount('detalles')
            ->whereIn('estado', ['aprobado', 'entregado', 'extendido'])
            ->whereNull('fecha_devolucion_real')
            ->when(!empty($params['buscar']), function ($q) use ($params) {
                $buscar = $params['buscar'];
                $q->where(function ($sq) use ($buscar) {
                    $sq->where('codigo', 'ILIKE', "%{$buscar}%")
                       ->orWhereHas('departamento', fn($d) => $d->where('nombre', 'ILIKE', "%{$buscar}%"))
                       ->orWhereHas('institucion', fn($i) => $i->where('nombre', 'ILIKE', "%{$buscar}%"))
                       ->orWhereHas('responsableReceptor', fn($r) => $r->where('nombre', 'ILIKE', "%{$buscar}%"));
                });
            })
            ->when(!empty($params['tipo']), fn($q, $v) => $q->where('tipo_prestamo', $v))
            ->when(!empty($params['departamento_id']), fn($q, $v) => $q->where('departamento_id', $v))
            ->when(!empty($params['institucion_id']), fn($q, $v) => $q->where('institucion_id', $v))
            ->orderBy('fecha_devolucion_esperada', 'asc');
    }

    public function columns(): array
    {
        return [
            ['key' => 'codigo',                     'label' => 'Código',       'width' => '10%'],
            ['key' => 'fecha_prestamo',             'label' => 'F. Préstamo',  'width' => '9%',  'format' => 'date'],
            ['key' => 'destino_nombre',             'label' => 'Destino',      'width' => '18%'],
            ['key' => 'responsableReceptor.nombre', 'label' => 'Responsable',  'width' => '15%'],
            ['key' => 'responsableReceptor.telefono','label' => 'Teléfono',    'width' => '11%'],
            ['key' => 'fecha_devolucion_esperada',  'label' => 'F. Tope',      'width' => '9%',  'format' => 'date'],
            ['key' => 'dias_restantes',             'label' => 'Días Rest.',   'width' => '8%',  'align' => 'center'],
            ['key' => 'detalles_count',             'label' => 'Items',        'width' => '6%',  'align' => 'center'],
            ['key' => 'estado',                     'label' => 'Estado',       'width' => '9%',  'format' => 'badge'],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            'Total activos' => (clone $q)->count(),
            'Aprobados'     => (clone $q)->where('estado', 'aprobado')->count(),
            'Entregados'    => (clone $q)->where('estado', 'entregado')->count(),
            'Extendidos'    => (clone $q)->where('estado', 'extendido')->count(),
            'Por vencer'    => (clone $q)
                                    ->whereDate('fecha_devolucion_esperada', '>=', now()->toDateString())
                                    ->whereDate('fecha_devolucion_esperada', '<=', now()->addDays(7)->toDateString())
                                    ->count(),
        ];
    }

    public function signatures(): array
    {
        return [
            ['role' => 'Elabora', 'nombre' => 'Departamento de Informática'],
            ['role' => 'Revisa',  'nombre' => 'Supervisor'],
        ];
    }
}