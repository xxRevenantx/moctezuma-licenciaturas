<?php

namespace App\Http\Controllers;

use App\Models\Mes;
use App\Http\Requests\StoreMesRequest;
use App\Http\Requests\UpdateMesRequest;

class MesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('admin.meses.index');
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
    public function store(StoreMesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Mes $mes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mes $mes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMesRequest $request, Mes $mes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mes $mes)
    {
        //
    }
}
