<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichas_soporte_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ficha_soporte_id')->constrained('fichas_soporte')->onDelete('cascade');
            $table->foreignId('componente_id')->nullable()->constrained('componentes')->onDelete('set null');
            $table->string('componente_nombre', 100)->nullable();
            $table->enum('estado_ingreso', ['funcionando', 'dañado', 'desgastado', 'no_aplica'])->default('funcionando');
            $table->enum('estado_salida', ['funcionando', 'dañado', 'reemplazado', 'reparado', 'no_aplica'])->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('ficha_soporte_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichas_soporte_detalle');
    }
};