<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->onDelete('cascade');
            $table->foreignId('activo_id')->nullable()->constrained('activos')->onDelete('cascade');
            $table->foreignId('componente_id')->nullable()->constrained('componentes')->onDelete('cascade');
            $table->string('tipo_item', 20)->default('activo'); // activo, componente
            $table->string('descripcion_personalizada', 255)->nullable(); // Para items no inventariados
            $table->integer('cantidad_solicitada')->default(1);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('solicitud_id');
            $table->index('activo_id');
            $table->index('componente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_solicitudes');
    }
};
