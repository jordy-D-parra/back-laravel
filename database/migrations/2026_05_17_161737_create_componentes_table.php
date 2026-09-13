<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50)->comment('RAM, Disco, Batería, Cargador, Pantalla, Teclado, Mouse, etc.');
            $table->foreignId('modelo_componente_id')->nullable()->constrained('modelo_componente')->onDelete('set null')->comment('Tipo de componente según modelo, NULL si es genérico');
            $table->string('marca', 100)->nullable()->comment('Marca del componente - texto libre');
            $table->string('modelo', 100)->nullable()->comment('Modelo del componente - texto libre');
            $table->string('serial', 100)->nullable()->unique()->comment('Número de serie si aplica');
            $table->string('capacidad', 50)->nullable()->comment('Ej: 8GB, 512GB, 65W, 15.6 pulgadas');
            $table->string('estado', 20)->default('en_bodega')->comment('en_bodega, instalado, prestado, desechado, en_reparacion');
            $table->foreignId('activo_id')->nullable()->constrained('activos')->onDelete('set null')->comment('Activo donde está instalado, NULL si está en bodega');
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('restrict');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->foreignId('responsable_id')->constrained('responsables')->onDelete('restrict');
            $table->string('ubicacion', 100)->nullable()->comment('Ubicación física: Oficina 3B, Bodega Central');
            $table->timestamp('fecha_instalacion')->nullable()->comment('Cuándo se instaló en un activo');
            $table->timestamp('fecha_retiro')->nullable()->comment('Cuándo se retiró de un activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('tipo');
            $table->index('estado');
            $table->index('activo_id');
            $table->index('modelo_componente_id');
            $table->index('institucion_id');
            $table->index('serial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes');
    }
};
