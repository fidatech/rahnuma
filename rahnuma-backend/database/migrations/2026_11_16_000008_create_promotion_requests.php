<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** An office asks for a paid placement and sends its payment; the administrator approves or rejects it. */
    public function up(): void
    {
        Schema::create('promotion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);                       // sponsored | featured
            $table->unsignedSmallInteger('days');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 5)->default('AFN');
            $table->string('payment_method', 30);             // hesabpay | aurumpay | mobile_money | cash
            $table->string('payment_reference', 120)->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('notes', 500)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('admin_note', 500)->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('promotion_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_requests');
    }
};
