<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activos', function (Blueprint $table) {
            $table->id();

            // ============================================================
            // IDENTIFICACIÓN
            // ============================================================
            $table->string('serial', 100)
                  ->unique()
                  ->comment('Número de serie único del equipo');

            $table->foreignId('modelo_id')
                  ->constrained('modelos')
                  ->onDelete('restrict');

            $table->foreignId('id_estatus')
                  ->constrained('estatus')
                  ->onDelete('restrict');

            // ============================================================
            // UBICACIÓN Y RESPONSABILIDAD
            // ============================================================
            $table->foreignId('institucion_id')
                  ->nullable()
                  ->constrained('instituciones')
                  ->onDelete('set null')
                  ->comment('Institución donde se encuentra el activo (puede ser null si se desconoce)');

            $table->foreignId('departamento_id')
                  ->nullable()
                  ->constrained('departamentos')
                  ->onDelete('set null');

            $table->foreignId('responsable_id')
                  ->nullable()
                  ->constrained('responsables')
                  ->onDelete('set null')
                  ->comment('Responsable actual del activo (se auto-asigna desde institución/departamento)');

            $table->string('ubicacion', 100)
                  ->nullable()
                  ->comment('Ubicación física: Oficina 3B, Laboratorio 2');

            // ============================================================
            // FECHAS Y VIDA ÚTIL
            // ============================================================
            $table->date('fecha_adquisicion')->nullable();
            $table->date('fecha_fin_garantia')->nullable();
            $table->integer('vida_util_anos')
                  ->nullable()
                  ->comment('Años de vida útil estimada');

            // ============================================================
            // ESPECIFICACIONES DINÁMICAS Y AGRUPACIÓN
            // ============================================================
            $table->json('especificaciones_tecnicas')
                  ->nullable()
                  ->comment('Campos dinámicos según categoría');

            $table->string('agrupacion', 100)
                  ->nullable()
                  ->comment('Agrupación de equipos (ej: Laboratorio A)');

            // ============================================================
            // RESERVA PARA PRÉSTAMOS
            // ============================================================
            $table->unsignedBigInteger('reservado_en_prestamo_id')
                  ->nullable()
                  ->comment('ID del préstamo que tiene reservado este activo');

            // ============================================================
            // IMPORTACIÓN MASIVA - DATOS INCOMPLETOS
            // ============================================================
            $table->json('datos_incompletos')
                  ->nullable()
                  ->comment('Campos que quedaron sin resolver durante la importación (JSON)');

            // ============================================================
            // OBSERVACIONES
            // ============================================================
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // ============================================================
            // ÍNDICES
            // ============================================================
            $table->index('serial');
            $table->index('modelo_id');
            $table->index('id_estatus');
            $table->index('institucion_id');
            $table->index('fecha_adquisicion');
            $table->index('fecha_fin_garantia');
            $table->index('reservado_en_prestamo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};