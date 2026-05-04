<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitante')->constrained('usuario');
            $table->string('tipo_solicitante', 20)->default('interno');
            $table->foreignId('institucion_id')->nullable()->constrained('institucion')->onDelete('set null');
            $table->string('oficio_adjunto', 255)->nullable();
            $table->date('fecha_solicitud')->useCurrent();
            $table->date('fecha_requerida')->nullable();
            $table->date('fecha_fin_estimada')->nullable();
            $table->text('justificacion')->nullable();
            $table->string('prioridad', 20)->default('normal');
            $table->string('estado_solicitud', 20)->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('usuario')->onDelete('set null');
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();

            $table->index('estado_solicitud');
            $table->index('id_solicitante');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud');
    }
};
