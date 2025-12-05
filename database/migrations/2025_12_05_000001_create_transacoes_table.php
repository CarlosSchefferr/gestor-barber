<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao')->nullable();
            $table->string('tipo'); // 'receita' or 'despesa'
            $table->decimal('valor', 12, 2)->default(0);
            $table->date('data')->nullable();
            $table->string('status')->default('Confirmado');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacoes');
    }
};
