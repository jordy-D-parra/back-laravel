<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modelo_componente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modelo_id')->constrained('modelos')->onDelete('cascade');
            $table->string('tipo', 50);
            $table->string('descripcion', 200);
            $table->string('capacidad', 50)->nullable();
            $table->boolean('requerido')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelo_componente');
    }
};
