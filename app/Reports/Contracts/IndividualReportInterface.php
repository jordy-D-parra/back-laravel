<?php

namespace App\Reports\Contracts;

interface IndividualReportInterface
{
    /**
     * Identificador unico del reporte (ej: "activo-individual").
     */
    public function key(): string;

    /**
     * Titulo legible del reporte.
     */
    public function title(): string;

    /**
     * Descripcion corta del reporte.
     */
    public function description(): string;

    /**
     * Categoria a la que pertenece (inventario, prestamos, etc).
     */
    public function category(): string;

    /**
     * Nombre del icono (emoji o similar).
     */
    public function icon(): string;

    /**
     * Orientacion del PDF: 'portrait' o 'landscape'.
     */
    public function pdfOrientation(): string;

    /**
     * Permiso adicional requerido. Null = basta con "ver-reportes".
     */
    public function requiredPermission(): ?string;

    /**
     * Busca el registro por su ID.
     * Debe lanzar ModelNotFoundException si no existe.
     */
    public function find(int $id): mixed;

    /**
     * Ruta de la vista Blade a usar para generar el PDF.
     */
    public function view(): string;

    /**
     * Nombre de archivo sugerido para el PDF.
     */
    public function filename(mixed $record): string;
}