<?php

namespace App\Reports\Definitions;

use App\Models\Prestamo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

/**
 * PLACEHOLDER - pendiente de implementar logica real.
 * Ya se puede listar en el explorador y probar el sistema.
 */
class AuditoriaReport extends BaseReport
{
    public function key(): string            { return "auditoria"; }
    public function title(): string          { return "Auditoria del Sistema"; }
    public function description(): string    { return "Reporte en construccion."; }
    public function category(): string       { return "auditoria"; }
    public function icon(): string           { return "book"; }
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
        ];
    }

    public function query(array $params): Builder
    {
        return Prestamo::query()->whereRaw("1 = 0");
    }

    public function columns(): array
    {
        return [
            ["key" => "id", "label" => "ID", "width" => "10%"],
        ];
    }

    public function summary(array $params): array
    {
        return [];
    }

    public function signatures(): array
    {
        return [];
    }

        public function requiredPermission(): ?string
    {
        return "ver-auditoria";
    }
}
