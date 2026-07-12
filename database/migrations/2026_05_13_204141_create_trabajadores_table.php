<?php

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
            $table->timestamps(); // created_at y updated_at para auditoría
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trabajadores');
    }
};
