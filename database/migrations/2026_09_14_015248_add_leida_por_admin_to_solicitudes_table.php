<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitudes', 'leida_por_admin')) {
                $table->boolean('leida_por_admin')->default(false)->after('estado_solicitud');
                $table->index('leida_por_admin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes', 'leida_por_admin')) {
                $table->dropIndex(['leida_por_admin']);
                $table->dropColumn('leida_por_admin');
            }
        });
    }
};