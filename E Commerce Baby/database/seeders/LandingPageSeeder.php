<?php

namespace Database\Seeders;

use App\Models\LandingPage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LandingPage::firstOrCreate(
            ['slug' => 'chicken-booster'],
            [
                'name'            => 'Chicken Booster (চিকেন বুস্টার)',
                'slug'            => 'chicken-booster',
                'status'          => 'published',
                'theme'           => 'chicken-booster',
                'product_id'      => 'chicken-booster',
                'product_name'    => 'Chicken Booster (Broiler & Layer Booster)',
                'title'           => 'চিকেন বুস্টার (Broiler & Layer Booster) — Growth Agro | রোগমুক্ত, দ্রুত মোটাতাজা ও সুস্থ মুরগি',
                'meta_title'      => 'চিকেন বুস্টার (Broiler & Layer Booster) — Growth Agro',
                'meta_description'=> 'মুরগির দ্রুত ওজন বৃদ্ধি, এফসিআর (FCR) উন্নয়ন ও সুস্থতার সম্পূর্ণ প্রাকৃতিক খাদ্য সম্পূরক। সারাদেশের খামারিদের নির্ভরযোগ্য সমাধান। ক্যাশ অন ডেলিভারি।',
                'content'         => LandingPage::getDefaultMasterContent(),
                'delivery_config' => LandingPage::getDefaultDeliveryConfig(),
                'theme_config'    => LandingPage::getDefaultThemeConfig(),
                'seo_config'      => [
                    'og_title'       => 'চিকেন বুস্টার (Broiler & Layer Booster) — Growth Agro',
                    'og_description' => 'রোগমুক্ত, দ্রুত মোটাতাজা ও সুস্থ মুরগি পেতে চান? জানুন প্রাকৃতিক সমাধান।',
                    'og_image'       => '/assets/images/chicken-booster-product.webp',
                    'canonical_url'  => 'https://growthagro.shop/product/chicken-booster',
                    'robots'         => 'index, follow',
                ],
                'section_order'   => LandingPage::getDefaultSectionOrder(),
                'published_at'    => Carbon::now(),
            ]
        );
    }
}
