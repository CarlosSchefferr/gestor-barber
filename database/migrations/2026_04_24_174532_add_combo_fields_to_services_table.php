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
        Schema::table('services', function (Blueprint $table) {
            $table->string('type')->default('service')->after('id');
            $table->integer('return_alert_days')->nullable()->after('commission');
            $table->text('observations')->nullable()->after('return_alert_days');
        });

        Schema::create('combo_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_services');

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['type', 'return_alert_days', 'observations']);
        });
    }
};
