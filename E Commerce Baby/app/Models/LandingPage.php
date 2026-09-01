<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    use HasFactory;

    protected $table = 'landing_pages';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'theme',
        'product_id',
        'product_name',
        'title',
        'meta_title',
        'meta_description',
        'content',
        'delivery_config',
        'theme_config',
        'seo_config',
        'section_order',
        'published_at',
    ];

    protected $casts = [
        'content'         => 'array',
        'delivery_config' => 'array',
        'theme_config'    => 'array',
        'seo_config'      => 'array',
        'section_order'   => 'array',
        'published_at'    => 'datetime',
    ];

    /**
     * Default Universal Theme Tokens (Modern, Clean E-Commerce Palette)
     */
    public static function getDefaultUniversalThemeConfig(): array
    {
        return [
            'primary_color'       => '#0F766E', // Modern Emerald/Teal
            'secondary_color'     => '#115E59', // Deep Teal
            'light_teal'          => '#F0FDFA', // Soft Teal Background
            'accent_yellow'       => '#F59E0B', // Accent Amber/Gold
            'accent_gold'         => '#D97706',
            'btn_red'             => '#E11D48', // Vibrant Rose/Red CTA
            'btn_red_hover'       => '#BE123C',
            'text_dark'           => '#0F172A', // Slate 900
            'text_muted'          => '#475569',
            'text_light'          => '#64748B',
            'bg_body'             => '#FFFFFF',
            'bg_card'             => '#FFFFFF',
            'bg_hero'             => '#0F766E',
            'bg_checkout'         => '#0F766E',
            'bg_footer'           => '#FFFFFF',
            'border_color'        => '#E2E8F0',
            'font_family_bn'      => "'Hind Siliguri', sans-serif",
            'font_family_en'      => "'Outfit', 'Poppins', sans-serif",
            'container_width'     => '1120px',
            'section_spacing'     => 'medium',
            'border_radius'       => 'md',
        ];
    }

    /**
     * Default Master Color Theme Tokens (Chicken Booster Preset)
     */
    public static function getDefaultThemeConfig(string $template = 'universal'): array
    {
        if ($template === 'chicken-booster') {
            return [
                'primary_color'       => '#054c55', // Chicken Booster Dark Teal
                'secondary_color'     => '#03363d', // Deep Teal
                'light_teal'          => '#eaf5f6', // Soft Background
                'accent_yellow'       => '#ffd166', // Accent Gold/Yellow
                'accent_gold'         => '#f59e0b',
                'btn_red'             => '#d90429', // Action Red
                'btn_red_hover'       => '#b50322',
                'text_dark'           => '#1e293b', // Dark Slate
                'text_muted'          => '#475569',
                'text_light'          => '#64748b',
                'bg_body'             => '#ffffff',
                'bg_card'             => '#ffffff',
                'bg_hero'             => '#054c55',
                'bg_checkout'         => '#054c55',
                'bg_footer'           => '#ffffff',
                'border_color'        => '#e2e8f0',
                'font_family_bn'      => "'Hind Siliguri', sans-serif",
                'font_family_en'      => "'Outfit', 'Poppins', sans-serif",
                'container_width'     => '1120px',
                'section_spacing'     => 'medium',
                'border_radius'       => 'md',
            ];
        }

        return self::getDefaultUniversalThemeConfig();
    }

    /**
     * Default Delivery Charge Configuration
     */
    public static function getDefaultDeliveryConfig(): array
    {
        return [
            'delivery_type'          => 'free', // 'free' or 'paid'
            'charge_inside_dhaka'    => 0,
            'charge_outside_dhaka'   => 0,
            'same_charge_everywhere' => true,
            'free_delivery_above'    => false,
            'free_delivery_threshold'=> 1000,
            'inside_label'           => 'ঢাকার ভিতরে',
            'outside_label'          => 'ঢাকার বাইরে',
        ];
    }

    /**
     * Default Section Order and Visibility
     */
    public static function getDefaultSectionOrder(): array
    {
        return [
            ['id' => 'hero',          'name' => 'Hero Banner',                 'enabled' => true],
            ['id' => 'videos',        'name' => 'Customer Videos / Reviews',  'enabled' => true],
            ['id' => 'benefits_1',    'name' => 'Product Benefits Checklist',  'enabled' => true],
            ['id' => 'benefits_2',    'name' => 'Key Features & Quality',      'enabled' => true],
            ['id' => 'usage',         'name' => 'How To Use / Usage Guide',    'enabled' => true],
            ['id' => 'offer',         'name' => 'Special Offer Banner',        'enabled' => false],
            ['id' => 'trust',         'name' => 'Trust & Authenticity Badges', 'enabled' => false],
            ['id' => 'reviews',       'name' => 'Customer Reviews & Ratings',  'enabled' => true],
            ['id' => 'faq',           'name' => 'FAQ Section',                 'enabled' => true],
            ['id' => 'checkout',      'name' => 'Checkout & Order Summary',    'enabled' => true],
            ['id' => 'footer',        'name' => 'Footer & Support Helpline',   'enabled' => true],
        ];
    }

    /**
     * Universal Product Template Default Content (Product-Agnostic)
     */
    public static function getDefaultUniversalContent(string $productName = 'প্রিমিয়াম কোয়ালিটি প্রোডাক্ট'): array
    {
        return [
            'header' => [
                'hotline_phone' => '01864-444411',
                'hotline_tel'   => '01864444411',
                'logo_image'    => '/images/logo.png',
                'cta_text'      => 'অর্ডার করুন',
            ],
            'hero' => [
                'alert_hook'      => '১০০% অরিজিনাল ও প্রিমিয়াম কোয়ালিটি নিশ্চিত',
                'main_title'      => 'আপনার পছন্দের ' . $productName . ' — সীমিত সময়ের বিশেষ ছাড়ে আজই অর্ডার করুন',
                'subtext'         => 'হাজারো সন্তুষ্ট গ্রাহকের নির্ভরযোগ্য পছন্দ। দ্রুত ডেলিভারি ও শতভাগ ক্যাশ অন ডেলিভারি সুবিধা।',
                'cta_button_text' => '👉 অর্ডার করতে ক্লিক করুন',
                'dual_cards'      => [
                    [
                        'tag'              => '১ পিস / ১ প্যাক (স্ট্যান্ডার্ড)',
                        'product_image'    => '/images/placeholder.webp',
                        'background_image' => '',
                        'variant_key'      => 'pkg-1',
                        'title'            => 'স্ট্যান্ডার্ড প্যাক — ক্লিক করে অর্ডার করুন',
                    ],
                    [
                        'tag'              => '২ পিস / ২ প্যাক (স্পেশাল কম্বো)',
                        'product_image'    => '/images/placeholder.webp',
                        'background_image' => '',
                        'variant_key'      => 'pkg-2',
                        'title'            => 'স্পেশাল কম্বো প্যাক — ক্লিক করে অর্ডার করুন',
                    ],
                ],
            ],
            'packages' => [
                [
                    'id'               => 'pkg-1',
                    'name'             => '১ পিস (স্ট্যান্ডার্ড প্যাক)',
                    'price'            => 500,
                    'old_price'        => 700,
                    'weight'           => 'Standard',
                    'image'            => '/images/placeholder.webp',
                    'default_quantity' => 1,
                    'is_active'        => true,
                    'sort_order'       => 1,
                ],
                [
                    'id'               => 'pkg-2',
                    'name'             => '২ পিস (স্পেশাল কম্বো অফার)',
                    'price'            => 950,
                    'old_price'        => 1400,
                    'weight'           => 'Combo',
                    'image'            => '/images/placeholder.webp',
                    'default_quantity' => 0,
                    'is_active'        => true,
                    'sort_order'       => 2,
                ],
            ],
            'video_reviews' => [
                'section_title' => 'গ্রাহকদের ভিডিও রিভিউ ও আনবক্সিং',
                'items' => [
                    [
                        'title'     => 'পণ্যটি ব্যবহারের বাস্তব অভিজ্ঞতা ও ফলাফল',
                        'thumbnail' => '/images/placeholder.webp',
                        'video_url' => '',
                    ],
                ],
            ],
            'benefits_section_1' => [
                'section_title' => 'কেন আমাদের পণ্যটি বেছে নেবেন?',
                'helpline_text' => 'প্রয়োজনে কল করুন: 01864-444411',
                'helpline_tel'  => '01864444411',
                'items' => [
                    ['title' => '১০০% প্রিমিয়াম ও অরিজিনাল কোয়ালিটি', 'desc' => 'সেরা মানের উপাদান দিয়ে তৈরি, দীর্ঘস্থায়ী ব্যবহারের নিশ্চয়তা।'],
                    ['title' => 'দ্রুত ও কার্যকর ফলাফল', 'desc' => 'নিয়মিত ও সঠিক ব্যবহারে শতভাগ সন্তোষজনক ফলাফল পাবেন।'],
                    ['title' => 'সহজ ও নিরাপদ ব্যবহার', 'desc' => 'দৈনন্দিন জীবনে সম্পূর্ণ নিরাপদ ও সহজে ব্যবহারের উপযোগী।'],
                    ['title' => 'সরাসরি প্রস্তুতকারক থেকে সংগৃহীত', 'desc' => 'কোনো নকল বা ভেজালের সুযোগ নেই, শতভাগ খাঁটি পণ্য।'],
                    ['title' => 'গ্রাহক সন্তুষ্টি গ্যারান্টি', 'desc' => 'পণ্য হাতে পেয়ে চেক করে নেওয়ার সুবিধা।'],
                ],
            ],
            'benefits_section_2' => [
                'section_title' => 'বিশেষ সুবিধাসমূহ ও গুণাগুণ',
                'helpline_text' => 'প্রয়োজনে কল করুন: 01864-444411',
                'helpline_tel'  => '01864444411',
                'items' => [
                    ['title' => 'আধুনিক ও আকর্ষণীয় ডিজাইন', 'desc' => 'প্রিমিয়াম ফিনিশিং ও টেকসই গঠন।'],
                    ['title' => 'কোনো পার্শ্বপ্রতিক্রিয়া বা ঝুঁকি নেই', 'desc' => 'পরীক্ষিত ও মাননিয়ন্ত্রিত পণ্য।'],
                    ['title' => 'সারাদেশে দ্রুততম হোম ডেলিভারি', 'desc' => 'ঢাকার ভিতরে ২৪ ঘণ্টা ও ঢাকার বাইরে ৪৮-৭২ ঘণ্টায় ডেলিভারি।'],
                    ['title' => 'ডেডিকেটেড কাস্টমার সাপোর্ট', 'desc' => 'যেকোনো জিজ্ঞাসা বা সহায়তায় আমাদের টিম সবসময় প্রস্তুত।'],
                ],
            ],
            'usage_guide' => [
                'section_title'    => 'ব্যবহার বিধি ও নির্দেশিকা',
                'image'            => '/images/placeholder.webp',
                'instruction_text' => 'প্যাকেটের নির্দেশিকা অনুযায়ী সঠিক নিয়মে ব্যবহার করুন। যেকোনো প্রশ্ন বা বিস্তারিত তথ্যের জন্য আমাদের সাপোর্ট নাম্বারে কল করুন।',
                'helpline_text'    => 'প্রয়োজনে কল করুন: 01864-444411',
                'helpline_tel'     => '01864444411',
            ],
            'testimonials' => [
                'section_title' => 'গ্রাহকদের বাস্তব রিভিউ ও মতামত',
                'items' => [
                    [
                        'name'            => 'তানভীর আহমেদ',
                        'location'        => 'ঢাকা',
                        'rating'          => 5,
                        'review_text'     => 'পণ্যটির কোয়ালিটি অত্যন্ত চমৎকার এবং ডেলিভারি খুব দ্রুত পেয়েছি। ধন্যবাদ!',
                        'photo'           => '',
                        'product_variant' => '১ পিস (স্ট্যান্ডার্ড প্যাক)',
                        'date'            => '১ সেপ্টেম্বর ২০২৬',
                        'is_verified'     => true,
                    ],
                    [
                        'name'            => 'মাহমুদুল হাসান',
                        'location'        => 'চট্টগ্রাম',
                        'rating'          => 5,
                        'review_text'     => 'অরিজিনাল প্রোডাক্ট পেয়েছি। প্যাকেজিং ও সার্ভিস খুবই ভালো। সবার জন্য রিকমেন্ডেড।',
                        'photo'           => '',
                        'product_variant' => '২ পিস (স্পেশাল কম্বো অফার)',
                        'date'            => '১ সেপ্টেম্বর ২০২৬',
                        'is_verified'     => true,
                    ],
                ],
            ],
            'faqs' => [
                'section_title' => 'সাধারণ জিজ্ঞাসা (FAQ)',
                'items' => [
                    [
                        'question' => 'ডেলিভারি পেতে কতদিন সময় লাগবে?',
                        'answer'   => 'ঢাকার ভিতরে ২৪ থেকে ৪৮ ঘণ্টার মধ্যে এবং ঢাকার বাইরে ২ থেকে ৩ কার্যদিবসের মধ্যে ডেলিভারি সম্পন্ন হয়।',
                    ],
                    [
                        'question' => 'পণ্য হাতে পেয়ে মূল্য পরিশোধ করা যাবে?',
                        'answer'   => 'হ্যাঁ, সারাদেশে ক্যাশ অন ডেলিভারি (Cash on Delivery) সুবিধা রয়েছে। পণ্য হাতে পেয়ে মূল্য পরিশোধ করতে পারবেন।',
                    ],
                    [
                        'question' => 'পণ্যটিতে কোনো সমস্যা হলে পরিবর্তনের সুযোগ আছে কি?',
                        'answer'   => 'অবশ্যই! পণ্যটি হাতে পাওয়ার পর কোনো ত্রুটি পরিলক্ষিত হলে আমাদের হেল্পলাইনে জানালে তাৎক্ষণিক পরিবর্তনের ব্যবস্থা করা হবে।',
                    ],
                ],
            ],
            'offer_banner' => [
                'enabled'          => false,
                'title'            => 'সীমিত সময়ের বিশেষ অফার!',
                'subtitle'         => 'আজই অর্ডার করুন এবং উপভোগ করুন আকর্ষণীয় মূল্যছাড় ও ফ্রি ডেলিভারি।',
                'badge'            => 'স্পেশাল ধামাকা অফার',
                'countdown_enable' => false,
                'countdown_end'    => '',
            ],
            'trust_badges' => [
                'section_title' => 'কেন আমাদের ওপর ভরসা রাখবেন?',
                'items' => [
                    ['title' => '১০০% অরিজিনাল প্রোডাক্ট', 'desc' => 'সরাসরি আমদানিকৃত ও মানসম্মত'],
                    ['title' => 'দ্রুততম হোম ডেলিভারি', 'desc' => 'সারাদেশে বিশ্বস্ত কুরিয়ার সার্ভিস'],
                    ['title' => 'নিরাপদ ক্যাশ অন ডেলিভারি', 'desc' => 'পণ্য দেখে টাকা পরিশোধের নিশ্চয়তা'],
                ],
            ],
            'checkout' => [
                'title'                 => 'অর্ডার করতে আপনার সঠিক তথ্য দিয়ে নিচের ফর্মটি সম্পূর্ণ পূরণ করুন।',
                'billing_title'         => 'Billing details',
                'summary_title'         => 'অর্ডারের সারসংক্ষেপ',
                'cod_badge_text'        => 'পণ্য হাতে পেয়ে চেক করে সম্পূর্ণ মূল্য পরিশোধ করুন। অগ্রিম কোনো টাকা দিতে হবে না।',
                'privacy_badge_heading' => 'Google & Gemini Data Privacy Standard',
                'privacy_badge_text'    => 'আপনার তথ্য শতভাগ নিরাপদ ও এনক্রিপ্টেড। আপনার ফোন নম্বর ও ঠিকানা শুধুমাত্র কুরিয়ার ডেলিভারির কাজে সুরক্ষিতভাবে ব্যবহৃত হবে।',
                'order_button_text'     => 'অর্ডার করুন',
                'success_title'         => 'আপনার অর্ডারটি সফল হয়েছে!',
                'success_message'       => 'অর্ডারটি নিশ্চিত করতে আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে ফোনে যোগাযোগ করবেন।',
            ],
            'footer' => [
                'copyright_text' => '© 2026 Growth Agro. All rights reserved.',
                'helpline_phone' => '01864-444411',
                'whatsapp_phone' => '8801864444411',
            ],
        ];
    }

    /**
     * Dispatcher to get default content based on selected template preset
     */
    public static function getDefaultContent(string $template = 'universal', string $productName = ''): array
    {
        if ($template === 'chicken-booster') {
            return self::getDefaultMasterContent();
        }

        return self::getDefaultUniversalContent($productName ?: 'প্রিমিয়াম কোয়ালিটি প্রোডাক্ট');
    }

    /**
     * Default Master Content (Chicken Booster Master Template Data)
     */
    public static function getDefaultMasterContent(): array
    {
        return [
            'header' => [
                'hotline_phone' => '01864-444411',
                'hotline_tel'   => '01864444411',
                'logo_image'    => '/assets/images/chicken-booster-logo.webp',
                'cta_text'      => 'অর্ডার করুন',
            ],
            'hero' => [
                'alert_hook'      => '"মুরগি দুর্বল? রোগে আক্রান্ত? উৎপাদনে ক্ষতি?"',
                'main_title'      => '"রোগমুক্ত, দ্রুত মোটাতাজা ও সুস্থ মুরগি পেতে চান? জানুন প্রাকৃতিক সমাধান"',
                'subtext'         => '৩০০০+ খামারির সফল অভিজ্ঞতা। ব্যবহারের ৩-৫ দিনের মধ্যেই উন্নতি দেখা যায়।',
                'cta_button_text' => '👉 অর্ডার করতে ক্লিক করুন',
                'dual_cards'      => [
                    [
                        'tag'              => 'Layer Booster (১ কেজি)',
                        'product_image'    => '/assets/images/layer-booster-product.webp',
                        'background_image' => '/assets/images/layer-farm-bg.webp',
                        'variant_key'      => 'layer-1kg',
                        'title'            => 'Layer Booster — ক্লিক করে অর্ডার করুন',
                    ],
                    [
                        'tag'              => 'Broiler Booster (১ কেজি)',
                        'product_image'    => '/assets/images/broiler-booster-product.webp',
                        'background_image' => '/assets/images/broiler-farm-bg.webp',
                        'variant_key'      => 'broiler-1kg',
                        'title'            => 'Broiler Booster — ক্লিক করে অর্ডার করুন',
                    ],
                ],
            ],
            'packages' => [
                [
                    'id'               => 'broiler-1kg',
                    'name'             => 'Broiler Booster (১ কেজি)',
                    'price'            => 2300,
                    'old_price'        => 2800,
                    'weight'           => '1 KG',
                    'image'            => '/assets/images/broiler-booster-product.webp',
                    'default_quantity' => 1,
                    'is_active'        => true,
                    'sort_order'       => 1,
                ],
                [
                    'id'               => 'layer-1kg',
                    'name'             => 'Layer Booster (১ কেজি)',
                    'price'            => 2300,
                    'old_price'        => 2800,
                    'weight'           => '1 KG',
                    'image'            => '/assets/images/layer-booster-product.webp',
                    'default_quantity' => 0,
                    'is_active'        => true,
                    'sort_order'       => 2,
                ],
            ],
            'video_reviews' => [
                'section_title' => 'খামারিদের সফলতার গল্প',
                'items' => [
                    [
                        'title'     => 'মুরগির দ্রুত ওজন বৃদ্ধি এবং FCR উন্নত করার বাস্তব অভিজ্ঞতা',
                        'thumbnail' => '/assets/images/review-broiler.webp',
                        'video_url' => '',
                    ],
                    [
                        'title'     => '১৫ দিনে ডিমের উৎপাদন ২০% বৃদ্ধি পাওয়ার খামারি রিভিউ',
                        'thumbnail' => '/assets/images/review-layer.webp',
                        'video_url' => '',
                    ],
                ],
            ],
            'benefits_section_1' => [
                'section_title' => 'Layer Booster-কেন ব্যবহার করবেন?',
                'helpline_text' => 'প্রয়োজনে কল করুন: 01864-444411',
                'helpline_tel'  => '01864444411',
                'items' => [
                    ['title' => 'ডিমের উৎপাদন বৃদ্ধি, রান্ডা উন্নতি এবং খাদ্য সাশ্রয়', 'desc' => ''],
                    ['title' => 'ইনফেকশাস ব্রঙ্কাইটিস (IB):', 'desc' => 'ডিম কমে যায়, পাতলা খোসার ডিম হওয়া প্রতিরোধ করে।'],
                    ['title' => 'ইনফেকশাস কোরাইজা:', 'desc' => 'মুখ ফোলা, নাক দিয়ে পানি পড়া, দুর্গন্ধযুক্ত নিঃসরণ দূর করে।'],
                    ['title' => 'সালমোনেলোসিস:', 'desc' => 'ডিমের খোসার ওপর ব্যাকটেরিয়া আক্রমণ ও পাতলা পায়খানা নিরাময়ে সাহায্য করে।'],
                    ['title' => 'এগ ড্রপ সিন্ড্রোম (Egg Drop Syndrome):', 'desc' => 'ডিমের আকস্মিক উৎপাদন কমে যাওয়া বা বিকৃত আকার রোধ করে।'],
                    ['title' => 'পাতলা খোসার ডিম:', 'desc' => 'ক্যালসিয়াম ও ভিটামিন ডির ঘাটতি দূর করে খোসা মজবুত করে।'],
                    ['title' => 'ডিম খাওয়া (Egg Eating):', 'desc' => 'অপর্যাপ্ত পুষ্টি বা স্ট্রেসের কারণে ডিম খাওয়ার বদভ্যাস বন্ধ করে।'],
                    ['title' => 'ড্রপিং ও বদহজম আমাশয়:', 'desc' => 'ডিমের গুণমান রক্ষা ও হজমশক্তি উন্নত রাখে।'],
                    ['title' => 'এগ বাউণ্ড (Egg Bound):', 'desc' => 'ডিম আটকে যাওয়া প্রতিরোধ করে এবং নিরাপদ প্রসব নিশ্চিত করে।'],
                    ['title' => 'নিউক্যাসেল (Ranikhet):', 'desc' => 'ভাইরাসজনিত হাঁচি, কাশি, ঘাড় মোচড়ানো প্রতিরোধে রোগ প্রতিরোধ ক্ষমতা বাড়ায়।'],
                ],
            ],
            'benefits_section_2' => [
                'section_title' => 'Broiler Booster-কেন ব্যবহার করবেন?',
                'helpline_text' => 'প্রয়োজনে কল করুন: 01864-444411',
                'helpline_tel'  => '01864444411',
                'items' => [
                    ['title' => 'নিউক্যাসেল ডিজিজ (Ranikhet):', 'desc' => 'ভাইরাসজনিত রোগ, হাঁচি, কাশি, ঘাড় মোচড়ানো কার্যকরভাবে প্রতিহত করে।'],
                    ['title' => 'গামবোরো রোগ (IBD):', 'desc' => 'বাচ্চা অবস্থায় রোগ প্রতিরোধ ক্ষমতা দুর্বল হতে দেয় না।'],
                    ['title' => 'সিআরডি (CRD - Chronic Respiratory Disease):', 'desc' => 'শ্বাসকষ্ট, ঘড়ঘড় আওয়াজ ও নাক দিয়ে পানি পড়া দূর করে।'],
                    ['title' => 'কোক্সিডিওসিস (Coccidiosis):', 'desc' => 'রক্তমিশ্রিত পায়খানা ও বাচ্চার শুকিয়ে যাওয়া প্রতিরোধ করে।'],
                    ['title' => 'ল্যামনেস (Lameness বা পা খোঁড়া):', 'desc' => 'বাচ্চার হাড় ও লিগামেন্ট মজবুত করে পা দুর্বলতা দূর করে।'],
                    ['title' => 'পা কাঁপা বা হাঁটার সমস্যা:', 'desc' => 'পুষ্টি উপাদানের অভাব পূরণ করে মুরগির চলনশক্তি সতেজ রাখে।'],
                    ['title' => 'হঠাৎ মৃত্যু সিন্ড্রোম (Sudden Death Syndrome – SDS):', 'desc' => 'হার্ট অ্যাটাক ও হঠাৎ মৃত্যুহার কমায়।'],
                    ['title' => 'দ্রুত ওজন বৃদ্ধি ও খাদ্য সাশ্রয়:', 'desc' => 'খাদ্য রূপান্তর হার (FCR) উন্নত করে কম খাদ্যে সর্বোচ্চ ওজন নিশ্চিত করে।'],
                ],
            ],
            'usage_guide' => [
                'section_title'    => 'ব্যবহার বিধিঃ',
                'image'            => '/assets/images/chicks-feeder.webp',
                'instruction_text' => '১০০০ মুরগির জন্য ১০০ গ্রাম পাউডার পানির সাথে মিশিয়ে ব্যবহার করতে হবে। পরবর্তী দিন থেকে ৫ গ্রাম হারে বেশি ব্যবহার করতে হবে।',
                'helpline_text'    => 'প্রয়োজনে কল করুন: 01864-444411',
                'helpline_tel'     => '01864444411',
            ],
            'testimonials' => [
                'section_title' => 'গ্রাহকদের মতামত ও রিভিউ',
                'items' => [
                    [
                        'name'            => 'মো: রফিকুল ইসলাম',
                        'location'        => 'ময়মনসিংহ',
                        'rating'          => 5,
                        'review_text'     => 'চিকেন বুস্টার ব্যবহার করে ব্রয়লারের ওজন ৪ দিনে ২০০ গ্রাম পর্যন্ত বেড়েছে। এফসিআর অনেক ভালো। ১০০% খাঁটি পণ্য।',
                        'photo'           => '',
                        'product_variant' => 'Broiler Booster (১ কেজি)',
                        'date'            => '১০ ফেব্রুয়ারি, ২০২৬',
                        'is_verified'     => true,
                    ],
                    [
                        'name'            => 'হাজী আনোয়ার হোসেন',
                        'location'        => 'গাজীপুর',
                        'rating'          => 5,
                        'review_text'     => 'লেয়ার মুরগিতে ডিমের পাতলা খোসা ও ড্রপ বন্ধ হয়েছে। ডিমের প্রোডাকশন খুব চমৎকার এসেছে। ধন্যবাদ Growth Agro.',
                        'photo'           => '',
                        'product_variant' => 'Layer Booster (১ কেজি)',
                        'date'            => '১৮ ফেব্রুয়ারি, ২০২৬',
                        'is_verified'     => true,
                    ],
                ],
            ],
            'faqs' => [
                'section_title' => 'সাধারণ জিজ্ঞাসা (FAQ)',
                'items' => [
                    [
                        'question' => 'চিকেন বুস্টার কীভাবে ব্যবহার করতে হয়?',
                        'answer'   => 'প্রতি ১০০০ মুরগির জন্য ১০০ গ্রাম পাউডার পানির সাথে মিশিয়ে খাওয়ানো যায়। বিস্তারিত ব্যবহার বিধি প্যাকেটের গায়ে লেখা রয়েছে।',
                    ],
                    [
                        'question' => 'কত দিনের মধ্যে রেজাল্ট পাওয়া যায়?',
                        'answer'   => 'ব্যবহারের ৩ থেকে ৫ দিনের মধ্যেই মুরগির সক্রিয়তা, ক্ষুধা ও ওজন বৃদ্ধির ইতিবাচক পরিবর্তন লক্ষ্য করা যায়।',
                    ],
                    [
                        'question' => 'ডেলিভারি চার্জ এবং মূল্য কীভাবে পরিশোধ করব?',
                        'answer'   => 'সারাদেশে ক্যাশ অন ডেলিভারি সুবিধা রয়েছে। পণ্য হাতে পেয়ে চেক করে সম্পূর্ণ মূল্য ডেলিভারিম্যানের কাছে পরিশোধ করতে পারবেন।',
                    ],
                ],
            ],
            'offer_banner' => [
                'enabled'          => false,
                'title'            => 'সীমিত সময়ের বিশেষ ছাড় অফার!',
                'subtitle'         => 'আজই অর্ডার করুন এবং পান বিশেষ মূল্যছাড় ও ফ্রি ডেলিভারি।',
                'badge'            => 'স্পেশাল ধামাকা অফার',
                'countdown_enable' => false,
                'countdown_end'    => '',
            ],
            'trust_badges' => [
                'section_title' => 'কেন আমাদের ওপর ভরসা রাখবেন?',
                'items' => [
                    ['title' => '১০০% প্রাকৃতিক ও নিরাপদ', 'desc' => 'কোনো ক্ষতিকর রাসায়নিক নেই'],
                    ['title' => 'দ্রুততম হোম ডেলিভারি', 'desc' => '৪৮-৭২ ঘণ্টার মধ্যে সারাদেশে ডেলিভারি'],
                    ['title' => 'ক্যাশ অন ডেলিভারি', 'desc' => 'পণ্য হাতে পেয়ে মূল্য পরিশোধ করুন'],
                ],
            ],
            'checkout' => [
                'title'                 => 'অর্ডার করতে আপনার সঠিক তথ্য দিয়ে নিচের ফর্মটি সম্পূর্ণ পূরণ করুন।',
                'billing_title'         => 'Billing details',
                'summary_title'         => 'অর্ডারের সারসংক্ষেপ',
                'cod_badge_text'        => 'পণ্য হাতে পেয়ে চেক করে সম্পূর্ণ মূল্য পরিশোধ করুন। অগ্রিম কোনো টাকা দিতে হবে না।',
                'privacy_badge_heading' => 'Google & Gemini Data Privacy Standard',
                'privacy_badge_text'    => 'আপনার তথ্য শতভাগ নিরাপদ ও এনক্রিপ্টেড। আপনার ফোন নম্বর ও ঠিকানা শুধুমাত্র কুরিয়ার ডেলিভারির কাজে সুরক্ষিতভাবে ব্যবহৃত হবে, কোনো তৃতীয় পক্ষের সাথে শেয়ার করা হয় না।',
                'order_button_text'     => 'অর্ডার করুন',
                'success_title'         => 'আপনার অর্ডারটি সফল হয়েছে!',
                'success_message'       => 'অর্ডারটি নিশ্চিত করতে আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে ফোনে যোগাযোগ করবেন।',
            ],
            'footer' => [
                'copyright_text' => '© 2026 Growth Agro (Chicken Booster). All rights reserved.',
                'helpline_phone' => '01864-444411',
                'whatsapp_phone' => '8801864444411',
            ],
        ];
    }

    /**
     * Duplicate a landing page safely
     */
    public function duplicate(): self
    {
        $clone = $this->replicate(['id', 'created_at', 'updated_at']);
        
        $baseSlug = Str::slug($this->name) . '-copy';
        $newSlug = $baseSlug;
        $counter = 1;
        
        while (self::where('slug', $newSlug)->exists()) {
            $newSlug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $clone->name = $this->name . ' (Copy)';
        $clone->slug = $newSlug;
        $clone->status = 'draft';
        $clone->published_at = null;
        $clone->save();

        return $clone;
    }

    /**
     * Scope for published pages
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
