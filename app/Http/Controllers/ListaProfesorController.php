<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ListaProfesorController extends Controller
{
      public function index(){
        return view('admin.profesores.lista-profesores');
    }
}
