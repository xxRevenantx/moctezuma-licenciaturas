<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Registro de Escolaridad</title>
</head>
<style>

    @page { margin:10px 45px 20px 45px; }

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


    table.tblPrincipal {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-top: 10px;
    }


      table.tblPrincipal tr td {
            font-family: 'calibri';
            font-size: 14px;
            text-transform: uppercase;
            text-align: left;
            padding-left: 6px;
            border: 1px solid #000;
            /* padding: 0 */
        }


       .rotate {
        /* writing-mode: vertical-rl; */
        transform: rotate(-90deg);

        }



</style>
<body>




    @foreach ( $periodos as $periodo )

    <div style="width:100%; margin-bottom:20px; margin-top:20px">
    <table width="100%" style="border-collapse: collapse;">
        <tr>
            <!-- Logo SEP -->
            <td style="width: 100px; text-align: left;">
                <img src="{{ public_path('storage/sep.jpg') }}" alt="SEP" style="height: 70px;">
            </td>

            <!-- Texto centrado -->
            <td style="text-align: center;">
                <p style="color: #000000; font-weight: bold; font-size: 15px; margin: 0; line-height:14px">
                    GOBIERNO DEL ESTADO LIBRE Y SOBERANO DE GUERRERO <br>
                    SECRETARIA DE EDUCACIÓN GUERRERO<br>
                    REGISTRO DE ESCOLARIDAD
                </p>

            </td>

            <!-- Logo Moctezuma -->
            <td style="width: 200px; text-align: right;">
                <img src="{{ public_path('storage/moctezuma.png') }}" alt="moctezuma" style="height: 60px;">
            </td>
        </tr>
    </table>
</div>

<!-- Estructura para DomPDF -->
<table width="100%" style="border-collapse: collapse; margin-bottom: 10px; text-transform: uppercase;">
    <tr>
        <!-- Información de Plantel -->
        <td style="vertical-align: top;">
            <div style="margin-bottom: 5px;">
                <span style="font-size: 16px;">NOMBRE DEL PLANTEL:</span>
                <span style="font-weight: bold; text-decoration: underline; font-size: 16px;">
                  {{ $escuela->nombre }}
                </span>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span style="font-size: 16px;">CLAVE DEL CENTRO DE TRABAJO:</span>
                <span style="font-weight: bold; text-decoration: underline; font-size: 16px;">
                  {{ $escuela->CCT }}
                </span>
            </div>

            <div>

                <span style="font-size: 16px;">DOMICILIO:</span>
                <span style="font-weight: bold; text-decoration: underline; font-size: 16px;">
                     {{ $escuela->calle }} No. {{ $escuela->no_exterior }}
                </span>

                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span style="font-weight: bold; text-decoration: underline; font-size: 16px;">
                     {{ $escuela->colonia }}
                </span>

                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span style="font-weight: bold; text-decoration: underline; font-size: 16px;">
                     {{ $escuela->municipio }}
                </span>

                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span style="font-weight: bold; text-decoration: underline; font-size: 16px;">
                     {{ $escuela->estado }}
                </span>

            </div>

            <div>
                <span style="font-size: 12px; margin-left:155px">CALLE Y NÚMERO</span>

                 <span style="font-size: 12px; margin-left:210px">COLONIA</span>
                 <span style="font-size: 12px; margin-left:140px">MUNICIPIO</span>
                 <span style="font-size: 12px; margin-left:120px">ENTIDAD FEDERATIVA</span>

            </div>
        </td>

        <!-- Tabla Estadística -->
        <td style="vertical-align: top; width: 240px; font-family: calibri">
            <table border="1" cellpadding="3" style="border-collapse: collapse; font-size: 13px; width: 100%; margin-left: 0px;">
                <tr>
                    <th colspan="4" style="text-align: center;">DATOS ESTADÍSTICOS</th>
                </tr>
                <tr>
                    <th style="text-align: center;">CONCEPTO</th>
                    <th style="text-align: center;">H</th>
                    <th style="text-align: center;">M</th>
                    <th style="text-align: center;">TOTAL</th>
                </tr>
                <tr>
                    <td style="text-align: center;">INSCRITOS</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Segunda fila de datos (números de acuerdo, ciclo escolar, etc.) -->
<table width="100%" style="border-collapse: collapse;">
    <tr>
        <td style="font-size: 16px;">
            NUMERO DE ACUERDO: <span style="text-decoration: underline; font-weight: bold; ">{{ $licenciatura->RVOE }}</span>
            &nbsp;&nbsp;
            CICLO ESCOLAR: <span style="text-decoration: underline; font-weight: bold; "> {{ $periodo->ciclo_escolar }}</span>
            &nbsp;&nbsp;
            GENERACIÓN: <span style="text-decoration: underline; font-weight: bold; "> {{ $periodo->generacion->generacion }}</span>
        </td>
    </tr>
    <tr>
        <td style="font-size: 15px; padding-top: 8px; text-transform: uppercase;">
            LICENCIATURA EN: <span style="text-decoration: underline; font-weight: bold; "> {{ $licenciatura->nombre }}</span>
            &nbsp;&nbsp;
            MODALIDAD: <span style="font-weight: bold; text-decoration: underline;">ESCOLARIZADA</span>
            &nbsp;&nbsp;
            CUATRIMESTRE: <span style="text-decoration: underline; font-weight: bold; ">{{ $periodo->cuatrimestre_id }}°</span>
            &nbsp;&nbsp;
            GRUPO: <span style="text-decoration: underline; font-weight: bold; ">_______</span>
            &nbsp;&nbsp;
            TURNO: <span style="text-decoration: underline; font-weight: bold; ">MATUTINO</span>
        </td>
    </tr>
