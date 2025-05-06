<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;



use App\Models\Cuatrimestre;
use App\Http\Requests\StoreCuatrimestreRequest;
use App\Http\Requests\UpdateCuatrimestreRequest;

class CuatrimestreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('admin.cuatrimestres.index');
    }


}
