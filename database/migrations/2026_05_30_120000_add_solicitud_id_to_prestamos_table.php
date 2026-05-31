<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->foreignId('solicitud_id')
                ->nullable()
                ->after('id')
                ->constrained('solicitudes')
                ->nullOnDelete();
            $table->index('solicitud_id');
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropForeign(['solicitud_id']);
            $table->dropColumn('solicitud_id');
        });
    }
};
