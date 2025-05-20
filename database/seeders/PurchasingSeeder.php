<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchasingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('purchasings')->insert([
            [
                'product' => 'Classic White T-Shirt',
                'quantity' => 2,
                'price' => 2998, // 1499 x 2
                'status' => 'Completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product' => 'Denim Jacket',
                'quantity' => 1,
                'price' => 4599,
                'status' => 'Shipped',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
