<?php
// database/migrations/xxxx_xx_xx_000001_add_categoria_id_to_marcas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marcas', function (Blueprint $table) {
            // Agregar columna categoria_id
            $table->foreignId('categoria_id')->nullable()->after('id')->constrained('categorias')->onDelete('cascade');

            // Eliminar restricción única anterior si existe
            $table->dropUnique(['nombre']);

            // Crear nueva restricción única combinada
            $table->unique(['categoria_id', 'nombre'], 'marcas_categoria_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('marcas', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
            $table->dropUnique('marcas_categoria_nombre_unique');
            $table->unique('nombre');
        });
    }
};
