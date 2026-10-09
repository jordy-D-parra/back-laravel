<?php

namespace App\Reports\Contracts;

interface ExportableInterface
{
    public function download(ReportInterface $report, array $params, string $filename);
}
