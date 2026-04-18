<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_prestamo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('prestamo')->onDelete('cascade');
            $table->foreignId('solicitada_por')->constrained('usuario');
            $table->date('nueva_fecha_devolucion');
            $table->text('motivo')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->foreignId('aprobada_por')->nullable()->constrained('usuario')->onDelete('set null');
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();

            $table->index('prestamo_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_prestamo');
    }
};
