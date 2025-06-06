<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Certificado de Estudios | {{ $alumno->matricula }}</title>
</head>
<style>

      @page { margin:0px 45px 0px 45px; }

    .page-break {
     page-break-after: always;
    }

     @font-face {
        font-family: 'calibri';
        font-style: normal;
        src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');

    }

    @font-face {
    font-family: 'calibri';
    font-style: bold;
    font-weight: 700;
    src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
    }



    body {
        font-family: 'calibri';
        margin: auto;
    }

     .encabezado {
        text-align: center;
        margin-top: 30px;
        font-size: 16px;
    }
    .img_encabezado {
        width: 80%;
        margin-left: -100px;
    }

    .titulo {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        margin-top: 10px;
        margin-bottom: 5px;
    }

    table{
         border-collapse: collapse;
        padding: 0;
        margin: 0
    }

    table.datos {
        margin-top: -40px;
        width: 100%;
        border-collapse: collapse;
        text-transform: uppercase;
    }

    table.tblPrincipal{
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
      border:  1px solid #000;
      /* height: 100%; */
      padding: 0;
      margin-top: 10px;
    }

    table.fisica td{

      padding: .5px;
      margin-top: 20px;
    }

    table tr.cuatrimestres td {
        font-size: 12px;
        line-height: 10px;
    }



    </style>
