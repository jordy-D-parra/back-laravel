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
            $table->string('usuario', 50)->unique();
            $table->string('password');
            $table->rememberToken();                          // ← ✅ AGREGADO
            $table->boolean('must_change_password')->default(true);
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('ultimo_login')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('trabajador_id')
                  ->nullable()
                  ->unique()
                  ->constrained('trabajadores')
                  ->onDelete('cascade');

            $table->foreignId('rol_id')
                  ->nullable()
                  ->constrained('roles')
                  ->onDelete('set null');

            // ✅ Consolidado desde: add_foto_perfil_to_usuarios_table
            $table->string('foto_perfil', 255)->nullable()->after('rol_id');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};