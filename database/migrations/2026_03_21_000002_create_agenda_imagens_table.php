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
        Schema::create('agenda_imagens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_config_id');
            $table->string('caminho_imagem');
            $table->integer('ordem')->default(0);
            $table->timestamps();

            $table->foreign('agenda_config_id')->references('id')->on('agenda_configs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_imagens');
    }
};