<body>

    <div class="encabezado">
          <img class="img_encabezado" src="{{ public_path('storage/imagen_kardex.png') }}" alt="Encabezado">
    </div>

          <table class="datos">

                    <tr>
                    <td style="text-align:center;" colspan="12"><h4 style="font-size:19.5px; margin-bottom:10px">CERTIFICADO DE ESTUDIOS</h4></td>
                    </tr>


                    <tr>

                    <td style="width:218px; margin-top:40px"></td>
                    <td>
                    <p  style="margin: 50px 0 0 0; font-size:15px; margin:0">CERTIFICA QUE: <b style="font-size:15px;"><u>{{$alumno->nombre}} {{$alumno->apellido_paterno}} {{$alumno->apellido_materno}}</u></b> </p><br>
                    <p style="font-size:13.5px; margin-top:-20px">CON CLAVE ÚNICA DE REGISTRO DE POBLACIÓN (CURP): <b><u>{{$alumno->CURP}}</u></b> <br>
                    CURSÓ Y ACREDITÓ LAS ASIGNATURAS CORRESPONDIENTES A LA <b>LICENCIATURA EN {{$licenciatura->nombre}} </b>  <br>
                    CON RECONOCIMIENTO DE VALIDEZ OFICIAL DE ESTUDIOS DE LA SECRETARÍA DE EDUCACIÓN GUERRERO.
                    SEGÚN ACUERDO NÚMERO: <b><u>{{$licenciatura->RVOE}}</u></b>, DE FECHA <b><u>29 DE ENERO DEL 2021</u></b>, Y CON CLAVE
                    CENTRO DE TRABAJO <b>{{$escuela->CCT}}. </p>

                    </td>
                    </tr>

           </table>
            <p  style="text-align:right; font-size:13.5px; margin-top:-5px">MATRÍCULA: <b><u>{{$alumno->matricula}}</u></b></p>


            @if ($alumno->licenciatura_id == 6)
                <table width="100%"  style="height:200px" class="tblPrincipal fisica"  cellpadding="0" cellspacing="0">
            @else
                <table width="100%" class="tblPrincipal"  cellspacing="0">
            @endif




          <tr>
              <!-- Tabla 1 -->
              <td width="50%" height="50%" valign="top" >
            <table width="100%" class="tbl1"  cellspacing="0">
                <thead>
                    <tr>
                        <th style="border-top:1px;  font-size:12px; border-left:1px transparent;background:#C5C5C5"><b>ASIGNATURAS</b></th>
                        <th style="border-top:1px;  font-size:12px; border-left:1px solid #000;background:#C5C5C5;"><b>CAL.<br>FINAL</b></th>

                        @if($alumno->licenciatura_id == 6)
                               <th style="border-top:1px transparent; font-size:12px; border-left:1px transparent;background:#C5C5C5;  font-size:10px; border-left:1px solid #000"><b>OBSERVA<br>CIONES</b></th>
                        @else
                            <th style="border-top:1px transparent; font-size:12px; border-left:1px transparent;background:#C5C5C5;   border-left:1px solid #000"><b>OBSERVACIONES</b></th>
                        @endif

                        </tr>
                </thead>
                        <tbody>
                        @foreach ($cuatrimestres as $key => $cuatrimestre)

                                @php
                                    $nombreCuatrimestre = '';
                                   switch ($cuatrimestre->id) {
                                    case '1':
                                        $nombreCuatrimestre = 'PRIMER CUATRIMESTRE';
                                        break;
                                    case '2':
                                        $nombreCuatrimestre = 'SEGUNDO CUATRIMESTRE';
                                        break;
                                    case '3':
                                        $nombreCuatrimestre = 'TERCER CUATRIMESTRE';
                                        break;
                                    case '4':
                                        $nombreCuatrimestre = 'CUARTO CUATRIMESTRE';
                                        break;
                                    case '5':
                                        $nombreCuatrimestre = 'QUINTO CUATRIMESTRE';
                                        break;
                                    case '6':
                                        $nombreCuatrimestre = 'SEXTO CUATRIMESTRE';
                                        break;
                                    case '7':
                                        $nombreCuatrimestre = 'SEPTIMO CUATRIMESTRE';
                                    break;
                                    case '8':
                                        $nombreCuatrimestre = 'OCTAVO CUATRIMESTRE';
                                        break;
                                    case '9':
                                        $nombreCuatrimestre = 'NOVENO CUATRIMESTRE';
                                        break;

                                    default:
                                        # code...
                                        break;
                                 }



                                @endphp

                                 @php


                                            $materias = \DB::table('asignacion_materias')
                                                    ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                                                    ->where('asignacion_materias.licenciatura_id', $alumno->licenciatura_id)
                                                    ->where('asignacion_materias.modalidad_id', $alumno->modalidad_id)
                                                    ->where('asignacion_materias.cuatrimestre_id', $cuatrimestre->id)
                                                    ->where('materias.calificable', '!=', 'false')
                                                    ->orderBy('materias.clave', 'asc')
                                                    ->select('materias.*', 'asignacion_materias.id as asignacion_materia_id')
                                                    ->get();




                                 @endphp

                            @if($cuatrimestre->id < 5)

                                    @php
                                        $periodo = \App\Models\Periodo::where('generacion_id', $alumno->generacion_id)
                                            ->where('cuatrimestre_id', $cuatrimestre->id)
                                            ->first();
                                    @endphp



                                    <tr class="cuatrimestres">
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000">
                                            <strong>{{$nombreCuatrimestre}}</strong><br>
                                            <strong>CICLO ESCOLAR: {{ $periodo ? $periodo->ciclo_escolar : '' }}</strong>
                                        </td>
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000"></td>
                                        <td style="padding-top:10px; padding-left:10px; text-align:center;"></td>
                                    </tr>


                                    @foreach ($materias as $key => $materia)

                                            @php
                                              $calificacion = \DB::table('calificaciones')
                                                    ->where('alumno_id', $alumno->id)
                                                    ->where('asignacion_materia_id', $materia->asignacion_materia_id)
                                                    ->first();
                                            @endphp

                                            <tr>
                                                <td style="text-align:left; font-size:11px; line-height:12px; text-transform:uppercase;
                                                 padding-left:10px; padding-top:0; padding-bottom:0; margin:0;  ">
                                                 {{$materia->nombre}}</td>
                                              <td style="text-align:center; font-size:11px; line-height:10px; text-transform:uppercase;
                                                 padding-top:0; padding-bottom:0; margin:0; border-left:1px solid #000;  border-right:1px solid #000">
                                                    @if(isset($calificacion) && $calificacion->calificacion !== null && $calificacion->calificacion !== '' && $calificacion->calificacion != 0)
                                                        {{ $calificacion->calificacion }}
                                                    @else
                                                      ---
                                                    @endif
                                                </td>
                                              <td  style="text-align:center; font-size:11px; line-height:10px; text-transform:uppercase;
                                                 padding-top:0; padding-bottom:0; margin:0; border-left:1px solid #000;"></td>

                                            </tr>


                                  @endforeach


                           @endif

                        @endforeach



                        @php
                            // Definir el número de filas vacías necesarias para cada licenciatura_id
                            $filas = 0;
                            if ($alumno->licenciatura_id == 5) { // EDUCACIÓN
                                $filas = 3;
                            } elseif ($alumno->licenciatura_id == 1) { // NUTRICIÓN
                                $filas = 4;
                            }
                        @endphp

                        @for ($i = 0; $i < $filas; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td style="padding-left:10px; text-align:center; border-right:1px solid #000; border-left:1px solid #000">&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor


              </tbody>
            </table>
        </td>

        <!-- Tabla 2 -->
        <td width="50%" valign="top">
            <table width="100%" class="tbl2" cellspacing="0">
                <thead>
                    <tr>
                        <th style="border-top:1px;  font-size:12px; border-left:1px solid #000;background:#C5C5C5"><b>ASIGNATURAS</b></th>
                        <th style="border-top:1px;  font-size:12px; border-left:1px solid #000;background:#C5C5C5;"><b>CAL.<br>FINAL</b></th>

                        @if($alumno->licenciatura_id == 6)
                               <th style="border-top:1px transparent; font-size:12px; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent; font-size:10px; border-left:1px solid #000"><b>OBSERVA<br>CIONES</b></th>
                        @else
                            <th style="border-top:1px transparent; font-size:12px; border-left:1px transparent;background:#C5C5C5;   border-left:1px solid #000"><b>OBSERVACIONES</b></th>
                        @endif

                        </tr>
                </thead>
                      <tbody>
                       @foreach ($cuatrimestres as $key => $cuatrimestre)

                              @php
                                    $nombreCuatrimestre = '';
                                   switch ($cuatrimestre->id) {
                                    case '1':
                                        $nombreCuatrimestre = 'PRIMER CUATRIMESTRE';
                                        break;
                                    case '2':
                                        $nombreCuatrimestre = 'SEGUNDO CUATRIMESTRE';
                                        break;
                                    case '3':
                                        $nombreCuatrimestre = 'TERCER CUATRIMESTRE';
                                        break;
                                    case '4':
                                        $nombreCuatrimestre = 'CUARTO CUATRIMESTRE';
                                        break;
                                    case '5':
                                        $nombreCuatrimestre = 'QUINTO CUATRIMESTRE';
                                        break;
                                    case '6':
                                        $nombreCuatrimestre = 'SEXTO CUATRIMESTRE';
                                        break;
                                    case '7':
                                        $nombreCuatrimestre = 'SEPTIMO CUATRIMESTRE';
                                    break;
                                    case '8':
                                        $nombreCuatrimestre = 'OCTAVO CUATRIMESTRE';
                                        break;
                                    case '9':
                                        $nombreCuatrimestre = 'NOVENO CUATRIMESTRE';
                                        break;

                                    default:
                                        # code...
                                        break;
                                 }

                                @endphp


                            @if($cuatrimestre->id > 4)

                             @php


                                      $periodo = \App\Models\Periodo::where('generacion_id', $alumno->generacion_id)
                                            ->where('cuatrimestre_id', $cuatrimestre->id)
                                            ->first();


                                            $materias = \DB::table('asignacion_materias')
                                                    ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                                                    ->where('asignacion_materias.licenciatura_id', $alumno->licenciatura_id)
                                                    ->where('asignacion_materias.modalidad_id', $alumno->modalidad_id)
                                                    ->where('asignacion_materias.cuatrimestre_id', $cuatrimestre->id)
                                                    ->where('materias.calificable', '!=', 'false')
                                                    ->orderBy('materias.clave', 'asc')
                                                    ->select('materias.*', 'asignacion_materias.id as asignacion_materia_id')
                                                    ->get();
                                 @endphp



                                     <tr class="cuatrimestres">
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-left:1px solid #000;  border-right:1px solid #000">
                                            <strong>{{$nombreCuatrimestre}}</strong><br>
                                              <strong>CICLO ESCOLAR: {{ $periodo ? $periodo->ciclo_escolar : '' }}</strong>
                                        </td>
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-left:1px solid #000; border-right:1px solid #000"></td>
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-left:1px solid #000;"></td>
                                    </tr>


                                    @foreach ($materias as $key => $materia)

                                            @php
                                              $calificacion = \DB::table('calificaciones')
                                                    ->where('alumno_id', $alumno->id)
                                                    ->where('asignacion_materia_id', $materia->asignacion_materia_id)
                                                    ->first();
                                            @endphp

                                            <tr>
                                                <td style="text-align:left; font-size:11px; border-left:1px solid #000;line-height:12px; text-transform:uppercase;
                                                 padding-left:10px; padding-top:0; padding-bottom:0; margin:0;  ">
                                                 {{$materia->nombre}}</td>
                                              <td style="text-align:center; font-size:11px; line-height:12px; text-transform:uppercase;
                                                 padding-top:0; padding-bottom:0; margin:0; border-left:1px solid #000; ">
                                                   @if(isset($calificacion) && $calificacion->calificacion !== null && $calificacion->calificacion !== '' && $calificacion->calificacion != 0)
                                                        {{ $calificacion->calificacion }}
                                                    @else
                                                       ---
                                                    @endif
                                                </td>
                                              <td  style="text-align:center; font-size:11px; line-height:12px; text-transform:uppercase;
                                                 padding-top:0; padding-bottom:0; margin:0; border-left:1px solid #000;"></td>

                                            </tr>


                                  @endforeach
                           @endif

                        @endforeach


                     </tbody>
            </table>
        </td>
    </tr>
</table>

@php
    // Obtener los IDs de materias calificables (no prácticas)
    $materiasCalificables = \App\Models\Materia::where('licenciatura_id', $alumno->licenciatura_id)
        ->where('calificable', 'true')
        ->pluck('id')
        ->toArray();


    // Obtener los creditos de materias calificables (no prácticas)
    $creditosMateriasCalificables = \App\Models\Materia::where('licenciatura_id', $alumno->licenciatura_id)
        ->where('calificable', 'true')
        ->sum('creditos');

    // Función para convertir número a letras en español
    function numeroALetras($numero) {
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        return mb_strtoupper($formatter->format($numero), 'UTF-8');
    }
    $materiasCalificablesEnLetras = numeroALetras(count($materiasCalificables));


      // Obtener los IDs de las asignaciones de materias calificables para el alumno
    $asignacionesCalificables = \DB::table('asignacion_materias')
        ->where('licenciatura_id', $alumno->licenciatura_id)
        ->where('modalidad_id', $alumno->modalidad_id)
        ->whereIn('materia_id', $materiasCalificables)
        ->pluck('id')
        ->toArray();

    // Obtener las calificaciones del alumno (solo las válidas y no nulas, mayores que cero)
    $calificaciones = \DB::table('calificaciones')
        ->where('alumno_id', $alumno->id)
        ->whereIn('asignacion_materia_id', $asignacionesCalificables)
        ->whereNotNull('calificacion')
        ->where('calificacion', '!=', '')
        ->where('calificacion', '!=', '0')
        ->pluck('calificacion')
        ->map(function($value) {
            return floatval($value);
        });

    $suma = $calificaciones->sum();
    $cuenta = $calificaciones->count();
    $promedio = $cuenta > 0 ? round($suma / $cuenta, 1) : '';
@endphp

<p style="text-align:justify; font-size:13px; margin:0; text-transform:uppercase">EL PRESENTE CERTIFICADO DE AMPARA <u><b>{{ $materiasCalificablesEnLetras }}</b></u> ASIGNATURAS, LAS CUALES CUBREN ÍNTEGRAMENTE EL PLAN DE ESTUDIOS DE LA LICENCIATURA <b>{{$licenciatura->nombre}}</b>
        CON UN TOTAL DE <b>{{ $creditosMateriasCalificables }}</b> CRÉDITOS Y UN PROMEDIO GENERAL DE APROVECHAMIENTO DE <b>{{ $promedio }}</b> LA ESCALA DE CALIFICACIONES ES DE (5 A 10) Y LA MÍNIMA APROBATORIA ES DE 6 (SEIS).
 </p>

        @php
            // Procesar la fecha dinámica
            $fechaObj = \Carbon\Carbon::parse($fecha);

            // Día en número, con cero a la izquierda si es menor a 10
            $dia = str_pad($fechaObj->day, 2, '0', STR_PAD_LEFT);

            // Mes en español, en minúsculas
            $meses = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
            ];
            $mes = $meses[intval($fechaObj->month)];

            // Año en letras dinámico
            function anioALetras($anio) {
            $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
            return mb_strtolower($formatter->format($anio), 'UTF-8');
            }
            $anio = anioALetras($fechaObj->year);


        @endphp

        <p style="text-align:justify; font-size:13px; text-transform:uppercase">
            EXPEDIDO EN CD. ALTAMIRANO, GUERRERO A <u>{{ $dia }}</u> DE <u>{{ $mes }}</u> del año <u>{{ $anio }}</u>.
        </p>

        @if($alumno->licenciatura_id == 6)
            <table style="margin:25px auto 0; text-align:center; font-size:14px; width:100%; text-transform:uppercase; line-height:12px">
                <tr>
                <td  colspan="5">_______________________________</td>
                </tr>
                <tr>
                <td colspan="5"><b>{{ $rector->nombre }} {{ $rector->apellido_paterno }} {{ $rector->apellido_materno }}</b></td>
                </tr>
                <tr>
                <td  colspan="5"><b>RECTOR</b></td>
                </tr>
            </table>
        @else
            <table style="margin:60px auto 0; text-align:center; font-size:13px; width:100%; text-transform:uppercase; line-height:12px">
                <tr>
                <td  colspan="5">_______________________________</td>
                </tr>
                <tr>
                <td colspan="5"><b>{{ $rector->nombre }} {{ $rector->apellido_paterno }} {{ $rector->apellido_materno }}</b></td>
                </tr>
                <tr>
                <td  colspan="5"><b>RECTOR</b></td>
                </tr>
            </table>

        @endif

   <div class="page-break"></div>
   {{-- PAGINA 2 --}}

        <table class="autoridades" style="width:100%; margin-top:24px">
            <tr>
                <td style="border:1px solid #000; width:250px; text-align:center; font-size:16px; border-bottom:1px transparent">REVISADO Y CONFRONTADO POR:</td>
                <td style="width:220px"></td>
                <td style="border:1px solid #000; width:250px; text-align:center; font-size:16px; line-height:13px ">JEFE(A) DEL DEPARTAMENTO DE <br> REGISTRO Y CERTIFICACIÓN</td>
            </tr>
            <tr>
                <td style="border:1px solid #000; width:250px; height:50px; text-align:center; font-size:16px;  border-bottom:1px transparent"></td>
                <td style="width:220px;  border-bottom:1px transparent"></td>
                <td style="border:1px solid #000; width:250px; height:50px; text-align:center; font-size:16px;border-bottom:1px transparent; border-top:1px transparent"></td>
            </tr>
            <tr>
                <td style="border:1px solid #000; width:200px; height:20px; font-size:16.5px; text-align:center; border-top:1px transparent; border-bottom:1px transparent; padding:0 10px"><p style="text-align:center;">BERNARDO LÓPEZ BELLO</p></td>
                <td style="width:220px;font-size:15px; text-align:center"><br><b>SELLO</b></td>
                <!-- <td style="border:1px solid #000;  text-align:center; width:200px; height:70px; font-size:16.5px; border-top:1px transparent;">PEDRO PASTOR DEL MORAL</td> -->
                <td style="border:1px solid #000; width:200px; text-align:center; height:20px; border-top:1px transparent; border-bottom:1px transparent; font-size:16.5px;">FRANCISCO JAVIER MEDINA MARIN</td>
            </tr>
            <tr>
                <td style="width:200px; height:45px; font-size:16px; text-align:left; border:1px solid #000; border-top:1px transparent; padding:0 10px">FECHA:</td>
                <td style="width:220px;font-size:15px; text-align:center"></td>
                <td style="border:1px solid #000; width:200px;  text-align:center; height:55px;  text-align:center; font-size:16px; border-top:1px transparent"></td>
            </tr>
         </table>

          <table style="width:35%; margin-top:50px; margin-bottom:910px">
            <tr>
                <td style="width:100px; font-size:16px;padding:5px; text-align:center; line-height:15px; text-transform:uppercase">
                _________________________<br>
                    {{ $directora->nombre }} {{ $directora->apellido_paterno }} {{ $directora->apellido_materno }} <br>
                    DIRECTORA GENERAL

                </td>
            </tr>
         </table>




         {{-- <table style="width:100%; margin-top:1020px"> --}}
         <table style="width:100%;">
            <tr>
                <td style="width:132px; line-height:10px; padding: 10px; text-align:center; font-size:16.5px; border:1.5px solid #000; ">FOLIO: <b>{{$alumno->folio}}</b></td>
                <td style="border:1px solid #000; text-align:left; padding:0 5px; font-size:9.5px; border:1px transparent">
                    ESTE CERTIFICADO REQUIERE DE TRAMITES ADICIONALES DE LEGALIZACIÓN. NO ES VÁLIDO SI PRESENTA BORRADURAS O ENMENDADURAS</td>
            </tr>
         </table>










</body>
</html>
