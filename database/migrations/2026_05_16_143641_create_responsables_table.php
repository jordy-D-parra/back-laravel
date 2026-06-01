<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsables', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('documento', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('cargo', 100)->nullable();
            $table->boolean('activo')->default(true);

            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('cascade');

            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('institucion_id');
            $table->index('departamento_id');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsables');
    }
};
