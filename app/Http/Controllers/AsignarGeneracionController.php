<?php

namespace App\Http\Controllers;

use App\Models\asignar_generacion;
use App\Http\Requests\Storeasignar_generacionRequest;
use App\Http\Requests\Updateasignar_generacionRequest;

class AsignarGeneracionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('admin.asignar-generacion.index');
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
    public function store(Storeasignar_generacionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(asignar_generacion $asignar_generacion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(asignar_generacion $asignar_generacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updateasignar_generacionRequest $request, asignar_generacion $asignar_generacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(asignar_generacion $asignar_generacion)
    {
        //
    }
}
