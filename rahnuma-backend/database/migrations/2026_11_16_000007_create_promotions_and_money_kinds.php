<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // the kinds of money in and money out; the administrator adds, renames and deletes them
        Schema::create('platform_categories', function (Blueprint $table) {
            $table->id();
            $table->string('direction', 3)->index();      // in | out
            $table->string('name', 60);
            $table->boolean('is_system')->default(false); // used by the system itself: cannot be deleted
            $table->timestamps();
            $table->unique(['direction', 'name']);
        });

        $now = now();
        $rows = [];
        foreach (['subscription' => true, 'sponsorship' => true, 'featuring' => true, 'setup_fee' => false, 'other_income' => false] as $name => $system) {
            $rows[] = ['direction' => 'in', 'name' => $name, 'is_system' => $system, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach (['hosting', 'salary', 'marketing', 'office', 'tax', 'other_expense'] as $name) {
            $rows[] = ['direction' => 'out', 'name' => $name, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('platform_categories')->insert($rows);

        // a featured placement ends on a day, like a sponsored one
        Schema::table('properties', function (Blueprint $table) {
            $table->timestamp('featured_until')->nullable()->after('featured_at');
        });
        Schema::table('agencies', function (Blueprint $table) {
            $table->timestamp('marketplace_featured_until')->nullable();
        });

        // every paid placement the administrator gave: what, for whom, how long and for how much
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20)->index();          // sponsored | featured | featured_office
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->index();
            $table->timestamp('stopped_at')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency', 5)->default('AFN');
            $table->foreignId('platform_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('notes', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::table('agencies', fn (Blueprint $table) => $table->dropColumn('marketplace_featured_until'));
        Schema::table('properties', fn (Blueprint $table) => $table->dropColumn('featured_until'));
        Schema::dropIfExists('platform_categories');
    }
};
