<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_soporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->nullable()->constrained('activo')->onDelete('cascade');
            $table->foreignId('tecnico_id')->nullable()->constrained('usuario');
            $table->foreignId('usuario_reporta_id')->constrained('usuario');
            $table->string('serial_asignado')->nullable(); // 🔥 COLUMNA AGREGADA
            $table->string('equipo_externo_nombre')->nullable(); // Para equipos externos
            $table->timestamp('fecha_ingreso')->useCurrent();
            $table->timestamp('fecha_entrega')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('trabajo_realizado')->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('costo_reparacion', 10, 2)->nullable(); // Costo de reparación
            $table->string('estado', 20)->default('pendiente');
            $table->foreignId('creado_por')->nullable()->constrained('usuario');
            $table->timestamps();

            $table->index('activo_id');
            $table->index('tecnico_id');
            $table->index('estado');
            $table->index('serial_asignado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_soporte');
    }
};