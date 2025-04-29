<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Exports\DirectivoExport;
use App\Models\Directivo;
use App\Http\Requests\StoreDirectivoRequest;
use App\Http\Requests\UpdateDirectivoRequest;
use Maatwebsite\Excel\Facades\Excel;

class DirectivoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $directivos = Directivo::all();
        return view('admin.directivo.index', compact('directivos'));
    }

    public function export()
    {
        return Excel::download(new DirectivoExport, 'directivos.xlsx');
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
    public function store(StoreDirectivoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Directivo $directivo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Directivo $directivo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDirectivoRequest $request, Directivo $directivo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Directivo $directivo)
    {
        //
    }
}
