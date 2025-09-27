<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Acta de Resultados | Gen: {{ $generacion->generacion }}</title>
</head>
<style>

      @page { margin:10px 45px 0px 45px; }

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
        width: 70%;
        margin-left: -100px;
    }

    .texto_encabezado{
        margin-top: 25px;
    }
    .texto_encabezado p{
        margin: 0px 0;
        padding: 0;
        text-align: center;
        font-family: 'calibri';
        font-size: 17px;
    }

    /* .datos{
        width: 100%;
        border: 3px double black;
        padding: 2px 2px 2px 2px;
        margin-top: 10px;
    }

    .datos p{
        text-transform: uppercase;
        font-family: 'calibri';
        margin: 0px 0;
        padding: 0;
        font-size: 16px;
    } */

    table.datos {
        width: 100%;
        /* border-collapse: collapse; */
        font-family: 'calibri';
        border-spacing: 0;
        border-collapse: collapse;
        border: 1px double #000; /* Borde doble, ajusta el grosor si lo quieres más delgado o más grueso */

    }
    table.datos td {
            font-family: 'calibri';
            font-size: 16px;
            text-transform: uppercase;
            /* vertical-align: middle; */
            text-align: left;
            padding-left: 6px;
            border: none;
            /* padding: 0 */
        }

        table.datos tr {
            padding: 0
        }
        table.datos td:nth-child(2),
        table.datos td:nth-child(3) {
            text-align: center;
        }

    </style>
<body>

