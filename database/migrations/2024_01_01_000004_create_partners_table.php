<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('partner_name');
            $table->enum('partner_type', ['supplier', 'customer', 'both']);
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            $table->string('address', 255)->nullable();
            $table->string('gln', 50)->nullable();
            
            $table->unsignedBigInteger('organization_id')->nullable(); 

            $table->timestamps();
            
            // Indexes
            $table->index('partner_type');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
