<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Matriculas\ExpedicionMatriculasExportService;
use App\Services\Matriculas\ExpedicionMatriculasService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use ZipArchive;

class ExpedicionMatriculasController extends Controller
{
    public function descargar(Request $request, ExpedicionMatriculasService $servicio, ExpedicionMatriculasExportService $exportador)
    {
        $data = $request->validate([
            'generacion_id' => ['required', 'integer', 'min:1', 'exists:generaciones,id'],
            'licenciaturas' => ['required', 'array', 'min:1', 'max:100'],
            'licenciaturas.*' => ['required', 'integer', 'min:1', 'distinct'],
            'alumnos' => ['required', 'array', 'min:1', 'max:10000'],
            'alumnos.*' => ['required', 'integer', 'min:1', 'distinct'],
            'columnas' => ['required', 'array', 'min:1', 'max:13'],
            'columnas.*' => ['required', 'string', 'distinct', Rule::in(array_keys(ExpedicionMatriculasService::COLUMNAS))],
            'estado' => ['required', Rule::in(['activos', 'bajas', 'todos'])],
            'seg' => ['required', Rule::in(['pendientes', 'asignados', 'todos'])],
            'modalidad_id' => ['nullable', 'integer', 'exists:modalidades,id'],
            'plantilla' => ['required', Rule::in(['seg', 'cum', 'personalizado'])],
            'salida' => ['required', Rule::in(['consolidado', 'individual', 'zip'])],
            'formato' => ['required', Rule::in(['excel', 'word', 'ambos'])],
            'meses' => ['sometimes', 'array'],
            'meses.*' => ['nullable', 'string', 'max:30'],
            'observaciones' => ['sometimes', 'array'],
            'observaciones.*' => ['nullable', 'string', 'max:500'],
        ]);

        $datos = $servicio->datos(
            (int) $data['generacion_id'], $data['licenciaturas'], $data['alumnos'], $data['columnas'],
            $data['estado'], $data['seg'], isset($data['modalidad_id']) ? (int) $data['modalidad_id'] : null,
            $data['meses'] ?? [], $data['observaciones'] ?? []
        );

        $salida = $data['salida'];
        $formatos = $data['formato'] === 'ambos' ? ['excel', 'word'] : [$data['formato']];
        $grupos = $datos['grupos'];
        $directorio = storage_path('app/expediciones-matriculas-temporales');
        if (! is_dir($directorio)) {
            mkdir($directorio, 0770, true);
        }
        $codigo = Str::random(14);
        $archivos = [];
        $nombreSeguro = Str::slug('solicitud-matriculas-' . $datos['generacion']);
        $separados = $salida !== 'consolidado';
        $paquetes = $separados ? array_map(fn ($grupo) => [$grupo], $grupos) : [$grupos];

        try {
            foreach ($paquetes as $i => $grupo) {
                $parte = $separados ? sprintf('-%02d-%s', $i + 1, Str::slug($grupo[0]['licenciatura'])) : '';
                $conjunto = [...$datos, 'grupos' => $grupo];
                foreach ($formatos as $formato) {
                    $extension = $formato === 'excel' ? 'xlsx' : 'docx';
                    $nombre = mb_substr($nombreSeguro . $parte, 0, 125) . '.' . $extension;
                    $archivo = $directorio . DIRECTORY_SEPARATOR . $codigo . '-' . $i . '-' . $extension;
                    if ($formato === 'excel') {
                        $exportador->excel($conjunto, $data['plantilla'], $archivo);
                    } else {
                        $exportador->word($conjunto, $data['plantilla'], $archivo);
                    }
                    $archivos[] = ['ruta' => $archivo, 'nombre' => $nombre];
                }
            }
            if (count($archivos) === 1 && $salida !== 'zip') {
                return response()->download($archivos[0]['ruta'], $archivos[0]['nombre'])
                    ->deleteFileAfterSend(true);
            }
            if (! class_exists(ZipArchive::class)) {
                throw new RuntimeException('Se requiere la extensión PHP zip para la descarga combinada.');
            }
            $zipFile = $directorio . DIRECTORY_SEPARATOR . $codigo . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No se pudo crear el paquete ZIP de matrículas.');
            }
            foreach ($archivos as $archivo) {
                $zip->addFile($archivo['ruta'], $archivo['nombre']);
            }
            if (! $zip->close()) {
                throw new RuntimeException('No se pudo finalizar el paquete ZIP.');
            }
            foreach ($archivos as $archivo) {
                @unlink($archivo['ruta']);
            }
            return response()->download($zipFile, $nombreSeguro . '.zip')->deleteFileAfterSend(true);
        } catch (\Throwable $exception) {
            foreach ($archivos as $archivo) {
                @unlink($archivo['ruta']);
            }
            if (isset($zipFile)) {
                @unlink($zipFile);
            }
            throw $exception;
        }
    }
}
