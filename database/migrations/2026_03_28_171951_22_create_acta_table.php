<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acta', function (Blueprint $table) {
            $table->id();
            $table->string('numero_acta', 50)->unique();
            $table->string('tipo_referencia', 20);
            $table->bigInteger('id_referencia');
            $table->timestamp('fecha_emision')->useCurrent();
            $table->text('contenido');
            $table->text('firma_digital')->nullable();
            $table->foreignId('generado_por')->nullable()->constrained('usuario')->onDelete('set null');
            $table->timestamps();

            $table->index(['tipo_referencia', 'id_referencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta');
    }
};
