<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            ProductsSeeder::class,
            PurchasingSeeder::class,
            SalesSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Sales',
            'email' => 'sales@kero.com',
            'password' => '123123123',
            'role' => 'Sales'

        ]);

        User::factory()->create([
            'name' => 'Purchase',
            'email' => 'purchase@kero.com',
            'password' => '123123123',
            'role' => 'Purchase'

        ]);
    }
}
