<?php

namespace App\Http\Controllers;

use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\Calificacion;
use App\Models\Constancia;
use App\Models\Cuatrimestre;
use App\Models\Dashboard;
use App\Models\Dia;
use App\Models\Directivo;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Grupo;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Justificante;
use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Profesor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{


    public function expediente($id){

        $alumno = Inscripcion::with(['generacion', 'licenciatura', 'modalidad', 'user'])->find($id);
        $escuela = Escuela::all()->first();

        if (!$alumno) {
            abort(404);
        }

        $data = [
            'alumno' => $alumno,
            'escuela' => $escuela,
        ];

        $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.expedientePDF', $data)->setPaper('letter', 'portrait');
        return $pdf->stream("Expediente_{$alumno->nombre}.pdf");
    }

    public function matricula(Request $request)
    {
        $licenciatura = $request->licenciatura_id;
        $modalidad = $request->modalidad_id;
        $filtrar_generacion = $request->filtrar_generacion;
        $filtar_foraneo = $request->filtar_foraneo; //
        $query = Inscripcion::where('licenciatura_id', $licenciatura)
            ->where('modalidad_id', $modalidad)
            ->where('status', "true")
            ->where('generacion_id', $filtrar_generacion)
            ->orderBy('apellido_paterno', 'asc')
            ->orderBy('apellido_materno', 'asc')
            ->orderBy('nombre', 'asc');
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
             return $pdf->stream("Lista_de_grupo_Lic_en_{$licenciatura_nombre->nombre}_Gen:{$generacion->generacion}.pdf");
    }

    // MATRICULA GENERACION
    public function matricula_generacion(Request $request)
    {
        $licenciatura = $request->licenciatura_id;
        $generacion = $request->generacion_id;

        // dd($licenciatura, $modalidad, $filtrar_generacion, $filtar_foraneo);

        $matricula = Inscripcion::where('licenciatura_id', $licenciatura)
            ->where('status', "true")
            ->where('generacion_id', $generacion)
            ->orderBy('apellido_paterno', 'asc')
            ->orderBy('apellido_materno', 'asc')
            ->orderBy('nombre', 'asc')
            ->get();

        $generacion = Generacion::where('id', $generacion)->first();
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
              $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.matriculaGeneracionPDF', $data)->setPaper('letter', 'landscape');
             return $pdf->stream("Lista_de_grupo_Lic_en_{$licenciatura_nombre->nombre}_Gen:{$generacion->generacion}.pdf");
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

  public function documento_expedicion(Request $request)
{
    $generacion_id   = $request->generacion;
    $documento       = $request->documento;
    $licenciatura_id = $request->licenciatura;

    // Recibe alumno_ids[] y los sanea (int, únicos, sin vacíos)
    $alumnoIds = collect((array) $request->input('alumno_ids', []))
        ->map(fn($v) => (int) $v)
        ->filter()          // quita null/0/empty
        ->unique()
        ->values()
        ->all();

    // Validación básica
    if (!$generacion_id || !$documento || !$licenciatura_id) {
        abort(404, 'Faltan parámetros');
    }

    $materias      = Materia::where('licenciatura_id', $licenciatura_id)
                        ->where('calificable', '!=', 'false')
                        ->orderBy('clave', 'asc')
                        ->get();

    $generacion    = Generacion::findOrFail($generacion_id);
    $licenciatura  = Licenciatura::findOrFail($licenciatura_id);
    $escuela       = Escuela::query()->first();
    $rector        = Directivo::where('identificador', 'rector')->first();
    $directora     = Directivo::where('identificador', 'directora')->first();
    $jefe          = Directivo::where('identificador', 'jefe')->where('status', 'true')->first();

    $periodos      = Periodo::where('generacion_id', $generacion_id)->get();
    $modalidades   = Modalidad::all();

    // Query base de alumnos por generación y licenciatura
    $alumnosQuery = Inscripcion::with('calificaciones')
        ->where('generacion_id', $generacion_id)
        ->where('licenciatura_id', $licenciatura_id)
        ->where('status', 'true');

    // Si vienen IDs seleccionados, filtramos por ID de Inscripcion
    if (!empty($alumnoIds)) {
        $alumnosQuery->whereIn('id', $alumnoIds);
    }

    $alumnos = $alumnosQuery
        ->orderBy('apellido_paterno')
        ->orderBy('apellido_materno')
        ->orderBy('nombre')
        ->get();

    if ($documento === 'acta-resultados') {
        $data = compact('generacion','escuela','licenciatura','materias','rector','directora','jefe','alumnos');
        $pdf  = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.actaResultadosPDF', $data)
                    ->setPaper('letter', 'portrait');
        return $pdf->stream("ACTA_DE_RESULTADOS_GEN_{$generacion->generacion}.pdf");

    } elseif ($documento === 'registro-escolaridad') {
        $data = compact('generacion','escuela','materias','licenciatura','alumnos','periodos','modalidades','rector','jefe');
        $pdf  = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.registroEscolaridadPDF', $data)
                    ->setPaper('legal', 'landscape');
        return $pdf->stream("REGISTRO_DE_ESCOLARIDAD_GEN_{$generacion->generacion}.pdf");
    }

    abort(404, 'Documento no soportado');
}


   //EXPEDICIÓN DE SABANAS

   // OFICIOS

   public function documento_oficios(Request $request){
    $documento = $request->tipo_documento;
    $fecha = $request->fecha_expedicion;
    $generacion_id = $request->generacion;

    $escuela = Escuela::all()->first();

    // Validación básica
    if (!$generacion_id || !$documento || !$fecha) {
        abort(404, 'Faltan parámetros');
    }

    $generacion = Generacion::find($generacion_id);

    $escuela = Escuela::all()->first();
    $rector = Directivo::where('identificador', 'rector')->first();
    $directora = Directivo::where('identificador', 'directora')->first();

    $jefe =Directivo::where('identificador', 'jefe')->where('status', 'true')->first();
    $subjefe =Directivo::where('identificador', 'subjefe')->where('status', 'true')->first();

    $revisado = Directivo::where('identificador', 'revisado')->where('status', 'true')->first();



   $licenciaturas = AsignarGeneracion::where('generacion_id', $generacion_id)
    ->whereHas('licenciatura', function ($query) {
        $query->whereNotNull('RVOE');
    })
    ->with('licenciatura') // Para poder acceder al nombre, RVOE, etc.
    ->get()
    ->unique('licenciatura_id') // Evita duplicados aunque haya varias modalidades
    ->pluck('licenciatura'); // Trae solo los modelos de licenciatura


     if($documento == 'matriculas'){
           $data = [
            'generacion' => $generacion,
            'escuela' => $escuela,
            'rector' => $rector,
            'directora' => $directora,
            'fecha' => $fecha,
            'jefe' => $jefe,
            'subjefe' => $subjefe,
            'licenciaturas' => $licenciaturas
            ];

         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.matriculasOficioPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("OFICIO_MATRICULAS_".$generacion->generacion.".pdf");
    }
    else if($documento == 'kardex'){
        $data = [
            'generacion' => $generacion,
            'escuela' => $escuela,
            'rector' => $rector,
            'directora' => $directora,
            'fecha' => $fecha,
            'jefe' => $jefe,
            'subjefe' => $subjefe,
            'licenciaturas' => $licenciaturas
            ];

         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.kardexOficioPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("OFICIO_KARDEX_".$generacion->generacion.".pdf");
    }else if($documento == 'registro-boletos'){
         $data = [
            'generacion' => $generacion,
            'escuela' => $escuela,
            'rector' => $rector,
            'directora' => $directora,
            'fecha' => $fecha,
            'jefe' => $jefe,
            'subjefe' => $subjefe,
            'licenciaturas' => $licenciaturas
            ];

         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.registrosBoletasOficioPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("OFICIO_REGISTROS_ESCOLARIDAD_ACTA_RESULTADOS_".$generacion->generacion.".pdf");
    }else if($documento == 'folios'){
        $data = [
            'generacion' => $generacion,
            'escuela' => $escuela,
            'rector' => $rector,
            'directora' => $directora,
            'fecha' => $fecha,
            'jefe' => $jefe,
            'subjefe' => $subjefe,
            'licenciaturas' => $licenciaturas
            ];

         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.foliosOficioPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("OFICIO_FOLIOS_".$generacion->generacion.".pdf");

    }else if($documento == 'certificados'){
        $data = [
            'generacion' => $generacion,
            'escuela' => $escuela,
            'rector' => $rector,
            'directora' => $directora,
            'fecha' => $fecha,
            'jefe' => $jefe,
            'subjefe' => $subjefe,
            'revisado' => $revisado,
            'licenciaturas' => $licenciaturas
            ];

         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.certificadosOficioPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("OFICIO_CERTIFICADOS_".$generacion->generacion.".pdf");
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

    $jefe =Directivo::where('identificador', 'jefe')->where('status', 'true')->first();

    $revisado = Directivo::where('identificador', 'revisado')->where('status', 'true')->first();

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
             return $pdf->stream("KARDEX_".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$matricula.".pdf");
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
            'jefe' =>$jefe,
            'revisado' => $revisado,
            'directora' => $directora
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.certificadoPDF', $data)->setPaper('legal', 'portrait');
             return $pdf->stream("CERTIFICADO_DE_ESTUDIOS_".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$matricula.".pdf");
    }
    else{
        abort(404, 'Tipo de documento no válido');
    }

   }


    //    CREDENCIAL DEL ALUMNO

  public function credencial_alumno(Request $request)
{
    // Obtén los IDs como un array desde el input "alumnos_ids[]"
    $alumnosIds = $request->input('alumnos_ids', []);


    // Verifica si llegaron datos
    if (empty($alumnosIds)) {
        return redirect()->back()->with('error', 'No se seleccionaron alumnos.');
    }

    // Obtiene los datos de los alumnos
    $alumnos = Inscripcion::whereIn('id', $alumnosIds)->get();

    // CICLO ESCOLAR
   $ciclo_escolar = Dashboard::orderBy('id', 'desc')->first();


    // Puedes ver los datos si estás probando
    // dd($alumnos);

    // Genera el PDF
    $data = [
        'alumnos' => $alumnos,
        'ciclo_escolar' => $ciclo_escolar
    ];

    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.credencialPDF', $data)
              ->setPaper('letter', 'portrait')

              ;

    return $pdf->stream("CREDENCIAL(S).pdf");
}

// CONSTANCIAS DEL ALUMNO

    public function constancia(Request $request){
         $id = $request->constancia_id;

         $constancia = Constancia::findOrFail($id);
         $alumno = Inscripcion::where('id', $constancia->alumno_id)->first();
         $rector = Directivo::where('cargo', 'Rector')->first();
         $escuela = Escuela::all()->first();

         $periodo = Periodo::where('generacion_id', $alumno->generacion_id)
         ->where('cuatrimestre_id', $alumno->cuatrimestre_id)
         ->first();



       // CICLO ESCOLAR
         $ciclo_escolar = Dashboard::orderBy('id', 'desc')->first();

        $data = [
            'constancia' => $constancia,
            'rector' => $rector,
            'escuela' => $escuela,
            'ciclo_escolar' => $ciclo_escolar,
            'periodo' => $periodo,
        ];
         $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.constanciasPDF', $data)->setPaper('letter', 'portrait');
             return $pdf->stream("CONSTANCIA_".$alumno["nombre"]."_".$alumno["apellido_paterno"]."_".$alumno["apellido_materno"]."_".$alumno->matricula.".pdf");

    }


    // ETIQUETAS

     public function etiquetas(Request $request)
{
    // Obtén los IDs como un array desde el input "alumnos_ids[]"
    $alumnosIds = $request->input('alumnos_ids', []);


    // Verifica si llegaron datos
    if (empty($alumnosIds)) {
        return redirect()->back()->with('error', 'No se seleccionaron alumnos.');
    }

    // Obtiene los datos de los alumnos
    $alumnos = Inscripcion::whereIn('id', $alumnosIds)->get();

    // CICLO ESCOLAR
   $ciclo_escolar = Dashboard::orderBy('id', 'desc')->first();


    // Puedes ver los datos si estás probando
    // dd($alumnos);

    // Genera el PDF
    $data = [
        'alumnos' => $alumnos,
        'ciclo_escolar' => $ciclo_escolar
    ];

    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.etiquetaPDF', $data)
              ->setPaper('letter', 'portrait') ;
    return $pdf->stream("ETIQUETA(S).pdf");
}


// HORARIO GENERAL SEMIESCOLARIZADA

public function horario_general_semiescolarizada()
{
    $horarios = Horario::with([
        'asignacionMateria.materia.licenciatura',
        'asignacionMateria.profesor',
        'licenciatura',
        'dia',
    ])
    ->where('modalidad_id', 2)
    ->get();

    // Columnas únicas (Cuat. + Lic.)
    $columnasUnicas = $horarios
        ->unique(fn ($item) => $item->cuatrimestre_id . '-' . $item->licenciatura_id)
        ->map(fn ($item) => [
            'cuatrimestre_id' => $item->cuatrimestre_id,
            'licenciatura_id' => $item->licenciatura_id,
            'etiqueta' => "Cuat. {$item->cuatrimestre_id} - Lic. " . (
                $item->licenciatura->nombre_corto ?? $item->licenciatura->nombre
            ),
        ])
        ->sortBy(fn ($col) => sprintf('%03d-%03d', $col['licenciatura_id'], $col['cuatrimestre_id']))
        ->values();

    // Horas únicas (ASC por hora inicial)
    $horasUnicas = $horarios->pluck('hora')
        ->unique()
        ->sortBy(function ($hora) {
            $inicio = trim(explode('-', (string)$hora)[0] ?? '');
            return strtotime(strtolower($inicio)) ?: 0;
        })
        ->values();

    $resumenDocentes = $horarios
        ->groupBy(function ($h) {
            return optional(optional($h->asignacionMateria)->profesor)->id ?: 'sin';
        })
        ->map(function ($items) {
            $prof = optional(optional($items->first()->asignacionMateria)->profesor);
            $materias = $items->map(function ($i) {
                    $m = optional(optional($i->asignacionMateria)->materia);
                    if (!$m) return null;
                    return [
                        'id'           => $m->id,
                        'nombre'       => $m->nombre,
                        'clave'        => $m->clave,
                        'licenciatura' => optional($m->licenciatura)->nombre ?? 'N/A',
                    ];
                })
                ->filter()
                ->unique('id')
                ->values();

            $nombre = trim(
                ($prof->nombre ?? 'Sin asignar') . ' ' .
                ($prof->apellido_paterno ?? '') . ' ' .
                ($prof->apellido_materno ?? '')
            );

            return [
                'nombre'      => preg_replace('/\s+/', ' ', $nombre),
                'color'       => $prof->color ?? '#e5e7eb',
                'materias'    => $materias,
                'total_horas' => $items->count(),
            ];
        })
        ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();

    $data = [
        'horarios'          => $horarios,
        'columnasUnicas'    => $columnasUnicas,
        'horasUnicas'       => $horasUnicas,
        'resumenDocentes'   => $resumenDocentes,
        'totalGeneralHoras' => $resumenDocentes->sum('total_horas'),
    ];

    // Doble carta horizontal (17x11) — orientación portrait para mantener 1224x792 sin rotar
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'livewire.admin.licenciaturas.submodulo.pdf.horarioGeneralSemiescolarizada',
        $data
    )->setPaper([0, 0, 1224, 792], 'portrait');

    return $pdf->stream('horario_general-semiescolarizada.pdf');
}




 // LISTA DE ASISTENCIAS
    public function lista_asistencia_escolarizada(Request $request){
        $materia_id = $request->asignacion_materia;
        $licenciatura_id = $request->licenciatura_id;
        $cuatrimestre_id = $request->cuatrimestre_id;
        $generacion_id = $request->generacion_id;
        $modalidad_id = $request->modalidad_id;
        $periodo =  $request->periodo;


        if (!$periodo) {
            abort(404, 'Periodo no encontrado');
        }
        // Obtén el periodo y genera los días del mes.
        $periodo = explode('-', $periodo); // Ejemplo: ['5', '8'] para Mayo-Agosto

        $meses = [
            '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio',
            '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];

        $generacion = Generacion::find($generacion_id);
        $licenciatura = Licenciatura::find($licenciatura_id);

       // Genera días hábiles (lunes a viernes) dentro del periodo
        $fechas = [];
        $anioActual = (int) date('Y');

        for ($mes = (int)$periodo[0]; $mes <= (int)$periodo[1]; $mes++) {
            $fechas[$mes] = [];
            $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anioActual);

            for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                $fechaStr = sprintf('%04d-%02d-%02d', $anioActual, $mes, $dia);
                $n = (int) date('N', strtotime($fechaStr)); // 1=Lun ... 7=Dom

                if ($n >= 1 && $n <= 5) { // Lunes a viernes
                    $fechas[$mes][] = $dia;
                }
            }
        }



          $materia = AsignacionMateria::with(['materia', 'profesor'])
            ->where('id', $materia_id)
            ->first();

            $alumnos = Inscripcion::where('licenciatura_id', $licenciatura_id)
                ->where('cuatrimestre_id', $cuatrimestre_id)
                ->where('generacion_id', $generacion_id)
                ->where('modalidad_id', $modalidad_id)
                ->where('status', 'true')
                ->where('foraneo', 'false') // Filtrar por alumnos no foráneos
                ->orderBy('apellido_paterno', 'asc')
                ->orderBy('apellido_materno', 'asc')
                ->orderBy('nombre', 'asc')
                ->get();


        if (!$materia) {
            abort(404, 'Materia no encontrada');
        }


         $data = [
            'escuela' => Escuela::all()->first(),
            'materia' => $materia,
            'alumnos' => $alumnos,
            'periodo' => $periodo,
             'fechas' => $fechas, // Añadimos las fechas generadas
             'meses' => $meses,
             'generacion' => $generacion,

         ];

         $exportacion = $licenciatura->nombre . '_' . $materia->materia->slug . '_' . $generacion->generacion . '_' . $cuatrimestre_id.'°_Cuatrimestre';

    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.lista-asistencia-escolarizadaPDF', $data)
              ->setPaper('letter', 'landscape');

        return $pdf->stream("LISTA_ASISTENCIA_ESCOLARIZADO".$exportacion.".pdf");
    }

    //LISTA DE ASISTENCIAS SEMIESCOLARIZADA
    public function lista_asistencia_semiescolarizada(Request $request){
        $materia_id = $request->asignacion_materia;
        $licenciatura_id = $request->licenciatura_id;
        $cuatrimestre_id = $request->cuatrimestre_id;
        $generacion_id = $request->generacion_id;
        $modalidad_id = $request->modalidad_id;
        $periodo =  $request->periodo;


        if (!$periodo) {
            abort(404, 'Periodo no encontrado');
        }
        // Obtén el periodo y genera los días del mes.
        $periodo = explode('-', $periodo); // Ejemplo: ['5', '8'] para Mayo-Agosto

        $meses = [
            '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio',
            '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];

        $generacion = Generacion::find($generacion_id);
        $licenciatura = Licenciatura::find($licenciatura_id);

        // Genera las fechas de días dentro del periodo.
        // Genera los sábados del periodo
            $fechas = [];
            $añoActual = date('Y');

            for ($mes = $periodo[0]; $mes <= $periodo[1]; $mes++) {
                $fechas[$mes] = [];
                $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $añoActual);
                for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                    $fecha = date('Y-m-d', strtotime("$añoActual-$mes-$dia"));
                    if (date('N', strtotime($fecha)) == 6) { // 6 = sábado
                        $fechas[$mes][] = $dia;
                    }
                }
            }


          $materia = AsignacionMateria::with(['materia', 'profesor'])
            ->where('id', $materia_id)
            ->first();

            $alumnos = Inscripcion::where('licenciatura_id', $licenciatura_id)
                ->where('cuatrimestre_id', $cuatrimestre_id)
                ->where('generacion_id', $generacion_id)
                ->where('modalidad_id', $modalidad_id)
                ->where('status', 'true')
                ->where('foraneo', 'false') // Filtrar por alumnos no foráneos
                ->orderBy('apellido_paterno', 'asc')
                ->orderBy('apellido_materno', 'asc')
                ->orderBy('nombre', 'asc')
                ->get();


        if (!$materia) {
            abort(404, 'Materia no encontrada');
        }


         $data = [
            'escuela' => Escuela::all()->first(),
            'materia' => $materia,
            'alumnos' => $alumnos,
            'periodo' => $periodo,
             'fechas' => $fechas, // Añadimos las fechas generadas
             'meses' => $meses,
             'generacion' => $generacion,

         ];

         $exportacion = $licenciatura->nombre . '_' . $materia->materia->slug . '_' . $generacion->generacion . '_' . $cuatrimestre_id.'°_Cuatrimestre';

    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.lista-asistencia-semiescolarizadaPDF', $data)
              ->setPaper('letter', 'landscape'); ;

        return $pdf->stream("LISTA_ASISTENCIA_".$exportacion.".pdf");
    }


    // LISTA DE EVALUACION
    public function lista_evaluacion(Request $request){
        $materia_id = $request->asignacion_materia;
        $licenciatura_id = $request->licenciatura_id;
        $cuatrimestre_id = $request->cuatrimestre_id;
        $generacion_id = $request->generacion_id;
        $modalidad_id = $request->modalidad_id;
        $periodo = $request->periodo;




         if(!$periodo){
              abort(404, 'Periodo no encontrado');
        }


        $generacion = Generacion::find($generacion_id);

        $ciclo_escolar = Dashboard::orderBy('id', 'desc')->first();

        $grupo = Grupo::where('licenciatura_id', $licenciatura_id)
            ->where('cuatrimestre_id', $cuatrimestre_id)
            ->first();

        $materia = AsignacionMateria::with(['materia', 'profesor'])
            ->where('id', $materia_id)
            ->first();



            $alumnos = Inscripcion::where('licenciatura_id', $licenciatura_id)
                ->where('cuatrimestre_id', $cuatrimestre_id)
                ->where('generacion_id', $generacion_id)
                ->where('modalidad_id', $modalidad_id)
                ->where('status', 'true')
                ->where('foraneo', 'false') // Filtrar por alumnos no foráneos
                ->orderBy('apellido_paterno', 'asc')
                ->orderBy('apellido_materno', 'asc')
                ->orderBy('nombre', 'asc')
                ->get();

        if (!$materia) {
            abort(404, 'Materia no encontrada');
        }





        // Obtén el periodo y genera los días del mes.
        $periodo = explode('-', $request->periodo); // Ejemplo: ['5', '8'] para Mayo-Agosto
        $meses = [
            '1' => 'ENE', '2' => 'FEB', '3' => 'MAR', '4' => 'ABR', '5' => 'MAY', '6' => 'JUN',
            '7' => 'JUL', '8' => 'AGO', '9' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DIC'
        ];


        $periodos = Periodo::where('generacion_id', $generacion_id)
         ->where('cuatrimestre_id',$cuatrimestre_id)
         ->first();


         $modalidad = Modalidad::findOrFail ($modalidad_id);

        $data = [
            'escuela' => Escuela::all()->first(),
            'materia' => $materia,
            'alumnos' => $alumnos,
            'periodo' => $periodo,
            'generacion' => $generacion,
            'ciclo_escolar' => $ciclo_escolar,
            'meses' => $meses,
            'periodos' => $periodos,
            'grupo' => $grupo,
            'modalidad' => $modalidad
         ];

    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.lista-evaluacionPDF', $data)
              ->setPaper('letter', 'landscape'); ;

        return $pdf->stream("Lista_evaluacion.pdf");

    }

    // CREDENCIAL DEL PROFESOR

      public function credencial_profesor(Request $request)
{
    // Obtén los IDs como un array desde el input "profesores_ids[]"
    $profesoresIds = $request->input('profesores_ids', []);


    // Verifica si llegaron datos
    if (empty($profesoresIds)) {
        return redirect()->back()->with('error', 'No se seleccionaron profesores.');
    }

    // Obtiene los datos de los profesores
    $profesores = Profesor::whereIn('id', $profesoresIds)->with('user')->get();

    // CICLO ESCOLAR
   $ciclo_escolar = Dashboard::orderBy('id', 'desc')->first();



    // Puedes ver los datos si estás probando
    // dd($alumnos);

    // Genera el PDF
    $data = [
        'profesores' => $profesores,
        'ciclo_escolar' => $ciclo_escolar
    ];

    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.credencialProfesorPDF', $data)
              ->setPaper('letter', 'portrait')

              ;

    return $pdf->stream("CREDENCIAL(S).pdf");
}

