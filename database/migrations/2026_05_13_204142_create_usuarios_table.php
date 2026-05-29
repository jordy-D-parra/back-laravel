<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('usuario', 50)->unique();  // En lugar de email
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('ultimo_login')->nullable();
            $table->timestamp('created_at')->useCurrent(); // Auditoría: creación
            $table->timestamp('updated_at')->useCurrent();

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

            // Índices adicionales
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
