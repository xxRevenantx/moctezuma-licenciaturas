<?php

namespace App\Http\Controllers;

use App\Models\Documentacion;
use App\Http\Requests\StoreDocumentacionRequest;
use App\Http\Requests\UpdateDocumentacionRequest;

class DocumentacionController extends Controller
{

    public function documentacion()
    {
        return view('admin.documentacion.index');
    }


    // Constancia
    public function constancias()
    {
        return view('admin.documentacion.constancias.index');
    }

    // Listas Generales
    public function listasGenerales()
    {
        return view('admin.documentacion.listas-generales.index');
    }



}
