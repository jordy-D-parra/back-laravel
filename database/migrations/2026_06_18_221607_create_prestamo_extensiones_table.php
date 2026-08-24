<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamo_extensiones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prestamo_id')->constrained('prestamos')->onDelete('cascade');
            $table->foreignId('aprobado_por')->nullable()->constrained('usuarios')->onDelete('set null');

            $table->string('tipo', 20)->default('completa'); // completa, parcial
            $table->date('fecha_anterior');
            $table->date('fecha_nueva');
            $table->text('motivo')->nullable();
            $table->text('items_extendidos')->nullable(); // JSON con IDs de detalles (para parcial)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo_extensiones');
    }
};
