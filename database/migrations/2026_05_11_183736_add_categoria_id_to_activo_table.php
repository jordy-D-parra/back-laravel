<?php
// database/migrations/xxxx_xx_xx_000003_add_categoria_id_to_activo_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            $table->foreignId('id_categoria')->nullable()->after('id_tipo_activo')->constrained('categorias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            $table->dropForeign(['id_categoria']);
            $table->dropColumn('id_categoria');
        });
    }
};
