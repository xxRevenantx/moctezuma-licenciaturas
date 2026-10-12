<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use Illuminate\Http\Response;

class FotografiaEstudianteController extends Controller
{
    public function show(Inscripcion $inscripcion): Response
    {
        $archivo = $inscripcion->fotoArchivo();

        abort_unless($archivo, 404, 'La fotografía del estudiante no está disponible.');

        return response($archivo['content'], 200, [
            'Content-Type' => $archivo['mime'],
            'Content-Disposition' => 'inline; filename="' . basename($archivo['path']) . '"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
