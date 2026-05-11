<?php
// database/migrations/2026_05_11_xxxxxx_add_columns_to_activo_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            // Agregar columnas faltantes
            if (!Schema::hasColumn('activo', 'vida_util_anos')) {
                $table->integer('vida_util_anos')->nullable()->after('fecha_adquisicion');
            }

            if (!Schema::hasColumn('activo', 'fecha_fin_garantia')) {
                $table->date('fecha_fin_garantia')->nullable()->after('vida_util_anos');
            }

            if (!Schema::hasColumn('activo', 'especificaciones_tecnicas')) {
                $table->json('especificaciones_tecnicas')->nullable()->after('observaciones');
            }

            if (!Schema::hasColumn('activo', 'id_marca')) {
                $table->foreignId('id_marca')->nullable()->after('id_categoria')->constrained('marcas')->nullOnDelete();
                $table->index('id_marca');
            }

            if (!Schema::hasColumn('activo', 'id_modelo')) {
                $table->foreignId('id_modelo')->nullable()->after('id_marca')->constrained('modelos')->nullOnDelete();
                $table->index('id_modelo');
            }

            // Marcar id_tipo_activo como nullable (por si queremos migrar)
            $table->foreignId('id_tipo_activo')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            $columns = ['vida_util_anos', 'fecha_fin_garantia', 'especificaciones_tecnicas', 'id_marca', 'id_modelo'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('activo', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
