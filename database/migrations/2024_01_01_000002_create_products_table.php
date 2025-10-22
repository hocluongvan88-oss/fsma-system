<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('product_name', 200);
            
            // Cột đã sửa: 'category'
            $table->string('category', 100)->nullable();
            
            // <<< CỘT BỊ THIẾU GÂY LỖI MỚI NHẤT >>>
            $table->string('unit_of_measure', 20)->default('kg'); 
            
            $table->boolean('is_ftl')->default(true)->comment('Food Traceability List');
            $table->text('description')->nullable();
            
            // Cột bảo mật: 'organization_id'
            $table->unsignedBigInteger('organization_id')->nullable(); 

            $table->timestamps();
            
            // Indexes
            $table->index('sku');
            $table->index('is_ftl');
            $table->index('organization_id');
            
            // Foreign Key (Nếu có bảng organizations)
            // $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
