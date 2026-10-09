<?php

namespace App\Reports\Definitions;

use App\Models\Activo;
use App\Reports\Contracts\IndividualReportInterface;
use Illuminate\Support\Str;

class ActivoIndividualReport implements IndividualReportInterface
{
    public function key(): string            { return 'activo-individual'; }
    public function title(): string          { return 'Ficha Tecnica de Activo'; }
    public function description(): string    { return 'Reporte individual de un activo con sus componentes instalados.'; }
    public function category(): string       { return 'inventario'; }
    public function icon(): string           { return 'monitor'; }
    public function pdfOrientation(): string { return 'portrait'; }

    public function requiredPermission(): ?string
    {
        return 'ver-activos';
    }

    /**
     * Busca el activo con TODAS sus relaciones necesarias para la ficha.
     */
    public function find(int $id): mixed
    {
        return Activo::with([
            'modelo.marca',
            'modelo.categoria',
            'estatus',
            'institucion.estado',
            'institucion.municipio',
            'institucion.parroquia',
            'departamento',
            'responsable',
            'componentes',
        ])->findOrFail($id);
    }

    public function view(): string
    {
        return 'reportes.pdf.activo-individual';
    }

    public function filename(mixed $record): string
    {
        $serial = $record->serial ?? ('activo-' . $record->id);
        return 'ficha-activo-' . Str::slug($serial) . '-' . now()->format('Ymd_His');
    }
}