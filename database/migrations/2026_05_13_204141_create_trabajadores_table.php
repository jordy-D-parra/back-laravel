<?php
// database/migrations/2026_05_13_204141_create_trabajadores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('departamento', 100)->default('Informática');
            $table->string('cargo', 100);
            $table->string('especialidad', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            
            // ✅ AGREGAR CAMPO EMAIL AL TRABAJADOR
            $table->string('email', 100)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trabajadores');
    }
};
