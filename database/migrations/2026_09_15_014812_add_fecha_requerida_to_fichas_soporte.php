<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fichas_soporte', function (Blueprint $table) {
            if (!Schema::hasColumn('fichas_soporte', 'fecha_requerida_entrega')) {
                $table->date('fecha_requerida_entrega')
                    ->nullable()
                    ->after('fecha_ingreso');
            }

            if (!Schema::hasColumn('fichas_soporte', 'correo_id')) {
                $table->foreignId('correo_id')
                    ->nullable()
                    ->after('activo_id')
                    ->constrained('correos_recibidos')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('fichas_soporte', 'origen')) {
                $table->string('origen', 20)->default('manual')->after('estado');
                // Valores: 'manual' o 'correo'
            }
        });
    }

    public function down(): void
    {
        Schema::table('fichas_soporte', function (Blueprint $table) {
            if (Schema::hasColumn('fichas_soporte', 'correo_id')) {
                $table->dropForeign(['correo_id']);
                $table->dropColumn('correo_id');
            }
            if (Schema::hasColumn('fichas_soporte', 'fecha_requerida_entrega')) {
                $table->dropColumn('fecha_requerida_entrega');
            }
            if (Schema::hasColumn('fichas_soporte', 'origen')) {
                $table->dropColumn('origen');
            }
        });
    }
};