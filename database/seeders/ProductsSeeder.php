<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'Classic White T-Shirt',
                'description' => '100% cotton crew neck tee, perfect for everyday wear.',
                'base_qty' => 100,
                'in_demand_qty' => 50,
                'price' => 1499,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Denim Jacket',
                'description' => 'Stylish blue denim jacket with button-up front and chest pockets.',
                'base_qty' => 40,
                'in_demand_qty' => 50,
                'price' => 4599,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Slim Fit Jeans',
                'description' => 'Stretchy slim fit jeans in dark wash, great for casual looks.',
                'base_qty' => 0,
                'in_demand_qty' => 2,
                'price' => 3799,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hooded Sweatshirt',
                'description' => 'Comfy pullover hoodie with front pocket and adjustable drawstring.',
                'base_qty' => 75,
                'in_demand_qty' => 3,
                'price' => 2999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Canvas Sneakers',
                'description' => 'Lightweight canvas sneakers with rubber soles, unisex design.',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 3299,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
