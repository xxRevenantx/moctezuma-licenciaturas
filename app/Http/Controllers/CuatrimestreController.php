<?php

namespace App\Http\Controllers;

use App\Models\Cuatrimestre;
use App\Http\Requests\StoreCuatrimestreRequest;
use App\Http\Requests\UpdateCuatrimestreRequest;

class CuatrimestreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('admin.cuatrimestres.index');
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
    public function store(StoreCuatrimestreRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Cuatrimestre $cuatrimestre)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cuatrimestre $cuatrimestre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCuatrimestreRequest $request, Cuatrimestre $cuatrimestre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cuatrimestre $cuatrimestre)
    {
        //
    }
}
