<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    /** @use HasFactory<\Database\Factories\SalesFactory> */
    use HasFactory;
    protected $fillable = ['so_number','product', 'quantity', 'price', 'customer_name', 'customer_email', 'customer_address', 'status','payment_proof_so','payment_proof_desc','shipping_proof_so','shipping_proof_desc','received_proof_so','received_proof_desc'];
}