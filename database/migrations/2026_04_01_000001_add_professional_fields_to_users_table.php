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
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf')->nullable()->unique()->after('phone');
            $table->string('professional_name')->nullable()->after('cpf');
            $table->enum('gender', ['M', 'F', 'O'])->nullable()->after('professional_name');
            $table->decimal('salary', 10, 2)->nullable()->after('gender');
            $table->string('cargo')->nullable()->after('salary');
            $table->boolean('usuario_admin')->default(0)->after('cargo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropColumn(['cpf', 'professional_name', 'gender', 'salary', 'cargo', 'usuario_admin']);
        });
    }
};
