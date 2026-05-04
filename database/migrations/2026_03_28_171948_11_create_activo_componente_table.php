<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activo_componente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activo')->onDelete('cascade');
            $table->foreignId('componente_id')->constrained('componente')->onDelete('cascade');
            $table->integer('cantidad')->default(1);
            $table->timestamp('fecha_instalacion')->useCurrent();
            $table->timestamp('fecha_retiro')->nullable();
            $table->text('observaciones')->nullable();

            $table->index('activo_id');
            $table->index('componente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_componente');
    }
};
