<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes_soporte', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20)->default('interno');
            $table->string('nombre', 200);
            $table->foreignId('institucion_id')->nullable()->constrained('institucion')->onDelete('set null');
            $table->foreignId('departamento_id')->nullable()->constrained('departamento')->onDelete('set null');
            $table->foreignId('responsable_id')->nullable()->constrained('responsable')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes_soporte');
    }
};
