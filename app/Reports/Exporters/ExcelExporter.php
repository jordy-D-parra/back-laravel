<?php

namespace App\Reports\Exporters;

use App\Reports\Contracts\ExportableInterface;
use App\Reports\Contracts\ReportInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter implements ExportableInterface
{
    public function download(ReportInterface $report, array $params, string $filename)
    {
        $rows    = $report->all();
        $columns = $report->columns();
        $totalCols = count($columns);
        $lastCol   = $this->columnLetter($totalCols);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($report->title(), 0, 30));

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue("A1", "REPUBLICA BOLIVARIANA DE VENEZUELA");
        $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue("A2", "GOBERNACION DEL ESTADO YARACUY - DIRECCION DE INFORMATICA");
        $sheet->getStyle("A2")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue("A3", $report->title());
        $sheet->getStyle("A3")->getFont()->setBold(true)->setSize(13)->getColor()->setRGB("1E3C72");
        $sheet->getStyle("A3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->setCellValue("A4", "Documento: " . $report->codigoDocumento() . "   |   Hash: " . $report->hashDocumento());
        $sheet->getStyle("A4")->getFont()->setSize(9)->getColor()->setRGB("666666");

        $sheet->mergeCells("A5:{$lastCol}5");
        $sheet->setCellValue("A5", "Generado por: " . $report->usuarioGenera() . "   |   Fecha: " . now()->format("d/m/Y H:i"));
        $sheet->getStyle("A5")->getFont()->setSize(9)->getColor()->setRGB("666666");

        $sheet->mergeCells("A6:{$lastCol}6");
        $sheet->setCellValue("A6", "Filtros: " . $report->filtrosAplicados());
        $sheet->getStyle("A6")->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB("666666");

        $headerRow = 8;
        $col = 1;
        foreach ($columns as $c) {
            $letter = $this->columnLetter($col);
            $sheet->setCellValue("{$letter}{$headerRow}", $c["label"]);

            $sheet->getStyle("{$letter}{$headerRow}")->applyFromArray([
                "font"      => ["bold" => true, "color" => ["rgb" => "FFFFFF"], "size" => 10],
                "fill"      => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "1E3C72"]],
                "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER, "vertical" => Alignment::VERTICAL_CENTER],
                "borders"   => ["allBorders" => ["borderStyle" => Border::BORDER_THIN, "color" => ["rgb" => "FFFFFF"]]],
            ]);
            $col++;
        }

        $row = $headerRow + 1;
        foreach ($rows as $item) {
            $col = 1;
            foreach ($columns as $c) {
                $letter = $this->columnLetter($col);
                $value  = data_get($item, $c["key"]);

                if (($c["format"] ?? null) === "date" && $value) {
                    try {
                        $value = \Carbon\Carbon::parse($value)->format("d/m/Y");
                    } catch (\Throwable $e) {}
                }

                $sheet->setCellValue("{$letter}{$row}", $value);

                $align = match ($c["align"] ?? "left") {
                    "center" => Alignment::HORIZONTAL_CENTER,
                    "right"  => Alignment::HORIZONTAL_RIGHT,
                    default  => Alignment::HORIZONTAL_LEFT,
                };
                $sheet->getStyle("{$letter}{$row}")->getAlignment()->setHorizontal($align);

                $sheet->getStyle("{$letter}{$row}")->applyFromArray([
                    "borders" => ["allBorders" => ["borderStyle" => Border::BORDER_THIN, "color" => ["rgb" => "DDDDDD"]]],
                ]);

                $col++;
            }
            $row++;
        }

        for ($i = 1; $i <= $totalCols; $i++) {
            $sheet->getColumnDimension($this->columnLetter($i))->setAutoSize(true);
        }

        $sheet->freezePane("A" . ($headerRow + 1));

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save("php://output");
        }, $filename . ".xlsx", [
            "Content-Type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        ]);
    }

    protected function columnLetter(int $index): string
    {
        $letter = "";
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = (int) (($index - $mod) / 26);
        }
        return $letter;
    }
}
