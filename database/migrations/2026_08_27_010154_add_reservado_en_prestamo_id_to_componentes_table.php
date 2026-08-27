<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/YYYY_MM_DD_add_reservado_en_prestamo_id_to_componentes_table.php
public function up()
{
    Schema::table('componentes', function (Blueprint $table) {
        $table->foreignId('reservado_en_prestamo_id')
              ->nullable()
              ->constrained('prestamos')
              ->onDelete('set null');
        
        $table->index('reservado_en_prestamo_id');
    });
}

public function down()
{
    Schema::table('componentes', function (Blueprint $table) {
        $table->dropForeign(['reservado_en_prestamo_id']);
        $table->dropColumn('reservado_en_prestamo_id');
    });
}
};
