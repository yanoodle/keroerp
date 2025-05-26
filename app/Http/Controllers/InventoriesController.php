<?php

namespace App\Http\Controllers;

use App\Models\Inventories;
use App\Http\Requests\StoreInventoriesRequest;
use App\Http\Requests\UpdateInventoriesRequest;
use App\Models\Products;
use App\Models\RefundItem;
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
        $refundItems = RefundItem::all();  // fetch all refund items
        
        return Inertia::render('Inventory', [
            'inventories' => $inventories,
            'refundItems' => $refundItems,
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


    public function processRefund(RefundItem $item)
    {
        if ($item->status !== 'Pending') {
            return back()->withErrors(['msg' => 'Refund already processed.']);
        }

        // Cari produk berdasarkan nama atau ID jika ada relasi
        $product = Products::where('name', $item->name)->first();

        if (!$product) {
            return back()->withErrors(['msg' => 'Product not found.']);
        }

        // Tambah qty ke base_qty
        $product->base_qty += $item->qty;
        $product->save();

        // Ubah status refund item
        $item->status = 'Added';
        $item->save();

        return back();
    }

    public function deleteRefundItem($id)
    {
        $refundItem = RefundItem::find($id);

        if (!$refundItem) {
            return back()->withErrors(['msg' => 'Refund item not found.']);
        }

        $refundItem->delete();

        return back()->with('success', 'Refund item deleted successfully.');
    }

}
