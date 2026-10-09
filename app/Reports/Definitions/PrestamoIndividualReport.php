<?php

namespace App\Reports\Definitions;

use App\Models\Prestamo;
use App\Reports\Contracts\IndividualReportInterface;
use Illuminate\Support\Str;

class PrestamoIndividualReport implements IndividualReportInterface
{
    public function key(): string            { return 'prestamo-individual'; }
    public function title(): string          { return 'Ficha Tecnica de Prestamo'; }
    public function description(): string    { return 'Reporte individual de un prestamo con sus items, extensiones y control de devolucion.'; }
    public function category(): string       { return 'prestamos'; }
    public function icon(): string           { return 'package'; }
    public function pdfOrientation(): string { return 'portrait'; }

    public function requiredPermission(): ?string
    {
        return 'ver-prestamos';
    }

    public function find(int $id): mixed
    {
        return Prestamo::with([
            'departamento',
            'institucion',
            'responsableReceptor',
            'responsableEmisor',
            'usuarioRegistra.trabajador',
            'solicitud',
            'detalles.prestable',
            'extensiones.aprobadoPor.trabajador',
        ])->findOrFail($id);
    }

    public function view(): string
    {
        return 'reportes.pdf.prestamo-individual';
    }

    public function filename(mixed $record): string
    {
        $codigo = $record->codigo ?? ('PRES-' . $record->id);
        return 'ficha-prestamo-' . Str::slug($codigo) . '-' . now()->format('Ymd_His');
    }
}