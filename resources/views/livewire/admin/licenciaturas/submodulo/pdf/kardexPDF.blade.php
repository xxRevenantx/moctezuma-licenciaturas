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

    table.datos {
        width: 100%;
        border-collapse: collapse;
        /* margin-top: 10px; */
        border: 1px solid #000;
        text-transform: uppercase;
    }

    table.datos td {

        /* border: 1px solid #000; */
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
            margin-top: 50px;
        }

        .celda {
            width: 50%;
            height: 140px;
            border-collapse: collapse;
            vertical-align: top;
        }

        .firma-rector {
            text-align: center;
            padding-top: 120px;
        }

        .firma-rector .linea {
            border-top: 1px solid black;
            width: 60%;
            margin: 0 auto;
            padding-top: 5px;
        }

        .promedio {
            padding: 30px 0 0 20px;
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

    </style>
<body>




<div class="encabezado">
          <img class="img_encabezado" src="{{ public_path('storage/encabezado.png') }}" alt="Encabezado">
    </div>

    <h1 class="titulo">KARDEX DEL ALUMNO</h1>


    <table class="datos">
        <tr>
            <td style="text-decoration: underline;font-weight:bold; text-align:center; font-size:14px">{{ $escuela->nombre }}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center">{{ $licenciatura->RVOE}}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center">{{ $escuela->CCT}}</td>
        </tr>
        <tr>
            <td style="font-size:13px; text-align:center">NOMBRE DE LA ESCUELA</td>
            <td style="font-size:13px; text-align:center">No. DE ACUERDO DE INCORPORACIÓN</td>
            <td style="font-size:13px; text-align:center">CLAVE DEL CENTRO DE TRABAJO</td>
        </tr>

        <tr>
            <td style="text-decoration: underline;font-weight:bold; text-align:center">{{ $alumno->nombre }}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center">{{ $alumno->apellido_paterno}}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center">{{ $alumno->apellido_materno}}</td>
        </tr>

         <tr>
            <td style="font-size:13px; text-align:center">NOMBRE(S)</td>
            <td style="font-size:13px; text-align:center">PRIMER APELLIDO</td>
            <td style="font-size:13px; text-align:center">SEGUNDO APELLIDO</td>
        </tr>

        <tr>
            <td colspan="2" style="text-decoration: underline;font-weight:bold; text-align:center">{{ $licenciatura->nombre }}</td>
            <td style="text-decoration: underline;font-weight:bold; text-align:center">ESCOLARIZADA</td>
        </tr>

         <tr>
            <td colspan="2" style="font-size:13px; text-align:center">LICENCIATURA</td>
            <td style="font-size:13px; text-align:center">MODALIDAD</td>
        </tr>



    </table>

    @foreach ($periodo as $dato)
        @php
        $nombreCuatrimestre = '';
    switch ($dato->cuatrimestre_id) {
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


 $nombreMeses = '';
    switch ($dato->mes->meses) {
        case 'SEPTIEMBRE/DICIEMBRE':
                $nombreMeses = "SEPTIEMBRE - DICIEMBRE";
            break;
        case 'ENERO/ABRIL':
                $nombreMeses = "ENERO - ABRIL";
            break;
        case 'MAYO/AGOSTO':
                $nombreMeses = "MAYO - AGOSTO";
            break;
        default:
             $nombreMeses = "---";
            break;
    }



    @endphp



  @if ($dato->cuatrimestre_id == 6)
            <div class="page-break"></div>
     @endif

    <table class="cuatrimestres">
        <tr>
            <td style="width:300px">{{ $nombreCuatrimestre }}</td>
            <td style="width:250px">PERIODO: {{ $nombreMeses }} {{ \Carbon\Carbon::parse($dato->inicio_periodo)->year }}</td>
            <td>CICLO ESCOLAR: {{ $dato->ciclo_escolar }}</td>
        </tr>
    </table>


    <table class="calificaciones">

                          @php
                    $materias = \DB::table('asignacion_materias')
                        ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
                        ->where('asignacion_materias.licenciatura_id', $alumno->licenciatura_id)
                        ->where('asignacion_materias.modalidad_id', $alumno->modalidad_id)
                        ->where('asignacion_materias.cuatrimestre_id', $dato->cuatrimestre_id)
                        ->where('materias.nombre', '!=', 'Práctica')
                        ->orderBy('materias.clave', 'asc')
                        ->select('materias.*', 'asignacion_materias.id as asignacion_materia_id')
                        ->get();
                   @endphp



            <tr>
         <td style="text-align:center;  font-weight:bold;" rowspan="2">CLAVE</td>
                    <td style=" width:230px;text-align:center;  font-weight:bold;" rowspan="2">ASIGNATURA</td>
                    <td style=" width:10px; text-align:center;  font-weight:bold;"; rowspan="2">CAL. <br>FINAL</td>
                    <td style="text-align:center;  font-weight:bold;" rowspan="2">%.<br> ASIST.</td>
                    <td style="text-align:center; font-weight:bold" colspan="6">PERIODOS DE REGULARIZACIÓN</td>

                    <td rowspan="{{ count($materias) + 2  }}" style="width: 20px; border:none; padding:0; margin:0"></td>


                    <td  rowspan="{{ count($materias) + 2 }}" style="width:125px; padding:0; margin:0">
                        <p style="text-align:center; font-size:12px; font-weight:bold; line-height:8px; margin:0; padding:0 ">REVISADO Y CONFRONTADO</p>

                        <p style="width:100%; border-top:1px solid #000; margin:100px 0 0 0;  padding:0"></p>
                        <p style="text-align:center; font-size:14px; margin:0; padding:0;  line-height:11px;  ">DÍA/MES/AÑO
                            <br>
                              {{ \Carbon\Carbon::parse($dato->termino_periodo)->format('d/m/y') }}


                        </p>
                    </td>



                       {{-- <td rowspan="{{ count($materias) + 2  }}" style="width:110px; padding:0; margin:0; height:100px; position:relative">
                             <p style="position:absolute; top:-13px; line-height:11px; text-align:center; font-weight:bold; font-size:12px">REVISADO Y CONFRONTADO</p>

                            <p style="width:100%; border-top:1px solid #000; margin:100px 0 0 0;  padding:0"></p>


                             <p style="text-align:center; font-size:14px; margin:0; padding:0 ">DÍA/MES/AÑO</p>

                             <p style="text-align:center; font-size:15px; margin:0; padding:0">
                                 {{ \Carbon\Carbon::parse($dato->termino_periodo)->format('d/m/y') }}
                             </p>
                    </td> --}}


            </tr>



                    <tr>

                    <td style="text-align:center; font-weight:bold;">FECHA</td>
                    <td style="text-align:center; font-weight:bold;">CALIF.</td>
                    <td style="text-align:center; font-weight:bold;">FECHA</td>
                    <td style="text-align:center; font-weight:bold;">CALIF.</td>
                    <td style="text-align:center; font-weight:bold;">FECHA</td>
                    <td style="text-align:center; font-weight:bold; border-right:1px solid #000 ">CALIF.</td>
                    </tr>


                @foreach ($materias as $materia)

                    @php
                        $calificacion = \DB::table('calificaciones')
                            ->where('alumno_id', $alumno->id)
                            ->where('asignacion_materia_id', $materia->asignacion_materia_id)
                            ->first();
                    @endphp
                    <tr>
                        <td style="text-align: center; height: 10px; padding:0px">{{ $materia->clave }}</td>
                        <td style="text-transform:uppercase; height: 10px; line-height:9px; font-size:11px; padding-left:5px; margin:0">{{ $materia->nombre }}</td>
                        <td style="text-align:center; padding:0px">
                            {{ $calificacion ? $calificacion->calificacion : '' }}
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
    $promedio = $cuenta > 0 ? round($suma / $cuenta, 1) : '';
@endphp

                @endforeach

    </table>
    @endforeach

     <table class="tabla-contenedor">
        <tr>
            <td class="celda firma-rector" style="border: 1px solid #000">
                <div class="linea">RECTOR(A)</div>
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
