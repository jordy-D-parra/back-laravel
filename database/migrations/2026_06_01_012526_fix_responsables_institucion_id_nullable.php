<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responsables', function (Blueprint $table) {
            // Hacer nullable la columna institucion_id
            $table->foreignId('institucion_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('responsables', function (Blueprint $table) {
            $table->foreignId('institucion_id')->nullable(false)->change();
        });
    }
};
