<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_prestamo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prestamo')->constrained('prestamo')->onDelete('cascade');
            $table->foreignId('id_activo')->nullable()->constrained('activo')->onDelete('cascade');
            $table->foreignId('periferico_id')->nullable()->constrained('periferico')->onDelete('cascade');
            $table->string('tipo_item', 20)->default('activo');
            $table->integer('cantidad')->default(1);
            $table->foreignId('estado_al_prestar')->nullable()->constrained('estatus')->onDelete('set null');
            $table->text('observaciones')->nullable();
            $table->boolean('devuelto')->default(false);
            $table->timestamps();

            $table->index('id_prestamo');
            $table->index('id_activo');
            $table->index('periferico_id');
            $table->index('devuelto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_prestamo');
    }
};
