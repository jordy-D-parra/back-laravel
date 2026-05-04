<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activo')->onDelete('cascade');
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitud')->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 20)->default('confirmada');
            $table->timestamps();

            $table->index('activo_id');
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};
