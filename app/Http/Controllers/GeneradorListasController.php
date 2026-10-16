<?php

namespace App\Http\Controllers;

use App\Services\GeneradorListasExportService;
use App\Services\GeneradorListasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class GeneradorListasController extends Controller
{
    public function exportar(Request $request, GeneradorListasService $listas, GeneradorListasExportService $exportar)
    {
        $data = $request->validate([
            'formato' => ['required', Rule::in(['pdf', 'word', 'zip_pdf', 'zip_word', 'zip_ambos'])],
            'licenciatura_ids' => ['sometimes', 'array', 'max:100'],
            'licenciatura_ids.*' => ['integer', 'distinct', 'exists:licenciaturas,id'],
            'generacion_ids' => ['sometimes', 'array', 'max:100'],
            'generacion_ids.*' => ['integer', 'distinct', 'exists:generaciones,id'],
            'cuatrimestre_ids' => ['sometimes', 'array', 'max:100'],
            'cuatrimestre_ids.*' => ['integer', 'distinct', 'exists:cuatrimestres,id'],
            'modalidad_id' => ['nullable', 'integer', 'exists:modalidades,id'],
            'procedencia' => ['required', Rule::in(['todos', 'true', 'false'])],
            'estado' => ['required', Rule::in(['activos', 'bajas', 'egresados', 'todos'])],
            'ciclo_escolar' => ['nullable', 'string', 'max:50', Rule::exists('historial_inscripciones', 'ciclo_escolar')],
            'busqueda' => ['nullable', 'string', 'max:120'],
            'columnas' => ['required', 'array', 'min:1', 'max:22'],
            'columnas.*' => ['required', 'string', 'distinct', Rule::in(array_keys(GeneradorListasService::CAMPOS))],
            'cabeceras' => ['sometimes', 'array'],
            'cabeceras.*' => ['nullable', 'string', 'max:60'],
            'modalidad_texto' => ['required', Rule::in(['real', 'escolarizada', 'semiescolarizada'])],
            'agrupar' => ['required', Rule::in(['licenciatura_generacion', 'licenciatura', 'generacion', 'cuatrimestre', 'modalidad', 'sin_agrupar'])],
            'orden' => ['required', Rule::in(['apellidos', 'matricula', 'nombre', 'inscripcion'])],
            'orientacion' => ['required', Rule::in(['auto', 'horizontal', 'vertical'])],
            'titulo' => ['required', 'string', 'max:100'],
            'subtitulo' => ['nullable', 'string', 'max:120'],
            'todos_filtrados' => ['required', 'boolean'],
            'alumno_ids' => ['sometimes', 'array', 'max:1500'],
            'alumno_ids.*' => ['integer', 'distinct', 'exists:inscripciones,id'],
            'observacion_general' => ['nullable', 'string', 'max:300'],
            'observaciones' => ['sometimes', 'array', 'max:1500'],
            'observaciones.*' => ['nullable', 'string', 'max:300'],
        ]);

        if (! empty($data['cabeceras']) && array_diff(array_keys($data['cabeceras']), $data['columnas'])) {
            throw ValidationException::withMessages(['cabeceras' => 'Hay encabezados para columnas no seleccionadas.']);
        }
        $grupos = $listas->grupos($data);
        if (! $grupos) throw ValidationException::withMessages(['licenciatura_ids' => 'No hay alumnos para los filtros indicados.']);
        if (count($grupos) > 120) throw ValidationException::withMessages(['agrupar' => 'La selección supera las 120 listas por descarga. Reduce los filtros.']);
        $n = array_sum(array_map(fn ($g) => count($g['alumnos']), $grupos));
        if ($n > 6000) throw ValidationException::withMessages(['licenciatura_ids' => 'Máximo 6000 alumnos por solicitud; divide la descarga.']);

        $nombre = 'LISTAS_MOCTEZUMA_' . now()->format('Ymd_His');
        if ($data['formato'] === 'pdf') {
            $contenido = $exportar->pdf($grupos, $data);
            $this->registrar($request, $data, $n, count($grupos));
            return response($contenido, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $nombre . '.pdf"',
            ]);
        }
        if ($data['formato'] === 'word') {
            $archivo = $exportar->word($grupos, $data);
            $this->registrar($request, $data, $n, count($grupos));
            return response()->download($archivo, $nombre . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        }
        if (! class_exists(ZipArchive::class)) throw ValidationException::withMessages(['formato' => 'El servidor PHP necesita la extensión ZIP.']);

        $directorio = storage_path('app/phpword-temp');
        if (! is_dir($directorio)) mkdir($directorio, 0775, true);
        $rutaZip = $directorio . '/' . Str::uuid() . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'No se pudo crear el archivo ZIP.');
        }
        try {
            $usados = [];
            foreach ($grupos as $indice => $grupo) {
                $base = Str::ascii($grupo['titulo']);
                $base = trim(preg_replace('/[^A-Za-z0-9_-]+/', '_', $base), '_');
                $nombreGrupo = sprintf('%03d_%s', $indice + 1, mb_substr($base ?: 'LISTA', 0, 90));
                $unGrupo = [$grupo];
                if ($data['formato'] !== 'zip_word' && ! $zip->addFromString($nombreGrupo . '.pdf', $exportar->pdf($unGrupo, $data))) {
                    throw new \RuntimeException('No se pudo agregar el PDF al ZIP.');
                }
                if ($data['formato'] !== 'zip_pdf') {
                    $docx = $exportar->word($unGrupo, $data);
                    try {
                        if (! $zip->addFile($docx, $nombreGrupo . '.docx')) {
                            throw new \RuntimeException('No se pudo agregar el documento Word al ZIP.');
                        }
                        // ZipArchive lee el archivo hasta close(); limpiar después.
                        $usados[] = $docx;
                    } catch (\Throwable $e) { @unlink($docx); throw $e; }
                }
            }
        } catch (\Throwable $e) {
            $zip->close(); @unlink($rutaZip);
            foreach ($usados as $archivo) @unlink($archivo);
            throw $e;
        }
        $ok = $zip->close();
        foreach ($usados as $archivo) @unlink($archivo);
        if (! $ok) { @unlink($rutaZip); abort(500, 'No se pudo completar el ZIP.'); }
        $this->registrar($request, $data, $n, count($grupos));
        return response()->download($rutaZip, $nombre . '.zip', ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }

    private function registrar(Request $request, array $data, int $alumnos, int $grupos): void
    {
        DB::table('generador_listas_descargas')->insert([
            'user_id' => $request->user()->id,
            'formato' => $data['formato'],
            'total_alumnos' => $alumnos,
            'total_listas' => $grupos,
            'filtros' => json_encode(collect($data)->except(['observaciones'])->all(), JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }
}
