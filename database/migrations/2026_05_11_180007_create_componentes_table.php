<?php
// database/migrations/xxxx_xx_xx_000004_create_componentes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('tipo', 50); // Batería, Cargador, Cable, etc.
            $table->string('serial', 100)->nullable()->unique();
            $table->enum('estado', ['disponible', 'asignado', 'mantenimiento'])->default('disponible');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('tipo');
            $table->index('estado');
            $table->index('serial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes');
    }
};
