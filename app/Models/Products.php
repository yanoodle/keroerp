<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    /** @use HasFactory<\Database\Factories\ProductsFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'base_qty',
        'in_demand_qty',
        'vendor_name',
        'vendor_contact',
        'sell_price',
        'buy_price',
    ];
}
