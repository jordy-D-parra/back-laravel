<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_solicitud', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitud')->constrained('solicitud')->onDelete('cascade');
            $table->foreignId('id_activo')->nullable()->constrained('activo')->onDelete('cascade');
            $table->foreignId('periferico_id')->nullable()->constrained('periferico')->onDelete('cascade');
            $table->string('tipo_item', 20)->default('activo');
            $table->integer('cantidad_solicitada')->default(1);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('id_solicitud');
            $table->index('id_activo');
            $table->index('periferico_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_solicitud');
    }
};