@foreach ($materias as $index => $materia)
            <div class="encabezado">
         <img class="img_encabezado" src="{{ public_path('storage/encabezado.png') }}" alt="Encabezado">
    </div>

    <div class="texto_encabezado">
         <p><b>SISTEMA EDUCATIVO ESTATAL</b></p>
        <p>ACREDITACIÓN Y CERTIFICACIÓN DE ESTUDIOS</p>
         <p><b>ACTA DE RESULTADOS DE EVALUACIÓN</b></p>
    </div>

        @php
               $grupo = \App\Models\Grupo::where('licenciatura_id', $licenciatura->id)
                    ->where('cuatrimestre_id',$materia->cuatrimestre_id)
                    ->first();

        @endphp


         <table class="datos">
            <tr>
                <td colspan="3">NOMBRE DEL PLANTEL: <u><b>{{$escuela->nombre  }}</b></u></td>
            </tr>
            <tr>
                <td colspan="3">CLAVE DEL CENTRO DE TRABAJO: <u><b>{{ $escuela->CCT }}</b></u></td>
            </tr>
            <tr>
                <td colspan="3">LICENCIATURA EN: <u><b>{{ $licenciatura->nombre }}</b></u></td>
            </tr>
            <tr>
                <td colspan="3">ASIGNATURA: <u><b>{{ $materia->nombre }}</b></u></td>
            </tr>
            <tr>
                <td colspan="3">CLAVE: <u><b>{{ $materia->clave }}</b></u></td>
            </tr>
            <tr>
                <td style="width:45%;">ACREDITACIÓN: _________________</td>
                <td style="width:25%;">GRUPO: <u><b>{{ $grupo->grupo }}</b></u></td>
                <td style="width:30%;">CUATRIMESTRE: <u><b>{{ $materia->cuatrimestre_id }}</b></u></td>
            </tr>
            <tr>
                <td>REGULARIZACIÓN: _________________</td>
                <td>TURNO: <u><b>MATUTINO</b></u></td>
                <td>TOTAL DE ALUMNOS <u><b>{{ $alumnos->count() }}</b></u></td>
            </tr>
        </table>






    <table style="width:100%; border-collapse: collapse; font-family: calibri; font-size: 14px; margin-top: 20px;">
        <tr>
            <td rowspan="2" style="width:6%; border: 1px solid black; text-align: center; font-weight: bold;">NO.<br>PROG.</td>
            <td rowspan="2" style="width:14%; border: 1px solid black; text-align: center; font-weight: bold;">NO. DE MATRICULA</td>
            <td rowspan="2" style="width:36%; border: 1px solid black; text-align: center; font-weight: bold;">NOMBRE DEL ALUMNO</td>
            <td colspan="2" style="width:18%; border: 1px solid black; text-align: center; font-weight: bold;">CALIFICACIÓN</td>
            <td rowspan="2" style="width:13%; border: 1px solid black; text-align: center; font-weight: bold;">% DE<br>ASISTENCIA</td>
            <td rowspan="2" style="width:13%; border: 1px solid black; text-align: center; font-weight: bold;">ACREDITA-<br>CIÓN</td>
        </tr>
        <tr>
            <td style="border: 1px solid black; text-align: center; font-weight: bold;">NUMERO</td>
            <td style="border: 1px solid black; text-align: center; font-weight: bold;">LETRA</td>
        </tr>

        @foreach ($alumnos as $alumno)
            @php
                // Buscar la asignación de la materia para el alumno actual
                $asignacion = \App\Models\AsignacionMateria::where('materia_id', $materia->id) // Filtra por el id de la materia actual
                    ->where('licenciatura_id', $licenciatura->id) // Filtra por el id de la licenciatura actual
                    ->where('modalidad_id', $alumno->modalidad_id) // Filtra por la modalidad del alumno
                    ->where('cuatrimestre_id', $materia->cuatrimestre_id) // Filtra por el cuatrimestre de la materia
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

                // Obtén el nombre del profesor, si existe la asignación
            $profesor = $asignacion && $asignacion->profesor
                ? $asignacion->profesor->nombre . ' ' . $asignacion->profesor->apellido_paterno . ' ' . $asignacion->profesor->apellido_materno
                : '---';

                // Obten la letra del numero de la calificacion, por ejemplo si es 10 = "DIEZ", 9 = "NUEVE"

                $letraCalificacion = '';
                switch ($calificacion) {
                    case 10:
                        $letraCalificacion = 'DIEZ';
                        break;
                    case 9:
                        $letraCalificacion = 'NUEVE';
                        break;
                    case 8:
                        $letraCalificacion = 'OCHO';
                        break;
                    case 7:
                        $letraCalificacion = 'SIETE';
                        break;
                    case 6:
                        $letraCalificacion = 'SEIS';
                        break;
                    case 5:
                        $letraCalificacion = 'CINCO';
                        break;
                    case 4:
                        $letraCalificacion = 'CUATRO';
                        break;
                    case 3:
                        $letraCalificacion = 'TRES';
                        break;
                    case 2:
                        $letraCalificacion = 'DOS';
                        break;
                    case 1:
                        $letraCalificacion = 'UNO';
                        break;
                    case 'NP':
                        $letraCalificacion = 'NP';
                        break;
                        default:
                        $letraCalificacion = '----';
                }



            @endphp
            <tr>
                <td style="border: 1px solid black; text-align: center; padding:0">{{ $loop->iteration }}</td>
                <td style="border: 1px solid black; text-align: center; padding:0">{{ $alumno->matricula }}</td>
                <td style="border: 1px solid black; text-align: left; padding: 0 0 0 5px;">
                    {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno}} {{ $alumno->nombre }}
                </td>
                <td style="border: 1px solid black; text-align: center; padding:0">
                    @if($calificacion === '')
                       ----
                    @else
                        {{ $calificacion }}
                    @endif
                </td>
                <td style="border: 1px solid black; text-align: center; padding:0">
                    @if($letraCalificacion === '')
                        ----
                    @else
                        {{ $letraCalificacion }}
                    @endif
                </td>
                <td style="border: 1px solid black; text-align: center; padding:0">100%</td>
                <td style="border: 1px solid black; text-align: center; padding:0">
                    SI
                </td>
            </tr>
        @endforeach

    </table>


<div style="width:100%; margin-top: 100px;">
    <table style="width:100%; border: none; font-size:15px">
        <tr>
            <td style="width:45%; text-align: center; border: none; text-transform: uppercase;">
                {{ $rector->nombre }} {{ $rector->apellido_paterno }} {{ $rector->apellido_materno }}
                <div style="border-top:2px solid #000; width:80%; margin: 0 auto 2px auto;"></div>
                <span style="font-weight:bold; line-height:10px">NOMBRE Y FIRMA<br>R E C T O R (A)</span>
            </td>
            <td style="width:10%; border:none;"></td>
            <td style="width:45%; text-align: center; border: none;  text-transform: uppercase;">
                {{ $profesor ?? "----" }}
                <div style="border-top:2px solid #000; width:80%; margin: 0 auto 2px auto;"></div>
                <span style="font-weight:bold; line-height:10px">NOMBRE Y FIRMA DEL PROFESOR(A)<br>&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="height: 20px; border:none;"></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center; border:none;   text-transform: uppercase;">
                   {{ $directora->nombre }} {{ $directora->apellido_paterno }} {{ $directora->apellido_materno }}
                <div style="border-top:2px solid #000; width:25%; margin: 0 auto 2px auto;"></div>
                <span style="font-weight:bold; line-height:10px">NOMBRE Y FIRMA<br>CONTROL ESCOLAR</span>
            </td>
        </tr>
    </table>
</div>





        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach



</body>
</html>
