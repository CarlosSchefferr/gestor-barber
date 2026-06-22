<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_config_id')->constrained('agenda_configs')->cascadeOnDelete();
            $table->uuid('session_token')->unique();
            $table->string('status')->default('active'); // active | closed | expired
            // Estado estruturado da conversa (sem PII bruta desnecessária).
            $table->json('state')->nullable();
            $table->string('locale', 10)->default('pt_BR');
            $table->string('ip_hash', 64)->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['agenda_config_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
