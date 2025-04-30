<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;

use App\Exports\LicenciaturaExport;
use App\Models\Licenciatura;
use App\Http\Requests\StoreLicenciaturaRequest;
use App\Http\Requests\UpdateLicenciaturaRequest;
use Maatwebsite\Excel\Facades\Excel;

class LicenciaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $licenciaturas = Licenciatura::all();
        return view('admin.licenciaturas.index', compact('licenciaturas'));
    }




    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLicenciaturaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Licenciatura $licenciatura)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Licenciatura $licenciatura)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLicenciaturaRequest $request, Licenciatura $licenciatura)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Licenciatura $licenciatura)
    {
        //
    }
}
