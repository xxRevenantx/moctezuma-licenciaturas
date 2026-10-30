<?php

use Illuminate\Support\Facades\Route;

it('registra las rutas de plantilla docente usadas en menú y exportaciones', function () {
    $rutas = [
        'index' => 'plantilla-docente',
        'preview' => 'plantilla-docente/preview',
        'pdf' => 'plantilla-docente/pdf',
        'word' => 'plantilla-docente/word',
        'excel' => 'plantilla-docente/excel',
    ];

    foreach ($rutas as $sufijo => $uri) {
        $nombre = "admin.plantilla-docente.{$sufijo}";
        expect(Route::has($nombre))->toBeTrue();
        $ruta = Route::getRoutes()->getByName($nombre);
        expect($ruta)->not->toBeNull();
        expect($ruta->uri())->toBe($uri);
        expect($ruta->methods())->toContain('GET');
        expect($ruta->gatherMiddleware())->toContain('can:admin.administracion');
    }
});
