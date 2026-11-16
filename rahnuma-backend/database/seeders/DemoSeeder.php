<?php

namespace Database\Seeders;

use App\Models\{Agency, Area, Customer, CustomerRequirement, District, Lead, Owner, Plan, Property, PropertyOwner, Province, User};
use App\Services\AccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Sample data for a laptop or a test server. NEVER part of DatabaseSeeder, so the live server
 * does not get it. Run it on purpose:
 *
 *     php artisan db:seed --class=DemoSeeder
 *
 * It adds 20 Rahnama offices in Kabul, Herat, Mazar-e-Sharif, Kandahar and Jalalabad, each with
 * an owner sign-in, 3 landlords and 5 properties (100 properties, at least one in each of the
 * 34 provinces, each with photos downloaded by DemoPhotoSeeder), plus customers, requirements
 * and leads for "Demo Rahnama", and two demo sign-ins for the regulatory workspace.
 *
 * Every demo sign-in uses the password ChangeMe123!
 *     demo@rahnuma.af                 owner of Demo Rahnama
 *     {office-slug}@demo.rahnuma.af   owner of each other office
 *     government@rahnuma.af, inspector@rahnuma.af
 *
 * It refuses to run when APP_ENV=production. Safe to run again: nothing is added twice.
 * The same offices and properties are produced on every machine.
 */
class DemoSeeder extends Seeder
{
    private const PASSWORD = 'ChangeMe123!';

    /** [name, slug, tagline, city, plan, verified, featured] */
    private const AGENCIES = [
        ['Demo Rahnama', 'demo-rahnama', 'Trusted property guidance in Kabul', 'KBL', 'professional', true, true],
        ['Shahr-e-Naw Real Estate', 'shahr-e-naw-real-estate', 'Apartments and offices in the heart of Kabul', 'KBL', 'business', true, true],
        ['Wazir Property Guide', 'wazir-property-guide', 'Homes for families and organisations', 'KBL', 'professional', true, false],
        ['Karte Char Rahnama', 'karte-char-rahnama', 'West Kabul houses and shops', 'KBL', 'basic', false, false],
        ['Khair Khana Amlak', 'khair-khana-amlak', 'North Kabul sale, rent and mortgage', 'KBL', 'professional', true, false],
        ['Barchi Property Office', 'barchi-property-office', 'Affordable homes in Dasht-e-Barchi', 'KBL', 'basic', false, false],
        ['Darulaman Estates', 'darulaman-estates', 'Land and new buildings on Darulaman road', 'KBL', 'professional', true, true],
        ['Pamir Rahnama', 'pamir-rahnama', 'Careful deals, clear paperwork', 'KBL', 'basic', false, false],
        ['Herat Aria Real Estate', 'herat-aria-real-estate', 'The property address of Herat', 'HRT', 'business', true, true],
        ['Jada-e-Mahtab Amlak', 'jada-e-mahtab-amlak', 'Shops and apartments in central Herat', 'HRT', 'professional', true, false],
        ['Jebrael Property Guide', 'jebrael-property-guide', 'Family homes in Jebrael township', 'HRT', 'basic', false, false],
        ['Injil Rahnama', 'injil-rahnama', 'Land and gardens around Herat', 'HRT', 'basic', false, false],
        ['Mazar Blue Mosque Estates', 'mazar-blue-mosque-estates', 'Mazar-e-Sharif homes and commercial space', 'BLK', 'business', true, true],
        ['Bokhdi Rahnama', 'bokhdi-rahnama', 'Karte Bokhdi and Karte Sulh specialists', 'BLK', 'professional', true, false],
        ['Khalid bin Walid Amlak', 'khalid-bin-walid-amlak', 'Township apartments in Mazar', 'BLK', 'basic', false, false],
        ['Aino Mena Real Estate', 'aino-mena-real-estate', 'Modern living in Aino Mena', 'KDH', 'business', true, true],
        ['Kandahar Property House', 'kandahar-property-house', 'Sale, rent and mortgage in Kandahar', 'KDH', 'professional', true, false],
        ['Loya Wiala Rahnama', 'loya-wiala-rahnama', 'Houses and land in Loya Wiala', 'KDH', 'basic', false, false],
        ['Jalalabad Green Estates', 'jalalabad-green-estates', 'Homes and gardens in Jalalabad', 'NGR', 'professional', true, false],
        ['Angoor Bagh Amlak', 'angoor-bagh-amlak', 'Angoor Bagh and city centre property', 'NGR', 'basic', false, false],
    ];

