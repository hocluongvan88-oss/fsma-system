<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('certificate_id')->unique();
            $table->text('certificate_pem');
            $table->text('public_key');
            $table->text('private_key_encrypted')->nullable()->comment('Encrypted private key');
            $table->string('issuer')->comment('Certificate issuer');
            $table->string('subject')->comment('Certificate subject');
            $table->string('serial_number')->unique();
            $table->timestamp('issued_at');
            $table->timestamp('expires_at');
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->string('signature_algorithm')->default('sha256WithRSAEncryption');
            $table->integer('key_size')->default(2048)->comment('RSA key size in bits');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('certificate_id');
            $table->index('is_revoked');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_certificates');
    }
};
