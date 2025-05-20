<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sales')->insert([
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
            [
                'product' => 'Slim Fit Jeans',
                'quantity' => 1,
                'price' => 3799,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product' => 'Hooded Sweatshirt',
                'quantity' => 3,
                'price' => 8997, // 2999 x 3
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product' => 'Canvas Sneakers',
                'quantity' => 1,
                'price' => 3299,
                'status' => 'Canceled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
