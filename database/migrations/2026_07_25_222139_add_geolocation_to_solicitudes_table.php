<?php
// database/migrations/2026_07_25_222139_add_geolocation_to_solicitudes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->foreignId('estado_id')->nullable()->after('fecha_fin_estimada')->constrained('estados')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->after('estado_id')->constrained('municipios')->onDelete('set null');
            $table->foreignId('parroquia_id')->nullable()->after('municipio_id')->constrained('parroquias')->onDelete('set null');
            $table->string('lugar_evento', 200)->nullable()->after('parroquia_id');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropForeign(['parroquia_id']);
            $table->dropForeign(['municipio_id']);
            $table->dropForeign(['estado_id']);
            $table->dropColumn(['estado_id', 'municipio_id', 'parroquia_id', 'lugar_evento']);
        });
    }
};