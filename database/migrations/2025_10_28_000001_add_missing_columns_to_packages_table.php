<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('packages', 'currency')) {
                $table->string('currency')->default('USD')->after('yearly_selling_price');
            }
            
            if (!Schema::hasColumn('packages', 'is_popular')) {
                $table->boolean('is_popular')->default(false)->after('features');
            }
            
            if (!Schema::hasColumn('packages', 'is_highlighted')) {
                $table->boolean('is_highlighted')->default(false)->after('is_popular');
            }
        });

        DB::table('packages')->whereNull('slug')->orWhere('slug', '')->update([
            'slug' => DB::raw('id')
        ]);

        Schema::table('packages', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'currency', 'is_popular', 'is_highlighted']);
        });
    }
};
