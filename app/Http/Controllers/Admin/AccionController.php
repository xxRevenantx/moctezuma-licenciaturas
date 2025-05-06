<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Accion;
use App\Http\Requests\StoreAccionRequest;
use App\Http\Requests\UpdateAccionRequest;

class AccionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('admin.acciones.index');
    }


}
