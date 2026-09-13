<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permiso_id')->constrained('permisos')->onDelete('cascade');
            $table->foreignId('rol_id')->constrained('roles')->onDelete('cascade');
            $table->unique(['permiso_id', 'rol_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_rol');
    }
};
