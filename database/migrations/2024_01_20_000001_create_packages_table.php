<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->string('id')->primary(); // 'free', 'basic', 'premium', 'enterprise'
            $table->string('name'); // Display name
            $table->text('description')->nullable();
            
            // Limits
            $table->integer('max_cte_records_monthly')->default(0); // 0 = unlimited
            $table->integer('max_documents')->default(0); // 0 = unlimited
            $table->integer('max_users')->default(1);
            
            // Pricing - Monthly
            $table->decimal('monthly_list_price', 10, 2)->nullable(); // Giá niêm yết tháng
            $table->decimal('monthly_selling_price', 10, 2)->nullable(); // Giá bán tháng
            
            // Pricing - Yearly
            $table->decimal('yearly_list_price', 10, 2)->nullable(); // Giá niêm yết năm
            $table->decimal('yearly_selling_price', 10, 2)->nullable(); // Giá bán năm
            
            // Promotion
            $table->boolean('show_promotion')->default(false);
            $table->string('promotion_text')->nullable(); // e.g., "Tiết kiệm 20%"
            
            // Features (JSON array)
            $table->json('features')->nullable();
            
            // Display
            $table->boolean('is_visible')->default(true); // Show in pricing table
            $table->boolean('is_selectable')->default(true); // Can be selected by users
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
