    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            // Thêm cột 'deleted_at'
            Schema::table('organizations', function (Blueprint $table) {
                $table->softDeletes(); 
            });
        }

        public function down(): void
        {
            // Loại bỏ cột 'deleted_at'
            Schema::table('organizations', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    };
    
