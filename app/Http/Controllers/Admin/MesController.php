<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Mes;
use App\Http\Requests\StoreMesRequest;
use App\Http\Requests\UpdateMesRequest;

class MesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('admin.meses.index');
    }


}
