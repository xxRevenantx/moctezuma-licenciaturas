<?php

namespace App\Services\ActasExamen;

use App\Models\ActaExamenProfesional;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class ActaExamenDocumentoService
{
    public function nombreSeguro(string $texto): string
    {
        return trim(Str::upper(Str::slug($texto, '_')), '_') ?: 'SIN_NOMBRE';
    }

    public function nombreArchivo(ActaExamenProfesional $acta): string
    {
        $d = $acta->datos_alumno;
        return 'ACTA_' . $this->nombreSeguro($d['apellidos_nombre'] ?? (string) $acta->alumno_id) . '_' . $acta->id;
    }

    public function pdf(Collection $actas): string
    {
        $actas->each->loadMissing('alumno');
        return Pdf::loadView('admin.actas-examen.pdf', ['actas' => $actas, 'documentos' => $this])
            ->setPaper('letter', 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false])
            ->output();
    }

    public function data(ActaExamenProfesional $acta): array
    {
        $alumno = $acta->datos_alumno ?? [];
        $sinodo = $acta->sinodo ?? [];
        $format = static fn ($date) => $date ? $date->locale('es')->translatedFormat('d \d\e F \d\e Y') : '________________';

        return [
            'nombre' => $alumno['nombre_completo'] ?? '',
            'matricula' => $alumno['matricula'] ?? '',
            'cct' => $alumno['cct'] ?? '12PSU0173I',
            'licenciatura' => $alumno['licenciatura'] ?? '',
            'generacion' => $alumno['generacion'] ?? '',
            'fecha' => $format($acta->fecha_examen),
            'hora' => substr((string) $acta->hora_examen, 0, 5),
            'autorizacion' => $acta->autorizacion_numero ?: '________',
            'entidad' => $acta->entidad_federativa ?: 'Guerrero',
            'titulo' => $acta->titulo_profesional,
            'acuerdo' => $acta->acuerdo_numero ?: '________',
            'fecha_acuerdo' => $format($acta->acuerdo_fecha),
            'presidente' => $sinodo[0]['nombre'] ?? '________________________________',
            'secretario' => $sinodo[1]['nombre'] ?? '________________________________',
            'vocal' => $sinodo[2]['nombre'] ?? '________________________________',
            'resultado' => $acta->resultado ?: '________________',
            'rector' => $acta->rector ?: '________________________________',
            'director' => $acta->director ?: '________________________________',
            'revisado' => $acta->revisado_por ?: '________________________________',
            'jefe' => $acta->jefe_registro ?: '________________________________',
            'revision' => $format($acta->fecha_revision),
            'final' => $acta->estado === 'autorizada',
            'estado' => $acta->estado,
            'aprobado' => $acta->resultado && ! str_contains(mb_strtolower($acta->resultado), 'no aprobado'),
        ];
    }

    public function fotoDataUri(ActaExamenProfesional $acta): ?string
    {
        $foto = $this->fotoArchivo($acta);
        return $foto ? 'data:' . $foto['mime'] . ';base64,' . base64_encode($foto['content']) : null;
    }

    public function fotoArchivo(ActaExamenProfesional $acta): ?array
    {
        if (! $acta->incluir_foto) return null;
        $ruta = $acta->foto_snapshot_path;
        if ($ruta && Storage::disk('local')->exists($ruta)) {
            $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
            $mime = match ($ext) { 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', default => 'image/jpeg' };
            return ['content' => Storage::disk('local')->get($ruta), 'mime' => $mime];
        }
        return $acta->estado === 'autorizada' ? null : $acta->alumno?->fotoArchivo();
    }

    /** Word con dos páginas explícitas por acta y encabezado de la plantilla oficial. */
    public function word(Collection $actas): string
    {
        $word = new PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(9);
        $word->addParagraphStyle('parrafoActa', ['spaceAfter' => 200, 'lineHeight' => 1.25, 'alignment' => Jc::BOTH]);
        $word->addParagraphStyle('centradoActa', ['alignment' => Jc::CENTER, 'spaceAfter' => 160]);
        $word->addParagraphStyle('firmasActa', ['alignment' => Jc::CENTER, 'spaceBefore' => 350, 'spaceAfter' => 160]);
        $word->addTitleStyle(1, ['name' => 'Arial', 'size' => 11, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 250]);

        $fotosTemporales = [];
        foreach ($actas as $acta) {
            $acta->loadMissing('alumno');
            $d = $this->data($acta);
            $section = $word->addSection([
                'pageSizeW' => Converter::inchToTwip(8.5),
                'pageSizeH' => Converter::inchToTwip(11),
                'marginTop' => Converter::cmToTwip(1.5),
                'marginBottom' => Converter::cmToTwip(1.5),
                'marginLeft' => Converter::cmToTwip(2.25),
                'marginRight' => Converter::cmToTwip(2.25),
            ]);

            $logo = resource_path('actas-examen/img');
            $logos = $section->addTable(['borderSize' => 0, 'cellMargin' => 25]);
            $logos->addRow();
            $logos->addCell(2200)->addImage($logo . '/seg.jpeg', ['width' => 62, 'height' => 49]);
            $logos->addCell(6400)->addImage($logo . '/moctezuma.png', ['width' => 245, 'height' => 62]);
            $section->addText('CLAVE: C.C.T. ' . $d['cct'], ['size' => 8, 'bold' => true], 'centradoActa');
            $section->addTitle('ACTA DE EXAMEN PROFESIONAL', 1);
            if (! $d['final']) {
                $section->addText(($d['estado'] === 'cancelada' ? 'ACTA CANCELADA - NO VÁLIDA' : 'DOCUMENTO PRELIMINAR - SIN AUTORIZAR'), ['bold' => true, 'color' => '9B2424', 'size' => 9], 'centradoActa');
            }
            $alumTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 120]);
            $alumTable->addRow();
            $fotoCelda = $alumTable->addCell(1450, ['valign' => 'top', 'borderSize' => 8]);
            $foto = $this->fotoArchivo($acta);
            if ($foto) {
                $tmp = tempnam(sys_get_temp_dir(), 'foto_acta_');
                file_put_contents($tmp, $foto['content']);
                $fotosTemporales[] = $tmp;
                $fotoCelda->addImage($tmp, ['width' => 65, 'height' => 80]);
            } else {
                $fotoCelda->addText(' ', ['size' => 10]);
                $fotoCelda->addText(' ', ['size' => 10]);
            }
            $alumTable->addCell(6900)->addText(
                'Entidad federativa: ' . $d['entidad'] . '. Número de autorización ' . $d['autorizacion']
                . ', siendo las ' . $d['hora'] . ' horas del día ' . $d['fecha'] . '.',
                ['size' => 9], 'parrafoActa'
            );
            $section->addText('Se reunió el Sínodo integrado por los CC.:', [], 'parrafoActa');
            foreach (['presidente', 'secretario', 'vocal'] as $rol) {
                $section->addText($d[$rol], ['underline' => 'single'], ['spaceAfter' => 80, 'indentation' => ['left' => 850]]);
            }
            $section->addText('Para aplicar el examen de titulación de:', [], ['spaceBefore' => 250]);
            $section->addText('C. ' . $d['nombre'], ['bold' => true, 'underline' => 'single'], ['spaceAfter' => 200]);
            $section->addText(
                'Bajo la Presidencia de la primera persona, con carácter de secretaría la segunda y vocalía la tercera, '
                . 'para proceder a efectuar la evaluación de EXAMEN PROFESIONAL para obtener el TÍTULO de '
                . $d['titulo'] . ', con reconocimiento de validez oficial de la Secretaría de Educación Guerrero, '
                . 'según acuerdo número ' . $d['acuerdo'] . ' de fecha ' . $d['fecha_acuerdo']
                . '. Los miembros del Sínodo examinaron a la persona sustentante y después de deliberar entre sí, '
                . 'resolvieron declararlo(a): ' . $d['resultado'] . '.', [], 'parrafoActa'
            );
            if ($d['aprobado']) {
                $section->addText('A continuación, la presidencia del sínodo comunicó al sustentante el resultado obtenido y le tomó la protesta de ley, en los términos siguientes:', [], 'parrafoActa');
                $section->addText('¿PROTESTA USTED EJERCER LA CARRERA CON ENTUSIASMO Y HONRADEZ, VELAR SIEMPRE POR EL PRESTIGIO Y BUEN NOMBRE DE ÉSTA Y CONTINUAR ESFORZÁNDOSE POR MEJORAR SU PREPARACIÓN EN TODOS LOS ÓRDENES PARA GARANTIZAR LOS INTERESES DE LA JUVENTUD Y DE LA PATRIA?', [], 'parrafoActa');
                $section->addText('“SÍ PROTESTO”', ['bold' => true, 'underline' => 'single'], 'centradoActa');
                $section->addText('SI ASÍ LO HICIERE QUE SUS COMPAÑEROS Y LA NACIÓN SE LO PREMIEN Y SI NO, SE LO DEMANDEN.', [], 'parrafoActa');
            } else {
                $section->addText('La protesta de ley únicamente se asentará cuando el resultado sea aprobatorio y el acta se haya autorizado.', ['italic' => true], 'parrafoActa');
            }

            $section->addPageBreak();
            if (! $d['final']) $section->addText($d['estado'] === 'cancelada' ? 'ACTA CANCELADA - NO VÁLIDA' : 'DOCUMENTO PRELIMINAR - SIN AUTORIZAR', ['bold' => true, 'color' => '9B2424', 'size' => 9], 'centradoActa');
            $section->addText('Terminado el acto se levanta, para constancia, la presente acta, que firman de conformidad el(la) Sustentante, el(la) Rector(a) y el(la) Director(a).', [], 'parrafoActa');
            $section->addText('__________________________________________________', [], ['alignment' => Jc::CENTER, 'spaceBefore' => 440]);
            $section->addText($d['nombre'] . ' — NOMBRE Y FIRMA DEL (DE LA) SUSTENTANTE', ['size' => 8], 'centradoActa');
            foreach (['presidente' => 'PRESIDENTE(A)', 'secretario' => 'SECRETARIO(A)', 'vocal' => 'VOCAL'] as $campo => $etiqueta) {
                $s = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
                $s->addRow();
                $s->addCell(4200)->addText($d[$campo] . "\n" . $etiqueta, ['size' => 9], 'firmasActa');
                $s->addCell(4200)->addText('____________________________' . "\n" . 'FIRMA', ['size' => 9], 'firmasActa');
            }
            $directivos = $section->addTable(['borderSize' => 0, 'cellMargin' => 90]);
            $directivos->addRow();
            $directivos->addCell(4200)->addText('____________________________' . "\n" . $d['rector'] . "\nRECTOR(A)", ['size' => 9], 'firmasActa');
            $directivos->addCell(4200)->addText('____________________________' . "\n" . $d['director'] . "\nDIRECTOR(A)", ['size' => 9], 'firmasActa');
            $rev = $section->addTable(['borderSize' => 8, 'borderColor' => '333333', 'cellMargin' => 170]);
            $rev->addRow(1700);
            $rev->addCell(4200)->addText('REVISADO Y CONFRONTADO POR' . "\n" . $d['revisado'] . "\n\nFECHA: " . $d['revision'], ['size' => 8], 'centradoActa');
            $rev->addCell(4200)->addText('JEFE(A) DEL DEPARTAMENTO DE REGISTRO Y CERTIFICACIÓN' . "\n" . $d['jefe'] . "\n\nNOMBRE, FIRMA Y SELLO", ['size' => 8], 'centradoActa');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'acta_word_');
        $file = $tmp . '.docx';
        @unlink($tmp);
        try {
            IOFactory::createWriter($word, 'Word2007')->save($file);
            return $file;
        } finally {
            foreach ($fotosTemporales as $foto) { @unlink($foto); }
        }
    }
}
