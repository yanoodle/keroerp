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
                'name' => 'Jersey Timnas Garuda Fantasy Indonesia 2024 by Kero Apparel',
                'description' => 'Jersey Timnas Garuda Fantasy Timnas atau Garuda dengan design elegan',
                'base_qty' => 100,
                'in_demand_qty' => 0,
                'price' => 138000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'First Evolution Jersey White',
                'description' => 'First Evolution Jersey White dengan design mewah dan simple yang cocok digunakan untuk segala macam aktivitas olahraga.',
                'base_qty' => 40,
                'in_demand_qty' => 0,
                'price' => 110400,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kaos Kaki Sepak Bola/Kaos Kaki Futsal Anti Slip Model Panjang By Kero Apparel',
                'description' => 'Kaos kaki futsal atau sepak bola dengan bahan berkualitas dan dilengkapi dengan anti slip di bagian bawah kaki panjang sampai lutut.',
                'base_qty' => 100,
                'in_demand_qty' => 0,
                'price' => 40000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jersey JEDA Navy From Kero Apparel',
                'description' => 'Jersey JEDA Navy from Kero Apparel Jersey atasan yang cocok untuk di gunakan segala aktivitas olahraga maupun style sehari hari.',
                'base_qty' => 75,
                'in_demand_qty' => 0,
                'price' => 95000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jersey Classic Series All Navy logo timbul (3D)',
                'description' => 'Kesic Series Full Navy Jersey atasan basic cocok digunakan segala kegiatan olahraga maupun outfit santai , Material : Drift puma best quality.',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 94050,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jersey JEDA Pink from Kero Apparel Jersey Futsal / Sepakbola',
                'description' => 'Jersey JEDA Pink from Kero Apparel Jersey atasan yang cocok digunakan segala aktivitas olahraga maupun style santai sehari-hari',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 95000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jersey Classic Series All Black logo timbul (3D)',
                'description' => 'Kesic Series Full Black Jersey atasan basic cocok digunakan segala kegiatan olahraga maupun outfit santai , Material : Drift puma best quality.',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 94050,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jersey JEDA White from Kero Apparel Jersey Futsal / Sepakbola',
                'description' => 'Jersey JEDA White from Kero Apparel Jersey atasan yang cocok digunakan segala aktivitas olahraga maupun style santai sehari-hari',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 95000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
                        [
                'name' => 'First Evolution Jersey Yellow',
                'description' => 'First Evolution Jersey Yellow Jersey dengan design mewah dan simple yang cocok digunakan untuk segala aktivitas olahraga maupun style casual untuk jalan dan nongkrong',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 110500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'First Evolution Jersey Gray',
                'description' => 'First Evolution Jersey Gray Jersey dengan design mewah dan simple yang cocok digunakan untuk segala aktivitas olahraga maupun style casual untuk jalan dan nongkrong',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 110400,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Custom Namaset Polyflex',
                'description' => 'Custom nama + Nomor punggung dengan bahan polyflex yang bisa langsung di aplikasikan di jersey kalian',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 30000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kaos kaki pendek kasual atau kaos kaki olahraga by Kero Apparel',
                'description' => 'Kaos kaki pendek kasual cocok untuk kegiatan olahraga, hangout dan kegiatan lainnya, bahan nyaman dan kualitas luar biasa',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 30000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jersey Classic Series All White logo timbul (3D)',
                'description' => 'Kesic Series Full White Jersey atasan basic cocok digunakan segala kegiatan olahraga maupun outfit santai , Material : Drift puma best quality.',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 94050,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Black Spilled T-Shirt atau Kaos Kero Apparel',
                'description' => 'Black Spilled T-Shirt By Kero Apparel kaos santai untuk pria & wanita cocok untuk kegiatan sehari-hari.',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 94050,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jersey Classic Series All Red logo timbul (3D)',
                'description' => 'Kesic Series Full Red Jersey atasan basic cocok digunakan segala kegiatan olahraga maupun outfit santai , Material : Drift puma best quality.',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 94050,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kero Handbag / Pouch / Tas tangan',
                'description' => 'Kero Handbag tas tangan dengan bahan Cordura Premium + Zipper YKK, Cocok di gunakan untuk berpergian untuk menyimpan dompet,charger,earphone.',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 95000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
                        [
                'name' => 'White Spilled T-Shirt atau Kaos Kero Apparel',
                'description' => 'White Spilled T-Shirt By Kero Apparel kaos santai untuk pria & wanita cocok untuk kegiatan sehari-hari.',
                'base_qty' => 50,
                'in_demand_qty' => 0,
                'price' => 94050,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
