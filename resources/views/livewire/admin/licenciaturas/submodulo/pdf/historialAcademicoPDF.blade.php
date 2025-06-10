<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>HISTORIAL ACADÉMICO | {{ $alumno->matricula }}</title>
</head>
<style>

            @page { margin:5px 45px 0px 45px; }

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
                width: 60%;
                margin-left: -100px;
            }

            .titulo {
                text-align: center;
                font-size: 20px;
                font-weight: bold;
                margin-top: 10px;
                margin-bottom: 5px;
            }

            table.datos {
                width: 100%;
                border-collapse: collapse;
                /* margin-top: 10px; */
                border: 1px solid #000;
                text-transform: uppercase;
            }

            table.datos td {

                /* border: 1px solid #000; */
                padding: 0;
                line-height: 13px;
            }

            table.cuatrimestres{
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px

            }
            table.cuatrimestres td{
                font-weight: bold;
                font-size: 12px;

            }

            table.calificaciones{
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            table.calificaciones td{
                font-size: 12px;
                border:1px solid #000;
                padding: 0;
                line-height: 10px;
            }

        .tabla-contenedor {
                    width: 100%;
                    border: 1px solid black;
                    border-collapse: collapse;
                    table-layout: fixed;
                    margin-top: 70px;
                }

                .celda {
                    width: 50%;
                    height: 110px;
                    border-collapse: collapse;
                    vertical-align: top;
                }

                .firma-rector {
                    text-align: center;
                    padding-top: 150px;
                }

                .firma-rector .linea {
                    border-top: 1px solid black;
                    width: 75%;
                    margin: 0 auto;
                    padding-top: 0px;
                    text-transform: uppercase
                }

                .promedio {
                    padding: 65px 0 0 20px;
                }

                .prom-label {
                    line-height: 1.5;
                    display: inline-block;
                    margin-top: 90px;
                }

                .prom-box {
                    display: inline-block;
                    width: 70px;
                    height: 20px;
                    border: 1px solid black;
                    vertical-align: middle;
                    margin-left: 10px;
                    margin-top: 90px;
                    text-align: center;
                    padding-bottom: 20px;
                    font-size: 20px;
                    font-weight: bold
                }



                table td.crossed
                    {
                    width: 50px;
                        height: 50px;
                        border: 1px solid black;
                        background: linear-gradient(to top right, black 1px, transparent 1px);
                    }

                    .datos_alumno {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                        border: 1px solid #000;
                    }
                    .datos_alumno td {
                        font-size: 12px;
                        /* border: 1px solid #000; */
                        padding: 1px 3px;
                        text-align: center;
                    }

    </style>
<body>




<div class="encabezado">
          <img class="img_encabezado" src="{{ public_path('storage/encabezado.png') }}" alt="Encabezado">
    </div>

    <h1 class="titulo">HISTORIAL ACADÉMICO</h1>


    <table class="datos">
        <tr>
            <td style="text-decoration: underline;font-weight:bold; text-align:center; font-size:14px">{{ $escuela->nombre }}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center">{{ $licenciatura->RVOE ?? "-------"}}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center">{{ $escuela->CCT}}</td>
        </tr>
        <tr>
            <td style="font-size:13px; text-align:center">NOMBRE DE LA ESCUELA</td>
            <td style="font-size:13px; text-align:center">No. DE ACUERDO DE INCORPORACIÓN</td>
            <td style="font-size:13px; text-align:center">CLAVE DEL CENTRO DE TRABAJO</td>
        </tr>

        <tr>
            <td style="text-decoration: underline;font-weight:bold; text-align:center; padding-top:8px">{{ $alumno->nombre }}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center; padding-top:8px">{{ $alumno->apellido_paterno}}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center; padding-top:8px">{{ $alumno->apellido_materno}}</td>
        </tr>

         <tr>
            <td style="font-size:13px; text-align:center">NOMBRE(S)</td>
            <td style="font-size:13px; text-align:center">PRIMER APELLIDO</td>
            <td style="font-size:13px; text-align:center">SEGUNDO APELLIDO</td>
        </tr>

        <tr>
            <td  style="text-decoration: underline;font-weight:bold; text-align:center; padding-top:8px">{{ $alumno->matricula }}</td>
            <td  style="text-decoration: underline;font-weight:bold; text-align:center; padding-top:8px">{{ $licenciatura->nombre }}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center; padding-top:8px">ESCOLARIZADA</td>
        </tr>

         <tr>
            <td  style="font-size:13px; text-align:center">MATRÍCULA</td>
            <td  style="font-size:13px; text-align:center">LICENCIATURA</td>
            <td style="font-size:13px; text-align:center">MODALIDAD</td>
        </tr>

    </table>


    <table class="datos_alumno">

        @php
            $promedio = \DB::table('calificaciones')
                ->where('alumno_id', $alumno->id)
                ->whereNotNull('calificacion')
                ->where('calificacion', '!=', '')
                ->where('calificacion', '!=', '0')
                ->where('calificacion', '!=', 'NP')
                ->pluck('calificacion')
                ->map(function($value) {
                    return floatval($value);
                });
            $suma = $promedio->sum();
            $cuenta = $promedio->count();
            $promedio = $cuenta > 0 ? floor($suma / $cuenta * 10) / 10 : '';


            $creditosAlumno = \DB::table('calificaciones')
                ->join('asignacion_materias', 'calificaciones.asignacion_materia_id', '=', 'asignacion_materias.id')
                ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                ->where('calificaciones.alumno_id', $alumno->id)
                ->whereNotNull('calificaciones.calificacion')
                ->where('calificaciones.calificacion', '!=', '')
                ->where('calificaciones.calificacion', '!=', '0')
                ->where('calificaciones.calificacion', '!=', 'NP')
                ->where('materias.calificable', '!=', 'false')
                ->where('calificaciones.calificacion', '>', 5)
                ->sum('materias.creditos');


            $creditosTotales = \DB::table('asignacion_materias')
                ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                ->where('asignacion_materias.licenciatura_id', $alumno->licenciatura_id)
                ->where('asignacion_materias.modalidad_id', $alumno->modalidad_id)
                ->where('asignacion_materias.cuatrimestre_id', '!=', null)
                ->where('materias.calificable', '!=', 'false')
                ->sum('materias.creditos');


            $materiasAprobadas = \DB::table('calificaciones')
                ->join('asignacion_materias', 'calificaciones.asignacion_materia_id', '=', 'asignacion_materias.id')
                ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                ->where('calificaciones.alumno_id', $alumno->id)
                ->whereNotNull('calificaciones.calificacion')
                ->where('calificaciones.calificacion', '!=', '')
                ->where('calificaciones.calificacion', '!=', '0')
                ->where('calificaciones.calificacion', '!=', 'NP')
                ->where('materias.calificable', '!=', 'false')
                ->where('calificaciones.calificacion', '>', 5)
                ->count();

            $materiasReprobadas = \DB::table('calificaciones')
                ->join('asignacion_materias', 'calificaciones.asignacion_materia_id', '=', 'asignacion_materias.id')
                ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                ->where('calificaciones.alumno_id', $alumno->id)
                ->whereNotNull('calificaciones.calificacion')
                ->where('calificaciones.calificacion', '!=', '')
                ->where('calificaciones.calificacion', '!=', '0')
                ->where('calificaciones.calificacion', '!=', 'NP')
                ->where('materias.calificable', '!=', 'false')
                ->where('calificaciones.calificacion', '<', 6)
                ->count();

            $materiasNoPresentadas = \DB::table('calificaciones')
                ->join('asignacion_materias', 'calificaciones.asignacion_materia_id', '=', 'asignacion_materias.id')
                ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                ->where('calificaciones.alumno_id', $alumno->id)
                ->where(function($query) {
                    $query->whereNull('calificaciones.calificacion')
                          ->orWhere('calificaciones.calificacion', 'NP');
                          // ->orWhere('calificaciones.calificacion', '')
                          // ->orWhere('calificaciones.calificacion', '0')
                })
                // ->where('materias.calificable', '!=', 'false')
                ->count();



        @endphp

        <tr>
            <td style="width:20px; text-align:center;  font-weight:bold; background:#d9d9d9" >PROMEDIO: </td>
            <td style="width:100px; text-align:center; font-size:14px;  font-weight:bold;">{{$promedio}}</td>
            <td style="width:20px; text-align:center;  font-weight:bold; background:#d9d9d9">APROBADAS: </td>
            <td style="width:20px; text-align:center; font-size:14px; font-weight:bold;">{{$materiasAprobadas}}</td>
            <td style=" text-align:center;  font-weight:bold; font-size:14px; background:#d9d9d9" rowspan="3">
                Fecha de expedición : {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td style="width:20px; text-align:center;  font-weight:bold; background:#d9d9d9" rowspan="2">CRÉDITOS: </td>
            <td style="width:100px; text-align:center;font-size:14px;  font-weight:bold;"  rowspan="2">{{$creditosAlumno}} de {{$creditosTotales}}</td>
            <td style="width:50px; text-align:center;  font-weight:bold; background:#d9d9d9">REPROBADAS: </td>
            <td style=" text-align:center;  font-weight:bold; font-size:14px;">{{ $materiasReprobadas }}</td>

        </tr>
        <tr>
           <td style="width:110px; text-align:center;  font-weight:bold; background:#d9d9d9">NO PRESENTADAS: </td>
          <td style=" text-align:center;  font-weight:bold; width:100px; font-size:14px;">{{ $materiasNoPresentadas }}</td>

        </tr>
    </table>

    @php
         $contadorMateria = 1;
    @endphp

    <table class="calificaciones">

            <tr>
                     <td style=" text-align:center;  font-weight:bold; background:#d9d9d9">#</td>
                     <td style=" text-align:center;  font-weight:bold; background:#d9d9d9">CLAVE</td>
                     <td style=" text-align:center;  font-weight:bold; background:#d9d9d9">CRÉDITOS</td>
                     <td style=" text-align:center;  font-weight:bold; background:#d9d9d9">CUATRIMESTRE</td>
                    <td style="text-align:center;  font-weight:bold; background:#d9d9d9" >ASIGNATURA</td>
                    <td style="text-align:center;  font-weight:bold; background:#d9d9d9" >CALIFICACIÓN</td>
                    <td style="text-align:center;  font-weight:bold; background:#d9d9d9" >TIPO DE EVALUACIÓN</td>
            </tr>


                    <tbody>


             @foreach ($periodos as $key => $periodo)



                           @php
                                    $nombreCuatrimestre = '';
                                   switch ($periodo->cuatrimestre->id) {
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

                                 $materias = \DB::table('asignacion_materias')
                                                    ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                                                    ->where('asignacion_materias.licenciatura_id', $alumno->licenciatura_id)
                                                    ->where('asignacion_materias.modalidad_id', $alumno->modalidad_id)
                                                    ->where('asignacion_materias.cuatrimestre_id', $periodo->cuatrimestre->id)
                                                    ->where('materias.calificable', '!=', 'false')
                                                    ->orderBy('materias.clave', 'asc')
                                                    ->select('materias.*', 'asignacion_materias.id as asignacion_materia_id')
                                                    ->get();





                            @endphp



                @foreach ($materias as $key2 => $materia)

                    @php
                        $calificacion = \DB::table('calificaciones')
                            ->where('alumno_id', $alumno->id)
                            ->where('asignacion_materia_id', $materia->asignacion_materia_id)
                            ->first();
                    @endphp
                    <tr>
                        <td style="text-align: center; padding:0px">{{ $contadorMateria }}</td>
                        <td style="text-align: center; padding:0px">{{ $materia->clave }}</td>
                        <td style="text-align: center; padding:0px">{{ $materia->creditos }}</td>
                        <td style="text-align: center; padding:0px">{{ $materia->cuatrimestre_id }}</td>
                        <td style="text-transform:uppercase;font-size:10px; padding-top:0; padding-left:0; padding-left:5px; margin:0">{{ $materia->nombre }}</td>
                        <td
                        style="text-align:center; padding:0px;"
                        >
                            @if ($calificacion)
                                @if ($calificacion->calificacion === 'NP' || (is_numeric($calificacion->calificacion) && $calificacion->calificacion < 6))
                                    <span style="color: red; font-weight: bold;">
                                        {{ $calificacion->calificacion }}
                                    </span>
                                @else
                                    {{ $calificacion->calificacion }}
                                @endif
                            @else
                                EN PROCESO
                            @endif
                        </td>
                        <td style="text-align:center;  padding:0px;">
                           {{ $calificacion ? "ORD" : "EN PROCESO" }}
                        </td>
                    </tr>



                            @php
                            // Obtener los IDs de materias calificables (no prácticas)
                            $materiasCalificables = \App\Models\Materia::where('licenciatura_id', $alumno->licenciatura_id)
                                ->where('calificable', 'true')
                                ->pluck('id')
                                ->toArray();

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

                                        @php
                               $contadorMateria++;
                                 @endphp

                                        @endforeach

   @endforeach
                </tbody>

    </table>








</body>
</html>
