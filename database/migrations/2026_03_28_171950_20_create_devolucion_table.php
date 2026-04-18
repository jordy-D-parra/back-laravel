<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devolucion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prestamo')->constrained('prestamo');
            $table->foreignId('id_tecnico')->constrained('usuario');
            $table->date('fecha_devolucion');
            $table->time('hora_devolucion');
            $table->string('tipo_devolucion', 20)->default('parcial');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('id_prestamo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devolucion');
    }
};
