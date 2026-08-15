<?php

namespace Database\Seeders;

use App\Models\SnackbarItem;
use Illuminate\Database\Seeder;

class SnackbarSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            'Signature Smoothies' => [
                ['The 7-10 Split', 'Mango, banana, and a drizzle of honey blended with oat milk.'],
                ['Turkey Special', 'Strawberry, yogurt, and a hint of vanilla.'],
                ['Fresh Frame', 'Pineapple, coconut, and lime over crushed ice.'],
            ],
            'Specialty Coffees' => [
                ['Espresso Shot', 'Double shot of house roast, pulled fresh.'],
                ['Cappuccino', 'Espresso, steamed milk, and a cloud of foam.'],
                ['Iced Latte', 'Chilled milk over espresso with your syrup pick.'],
                ['Cold Brew', 'Slow-steeped overnight, served over ice.'],
            ],
            'Fizzy & Fresh' => [
                ['Craft Lemonade', 'Fresh-squeezed, with mint or berry.'],
                ['Cola & Orange Soda', 'Ice-cold, straight from the fountain.'],
                ['Sparkling Water', 'Bubbles with a twist of lime.'],
            ],
            'Game-Day Bites' => [
                ['Loaded Nachos', 'Tortilla chips, cheese sauce, jalapeños, and salsa.'],
                ['Boneless Wings', 'Golden and crispy with your choice of sauce.'],
                ['Soft Pretzels', 'Warm, salted, with cheese dip.'],
                ['Popcorn Buckets', 'Buttered, salted, or caramel.'],
            ],
        ];

        $order = 1;
        foreach ($menu as $category => $items) {
            foreach ($items as [$name, $description]) {
                SnackbarItem::updateOrCreate(
                    ['category' => $category, 'name' => $name],
                    ['description' => $description, 'sort_order' => $order, 'is_active' => true]
                );
                $order++;
            }
        }
    }
}
