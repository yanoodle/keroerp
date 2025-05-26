<?php

namespace App\Http\Controllers;

use App\Models\Purchasing;
use App\Http\Requests\StorePurchasingRequest;
use App\Http\Requests\UpdatePurchasingRequest;
use App\Models\Products;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchasingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchasing::all();
        $inventories = Products::all();
        return Inertia::render('Purchase', [
            'purchases' => $purchases,
            'inventories' => $inventories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product' => 'required',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'status' => 'required'
        ]);
    
        $purchaseData = $request->only(['product', 'quantity', 'price', 'status']);
    
        Purchasing::create($purchaseData);
    
        return redirect('purchase');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchasing $purchasing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchasing $purchasing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchasing $purchase)
    {
        $request->validate([
            'product' => 'required',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'status' => 'required'
        ]);
    
        $purchase->update($request->all());
    
        // If status is Completed, update the product's base_qty
        if ($request->status === 'Completed') {
            $product = Products::where('name', $purchase->product)->first();
            if ($product) {
                // Add purchased quantity to product's base_qty
                $product->base_qty += $purchase->quantity;
                $product->save();
            }
        }
    
        return redirect('purchase');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchasing $purchase)
    {
        $purchase->delete();
        return redirect('purchase');
    }
}
