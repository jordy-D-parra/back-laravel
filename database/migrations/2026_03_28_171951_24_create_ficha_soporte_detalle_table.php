<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_soporte_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ficha_soporte_id')->constrained('ficha_soporte')->onDelete('cascade');
            $table->foreignId('componente_id')->constrained('componente');
            $table->string('estado_ingreso', 50)->nullable();
            $table->string('estado_salida', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('ficha_soporte_id');
            $table->index('componente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_soporte_detalle');
    }
};
