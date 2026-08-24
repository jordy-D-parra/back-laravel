<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('tipo_prestamo', 20)->default('equipo'); // equipo, componente, mixto
            $table->string('estado', 20)->default('pendiente'); // pendiente, aprobado, entregado, devuelto, vencido, cancelado, extendido
            $table->text('observaciones')->nullable();
            $table->text('condiciones')->nullable();

            // Relaciones
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes')->onDelete('set null');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->onDelete('set null');
            $table->foreignId('responsable_receptor_id')->nullable()->constrained('responsables')->onDelete('set null');
            $table->foreignId('responsable_emisor_id')->nullable()->constrained('responsables')->onDelete('set null');
            $table->foreignId('usuario_registra_id')->nullable()->constrained('usuarios')->onDelete('set null');

            // Fechas
            $table->date('fecha_prestamo');
            $table->date('fecha_devolucion_esperada');
            $table->date('fecha_devolucion_real')->nullable();

            // Control de extensiones
            $table->boolean('tiene_extension')->default(false);
            $table->integer('total_extensiones')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('codigo');
            $table->index('estado');
            $table->index('fecha_prestamo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
