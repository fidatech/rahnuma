<?php

namespace Database\Seeders;

use App\Models\{Agency, Area, District, Plan, Province, User};
use App\Services\AccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [];
        foreach ([['Basic', 'basic', 1000, 10000, 2, 100], ['Professional', 'professional', 2500, 25000, 7, 1000], ['Business', 'business', 5000, 50000, 25, null]] as [$n, $c, $m, $a, $u, $p]) $plans[$c] = Plan::firstOrCreate(['code' => $c], ['name' => $n, 'monthly_price' => $m, 'annual_price' => $a, 'user_limit' => $u, 'property_limit' => $p, 'features' => ['marketplace' => true, 'accounting' => true, 'mobile' => true], 'is_active' => 1]);
        $kabul = Province::firstOrCreate(['code' => 'KBL'], ['name_en' => 'Kabul', 'name_fa' => 'کابل', 'name_ps' => 'کابل', 'latitude' => 34.5553, 'longitude' => 69.2075]);
        $d10 = District::firstOrCreate(['province_id' => $kabul->id, 'code' => 'KBL-N10'], ['name_en' => 'District 10', 'name_fa' => 'ناحیه ۱۰', 'name_ps' => '۱۰ ناحیه']);
        Area::firstOrCreate(['district_id' => $d10->id, 'name_en' => 'Shahr-e-Naw'], ['name_fa' => 'شهرنو', 'name_ps' => 'شهر نو', 'latitude' => 34.5378, 'longitude' => 69.1723]);
        foreach ([['Herat', 'هرات', 'هرات', 'HRT'], ['Balkh', 'بلخ', 'بلخ', 'BLK'], ['Kandahar', 'قندهار', 'کندهار', 'KDH'], ['Nangarhar', 'ننگرهار', 'ننګرهار', 'NGR']] as [$en, $fa, $ps, $c]) Province::firstOrCreate(['code' => $c], ['name_en' => $en, 'name_fa' => $fa, 'name_ps' => $ps]);
        User::firstOrCreate(['email' => 'admin@rahnuma.af'], ['name' => 'Platform Admin', 'password' => Hash::make('ChangeMe123!'), 'role' => 'platform_admin', 'locale' => 'en', 'is_active' => 1]);
        User::firstOrCreate(['email' => 'government@rahnuma.af'], ['name' => 'Government Demo Admin', 'password' => Hash::make('ChangeMe123!'), 'role' => 'government_admin', 'locale' => 'fa', 'is_active' => 1]);
        User::firstOrCreate(['email' => 'inspector@rahnuma.af'], ['name' => 'Government Inspector', 'password' => Hash::make('ChangeMe123!'), 'role' => 'inspector', 'locale' => 'fa', 'is_active' => 1]);
        $agency = Agency::firstOrCreate(['slug' => 'demo-rahnama'], ['plan_id' => $plans['professional']->id, 'province_id' => $kabul->id, 'district_id' => $d10->id, 'name' => 'Demo Rahnama', 'license_status' => 'unverified', 'phone' => '+93 700 000 000', 'address' => 'Kabul, Afghanistan', 'latitude' => 34.5378, 'longitude' => 69.1723, 'is_verified' => 0, 'is_active' => 1, 'trial_ends_at' => now()->addDays(30)]);
        User::firstOrCreate(['email' => 'demo@rahnuma.af'], ['agency_id' => $agency->id, 'name' => 'Demo Owner', 'password' => Hash::make('ChangeMe123!'), 'role' => 'agency_owner', 'locale' => 'fa', 'is_active' => 1]);
        app(AccountingService::class)->ensureDefaultAccounts($agency);
    }
}
