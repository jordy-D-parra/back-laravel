<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamo_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prestamo_id')->constrained('prestamos')->onDelete('cascade');

            // Polimórfico: Activo o Componente
            $table->morphs('prestable');

            $table->integer('cantidad')->default(1);
            $table->text('estado_entrega')->nullable();
            $table->text('estado_devolucion')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo_detalles');
    }
};
