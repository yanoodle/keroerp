<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $products = \App\Models\Products::all();
        $sales = \App\Models\Sales::all();
        $purchases = \App\Models\Purchasing::all();

        // Aggregate sales qty per product
        $salesByProduct = $sales->groupBy('product')->map(fn($items) => $items->sum('quantity'));
        $purchaseByProduct = $purchases->groupBy('product')->map(fn($items) => $items->sum('quantity'));

        // Count sales status
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
