<?php

namespace App\Http\Controllers;

use App\Models\ActaExamenProfesional;
use App\Services\ActasExamen\ActaExamenDocumentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ActaExamenProfesionalController extends Controller
{
    public function pdf(ActaExamenProfesional $acta, ActaExamenDocumentoService $service)
    {
        $content = $service->pdf(collect([$acta]));
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $service->nombreArchivo($acta) . '.pdf"',
        ]);
    }

    public function word(ActaExamenProfesional $acta, ActaExamenDocumentoService $service)
    {
        return response()->download($service->word(collect([$acta])), $service->nombreArchivo($acta) . '.docx')
            ->deleteFileAfterSend(true);
    }

    /** Exportación de hasta 50 actas existentes: ni descarga ni exportación autorizan actas. */
    public function exportar(Request $request, ActaExamenDocumentoService $service)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:50',
            'ids.*' => 'required|integer|distinct|exists:actas_examen_profesional,id',
            'formato' => 'required|in:pdf,word,ambos',
            'salida' => 'required|in:consolidado,separados',
        ]);
        $actas = ActaExamenProfesional::with('alumno')->whereIn('id', $data['ids'])->get();
        $actas = $actas->sortBy(fn ($a) => implode('|', [
            $a->datos_alumno['licenciatura'] ?? '',
            $a->datos_alumno['generacion'] ?? '',
            $a->datos_alumno['apellidos_nombre'] ?? '',
        ]), SORT_NATURAL | SORT_FLAG_CASE)->values();
        abort_if($actas->count() !== count($data['ids']), 422, 'Selección incompleta.');

        if ($data['salida'] === 'consolidado' && $data['formato'] === 'pdf') {
            return response($service->pdf($actas), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="ACTAS_EXAMEN_CONSOLIDADO.pdf"',
            ]);
        }
        if ($data['salida'] === 'consolidado' && $data['formato'] === 'word') {
            return response()->download($service->word($actas), 'ACTAS_EXAMEN_CONSOLIDADO.docx')->deleteFileAfterSend(true);
        }

        abort_unless(class_exists(ZipArchive::class), 500, 'Se requiere la extensión PHP ZIP.');
        $base = storage_path('app/private/actas-examen/' . uniqid('exp_', true));
        $zipPath = $base . '.zip';
        File::ensureDirectoryExists(dirname($zipPath));
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'No fue posible crear el archivo comprimido.');
        }
        try {
            if ($data['salida'] === 'consolidado') {
                $zip->addFromString('ACTAS_EXAMEN_CONSOLIDADO.pdf', $service->pdf($actas));
                $wordFile = $service->word($actas);
                try { $zip->addFile($wordFile, 'ACTAS_EXAMEN_CONSOLIDADO.docx'); }
                finally { /* La fuente se elimina después de cerrar el ZIP. */ }
                $tempWords = [$wordFile];
            } else {
                $tempWords = [];
                foreach ($actas as $acta) {
                    $d = $acta->datos_alumno;
                    $carpeta = $service->nombreSeguro($d['licenciatura'] ?? 'Licenciatura') . '/'
                        . $service->nombreSeguro($d['generacion'] ?? 'Generacion') . '/';
                    $nombre = $service->nombreArchivo($acta);
                    if (in_array($data['formato'], ['pdf', 'ambos'], true)) {
                        $zip->addFromString($carpeta . $nombre . '.pdf', $service->pdf(collect([$acta])));
                    }
                    if (in_array($data['formato'], ['word', 'ambos'], true)) {
                        $wordFile = $service->word(collect([$acta]));
                        $tempWords[] = $wordFile;
                        $zip->addFile($wordFile, $carpeta . $nombre . '.docx');
                    }
                }
            }
        } finally {
            $zip->close();
            foreach ($tempWords ?? [] as $file) { @unlink($file); }
        }

        return response()->download($zipPath, 'ACTAS_EXAMEN_PRO_2_0.zip')->deleteFileAfterSend(true);
    }
}
