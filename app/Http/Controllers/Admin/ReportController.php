<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Reports\Contracts\ReportInterface;
use App\Reports\Exporters\CsvExporter;
use App\Reports\Exporters\ExcelExporter;
use App\Reports\Exporters\PdfExporter;
use App\Reports\ReportRegistry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected PdfExporter   $pdf,
        protected ExcelExporter $xlsx,
        protected CsvExporter   $csv,
    ) {}

    // ============================================================
    // VISTA DEL EXPLORADOR (SIN CAMBIOS)
    // ============================================================
    public function index()
    {
        if (!auth()->user()->hasPermission("ver-reportes")) {
            abort(403, "No tienes permiso para ver reportes");
        }

        $catalogo = collect(ReportRegistry::all())
            ->map(function ($items) {
                return collect($items)->filter(function ($item) {
                    $report = ReportRegistry::make($item["key"]);
                    $perm = $report->requiredPermission();
                    return !$perm || auth()->user()->hasPermission($perm);
                })->all();
            })
            ->filter(fn($items) => count($items) > 0)
            ->all();

        $fuentes = [
            "departamentos" => Departamento::where("activo", true)
                ->orderBy("nombre")
                ->get(["id", "nombre"])
                ->map(fn($d) => ["value" => $d->id, "label" => $d->nombre])
                ->toArray(),

            "instituciones" => Institucion::where("activo", true)
                ->orderBy("nombre")
                ->get(["id", "nombre"])
                ->map(fn($i) => ["value" => $i->id, "label" => $i->nombre])
                ->toArray(),
        ];

        return view("reportes.explorer", compact("catalogo", "fuentes"));
    }

    // ============================================================
    // JSON DE DATOS (SIN CAMBIOS)
    // ============================================================
    public function data(string $key, Request $request)
    {
        if (!auth()->user()->hasPermission("ver-reportes")) {
            return response()->json(["success" => false, "message" => "No autorizado"], 403);
        }

        $report = ReportRegistry::make($key);

        if ($report->requiredPermission() && !auth()->user()->hasPermission($report->requiredPermission())) {
            return response()->json(["success" => false, "message" => "No tienes permiso para este reporte"], 403);
        }

        $params = $this->validatedParams($report, $request);
        $report->withParams($params);
        $paginado = $report->paginate(15);

        return response()->json([
            "success" => true,
            "report"  => [
                "key"         => $report->key(),
                "title"       => $report->title(),
                "description" => $report->description(),
                "orientation" => $report->pdfOrientation(),
            ],
            "filters"   => $report->filters(),
            "columns"   => $report->columns(),
            "summary"   => $report->summary($params),
            "rows"      => $paginado->items(),
            "pagination" => [
                "current_page" => $paginado->currentPage(),
                "last_page"    => $paginado->lastPage(),
                "per_page"     => $paginado->perPage(),
                "total"        => $paginado->total(),
                "from"         => $paginado->firstItem(),
                "to"           => $paginado->lastItem(),
            ],
            "filtros_aplicados" => $report->filtrosAplicados(),
            "codigo_documento"  => $report->codigoDocumento(),
        ]);
    }

    // ============================================================
    // DESCARGA (SIN CAMBIOS)
    // ============================================================
    public function download(string $key, string $format, Request $request)
    {
        if (!auth()->user()->hasPermission("ver-reportes")) {
            abort(403, "No tienes permiso para exportar reportes");
        }

        $report = ReportRegistry::make($key);

        if ($report->requiredPermission() && !auth()->user()->hasPermission($report->requiredPermission())) {
            abort(403, "No tienes permiso para este reporte");
        }

        $params = $this->validatedParams($report, $request);
        $report->withParams($params);
        $filename = $report->filename();

        return match ($format) {
            "pdf"   => $this->pdf->download($report, $params, $filename),
            "xlsx"  => $this->xlsx->download($report, $params, $filename),
            "csv"   => $this->csv->download($report, $params, $filename),
            default => abort(400, "Formato no soportado"),
        };
    }

    // ============================================================
    // NUEVO: VISTA PREVIA DEL REPORTE INDIVIDUAL (abre en nueva pestaña)
    // ============================================================
    public function individual(string $key, int $id)
    {
        if (!auth()->user()->hasPermission("ver-reportes")) {
            abort(403, "No tienes permiso para ver reportes");
        }

        $report = ReportRegistry::makeIndividual($key);

        if ($report->requiredPermission() && !auth()->user()->hasPermission($report->requiredPermission())) {
            abort(403, "No tienes permiso para este reporte");
        }

        $record = $report->find($id);

        $pdf = Pdf::loadView($report->view(), [
            'report' => $report,
            'record' => $record,
            'titulo' => $report->title(),
        ])->setPaper('letter', $report->pdfOrientation());

        // ✅ stream() → se abre en el navegador para previsualizar
        return $pdf->stream($report->filename($record) . '.pdf');
    }

    // ============================================================
    // NUEVO: DESCARGA DIRECTA DEL REPORTE INDIVIDUAL
    // ============================================================
    public function individualPdf(string $key, int $id)
    {
        if (!auth()->user()->hasPermission("ver-reportes")) {
            abort(403, "No tienes permiso para exportar reportes");
        }

        $report = ReportRegistry::makeIndividual($key);

        if ($report->requiredPermission() && !auth()->user()->hasPermission($report->requiredPermission())) {
            abort(403, "No tienes permiso para este reporte");
        }

        $record = $report->find($id);

        $pdf = Pdf::loadView($report->view(), [
            'report' => $report,
            'record' => $record,
            'titulo' => $report->title(),
        ])->setPaper('letter', $report->pdfOrientation());

        return $pdf->download($report->filename($record) . '.pdf');
    }

    // ============================================================
    // VALIDACION DE PARAMETROS (SIN CAMBIOS)
    // ============================================================
    protected function validatedParams(ReportInterface $report, Request $request): array
    {
        $rules = [];

        foreach ($report->filters() as $name => $f) {
            $rules[$name] = match ($f["type"]) {
                "daterange" => "required|array",
                "date"      => "required|date",
                "select"    => "nullable",
                "text"      => "nullable|string",
                default     => "nullable",
            };
        }

        return $request->validate($rules);
    }
}