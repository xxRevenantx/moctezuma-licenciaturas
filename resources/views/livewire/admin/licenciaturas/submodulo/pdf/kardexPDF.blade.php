<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>KARDEX | {{ $alumno->matricula }}</title>
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
        margin-top: 10px;
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
         margin-top: 2px

    }
    table.cuatrimestres td{
        font-weight: bold;
        font-size: 12px;

    }

    table.calificaciones{
            width: 100%;
         border-collapse: collapse;
    }

    table.calificaciones td{
        font-size: 12px;
        border:1px solid #000
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

    </style>
<body>




<div class="encabezado">
          <img class="img_encabezado" src="{{ public_path('storage/encabezado.png') }}" alt="Encabezado">
    </div>

    <h1 class="titulo">KARDEX DEL ALUMNO</h1>


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
            <td colspan="2" style="text-decoration: underline;font-weight:bold; text-align:center; padding-top:8px">{{ $licenciatura->nombre }}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center; padding-top:8px">ESCOLARIZADA</td>
        </tr>

         <tr>
            <td colspan="2" style="font-size:13px; text-align:center">LICENCIATURA</td>
            <td style="font-size:13px; text-align:center">MODALIDAD</td>
        </tr>



    </table>


             @foreach ($cuatrimestres as $key => $cuatrimestre)

                    @if($cuatrimestre->id === 6)
                           <div class="page-break"></div>
                    @endif

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

                                 $materias = \DB::table('asignacion_materias')
                                                    ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                                                    ->where('asignacion_materias.licenciatura_id', $alumno->licenciatura_id)
                                                    ->where('asignacion_materias.modalidad_id', $alumno->modalidad_id)
                                                    ->where('asignacion_materias.cuatrimestre_id', $cuatrimestre->id)
                                                    ->where('materias.calificable', '!=', 'false')
                                                    ->orderBy('materias.clave', 'asc')
                                                    ->select('materias.*', 'asignacion_materias.id as asignacion_materia_id')
                                                    ->get();



                                        $periodo = \App\Models\Periodo::where('generacion_id', $alumno->generacion_id)
                                            ->where('cuatrimestre_id', $cuatrimestre->id)
                                            ->first();



                            @endphp





    <table class="cuatrimestres">
        <tr>
            <td style="width:300px">{{ $nombreCuatrimestre }}</td>
            <td style="width:250px">
                @if($periodo && $periodo->mes && $periodo->inicio_periodo)
                    PERIODO: {{ $periodo->mes->meses }} {{ \Carbon\Carbon::parse($periodo->inicio_periodo)->year }}
                @else
                    PERIODO: -----
                @endif
            </td>
            <td>CICLO ESCOLAR: {{ $periodo->ciclo_escolar ?? "----" }}</td>
        </tr>
    </table>


    <table class="calificaciones">

            <tr>
                     <td style="text-align:center; line-height:9px; padding:0; font-weight:bold;" rowspan="2">CLAVE</td>
                    <td style=" width:230px;text-align:center;  font-weight:bold; line-height:9px; padding:0;" rowspan="2">ASIGNATURA</td>
                    <td style=" width:10px; text-align:center;  font-weight:bold; line-height:9px; padding:0;" rowspan="2">CAL. <br>FINAL</td>
                    <td style="text-align:center;  font-weight:bold; line-height:9px; padding:0;" rowspan="2">%.<br> ASIST.</td>
                    <td style="text-align:center; font-weight:bold; line-height:9px; padding:0;" colspan="6">PERIODOS DE REGULARIZACIÓN</td>

                    <td rowspan="{{ count($materias) + 2  }}" style="width: 20px; border:none; padding:0; margin:0"></td>


                    <td  rowspan="{{ count($materias) + 2 }}" style="width:125px; padding:0; margin:0">
                        <p style="text-align:center; font-size:12px; font-weight:bold; line-height:8px; margin:0; padding:0 ">REVISADO Y CONFRONTADO</p>

                        <p style="width:100%; border-top:1px solid #000; margin:100px 0 0 0;  padding:0"></p>
                        <p style="text-align:center; font-size:14px; margin:0; padding:0;  line-height:11px;  ">DÍA/MES/AÑO
                            <br>
                            @if($periodo && $periodo->termino_periodo)
                                {{ \Carbon\Carbon::parse($periodo->termino_periodo)->format('d/m/y') }}
                            @else
                                ------
                            @endif


                        </p>
                    </td>
            </tr>

                    <tr>

                    <td style="text-align:center; font-weight:bold;">FECHA</td>
                    <td style="text-align:center; font-weight:bold;">CALIF.</td>
                    <td style="text-align:center; font-weight:bold;">FECHA</td>
                    <td style="text-align:center; font-weight:bold;">CALIF.</td>
                    <td style="text-align:center; font-weight:bold;">FECHA</td>
                    <td style="text-align:center; font-weight:bold; border-right:1px solid #000 ">CALIF.</td>
                    </tr>

                    <tbody>

                @foreach ($materias as $materia)

                    @php
                        $calificacion = \DB::table('calificaciones')
                            ->where('alumno_id', $alumno->id)
                            ->where('asignacion_materia_id', $materia->asignacion_materia_id)
                            ->first();
                    @endphp
                    <tr>
                        <td style="text-align: center; height: 5px; padding:0px">{{ $materia->clave }}</td>
                        <td style="text-transform:uppercase; height: 5px; line-height:8px; font-size:11px; padding-left:5px; padding-top:0; padding-bottom:0; margin:0">{{ $materia->nombre }}</td>
                        <td
                        style="text-align:center; padding:0px;"
                        >
                            {{ $calificacion ? $calificacion->calificacion : "---" }}
                        </td>
                        <td style="text-align:center; padding:0px">100</td>
                        <td style="padding:0px"></td>
                        <td style="padding:0px"></td>
                        <td style="padding:0px"></td>
                        <td style="padding:0px"></td>
                        <td style="padding:0px"></td>
                        <td style="padding:0px"></td>
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

                            //  $promedio = $cuenta > 0 ? floor($suma / $cuenta * 10) / 10 : '';
                             $promedio = $cuenta > 0 ? number_format(floor($suma / $cuenta * 10) / 10, 1) : '';
                        @endphp

                                        @endforeach


                </tbody>

    </table>
        @endforeach


     <table class="tabla-contenedor">
        <tr>
            <td class="celda firma-rector" style="border: 1px solid #000">
                <div class="linea">  {{ $rector->nombre }} {{ $rector->apellido_paterno }} {{ $rector->apellido_materno }} RECTOR(A)</div>
            </td>
            <td class="celda">
                <div class="promedio">
                    <span class="prom-label" style="line-height: 13px">PROMEDIO<br>GENERAL DE<br>APROVECHAMIENTO:</span>
                    <span class="prom-box">{{ $promedio }}</span>
                </div>
            </td>
        </tr>
    </table>





</body>
</html>