// CREDENCIAL DEL PROFESOR ESTUDIANTE
public function credencial_profesor_estudiante(Request $request)
{
    // Obtén los IDs como un array desde el input "profesores_ids[]"
    $profesoresIds = $request->input('profesores_ids', []);
    // Verifica si llegaron datos
    if (empty($profesoresIds)) {
        return redirect()->back()->with('error', 'No se seleccionaron profesores.');
    }
    // Obtiene los datos de los profesores
    $profesores = Profesor::whereIn('id', $profesoresIds)->
    with('user')->get();
    // CICLO ESCOLAR
    $ciclo_escolar = Dashboard::orderBy('id', 'desc')->first();
    // Puedes ver los datos si estás probando
    // dd($alumnos);

    $licenciatura = Licenciatura::where('id', $request->input('licenciatura_id'))->first();
    // Genera el PDF
    $data = [
        'profesores' => $profesores,
        'ciclo_escolar' => $ciclo_escolar,
        'licenciatura' => $licenciatura
    ];
    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.credencialProfesorEstudiantePDF', $data)
              ->setPaper('letter', 'portrait') ;
    return $pdf->stream("CREDENCIAL(S).pdf");
}

    // CALIFICACION DEL ALUMNO
    public function calificacion_alumno(Request $request)
    {
                $alumno = $request->alumno_id;
                $modalidad = $request->modalidad_id;
                $generacion = $request->generacion_id;
                $cuatrimestre = $request->cuatrimestre_id;

            $periodo = Periodo::where('generacion_id', $generacion)
                ->where('cuatrimestre_id',$cuatrimestre)
                ->first();


                $calificaciones = Calificacion::with(['asignacionMateria.materia', 'asignacionMateria.profesor'])
                    ->where('alumno_id', $alumno)
                    ->whereHas('asignacionMateria', function ($query) use ($modalidad, $generacion, $cuatrimestre) {
                    $query->where('modalidad_id', $modalidad)
                        ->where('generacion_id', $generacion)
                        ->where('cuatrimestre_id', $cuatrimestre);
                    })
                    ->get()
                    ->sortBy(function ($item) {
                    return $item->asignacionMateria->materia->clave ?? '';
                    })
                    ->values();

                $escuela = Escuela::all()->first();
                $inscripcion = Inscripcion::where('id', $alumno)->first();
                $licenciatura = Licenciatura::where('id', $inscripcion->licenciatura_id)->first();
                $profesor = Profesor::where('id', $inscripcion->profesor_id)->first();
                $generacion = Generacion::where('id', $generacion)->first();
                $cuatrimestre = Cuatrimestre::where('id', $cuatrimestre)->first();

                $ciclo_escolar = Dashboard::orderBy('id', 'desc')->first();

            $data = [
                'cuatrimestre' => $cuatrimestre,
                'calificaciones' => $calificaciones,
                'ciclo_escolar' => $ciclo_escolar,
                'escuela' => $escuela,
                'licenciatura' => $licenciatura,
                'generacion' => $generacion,
                'periodo' => $periodo,
                'inscripcion' => $inscripcion
            ];
            $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.boletaCalificacionPDF', $data)
                    ->setPaper('letter', 'portrait') ;
            return $pdf->stream("BOLETA_DEL_".$cuatrimestre->cuatrimestre."°_CUATRIMESTRE_ALUMNO:".$inscripcion->nombre." ".$inscripcion->apellido_paterno." ".$inscripcion->apellido_materno.".pdf");

    }

    // CALIFICACIONES GENERALES
    public function calificaciones_generales(Request $request)
    {           $licenciatura = $request->licenciatura_id;
                $modalidad = $request->modalidad_id;
                $generacion = $request->generacion_id;
                $cuatrimestre = $request->cuatrimestre_id;


            $periodo = Periodo::where('generacion_id', $generacion)
                ->where('cuatrimestre_id',$cuatrimestre)
                ->first();

                $escuela = Escuela::all()->first();
                $licenciatura = Licenciatura::where('id', $licenciatura)->first();
                $generacion = Generacion::where('id', $generacion)->first();
                $cuatrimestres = Cuatrimestre::where('id', $cuatrimestre)->first();
                $ciclo_escolar = Dashboard::orderBy('id', 'desc')->first();


                    // Obtenemos todas las calificaciones de los alumnos de esa generación/modalidad/cuatrimestre
            $calificaciones = Calificacion::with(['alumno', 'asignacionMateria'])
                ->where('licenciatura_id', $licenciatura->id)
                ->where('modalidad_id', $modalidad)
                ->where('generacion_id', $generacion->id)
                ->where('cuatrimestre_id', $cuatrimestres->id)
                ->get();

                $totalMaterias = $calificaciones->pluck('asignacionMateria.materia.clave')
                ->unique()
                ->count();

                // dd($calificaciones);

            // Agrupar por alumno y materias
                 $alumnos = $calificaciones->groupBy('alumno_id')->map(function ($items, $alumnoId) {
                $alumno = $items->first()->alumno;



                $nombre = $alumno->nombre ?? '';
                $paterno = $alumno->apellido_paterno ?? '';
                $materno = $alumno->apellido_materno ?? '';
                $matricula = $alumno->matricula;

                $materias = $items->sortBy(function ($item) {
                return $item->asignacionMateria->materia->clave ?? '';
            })->mapWithKeys(function ($item) {
                $materia = $item->asignacionMateria->materia;
                return [$materia->clave => [
                    'nombre' => $materia->nombre,
                    'calificacion' => (int) $item->calificacion
                ]];
            });

            $promedio = collect($materias)->pluck('calificacion')->avg();

                return [
                    'matricula' => $matricula,
                    'nombre' => $nombre,
                    'apellido_paterno' => $paterno,
                    'apellido_materno' => $materno,
                    'materias' => $materias,
                    'promedio' => $promedio,
                ];
            })->sortBy('apellido_paterno')->values();



            $data = [
                'cuatrimestres' => $cuatrimestre,
                'ciclo_escolar' => $ciclo_escolar,
                'escuela' => $escuela,
                'licenciatura' => $licenciatura,
                'generacion' => $generacion,
                'periodo' => $periodo,
                'alumnos' => $alumnos,
                 'totalMaterias' => $totalMaterias,
            ];
            $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.calificacionesGeneralesPDF', $data)
                    ->setPaper('letter', 'landscape') ;
            return $pdf->stream("CALIFICACIONES_GENERALES_" . strtoupper($licenciatura->nombre) . "_" . strtoupper($generacion->generacion) . "_" . strtoupper($cuatrimestres->cuatrimestre) . "°_CUATRIMESTRE.pdf");

    }


    // JUSTIFICANTE

    public function justificante($justificante){

        $justificantes = Justificante::findOrFail($justificante);


       $data = [
        'justificantes' => $justificantes,
    ];
    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.justificantePDF', $data)
              ->setPaper('letter', 'portrait') ;
    return $pdf->stream("JUSTIFICANTE_".$justificantes->alumno->nombre."_".$justificantes->alumno->apellido_paterno."_".$justificantes->alumno->apellido_materno.".pdf");

    }

