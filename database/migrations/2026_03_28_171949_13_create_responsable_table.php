<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsable', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('departamento', 150)->nullable();
            $table->string('tipo', 20)->default('interno');
            $table->foreignId('institucion_id')->nullable()->constrained('institucion')->onDelete('set null');
            $table->string('documento', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->timestamps();

            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsable');
    }
};
