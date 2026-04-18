<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notificacion_sistema', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('tipo', 50);
            $table->string('titulo');
            $table->text('mensaje');
            $table->json('datos_extra')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_envio')->useCurrent();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('cascade');
            $table->index(['usuario_id', 'leida']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notificacion_sistema');
    }
};
