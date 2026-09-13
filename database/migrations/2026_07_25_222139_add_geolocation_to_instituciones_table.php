<?php
// database/migrations/2026_07_25_222139_add_geolocation_to_instituciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            // Agregar los 3 campos de ubicación
            $table->foreignId('estado_id')->nullable()->after('informacion')->constrained('estados')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->after('estado_id')->constrained('municipios')->onDelete('set null');
            $table->foreignId('parroquia_id')->nullable()->after('municipio_id')->constrained('parroquias')->onDelete('set null');

            // Eliminar el campo 'ubicacion' (ya no se usa)
            if (Schema::hasColumn('instituciones', 'ubicacion')) {
                $table->dropColumn('ubicacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropForeign(['parroquia_id']);
            $table->dropForeign(['municipio_id']);
            $table->dropForeign(['estado_id']);
            $table->dropColumn(['estado_id', 'municipio_id', 'parroquia_id']);

            // Restaurar ubicacion
            if (!Schema::hasColumn('instituciones', 'ubicacion')) {
                $table->string('ubicacion', 200)->nullable()->after('informacion');
            }
        });
    }
};
