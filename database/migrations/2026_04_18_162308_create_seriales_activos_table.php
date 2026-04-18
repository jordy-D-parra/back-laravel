<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seriales_activo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activo')->onDelete('cascade');
            $table->string('serial', 100)->unique();
            $table->enum('estado', ['disponible', 'asignado', 'reparacion', 'dado_baja'])->default('disponible');
            $table->string('asignado_a', 100)->nullable(); // Cambiado a string, sin foreign key
            $table->date('fecha_asignacion')->nullable();
            $table->timestamps();
            
            $table->index('serial');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seriales_activo');
    }
};