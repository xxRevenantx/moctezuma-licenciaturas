<?php

namespace App\Http\Controllers;

use App\Models\Dia;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{


    public function matricula(Request $request)
    {
        $licenciatura = $request->licenciatura_id;
        $modalidad = $request->modalidad_id;
        $filtrar_generacion = $request->filtrar_generacion;
        $filtar_foraneo = $request->filtar_foraneo; //
        $query = Inscripcion::where('licenciatura_id', $licenciatura)
            ->where('modalidad_id', $modalidad)
            ->where('generacion_id', $filtrar_generacion);
        if ($filtar_foraneo !== null) {
            $query->where('foraneo', $filtar_foraneo);
        }
        $matricula = $query->get();
        $generacion = Generacion::where('id', $filtrar_generacion)->first();
        $licenciatura_nombre = Licenciatura::where('id', $licenciatura)->first();
        $escuela = Escuela::all()->first();
        // Aquí puedes agregar la lógica para generar el PDF con los datos recibidos
        // Por ejemplo, puedes usar una librería como Dompdf o Snappy para generar el PDF

        $data = [
            'matricula' => $matricula,
            'generacion' => $generacion,
            'licenciatura_nombre' => $licenciatura_nombre,
            'escuela' => $escuela,
        ];
              $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.matriculaPDF', $data)->setPaper('letter', 'landscape');
             return $pdf->stream("Matricula.pdf");
    }


    // HORARIO SEMIESCOLARIZADA
    public function horario_semiescolarizada(Request $request)
    {
        $licenciatura = $request->licenciatura_id;
        $modalidad = $request->modalidad_id;
        $filtrar_generacion = $request->filtrar_generacion;
        $filtrar_cuatrimestre = $request->filtrar_cuatrimestre;

        // dd($licenciatura, $modalidad, $filtrar_generacion, $filtrar_cuatrimestre);

        $horario = Horario::where('licenciatura_id', $licenciatura)
        ->where('modalidad_id', $modalidad)
        ->where('generacion_id', $filtrar_generacion)
        ->where('cuatrimestre_id', $filtrar_cuatrimestre)
        ->get();

           $escuela = Escuela::all()->first();
           $generacion = Generacion::where('id', $filtrar_generacion)->first();
           $licenciatura_nombre = Licenciatura::where('id', $licenciatura)->first();

        $data = [
            'horario' => $horario,
            'escuela' => $escuela,
            'generacion' => $generacion,
            'licenciatura_nombre' => $licenciatura_nombre,
            'cuatrimestre' => $filtrar_cuatrimestre,
        ];
              $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.horarioSemiescolarizadaPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("HORARIO_DE_CLASES_GEN_".$generacion->generacion."_".$filtrar_cuatrimestre."°CUATRIMESTRE.pdf");
    }

    // HORARIO ESCOLARIZADA
    public function horario_escolarizada(Request $request)
    {
        $licenciatura = $request->licenciatura_id;
        $modalidad = $request->modalidad_id;
        $filtrar_generacion = $request->filtrar_generacion;
        $filtrar_cuatrimestre = $request->filtrar_cuatrimestre;

        // dd($licenciatura, $modalidad, $filtrar_generacion, $filtrar_cuatrimestre);

        $horario = Horario::where('licenciatura_id', $licenciatura)
        ->where('modalidad_id', $modalidad)
        ->where('generacion_id', $filtrar_generacion)
        ->where('cuatrimestre_id', $filtrar_cuatrimestre)
        ->get();

           $escuela = Escuela::all()->first();
           $generacion = Generacion::where('id', $filtrar_generacion)->first();
           $licenciatura_nombre = Licenciatura::where('id', $licenciatura)->first();
           $dias = Dia::where('dia', '!=', 'Sábado')->get();

           // Ejemplo en tu componente o controlador
           $materias = \App\Models\Horario::with([
                'asignacionMateria.materia',
                'asignacionMateria.profesor'
            ])
            ->where('licenciatura_id', $licenciatura)
            ->where('modalidad_id', $modalidad)
            ->where('generacion_id', $filtrar_generacion)
            ->get()
            ->map(function ($h) {
                return (object)[
                    'clave'    => $h->asignacionMateria->materia->clave ?? '',
                    'nombre'   => $h->asignacionMateria->materia->nombre ?? '',
                    'profesor' => $h->asignacionMateria->profesor->nombre ?? '',
                    'apellido_paterno' => $h->asignacionMateria->profesor->apellido_paterno ?? '',
                    'apellido_materno' => $h->asignacionMateria->profesor->apellido_materno ?? '',
                ];
            })
            ->unique(fn($item) => $item->clave . $item->nombre . $item->profesor) // ← Esto evita repetidos
            ->values();

        $data = [
            'horario' => $horario,
            'escuela' => $escuela,
            'generacion' => $generacion,
            'licenciatura_nombre' => $licenciatura_nombre,
            'cuatrimestre' => $filtrar_cuatrimestre,
            'dias' => $dias,
            'materias' => $materias,
        ];
              $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.horarioEscolarizadaPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("HORARIO_DE_CLASES_GEN_".$generacion->generacion."_".$filtrar_cuatrimestre."°CUATRIMESTRE.pdf");
    }
}
