<?php
// database/migrations/xxxx_xx_xx_000005_create_modelo_componente_table.php

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
            $table->foreignId('componente_id')->constrained('componentes')->onDelete('cascade');
            $table->integer('cantidad')->default(1);
            $table->boolean('requerido')->default(true);
            $table->timestamps();

            $table->unique(['modelo_id', 'componente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelo_componente');
    }
};
