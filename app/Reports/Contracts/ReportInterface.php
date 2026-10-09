<?php

namespace App\Reports\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ReportInterface
{
    public function key(): string;
    public function title(): string;
    public function description(): string;
    public function category(): string;
    public function icon(): string;
    public function pdfOrientation(): string;
    public function filters(): array;
    public function query(array $params): Builder;
    public function columns(): array;
    public function summary(array $params): array;
    public function signatures(): array;

    /**
     * Permiso adicional requerido.
     * Si retorna null, basta con "ver-reportes".
     */
    public function requiredPermission(): ?string;
}