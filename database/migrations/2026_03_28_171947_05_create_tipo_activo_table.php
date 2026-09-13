<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_activo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('categoria', 50)->nullable();
            $table->boolean('requiere_serial')->default(true);
            $table->boolean('requiere_cantidad')->default(false);
            $table->boolean('requiere_mantenimiento')->default(false);
            $table->integer('vida_util_meses')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_activo');
    }
};
