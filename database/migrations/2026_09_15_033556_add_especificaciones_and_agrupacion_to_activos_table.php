<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            // Columna JSON para especificaciones técnicas dinámicas
            if (!Schema::hasColumn('activos', 'especificaciones_tecnicas')) {
                $table->json('especificaciones_tecnicas')
                      ->nullable()
                      ->after('vida_util_anos')
                      ->comment('Campos dinámicos según categoría');
            }

            // Columna para agrupación de equipos
            if (!Schema::hasColumn('activos', 'agrupacion')) {
                $table->string('agrupacion', 100)
                      ->nullable()
                      ->after('especificaciones_tecnicas')
                      ->comment('Agrupación de equipos (ej: Laboratorio A)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            if (Schema::hasColumn('activos', 'agrupacion')) {
                $table->dropColumn('agrupacion');
            }
            if (Schema::hasColumn('activos', 'especificaciones_tecnicas')) {
                $table->dropColumn('especificaciones_tecnicas');
            }
        });
    }
};