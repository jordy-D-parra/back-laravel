<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200)->unique();
            $table->text('informacion')->nullable();
            $table->string('representante', 150)->nullable();

            // ✅ Consolidado desde: add_geolocation_to_instituciones_table
            $table->foreignId('estado_id')->nullable()->after('informacion')
                  ->constrained('estados')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->after('estado_id')
                  ->constrained('municipios')->onDelete('set null');
            $table->foreignId('parroquia_id')->nullable()->after('municipio_id')
                  ->constrained('parroquias')->onDelete('set null');

            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituciones');
    }
};