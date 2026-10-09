<?php

namespace App\Reports\Exporters;

use App\Reports\Contracts\ExportableInterface;
use App\Reports\Contracts\ReportInterface;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExporter implements ExportableInterface
{
    public function download(ReportInterface $report, array $params, string $filename)
    {
        $rows    = $report->all();
        $summary = $report->summary($params);

        $pdf = Pdf::loadView("reportes.pdf.generic", [
            "titulo"           => $report->title(),
            "descripcion"      => $report->description(),
            "codigo"           => $report->codigoDocumento(),
            "hash"             => $report->hashDocumento(),
            "usuario"          => $report->usuarioGenera(),
            "fecha_generacion" => now()->format("d/m/Y H:i"),
            "filtrosAplicados" => $report->filtrosAplicados(),
            "columnas"         => $report->columns(),
            "filas"            => $rows,
            "summary"          => $summary,
            "firmas"           => $report->signatures(),
            "total"            => $rows->count(),
        ])
        ->setPaper("letter", $report->pdfOrientation());

        return $pdf->stream($filename . ".pdf");
    }
}
