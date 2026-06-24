<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_configs', function (Blueprint $table) {
            if (! Schema::hasColumn('agenda_configs', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('nome_barbearia');
            }
            if (! Schema::hasColumn('agenda_configs', 'logo')) {
                $table->string('logo')->nullable()->after('descricao');
            }
        });

        // Backfill de slugs a partir do nome da barbearia, garantindo unicidade.
        $usados = [];
        foreach (DB::table('agenda_configs')->select('id', 'nome_barbearia', 'public_token')->get() as $cfg) {
            $base = Str::slug($cfg->nome_barbearia ?: 'barbearia');
            if ($base === '') {
                $base = 'barbearia';
            }

            $slug = $base;
            $i = 2;
            while (in_array($slug, $usados, true) || DB::table('agenda_configs')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i;
                $i++;
            }

            $usados[] = $slug;
            DB::table('agenda_configs')->where('id', $cfg->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('agenda_configs', function (Blueprint $table) {
            if (Schema::hasColumn('agenda_configs', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('agenda_configs', 'logo')) {
                $table->dropColumn('logo');
            }
        });
    }
};
