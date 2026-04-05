<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periferico', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('tipo', 50)->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('serial', 100)->nullable()->unique();
            $table->integer('cantidad_total')->default(1);
            $table->integer('cantidad_disponible')->default(1);
            $table->string('ubicacion', 200)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('tipo');
            $table->index('cantidad_disponible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periferico');
    }
};
