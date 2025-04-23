<?php

namespace App\Http\Controllers;

use App\Models\Inventories;
use App\Http\Requests\StoreInventoriesRequest;
use App\Http\Requests\UpdateInventoriesRequest;
use App\Models\Products;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventories = Products::all();
        return Inertia::render('Inventory', ['inventories' => $inventories]);
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
        {
            $request->validate([
                'name' => 'required',
                'description' => 'required',
                'base_qty' => 'required',
                'in_demand_qty' => 'required',
                'price' => 'required'
            ]);
    
            $inventoryData = $request->only([
                'name', 'description', 'base_qty', 'in_demand_qty', 'price'
            ]);
    
            // Create or update inventory
            $inventory = Products::updateOrCreate(['name' => $inventoryData['name']], $inventoryData);
    
            // Check if base_qty is zero
            if ($inventory->base_qty == 0) {
                $desc = 'Item is out of stock.';
                $status = 'Danger';
            }
            // Check if base_qty is lower than in_demand_qty
            elseif ($inventory->base_qty < $inventory->in_demand_qty) {
                $desc = 'Item is low in stock.';
                $status = 'Warning';
            } else {
                $desc = null;
                $status = null;
            }
    
            return redirect('inventories');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventories $inventories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventories $inventories)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Products $inventory)
    {
        {
            $request->validate([
                'name' => 'required',
                'description' => 'required',
                'base_qty' => 'required',
                'in_demand_qty' => 'required',
                'price' => 'required'
            ]);
    
            // Original data before update
            $originalBaseQty = $inventory->base_qty;
            $originalInDemandQty = $inventory->in_demand_qty;
    
            // Update inventory
            $inventory->update($request->all());
    
            // Check if base_qty is zero after update
            if ($inventory->base_qty == 0) {
                $desc = 'Item is out of stock.';
                $status = 'Danger';
            }
            // Check if base_qty is lower than in_demand_qty after update
            elseif ($inventory->base_qty < $inventory->in_demand_qty) {
                $desc = 'Item is low in stock.';
                $status = 'Warning';
            } else {
                $desc = null;
                $status = null;
            }
    
            return redirect('inventories');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $inventory)
    {
        {
            $inventory->delete();
            return redirect('inventories');
        }
    }
}
