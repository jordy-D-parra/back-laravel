<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('tipo_solicitante', 20)->default('interno'); // interno, externo
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->onDelete('set null');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->foreignId('responsable_id')->nullable()->constrained('responsables')->onDelete('set null');
            $table->string('oficio_adjunto', 255)->nullable();
            $table->date('fecha_solicitud')->useCurrent();
            $table->date('fecha_requerida')->nullable();
            $table->date('fecha_fin_estimada')->nullable();
            $table->text('justificacion')->nullable();
            $table->string('prioridad', 20)->default('normal'); // baja, normal, alta, urgente
            $table->string('estado_solicitud', 20)->default('pendiente'); // pendiente, aprobada, rechazada, cancelada
            $table->text('observaciones')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();

            $table->index('estado_solicitud');
            $table->index('usuario_id');
            $table->index('prioridad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
