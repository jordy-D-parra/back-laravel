<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cambio_rol_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('usuario');
            $table->foreignId('id_rol_anterior')->constrained('rol');
            $table->foreignId('id_rol_nuevo')->constrained('rol');
            $table->foreignId('id_usuario_cambio')->constrained('usuario');
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->text('justificacion')->nullable();
            $table->timestamps();

            $table->index('id_usuario');
            $table->index('fecha_cambio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambio_rol_historial');
    }
};
