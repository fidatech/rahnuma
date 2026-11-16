<?php

namespace Database\Seeders;

use App\Models\{Area, District, Province};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Real reference data: the 34 provinces of Afghanistan, and the districts and neighbourhoods
 * of the five largest cities. Part of DatabaseSeeder, so it is installed on the live server.
 *
 * Safe to run again: existing rows are kept and only missing ones are added.
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // [English, Dari, Pashto, code, latitude, longitude]
        $provinces = [
            ['Kabul', 'کابل', 'کابل', 'KBL', 34.5553, 69.2075],
            ['Herat', 'هرات', 'هرات', 'HRT', 34.3529, 62.2040],
            ['Balkh', 'بلخ', 'بلخ', 'BLK', 36.7090, 67.1109],
            ['Kandahar', 'قندهار', 'کندهار', 'KDH', 31.6289, 65.7372],
            ['Nangarhar', 'ننگرهار', 'ننګرهار', 'NGR', 34.4265, 70.4515],
            ['Badakhshan', 'بدخشان', 'بدخشان', 'BDS', 37.1166, 70.5800],
            ['Badghis', 'بادغیس', 'بادغیس', 'BDG', 34.9876, 63.1289],
            ['Baghlan', 'بغلان', 'بغلان', 'BGL', 35.9446, 68.7151],
            ['Bamyan', 'بامیان', 'بامیان', 'BAM', 34.8100, 67.8212],
            ['Daykundi', 'دایکندی', 'دایکندي', 'DAY', 33.7228, 66.1300],
            ['Farah', 'فراه', 'فراه', 'FRA', 32.3745, 62.1164],
            ['Faryab', 'فاریاب', 'فاریاب', 'FYB', 35.9214, 64.7842],
            ['Ghazni', 'غزنی', 'غزني', 'GHA', 33.5536, 68.4269],
            ['Ghor', 'غور', 'غور', 'GHO', 34.5195, 65.2509],
            ['Helmand', 'هلمند', 'هلمند', 'HEL', 31.5938, 64.3716],
            ['Jowzjan', 'جوزجان', 'جوزجان', 'JOW', 36.6654, 65.7529],
            ['Kapisa', 'کاپیسا', 'کاپیسا', 'KAP', 35.0170, 69.3340],
            ['Khost', 'خوست', 'خوست', 'KHO', 33.3395, 69.9204],
            ['Kunar', 'کنر', 'کونړ', 'KNR', 34.8466, 71.0973],
            ['Kunduz', 'کندز', 'کندز', 'KDZ', 36.7286, 68.8681],
            ['Laghman', 'لغمان', 'لغمان', 'LAG', 34.6714, 70.2094],
            ['Logar', 'لوگر', 'لوګر', 'LOG', 33.9953, 69.0227],
            ['Nimroz', 'نیمروز', 'نیمروز', 'NIM', 30.9600, 61.8600],
            ['Nuristan', 'نورستان', 'نورستان', 'NUR', 35.3250, 70.9071],
            ['Paktia', 'پکتیا', 'پکتیا', 'PIA', 33.5970, 69.2259],
            ['Paktika', 'پکتیکا', 'پکتیکا', 'PKA', 33.1757, 68.7304],
            ['Panjshir', 'پنجشیر', 'پنجشېر', 'PAN', 35.3158, 69.5190],
            ['Parwan', 'پروان', 'پروان', 'PAR', 35.0130, 69.1711],
            ['Samangan', 'سمنگان', 'سمنګان', 'SAM', 36.2655, 68.0167],
            ['Sar-e-Pul', 'سرپل', 'سرپل', 'SAR', 36.2166, 65.9360],
            ['Takhar', 'تخار', 'تخار', 'TAK', 36.7361, 69.5345],
            ['Uruzgan', 'ارزگان', 'ارزګان', 'URU', 32.6271, 65.8733],
            ['Maidan Wardak', 'میدان وردک', 'میدان وردګ', 'WAR', 34.3963, 68.8683],
            ['Zabul', 'زابل', 'زابل', 'ZAB', 32.1058, 66.9069],
        ];

        $ids = [];
        foreach ($provinces as [$en, $fa, $ps, $code, $lat, $lng]) {
            $province = Province::firstOrCreate(['code' => $code], ['name_en' => $en, 'name_fa' => $fa, 'name_ps' => $ps, 'latitude' => $lat, 'longitude' => $lng]);
            if ($province->latitude === null) {
                $province->update(['latitude' => $lat, 'longitude' => $lng]);
            }
            $ids[$code] = $province->id;
        }

        // province code => [district code, English, Dari, Pashto, [[area English, Dari, Pashto, latitude, longitude], ...]]
        $cities = [
            'KBL' => [
                ['KBL-N10', 'District 10', 'ناحیه ۱۰', '۱۰ ناحیه', [
                    ['Shahr-e-Naw', 'شهرنو', 'شهر نو', 34.5378, 69.1723],
                    ['Wazir Akbar Khan', 'وزیر اکبر خان', 'وزیر اکبر خان', 34.5357, 69.1880],
                    ['Qala-e-Fathullah', 'قلعه فتح‌الله', 'قلعه فتح الله', 34.5446, 69.1652],
                    ['Taimani', 'تایمنی', 'تایمني', 34.5492, 69.1601],
                ]],
                ['KBL-N03', 'District 3', 'ناحیه ۳', '۳ ناحیه', [
                    ['Karte Char', 'کارته چهار', 'کارته چهار', 34.5126, 69.1368],
                    ['Dehbori', 'دهبوری', 'دهبوري', 34.5155, 69.1260],
                ]],
                ['KBL-N04', 'District 4', 'ناحیه ۴', '۴ ناحیه', [
                    ['Karte Parwan', 'کارته پروان', 'کارته پروان', 34.5400, 69.1410],
                    ['Kolola Pushta', 'کلوله پشته', 'کلوله پشته', 34.5443, 69.1585],
                ]],
                ['KBL-N05', 'District 5', 'ناحیه ۵', '۵ ناحیه', [
                    ['Kote Sangi', 'کوته سنگی', 'کوټه سنګي', 34.5119, 69.1140],
                    ['Khushal Khan', 'خوشحال خان', 'خوشحال خان', 34.5230, 69.0985],
                ]],
                ['KBL-N06', 'District 6', 'ناحیه ۶', '۶ ناحیه', [
                    ['Karte Se', 'کارته سه', 'کارته سه', 34.4996, 69.1305],
                    ['Darulaman', 'دارالامان', 'دارالامان', 34.4680, 69.1190],
                ]],
                ['KBL-N11', 'District 11', 'ناحیه ۱۱', '۱۱ ناحیه', [
                    ['Khair Khana', 'خیرخانه', 'خیرخانه', 34.5705, 69.1310],
                ]],
                ['KBL-N13', 'District 13', 'ناحیه ۱۳', '۱۳ ناحیه', [
                    ['Dasht-e-Barchi', 'دشت برچی', 'دشت برچي', 34.5020, 69.0560],
                ]],
            ],
            'HRT' => [
                ['HRT-CTY', 'Herat City', 'شهر هرات', 'هرات ښار', [
                    ['Jada-e-Mahtab', 'جاده مهتاب', 'جاده مهتاب', 34.3560, 62.2150],
                    ['Bagh-e-Azadi', 'باغ آزادی', 'باغ آزادي', 34.3470, 62.2090],
                    ['Jebrael', 'جبرئیل', 'جبرئیل', 34.3760, 62.1640],
                    ['Shahr-e-Naw (Herat)', 'شهرنو هرات', 'د هرات شهر نو', 34.3520, 62.1990],
                ]],
                ['HRT-INJ', 'Injil', 'انجیل', 'انجیل', [
                    ['Injil Centre', 'مرکز انجیل', 'د انجیل مرکز', 34.3050, 62.2500],
                ]],
            ],
            'BLK' => [
                ['BLK-MZR', 'Mazar-e-Sharif', 'مزار شریف', 'مزار شریف', [
                    ['Karte Bokhdi', 'کارته بخدی', 'کارته بخدي', 36.7180, 67.1210],
                    ['Karte Sulh', 'کارته صلح', 'کارته صلح', 36.6990, 67.1250],
                    ['Dasht-e-Shor', 'دشت شور', 'دشت شور', 36.7290, 67.1010],
                    ['Khalid bin Walid Township', 'شهرک خالد بن ولید', 'د خالد بن ولید ښارګوټی', 36.6880, 67.1560],
                ]],
            ],
            'KDH' => [
                ['KDH-CTY', 'Kandahar City', 'شهر قندهار', 'کندهار ښار', [
                    ['Aino Mena', 'عینو مینه', 'عینو مېنه', 31.6420, 65.7900],
                    ['Shahr-e-Naw (Kandahar)', 'شهرنو قندهار', 'د کندهار شهر نو', 31.6210, 65.7120],
                    ['Loya Wiala', 'لویه ویاله', 'لویه ویاله', 31.6480, 65.7180],
                ]],
            ],
            'NGR' => [
                ['NGR-JAL', 'Jalalabad', 'جلال‌آباد', 'جلال اباد', [
                    ['Angoor Bagh', 'انگور باغ', 'انګور باغ', 34.4340, 70.4380],
                    ['Joy-e-Haft', 'جوی هفت', 'جوی هفت', 34.4230, 70.4610],
                    ['Talashi Chowk', 'چوک تلاشی', 'تلاشي چوک', 34.4290, 70.4500],
                ]],
            ],
        ];

        foreach ($cities as $provinceCode => $districts) {
            foreach ($districts as [$code, $en, $fa, $ps, $areas]) {
                $district = District::firstOrCreate(['province_id' => $ids[$provinceCode], 'code' => $code], ['name_en' => $en, 'name_fa' => $fa, 'name_ps' => $ps]);
                foreach ($areas as [$areaEn, $areaFa, $areaPs, $lat, $lng]) {
                    Area::firstOrCreate(['district_id' => $district->id, 'name_en' => $areaEn], ['name_fa' => $areaFa, 'name_ps' => $areaPs, 'latitude' => $lat, 'longitude' => $lng]);
                }
            }
        }

        Cache::forget('rahnuma:provinces:list');
    }
}
