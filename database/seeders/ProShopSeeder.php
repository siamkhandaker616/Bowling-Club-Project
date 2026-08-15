<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProShopSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Strike Pro Reactive Ball',
                'slug' => 'strike-pro-reactive-ball',
                'category' => 'Balls',
                'description' => 'Reactive coverstock for serious hook. 14 lb, thumb-fit and ready to roll.',
                'price' => 9500,
                'stock' => 8,
            ],
            [
                'name' => 'Lane Master Plastic Ball',
                'slug' => 'lane-master-plastic-ball',
                'category' => 'Balls',
                'description' => 'Straight-line plastic starter ball — great for spares and first-time bowlers.',
                'price' => 4200,
                'stock' => 14,
            ],
            [
                'name' => 'Golden Pocket Urethane Ball',
                'slug' => 'golden-pocket-urethane-ball',
                'category' => 'Balls',
                'description' => 'Urethane shell with predictable mid-lane read. 13 lb classic.',
                'price' => 7800,
                'stock' => 5,
            ],
            [
                'name' => 'Pro Grip House Shoes (UK 8)',
                'slug' => 'pro-grip-house-shoes-uk8',
                'category' => 'Shoes',
                'description' => 'Slide-and-brake sole system, breathable upper. Rent or keep — they grip the approach.',
                'price' => 3200,
                'stock' => 20,
            ],
            [
                'name' => 'Pro Grip House Shoes (UK 10)',
                'slug' => 'pro-grip-house-shoes-uk10',
                'category' => 'Shoes',
                'description' => 'Slide-and-brake sole system in a larger fit. Same grip, more room.',
                'price' => 3200,
                'stock' => 16,
            ],
            [
                'name' => 'Tenth Frame Microfiber Towel',
                'slug' => 'tenth-frame-microfiber-towel',
                'category' => 'Accessories',
                'description' => 'Wipe down your ball between shots — keeps oil lines honest.',
                'price' => 650,
                'stock' => 40,
            ],
            [
                'name' => 'Three-Finger Accessory Bag',
                'slug' => 'three-finger-accessory-bag',
                'category' => 'Accessories',
                'description' => 'Roomy side bag for ball, towel, and rosin. Fits most lockers.',
                'price' => 1800,
                'stock' => 25,
            ],
            [
                'name' => 'Rosin Powder Sachet',
                'slug' => 'rosin-powder-sachet',
                'category' => 'Accessories',
                'description' => 'Keeps your grip dry on humid nights. One sachet lasts a season.',
                'price' => 350,
                'stock' => 60,
            ],
            [
                'name' => 'League Night Cap',
                'slug' => 'league-night-cap',
                'category' => 'Apparel',
                'description' => 'Tenth Frame crest on front. For league nights and nothing else.',
                'price' => 900,
                'stock' => 30,
            ],
            [
                'name' => 'Pin Ringer Tee',
                'slug' => 'pin-ringer-tee',
                'category' => 'Apparel',
                'description' => 'Heavy cotton tee with the bowling pin print. Sizes M–2XL.',
                'price' => 1100,
                'stock' => 22,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                array_merge($product, ['image' => null, 'is_active' => true])
            );
        }
    }
}
