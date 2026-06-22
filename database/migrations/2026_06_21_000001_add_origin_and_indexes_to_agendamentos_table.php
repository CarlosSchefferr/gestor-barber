<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            if (! Schema::hasColumn('agendamentos', 'origin')) {
                $table->string('origin')->nullable()->after('public_token');
            }

            // Índices para busca eficiente de conflito por profissional/período.
            $table->index(['barbeiro_id', 'starts_at'], 'agendamentos_barbeiro_starts_idx');
            $table->index(['barbeiro_id', 'ends_at'], 'agendamentos_barbeiro_ends_idx');
        });
    }

    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropIndex('agendamentos_barbeiro_starts_idx');
            $table->dropIndex('agendamentos_barbeiro_ends_idx');

            if (Schema::hasColumn('agendamentos', 'origin')) {
                $table->dropColumn('origin');
            }
        });
    }
};
