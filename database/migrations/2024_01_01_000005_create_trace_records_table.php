<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trace_records', function (Blueprint $table) {
            $table->id();
            $table->string('tlc')->unique();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 20)->default('kg');
            $table->string('lot_code')->nullable();
            $table->date('harvest_date')->nullable();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'consumed', 'shipped', 'destroyed'])->default('active');

            // <<< CỘT BỊ THIẾU GÂY LỖI: 'path' >>>
            // Đây là cột được truy vấn trong lỗi cuối cùng
            $table->text('path')->nullable(); 

            // CỘT BẢO MẬT: organization_id (Gây ra lỗi WHERE)
            $table->unsignedBigInteger('organization_id')->nullable(); 

            $table->string('materialized_path', 500)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('tlc');
            $table->index('lot_code');
            $table->index('status');
            $table->index('materialized_path');
            $table->index('organization_id'); // Thêm index cho khóa ngoại
            
            // Foreign Key (Nếu có bảng organizations)
            // $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trace_records');
    }
};
