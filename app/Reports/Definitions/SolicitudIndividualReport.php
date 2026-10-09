<?php

namespace App\Reports\Definitions;

use App\Models\Solicitud;
use App\Reports\Contracts\IndividualReportInterface;
use Illuminate\Support\Str;

class SolicitudIndividualReport implements IndividualReportInterface
{
    public function key(): string            { return 'solicitud-individual'; }
    public function title(): string          { return 'Ficha Tecnica de Solicitud'; }
    public function description(): string    { return 'Reporte individual de una solicitud de prestamo con sus items.'; }
    public function category(): string       { return 'solicitudes'; }
    public function icon(): string           { return 'file-text'; }
    public function pdfOrientation(): string { return 'portrait'; }

    public function requiredPermission(): ?string
    {
        return 'ver-solicitudes';
    }

    public function find(int $id): mixed
    {
        return Solicitud::with([
            'usuario.trabajador',
            'institucion',
            'departamento',
            'responsable',
            'estado',
            'municipio',
            'parroquia',
            'detalles.activo.modelo.marca',
            'detalles.componente',
        ])->findOrFail($id);
    }

    public function view(): string
    {
        return 'reportes.pdf.solicitud-individual';
    }

    public function filename(mixed $record): string
    {
        $codigo = 'SOL-' . str_pad($record->id, 6, '0', STR_PAD_LEFT);
        return 'ficha-solicitud-' . Str::slug($codigo) . '-' . now()->format('Ymd_His');
    }
}