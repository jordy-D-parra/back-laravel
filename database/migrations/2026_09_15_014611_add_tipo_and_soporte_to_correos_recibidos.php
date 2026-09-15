<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('correos_recibidos', function (Blueprint $table) {
            // Tipo de correo: 'solicitud' o 'soporte'
            if (!Schema::hasColumn('correos_recibidos', 'tipo')) {
                $table->string('tipo', 20)->default('solicitud')->after('subject');
                $table->index('tipo');
            }

            // Ficha de soporte generada (si aplica)
            if (!Schema::hasColumn('correos_recibidos', 'ficha_soporte_id')) {
                $table->foreignId('ficha_soporte_id')
                    ->nullable()
                    ->after('solicitud_id')
                    ->constrained('fichas_soporte')
                    ->onDelete('set null');
            }

            // Fecha requerida de entrega extraída del correo
            if (!Schema::hasColumn('correos_recibidos', 'fecha_requerida_entrega')) {
                $table->date('fecha_requerida_entrega')->nullable()->after('datos_extraidos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('correos_recibidos', function (Blueprint $table) {
            if (Schema::hasColumn('correos_recibidos', 'ficha_soporte_id')) {
                $table->dropForeign(['ficha_soporte_id']);
                $table->dropColumn('ficha_soporte_id');
            }
            if (Schema::hasColumn('correos_recibidos', 'tipo')) {
                $table->dropIndex(['tipo']);
                $table->dropColumn('tipo');
            }
            if (Schema::hasColumn('correos_recibidos', 'fecha_requerida_entrega')) {
                $table->dropColumn('fecha_requerida_entrega');
            }
        });
    }
};