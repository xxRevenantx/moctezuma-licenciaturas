<?php


use App\Http\Controllers\Admin\DirectivoController;
use App\Http\Controllers\Admin\GeneracionController;
use App\Http\Controllers\Admin\LicenciaturaController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Generacion;
use Illuminate\Support\Facades\Route;

// Route::get('/admin', function () {
//     return view('admin.dashboard');
// })->middleware(['auth'])->name('admin.dashboard');
Route::middleware(['auth'])->group(function () {

    Route::resource('usuarios', UserController::class)->names('admin.usuarios');


    Route::resource('licenciaturas', LicenciaturaController::class)->names('admin.licenciaturas');
    Route::resource('directivos', DirectivoController::class)->names('admin.directivos');
    Route::resource('generaciones', GeneracionController::class)->names('admin.generaciones');




    Route::get('exportar-licenciaturas', [LicenciaturaController::class, 'export'])->name('exportar.licenciaturas');

    Route::get('exportar-directivos', [DirectivoController::class, 'export'])->name('exportar.directivos');
});
