<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->nullable()->constrained('chat_sessions')->nullOnDelete();
            $table->foreignId('agenda_config_id')->nullable()->constrained('agenda_configs')->nullOnDelete();
            $table->date('usage_date');
            $table->string('model', 60)->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('cached_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('tool_calls')->default(0);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('status', 30)->default('ok');
            $table->string('response_id', 80)->nullable();
            $table->timestamps();

            $table->index(['agenda_config_id', 'usage_date']);
            $table->index('usage_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_usages');
    }
};
