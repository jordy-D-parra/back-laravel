<?php

namespace App\Reports\Definitions;

use App\Models\FichaSoporte;
use App\Reports\Contracts\IndividualReportInterface;
use Illuminate\Support\Str;

class SoporteIndividualReport implements IndividualReportInterface
{
    public function key(): string            { return 'soporte-individual'; }
    public function title(): string          { return 'Ficha Tecnica de Soporte'; }
    public function description(): string    { return 'Reporte individual de una ficha de soporte tecnico con sus componentes revisados.'; }
    public function category(): string       { return 'soporte'; }
    public function icon(): string           { return 'tool'; }
    public function pdfOrientation(): string { return 'portrait'; }

    public function requiredPermission(): ?string
    {
        return 'ver-fichas-soporte';
    }

    public function find(int $id): mixed
    {
        return FichaSoporte::with([
            'activo.modelo.marca',
            'activo.modelo.categoria',
            'activo.estatus',
            'activo.institucion.estado',
            'activo.institucion.municipio',
            'activo.institucion.parroquia',
            'activo.departamento',
            'activo.responsable',
            'tecnico.trabajador',
            'usuarioReporta.trabajador',
            'detalles.componente',
            'correo',
        ])->findOrFail($id);
    }

    public function view(): string
    {
        return 'reportes.pdf.soporte-individual';
    }

    public function filename(mixed $record): string
    {
        $codigo = 'SOP-' . str_pad($record->id, 6, '0', STR_PAD_LEFT);
        return 'ficha-soporte-' . Str::slug($codigo) . '-' . now()->format('Ymd_His');
    }
}