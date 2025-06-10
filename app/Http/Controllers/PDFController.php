<?php

namespace App\Http\Controllers;

use App\Models\Cuatrimestre;
use App\Models\Dia;
use App\Models\Directivo;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\Periodo;
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


    // Expedicion de registros de escolaridad y actas de resultados

   public function documento_expedicion(Request $request){
       $generacion_id = $request->generacion;
       $documento = $request->documento;
       $licenciatura_id = $request->licenciatura;

    $materias = Materia::where('licenciatura_id', $licenciatura_id)
         ->where('calificable', '!=', 'false')
         ->orderBy('clave', 'asc')
         ->get();

         // Validación básica
    if (!$generacion_id || !$documento || !$licenciatura_id) {
        abort(404, 'Faltan parámetros');
    }

    $generacion = Generacion::find($generacion_id);
    $licenciatura = Licenciatura::find($licenciatura_id);
    $escuela = Escuela::all()->first();
    $rector = Directivo::where('cargo', 'Rector')->first();
    $directora = Directivo::where('cargo', 'Directora General')->first();

    // PERIODOS ESDOLARES

    $periodos = Periodo::where('generacion_id', $generacion_id)
        ->get();

    // Alumnos por generación y licenciatura
    $alumnos = Inscripcion::with('calificaciones')
        ->where('generacion_id', $generacion_id)
        ->where('licenciatura_id', $licenciatura_id)
        ->orderBy('apellido_paterno', 'asc')
        ->orderBy('apellido_materno', 'asc')
        ->orderBy('nombre', 'asc')
        // ->limit(1)
        ->get();

    if($documento == 'acta-resultados'){
        $data = [
            'generacion' => $generacion,
            'escuela' => $escuela,
            'licenciatura' => $licenciatura,
            'materias' => $materias,
            'rector' => $rector,
            'directora' => $directora,
            'alumnos' => $alumnos,
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.actaResultadosPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("ACTA_DE_RESULTADOS_GEN_".$generacion->generacion.".pdf");

    }elseif($documento == 'registro-escolaridad'){
       $data = [
            'generacion' => $generacion,
            'escuela' => $escuela,
            'materias' => $materias,
            'licenciatura' => $licenciatura,
            'alumnos' => $alumnos,
            'periodos' => $periodos,
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.registroEscolaridadPDF', $data)->setPaper('legal', 'landscape');
             return $pdf->stream("REGISTRO_DE_ESCOLARIDAD_GEN_".$generacion->generacion.".pdf");
    }

   }

   // DOCUMENTO PERSONAL

  public function documento_personal(Request $request){
       $matricula = $request->alumno_id;
       $documento = $request->tipo_documento;
       $fecha = $request->fecha_expedicion;

       $escuela = Escuela::all()->first();
       $alumno = Inscripcion::where('matricula', $matricula)->first();
       if (!$alumno) {
           abort(404, 'Alumno no encontrado');
       }


    $licenciatura = Licenciatura::find($alumno->licenciatura_id);
    $cuatrimestres = Cuatrimestre::all();
    $rector = Directivo::where('cargo', 'Rector')->first();
    $directora = Directivo::where('cargo', 'Directora General')->first();

     $periodos = Periodo::where('generacion_id', $alumno->generacion_id)
        ->get();


    if($documento == 'kardex'){
        $data = [
            'alumno' => $alumno,
            'escuela'=> $escuela,
            'licenciatura'=> $licenciatura,
            'cuatrimestres' => $cuatrimestres,
            'rector' => $rector,
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.kardexPDF', $data)->setPaper('legal', 'portrait');
             return $pdf->stream("KARDEX".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$matricula.".pdf");
    }
    else if($documento == 'historial-academico'){
        $data = [
             'alumno' => $alumno,
            'escuela'=> $escuela,
            'licenciatura'=> $licenciatura,
            'cuatrimestres' => $cuatrimestres,
            'rector' => $rector,
            'directora' => $directora,
            'fecha' => $fecha,
            'periodos' => $periodos
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.historialAcademicoPDF', $data)->setPaper('legal', 'portrait');
             return $pdf->stream("HISTORIAL_ACADEMICO_".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$matricula.".pdf");
    }
    else if($documento == 'diploma'){
        $data = [
            'alumno' => $alumno,
            'escuela'=> $escuela,
            'licenciatura'=> $licenciatura,
            'fecha' => $fecha,
            'rector' => $rector,
            'directora' => $directora
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.diplomaPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("DIPLOMA_".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$matricula.".pdf");
    }
    else if($documento == 'carta-de-pasante'){
        $data = [
            'alumno' => $alumno,
            'escuela'=> $escuela,
            'licenciatura'=> $licenciatura,
            'fecha' => $fecha,
            'rector' => $rector,
            'directora' => $directora
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.cartaPasantePDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("CARTA_DE_PASANTE_".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$matricula.".pdf");
    }

    else if($documento == 'constancia-de-termino'){
        $data = [
            'alumno' => $alumno,
            'escuela'=> $escuela,
            'licenciatura'=> $licenciatura,
            'fecha' => $fecha,
            'rector' => $rector,
            'directora' => $directora
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.constanciaTerminoPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("CONSTANCIA_DE_TERMINO_".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$matricula.".pdf");
    }

    else if($documento == 'certificado-de-estudios'){
        $data = [
            'alumno' => $alumno,
            'escuela'=> $escuela,
            'licenciatura'=> $licenciatura,
            'fecha' => $fecha,
            'cuatrimestres' => $cuatrimestres,
            'rector' => $rector,
            'directora' => $directora
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.certificadoPDF', $data)->setPaper('legal', 'portrait');
             return $pdf->stream("CERTIFICADO_DE_ESTUDIOS_".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$matricula.".pdf");
    }
    else{
        abort(404, 'Tipo de documento no válido');
    }

   }

}
