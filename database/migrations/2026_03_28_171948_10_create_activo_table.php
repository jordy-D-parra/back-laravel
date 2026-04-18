<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo', function (Blueprint $table) {
            $table->id();
            $table->string('serial', 100)->unique();
            $table->string('tipo_equipo', 20)->default('principal');
            $table->string('marca_modelo', 200);
            $table->string('capacidad', 50)->nullable(); // Campo movido aquí para mejor orden
            $table->foreignId('id_estatus')->constrained('estatus');
            $table->foreignId('id_tipo_activo')->constrained('tipo_activo');
            $table->integer('cantidad')->default(1);
            $table->string('ubicacion', 100)->nullable();
            $table->date('disponible_desde')->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('valor_compra', 12, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->text('detalles_tecnicos')->nullable();
            $table->timestamps();

            $table->index('tipo_equipo');
            $table->index('capacidad'); // Índice para búsquedas por capacidad
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo');
    }
};