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
            $table->foreignId('id_estatus')->constrained('estatus');
            $table->foreignId('id_tipo_activo')->constrained('tipo_activo');
            $table->integer('cantidad')->default(1);
            $table->string('ubicacion', 100)->nullable();
            
            // 📅 Fechas clave
            $table->date('disponible_desde')->nullable();
            $table->date('fecha_adquisicion')->nullable();
            
            // ⏱️ Vida útil del equipo
            $table->integer('vida_util_anos')->nullable()->comment('Años de vida útil estimada del equipo');
            $table->date('fecha_fin_garantia')->nullable();
            
            // 🔧 Especificaciones técnicas dinámicas (JSON)
            $table->json('especificaciones_tecnicas')->nullable()->comment('Campos dinámicos según categoría');
            
            // 📝 Observaciones generales
            $table->text('observaciones')->nullable();
            
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('tipo_equipo');
            $table->index('fecha_adquisicion');
            $table->index('vida_util_anos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo');
    }
};