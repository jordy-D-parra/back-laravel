<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_tecnico', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->foreignId('cliente_id')->constrained('clientes_soporte');
            $table->string('tipo_equipo', 20)->default('activo'); // 'activo' o 'periferico'
            $table->foreignId('activo_id')->nullable()->constrained('activo')->onDelete('set null');
            $table->foreignId('periferico_id')->nullable()->constrained('periferico')->onDelete('set null');
            $table->foreignId('tecnico_id')->nullable()->constrained('tecnicos')->onDelete('set null');

            // Fases del servicio
            $table->enum('fase', [
                'diagnostico',
                'reparacion',
                'pruebas',
                'entregado',
                'cerrado'
            ])->default('diagnostico');

            // Fechas clave
            $table->date('fecha_inicio_diagnostico')->nullable();
            $table->date('fecha_fin_diagnostico')->nullable();
            $table->date('fecha_inicio_reparacion')->nullable();
            $table->date('fecha_fin_reparacion')->nullable();
            $table->date('fecha_inicio_pruebas')->nullable();
            $table->date('fecha_fin_pruebas')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->date('fecha_cierre')->nullable();

            // Tiempos calculados (días hábiles)
            $table->integer('dias_diagnostico')->nullable();
            $table->integer('dias_reparacion')->nullable();
            $table->integer('dias_pruebas')->nullable();
            $table->integer('dias_totales')->nullable();

            // Información del servicio
            $table->text('problema_reportado')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('trabajo_realizado')->nullable();
            $table->text('observaciones')->nullable();

            // Control
            $table->enum('estado', ['activo', 'pausado', 'completado', 'cancelado'])->default('activo');
            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal');

            // Auditoría
            $table->foreignId('creado_por')->constrained('usuario');
            $table->foreignId('cerrado_por')->nullable()->constrained('usuario')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('codigo');
            $table->index('fase');
            $table->index('estado');
            $table->index('tecnico_id');
            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_tecnico');
    }
};