</table>


       <table class="tblPrincipal">
                <tr>


                <td rowspan="2" style="font-size:10px; width:100px  font-weight:normal;  padding:0px; margin:0px; height:0px">
                    <div style="white-space:nowrap;  width:10px;" class="rotate"><b>NÚMERO DE REGISTRO</b></div>
                </td>


                <td colspan="2"><b>ANTECEDENTES</b></td>
                <td style="width:10px" rowspan="2"><div style="text-align:center;" class="rotate" ><b>NÚMERO DE MATRÍCULA</b></div></td>
                <td colspan="3" style=" text-align:center;"><b>NOMBRE DEL ALUMNO</b></td>
                <td colspan="1" style=" text-align:center;"></td>
                <td colspan="" style=" text-align:center;">RESULTADO FINAL</td>
                <td colspan="2" style=" text-align:center; font-size:8px">REGULARIZACIONES</td>
                <td  style=" text-align:center"></td>
                <td  style=" text-align:center"></td>
                <td  style=" text-align:center"></td>
                </tr>
            <tr>
            <th style="width:50px"><div style=" width:50px; text-align:center"  class="rotate"><b>ASIGNATURAS NO ACREDITADAS</b></div></th>
            <th style="width:50px"><div style=" width:50px;"  class="rotate"><b>SITUACIÓN ESCOLAR</b></div></th>
            <th style="width:100px;"><b>PRIMER APELLIDO</b></th>
            <th style="width:100px;"><b>SEGUNDO APELLIDO</b></th>
            <th style="width:100px;;"><b>NOMBRE(S)</b></th>
            <th style="font-size:10px; font-weight:normal; width:10px;padding:0px; margin:0px; height:0px"><div style=" width:40px; white-space: nowrap;" class="rotate"><b>SEXO: H o M</b></div></th>';


            @php
                   // Buscar la asignación de la materia para el alumno actual
                $asignacion = \App\Models\AsignacionMateria::where('licenciatura_id', $licenciatura->id)
                ->where('cuatrimestre_id', $periodo->cuatrimestre_id)
                ->get();

            @endphp

                @foreach ($asignacion as $mat)
                        <th style="font-size:10px; font-weight:normal;  padding:0px; margin:0px; height:0px; ">
                                            <div style="white-space: wrap; font-size:10px; line-height:10px;font-weight:normal;padding:0 ; text-transform:uppercase" class="rotate">

                                                            {{ $mat->materia->nombre }}

                                            </div>
                        </th>
            @endforeach


            <th style="width:40px"></th>
            <th style="width:40px"></th>
            <th style="width:10px" class="alto"><div style=" width:50px;" class="rotate"><b>ASIGNATURAS ACREDITADAS</b></div></th>
            <th style="width:10px" class="alto"><div style=" width:50px;" class="rotate"><b>ASIGNATURAS NO ACREDITADAS</b></div></th>
            <th style="width:10px" class="alto"><div style=" width:50px;" class="rotate"><b>SITUACIÓN ESCOLAR</b></div></th>
            </tr>

            @foreach ($alumnos as $alumno )

            @php
                   // Buscar la asignación de la materia para el alumno actual
                $asignacion = \App\Models\AsignacionMateria::where('licenciatura_id', $licenciatura->id) // Filtra por el id de la licenciatura actual
                    ->where('modalidad_id', $alumno->modalidad_id) // Filtra por la modalidad del alumno
                    ->where('cuatrimestre_id', $alumno->cuatrimestre_id) // Filtra por el cuatrimestre de la materia
                    ->first(); // Obtiene el primer resultado

                // Inicializa la variable de calificación en null
                $calificacion = null;
                // Si se encontró la asignación de materia
                if($asignacion){
                    // Busca la calificación del alumno para esa asignación de materia
                    $calificacionObj = $alumno->calificaciones
                        ->where('asignacion_materia_id', $asignacion->id) // Filtra por el id de la asignación de materia
                        ->first(); // Obtiene el primer resultado
                    // Si existe la calificación, la asigna, si no, deja vacío
                    $calificacion = $calificacionObj ? $calificacionObj->calificacion : '';
                }

            @endphp





                <tr>
                     <td style="font-size:11px">{{$loop->iteration}}</td>
                  <td style="font-size:11px">0</td>
                  <td style="font-size:11px">R</td>
                  <td style="font-size:11px">{{$alumno->matricula}}</td>
                  <td style="font-size:11px">{{$alumno->apellido_paterno}}</td>
                  <td style="font-size:11px">{{$alumno->apellido_materno}}</td>
                  <td style="font-size:11px">{{$alumno->nombre}}</td>
                  <td style="font-size:11px" class="datos">{{$alumno->sexo}}</td>

                <td style="font-size:11px" class="calificacion">0</td>
                <td style="font-size:11px" class="calificacion">0</td>
                <td style="font-size:11px" class="calificacion">0</td>
                <td style="font-size:11px" class="calificacion">0</td>
                <td style="font-size:11px" class="calificacion">0</td>



                  <td></td>
                  <td></td>
                  <td  style="font-size:11px" ></td>
                  <td style="font-size:11px">0</td>
                  <td style="font-size:11px">R</td>
                </tr>

    @endforeach

            </table>



        @if (!$loop->last)
            <div class="page-break"></div>
        @endif

    @endforeach



</body>
</html>
