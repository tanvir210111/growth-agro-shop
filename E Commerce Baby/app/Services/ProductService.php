<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;

class ProductService
{
    protected array $collections = [];
    protected array $products = [];

    public function __construct()
    {
        $this->initializeData();
    }

    protected function initializeData(): void
    {
        try {
            $dbCategories = Category::where('status', true)->orderBy('sort_order', 'asc')->get();
            if ($dbCategories->isNotEmpty()) {
                $this->collections = $dbCategories->map(function($c) {
                    return [
                        'id' => $c->handle,
                        'title' => $c->title,
                        'handle' => $c->handle,
                        'image' => $c->image,
                        'banner_image' => $c->banner_image,
                        'description' => $c->description,
                        'item_count' => $c->products()->count(),
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            // fallback
        }
        $this->collections = [
            [
                'id' => 'all-collection',
                'title' => 'All Collection',
                'handle' => 'all-collection',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/a4edc212a4e5b4a57d780575907e5a40.jpg?v=1756911712&width=800',
                'banner_image' => 'images/banners/all-collection.jpg',
                'description' => 'Explore our complete selection of premium, ultra-soft baby & toddler clothing sets.',
                'item_count' => 32
            ],
            [
                'id' => 'baby-boys',
                'title' => 'Baby Boy',
                'handle' => 'baby-boys',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/13b462fd13277bf5914e0e4a8c08d994.jpg?v=1756905723&width=800',
                'banner_image' => 'images/banners/baby-boys.jpg',
                'description' => 'Cute, comfy & playful outfits curated specially for charming baby boys.',
                'item_count' => 18
            ],
            [
                'id' => 'baby-girl',
                'title' => 'Baby Girl',
                'handle' => 'baby-girl',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/b9a1d66bf15b260e69d253c83c078552.jpg?v=1756905200&width=800',
                'banner_image' => 'images/banners/baby-girl.jpg',
                'description' => 'Charming floral sets, ruffle tops, frocks and adorable outfits for baby girls.',
                'item_count' => 16
            ],
            [
                'id' => 'new-arrival',
                'title' => 'New Arrival',
                'handle' => 'new-arrival',
                'image' => 'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                'banner_image' => 'images/banners/new-arrival.jpg',
                'description' => 'Fresh designs and new trendy arrivals for this season.',
                'item_count' => 12
            ],
            [
                'id' => 'maggie-t-shirt-sets',
                'title' => 'Maggie T-Shirt Sets',
                'handle' => 'maggie-t-shirt-sets',
                'image' => 'https://kidoriabd.com/cdn/shop/files/Bear_Printed_T-Shirt_Set.png?v=1781543299&width=800',
                'banner_image' => 'images/banners/maggie-t-shirt-sets.jpg',
                'description' => 'Breathable 100% combed cotton sleeveless & short sleeve sets.',
                'item_count' => 14
            ],
            [
                'id' => 'winter-collection',
                'title' => 'Winter Collection',
                'handle' => 'winter-collection',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/Winter_Collection.png?v=1763392918&width=800',
                'banner_image' => 'images/banners/winter-collection.jpg',
                'description' => 'Cozy blazers, wide leg sets, jackets and warm layers.',
                'item_count' => 8
            ],
            [
                'id' => 'clearance-sale',
                'title' => 'Clearance Sale',
                'handle' => 'clearance-sale',
                'image' => 'https://kidoriabd.com/cdn/shop/collections/25.png?v=1756911894&width=800',
                'banner_image' => 'images/banners/clearance-sale.jpg',
                'description' => 'Special discounted prices with up to 40% OFF on selected stock.',
                'item_count' => 10
            ],
            [
                'id' => 'backpack-toys',
                'title' => 'Backpacks & Toys',
                'handle' => 'backpack-toys',
                'image' => 'https://kidoriabd.com/cdn/shop/files/63.png?v=1756903438&width=800',
                'banner_image' => 'images/banners/backpack-toys.jpg',
                'description' => 'Fun preschool backpacks, plush soft toys and educational items.',
                'item_count' => 6
            ]
        ];

        $this->products = [
            [
                'id' => 1,
                'title' => 'Girls Red Butterfly Printed T-Shirt & Floral Shorts 2-Piece Set',
                'slug' => 'girls-red-butterfly-printed-t-shirt-floral-shorts-set',
                'sku' => 'BFB-0152D',
                'price' => 790,
                'original_price' => 950,
                'discount_percent' => 17,
                'category_handle' => 'baby-girl',
                'category_name' => 'Baby Girl',
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => true,
                'rating' => 4.9,
                'reviews_count' => 24,
                'stock' => 18,
                'sizes' => ['3-6 Months', '6-12 Months', '1-2 Years', '2-3 Years', '3-4 Years', '4-5 Years'],
                'colors' => ['Red/Floral', 'Pastel Pink'],
                'primary_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                'secondary_image' => 'https://kidoriabd.com/cdn/shop/files/zdxgfgd.png?v=1771917728&width=800',
                'gallery' => [
                    'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                    'https://kidoriabd.com/cdn/shop/files/zdxgfgd.png?v=1771917728&width=800',
                    'https://kidoriabd.com/cdn/shop/files/Layer_13.jpg?v=1787037596&width=800'
                ],
                'short_description' => 'Super soft 100% breathable organic cotton 2-piece summer outfit with butterfly embroidery and blooming floral shorts.',
                'description' => '<p>Dress your little princess in this enchanting <strong>2-Piece Butterfly & Floral Summer Set</strong> from Baby Fashion BD. Crafted with high-grade breathable organic combed cotton, this outfit ensures all-day comfort, skin softness, and high freedom of movement.</p><ul><li><strong>Material:</strong> 100% Combed Breathable Cotton</li><li><strong>Waistband:</strong> Elasticized soft waistband (no tight marks)</li><li><strong>Care:</strong> Machine wash cold, gentle cycle</li><li><strong>Origin:</strong> Crafted with love in Bangladesh</li></ul>'
            ],
            [
                'id' => 2,
                'title' => 'Boys & Girls Cute Duck T-Shirt & Shorts 2Pcs Set',
                'slug' => 'boys-girls-cute-duck-t-shirt-shorts-2pcs-set',
                'sku' => 'BFB-0073',
                'price' => 750,
                'original_price' => 890,
                'discount_percent' => 15,
                'category_handle' => 'baby-boys',
                'category_name' => 'Baby Boy',
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => true,
                'rating' => 4.8,
                'reviews_count' => 31,
                'stock' => 22,
                'sizes' => ['3-6 Months', '6-12 Months', '1-2 Years', '2-3 Years', '3-4 Years', '4-5 Years'],
                'colors' => ['Sunny Yellow', 'Baby Blue'],
                'primary_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                'secondary_image' => 'https://kidoriabd.com/cdn/shop/files/zdxgfgd.png?v=1771917728&width=800',
                'gallery' => [
                    'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                    'https://kidoriabd.com/cdn/shop/files/zdxgfgd.png?v=1771917728&width=800'
                ],
                'short_description' => 'Bright and playful unisex duckling print t-shirt paired with ultra-comfy stretchable shorts.',
                'description' => '<p>A best-seller for outdoor play and daily casual wear! Featuring an adorable cheerful duck graphic, non-toxic eco-friendly dyes, and reinforced seams for endless wash durability.</p>'
            ],
            [
                'id' => 3,
                'title' => 'Boys & Girls Bear Printed T-Shirt and Shorts 2Pcs Set',
                'slug' => 'boys-girls-bear-printed-t-shirt-and-shorts-set',
                'sku' => 'BFB-0062',
                'price' => 690,
                'original_price' => 850,
                'discount_percent' => 19,
                'category_handle' => 'maggie-t-shirt-sets',
                'category_name' => 'Maggie T-Shirt Sets',
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => false,
                'rating' => 5.0,
                'reviews_count' => 45,
                'stock' => 15,
                'sizes' => ['6-12 Months', '1-2 Years', '2-3 Years', '3-4 Years', '4-5 Years'],
                'colors' => ['Oatmeal/Brown', 'Sage Green'],
                'primary_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_13.jpg?v=1787037596&width=800',
                'secondary_image' => 'https://kidoriabd.com/cdn/shop/files/Bear_Printed_T-Shirt_Set.png?v=1781543299&width=800',
                'gallery' => [
                    'https://kidoriabd.com/cdn/shop/files/Layer_13.jpg?v=1787037596&width=800',
                    'https://kidoriabd.com/cdn/shop/files/Bear_Printed_T-Shirt_Set.png?v=1781543299&width=800'
                ],
                'short_description' => 'Cozy bear illustrations with contrast trim neck and soft terry cotton shorts.',
                'description' => '<p>Give your child cozy comfort with this bear print matching set. Perfect for daytime adventures, naps, and family outings.</p>'
            ],
            [
                'id' => 4,
                'title' => 'Baby Boy Blue Colorblock T-Shirt & Shorts 2Pcs Set',
                'slug' => 'baby-boy-blue-colorblock-t-shirt-shorts-set',
                'sku' => 'BFB-0074',
                'price' => 720,
                'original_price' => 890,
                'discount_percent' => 19,
                'category_handle' => 'baby-boys',
                'category_name' => 'Baby Boy',
                'is_featured' => true,
                'is_bestseller' => false,
                'is_new' => true,
                'rating' => 4.7,
                'reviews_count' => 19,
                'stock' => 25,
                'sizes' => ['3-6 Months', '6-12 Months', '1-2 Years', '2-3 Years', '3-4 Years'],
                'colors' => ['Ocean Blue', 'Sky & Navy'],
                'primary_image' => 'https://kidoriabd.com/cdn/shop/files/63.png?v=1756903438&width=800',
                'secondary_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                'gallery' => [
                    'https://kidoriabd.com/cdn/shop/files/63.png?v=1756903438&width=800'
                ],
                'short_description' => 'Modern colorblocked athletic styling in soft baby-friendly cotton fabric.',
                'description' => '<p>Sporty, dynamic, and easy to wash. Features snap buttons at neck for effortless dressing over baby’s head.</p>'
            ],
            [
                'id' => 5,
                'title' => 'Girls Disney Frozen Elsa Ruffle Top & Shorts Set',
                'slug' => 'girls-disney-frozen-elsa-ruffle-top-shorts-set',
                'sku' => 'BFB-0192',
                'price' => 850,
                'original_price' => 1050,
                'discount_percent' => 19,
                'category_handle' => 'baby-girl',
                'category_name' => 'Baby Girl',
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => true,
                'rating' => 4.9,
                'reviews_count' => 52,
                'stock' => 14,
                'sizes' => ['1-2 Years', '2-3 Years', '3-4 Years', '4-5 Years', '5-6 Years'],
                'colors' => ['Ice Blue', 'Frozen Lilac'],
                'primary_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_13.jpg?v=1787037596&width=800',
                'secondary_image' => 'https://kidoriabd.com/cdn/shop/files/zdxgfgd.png?v=1771917728&width=800',
                'gallery' => [
                    'https://kidoriabd.com/cdn/shop/files/Layer_13.jpg?v=1787037596&width=800',
                    'https://kidoriabd.com/cdn/shop/files/zdxgfgd.png?v=1771917728&width=800'
                ],
                'short_description' => 'Magical flutter ruffles with dazzling fairytale artwork and soft cotton lining.',
                'description' => '<p>A dream outfit for your princess. Features ruffle shoulder sleeves and vibrant fairytale styling.</p>'
            ],
            [
                'id' => 6,
                'title' => 'Girls Premium Blazer and Wide Leg Pants Set (Brown)',
                'slug' => 'girls-premium-blazer-wide-leg-pants-set-brown',
                'sku' => 'BFB-0210',
                'price' => 1250,
                'original_price' => 1550,
                'discount_percent' => 20,
                'category_handle' => 'winter-collection',
                'category_name' => 'Winter Collection',
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => false,
                'rating' => 4.8,
                'reviews_count' => 18,
                'stock' => 9,
                'sizes' => ['2-3 Years', '3-4 Years', '4-5 Years', '5-6 Years'],
                'colors' => ['Warm Brown', 'Caramel'],
                'primary_image' => 'https://kidoriabd.com/cdn/shop/files/Bear_Printed_T-Shirt_Set.png?v=1781543299&width=800',
                'secondary_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_5_0e764c19-6180-45bf-941e-3e34551a35ae.jpg?v=1787037654&width=800',
                'gallery' => [
                    'https://kidoriabd.com/cdn/shop/files/Bear_Printed_T-Shirt_Set.png?v=1781543299&width=800'
                ],
                'short_description' => 'Elegant smart-casual winter set featuring tailored blazer and comfortable wide leg trousers.',
                'description' => '<p>Turn heads at every party with this high-fashion baby blazer suit set. Tailored to perfection with gentle inner lining.</p>'
            ],
            [
                'id' => 7,
                'title' => 'Girls Black Color Bow Printed T-Shirt & Shorts Set',
                'slug' => 'girls-black-color-bow-printed-t-shirt-shorts-set',
                'sku' => 'BFB-0190',
                'price' => 650,
                'original_price' => 790,
                'discount_percent' => 18,
                'category_handle' => 'clearance-sale',
                'category_name' => 'Clearance Sale',
                'is_featured' => false,
                'is_bestseller' => true,
                'is_new' => false,
                'rating' => 4.6,
                'reviews_count' => 15,
                'stock' => 8,
                'sizes' => ['6-12 Months', '1-2 Years', '2-3 Years', '3-4 Years'],
                'colors' => ['Midnight Black', 'Classic Mono'],
                'primary_image' => 'https://kidoriabd.com/cdn/shop/files/zdxgfgd.png?v=1771917728&width=800',
                'secondary_image' => 'https://kidoriabd.com/cdn/shop/files/Layer_13.jpg?v=1787037596&width=800',
                'gallery' => [
                    'https://kidoriabd.com/cdn/shop/files/zdxgfgd.png?v=1771917728&width=800'
                ],
                'short_description' => 'Cute bow graphic tee with comfortable elastic casual shorts.',
                'description' => '<p>Minimalist, classy, and extremely soft on sensitive toddler skin.</p>'
            ],
            [
                'id' => 8,
                'title' => 'Cute Animal Toddler Preschool Backpack with Safety Leash',
                'slug' => 'cute-animal-toddler-preschool-backpack',
                'sku' => 'BFB-BAG01',
                'price' => 890,
                'original_price' => 1150,
                'discount_percent' => 22,
                'category_handle' => 'backpack-toys',
                'category_name' => 'Backpacks & Toys',
                'is_featured' => false,
                'is_bestseller' => true,
                'is_new' => true,
                'rating' => 4.9,
                'reviews_count' => 38,
                'stock' => 12,
                'sizes' => ['One Size (1-5 Years)'],
                'colors' => ['Fox Orange', 'Bunny Pink', 'Bear Brown'],
                'primary_image' => 'https://kidoriabd.com/cdn/shop/files/63.png?v=1756903438&width=800',
                'secondary_image' => 'https://kidoriabd.com/cdn/shop/files/Bear_Printed_T-Shirt_Set.png?v=1781543299&width=800',
                'gallery' => [
                    'https://kidoriabd.com/cdn/shop/files/63.png?v=1756903438&width=800'
                ],
                'short_description' => 'Lightweight ergonomic backpack for nursery and outings with safety harness strap.',
                'description' => '<p>Keep your toddler safe and delighted with this cute plush backpack. Water-resistant and breathable padded shoulder straps.</p>'
            ]
        ];
    }

    public function getCollections(): array
    {
        return $this->collections;
    }

    public function getCollectionByHandle(string $handle): ?array
    {
        foreach ($this->collections as $col) {
            if ($col['handle'] === $handle) {
                return $col;
            }
        }
        return null;
    }

    public function getAllProducts(): array
    {
        return $this->products;
    }

    public function getFeaturedProducts(int $limit = 8): array
    {
        $featured = array_filter($this->products, fn($p) => !empty($p['is_featured']));
        return array_slice(array_values($featured), 0, $limit);
    }

    public function getBestsellers(int $limit = 8): array
    {
        $bestsellers = array_filter($this->products, fn($p) => !empty($p['is_bestseller']));
        return array_slice(array_values($bestsellers), 0, $limit);
    }

    public function getNewArrivals(int $limit = 8): array
    {
        $newArrivals = array_filter($this->products, fn($p) => !empty($p['is_new']));
        return array_slice(array_values($newArrivals), 0, $limit);
    }

    public function getProductsByCollection(string $handle, ?string $sort = null, ?int $minPrice = null, ?int $maxPrice = null): array
    {
        if ($handle === 'all-collection' || $handle === 'frontpage') {
            $filtered = $this->products;
        } else {
            $filtered = array_filter($this->products, fn($p) => ($p['category_handle'] === $handle));
        }

        if ($minPrice !== null) {
            $filtered = array_filter($filtered, fn($p) => $p['price'] >= $minPrice);
        }
        if ($maxPrice !== null) {
            $filtered = array_filter($filtered, fn($p) => $p['price'] <= $maxPrice);
        }

        $filtered = array_values($filtered);

        if ($sort === 'price_asc') {
            usort($filtered, fn($a, $b) => $a['price'] <=> $b['price']);
        } elseif ($sort === 'price_desc') {
            usort($filtered, fn($a, $b) => $b['price'] <=> $a['price']);
        } elseif ($sort === 'newest') {
            usort($filtered, fn($a, $b) => ($b['is_new'] ? 1 : 0) <=> ($a['is_new'] ? 1 : 0));
        } elseif ($sort === 'rating') {
            usort($filtered, fn($a, $b) => $b['rating'] <=> $a['rating']);
        }

        return $filtered;
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->products as $p) {
            if ($p['slug'] === $slug) {
                return $p;
            }
        }
        return null;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->products as $p) {
            if ($p['id'] === $id) {
                return $p;
            }
        }
        return null;
    }

    public function search(string $query, int $limit = 10): array
    {
        $q = mb_strtolower(trim($query));
        if (empty($q)) {
            return [];
        }

        $results = array_filter($this->products, function ($p) use ($q) {
            return str_contains(mb_strtolower($p['title']), $q) ||
                   str_contains(mb_strtolower($p['category_name']), $q) ||
                   str_contains(mb_strtolower($p['sku']), $q) ||
                   str_contains(mb_strtolower($p['short_description']), $q);
        });

        return array_slice(array_values($results), 0, $limit);
    }
}
