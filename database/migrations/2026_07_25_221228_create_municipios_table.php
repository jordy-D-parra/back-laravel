<?php
// database/migrations/2026_07_25_221228_create_municipios_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_id')->constrained('estados')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['estado_id', 'nombre']);
            $table->index('nombre');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
