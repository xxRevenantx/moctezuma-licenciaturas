<?php


use App\Http\Controllers\Admin\DirectivoController;
use App\Http\Controllers\Admin\GeneracionController;
use App\Http\Controllers\Admin\LicenciaturaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AsignarGeneracionController;
use App\Livewire\Admin\Usuarios\MostrarUsuarios;
use App\Models\Generacion;
use Illuminate\Support\Facades\Route;

// Route::get('/admin', function () {
//     return view('admin.dashboard');
// })->middleware(['auth'])->name('admin.dashboard');
Route::middleware(['auth'])->group(function () {

    Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

    Route::resource('usuarios', UserController::class)->middleware('can:admin.usuarios')->names('admin.usuarios');


    Route::resource('licenciaturas', LicenciaturaController::class)->middleware('can:admin.administracion')->names('admin.licenciaturas');
    Route::resource('directivos', DirectivoController::class)->middleware('can:admin.administracion')->names('admin.directivos');
    Route::resource('generaciones', GeneracionController::class)->middleware('can:admin.generaciones')->names('admin.generaciones');

    Route::resource('asignar-generacion', AsignarGeneracionController::class)->middleware('can:admin.asignar.generacion')->names('admin.asignar.generacion');



});
