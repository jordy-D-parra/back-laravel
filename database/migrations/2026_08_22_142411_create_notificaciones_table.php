<?php
// database/migrations/2026_08_22_000001_create_notificaciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->constrained('usuarios')
                  ->onDelete('cascade');
            
            $table->string('tipo', 50)->default('sistema');
            $table->string('titulo', 200);
            $table->text('mensaje');
            $table->string('url', 500)->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_envio')->useCurrent();
            $table->timestamps();

            // Índices para búsqueda rápida
            $table->index(['usuario_id', 'leida']);
            $table->index('fecha_envio');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};