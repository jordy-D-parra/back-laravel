<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responsables', function (Blueprint $table) {
            // Primero, limpiar posibles duplicados (mantener el más antiguo)
            DB::statement('
                DELETE FROM responsables
                WHERE id NOT IN (
                    SELECT MIN(id)
                    FROM responsables
                    WHERE documento IS NOT NULL AND documento != \'\'
                    GROUP BY documento
                )
            ');

            // Luego, agregar unique constraint
            $table->unique('documento', 'responsables_documento_unique');
        });
    }

    public function down(): void
    {
        Schema::table('responsables', function (Blueprint $table) {
            $table->dropUnique('responsables_documento_unique');
        });
    }
};
