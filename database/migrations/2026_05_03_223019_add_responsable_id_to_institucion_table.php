<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institucion', function (Blueprint $table) {
            if (!Schema::hasColumn('institucion', 'responsable_id')) {
                $table->foreignId('responsable_id')
                    ->nullable()
                    ->after('activo')
                    ->constrained('responsable')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('institucion', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropColumn('responsable_id');
        });
    }
};
