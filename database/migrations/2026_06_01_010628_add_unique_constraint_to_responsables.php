<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la tabla existe
        if (Schema::hasTable('responsables')) {
            // Primero, eliminar duplicados si los hay (opcional)
            // Esto es útil si ya tienes datos
            DB::statement('
                DELETE FROM responsables
                WHERE id NOT IN (
                    SELECT MIN(id)
                    FROM responsables
                    WHERE institucion_id IS NOT NULL
                    GROUP BY nombre, documento, institucion_id
                )
            ');

            // Agregar el unique constraint
            Schema::table('responsables', function (Blueprint $table) {
                // Solo crear si no existe
                $table->unique(['nombre', 'documento', 'institucion_id'], 'unique_responsable_por_institucion');
            });
        }
    }

    public function down(): void
    {
        Schema::table('responsables', function (Blueprint $table) {
            // Solo eliminar si existe
            if (Schema::hasTable('responsables')) {
                $table->dropUniqueIfExists('unique_responsable_por_institucion');
            }
        });
    }
};
