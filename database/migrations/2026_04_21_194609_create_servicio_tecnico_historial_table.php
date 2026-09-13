<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_tecnico_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_tecnico_id')->constrained('servicio_tecnico')->onDelete('cascade');
            $table->string('fase_anterior', 30)->nullable();
            $table->string('fase_nueva', 30);
            $table->string('campo_modificado', 50)->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->text('comentario')->nullable();
            $table->foreignId('realizado_por')->constrained('usuario');
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->timestamps();

            $table->index('servicio_tecnico_id');
            $table->index('fase_nueva');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_tecnico_historial');
    }
};
