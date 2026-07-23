<?php

namespace App\Http\Controllers;

use App\Models\DocumentoIdentidadFuente;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentoIdentidadFuenteController extends Controller
{
    public function ver(DocumentoIdentidadFuente $fuente): BinaryFileResponse
    {
        Gate::authorize('documentos-identidad.ver');

        return $this->responderOriginal($fuente, 'inline');
    }

    public function descargar(DocumentoIdentidadFuente $fuente): BinaryFileResponse
    {
        Gate::authorize('documentos-identidad.descargar');

        return $this->responderOriginal($fuente, 'attachment');
    }

    public function pagina(
        Request $request,
        DocumentoIdentidadFuente $fuente,
        int $pagina,
        DocumentoIdentidadService $service
    ): BinaryFileResponse {
        Gate::authorize('documentos-identidad.ver');
        abort_unless($fuente->estado === 'activo', Response::HTTP_NOT_FOUND);

        $rotacion = (int) $request->integer('rotacion', 0);
        $ruta = $service->rutaVistaPagina($fuente, $pagina, $rotacion);

        return response()->file($ruta, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pagina-' . $pagina . '.pdf"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function responderOriginal(DocumentoIdentidadFuente $fuente, string $disposition): BinaryFileResponse
    {
        abort_unless($fuente->estado === 'activo', Response::HTTP_NOT_FOUND);
        $disk = Storage::disk((string) config('documentos_identidad.disk', 'local'));
        $ruta = $fuente->ruta_original ?: $fuente->ruta;
        abort_unless($disk->exists($ruta), Response::HTTP_NOT_FOUND, 'El archivo fuente no existe.');

        $nombre = Str::of($fuente->nombre_original ?: basename($ruta))
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '_')
            ->trim('_')
            ->toString();

        return response()->file($disk->path($ruta), [
            'Content-Type' => $fuente->mime_original ?: 'application/octet-stream',
            'Content-Disposition' => $disposition . '; filename="' . $nombre . '"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
