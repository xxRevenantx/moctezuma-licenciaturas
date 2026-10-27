<?php

use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Services\DocumentosIdentidad\ExpedienteIdentidadExportService;
use Illuminate\Support\Facades\File;
use setasign\Fpdi\Fpdi;

function alumnoPdfPrueba(string $paterno, string $materno, string $nombre, string $carrera, string $generacion): Inscripcion
{
    $alumno = new Inscripcion();
    $alumno->apellido_paterno = $paterno;
    $alumno->apellido_materno = $materno;
    $alumno->nombre = $nombre;
    $alumno->matricula = 'MAT-TEST';

    $licenciatura = new Licenciatura();
    $licenciatura->nombre = $carrera;
    $cohorte = new Generacion();
    $cohorte->generacion = $generacion;

    $alumno->setRelation('licenciatura', $licenciatura);
    $alumno->setRelation('generacion', $cohorte);

    return $alumno;
}

function generarPdfPrueba(string $ruta, string $texto, int $paginas = 1): void
{
    $pdf = new Fpdi();
    foreach (range(1, $paginas) as $pagina) {
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(100, 10, "$texto - $pagina", 0, 1);
    }
    $pdf->Output('F', $ruta);
}

test('el ZIP clasifica los PDF imprimibles por licenciatura y respeta el numero de paginas', function () {
    if (! class_exists(ZipArchive::class)) {
        $this->markTestSkipped('Se requiere la extension ZIP de PHP.');
    }

    $service = app(ExpedienteIdentidadExportService::class);
    $temporal = storage_path('app/tests-expedientes-' . uniqid());
    File::ensureDirectoryExists($temporal);

    try {
        $derecho1 = alumnoPdfPrueba('ALCANTARA', 'RUIZ', 'ANA', 'Derecho', '2025-2028');
        $derecho2 = alumnoPdfPrueba('BENITEZ', 'CRUZ', 'LUIS', 'Derecho', '2026-2029');
        $arquitectura = alumnoPdfPrueba('CASTILLO', 'LUNA', 'EVA', 'Arquitectura', '2025-2028');

        $ruta1 = "$temporal/ana.pdf";
        $ruta2 = "$temporal/luis.pdf";
        $ruta3 = "$temporal/eva.pdf";
        generarPdfPrueba($ruta1, 'ANA', 2);
        generarPdfPrueba($ruta2, 'LUIS', 1);
        generarPdfPrueba($ruta3, 'EVA', 1);

        $carpeta = new ReflectionMethod($service, 'carpetaPdfImprimibles');
        expect($carpeta->invoke($service, $derecho1, 'generacion'))
            ->toBe('GENERACION_2025_2028/DERECHO');
        expect($carpeta->invoke($service, $derecho1, 'licenciatura'))
            ->toBe('DERECHO');
        expect($carpeta->invoke($service, $derecho2, 'licenciatura'))
            ->toBe('DERECHO');

        $zip = new ZipArchive();
        $rutaZip = "$temporal/prueba.zip";
        expect($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE))->toBeTrue();

        $grupos = [
            'DERECHO' => [
                ['alumno' => $derecho1, 'ruta' => $ruta1, 'paginas' => 2],
                ['alumno' => $derecho2, 'ruta' => $ruta2, 'paginas' => 1],
            ],
            'ARQUITECTURA' => [
                ['alumno' => $arquitectura, 'ruta' => $ruta3, 'paginas' => 1],
            ],
        ];

        (new ReflectionMethod($service, 'agregarPdfsImprimibles'))
            ->invoke($service, $zip, $grupos, $temporal);
        expect($zip->close())->toBeTrue();

        $lector = new ZipArchive();
        expect($lector->open($rutaZip))->toBeTrue();
        expect($lector->locateName('DERECHO/001_ALCANTARA_RUIZ_ANA_GEN_2025_2028.pdf'))->not->toBeFalse();
        expect($lector->locateName('DERECHO/002_BENITEZ_CRUZ_LUIS_GEN_2026_2029.pdf'))->not->toBeFalse();
        expect($lector->locateName('DERECHO/00_TODOS_LOS_EXPEDIENTES_PARA_IMPRIMIR.pdf'))->not->toBeFalse();
        expect($lector->locateName('ARQUITECTURA/00_TODOS_LOS_EXPEDIENTES_PARA_IMPRIMIR.pdf'))->not->toBeFalse();

        $generalDerecho = "$temporal/validar.pdf";
        File::put($generalDerecho, $lector->getFromName('DERECHO/00_TODOS_LOS_EXPEDIENTES_PARA_IMPRIMIR.pdf'));
        $lector->close();
        expect((new Fpdi())->setSourceFile($generalDerecho))->toBe(3);
    } finally {
        File::deleteDirectory($temporal);
    }
});
