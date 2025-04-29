<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Generacion;
use App\Http\Requests\StoreGeneracionRequest;
use App\Http\Requests\UpdateGeneracionRequest;

class GeneracionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.generaciones.index');
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
    public function store(StoreGeneracionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Generacion $generacion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Generacion $generacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGeneracionRequest $request, Generacion $generacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Generacion $generacion)
    {
        //
    }
}
