<?php
// database/migrations/2026_05_13_204142_create_usuarios_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('usuario', 50)->unique();            
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('ultimo_login')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // FK única hacia trabajadores (1:1)
            $table->foreignId('trabajador_id')
                  ->nullable()
                  ->unique()
                  ->constrained('trabajadores')
                  ->onDelete('cascade');

            // FK hacia roles
            $table->foreignId('rol_id')
                  ->nullable()
                  ->constrained('roles')
                  ->onDelete('set null');

            // Índices
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};