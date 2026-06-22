<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_booking_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->foreignId('agenda_config_id')->constrained('agenda_configs')->cascadeOnDelete();
            $table->uuid('token')->unique();

            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('duration_minutes');

            // Dados pessoais coletados em campos seguros do sistema. Necessários
            // para criar o agendamento. Nunca enviados à OpenAI.
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('observacoes')->nullable();

            $table->string('status')->default('pending'); // pending | confirmed | expired | cancelled
            $table->string('idempotency_key', 80)->nullable()->unique();
            $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['chat_session_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_booking_proposals');
    }
};
