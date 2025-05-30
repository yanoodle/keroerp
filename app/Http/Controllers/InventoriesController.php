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
        $refundItems = RefundItem::all();

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
    { {
            $request->validate([
                'name' => 'required',
                'description' => 'required',
                'base_qty' => 'required',
                'in_demand_qty' => 'required',
                'vendor_name' => 'required',
                'vendor_contact' => 'required',
                'sell_price' => 'required',
                'buy_price' => 'required'
            ]);

            $inventoryData = $request->only([
                'name',
                'description',
                'base_qty',
                'in_demand_qty',
                'vendor_name',
                'vendor_contact',
                'sell_price',
                'buy_price'
            ]);

            $inventory = Products::updateOrCreate(['name' => $inventoryData['name']], $inventoryData);

            if ($inventory->base_qty == 0) {
                $desc = 'Item is out of stock.';
                $status = 'Danger';
            } elseif ($inventory->base_qty < $inventory->in_demand_qty) {
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
    { {
            $request->validate([
                'name' => 'required',
                'description' => 'required',
                'base_qty' => 'required',
                'in_demand_qty' => 'required',
                'vendor_name' => 'required',
                'vendor_contact' => 'required',
                'sell_price' => 'required',
                'buy_price' => 'required'
            ]);

            $originalBaseQty = $inventory->base_qty;
            $originalInDemandQty = $inventory->in_demand_qty;

            $inventory->update($request->all());

            if ($inventory->base_qty == 0) {
                $desc = 'Item is out of stock.';
                $status = 'Danger';
            } elseif ($inventory->base_qty < $inventory->in_demand_qty) {
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
    { {
            $inventory->delete();
            return redirect('inventories');
        }
    }


    public function processRefund(RefundItem $item)
    {
        if ($item->status !== 'Pending') {
            return back()->withErrors(['msg' => 'Refund already processed.']);
        }

        $product = Products::where('name', $item->name)->first();

        if (!$product) {
            return back()->withErrors(['msg' => 'Product not found.']);
        }

        $product->base_qty += $item->qty;
        $product->save();

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
