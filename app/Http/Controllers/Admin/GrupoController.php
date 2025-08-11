<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Grupo;
use App\Http\Requests\StoreGrupoRequest;
use App\Http\Requests\UpdateGrupoRequest;

class GrupoController extends Controller
{

    public function index()
    {
        return view('admin.grupos.index');
    }


}