    private const PEOPLE = [
        'Ahmad Karimi', 'Mohammad Nazari', 'Abdul Rahman Safi', 'Hamid Wardak', 'Zabihullah Ahmadi', 'Nasir Popal', 'Farid Hotak', 'Jawid Rasuli',
        'Mirwais Stanikzai', 'Sayed Mustafa Hashimi', 'Khalil Noorzai', 'Najibullah Amiri', 'Shafiq Sultani', 'Rahmatullah Barakzai', 'Yama Durrani',
        'Haji Gul Mohammad', 'Qais Mohammadi', 'Wahid Azizi', 'Obaidullah Mangal', 'Bashir Faizi', 'Zarghona Noori', 'Fatima Rezaie', 'Mariam Sharifi',
        'Shukria Haidari', 'Latifa Yusufzai', 'Homa Sadat', 'Nilofar Jalali', 'Sima Ghafoori', 'Parwin Kakar', 'Roya Hussaini',
    ];

    /** kind => [property type, deal, English title, Dari title, min, max, currency, bedrooms, area range, unit] */
    private const KINDS = [
        ['apartment', 'rent', ':n-bedroom apartment in :area', 'اپارتمان :n اتاقه در :area', 150, 650, 'USD', [1, 4], [70, 180], 'sqm'],
        ['apartment', 'sale', ':n-bedroom apartment for sale in :area', 'اپارتمان :n اتاقه فروشی در :area', 28000, 120000, 'USD', [2, 4], [85, 210], 'sqm'],
        ['house', 'sale', ':n-bedroom family house in :area', 'خانهٔ :n اتاقه در :area', 65000, 340000, 'USD', [3, 8], [180, 600], 'sqm'],
        ['house', 'rent', 'House with yard for rent in :area', 'حویلی کرایی در :area', 12000, 60000, 'AFN', [3, 7], [160, 450], 'sqm'],
        ['house', 'bay_wafa', 'House for mortgage (Bay-e-Wafa) in :area', 'خانهٔ گروی در :area', 12000, 70000, 'USD', [3, 6], [150, 400], 'sqm'],
        ['shop', 'rent', 'Shop on the main road, :area', 'دکان سر سرک عمومی در :area', 8000, 55000, 'AFN', null, [18, 90], 'sqm'],
        ['shop', 'sale', 'Commercial shop for sale in :area', 'دکان تجارتی فروشی در :area', 18000, 95000, 'USD', null, [20, 110], 'sqm'],
        ['office', 'rent', 'Office floor for rent in :area', 'دفتر کرایی در :area', 300, 1800, 'USD', null, [60, 320], 'sqm'],
        ['land', 'sale', 'Residential plot in :area', 'نمرهٔ رهایشی در :area', 900000, 9000000, 'AFN', null, [1, 6], 'biswa'],
        ['apartment', 'bay_wafa', 'Apartment for mortgage (Bay-e-Wafa) in :area', 'اپارتمان گروی در :area', 8000, 40000, 'USD', [2, 4], [80, 170], 'sqm'],
        ['warehouse', 'rent', 'Warehouse with truck access near :area', 'گدام با راه موتر باربری نزدیک :area', 25000, 120000, 'AFN', null, [200, 1200], 'sqm'],
        ['building', 'sale', 'Four-storey building for sale in :area', 'تعمیر چهارمنزله فروشی در :area', 220000, 900000, 'USD', null, [300, 900], 'sqm'],
        ['garden', 'sale', 'Garden with well near :area', 'باغ با چاه آب نزدیک :area', 1500000, 12000000, 'AFN', null, [1, 5], 'jerib'],
    ];

    private int $seed = 20261116;

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DemoSeeder adds sample offices and properties. It does not run when APP_ENV=production.');

