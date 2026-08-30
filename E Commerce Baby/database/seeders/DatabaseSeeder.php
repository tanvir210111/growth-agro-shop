<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Slider;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin Account
        Admin::updateOrCreate(
            ['email' => 'captaincrown@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Aziz625713'),
                'role' => 'super_admin',
                'avatar' => null,
            ]
        );

        // 2. Settings
        $settings = [
            'store_name' => 'Baby Fashion BD',
            'store_phone' => '01560-016740',
            'store_email' => 'support@babyfashionbd.com',
            'store_address' => 'Level 3, Block D, Bashundhara R/A, Dhaka-1229, Bangladesh',
            'delivery_inside_dhaka' => '70',
            'delivery_outside_dhaka' => '130',
            'free_delivery_threshold' => '3000',
            'currency_symbol' => '৳',
            'order_prefix' => 'BFB-',
            'meta_title' => 'Baby Fashion BD - Premium Soft Baby & Toddler Outfits in Bangladesh',
            'meta_description' => 'Baby Fashion BD is Bangladesh trusted online destination for 100% breathable organic cotton baby clothing sets, rompers, frocks, t-shirts, and winter outfits.',
        ];

        foreach ($settings as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        // 3. Categories
        $categoriesData = [
            [
                'title' => 'All Collection',
                'handle' => 'all-collection',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/a4edc212a4e5b4a57d780575907e5a40.jpg?v=1756911712&width=800',
                'banner_image' => 'images/banners/all-collection.jpg',
                'description' => 'Explore our complete selection of premium, ultra-soft baby & toddler clothing sets.',
                'sort_order' => 1
            ],
            [
                'title' => 'Baby Boy',
                'handle' => 'baby-boys',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/13b462fd13277bf5914e0e4a8c08d994.jpg?v=1756905723&width=800',
                'banner_image' => 'images/banners/baby-boys.jpg',
                'description' => 'Cute, comfy & playful outfits curated specially for charming baby boys.',
                'sort_order' => 2
            ],
            [
                'title' => 'Baby Girl',
                'handle' => 'baby-girl',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/b9a1d66bf15b260e69d253c83c078552.jpg?v=1756905200&width=800',
                'banner_image' => 'images/banners/baby-girl.jpg',
                'description' => 'Charming floral sets, ruffle tops, frocks and adorable outfits for baby girls.',
                'sort_order' => 3
            ],
            [
                'title' => 'New Arrival',
                'handle' => 'new-arrival',
                'image' => 'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                'banner_image' => 'images/banners/new-arrival.jpg',
                'description' => 'Fresh designs and new trendy arrivals for this season.',
                'sort_order' => 4
            ],
            [
                'title' => 'Maggie T-Shirt Sets',
                'handle' => 'maggie-t-shirt-sets',
                'image' => 'https://kidoriabd.com/cdn/shop/files/Bear_Printed_T-Shirt_Set.png?v=1781543299&width=800',
                'banner_image' => 'images/banners/maggie-t-shirt-sets.jpg',
                'description' => 'Breathable 100% combed cotton sleeveless & short sleeve sets.',
                'sort_order' => 5
            ],
            [
                'title' => 'Winter Collection',
                'handle' => 'winter-collection',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/Winter_Collection.png?v=1763392918&width=800',
                'banner_image' => 'images/banners/winter-collection.jpg',
                'description' => 'Cozy blazers, wide leg sets, jackets and warm layers.',
                'sort_order' => 6
            ],
            [
                'title' => 'Clearance Sale',
                'handle' => 'clearance-sale',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/25.png?v=1756911894&width=800',
                'banner_image' => 'images/banners/clearance-sale.jpg',
                'description' => 'Special discounted prices with up to 40% OFF on selected stock.',
                'sort_order' => 7
            ],
            [
                'title' => 'Backpacks & Toys',
                'handle' => 'backpack-toys',
                'image' => 'https://kidoriabd.com/cdn/shop/files/63.png?v=1756903438&width=800',
                'banner_image' => 'images/banners/backpack-toys.jpg',
                'description' => 'Fun preschool backpacks, plush soft toys and educational items.',
                'sort_order' => 8
            ]
        ];

        $categoryMap = [];
        foreach ($categoriesData as $c) {
            $cat = Category::updateOrCreate(
                ['handle' => $c['handle']],
                [
                    'title' => $c['title'],
                    'image' => $c['image'],
                    'banner_image' => $c['banner_image'],
                    'description' => $c['description'],
                    'sort_order' => $c['sort_order'],
                    'status' => true
                ]
            );
            $categoryMap[$c['handle']] = $cat->id;
        }

        // 4. Sliders (Hero Banners)
        foreach ($categoriesData as $idx => $c) {
            Slider::updateOrCreate(
                ['link' => '/collections/' . $c['handle']],
                [
                    'title' => $c['title'],
                    'subtitle' => $c['description'],
                    'image' => $c['banner_image'],
                    'button_text' => 'SHOP NOW >',
                    'sort_order' => $idx + 1,
                    'status' => true
                ]
            );
        }

        // 5. Products Catalog
        $productsData = [
            [
                'title' => 'Pastel Dino Cotton Romper Set',
                'slug' => 'pastel-dino-cotton-romper-set',
                'sku' => 'BFB-DR-01',
                'category_handle' => 'baby-boys',
                'regular_price' => 850,
                'sale_price' => 590,
                'cost_price' => 320,
                'stock' => 45,
                'featured_image' => 'https://kidoriabd.com/cdn/shop/files/dino-baby-romper.jpg?v=1787037654&width=800',
                'hover_image' => 'https://kidoriabd.com/cdn/shop/files/dino-baby-romper-2.jpg?v=1787037654&width=800',
                'sizes' => ['0-3M', '3-6M', '6-12M', '1-2Y'],
                'short_description' => 'Ultra-breathable 100% pure organic cotton snap-button romper with cute dinosaur prints.',
                'description' => 'Made from certified 100% pure combed organic cotton, this adorable Dino Romper Set is gentle on delicate baby skin. Includes convenient nickel-free bottom snaps for hassle-free diaper changes and expandable lap shoulders.',
                'is_featured' => true,
                'is_new_arrival' => true,
                'is_bestseller' => true,
                'is_clearance' => false,
            ],
            [
                'title' => 'Floral Meadow Ruffle Top & Bloomer Set',
                'slug' => 'floral-meadow-ruffle-top-bloomer-set',
                'sku' => 'BFB-FL-02',
                'category_handle' => 'baby-girl',
                'regular_price' => 950,
                'sale_price' => 690,
                'cost_price' => 380,
                'stock' => 38,
                'featured_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                'hover_image' => 'https://kidoriabd.com/cdn/shop/files/floral-baby-dress.jpg?v=1787037654&width=800',
                'sizes' => ['3-6M', '6-12M', '1-2Y', '2-3Y'],
                'short_description' => 'Charming pastel botanical floral set with flutter ruffle sleeves and matching elastic bloomer shorts.',
                'description' => 'Soft, breathable, and picture-perfect for warm days and family outings. Crafted with skin-friendly hypoallergenic fabric with a sweet bow headband included.',
                'is_featured' => true,
                'is_new_arrival' => true,
                'is_bestseller' => true,
                'is_clearance' => false,
            ],
            [
                'title' => 'Teddy Bear Maggie T-Shirt & Shorts Set',
                'slug' => 'teddy-bear-maggie-t-shirt-shorts-set',
                'sku' => 'BFB-MG-03',
                'category_handle' => 'maggie-t-shirt-sets',
                'regular_price' => 750,
                'sale_price' => 490,
                'cost_price' => 260,
                'stock' => 60,
                'featured_image' => 'https://kidoriabd.com/cdn/shop/files/Bear_Printed_T-Shirt_Set.png?v=1781543299&width=800',
                'hover_image' => 'https://kidoriabd.com/cdn/shop/files/teddy-shorts-back.png?v=1781543299&width=800',
                'sizes' => ['6-12M', '1-2Y', '2-3Y', '3-4Y'],
                'short_description' => 'Lightweight sleeveless cartoon graphic tee with soft elasticated drawstring shorts.',
                'description' => 'Perfect daily summer playwear designed to keep active toddlers cool, happy, and comfortable all day long.',
                'is_featured' => true,
                'is_new_arrival' => false,
                'is_bestseller' => true,
                'is_clearance' => false,
            ],
            [
                'title' => 'Safari Animals Organic Bodysuit (Pack of 3)',
                'slug' => 'safari-animals-organic-bodysuit-pack-3',
                'sku' => 'BFB-SF-04',
                'category_handle' => 'all-collection',
                'regular_price' => 1250,
                'sale_price' => 890,
                'cost_price' => 510,
                'stock' => 50,
                'featured_image' => 'https://kidoriabd.com/cdn/shop/collections/13b462fd13277bf5914e0e4a8c08d994.jpg?v=1756905723&width=800',
                'hover_image' => 'https://kidoriabd.com/cdn/shop/files/safari-bodysuit-2.jpg?v=1756905723&width=800',
                'sizes' => ['0-3M', '3-6M', '6-12M', '12-18M'],
                'short_description' => 'Value 3-pack organic cotton short-sleeve bodysuits featuring Lion, Giraffe & Elephant prints.',
                'description' => 'Made with 100% GOTS-certified organic ribbed cotton. Super soft, stretchable, and designed for maximum newborn comfort.',
                'is_featured' => true,
                'is_new_arrival' => true,
                'is_bestseller' => false,
                'is_clearance' => false,
            ],
            [
                'title' => 'Nordic Cozy Knitted Cardigan & Pant Set',
                'slug' => 'nordic-cozy-knitted-cardigan-pant-set',
                'sku' => 'BFB-WN-05',
                'category_handle' => 'winter-collection',
                'regular_price' => 1450,
                'sale_price' => 1050,
                'cost_price' => 620,
                'stock' => 25,
                'featured_image' => 'https://kidoriabd.com/cdn/shop/collections/Winter_Collection.png?v=1763392918&width=800',
                'hover_image' => 'https://kidoriabd.com/cdn/shop/files/winter-cardigan-2.png?v=1763392918&width=800',
                'sizes' => ['6-12M', '1-2Y', '2-3Y', '3-4Y'],
                'short_description' => 'Warm woolen knit cardigan with wood buttons and matching thermal winter pants.',
                'description' => 'Cozy, gentle-on-skin knitted winter warmth for chilly days, weddings, and holiday outings.',
                'is_featured' => false,
                'is_new_arrival' => false,
                'is_bestseller' => false,
                'is_clearance' => false,
            ],
            [
                'title' => 'Plush 3D Animal Toddler Backpack',
                'slug' => 'plush-3d-animal-toddler-backpack',
                'sku' => 'BFB-BP-06',
                'category_handle' => 'backpack-toys',
                'regular_price' => 850,
                'sale_price' => 550,
                'cost_price' => 290,
                'stock' => 30,
                'featured_image' => 'https://kidoriabd.com/cdn/shop/files/63.png?v=1756903438&width=800',
                'hover_image' => 'https://kidoriabd.com/cdn/shop/files/backpack-lion-side.png?v=1756903438&width=800',
                'sizes' => ['Standard'],
                'short_description' => 'Ultra-lightweight soft plush animal character nursery bag with safety harness strap.',
                'description' => 'Features padded adjustable shoulder straps, durable smooth zippers, and water-resistant lining. Ideal for preschool, travel, and daycare.',
                'is_featured' => true,
                'is_new_arrival' => true,
                'is_bestseller' => false,
                'is_clearance' => false,
            ],
            [
                'title' => 'Pastel Striped Pocket Polo & Chino Shorts',
                'slug' => 'pastel-striped-pocket-polo-chino-shorts',
                'sku' => 'BFB-PL-07',
                'category_handle' => 'baby-boys',
                'regular_price' => 990,
                'sale_price' => 690,
                'cost_price' => 390,
                'stock' => 40,
                'featured_image' => 'https://kidoriabd.com/cdn/shop/collections/13b462fd13277bf5914e0e4a8c08d994.jpg?v=1756905723&width=800',
                'hover_image' => 'https://kidoriabd.com/cdn/shop/files/polo-chino-2.jpg?v=1756905723&width=800',
                'sizes' => ['1-2Y', '2-3Y', '3-4Y', '4-5Y'],
                'short_description' => 'Smart casual collared polo t-shirt with roll-up chino cotton shorts.',
                'description' => 'Tailored for festive gatherings and family parties with breathable fabric and soft elasticated waist.',
                'is_featured' => false,
                'is_new_arrival' => false,
                'is_bestseller' => true,
                'is_clearance' => false,
            ],
            [
                'title' => 'Daisy Embroidered Cotton Frock with Bonnet',
                'slug' => 'daisy-embroidered-cotton-frock-bonnet',
                'sku' => 'BFB-DS-08',
                'category_handle' => 'baby-girl',
                'regular_price' => 1100,
                'sale_price' => 790,
                'cost_price' => 430,
                'stock' => 35,
                'featured_image' => 'https://kidoriabd.com/cdn/shop/collections/b9a1d66bf15b260e69d253c83c078552.jpg?v=1756905200&width=800',
                'hover_image' => 'https://kidoriabd.com/cdn/shop/files/daisy-dress-back.jpg?v=1756905200&width=800',
                'sizes' => ['0-6M', '6-12M', '1-2Y', '2-3Y'],
                'short_description' => 'Delicate daisy flower hand-embroidery on pure soft cotton with matching summer bonnet hat.',
                'description' => 'Vintage charm meets modern baby comfort. Includes breathable inner lining and easy back button fastening.',
                'is_featured' => true,
                'is_new_arrival' => false,
                'is_bestseller' => true,
                'is_clearance' => true,
            ]
        ];

        foreach ($productsData as $p) {
            $catId = $categoryMap[$p['category_handle']] ?? null;
            Product::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'title' => $p['title'],
                    'sku' => $p['sku'],
                    'category_id' => $catId,
                    'category_handle' => $p['category_handle'],
                    'regular_price' => $p['regular_price'],
                    'sale_price' => $p['sale_price'],
                    'cost_price' => $p['cost_price'],
                    'stock' => $p['stock'],
                    'featured_image' => $p['featured_image'],
                    'hover_image' => $p['hover_image'],
                    'gallery_images' => [$p['featured_image'], $p['hover_image']],
                    'sizes' => $p['sizes'],
                    'short_description' => $p['short_description'],
                    'description' => $p['description'],
                    'is_featured' => $p['is_featured'],
                    'is_new_arrival' => $p['is_new_arrival'],
                    'is_bestseller' => $p['is_bestseller'],
                    'is_clearance' => $p['is_clearance'],
                    'status' => true,
                ]
            );
        }

        // 6. Seed Sample Orders for Admin Demonstration
        $order1 = Order::updateOrCreate(
            ['invoice_no' => 'BFB-260825-A1092'],
            [
                'customer_name' => 'Tania Rahman',
                'customer_phone' => '01712345678',
                'customer_address' => 'House 14, Road 7, Sector 3, Uttara, Dhaka',
                'city_type' => 'inside_dhaka',
                'delivery_charge' => 70,
                'subtotal' => 1280,
                'discount' => 0,
                'total_amount' => 1350,
                'payment_method' => 'COD',
                'status' => 'pending',
                'note' => 'Please deliver in the afternoon.',
                'created_at' => now()->subHours(2),
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order1->id, 'product_name' => 'Pastel Dino Cotton Romper Set'],
            [
                'product_image' => 'https://kidoriabd.com/cdn/shop/files/dino-baby-romper.jpg?v=1787037654&width=800',
                'size' => '3-6M',
                'price' => 590,
                'quantity' => 1,
                'total' => 590
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order1->id, 'product_name' => 'Floral Meadow Ruffle Top & Bloomer Set'],
            [
                'product_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                'size' => '6-12M',
                'price' => 690,
                'quantity' => 1,
                'total' => 690
            ]
        );

        $order2 = Order::updateOrCreate(
            ['invoice_no' => 'BFB-260824-B8314'],
            [
                'customer_name' => 'Mahmudul Hasan',
                'customer_phone' => '01898765432',
                'customer_address' => 'Chittagong GEC Circle, Nasirabad, Chattogram',
                'city_type' => 'outside_dhaka',
                'delivery_charge' => 130,
                'subtotal' => 890,
                'discount' => 0,
                'total_amount' => 1020,
                'payment_method' => 'COD',
                'status' => 'processing',
                'note' => 'Call before delivery.',
                'created_at' => now()->subDay(),
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order2->id, 'product_name' => 'Safari Animals Organic Bodysuit (Pack of 3)'],
            [
                'product_image' => 'https://kidoriabd.com/cdn/shop/collections/13b462fd13277bf5914e0e4a8c08d994.jpg?v=1756905723&width=800',
                'size' => '3-6M',
                'price' => 890,
                'quantity' => 1,
                'total' => 890
            ]
        );

        $order3 = Order::updateOrCreate(
            ['invoice_no' => 'BFB-260823-C4491'],
            [
                'customer_name' => 'Farhana Akter',
                'customer_phone' => '01911223344',
                'customer_address' => 'Flat 4B, Dhanmondi 27, Dhaka',
                'city_type' => 'inside_dhaka',
                'delivery_charge' => 70,
                'subtotal' => 1050,
                'discount' => 0,
                'total_amount' => 1120,
                'payment_method' => 'COD',
                'status' => 'delivered',
                'note' => '',
                'created_at' => now()->subDays(2),
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order3->id, 'product_name' => 'Nordic Cozy Knitted Cardigan & Pant Set'],
            [
                'product_image' => 'https://kidoriabd.com/cdn/shop/collections/Winter_Collection.png?v=1763392918&width=800',
                'size' => '1-2Y',
                'price' => 1050,
                'quantity' => 1,
                'total' => 1050
            ]
        );
    }
}
