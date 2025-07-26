<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Logic to retrieve and display a list of students
        return view('admin.estudiante.index');
    }


}
