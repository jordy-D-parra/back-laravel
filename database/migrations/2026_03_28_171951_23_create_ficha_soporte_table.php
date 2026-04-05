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
            $table->foreignId('activo_id')->constrained('activo')->onDelete('cascade');
            $table->foreignId('tecnico_id')->constrained('usuario');
            $table->foreignId('usuario_reporta_id')->constrained('usuario');
            $table->timestamp('fecha_ingreso')->useCurrent();
            $table->timestamp('fecha_salida')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('trabajo_realizado')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado', 20)->default('en_proceso');
            $table->timestamps();

            $table->index('activo_id');
            $table->index('tecnico_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_soporte');
    }
};
