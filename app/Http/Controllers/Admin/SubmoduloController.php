<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Accion;
use App\Models\Licenciatura;
use Illuminate\Http\Request;

class SubmoduloController extends Controller
{

    public function index($slug_licenciatura, $slug_modalidad, $submodulo)
    {
        $accion = Accion::where('slug', $submodulo)->firstOrFail();
        $acciones = Accion::all();
        $licenciatura = Licenciatura::where('slug', $slug_licenciatura)->firstOrFail();



        return view('admin.licenciaturas.index', [
            'slug_licenciatura' => $slug_licenciatura,
            'slug_modalidad' => $slug_modalidad,
            'submodulo' => $submodulo,
            'accion' => $accion->accion,
            'acciones' => $acciones,
            'licenciatura' => $licenciatura,
        ]);

    }

}
