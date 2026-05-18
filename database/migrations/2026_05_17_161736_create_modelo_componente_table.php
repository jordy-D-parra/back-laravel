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
            $table->foreignId('componente_id')->constrained('componentes')->onDelete('cascade');
            $table->integer('cantidad')->default(1)->comment('Cantidad requerida de este componente');
            $table->boolean('requerido')->default(true)->comment('¿Es obligatorio para este modelo?');
            $table->timestamps();

            $table->unique(['modelo_id', 'componente_id'], 'modelo_componente_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelo_componente');
    }
};
