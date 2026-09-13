<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            // Verificar si la columna ya existe antes de agregarla
            if (!Schema::hasColumn('solicitud', 'departamento_id')) {
                $table->foreignId('departamento_id')
                      ->nullable()
                      ->after('institucion_id')
                      ->constrained('departamento')
                      ->onDelete('set null');
            }

            // Verificar si la columna ya existe antes de agregarla
            if (!Schema::hasColumn('solicitud', 'responsable_id')) {
                $table->foreignId('responsable_id')
                      ->nullable()
                      ->after('departamento_id')
                      ->constrained('responsable')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropForeign(['responsable_id']);
            $table->dropColumn(['departamento_id', 'responsable_id']);
        });
    }
};
