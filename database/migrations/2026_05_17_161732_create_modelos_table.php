<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->text('especificaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['marca_id', 'nombre']);
            $table->index('nombre');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelos');
    }
};
