<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;

class InventoryCategoriesSeeder extends Seeder
{
    public const CATEGORY_MAP = [
        'footwear' => 'rental_shoes',
        'oil' => 'oil_supplies',
        'gear' => 'balls',
        'cleaning' => 'cleaning_supplies',
        'bar' => 'food_drinks',
        'paper' => 'lane_equipment',
    ];

    public function run(): void
    {
        foreach (self::CATEGORY_MAP as $old => $new) {
            Inventory::where('category', $old)->update(['category' => $new]);
        }

        $byName = [
            'Lane Oil' => 'oil_supplies',
            'Ball Polish' => 'oil_supplies',
            'Cleaning Wipes' => 'cleaning_supplies',
            'Bar Napkins' => 'food_drinks',
        ];

        foreach ($byName as $name => $category) {
            Inventory::where('name', $name)->where('category', '!=', $category)->update(['category' => $category]);
        }

        $extra = [
            ['name' => 'Bowling Balls', 'category' => 'balls', 'quantity' => 24, 'max_quantity' => 30, 'reorder_threshold' => 4, 'cost_per_unit' => 60],
            ['name' => 'Score Sheets', 'category' => 'lane_equipment', 'quantity' => 100, 'max_quantity' => 200, 'reorder_threshold' => 40, 'cost_per_unit' => 1],
            ['name' => 'Chips & Soda', 'category' => 'food_drinks', 'quantity' => 40, 'max_quantity' => 80, 'reorder_threshold' => 15, 'cost_per_unit' => 3],
            ['name' => 'Ball Return Cushions', 'category' => 'lane_equipment', 'quantity' => 12, 'max_quantity' => 16, 'reorder_threshold' => 4, 'cost_per_unit' => 45],
            ['name' => 'Pinsetter Belts', 'category' => 'spare_parts', 'quantity' => 8, 'max_quantity' => 12, 'reorder_threshold' => 3, 'cost_per_unit' => 80],
            ['name' => 'Tool Kit', 'category' => 'spare_parts', 'quantity' => 4, 'max_quantity' => 6, 'reorder_threshold' => 1, 'cost_per_unit' => 120],
        ];

        foreach ($extra as $item) {
            if (! Inventory::where('name', $item['name'])->exists()) {
                Inventory::create(array_merge($item, ['condition' => 'good']));
            }
        }
    }
}
