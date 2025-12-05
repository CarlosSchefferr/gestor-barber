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
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('valor_meta', 12, 2)->default(0);
            $table->decimal('valor_atual', 12, 2)->default(0);
            $table->date('data_inicio')->nullable();
            $table->date('data_limite')->nullable();
            $table->string('quem_tem_acesso')->default('all'); // all,current,barbers,owners,attendants
            $table->string('tipo')->default('outro');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
