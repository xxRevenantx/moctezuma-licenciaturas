<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>Lista de Evaluación | Gen: {{$generacion->generacion}} </title>
</head>
<style>

    @page { margin:10px 45px 20px 45px; }


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

        font-family: 'calibri',
        margin: auto;
        font-size: 13px;
    }



    .fecha {
        font-size: 16px;
        font-weight: bold;
    }


    table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }

    th, td {
        text-align: center;
        font-size: 15px;
        padding-left: 5px
    }
    td{
         border: 1px solid #000;
    }

    th {
         border: 1px solid #2d2d2d;
        background: #638acd;
        font-weight: bold;
        text-align: center;
        color: white;
    }


    p.nota {
        font-size: 14px;
        color: red;
    }

    footer {
        position: absolute;
        bottom: 0;
        left: 0;
        text-align: center;
        font-size: 12px;
        line-height: 12px;
        width: 100%;

        border-top: 1px solid #4a5568;
        border-bottom: 1px solid #4a5568;

    }
    footer p{
        margin: 0;
        padding: 0;
    }
    .sm{
        font-size: 8px;
    }


     .titulo {
            font-weight: bold;
            color: #4a5568; /* Color similar al de la imagen */
            text-align: center;
            font-size: 27px;
            margin-top: 50px;
            padding: 0px 0;
            border-top: 2px solid #4a5568;
            border-bottom: 2px solid #4a5568;
            display: inline-block;
        }


    p.subtitulo{
        text-align: left;
        font-size: 16px;
         padding: 3px 0;
        margin: 0;
        font-weight: bold;
    }

    p.licenciatura{
        text-transform: uppercase;
        font-size: 16px;
        line-height: 7px;

    }



    img.img1 {
        position: absolute;
        top: 10px;
        left: 10px;
    }

    img.img2 {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .watermark {
        position: fixed;
        top: 70%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        z-index: -1;
        opacity: 0.1;
        text-align: center;
    }
    p.cuatrimestre{
        text-transform: uppercase;
        font-size: 18px;
        text-align: center;
    }

</style>
<body>
        <div class="watermark">
            <img src="{{ public_path('storage/letra.png') }}" alt="Watermark">
        </div>
       <div style="text-align: center;">
             <img class="img1" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo" height="100px" width="100">
            <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA</h1>

            @if ($materia->licenciatura->imagen)
                <img class="img2" src="{{ public_path('storage/licenciaturas/'.$materia->licenciatura->imagen) }}" alt="Logo Licenciatura"  height="100px" width="100">
            @else
               <img class="img2" src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo"  height="100px" width="100">

            @endif


        </div>
        <p style="font-size: 24px; text-align:center; font-weight:bold; margin-top:-30px; line-height:20px">CONCENTRADO DE CALIFICACIONES <br> CUATRIMESTRAL</p>
        <p style="font-size: 20px; color:#5b5b5b; text-align:center; font-weight:bold; margin-top:-25px; line-height:20px">CICLO ESCOLAR: {{$ciclo_escolar->ciclo_escolar}}</p>

        {{-- AQUI VA LA TABLA --}}



         <table style="width: 100%; border-collapse: collapse;  font-size: 14px; margin-bottom: 20px;" border="1">
        <thead>
            <tr style="background-color: #f2f2f2; text-align: center;">
                <th colspan="3">CONCENTRADO DE CALIFICACIONES</th>
                <th colspan="5" style="text-transform: uppercase">FECHAS: {{ \Carbon\Carbon::parse($periodos->inicio_periodo)->translatedFormat('d \d\e F \d\e\l Y') }} al
                    {{ \Carbon\Carbon::parse($periodos->termino_periodo)->translatedFormat('d \d\e F \d\e\l Y') }}
                    </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>LICENCIATURA:</strong></td>
                <td colspan="2" style="text-transform: uppercase">{{ $materia->licenciatura->nombre }}</td>
                <td><strong>GRUPO:</strong></td>
                <td>{{ $grupo->grupo }}</td>
                <td><strong>CUATRIMESTRE:</strong></td>
                <td colspan="2">{{ $materia->cuatrimestre->cuatrimestre }}°</td>
            </tr>
            <tr>
                <td><strong>MODALIDAD:</strong></td>
                <td>{{ $materia->modalidad->nombre }}</td>
                <td><strong>PERIODO:</strong></td>
                <td colspan="3">{{ $meses[$periodo[0]] }}/{{ $meses[$periodo[1]] }}</td>
                <td><strong>GENERACIÓN</strong></td>
                <td>{{ $generacion->generacion }}</td>
            </tr>
            <tr>
                <td><strong>PROFESOR:</strong></td>
                <td colspan="3">{{ $materia->profesor->nombre }} {{ $materia->profesor->apellido_paterno }} {{ $materia->profesor->apellido_materno }}</td>
                <td><strong>ASIGNATURA:</strong></td>
                <td colspan="3" style="text-transform: uppercase">{{ $materia->materia->nombre }}</td>
            </tr>
        </tbody>
    </table>


      <table>
           <thead>
                <tr>
                    <th style="text-align: center" rowspan="2" >No.</th>
                    <th style="text-align: center" rowspan="2" >NOMBRE COMPLETO</th>
                    <th style="text-align: center" colspan="7" >EVALUACIÓN</th>
                </tr>
                <tr>
                    <th style="text-align: center" >ASISTENCIAS</th>
                    <th style="text-align: center" >PARTICIPACIONES</th>
                    <th style="text-align: center" >TAREAS</th>
                    <th style="text-align: center" >EVIDENCIAS</th>
                    <th style="text-align: center" >EXAMEN</th>
                    <th style="text-align: center" >CAL.FINAL</th>
                    <th style="text-align: center" >OBSERVACIONES</th>
                </tr>
            </thead>


            <tbody>

                @foreach($alumnos as $key =>  $alumno)
                    <tr>
                        <td style="text-align: center;">{{ $key+1 }}</td>
                        <td  style="text-align: left;">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach

            </tbody>
        </table>

        <p style="font-size: 14px"><b>Nota:</b> La calificación final sólo será redondeada si es mayor o igual a (.6)</p>

        <p style="text-align:center; font-size: 14px; font-weight: bold; margin-top: 100px;">
            ___________________________________________ <br>
            NOMBRE Y FIRMA DEL DOCENTE
        </p>





    <footer>
        <p>{{$escuela->nombre}} C.C.T. {{$escuela->CCT}}. C. {{$escuela->calle}} No. {{$escuela->no_exterior}}, Col. {{$escuela->colonia}}, C.P. {{$escuela->codigo_postal}}, Cd. {{$escuela->ciudad}}, {{$escuela->estado}}.</p>
        <p>Fecha de expedición: {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}</p>
    </footer>

</body>
</html>
