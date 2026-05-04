<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitud')->nullable()->constrained('solicitud')->onDelete('set null');
            $table->foreignId('id_tecnico')->constrained('usuario');
            $table->foreignId('id_responsable')->constrained('responsable');
            $table->date('fecha_prestamo');
            $table->time('hora_prestamo');
            $table->date('fecha_retorno_estimada')->nullable();
            $table->date('fecha_retorno_real')->nullable();
            $table->string('tipo_prestamo', 20)->default('interno');
            $table->string('estado_prestamo', 20)->default('activo');
            $table->boolean('pendiente_completar')->default(false);
            $table->text('observaciones')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('usuario')->onDelete('set null');
            $table->timestamps();

            $table->index('estado_prestamo');
            $table->index('id_tecnico');
            $table->index('id_responsable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo');
    }
};
