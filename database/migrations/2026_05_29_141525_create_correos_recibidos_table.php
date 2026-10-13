<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correos_recibidos', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('subject')->nullable();
            $table->string('tipo', 20)->default('solicitud');
            $table->text('body_text')->nullable();
            $table->text('body_html')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->boolean('leido')->default(false);
            $table->boolean('procesado')->default(false);

            $table->foreignId('solicitud_id')
                  ->nullable()
                  ->constrained('solicitudes')
                  ->onDelete('set null');

            // ✅ Columna simple (sin FK) para evitar dependencia circular con fichas_soporte.
            //    La FK real vive en fichas_soporte.correo_id
            $table->unsignedBigInteger('ficha_soporte_id')->nullable();
            $table->index('ficha_soporte_id');

            $table->json('datos_extraidos')->nullable();
            $table->date('fecha_requerida_entrega')->nullable();

            $table->foreignId('usuario_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->onDelete('set null');

            $table->timestamps();

            $table->index('leido');
            $table->index('procesado');
            $table->index('from_email');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correos_recibidos');
    }
};