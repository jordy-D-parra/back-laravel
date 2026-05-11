<?php
// database/migrations/2026_05_11_xxxxxx_make_marca_modelo_nullable_in_activo.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            $table->string('marca_modelo', 200)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activo', function (Blueprint $table) {
            $table->string('marca_modelo', 200)->nullable(false)->change();
        });
    }
};
