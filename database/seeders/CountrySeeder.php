<?php

// namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;
// use App\Models\Country;


// class CountrySeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//         $countries = [
//             ['name' => 'Egypt', 'code' => '20'],
//             ['name' => 'United States', 'code' => '1'],
//             ['name' => 'Germany', 'code' => '49'],
//             ['name' => 'United Kingdom', 'code' => '44'],
//             ['name' => 'France', 'code' => '33'],
//             ['name' => 'India', 'code' => '91'],
//             ['name' => 'Saudi Arabia', 'code' => '966'],
//             ['name' => 'United Arab Emirates', 'code' => '971'],
//             ['name' => 'Canada', 'code' => '1'],
//             ['name' => 'Australia', 'code' => '61'],
//         ];

//         foreach ($countries as $country) {
//             Country::create($country);
//         }
//     }
// }



namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
                'code' => '20',
                'image' => 'flags/Flag_of_Egypt.svg.webp'
            ],
            [
                'name' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية'],
                'code' => '966',
                'image' => 'flags/Flag_of_Saudi_Arabia.svg.webp'
            ],
            [
                'name' => ['en' => 'United Arab Emirates', 'ar' => 'الإمارات'],
                'code' => '971',
                'image' => 'flags/Flag_of_the_United_Arab_Emirates.svg.webp'
            ],
            [
                'name' => ['en' => 'Jordan', 'ar' => 'الأردن'],
                'code' => '962',
                'image' => 'flags/Flag_of_Jordan.svg'
            ],
            [
                'name' => ['en' => 'Iraq', 'ar' => 'العراق'],
                'code' => '964',
                'image' => 'flags/Flag-Iraq.webp'
            ],
            [
                'name' => ['en' => 'Lebanon', 'ar' => 'لبنان'],
                'code' => '961',
                'image' => 'flags/Flag_of_Lebanon.svg'
            ],
            [
                'name' => ['en' => 'Sudan', 'ar' => 'السودان'],
                'code' => '249',
                'image' => 'flags/Flag-Sudan.webp'
            ],
            [
                'name' => ['en' => 'Morocco', 'ar' => 'المغرب'],
                'code' => '212',
                'image' => 'flags/Flag_of_Morocco.svg.webp'
            ],
                        [
                'name' => ['en' => 'Qatar', 'ar' => "قطر"],
                'code' => '974',
                'image' => 'flags/Flag_of_Qatar.svg'
            ],
            // [
            //     'name' => ['en' => 'Algeria', 'ar' => 'الجزائر'],
            //     'code' => '213',
            //     'image' => 'flags/dz.png'
            // ],
            // [
            //     'name' => ['en' => 'Tunisia', 'ar' => 'تونس'],
            //     'code' => '216',
            //     'image' => 'flags/tn.png'
            // ],
            // [
            //     'name' => ['en' => 'Libya', 'ar' => 'ليبيا'],
            //     'code' => '218',
            //     'image' => 'flags/ly.png'
            // ],
            // [
            //     'name' => ['en' => 'Palestine', 'ar' => 'فلسطين'],
            //     'code' => '970',
            //     'image' => 'flags/ps.png'
            // ],
            // [
            //     'name' => ['en' => 'Syria', 'ar' => 'سوريا'],
            //     'code' => '963',
            //     'image' => 'flags/sy.png'
            // ],
            // [
            //     'name' => ['en' => 'Yemen', 'ar' => 'اليمن'],
            //     'code' => '967',
            //     'image' => 'flags/ye.png'
            // ],
            // [
            //     'name' => ['en' => 'Bahrain', 'ar' => 'البحرين'],
            //     'code' => '973',
            //     'image' => 'flags/bh.png'
            // ],
            // [
            //     'name' => ['en' => 'Kuwait', 'ar' => 'الكويت'],
            //     'code' => '965',
            //     'image' => 'flags/kw.png'
            // ],
            // [
            //     'name' => ['en' => 'Qatar', 'ar' => 'قطر'],
            //     'code' => '974',
            //     'image' => 'flags/qa.png'
            // ],
            // [
            //     'name' => ['en' => 'Oman', 'ar' => 'عُمان'],
            //     'code' => '968',
            //     'image' => 'flags/om.png'
            // ],
            // [
            //     'name' => ['en' => 'Comoros', 'ar' => 'جزر القمر'],
            //     'code' => '269',
            //     'image' => 'flags/km.png'
            // ],
            // [
            //     'name' => ['en' => 'Mauritania', 'ar' => 'موريتانيا'],
            //     'code' => '222',
            //     'image' => 'flags/mr.png'
            // ],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
