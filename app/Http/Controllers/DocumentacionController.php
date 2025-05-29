<?php

namespace App\Http\Controllers;

use App\Models\Documentacion;
use App\Http\Requests\StoreDocumentacionRequest;
use App\Http\Requests\UpdateDocumentacionRequest;

class DocumentacionController extends Controller
{

    public function index()
    {
        return view('admin.documentacion.index');
    }




}
