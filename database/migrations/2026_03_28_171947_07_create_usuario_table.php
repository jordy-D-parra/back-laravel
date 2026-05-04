<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 20)->unique();
            $table->string('password');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('departamento', 100)->nullable();
            $table->string('cargo', 100);
            $table->foreignId('departamento_id')->nullable()->constrained('departamento')->onDelete('set null');
            $table->text('pregunta_seguridad_1');
            $table->string('respuesta_1', 255);
            $table->text('pregunta_seguridad_2');
            $table->string('respuesta_2', 255);
            $table->foreignId('id_rol')->nullable()->constrained('rol')->onDelete('set null');
            $table->string('estado_usuario', 20)->default('pendiente');
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('usuario')->onDelete('set null');
            $table->timestamp('ultimo_login')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('cedula');
            $table->index('estado_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
