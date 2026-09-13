<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('usuario')->onDelete('set null');
            $table->string('user_name', 200)->nullable();
            $table->string('user_role', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('operation', 20);
            $table->string('table_name', 100);
            $table->bigInteger('record_id')->nullable();
            $table->string('field_name', 100)->nullable();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('description')->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('request_url', 500)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['table_name', 'record_id']);
            $table->index('operation');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
