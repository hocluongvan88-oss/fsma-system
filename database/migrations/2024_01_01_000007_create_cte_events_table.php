<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cte_events', function (Blueprint $table) {
            $table->id();
            $table->enum('event_type', ['receiving', 'transformation', 'shipping']);
            $table->foreignId('trace_record_id')->constrained()->onDelete('cascade');
            $table->timestamp('event_date');
            $table->foreignId('location_id')->constrained()->onDelete('restrict');
            $table->foreignId('partner_id')->nullable()->constrained()->onDelete('set null');
            $table->json('input_tlcs')->nullable()->comment('For transformation events');
            $table->string('reference_doc', 100)->nullable()->comment('PO, Invoice, BOL number');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            // Tạo index cho các cột cần thiết (chỉ tạo index event_type ở đây 1 lần thôi)
            $table->index('event_type');
            $table->index('event_date');
            $table->index('trace_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cte_events');
    }
};
