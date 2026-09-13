<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Usuario que realizó la acción
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('usuario_nombre')->nullable();

            // Información de la acción
            $table->string('accion'); // 'crear', 'editar', 'eliminar', 'cambio_estado', 'login', 'logout', etc.
            $table->string('modulo'); // 'usuarios', 'inventario', 'prestamos', 'solicitudes', 'soporte', etc.
            $table->string('tabla_afectada')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();

            // Datos de la operación
            $table->json('datos_originales')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->text('descripcion')->nullable();

            // IP y User Agent
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['modulo', 'accion']);
            $table->index('registro_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};