<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_solicitud', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_solicitud', 'descripcion_personalizada')) {
                $table->string('descripcion_personalizada', 500)->nullable()->after('cantidad_solicitada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_solicitud', function (Blueprint $table) {
            $table->dropColumn('descripcion_personalizada');
        });
    }
};
