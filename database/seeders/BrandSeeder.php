<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Country;
use App\Models\ReferralLink;
use App\Models\DiscountCode;
use App\Models\SocialMediaPlatform;
use App\Models\ReferralEarning;
use Illuminate\Support\Facades\DB;


class BrandSeeder extends Seeder
{
    public function run(): void
    {
    //     $countries = Country::all();
    //     $categoryId = 1; // يمكنك تغييره حسب الحاجة

    //     $brands = [
    //         'Amazon' => [
    //             'en' => 'Online marketplace for everything.',
    //             'ar' => 'منصة تسوق إلكترونية لكل شيء.'
    //         ],
    //         'Apple' => [
    //             'en' => 'Innovative tech and smart devices.',
    //             'ar' => 'تقنيات مبتكرة وأجهزة ذكية.'
    //         ],
    //         'Nike' => [
    //             'en' => 'Sportswear and activewear.',
    //             'ar' => 'ملابس وأحذية رياضية.'
    //         ],
    //         'Adidas' => [
    //             'en' => 'Athletic clothing and footwear.',
    //             'ar' => 'ملابس وأحذية للرياضيين.'
    //         ],
    //         'Samsung' => [
    //             'en' => 'Electronics and appliances.',
    //             'ar' => 'إلكترونيات وأجهزة منزلية.'
    //         ],
    //         'Sony' => [
    //             'en' => 'Tech, gaming, and entertainment.',
    //             'ar' => 'تكنولوجيا وألعاب وترفيه.'
    //         ],
    //         'Zara' => [
    //             'en' => 'Trendy fashion and accessories.',
    //             'ar' => 'أزياء عصرية وإكسسوارات.'
    //         ],
    //         'H&M' => [
    //             'en' => 'Affordable fashion for all.',
    //             'ar' => 'أزياء بأسعار مناسبة للجميع.'
    //         ],
    //         'Shein' => [
    //             'en' => 'Online fast fashion.',
    //             'ar' => 'أزياء سريعة على الإنترنت.'
    //         ],
    //         'Starbucks' => [
    //             'en' => 'Coffeehouse and drinks.',
    //             'ar' => 'مقهى ومشروبات متنوعة.'
    //         ],
    //         'McDonald\'s' => [
    //             'en' => 'Fast food chain.',
    //             'ar' => 'سلسلة وجبات سريعة.'
    //         ],
    //         'Coca-Cola' => [
    //             'en' => 'Beverage and soft drinks.',
    //             'ar' => 'مشروبات غازية ومنعشة.'
    //         ],
    //         'Pepsi' => [
    //             'en' => 'Beverages and entertainment.',
    //             'ar' => 'مشروبات وترفيه.'
    //         ],
    //         'L\'Oréal' => [
    //             'en' => 'Cosmetics and beauty products.',
    //             'ar' => 'مستحضرات تجميل وعناية بالبشرة.'
    //         ],
    //         'Dior' => [
    //             'en' => 'Luxury fashion and perfumes.',
    //             'ar' => 'أزياء وعطور فاخرة.'
    //         ],
    //         'Gucci' => [
    //             'en' => 'High-end fashion brand.',
    //             'ar' => 'ماركة أزياء فاخرة.'
    //         ],
    //         'Toyota' => [
    //             'en' => 'Automobiles and mobility.',
    //             'ar' => 'سيارات وحلول تنقل.'
    //         ],
    //         'Honda' => [
    //             'en' => 'Cars and motorcycles.',
    //             'ar' => 'سيارات ودراجات نارية.'
    //         ],
    //         'Microsoft' => [
    //             'en' => 'Software and cloud tech.',
    //             'ar' => 'برمجيات وتقنيات سحابية.'
    //         ],
    //         'Google' => [
    //             'en' => 'Search, advertising, and AI.',
    //             'ar' => 'بحث، إعلانات، وذكاء اصطناعي.'
    //         ],
    //     ];

    //     foreach ($brands as $nameEn => $desc) {
    //         $brand = Brand::create([
    //             'name' => [
    //                 'en' => $nameEn,
    //                 'ar' => $this->arabicBrandName($nameEn),
    //             ],
    //             'description' => [
    //                 'en' => $desc['en'],
    //                 'ar' => $desc['ar'],
    //             ],
    //             'logo' => 'brand_image.png',
    //             'category_id' => $categoryId,
    //             'total_marketers' => rand(50, 500),
    //         ]);

    //         // Attach all countries
    //         $brand->countries()->sync($countries->pluck('id'));

    //         // Add 3 referral links
    //         for ($i = 1; $i <= 3; $i++) {
    //             ReferralLink::create([
    //                 'brand_id' => $brand->id,
    //                 'link' => "https://example.com/referral/{$nameEn}/{$i}",
    //                 'earning_precentage' => rand(5, 15),
    //             ]);
    //         }

    //         // Add 3 discount codes
    //         for ($i = 1; $i <= 3; $i++) {
    //             DiscountCode::create([
    //                 'brand_id' => $brand->id,
    //                 'code' => strtoupper(substr($nameEn, 0, 3)) . rand(100, 999),
    //                 'earning_precentage' => rand(5, 15),
    //             ]);
    //         }
    //     }
    // }

    // private function arabicBrandName(string $english): string
    // {
    //     $translations = [
    //         'Amazon' => 'أمازون',
    //         'Apple' => 'آبل',
    //         'Nike' => 'نايكي',
    //         'Adidas' => 'أديداس',
    //         'Samsung' => 'سامسونج',
    //         'Sony' => 'سوني',
    //         'Zara' => 'زارا',
    //         'H&M' => 'اتش آند ام',
    //         'Shein' => 'شي إن',
    //         'Starbucks' => 'ستاربكس',
    //         'McDonald\'s' => 'ماكدونالدز',
    //         'Coca-Cola' => 'كوكاكولا',
    //         'Pepsi' => 'بيبسي',
    //         'L\'Oréal' => 'لوريال',
    //         'Dior' => 'ديور',
    //         'Gucci' => 'غوتشي',
    //         'Toyota' => 'تويوتا',
    //         'Honda' => 'هوندا',
    //         'Microsoft' => 'مايكروسوفت',
    //         'Google' => 'جوجل',
    //     ];

    //     return $translations[$english] ?? $english;





    //   SocialMediaPlatform::all()->delete();
    // ReferralEarning::truncate(); // if you want to also reset auto-increment ID

    // SocialMediaPlatform::truncate(); // if you want to also reset auto-increment ID


    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
// ReferralEarning::truncate();
SocialMediaPlatform::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');



        $platforms = [
            [
                'name' => [
                    'en' => 'Facebook',
                    'ar' => 'فيسبوك'
                ],
                'logo' => 'logos/facebook.png'
            ],
            [
                'name' => [
                    'en' => 'Instagram',
                    'ar' => 'إنستغرام'
                ],
                'logo' => 'logos/Instagram.png'
            ],
            [
                'name' => [
                    'en' => 'Snapchat',
                    'ar' => 'سناب شات'
                ],
                'logo' => 'logos/snapchat.png'
            ],
       
   
            [
                'name' => [
                    'en' => 'Telegram',
                    'ar' => 'تليجرام'
                ],
                'logo' => 'logos/telegram.png'
            ],
            [
                'name' => [
                    'en' => 'WhatsApp',
                    'ar' => 'واتساب'
                ],
                'logo' => 'logos/whatsapp.png'
            ],
                [
                'name' => [
                    'en' => 'Messanger',
                    'ar' => 'ماسنجر'
                ],
                'logo' => 'logos/messanger.png'
            ],

        ];

        foreach ($platforms as $platform) {
            SocialMediaPlatform::updateOrCreate(
                ['name->en' => $platform['name']['en']],
                [
                    'name' => $platform['name'],
                    'logo' => $platform['logo'],
                ]
            );
        }
    }
}
