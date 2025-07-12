<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Http\Requests\StoreProfesorRequest;
use App\Http\Requests\UpdateProfesorRequest;

class ProfesorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.profesores.index');
    }

    public function lista_profesores(){
        return view('admin.profesores.lista-profesores');
    }

    public function credencial_profesor()
    {
        return view('admin.profesores.credencial-profesor');
    }



}
