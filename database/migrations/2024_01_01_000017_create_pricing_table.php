<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing', function (Blueprint $table) {
            $table->id();
            $table->enum('package_id', ['free', 'basic', 'premium', 'enterprise'])->unique();
            $table->string('package_name');
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->decimal('list_price_monthly', 10, 2)->default(0);
            $table->decimal('list_price_yearly', 10, 2)->default(0);
            $table->integer('max_cte_records_monthly')->default(0);
            $table->integer('max_documents')->default(0);
            $table->integer('max_users')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('pricing')->insert([
            [
                'package_id' => 'free',
                'package_name' => 'Free Tier',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'list_price_monthly' => 0,
                'list_price_yearly' => 0,
                'max_cte_records_monthly' => 50,
                'max_documents' => 1,
                'max_users' => 1,
                'is_active' => true,
            ],
            [
                'package_id' => 'basic',
                'package_name' => 'Basic',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'list_price_monthly' => 0,
                'list_price_yearly' => 0,
                'max_cte_records_monthly' => 500,
                'max_documents' => 10,
                'max_users' => 1,
                'is_active' => true,
            ],
            [
                'package_id' => 'premium',
                'package_name' => 'Premium',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'list_price_monthly' => 0,
                'list_price_yearly' => 0,
                'max_cte_records_monthly' => 2500,
                'max_documents' => 0,
                'max_users' => 3,
                'is_active' => true,
            ],
            [
                'package_id' => 'enterprise',
                'package_name' => 'Enterprise',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'list_price_monthly' => 0,
                'list_price_yearly' => 0,
                'max_cte_records_monthly' => 0,
                'max_documents' => 0,
                'max_users' => 0,
                'is_active' => true,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing');
    }
};
