<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('abbreviation', 20)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('product_units')->insert([
            ['name' => 'Unidade', 'abbreviation' => 'un', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mililitro', 'abbreviation' => 'ml', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Litro', 'abbreviation' => 'l', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grama', 'abbreviation' => 'g', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quilograma', 'abbreviation' => 'kg', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_unit_id')->nullable()->after('description')->constrained('product_units')->nullOnDelete();
            $table->string('brand')->default('Sem marca')->after('description')->index();
            $table->string('registration_type', 20)->default('product')->after('product_unit_id')->index();
            $table->string('usage_type', 30)->default('barbershop')->after('registration_type')->index();
            $table->decimal('commission_percentage', 8, 2)->default(0)->after('price');
            $table->integer('minimum_stock')->default(0)->after('quantity');
            $table->string('image_path')->nullable()->after('minimum_stock');
            $table->string('barcode')->nullable()->after('image_path')->index();
            $table->boolean('active')->default(true)->after('barcode')->index();
        });

        $defaultUnitId = DB::table('product_units')->where('name', 'Unidade')->value('id');

        DB::table('products')->whereNull('brand')->update(['brand' => 'Sem marca']);
        DB::table('products')->whereNull('product_unit_id')->update(['product_unit_id' => $defaultUnitId]);

        Schema::create('product_combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['combo_product_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('product_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type', 20)->index();
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('origin_type', 40)->default('stock_adjustment')->index();
            $table->unsignedBigInteger('origin_id')->nullable()->index();
            $table->string('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });

        Schema::create('product_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type', 20)->index();
            $table->decimal('value', 10, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
        Schema::dropIfExists('product_stock_movements');
        Schema::dropIfExists('product_combo_items');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_unit_id');
            $table->dropColumn([
                'brand',
                'registration_type',
                'usage_type',
                'commission_percentage',
                'minimum_stock',
                'image_path',
                'barcode',
                'active',
            ]);
        });

        Schema::dropIfExists('product_units');
    }
};
