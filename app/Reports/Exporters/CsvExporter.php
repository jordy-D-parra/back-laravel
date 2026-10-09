<?php

namespace App\Reports\Exporters;

use App\Reports\Contracts\ExportableInterface;
use App\Reports\Contracts\ReportInterface;

class CsvExporter implements ExportableInterface
{
    public function download(ReportInterface $report, array $params, string $filename)
    {
        $rows    = $report->all();
        $columns = $report->columns();

        return response()->streamDownload(function () use ($rows, $columns) {
            $handle = fopen("php://output", "w");

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, array_column($columns, "label"), ";");

            foreach ($rows as $item) {
                $fila = [];
                foreach ($columns as $c) {
                    $value = data_get($item, $c["key"]);

                    if (($c["format"] ?? null) === "date" && $value) {
                        try {
                            $value = \Carbon\Carbon::parse($value)->format("d/m/Y");
                        } catch (\Throwable $e) {}
                    }

                    $fila[] = $value;
                }
                fputcsv($handle, $fila, ";");
            }

            fclose($handle);
        }, $filename . ".csv", [
            "Content-Type" => "text/csv; charset=UTF-8",
        ]);
    }
}
