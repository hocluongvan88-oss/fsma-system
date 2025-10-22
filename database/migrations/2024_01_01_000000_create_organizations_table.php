<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cần chạy TRƯỚC bảng 'locations' để Foreign Key hoạt động.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Thêm cột cần thiết cho việc tuân thủ quy định (GACC/FDA)
            $table->string('registration_number')->nullable()->comment('Mã đăng ký GACC/FDA');
            
            $table->timestamps();
            $table->softDeletes(); // Nếu bạn sử dụng soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
