<?php
// database/migrations/YYYY_MM_DD_add_marca_id_to_categorias_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('marca_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('marcas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropForeign(['marca_id']);
            $table->dropColumn('marca_id');
        });
    }
};