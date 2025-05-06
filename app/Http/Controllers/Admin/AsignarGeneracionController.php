<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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


}
