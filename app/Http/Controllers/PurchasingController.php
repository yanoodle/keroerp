<?php

namespace App\Http\Controllers;

use App\Models\Purchasing;
use App\Http\Requests\StorePurchasingRequest;
use App\Http\Requests\UpdatePurchasingRequest;
use App\Models\Products;
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
    public function store(StorePurchasingRequest $request)
    {
        
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
    public function update(UpdatePurchasingRequest $request, Purchasing $purchasing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchasing $purchasing)
    {
        //
    }
}
