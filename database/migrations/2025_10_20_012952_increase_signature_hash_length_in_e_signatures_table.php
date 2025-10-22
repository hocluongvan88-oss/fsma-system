<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            // Tăng độ dài lên 2000 ký tự hoặc chuyển sang TEXT
            // TEXT là an toàn nhất
            $table->text('signature_hash')->change();
        });
    }

    public function down(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            // Hoàn tác về độ dài cũ (nếu bạn biết độ dài ban đầu, ví dụ: 512)
            // Nếu không chắc, để trống hoặc về một giá trị an toàn
            $table->string('signature_hash', 512)->change();
        });
    }
};
