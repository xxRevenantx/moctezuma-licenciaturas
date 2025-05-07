<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Accion;
use Illuminate\Http\Request;

class SeleccionarModalidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($slug)
    {

        return view('admin.seleccionar-modalidad.index', [
            'slug_licenciatura' => $slug
        ]);

    }


}
