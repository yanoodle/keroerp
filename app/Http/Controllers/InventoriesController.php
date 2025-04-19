<?php

namespace App\Http\Controllers;

use App\Models\Inventories;
use App\Http\Requests\StoreInventoriesRequest;
use App\Http\Requests\UpdateInventoriesRequest;
use Inertia\Inertia;

class InventoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Inventory');
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
    public function store(StoreInventoriesRequest $request)
    {
        //
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
    public function update(UpdateInventoriesRequest $request, Inventories $inventories)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventories $inventories)
    {
        //
    }
}
