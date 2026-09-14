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
            $table->text('body_text')->nullable();
            $table->text('body_html')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->boolean('leido')->default(false);
            $table->boolean('procesado')->default(false);
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes')->onDelete('set null');
            $table->json('datos_extraidos')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();

            $table->index('leido');
            $table->index('procesado');
            $table->index('from_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correos_recibidos');
    }
};