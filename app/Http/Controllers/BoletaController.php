<?php

namespace App\Http\Controllers;

use App\Services\Boletas\BoletaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use ZipArchive;

class BoletaController extends Controller
{
    public function exportar(Request $request, BoletaService $boletas)
    {
        $datos = $request->validate([
            'licenciatura_id' => ['required', 'integer', 'exists:licenciaturas,id'],
            'modalidad_id' => ['required', 'integer', 'exists:modalidades,id'],
            'generacion_id' => ['required', 'integer', 'exists:generaciones,id'],
            'cuatrimestre_id' => ['required'],
            'alumno_ids' => ['required', 'array', 'min:1'],
            'alumno_ids.*' => ['integer', 'distinct', 'exists:inscripciones,id'],
            'formato' => ['required', 'in:consolidado,zip,por_alumno'],
            'accion' => ['required', 'in:preview,download'],
            'incluir_incompletas' => ['nullable', 'boolean'],
        ]);

        $licenciaturaId = (int) $datos['licenciatura_id'];
        $modalidadId = (int) $datos['modalidad_id'];
        $generacionId = (int) $datos['generacion_id'];
        $alumnoIds = collect($datos['alumno_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $incluirIncompletas = (bool) ($datos['incluir_incompletas'] ?? false);

        $cuatrimestreIds = $this->resolverCuatrimestres(
            (string) $datos['cuatrimestre_id'],
            $licenciaturaId,
            $modalidadId,
            $generacionId,
            $boletas
        );

        abort_if($cuatrimestreIds->isEmpty(), 422, 'No hay cuatrimestres disponibles para generar boletas.');

        $alumnosValidos = $boletas->alumnosActivos($licenciaturaId, $modalidadId, $generacionId)
            ->whereIn('id', $alumnoIds)
            ->values();

        abort_if(
            $alumnosValidos->count() !== $alumnoIds->count(),
            422,
            'Uno o más alumnos no pertenecen al contexto seleccionado o ya no están activos.'
        );

        $analisis = $boletas->analizarAlumnos(
            $alumnosValidos,
            $licenciaturaId,
            $modalidadId,
            $generacionId,
            $cuatrimestreIds
        );

        $items = collect();
        foreach ($alumnosValidos as $alumno) {
            foreach ($cuatrimestreIds as $cuatrimestreId) {
                $estado = $analisis['alumnos'][$alumno->id]['periodos'][$cuatrimestreId] ?? null;
                if (!$estado || $estado['codigo'] === 'sin_calificaciones') {
                    continue;
                }
                if ($estado['codigo'] === 'incompleta' && !$incluirIncompletas) {
                    continue;
                }

                $items->push([
                    'alumno_id' => (int) $alumno->id,
                    'cuatrimestre_id' => (int) $cuatrimestreId,
                    'estado' => $estado['codigo'],
                ]);
            }
        }

        abort_if(
            $items->isEmpty(),
            422,
            $incluirIncompletas
                ? 'No hay boletas con calificaciones para los alumnos seleccionados.'
                : 'No hay boletas completas para generar. Activa “Incluir boletas incompletas” si deseas incluirlas.'
        );

        $nombreBase = $this->nombreBase($licenciaturaId, $modalidadId, $generacionId, $boletas);

        return match ($datos['formato']) {
            'zip' => $this->generarZip(
                $items,
                $licenciaturaId,
                $modalidadId,
                $generacionId,
                $boletas,
                $nombreBase
            ),
            'por_alumno' => $this->generarPorAlumno(
                $items,
                $licenciaturaId,
                $modalidadId,
                $generacionId,
                $boletas,
                $nombreBase,
                $datos['accion']
            ),
            default => $this->generarConsolidado(
                $items,
                $licenciaturaId,
                $modalidadId,
                $generacionId,
                $boletas,
                $nombreBase,
                $datos['accion']
            ),
        };
    }

    private function resolverCuatrimestres(
        string $valor,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        BoletaService $boletas
    ): Collection {
        $disponibles = $boletas->cuatrimestresDisponibles($licenciaturaId, $modalidadId, $generacionId)
            ->pluck('cuatrimestre_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($valor === 'todos') {
            return $disponibles;
        }

        abort_unless(ctype_digit($valor), 422, 'Cuatrimestre no válido.');
        $id = (int) $valor;
        abort_unless($disponibles->contains($id), 422, 'El cuatrimestre no pertenece al contexto seleccionado.');

        return collect([$id]);
    }

    private function generarConsolidado(
        Collection $items,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        BoletaService $boletas,
        string $nombreBase,
        string $accion
    ) {
        $fpdi = new Fpdi();
        $temporales = [];

        try {
            foreach ($items as $item) {
                $dataset = $boletas->datasetBoleta(
                    $item['alumno_id'],
                    $licenciaturaId,
                    $modalidadId,
                    $generacionId,
                    $item['cuatrimestre_id']
                );

                $temporal = $this->crearPdfTemporal($dataset);
                $temporales[] = $temporal;
                $this->agregarPdf($fpdi, $temporal);
            }

            $contenido = $fpdi->Output('S');
            $nombre = "{$nombreBase}_CONSOLIDADO.pdf";

            return response($contenido, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => ($accion === 'download' ? 'attachment' : 'inline') . '; filename="' . $nombre . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
            ]);
        } finally {
            $this->limpiarTemporales($temporales);
        }
    }

    private function generarZip(
        Collection $items,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        BoletaService $boletas,
        string $nombreBase
    ) {
        $zipPath = tempnam(sys_get_temp_dir(), 'boletas_zip_');
        if ($zipPath === false) {
            abort(500, 'No fue posible crear el archivo temporal.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            abort(500, 'No fue posible crear el ZIP de boletas.');
        }

        try {
            foreach ($items as $item) {
                $dataset = $boletas->datasetBoleta(
                    $item['alumno_id'],
                    $licenciaturaId,
                    $modalidadId,
                    $generacionId,
                    $item['cuatrimestre_id']
                );

                $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.boletaCalificacionPDF', $dataset)
                    ->setPaper('letter', 'portrait')
                    ->output();

                $cuatri = str_pad((string) $dataset['cuatrimestre']->cuatrimestre, 2, '0', STR_PAD_LEFT);
                $carpeta = $cuatri . '_CUATRIMESTRE';
                $zip->addFromString($carpeta . '/' . $boletas->nombreArchivo($dataset), $pdf);
            }
        } finally {
            $zip->close();
        }

        return response()->download($zipPath, "{$nombreBase}_BOLETAS.zip", [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function generarPorAlumno(
        Collection $items,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        BoletaService $boletas,
        string $nombreBase,
        string $accion
    ) {
        $porAlumno = $items->groupBy('alumno_id');

        if ($porAlumno->count() === 1) {
            $fpdi = new Fpdi();
            $temporales = [];
            $nombreAlumno = 'ALUMNO';

            try {
                foreach ($porAlumno->first() as $item) {
                    $dataset = $boletas->datasetBoleta(
                        $item['alumno_id'],
                        $licenciaturaId,
                        $modalidadId,
                        $generacionId,
                        $item['cuatrimestre_id']
                    );
                    $nombreAlumno = $this->slugNombreAlumno($dataset['inscripcion']);
                    $temporal = $this->crearPdfTemporal($dataset);
                    $temporales[] = $temporal;
                    $this->agregarPdf($fpdi, $temporal);
                }

                $contenido = $fpdi->Output('S');
                $nombre = "BOLETAS_{$nombreAlumno}.pdf";

                return response($contenido, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => ($accion === 'download' ? 'attachment' : 'inline') . '; filename="' . $nombre . '"',
                    'Cache-Control' => 'private, max-age=0, must-revalidate',
                ]);
            } finally {
                $this->limpiarTemporales($temporales);
            }
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'boletas_alumno_');
        if ($zipPath === false) {
            abort(500, 'No fue posible crear el archivo temporal.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            abort(500, 'No fue posible crear el ZIP de alumnos.');
        }

        try {
            foreach ($porAlumno as $itemsAlumno) {
                $fpdi = new Fpdi();
                $temporales = [];
                $nombreAlumno = 'ALUMNO';

                try {
                    foreach ($itemsAlumno as $item) {
                        $dataset = $boletas->datasetBoleta(
                            $item['alumno_id'],
                            $licenciaturaId,
                            $modalidadId,
                            $generacionId,
                            $item['cuatrimestre_id']
                        );
                        $nombreAlumno = $this->slugNombreAlumno($dataset['inscripcion']);
                        $temporal = $this->crearPdfTemporal($dataset);
                        $temporales[] = $temporal;
                        $this->agregarPdf($fpdi, $temporal);
                    }

                    $zip->addFromString("BOLETAS_{$nombreAlumno}.pdf", $fpdi->Output('S'));
                } finally {
                    $this->limpiarTemporales($temporales);
                }
            }
        } finally {
            $zip->close();
        }

        return response()->download($zipPath, "{$nombreBase}_POR_ALUMNO.zip", [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function crearPdfTemporal(array $dataset): string
    {
        $path = tempnam(sys_get_temp_dir(), 'boleta_pdf_');
        if ($path === false) {
            abort(500, 'No fue posible crear un PDF temporal.');
        }

        $contenido = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.boletaCalificacionPDF', $dataset)
            ->setPaper('letter', 'portrait')
            ->output();

        file_put_contents($path, $contenido);

        return $path;
    }

    private function agregarPdf(Fpdi $destino, string $archivo): void
    {
        $paginas = $destino->setSourceFile($archivo);

        for ($pagina = 1; $pagina <= $paginas; $pagina++) {
            $tpl = $destino->importPage($pagina);
            $size = $destino->getTemplateSize($tpl);
            $destino->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $destino->useTemplate($tpl);
        }
    }

    private function limpiarTemporales(array $archivos): void
    {
        foreach ($archivos as $archivo) {
            if (is_string($archivo) && is_file($archivo)) {
                @unlink($archivo);
            }
        }
    }

    private function nombreBase(int $licenciaturaId, int $modalidadId, int $generacionId, BoletaService $boletas): string
    {
        $lic = $boletas->licenciaturasConAlumnos()->firstWhere('id', $licenciaturaId)?->nombre ?? 'LICENCIATURA';
        $mod = $boletas->modalidadesConAlumnos($licenciaturaId)->firstWhere('id', $modalidadId)?->nombre ?? 'MODALIDAD';
        $gen = $boletas->generacionesConAlumnos($licenciaturaId, $modalidadId)->firstWhere('id', $generacionId)?->generacion ?? 'GENERACION';

        return Str::of("BOLETAS_{$lic}_{$mod}_{$gen}")
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9_\-]+/', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();
    }

    private function slugNombreAlumno($alumno): string
    {
        return Str::of(
            $alumno->apellido_paterno . '_' . $alumno->apellido_materno . '_' . $alumno->nombre . '_' . $alumno->matricula
        )
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9_\-]+/', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();
    }
}