            return;
        }

        $plans = Plan::pluck('id', 'code');
        if ($plans->isEmpty() || Province::count() < 5) {
            $this->call(DatabaseSeeder::class);
            $plans = Plan::pluck('id', 'code');
        }

        foreach ([['government@rahnuma.af', 'Government Demo Admin', 'government_admin'], ['inspector@rahnuma.af', 'Government Inspector', 'inspector']] as [$email, $name, $role]) {
            User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make(self::PASSWORD), 'role' => $role, 'locale' => 'fa', 'is_active' => 1]);
        }

        $accounting = app(AccountingService::class);
        // the centre of the capital of every province that has no demo office
        $cities = array_unique(array_column(self::AGENCIES, 3));
        $elsewhere = Area::whereIn('district_id', District::whereIn('province_id', Province::whereNotIn('code', $cities)->pluck('id'))->pluck('id'))->orderBy('id')->get();
        if ($elsewhere->isEmpty()) {
            $this->call(LocationSeeder::class);
            $elsewhere = Area::whereIn('district_id', District::whereIn('province_id', Province::whereNotIn('code', $cities)->pluck('id'))->pluck('id'))->orderBy('id')->get();
        }
        $propertyCount = 0;

        foreach (self::AGENCIES as $index => [$name, $slug, $tagline, $provinceCode, $plan, $verified, $featured]) {
            $province = Province::where('code', $provinceCode)->firstOrFail();
            $areas = Area::whereIn('district_id', District::where('province_id', $province->id)->pluck('id'))->orderBy('id')->get();
            $home = $areas[$index % $areas->count()];
            $number = $index + 1;

            $agency = Agency::firstOrCreate(['slug' => $slug], [
                'plan_id' => $plans[$plan] ?? null,
                'province_id' => $province->id,
                'district_id' => $home->district_id,
                'area_id' => $home->id,
                'name' => $name,
                'tagline' => $tagline,
                'description' => "$name is a Rahnama office in {$home->name_en}, {$province->name_en}. We help families and businesses buy, rent and mortgage property with clear terms and complete paperwork.",
                'license_number' => $verified ? sprintf('RH-%s-%04d', $provinceCode, 1200 + $number) : null,
                'license_status' => $verified ? 'active' : 'unverified',
                'phone' => sprintf('+93 70%d %03d %03d', $number % 10, 100 + $number * 7, 200 + $number * 13),
                'whatsapp' => sprintf('+93 79%d %03d %03d', $number % 10, 300 + $number * 5, 400 + $number * 11),
                'email' => "info@$slug.example",
                'address' => "{$home->name_en}, {$province->name_en}, Afghanistan",
                'office_hours' => 'Sat–Thu, 8:00–17:00',
                'languages' => $provinceCode === 'KDH' || $provinceCode === 'NGR' ? ['ps', 'fa'] : ['fa', 'ps', 'en'],
                'latitude' => $home->latitude,
                'longitude' => $home->longitude,
                'is_verified' => $verified,
                'is_active' => 1,
                'marketplace_featured' => $featured,
                'profile_completed_at' => now(),
                'trial_ends_at' => now()->addYear(),
            ]);
            $accounting->ensureDefaultAccounts($agency);

            User::firstOrCreate(['email' => $slug === 'demo-rahnama' ? 'demo@rahnuma.af' : "$slug@demo.rahnuma.af"], [
                'agency_id' => $agency->id,
                'name' => $slug === 'demo-rahnama' ? 'Demo Owner' : self::PEOPLE[($index * 3) % count(self::PEOPLE)],
                'password' => Hash::make(self::PASSWORD),
                'role' => 'agency_owner',
                'job_title' => 'Agency owner',
                'locale' => $provinceCode === 'KDH' || $provinceCode === 'NGR' ? 'ps' : 'fa',
                'is_active' => 1,
            ]);

            // three landlords of the office
            $owners = [];
            for ($o = 0; $o < 3; $o++) {
                $person = self::PEOPLE[($index * 5 + $o * 7 + 1) % count(self::PEOPLE)];
                $owners[] = Owner::withoutGlobalScopes()->firstOrCreate(
                    ['agency_id' => $agency->id, 'phone' => sprintf('07%02d %03d %03d', 10 + $number, 100 + $o * 111, 500 + $number * 3)],
                    ['name' => $person, 'tazkira_no' => sprintf('1400-%04d-%05d', 100 + $number, 10000 + $o * 37 + $number), 'address' => "{$home->name_en}, {$province->name_en}"]
                );
            }

            // five properties of the office: three in the neighbourhoods of its own city and two in
            // other provinces, so that together the offices have a property in every province
            for ($p = 0; $p < 5; $p++) {
                $area = $p < 3 ? $areas[($index + $p * 2) % $areas->count()] : $elsewhere[($index * 2 + $p - 3) % $elsewhere->count()];
                $propertyCount += $this->property($agency, $owners[$p % 3], $area, $index, $p) ? 1 : 0;
            }

            if ($slug === 'demo-rahnama') {
                $this->customers($agency, $province, $areas);
            }
        }

        // photos from the internet for every demo property (needs a connection; skipped quietly without one)
        $this->call(DemoPhotoSeeder::class);

        Cache::forget('rahnuma:marketplace:home:v8');
        $this->command?->info('Demo data ready: '.count(self::AGENCIES).' offices, '.$propertyCount.' new properties. Sign in with demo@rahnuma.af / '.self::PASSWORD);
    }

    /** One sample property. Returns false when it was already there. */
    private function property(Agency $agency, Owner $owner, Area $area, int $index, int $p): bool
    {
        $province = Province::findOrFail(District::findOrFail($area->district_id)->province_id);
        $code = sprintf('DM-%02d%02d', $index + 1, $p + 1);
        if (Property::withoutGlobalScopes()->where('agency_id', $agency->id)->where('code', $code)->exists()) {
            return false;
        }

        [$type, $deal, $titleEn, $titleFa, $min, $max, $currency, $beds, $size, $unit] = self::KINDS[($index * 3 + $p * 4) % count(self::KINDS)];
        $bedrooms = $beds ? $this->between($beds[0], $beds[1]) : null;
        $dari = ($index + $p) % 2 === 1;
        $title = strtr($dari ? $titleFa : $titleEn, [':n' => (string) $bedrooms, ':area' => $dari ? $area->name_fa : $area->name_en]);
        $amount = $this->rounded($this->between($min, $max));

        // most sample properties are on the marketplace; a few stay private or are already closed
        $roll = ($index * 7 + $p * 3) % 20;
        $visibility = $roll < 13 ? 'public' : ($roll < 17 ? 'featured' : 'private');
        $status = $roll === 19 ? ['sale' => 'sold', 'rent' => 'rented', 'bay_wafa' => 'mortgaged'][$deal] : 'available';
        $sponsored = $visibility !== 'private' && $status === 'available' && ($index * 5 + $p) % 11 === 0;
        $residential = in_array($type, ['house', 'apartment'], true);
        $created = now()->subDays($this->between(1, 75))->subMinutes($this->between(0, 1400));

        $property = new Property([
            'agency_id' => $agency->id,
            'owner_id' => $owner->id,
            'province_id' => $province->id,
            'district_id' => $area->district_id,
            'area_id' => $area->id,
            'code' => $code,
            'title' => $title,
            'description' => $dari
                ? "ملکیت در {$area->name_fa}، {$province->name_fa}. موقعیت مناسب، نزدیک سرک عمومی، مکتب و بازار. اسناد مکمل است و بازدید با هماهنگی دفتر امکان دارد."
                : "Located in {$area->name_en}, {$province->name_en}. Good access to the main road, schools and the bazaar. Documents are complete and visits can be arranged through the office.",
            'property_type' => $type,
            'transaction_type' => $deal,
            'status' => $status,
            'visibility' => $visibility,
            'address' => 'Street '.$this->between(1, 14).', '.$area->name_en.', '.$province->name_en,
            'latitude' => $area->latitude !== null ? round((float) $area->latitude + $this->between(-40, 40) / 10000, 7) : null,
            'longitude' => $area->longitude !== null ? round((float) $area->longitude + $this->between(-40, 40) / 10000, 7) : null,
            'currency' => $currency,
            'price' => $deal === 'rent' ? null : $amount,
            'monthly_rent' => $deal === 'rent' ? $amount : null,
            'deposit_amount' => $deal === 'rent' ? $amount * 2 : null,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bedrooms ? max(1, intdiv($bedrooms + 1, 2)) : ($type === 'office' ? 2 : null),
            'kitchens' => $residential ? 1 : null,
            'floors' => $type === 'building' ? 4 : ($type === 'house' ? $this->between(1, 3) : null),
            'floor_number' => in_array($type, ['apartment', 'office'], true) ? $this->between(1, 9) : null,
            'total_area' => $this->between($size[0], $size[1]),
            'area_unit' => $unit,
            'property_age' => in_array($type, ['land', 'garden'], true) ? null : $this->between(0, 18),
            'parking' => $residential || $type === 'office' ? $this->chance(70) : false,
            'electricity' => ! in_array($type, ['land', 'garden'], true),
            'solar' => $residential && $this->chance(45),
            'water' => $type !== 'land',
            'well' => in_array($type, ['house', 'garden'], true) && $this->chance(60),
            'heating' => $residential && $this->chance(40),
            'cooling' => $residential && $this->chance(30),
            'furnished' => $type === 'apartment' && $deal === 'rent' && $this->chance(35),
            'internet' => in_array($type, ['apartment', 'office'], true) && $this->chance(65),
            'security' => in_array($type, ['apartment', 'office', 'building'], true) && $this->chance(60),
            'elevator' => in_array($type, ['apartment', 'office', 'building'], true) && $this->chance(55),
            'balcony' => $type === 'apartment' && $this->chance(70),
            'garden' => $type === 'house' && $this->chance(55),
            'basement' => in_array($type, ['house', 'building'], true) && $this->chance(40),
            'storage' => in_array($type, ['shop', 'warehouse', 'house'], true) && $this->chance(50),
            'featured_at' => $visibility === 'featured' ? $created : null,
            'published_at' => $visibility === 'private' ? null : $created,
            'is_sponsored' => $sponsored,
            'sponsored_until' => $sponsored ? now()->addDays(30) : null,
            'views_count' => $visibility === 'private' ? 0 : $this->between(5, 900),
        ]);
        $property->created_at = $created;
        $property->updated_at = $created;
        $property->save();

        PropertyOwner::withoutGlobalScopes()->firstOrCreate(
            ['property_id' => $property->id, 'owner_id' => $owner->id],
            ['agency_id' => $agency->id, 'is_primary' => true, 'ownership_percent' => 100]
        );

        return true;
    }

    /** Customers, what they are looking for, and leads, so the workspace of Demo Rahnama is not empty. */
    private function customers(Agency $agency, Province $province, $areas): void
    {
        $manager = User::where('agency_id', $agency->id)->first();
        $properties = Property::withoutGlobalScopes()->where('agency_id', $agency->id)->orderBy('id')->get();
        $sources = ['walk-in', 'referral', 'marketplace', 'whatsapp', 'facebook', 'phone'];
        $stages = ['new', 'contacted', 'property_suggested', 'visit_scheduled', 'negotiating', 'won'];
        // [deal, type, min, max, currency, min bedrooms]
        $needs = [['rent', 'apartment', 200, 500, 'USD', 2], ['sale', 'house', 80000, 200000, 'USD', 4], ['rent', 'shop', 10000, 40000, 'AFN', null], ['bay_wafa', 'house', 15000, 50000, 'USD', 3], ['sale', 'apartment', 30000, 90000, 'USD', 3], ['rent', 'office', 400, 1200, 'USD', null]];

        foreach ($needs as $i => [$deal, $type, $min, $max, $currency, $bedrooms]) {
            $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                ['agency_id' => $agency->id, 'phone' => sprintf('0799 55%d %03d', $i, 110 + $i * 101)],
                ['name' => self::PEOPLE[20 + $i], 'source' => $sources[$i], 'address' => $areas[$i % $areas->count()]->name_en.', '.$province->name_en, 'assigned_user_id' => $manager?->id, 'notes' => 'Sample customer.']
            );
            if (! $customer->wasRecentlyCreated) {
                continue;
            }

            CustomerRequirement::withoutGlobalScopes()->create([
                'agency_id' => $agency->id, 'customer_id' => $customer->id, 'transaction_type' => $deal, 'property_type' => $type,
                'province_id' => $province->id, 'min_price' => $min, 'max_price' => $max, 'currency' => $currency,
                'min_bedrooms' => $bedrooms, 'notes' => 'Prefers a quiet street close to the main road.', 'is_active' => true,
            ]);
            Lead::withoutGlobalScopes()->create([
                'agency_id' => $agency->id, 'customer_id' => $customer->id, 'property_id' => $properties[$i % max(1, $properties->count())]->id ?? null,
                'assigned_user_id' => $manager?->id, 'source' => $sources[$i], 'status' => $stages[$i],
                'next_follow_up_at' => now()->addDays($i + 1)->setTime(10 + $i, 0), 'notes' => 'Sample lead.',
            ]);
        }
    }

    // The same numbers on every machine, so two developers see the same demo data.
    private function between(int $min, int $max): int
    {
        $this->seed = ($this->seed * 1103515245 + 12345) % 2147483648;

        return $min + intdiv($this->seed, 65536) % ($max - $min + 1);
    }

    private function chance(int $percent): bool
    {
        return $this->between(1, 100) <= $percent;
    }

    /** A price as people write it: 85,000 and not 84,317. */
    private function rounded(int $amount): int
    {
        $step = $amount >= 1000000 ? 50000 : ($amount >= 100000 ? 5000 : ($amount >= 10000 ? 1000 : ($amount >= 1000 ? 100 : 10)));

        return max($step, intdiv($amount, $step) * $step);
    }
}
