<?php

namespace App\Http\Controllers;

use App\Models\Constancia;
use App\Http\Requests\StoreConstanciaRequest;
use App\Http\Requests\UpdateConstanciaRequest;

class ConstanciaController extends Controller
{

    public function index()
    {
        return view('admin.documentacion.constancias.index');
    }


}
