<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('question_number'); // 1 o 2
            $table->string('answer_hash'); // respuesta encriptada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_answers');
    }
};
