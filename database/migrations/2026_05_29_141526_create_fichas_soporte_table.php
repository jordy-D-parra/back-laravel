<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichas_soporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos')->onDelete('cascade');
            $table->foreignId('tecnico_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('tecnico_nombre', 150)->nullable();
            $table->foreignId('usuario_reporta_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('usuario_reporta_nombre', 150)->nullable();
            $table->timestamp('fecha_ingreso')->useCurrent();
            $table->date('fecha_requerida_entrega')->nullable()->after('fecha_ingreso');
            $table->timestamp('fecha_salida')->nullable();

            // ✅ AGREGAR ESTAS COLUMNAS AQUÍ EN VEZ DE EN OTRA MIGRACIÓN
            $table->timestamp('fecha_aceptacion')->nullable()->after('fecha_salida');
            $table->timestamp('fecha_rechazo')->nullable()->after('fecha_aceptacion');
            $table->text('motivo_rechazo')->nullable()->after('fecha_rechazo');

            $table->text('diagnostico')->nullable();
            $table->text('trabajo_realizado')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado', 20)->default('en_proceso'); // ✅ Cambiado de enum a string
            $table->string('origen', 20)->default('manual')->after('estado');
            $table->timestamps();

            $table->index('activo_id');
            $table->index('estado');
            $table->index('fecha_ingreso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichas_soporte');
    }
};