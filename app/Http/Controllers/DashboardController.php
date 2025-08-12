<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Purchasing;
use App\Models\Sales;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Products::all();
        $sales = Sales::all();
        $purchases = Purchasing::all();

        $salesByProduct = $sales->groupBy('product')->map(fn($items) => $items->sum('quantity'));
        $purchaseByProduct = $purchases->groupBy('product')->map(fn($items) => $items->sum('quantity'));

        $salesStatus = $sales->groupBy('status')->map(fn($items) => $items->count());
        $purchaseStatus = $purchases->groupBy('status')->map(fn($items) => $items->count());

        return Inertia::render('Dashboard', [
            'products' => $products,
            'salesByProduct' => $salesByProduct,
            'purchaseByProduct' => $purchaseByProduct,
            'salesStatus' => $salesStatus,
            'purchaseStatus' => $purchaseStatus,
        ]);
    }
}