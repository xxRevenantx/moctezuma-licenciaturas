<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>CALIFICACIONES GENERALES {{ $cuatrimestres }}° CUATRIMESTRE </title>
</head>
<style>

    @page { margin:10px 55px 20px 55px; }


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
        text-align: center;
        font-size: 15px;
    }

    th, td {

        padding: 8px;
        text-align: center
    }
    td{
         border: 1px solid #000000;
        text-align: center;
        text-transform: uppercase;
    }

    th {
         border: 1px solid #2d2d2d;
        background: #e7e7e7;
        font-weight: bold;
        text-align: center;
        color: rgb(0, 0, 0);
        text-transform: uppercase;
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
            font-size: 24px;
            margin-top: 20px;
            padding: 5px 0;
            border-top: 2px solid #4a5568;
            border-bottom: 2px solid #4a5568;
            display: inline-block;
        }


    p.subtitulo{
        text-align: center;
        font-size: 16px;
         padding: 3px 0;
        margin: 0;
        font-weight: bold;
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

</style>
<body>
        <div class="watermark">
            <img src="{{ public_path('storage/letra.png') }}" alt="Watermark">
        </div>
       <div style="text-align: center;">
             <img class="img1" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo" height="100px" width="100">
            <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA</h1>
            <h2 style="margin-top: -20px; font-size:23px; color:#4a5568">GENERACIÓN {{ $generacion->generacion }} </h2>
            <h2 style="margin-top: -20px; font-size:23px; color:#4a5568; text-transform:uppercase">LIC. {{ $licenciatura->nombre }} </h2>

            @if(!empty($licenciatura->imagen) && file_exists(public_path('storage/licenciaturas/'.$licenciatura->imagen)))
                <img class="img2" src="{{ public_path('storage/licenciaturas/'.$licenciatura->imagen) }}" alt="Logo Licenciatura" height="100px" width="100">

            @else
                <img class="img2" src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo Licenciatura" height="100px" width="100">


            @endif

        </div>
        <p style="font-size: 24px" class="subtitulo">CALIFICACIONES DEL {{ $cuatrimestres }}° CUATRIMESTRE</p>

                        @php
                    $primerAlumno = $alumnos->first();
                @endphp

                    <table>
                    <thead>
                                <tr>
                                    <th colspan="5">DATOS DEL ALUMNO</th>
                                    <th colspan="{{ $totalMaterias+1 }}">MATERIAS</th>
                                </tr>
                                <tr>
                                    <th style="font-size: 11px">#</th>
                                    <th style="font-size: 11px">MATRÍCULA</th>
                                    <th style="font-size: 11px">NOMBRE(S)</th>
                                    <th style="font-size: 11px">APELLIDO PATERNO</th>
                                    <th style="font-size: 11px">APELLIDO MATERNO</th>
                                    @if(!empty($primerAlumno['materias']))
                                        @foreach ($primerAlumno['materias'] as $clave => $info)
                                            <th style="font-size: 11px">{{ $info['nombre'] }}</th>
                                        @endforeach
                                    @else
                                        <th style="font-size: 11px">NO HAY CALIFICACIONES</th>
                                    @endif
                                    <th style="font-size: 11px">Promedio</th>
                                </tr>
                            </thead>
                   <tbody>
                                @foreach ($alumnos as $index => $alumno)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $alumno['matricula'] }}</td>
                                        <td>{{ $alumno['nombre'] }}</td>
                                        <td>{{ $alumno['apellido_paterno'] }}</td>
                                        <td>{{ $alumno['apellido_materno'] }}</td>
                                        @foreach ($alumno['materias'] as $info)
                                            <td style="font-size:15px">{{ $info['calificacion'] }}</td>
                                        @endforeach
                                        <td><strong>{{ floor($alumno['promedio'] * 10) / 10 }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>

                </table>





     <footer>
        <p>{{$escuela->nombre}} C. {{$escuela->calle}} No. {{$escuela->no_exterior}}, Col. {{$escuela->colonia}}, C.P. {{$escuela->codigo_postal}}, Cd. {{$escuela->ciudad}}, {{$escuela->estado}}.</p>
        <p>Fecha de expedición: {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}</p>
    </footer>

</body>
</html>
