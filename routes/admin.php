<?php

use App\Http\Controllers\Admin\AccionController;
use App\Http\Controllers\Admin\AsignarGeneracionController;
use App\Http\Controllers\Admin\CuatrimestreController;
use App\Http\Controllers\Admin\DirectivoController;
use App\Http\Controllers\Admin\GeneracionController;
use App\Http\Controllers\Admin\InscripcionController;
use App\Http\Controllers\Admin\LicenciaturaController;
use App\Http\Controllers\Admin\PeriodoController;
use App\Http\Controllers\Admin\SeleccionarModalidadController;
use App\Http\Controllers\Admin\SubmoduloController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\MesController;
use App\Livewire\Admin\Licenciaturas\SeleccionarModalidad;
use App\Livewire\Admin\Usuarios\MostrarUsuarios;
use App\Models\Generacion;
use Illuminate\Support\Facades\Route;

// Route::get('/admin', function () {
//     return view('admin.dashboard');
// })->middleware(['auth'])->name('admin.dashboard');




Route::middleware(['auth'])->group(function () {


    Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');
    Route::resource('usuarios', UserController::class)->middleware('can:admin.usuarios')->names('admin.usuarios');

    Route::resource('acciones', AccionController::class)->middleware('can:admin.administracion')->names('admin.acciones');


    Route::resource('estados', EstadoController::class)->middleware('can:admin.administracion')->names('admin.estados');
    Route::resource('ciudades', CiudadController::class)->middleware('can:admin.administracion')->names('admin.ciudades');



    Route::resource('asignacion-de-licenciaturas', LicenciaturaController::class)->middleware('can:admin.administracion')->names('admin.asignacion.licenciaturas');


    Route::resource('directivos', DirectivoController::class)->middleware('can:admin.administracion')->names('admin.directivos');
    Route::resource('cuatrimestres', CuatrimestreController::class)->middleware('can:admin.administracion')->names('admin.cuatrimestres');
    Route::resource('periodos-escolares', PeriodoController::class)->middleware('can:admin.administracion')->names('admin.periodos');

    Route::resource('crear-generacion', GeneracionController::class)->middleware('can:admin.generaciones')->names('admin.generaciones');

    Route::resource('asignar-generacion', AsignarGeneracionController::class)->middleware('can:admin.asignar.generacion')->names('admin.asignar.generacion');



    Route::prefix('licenciaturas')->group(function () {
        // Paso 1: Selección de modalidad
        Route::get('{slug_licenciatura}', [SeleccionarModalidadController::class, 'index'])->name('licenciaturas.seleccionar-modalidad');

        Route::get('{slug_licenciatura}/{slug_modalidad}/{submodulo}', [SubmoduloController::class, 'index'])->name('licenciaturas.submodulo');

    });




});


