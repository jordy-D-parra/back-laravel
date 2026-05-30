<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('responsable_id');
            $table->unsignedBigInteger('activo_id');
            $table->date('fecha_salida')->nullable();
            $table->date('fecha_devolucion')->nullable();
            $table->enum('estado', ['pendiente', 'entregado', 'vencido', 'devuelto']);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Constraints (optional but recommended)
            $table->foreign('responsable_id')->references('id')->on('responsables')->onDelete('restrict');
            $table->foreign('activo_id')->references('id')->on('activos')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
