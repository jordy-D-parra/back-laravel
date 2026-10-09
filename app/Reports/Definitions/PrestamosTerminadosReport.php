<?php

namespace App\Reports\Definitions;

use App\Models\Prestamo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class PrestamosTerminadosReport extends BaseReport
{
    public function key(): string            { return 'prestamos-terminados'; }
    public function title(): string          { return 'Préstamos Terminados'; }
    public function description(): string    { return 'Préstamos finalizados (devueltos, cancelados y rechazados).'; }
    public function category(): string       { return 'prestamos'; }
    public function icon(): string           { return 'check-circle'; }
    public function pdfOrientation(): string { return 'landscape'; }

    public function requiredPermission(): ?string
    {
        return 'ver-prestamos';
    }

    public function filters(): array
    {
        return [
            'rango' => [
                'type'     => 'daterange',
                'label'    => 'Rango de fechas',
                'required' => true,
                'default'  => [
                    'from' => now()->startOfMonth()->toDateString(),
                    'to'   => now()->toDateString(),
                ],
            ],
            'buscar' => [
                'type'  => 'text',
                'label' => 'Buscar',
            ],
            'estado' => [
                'type'    => 'select',
                'label'   => 'Estado',
                'options' => [
                    ''          => 'Todos',
                    'devuelto'  => 'Devuelto',
                    'cancelado' => 'Cancelado',
                    'rechazado' => 'Rechazado',
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
        $from = $params['rango']['from'] ?? null;
        $to   = $params['rango']['to']   ?? null;

        return Prestamo::query()
            ->with([
                'departamento:id,nombre',
                'institucion:id,nombre',
                'responsableReceptor:id,nombre',
                'responsableEmisor:id,nombre',
            ])
            ->withCount('detalles')
            ->whereIn('estado', ['devuelto', 'cancelado', 'rechazado'])
            ->when($from, fn($q, $v) => $q->whereDate('fecha_prestamo', '>=', $v))
            ->when($to,   fn($q, $v) => $q->whereDate('fecha_prestamo', '<=', $v))
            ->when(!empty($params['buscar']), function ($q) use ($params) {
                $buscar = $params['buscar'];
                $q->where(function ($sq) use ($buscar) {
                    $sq->where('codigo', 'ILIKE', "%{$buscar}%")
                       ->orWhereHas('departamento', fn($d) => $d->where('nombre', 'ILIKE', "%{$buscar}%"))
                       ->orWhereHas('institucion', fn($i) => $i->where('nombre', 'ILIKE', "%{$buscar}%"))
                       ->orWhereHas('responsableReceptor', fn($r) => $r->where('nombre', 'ILIKE', "%{$buscar}%"));
                });
            })
            ->when(!empty($params['estado']), fn($q, $v) => $q->where('estado', $v))
            ->when(!empty($params['tipo']), fn($q, $v) => $q->where('tipo_prestamo', $v))
            ->when(!empty($params['departamento_id']), fn($q, $v) => $q->where('departamento_id', $v))
            ->when(!empty($params['institucion_id']), fn($q, $v) => $q->where('institucion_id', $v))
            ->orderByDesc('fecha_prestamo');
    }

    public function columns(): array
    {
        return [
            ['key' => 'codigo',                     'label' => 'Código',       'width' => '10%'],
            ['key' => 'fecha_prestamo',             'label' => 'F. Préstamo',  'width' => '9%',  'format' => 'date'],
            ['key' => 'fecha_devolucion_real',      'label' => 'F. Devolución','width' => '9%',  'format' => 'date'],
            ['key' => 'destino_nombre',             'label' => 'Destino',      'width' => '20%'],
            ['key' => 'responsableReceptor.nombre', 'label' => 'Responsable',  'width' => '16%'],
            ['key' => 'responsableEmisor.nombre',   'label' => 'Entregado por','width' => '14%'],
            ['key' => 'tipo_prestamo',              'label' => 'Tipo',         'width' => '8%'],
            ['key' => 'detalles_count',             'label' => 'Items',        'width' => '6%',  'align' => 'center'],
            ['key' => 'estado',                     'label' => 'Estado',       'width' => '8%',  'format' => 'badge'],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            'Total terminados' => (clone $q)->count(),
            'Devueltos'        => (clone $q)->where('estado', 'devuelto')->count(),
            'Cancelados'       => (clone $q)->where('estado', 'cancelado')->count(),
            'Rechazados'       => (clone $q)->where('estado', 'rechazado')->count(),
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