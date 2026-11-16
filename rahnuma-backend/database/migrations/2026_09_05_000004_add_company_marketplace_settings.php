<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('cover_path')->nullable()->after('logo_path');
            $table->string('tagline')->nullable()->after('cover_path');
            $table->string('secondary_phone')->nullable()->after('phone');
            $table->string('website')->nullable()->after('email');
            $table->string('facebook_url')->nullable()->after('website');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('telegram_url')->nullable()->after('instagram_url');
            $table->string('office_hours')->nullable()->after('address');
            $table->json('languages')->nullable()->after('office_hours');
            $table->string('default_currency', 5)->default('AFN')->after('languages');
            $table->string('default_locale', 5)->default('fa')->after('default_currency');
            $table->string('timezone')->default('Asia/Kabul')->after('default_locale');
            $table->boolean('marketplace_enabled')->default(true)->after('timezone');
            $table->boolean('marketplace_show_phone')->default(true)->after('marketplace_enabled');
            $table->boolean('marketplace_show_whatsapp')->default(true)->after('marketplace_show_phone');
            $table->boolean('marketplace_show_address')->default(true)->after('marketplace_show_whatsapp');
            $table->boolean('marketplace_show_license')->default(true)->after('marketplace_show_address');
            $table->boolean('marketplace_show_exact_location')->default(false)->after('marketplace_show_license');
            $table->boolean('marketplace_featured')->default(false)->after('marketplace_show_exact_location');
            $table->timestamp('profile_completed_at')->nullable()->after('marketplace_featured');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('job_title')->nullable()->after('phone');
            $table->string('avatar_path')->nullable()->after('job_title');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('is_sponsored')->default(false)->after('featured_at');
            $table->timestamp('sponsored_until')->nullable()->after('is_sponsored');
            $table->unsignedBigInteger('views_count')->default(0)->after('sponsored_until');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['is_sponsored', 'sponsored_until', 'views_count']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'job_title', 'avatar_path']);
        });
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn([
                'cover_path','tagline','secondary_phone','website','facebook_url','instagram_url','telegram_url',
                'office_hours','languages','default_currency','default_locale','timezone','marketplace_enabled',
                'marketplace_show_phone','marketplace_show_whatsapp','marketplace_show_address','marketplace_show_license',
                'marketplace_show_exact_location','marketplace_featured','profile_completed_at'
            ]);
        });
    }
};
