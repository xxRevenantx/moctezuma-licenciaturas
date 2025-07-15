<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>BOLETA DEL {{$cuatrimestre->cuatrimestre}}° CUATRIMESTRE </title>
</head>
<style>

    @page { margin:10px 65px 20px 65px; }


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
            margin-top: 50px;
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
            <h2 style="margin-top: -20px; font-size:23px; color:#4a5568">CICLO ESCOLAR {{ $ciclo_escolar->ciclo_escolar }} </h2>
            <img class="img2" src="{{ public_path('storage/licenciaturas/'.$licenciatura->imagen) }}" alt="Logo Licenciatura"  height="100px" width="100">
        </div>
        <p style="font-size: 24px" class="subtitulo">BOLETA DE CALIFICACIONES</p>

        {{-- <p>GRADO: {{$grade->grado}}° GRUPO: {{ $group != null ? $group->grupo : "TODOS" }} </p> --}}

        <table>
            <tr style="background: #eeeeee">
                <td style="padding: 0">{{$licenciatura->nombre}}</td>
                <td style="padding: 0">{{$escuela->CCT}}</td>
                <td style="padding: 0">{{$cuatrimestre->cuatrimestre}}° CUATRIMESTRE</td>
                <td style="padding: 0">{{$periodo->mes->meses_corto}}</td>
                <td style="padding: 0">{{$periodo->generacion->generacion}}</td>
            </tr>
            <tr style="font-size: 10px;">
                <td style="padding: 0">LICENCIATURA</td>
                <td style="padding: 0">C.C.T.</td>
                <td style="padding: 0">CUATRIMESTRE</td>
                <td style="padding: 0">PERIODO ESC.</td>
                <td style="padding: 0">GENERACIÓN</td>
            </tr>
            <tr>
                <td colspan="3" style="background: #eeeeee; padding: 0; font-size:12px">{{$escuela->calle}} #{{ $escuela->no_exterior }}. COL.{{ $escuela->colonia }}. CD.{{ $escuela->ciudad }}</td>
                <td style="padding: 0">{{$escuela->municipio}}</td>
                <td style="padding: 0">{{$escuela->estado}}</td>
            </tr>
            <tr style="font-size: 10px;">
                <td colspan="3" style="padding: 0">DIRECCIÓN</td>
                <td style="padding: 0">MUNICIPIO</td>
                <td style="padding: 0">ESTADO</td>
            </tr>

            <tr style="background: #eeeeee">
                <td colspan="2" style="padding: 0; border-right: none;">{{ $inscripcion->apellido_paterno }}</td>
                <td  style="padding: 0">{{ $inscripcion->apellido_materno }}</td>
                <td colspan="2" style="padding: 0"> {{$inscripcion->nombre}}</td>
            </tr>
            <tr style="font-size: 10px;">
                <td colspan="2"  style="padding: 0">APELLIDO PATERNO</td>
                <td  style="padding: 0">APELLIDO MATERNO</td>
                <td colspan="2" style="padding: 0">NOMBRE(S)</td>
            </tr>
        </table>

        <table style="margin-top: 50px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>ASIGNATURA</th>
                    <th>CALIFICACIÓN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($calificaciones as $calificacion)

                    <tr>
                        <td>{{ $calificacion->asignacionMateria->materia->nombre }}</td>
                        <td>{{ $calificacion->calificacion }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-weight: bold; font-size:17px; text-align:right">CALIFICACIÓN CUATRIMESTRAL</td>
                    <td style="font-weight: bold; font-size:17px;" >
                        @if ($calificaciones->count() > 0)
                            {{ number_format($calificaciones->sum('calificacion') / $calificaciones->count(), 1) }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </tbody>
            </tbody>
        </table>


     <footer>
        <p>{{$escuela->nombre}} C. {{$escuela->calle}} No. {{$escuela->no_exterior}}, Col. {{$escuela->colonia}}, C.P. {{$escuela->codigo_postal}}, Cd. {{$escuela->ciudad}}, {{$escuela->estado}}.</p>
        <p>Fecha de expedición: {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}</p>
    </footer>

</body>
</html>
