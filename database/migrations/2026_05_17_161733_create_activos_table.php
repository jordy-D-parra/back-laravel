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
            $table->string('serial', 100)->unique()->comment('Número de serie único del equipo');
            $table->foreignId('modelo_id')->constrained('modelos')->onDelete('restrict');
            $table->foreignId('id_estatus')->constrained('estatus')->onDelete('restrict');
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('restrict');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->foreignId('responsable_id')->constrained('responsables')->onDelete('restrict');
            $table->string('ubicacion', 100)->nullable()->comment('Ubicación física: Oficina 3B, Laboratorio 2');
            $table->date('fecha_adquisicion')->nullable();
            $table->date('fecha_fin_garantia')->nullable();
            $table->integer('vida_util_anos')->nullable()->comment('Años de vida útil estimada');

            // ✅ Consolidado desde: add_especificaciones_and_agrupacion_to_activos_table
            $table->json('especificaciones_tecnicas')->nullable()
                  ->comment('Campos dinámicos según categoría');
            $table->string('agrupacion', 100)->nullable()
                  ->comment('Agrupación de equipos (ej: Laboratorio A)');

            $table->unsignedBigInteger('reservado_en_prestamo_id')->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();

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