<?php

namespace Database\Seeders;

use App\Models\{Plan, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Only what a real installation needs: the subscription plans, the provinces and city
 * locations, and the platform administrator. This is what `php artisan migrate --seed`
 * and `php artisan db:seed` install, on a laptop and on the live server alike.
 *
 * Sample offices, properties and demo sign-ins are in DemoSeeder and are never run from here:
 *
 *     php artisan db:seed --class=DemoSeeder
 *
 * Safe to run again: existing rows are kept.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Basic', 'basic', 1000, 10000, 2, 100], ['Professional', 'professional', 2500, 25000, 7, 1000], ['Business', 'business', 5000, 50000, 25, null]] as [$name, $code, $monthly, $annual, $users, $properties]) {
            Plan::firstOrCreate(['code' => $code], ['name' => $name, 'monthly_price' => $monthly, 'annual_price' => $annual, 'user_limit' => $users, 'property_limit' => $properties, 'features' => ['marketplace' => true, 'accounting' => true, 'mobile' => true], 'is_active' => 1]);
        }

        $this->call(LocationSeeder::class);

        // The first platform administrator. Change this password right after the first sign-in.
        User::firstOrCreate(['email' => 'admin@rahnuma.af'], ['name' => 'Platform Admin', 'password' => Hash::make('ChangeMe123!'), 'role' => 'platform_admin', 'locale' => 'en', 'is_active' => 1]);
    }
}
