<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('nome_barbearia');
            $table->text('descricao')->nullable();
            $table->string('telefone')->nullable();
            $table->string('endereco')->nullable();
            $table->time('horario_inicio')->default('08:00');
            $table->time('horario_fim')->default('18:00');
            $table->integer('intervalo_slots')->default(30); // em minutos
            $table->json('dias_atendimento')->default('["segunda","terca","quarta","quinta","sexta"]');
            $table->boolean('ativa')->default(true);
            $table->uuid('public_token')->unique();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_configs');
    }
};
