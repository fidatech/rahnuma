<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // settings of the platform that the administrator edits in the panel
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // money in and money out of the platform itself (not of the offices)
        Schema::create('platform_entries', function (Blueprint $table) {
            $table->id();
            $table->string('direction', 3)->index();          // in | out
            $table->string('category', 40)->index();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 5)->default('AFN');
            $table->date('entry_date')->index();
            $table->foreignId('agency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_entries');
        Schema::dropIfExists('settings');
    }
};
