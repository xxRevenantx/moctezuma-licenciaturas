<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Throwable;

class DocumentosUnificadosController extends Controller
{
    public function DocumentosUnificadosAlumno(int $id, DocumentoIdentidadService $service)
    {
        Gate::authorize('documentos-identidad.descargar');

        $alumno = Inscripcion::query()
            ->with(['licenciatura:id,nombre', 'generacion:id,generacion'])
            ->findOrFail($id);

        $disk = $service->disk();
        $pdf = new Fpdi();
        $documentos = [];
        $faltantes = [];
        $errores = [];

        foreach ($service->tipos() as $tipo => $config) {
            $documento = $service->actual($alumno->id, $tipo);

            if (! $documento) {
                $faltantes[] = $config['label'];
                continue;
            }

            if (! Storage::disk($disk)->exists($documento->ruta)) {
                $faltantes[] = $config['label'];
                $errores[] = "Archivo físico faltante: {$config['label']}";
                Log::warning('Documento de identidad faltante al unificar', [
                    'documento_id' => $documento->id,
                    'inscripcion_id' => $alumno->id,
                    'ruta' => $documento->ruta,
                ]);
                continue;
            }

            $documentos[] = [
                'tipo' => $tipo,
                'label' => $config['label'],
                'documento' => $documento,
                'ruta' => Storage::disk($disk)->path($documento->ruta),
            ];
        }

        $this->agregarPortada($pdf, $alumno, $documentos, $faltantes);

        foreach ($documentos as $item) {
            try {
                $pageCount = $pdf->setSourceFile($item['ruta']);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $template = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($template);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                }
            } catch (Throwable $e) {
                $errores[] = "No fue posible integrar {$item['label']}.";
                Log::error('Error al unificar documento de identidad', [
                    'documento_id' => $item['documento']->id,
                    'inscripcion_id' => $alumno->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($errores !== []) {
            $this->agregarPaginaErrores($pdf, $errores);
        }

        $nombreAlumno = Str::of(trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"))
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_');
        $matricula = Str::of((string) $alumno->matricula)->ascii()->replaceMatches('/[^A-Za-z0-9]+/', '');
        $fileName = "EXPEDIENTE_IDENTIDAD_{$nombreAlumno}_{$matricula}.pdf";
        $contenido = $pdf->Output('S');

        return response($contenido, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Content-Length' => (string) strlen($contenido),
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function agregarPortada(Fpdi $pdf, Inscripcion $alumno, array $documentos, array $faltantes): void
    {
        $pdf->AddPage('P', 'Letter');
        $pdf->SetFillColor(0, 100, 146);
        $pdf->Rect(0, 0, 216, 34, 'F');
        $pdf->SetFillColor(136, 172, 46);
        $pdf->Rect(0, 34, 216, 4, 'F');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetXY(14, 10);
        $pdf->Cell(188, 8, utf8_decode('EXPEDIENTE DE IDENTIDAD'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(14);
        $pdf->Cell(188, 6, utf8_decode('Centro Universitario Moctezuma'), 0, 1, 'C');

        $pdf->SetTextColor(17, 24, 39);
        $pdf->SetXY(18, 50);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(180, 7, utf8_decode('DATOS DEL ALUMNO'), 0, 1);
        $pdf->SetFont('Arial', '', 10);

        $nombre = trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}");
        $filas = [
            ['Nombre', $nombre],
            ['Matrícula', (string) $alumno->matricula],
            ['CURP', (string) $alumno->CURP],
            ['Licenciatura', (string) optional($alumno->licenciatura)->nombre],
            ['Generación', (string) optional($alumno->generacion)->generacion],
            ['Generado', now()->format('d/m/Y H:i')],
        ];

        foreach ($filas as [$etiqueta, $valor]) {
            $pdf->SetX(18);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(34, 7, utf8_decode($etiqueta . ':'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->MultiCell(146, 7, utf8_decode($valor ?: '—'), 0, 'L');
        }

        $pdf->Ln(5);
        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(180, 7, utf8_decode('ÍNDICE DOCUMENTAL'), 0, 1);
        $pdf->SetFont('Arial', '', 9);

        $entregados = collect($documentos)->pluck('label')->all();
        $tipos = config('documentos_identidad.types', []);
        $numero = 1;

        foreach ($tipos as $config) {
            $esta = in_array($config['label'], $entregados, true);
            $pdf->SetX(18);
            $pdf->SetTextColor($esta ? 22 : 153, $esta ? 101 : 27, $esta ? 52 : 27);
            $estado = $esta ? 'INCLUIDO' : 'PENDIENTE';
            $pdf->Cell(180, 7, utf8_decode("{$numero}. {$config['label']} — {$estado}"), 0, 1);
            $numero++;
        }

        $pdf->SetTextColor(17, 24, 39);
        $pdf->SetY(245);
        $pdf->SetFont('Arial', 'I', 8);
        $nota = $faltantes === []
            ? 'El expediente contiene todos los documentos configurados.'
            : 'Documentos pendientes: ' . implode(', ', $faltantes) . '.';
        $pdf->MultiCell(180, 5, utf8_decode($nota), 0, 'L');
    }

    protected function agregarPaginaErrores(Fpdi $pdf, array $errores): void
    {
        $pdf->AddPage('P', 'Letter');
        $pdf->SetTextColor(153, 27, 27);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetXY(18, 22);
        $pdf->Cell(180, 8, utf8_decode('ADVERTENCIAS DEL EXPEDIENTE'), 0, 1);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->SetFont('Arial', '', 10);

        foreach (array_unique($errores) as $error) {
            $pdf->SetX(18);
            $pdf->MultiCell(180, 7, utf8_decode('• ' . $error), 0, 'L');
        }
    }
}
