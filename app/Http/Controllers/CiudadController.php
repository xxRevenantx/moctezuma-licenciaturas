<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Http\Request;

class CiudadController extends Controller
{

    public function index()
    {

        return view('admin.ciudades.index');
    }

}
