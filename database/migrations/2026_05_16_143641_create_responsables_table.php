<?php
// database/migrations/2026_05_16_143641_create_responsables_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('responsables', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('documento', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('cargo', 100)->nullable();
            $table->boolean('activo')->default(true);

            // ✅ SOLO UNA VEZ - Corregido el duplicado
            $table->foreignId('institucion_id')
                  ->nullable()
                  ->constrained('instituciones')
                  ->onDelete('set null');

            $table->foreignId('departamento_id')
                  ->nullable()
                  ->constrained('departamentos')
                  ->onDelete('set null');

            $table->timestamps();

            // Índices para mejorar rendimiento
            $table->index('nombre');
            $table->index('documento');
            $table->index('activo');
            $table->index('institucion_id');
            $table->index('departamento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsables');
    }
};
