<?php

namespace App\Http\Controllers;

use App\Models\DocumentoIdentidad;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentoIdentidadController extends Controller
{
    public function ver(DocumentoIdentidad $documento): BinaryFileResponse
    {
        Gate::authorize('documentos-identidad.ver');

        return $this->responder($documento, 'inline');
    }

    public function descargar(DocumentoIdentidad $documento): BinaryFileResponse
    {
        Gate::authorize('documentos-identidad.descargar');

        return $this->responder($documento, 'attachment');
    }

    protected function responder(DocumentoIdentidad $documento, string $disposition): BinaryFileResponse
    {
        $disk = (string) config('documentos_identidad.disk', 'local');
        abort_unless(Storage::disk($disk)->exists($documento->ruta), Response::HTTP_NOT_FOUND, 'El archivo no existe en el almacenamiento privado.');

        $nombre = Str::of($documento->nombre_original ?: $documento->nombre_almacenado)
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '_')
            ->trim('_')
            ->toString();

        if (! str_ends_with(strtolower($nombre), '.pdf')) {
            $nombre .= '.pdf';
        }

        return response()->file(Storage::disk($disk)->path($documento->ruta), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $nombre . '"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