// HORARIO DOCENTE SEMIESCOLARIZADA
public function horario_docente_semiescolarizada(Request $request)
{
    $profesor_id  = (int) $request->input('profesor_id');
    $modalidad_id = (int) $request->input('modalidad_id', 2); // 2 = Semiescolarizada

    $profesor  = Profesor::findOrFail($profesor_id);
    $modalidad = Modalidad::findOrFail($modalidad_id);
    $escuela   = Escuela::first();

    $registros = Horario::with([
        'asignacionMateria.materia.licenciatura',
        'asignacionMateria.profesor',
        'licenciatura',
        'dia',
    ])
    ->where('modalidad_id', $modalidad_id)
    ->whereHas('asignacionMateria', fn($q) => $q->where('profesor_id', $profesor_id))
    // 8:00am-9:00am -> toma la hora de inicio y ordena ASC (más temprano primero)
    ->orderByRaw("STR_TO_DATE(LOWER(TRIM(SUBSTRING_INDEX(hora,'-',1))), '%h:%i%p') ASC")
    ->get();


    $data = compact('profesor', 'modalidad', 'escuela', 'registros');

    $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.horarioDocenteSemiescolarizadaPDF', $data)
        ->setPaper('letter', 'landscape');

    $nombreArchivo = sprintf(
        'HORARIO_DOCENTE_SEMIESCOLARIZADA_%s_%s_%s.pdf',
        $profesor->nombre,
        $profesor->apellido_paterno,
        $profesor->apellido_materno
    );

    return $pdf->stream($nombreArchivo);
}

