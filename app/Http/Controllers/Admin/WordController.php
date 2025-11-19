<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Html as HtmlWriter;
use PhpOffice\PhpWord\Shared\Converter; // 👈 importante

class WordController extends Controller
{
    public function acta_examen(Request $request)
    {
        // 1) Directorio temporal para PhpWord
        $tempPath = storage_path('app/phpword-temp');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0777, true);
        }
        Settings::setTempDir($tempPath);

        // 2) Renderizar el Blade a HTML
        $html = view('admin.word.acta-examen', [
            // aquí puedes pasar variables reales si quieres
            'nombreAlumno' => 'Juan Pérez de Prueba',
        ])->render();

        // 3) Crear documento y sección
        $phpWord = new PhpWord();
           // 👉 Sección tamaño OFICIO (Legal 8.5 x 14")
        $section = $phpWord->addSection([
        'pageSizeW'    => Converter::inchToTwip(8.5),  // ancho oficio
        'pageSizeH'    => Converter::inchToTwip(13.38),   // alto oficio
        'marginTop'    => Converter::cmToTwip(2.5),
        'marginBottom' => Converter::cmToTwip(2.5),
        'marginLeft'   => Converter::cmToTwip(2.5),
        'marginRight'  => Converter::cmToTwip(2.5),
    ]);

        // 4) Insertar el HTML del Blade en la sección
        HtmlWriter::addHtml($section, $html, false, false);

        // 5) Guardar el archivo en storage
        $fileName = 'Acta_Examen_' . time() . '.docx';
        $filePath = storage_path('app/' . $fileName);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        // 6) Descargar y borrar después de enviar
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
