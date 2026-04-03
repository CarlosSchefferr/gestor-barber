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
        Schema::table('clientes', function (Blueprint $table) {
            // Novos campos de dados do cliente
            $table->date('data_nascimento')->nullable()->after('nome');
            $table->string('cep', 10)->nullable()->after('telefone');
            $table->string('bairro', 100)->nullable()->after('cep');
            $table->string('foto')->nullable()->after('bairro');

            // Campos de auditoria
            $table->foreignId('created_by')->nullable()->after('foto')->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'data_nascimento',
                'cep',
                'bairro',
                'foto',
                'created_by',
                'updated_by'
            ]);
        });
    }
};
