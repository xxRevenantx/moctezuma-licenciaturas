<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>Horario</title>
</head>
<style>

      @page { margin:10px 45px 20px 45px; }
        body {
        /* width: 21.59cm;
        height: 27.94cm; */
        /* padding: 20px; */
        font-family: 'figtree', sans-serif;
        margin: auto;
        font-size: 15px;
        text-transform: uppercase
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

        padding: 8px;
        text-align: left;
    }
    td{
         border: 1px solid #8a8a8a;
        text-align: left;
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
            padding: 10px 0;
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

    .fechaImpresion{
        position: absolute;
        bottom: 40px;
        left: 250px;
        text-align: center;
        font-size: 12px;
        width: 100%;
    }

</style>
<body>
        <div class="watermark">
            <img src="{{ public_path('storage/letra.png') }}" alt="Watermark">
        </div>
         <div style="text-align: center;">
             <img class="img1" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo" height="100px" width="100">
            <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA</h1>
            <img class="img2" src="{{ public_path('storage/licenciaturas/'.$licenciatura_nombre->imagen) }}" alt="Logo Licenciatura"  height="100px" width="100">
        </div>
        <p style="font-size: 25px" class="subtitulo">HORARIO DE CLASES</p>
        <p style="text-transform: uppercase" class="subtitulo">LICENCIATURA EN {{$licenciatura_nombre->nombre}}</p>
        <p class="subtitulo">GENERACIÓN: {{$generacion->generacion}}</p>

        <div style="background: #88AC2E; color: white; text-align: center; padding: 5px; font-weight: bold; font-size: 16px; margin-top: 10px;">
            {{$cuatrimestre}}° CUATRIMESTRE | SEMIESCOLARIZADA
        </div>

      <table class="min-w-full text-xs text-left">
    <thead class="bg-gray-200 dark:bg-gray-700">
        <tr>
            <th class="px-2 py-1" style="width:150px">Hora</th>
            <th class="px-2 py-1">Materia</th>
            <th class="px-2 py-1">Clave</th>
        </tr>
    </thead>
    <tbody>
        @php
            $inserted = false;
        @endphp
        @foreach ($horario as $h)
            <tr class="border-b dark:border-gray-600">
                <td class="px-2 py-1">{{ $h->hora }}</td>
                <td class="px-2 py-1">{{ $h->asignacionMateria->materia->nombre }}</td>
                <td class="px-2 py-1">{{ $h->asignacionMateria->materia->clave }}</td>
            </tr>
            @if (!$inserted && $h->hora == '9:00am-10:00am')
                <tr class="border-b dark:border-gray-600">
                    <td class="px-2 py-1" style="background: #d4d4d4">10:00am-10:30am</td>
                    <td class="px-2 py-1" style="background: #d4d4d4; text-align:center; letter-spacing: 60px">RECESO</td>
                    <td class="px-2 py-1" style="background: #d4d4d4">-----</td>
                </tr>
                @php $inserted = true; @endphp
            @endif
        @endforeach
    </tbody>
</table>

{{-- PROFESORES --}}


<h1 style="text-align: center; font-size:20px; margin-top:30px">ASIGNACIÓN DE PROFESORES</h1>

<hr>

<table class="min-w-full text-xs text-left mb-4" style="margin-top: 20px; text-transform: uppercase; font-size:14px">
    <thead >
        <tr>
            <th class="px-2 py-1" style="background: #d4d4d4; color: black">#</th>
            <th class="px-2 py-1" style="background: #d4d4d4; color: black">Clave</th>
            <th class="px-2 py-1" style="background: #d4d4d4; color: black">Materia</th>
            <th class="px-2 py-1" style="background: #d4d4d4; color: black">Profesor</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($horario as $h)

            <tr class="border-b dark:border-gray-600">
                <td class="px-2 py-1">
                    {{ $loop->iteration }}
                </td>
                <td class="px-2 py-1">{{$h->asignacionMateria->materia->clave}}</td>
                <td class="px-2 py-1">
                    {{ $h->asignacionMateria->materia->nombre }}
                </td>
                <td class="px-2 py-1">
                    {{  $h->asignacionMateria->profesor->nombre }} {{  $h->asignacionMateria->profesor->apellido_paterno }} {{  $h->asignacionMateria->profesor->apellido_materno }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>






    <footer>
        <p>{{$escuela->nombre}} C.C.T. {{$escuela->CCT}}</p>
        <p>C. {{$escuela->calle}} No. {{$escuela->no_exterior}}, Col. {{$escuela->colonia}}, C.P. {{$escuela->codigo_postal}}, Cd. {{$escuela->ciudad}}, {{$escuela->estado}}.</p>
        <p>Tel. {{$escuela->telefono}}</p>
        <p style="font-weight: bold">Fecha de expedición: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </footer>

</body>
</html>
