<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Http\Requests\StoreSalesRequest;
use App\Http\Requests\UpdateSalesRequest;
use App\Models\Products;
use App\Models\RefundItem;
use App\Models\Purchasing;
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
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'customer_name' => 'required',
            'customer_email' => 'required',
            'customer_address' => 'required',
            'status' => 'required'
        ]);

        $salesData = $request->only(['product', 'quantity', 'price', 'customer_name', 'customer_email', 'customer_address', 'status']);

        $lastId = Sales::max('id') ?? 0;
        $salesData['so_number'] = 'SO' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);

        Sales::create($salesData);

        $product = Products::where('name', $request->product)->first();

        if ($product) {
            $product->in_demand_qty += (int)$request->quantity;
            $product->save();

            $this->adjustPurchasing($product);
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
    $rules = [
        'product' => 'required',
        'quantity' => 'required|integer',
        'price' => 'required|numeric',
        'status' => 'required'
    ];

    // Tambahan validasi jika status = Paid
    if ($request->status === 'Paid') {
        $rules['payment_proof_so'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
        $rules['payment_proof_desc'] = 'string|max:255';
    }

        if ($request->status === 'Shipped') {
        $rules['shipping_proof_so'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
        $rules['shipping_proof_desc'] = 'nullable|string|max:255';
    }

        if ($request->status === 'Received') {
        $rules['received_proof_so'] = 'nullable|image|mimes:jpg,jpeg,png|max:2048';
        $rules['received_proof_desc'] = 'nullable|string|max:255';
    }

    $validated = $request->validate($rules);

    $product = Products::where('name', $request->product)->first();

    if (!$product) {
        return redirect('sales')->withErrors('Product not found.');
    }

    $newQty = (int) $request->quantity;
    $oldQty = $sale->quantity;
    $qtyDiff = $newQty - $oldQty;

    if ($sale->status === 'Pending' || $sale->status === 'Approved') {
        $product->in_demand_qty += $qtyDiff;
    }

    if (
        in_array($request->status, ['Canceled', 'Refunded', 'Deleted']) &&
        in_array($sale->status, ['Pending', 'Approved', 'Paid'])
    ) {
        $product->in_demand_qty -= $oldQty;
    } elseif (
        $request->status === 'Shipped' &&
        !in_array($sale->status, ['Shipped', 'Completed', 'Refunded', 'Deleted'])
    ) {
        $product->base_qty -= $newQty;
        $product->in_demand_qty -= $newQty;
    }

    $product->in_demand_qty = max(0, $product->in_demand_qty);
    $product->base_qty = max(0, $product->base_qty);
    $product->save();

    if ($request->status === 'Refunded') {
        $refundItem = RefundItem::where('name', $request->product)->first();

        if ($refundItem) {
            $refundItem->increment('qty', $newQty);
            $refundItem->description = 'Refunded from sale ID: ' . $sale->id;
            $refundItem->save();
        } else {
            RefundItem::create([
                'name' => $request->product,
                'qty' => $newQty,
                'description' => 'Refunded from sale ID: ' . $sale->id,
                'status' => 'Pending',
            ]);
        }
    }

    // Simpan payment proof SO kalau ada
    if ($request->hasFile('payment_proof_so')) {
        $file = $request->file('payment_proof_so');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('payment_proof_so', $filename, 'public');
        $validated['payment_proof_so'] = $filename;
    }

    if ($request->hasFile('shipping_proof_so')) {
    $file = $request->file('shipping_proof_so');
    $filename = time() . '_' . $file->getClientOriginalName();
    $file->storeAs('shipping_proof_so', $filename, 'public');
    $validated['shipping_proof_so'] = $filename;
    }

    if ($request->hasFile('received_proof_so')) {
    $file = $request->file('received_proof_so');
    $filename = time() . '_' . $file->getClientOriginalName();
    $file->storeAs('received_proof_so', $filename, 'public');
    $validated['received_proof_so'] = $filename;
    }

    $sale->update($validated);

    $this->adjustPurchasing($product);

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

            $this->adjustPurchasing($product);
        }

        $sale->delete();

        return redirect('sales');
    }

    private function adjustPurchasing(Products $product)
    {
        $needed = $product->in_demand_qty - $product->base_qty;

        $unitBuyPrice = $product->buy_price > 0 ? $product->buy_price : 100;

        $totalPrice = round($unitBuyPrice * $needed, 2);

        $purchasing = Purchasing::where('product', $product->name)
            ->where('status', 'Pending')
            ->first();

        if ($needed > 0) {
            if ($purchasing) {
                $purchasing->quantity = $needed;
                $purchasing->price = $totalPrice;
                $purchasing->save();
            } else {
                Purchasing::create([
                    'product' => $product->name,
                    'quantity' => $needed,
                    'price' => $totalPrice,
                    'status' => 'Pending',
                ]);
            }
        } else {
            if ($purchasing) {
                $purchasing->delete();
            }
        }
    }
}