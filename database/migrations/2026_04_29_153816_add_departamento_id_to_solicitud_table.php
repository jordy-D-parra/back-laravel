<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            $table->foreignId('departamento_id')->nullable()->after('institucion_id')->constrained('departamento')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }
};
