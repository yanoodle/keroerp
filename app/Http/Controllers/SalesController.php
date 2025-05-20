<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Http\Requests\StoreSalesRequest;
use App\Http\Requests\UpdateSalesRequest;
use App\Models\Products;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sales::all();
        $inventories = Products::all();
        return Inertia::render('Sales', [
            'sales' => $sales,
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
    
        $saleData = $request->only(['product', 'quantity', 'price', 'status']);
    
        Sales::create($saleData);
    
        $product = Products::where('name', $request->product)->first();
    
        if ($product) {
            $product->in_demand_qty += (int) $request->quantity;
            $product->save();
        }
    
        return redirect('sales');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sales $sales)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sales $sales)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sales $sale)
    {
        $request->validate([
            'product' => 'required',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'status' => 'required'
        ]);
    
        $product = Products::where('name', $request->product)->first();
    
        if ($product) {
            $product->in_demand_qty += (int) $request->quantity;
            $product->save();
        }
    
        $sale->update($request->all());
    
        return redirect('sales');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sales $sale)
    {
        $product = Products::where('name', $sale->product)->first();
    
        if ($product) {
            $product->in_demand_qty -= $sale->quantity;
    
            if ($product->in_demand_qty < 0) {
                $product->in_demand_qty = 0;
            }
    
            $product->save();
        }
    
        $sale->delete();
    
        return redirect('sales');
    }
}
