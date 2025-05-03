<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (){
            Route::middleware('web', 'auth')
            ->prefix('admin') // para la ruta
            ->name('admin.') // para el nombre de la ruta
            ->group(base_path('routes/admin.php'));
        }

    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->append(\App\Http\Middleware\ModoMantenimiento::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
