<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estatus', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 50)->unique();
            $table->string('color_badge', 20)->default('secondary');
            $table->boolean('permite_prestamo')->default(true);
            $table->boolean('permite_solicitud')->default(true);
            $table->boolean('es_terminal')->default(false)->comment('Si es true, no se puede cambiar a otro estado');
            $table->timestamps();

            $table->index('descripcion');
            $table->index('permite_prestamo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estatus');
    }
};