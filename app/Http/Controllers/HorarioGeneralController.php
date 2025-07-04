<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HorarioGeneralController extends Controller
{

     public function index()
    {
        return view('admin.horario-general.index');
    }

}
