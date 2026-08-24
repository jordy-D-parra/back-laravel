<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('informacion')->nullable();
            $table->string('representante', 150)->nullable();
            $table->string('ubicacion', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

$table->foreignId('institucion_id')->nullable()->constrained('instituciones')->onDelete('set null');

            $table->unique(['nombre', 'institucion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
