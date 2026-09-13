<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componente', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_componente', 50);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('capacidad', 50)->nullable();
            $table->text('especificaciones')->nullable();
            $table->timestamps();

            $table->index('tipo_componente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componente');
    }
};
