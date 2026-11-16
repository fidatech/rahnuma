<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->index(['agency_id','status','visibility'], 'properties_agency_status_visibility_idx');
            $table->index(['agency_id','property_type','transaction_type'], 'properties_agency_type_deal_idx');
            $table->index(['province_id','property_type','transaction_type','status'], 'properties_marketplace_filter_idx');
            $table->index(['is_sponsored','sponsored_until'], 'properties_sponsor_idx');
            $table->index(['published_at'], 'properties_published_at_idx');
        });
        Schema::table('property_media', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('path');
            $table->index(['property_id','is_cover','sort_order'], 'property_media_cover_idx');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['agency_id','status','next_follow_up_at'], 'leads_pipeline_idx');
        });
        Schema::table('visits', function (Blueprint $table) {
            $table->index(['agency_id','status','scheduled_at'], 'visits_schedule_idx');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['agency_id','status','transaction_type','created_at'], 'transactions_workspace_idx');
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->index(['agency_id','status'], 'commissions_due_idx');
        });
        Schema::table('rent_schedules', function (Blueprint $table) {
            $table->index(['agency_id','status','due_date'], 'rent_schedule_due_idx');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['agency_id','paid_at'], 'payments_timeline_idx');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['agency_id','expense_date'], 'expenses_timeline_idx');
        });
        Schema::table('inquiries', function (Blueprint $table) {
            $table->index(['agency_id','status','created_at'], 'inquiries_inbox_idx');
        });
        Schema::table('owners', function (Blueprint $table) {
            $table->index(['agency_id','name'], 'owners_agency_name_idx');
            $table->index(['agency_id','phone'], 'owners_agency_phone_idx');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['agency_id','name'], 'customers_agency_name_idx');
            $table->index(['agency_id','phone'], 'customers_agency_phone_idx');
        });
        Schema::table('properties', function (Blueprint $table) {
            $table->index(['agency_id','code'], 'properties_agency_code_idx');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['agency_id','reference_no'], 'transactions_agency_reference_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index(['agency_id','is_active','name'], 'users_agency_active_name_idx');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_agency_status_visibility_idx');
            $table->dropIndex('properties_agency_type_deal_idx');
            $table->dropIndex('properties_marketplace_filter_idx');
            $table->dropIndex('properties_sponsor_idx');
            $table->dropIndex('properties_published_at_idx');
        });
        Schema::table('property_media', function (Blueprint $table) { $table->dropIndex('property_media_cover_idx'); $table->dropColumn('thumbnail_path'); });
        Schema::table('leads', fn (Blueprint $table) => $table->dropIndex('leads_pipeline_idx'));
        Schema::table('visits', fn (Blueprint $table) => $table->dropIndex('visits_schedule_idx'));
        Schema::table('transactions', fn (Blueprint $table) => $table->dropIndex('transactions_workspace_idx'));
        Schema::table('commissions', fn (Blueprint $table) => $table->dropIndex('commissions_due_idx'));
        Schema::table('rent_schedules', fn (Blueprint $table) => $table->dropIndex('rent_schedule_due_idx'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex('payments_timeline_idx'));
        Schema::table('expenses', fn (Blueprint $table) => $table->dropIndex('expenses_timeline_idx'));
        Schema::table('inquiries', fn (Blueprint $table) => $table->dropIndex('inquiries_inbox_idx'));
        Schema::table('owners', function (Blueprint $table) { $table->dropIndex('owners_agency_name_idx'); $table->dropIndex('owners_agency_phone_idx'); });
        Schema::table('customers', function (Blueprint $table) { $table->dropIndex('customers_agency_name_idx'); $table->dropIndex('customers_agency_phone_idx'); });
        Schema::table('properties', fn (Blueprint $table) => $table->dropIndex('properties_agency_code_idx'));
        Schema::table('transactions', fn (Blueprint $table) => $table->dropIndex('transactions_agency_reference_idx'));
        Schema::table('users', fn (Blueprint $table) => $table->dropIndex('users_agency_active_name_idx'));
    }
};
