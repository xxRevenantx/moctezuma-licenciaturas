<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Inscripcion;

class DocumentosUnificadosController extends Controller
{
    public function DocumentosUnificadosAlumno($id)
    {
        $alumno = Inscripcion::findOrFail($id);

        $matricula = preg_replace('/[^A-Za-z0-9]/', '', (string) $alumno->matricula);
        $curp      = preg_replace('/[^A-Za-z0-9]/', '', (string) $alumno->CURP);
        $nombreCompletoSlug = strtoupper(trim("{$alumno->nombre}_{$alumno->apellido_paterno}_{$alumno->apellido_materno}"));
        $nombreCompleto = strtoupper(trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"));

        // Mapeo: carpeta => columna en BD
        $map = [
            'curp'                  => 'CURP_documento',
            'actas'                 => 'acta_nacimiento',
            'certificado_estudios'  => 'certificado_estudios',
            'comprobante_domicilio' => 'comprobante_domicilio',
            'certificado_medico'    => 'certificado_medico',
            'ine'                   => 'ine',
        ];

        $pdf = new Fpdi();
        $errores = [];
        $importadas = 0;

        foreach ($map as $carpeta => $columna) {
            // 1) Intentar con el nombre guardado en BD
            $archivo = $alumno->{$columna};

            // 2) Si no hay en BD, intentar el patrón histórico
            if (!$archivo) {
                // Nombres históricos esperados
                $patrones = [
                    'curp'                  => "CURP_{$matricula}_{$curp}.pdf",
                    'actas'                 => "ACTADENACIMIENTO_{$matricula}_{$curp}.pdf",
                    'certificado_estudios'  => "CERTIFICADODEESTUDIOS_{$matricula}_{$curp}.pdf",
                    'comprobante_domicilio' => "COMPROBANTEDOMICILIO_{$matricula}_{$curp}.pdf",
                    'certificado_medico'    => "CERTIFICADOMEDICO_{$matricula}_{$curp}.pdf",
                    'ine'                   => "INE_{$matricula}_{$curp}.pdf",
                ];
                $archivo = $patrones[$carpeta] ?? null;
            }

            if (!$archivo) {
                $errores[] = "No hay nombre de archivo para {$columna}.";
                continue;
            }

            $path = public_path("storage/documentos/{$carpeta}/{$archivo}");

            if (!File::exists($path)) {
                $errores[] = "Archivo no encontrado: documentos/{$carpeta}/{$archivo}";
                Log::warning("Archivo no encontrado: {$path}");
                continue;
            }

            try {
                $pageCount = $pdf->setSourceFile($path);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $template = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($template);

                    // FPDI devuelve width/height y orientation ('P' o 'L')
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                    $importadas++;
                }
            } catch (\Throwable $e) {
                $msg = "Archivo corrupto o inválido: documentos/{$carpeta}/{$archivo}";
                $errores[] = $msg;
                Log::error("Error al procesar {$path}: " . $e->getMessage());
            }
        }

        // Si no importamos ninguna página, devolvemos un PDF con mensaje
        if ($importadas === 0) {
            $pdf = new Fpdi(); // reiniciar
            $pdf->AddPage();
            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 8, utf8_decode(
                "No se encontraron documentos válidos para este alumno.\n\n" .
                "Nombre: {$nombreCompleto}\nMatrícula: {$matricula}\nCURP: {$curp}"
            ));
        }

        // Asegurar carpeta temp
        $tempDir = public_path('storage/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $fileName = "DOCUMENTOS_UNIFICADOS_{$nombreCompletoSlug}_{$matricula}_{$curp}.pdf";
        $tempPath = "{$tempDir}/{$fileName}";

        // Guardar PDF
        $pdf->Output($tempPath, 'F');

        // Flashear errores si existen
        if (!empty($errores)) {
            session()->flash('errores_documentos', $errores);
        }

        // Entregar inline
        return response()->file($tempPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}
