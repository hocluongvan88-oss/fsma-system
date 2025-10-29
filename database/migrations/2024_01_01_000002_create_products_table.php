<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Đây là class Migration chính xác, không trùng tên với DatabaseSeeder
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại đến Organization - FIX LỖI 1364: 
            // Thay thế foreignId bằng unsignedBigInteger và foreign() thủ công
            // để đảm bảo khả năng tùy chỉnh NULL/DEFAULT được áp dụng.
            // Dù Seeder đã cung cấp giá trị 2, vẫn nên dùng nullable() để phòng MySQL Strict Mode.
            $table->unsignedBigInteger('organization_id')->nullable(); 
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            $table->string('sku')->unique();
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->boolean('is_ftl')->default(true); // Food Traceability List item
            $table->string('category')->nullable();
            $table->string('unit_of_measure')->default('kg');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
