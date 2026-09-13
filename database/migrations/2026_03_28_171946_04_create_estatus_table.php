<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estatus', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 50);
            $table->string('color_badge', 20)->default('secondary');
            $table->boolean('permite_prestamo')->default(true);
            $table->boolean('permite_solicitud')->default(true);
            $table->boolean('es_terminal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estatus');
    }
};
