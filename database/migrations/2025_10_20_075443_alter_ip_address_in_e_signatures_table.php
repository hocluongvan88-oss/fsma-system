<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            $table->text('ip_address')->change(); // đổi từ VARCHAR sang TEXT
        });
    }

    public function down(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            $table->string('ip_address', 45)->change(); // quay lại nếu rollback
        });
    }
};
