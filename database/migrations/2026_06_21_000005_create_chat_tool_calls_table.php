<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_tool_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->string('tool', 60);
            $table->json('arguments')->nullable(); // argumentos sanitizados (sem PII)
            $table->string('status', 20)->default('ok'); // ok | invalid | error
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['chat_session_id', 'tool']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_tool_calls');
    }
};