// HORARIO DOCENTE ESCOLARIZADA (PDF)
public function horario_docente_escolarizada(Request $request)
{
    $profesor_id  = (int) $request->input('profesor_id');
    $modalidad_id = (int) $request->input('modalidad_id', 1); // 1 = Escolarizada

    $profesor  = Profesor::findOrFail($profesor_id);
    $modalidad = Modalidad::findOrFail($modalidad_id);
    $escuela   = Escuela::first();

    $registros = Horario::with([
            'asignacionMateria.materia.licenciatura',
            'asignacionMateria.profesor',
            'licenciatura',
            'dia',
        ])
        ->where('modalidad_id', $modalidad_id)
        ->whereHas('asignacionMateria', fn($q) => $q->where('profesor_id', $profesor_id))
        // 8:00am-9:00am -> ordena por hora de inicio ASC
        ->orderByRaw("STR_TO_DATE(LOWER(TRIM(SUBSTRING_INDEX(hora,'-',1))), '%h:%i%p') ASC")
        ->get();

    $data = compact('profesor', 'modalidad', 'escuela', 'registros');

    $pdf = Pdf::loadView(
            'livewire.admin.licenciaturas.submodulo.pdf.horarioDocenteEscolarizadaPDF',
            $data
        )->setPaper('letter', 'landscape');

    $nombreArchivo = sprintf(
        'HORARIO_DOCENTE_ESCOLARIZADA_%s_%s_%s.pdf',
        $profesor->nombre,
        $profesor->apellido_paterno,
        $profesor->apellido_materno
    );

    return $pdf->stream($nombreArchivo);
}




    //ALUMNOS DOCUMENTACION

public function alumnos_documentacion(int $licenciatura)
{
        $licenciaturaNombre = 'Todas las licenciaturas';

        $query = Inscripcion::query()
            ->with(['generacion:id,generacion', 'licenciatura:id,nombre']);

        if ($licenciatura !== 0) {
            $lic = Licenciatura::findOrFail($licenciatura);
            $licenciaturaNombre = $lic->nombre;
            $query->where('licenciatura_id', $lic->id);
        }

        $alumnos = $query
            ->orderBy('generacion_id')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        // Agrupar por etiqueta de generación (ej. "2022-2025"); si no tiene, "Sin generación"
        $grupos = $alumnos->groupBy(function ($a) {
            return optional($a->generacion)->generacion ?: 'Sin generación';
        })->sortKeys();

        $data = [
            'licenciaturaNombre' => $licenciaturaNombre,
            'grupos'             => $grupos,
        ];

        $filename = 'ALUMNOS_DOCUMENTACION_' . strtoupper(str_replace(' ', '_', $licenciaturaNombre)) . '.pdf';

        $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.alumnosDocumentacionPDF', $data)
            ->setPaper('letter', 'landscape');

        return $pdf->stream($filename);
}
}
