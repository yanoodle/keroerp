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
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'status' => 'required'
        ]);

        $purchaseData = $request->only(['product', 'quantity', 'price', 'status']);

        // Generate PO number otomatis
        $lastId = Purchasing::max('id') ?? 0;
        $purchaseData['po_number'] = 'PO' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);

        Purchasing::create($purchaseData);

        return redirect('purchase')->with('success', 'Purchase created successfully.');
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
    $oldStatus = $purchase->status;

    $rules = [
        'product' => 'required',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric',
        'status' => 'required',
    ];

    if ($request->status === 'Paid') {
        $rules['payment_proof'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
        $rules['payment_proof_desc'] = 'nullable|string|max:255';
    }

    if ($request->status === 'Received') {
        $rules['received_proof'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
        $rules['received_proof_desc'] = 'nullable|string|max:255';
    }

    $validated = $request->validate($rules);

    // Update stok berdasarkan status lama dan baru
    $product = Products::where('name', $purchase->product)->first();

    if ($product) {
        if ($oldStatus === 'Pending' && $request->status === 'Approved') {
            $product->in_demand_qty += $purchase->quantity;
        }

        if ($oldStatus === 'Approved' && $request->status === 'Received') {
            // no stok update karena barang belum sampai fisik
        }

        if ($oldStatus === 'Received' && $request->status === 'Completed') {
            $product->base_qty += $purchase->quantity;
            $product->in_demand_qty -= $purchase->quantity;
        }

        if ($oldStatus === 'Completed' && $request->status === 'Canceled') {
            $product->base_qty -= $purchase->quantity;
        }

        $product->save();
    }

    // Upload payment proof
    if ($request->hasFile('payment_proof')) {
        $file = $request->file('payment_proof');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('payment_proofs', $filename, 'public');
        $validated['payment_proof'] = $filename;
    }

    // Upload received proof
    if ($request->hasFile('received_proof')) {
        $file = $request->file('received_proof');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('received_proofs', $filename, 'public');
        $validated['received_proof'] = $filename;
    }

    // Update purchase record
    $purchase->update($validated);

    return redirect('purchase')->with('success', 'Purchase updated successfully.');
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