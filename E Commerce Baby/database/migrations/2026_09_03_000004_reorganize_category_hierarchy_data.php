<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Reorganize existing uploaded data into hierarchical subcategories non-destructively.
     */
    public function up(): void
    {
        $electronics = Category::where('handle', 'electronics')->first();
        $fashion = Category::where('handle', 'fashion')->first();
        $toysGames = Category::where('handle', 'toys-games')->first();

        // 1. Reassign existing baby & toddler fashion categories under Fashion parent
        if ($fashion) {
            $babyBoy = Category::where('handle', 'baby-boys')->first();
            if ($babyBoy) {
                $babyBoy->update(['parent_id' => $fashion->id, 'sort_order' => 1]);
            }

            $babyGirl = Category::where('handle', 'baby-girl')->first();
            if ($babyGirl) {
                $babyGirl->update(['parent_id' => $fashion->id, 'sort_order' => 2]);
            }

            $maggieSets = Category::where('handle', 'maggie-t-shirt-sets')->first();
            if ($maggieSets) {
                $maggieSets->update(['parent_id' => $fashion->id, 'sort_order' => 3]);
            }

            $winterCol = Category::where('handle', 'winter-collection')->first();
            if ($winterCol) {
                $winterCol->update(['parent_id' => $fashion->id, 'sort_order' => 4]);
            }

            // Create Men's Clothing subcategory
            $mensClothing = Category::firstOrCreate(
                ['handle' => 'mens-clothing'],
                [
                    'parent_id'    => $fashion->id,
                    'title'        => "Men's Clothing",
                    'description'  => "Men's t-shirts, polo shirts, and casual wear.",
                    'sort_order'   => 5,
                    'status'       => true,
                    'image'        => '/images/categories/fashion.svg',
                    'banner_image' => '/images/categories/fashion.svg',
                ]
            );
            if ($mensClothing->parent_id !== $fashion->id) {
                $mensClothing->update(['parent_id' => $fashion->id, 'sort_order' => 5]);
            }

            Product::whereIn('slug', ['mens-cotton-t-shirt', 'premium-polo-shirt', 'mens-casual-hoodie'])
                ->update([
                    'category_id'     => $mensClothing->id,
                    'category_handle' => 'mens-clothing',
                ]);

            // Create Women's Clothing subcategory
            $womensClothing = Category::firstOrCreate(
                ['handle' => 'womens-clothing'],
                [
                    'parent_id'    => $fashion->id,
                    'title'        => "Women's Clothing",
                    'description'  => "Women's casual shirts, tops, and stylish apparel.",
                    'sort_order'   => 6,
                    'status'       => true,
                    'image'        => '/images/categories/fashion.svg',
                    'banner_image' => '/images/categories/fashion.svg',
                ]
            );
            if ($womensClothing->parent_id !== $fashion->id) {
                $womensClothing->update(['parent_id' => $fashion->id, 'sort_order' => 6]);
            }

            Product::whereIn('slug', ['womens-casual-shirt'])
                ->update([
                    'category_id'     => $womensClothing->id,
                    'category_handle' => 'womens-clothing',
                ]);
        }

        // 2. Reorganize Electronics data into appropriate subcategories
        if ($electronics) {
            $audioGear = Category::firstOrCreate(
                ['handle' => 'audio-earphones'],
                [
                    'parent_id'    => $electronics->id,
                    'title'        => 'Audio & Earphones',
                    'description'  => 'True wireless earbuds, headphones, and bluetooth audio gear.',
                    'sort_order'   => 1,
                    'status'       => true,
                    'image'        => '/images/categories/electronics.svg',
                    'banner_image' => '/images/categories/electronics.svg',
                ]
            );
            if ($audioGear->parent_id !== $electronics->id) {
                $audioGear->update(['parent_id' => $electronics->id, 'sort_order' => 1]);
            }

            Product::whereIn('slug', ['wireless-earbuds', 'bluetooth-headphones', 'portable-bluetooth-speaker'])
                ->update([
                    'category_id'     => $audioGear->id,
                    'category_handle' => 'audio-earphones',
                ]);

            $smartWatches = Category::firstOrCreate(
                ['handle' => 'smart-watches'],
                [
                    'parent_id'    => $electronics->id,
                    'title'        => 'Smart Watches',
                    'description'  => 'Smart fitness trackers and biometric smart watches.',
                    'sort_order'   => 2,
                    'status'       => true,
                    'image'        => '/images/categories/electronics.svg',
                    'banner_image' => '/images/categories/electronics.svg',
                ]
            );
            if ($smartWatches->parent_id !== $electronics->id) {
                $smartWatches->update(['parent_id' => $electronics->id, 'sort_order' => 2]);
            }

            Product::whereIn('slug', ['smart-watch-pro-4'])
                ->update([
                    'category_id'     => $smartWatches->id,
                    'category_handle' => 'smart-watches',
                ]);

            $mobileAcc = Category::firstOrCreate(
                ['handle' => 'mobile-accessories'],
                [
                    'parent_id'    => $electronics->id,
                    'title'        => 'Mobile Accessories',
                    'description'  => 'Holders, mounts, and mobile gadgets.',
                    'sort_order'   => 3,
                    'status'       => true,
                    'image'        => '/images/categories/electronics.svg',
                    'banner_image' => '/images/categories/electronics.svg',
                ]
            );
            if ($mobileAcc->parent_id !== $electronics->id) {
                $mobileAcc->update(['parent_id' => $electronics->id, 'sort_order' => 3]);
            }

            Product::whereIn('slug', ['smartphone-holder'])
                ->update([
                    'category_id'     => $mobileAcc->id,
                    'category_handle' => 'mobile-accessories',
                ]);
        }

        // 3. Reassign Backpacks & Toys under Toys & Games
        if ($toysGames) {
            $backpacks = Category::where('handle', 'backpack-toys')->first();
            if ($backpacks) {
                $backpacks->update(['parent_id' => $toysGames->id, 'sort_order' => 1]);
            }
        }
    }

    /**
     * Reverse the migrations safely.
     */
    public function down(): void
    {
        Category::whereIn('handle', ['baby-boys', 'baby-girl', 'maggie-t-shirt-sets', 'winter-collection', 'backpack-toys'])
            ->update(['parent_id' => null]);
    }
};
