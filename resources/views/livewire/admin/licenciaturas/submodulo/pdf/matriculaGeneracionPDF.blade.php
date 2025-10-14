<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>Lista de Alumnos Generales | Gen: {{$generacion->generacion}}</title>
</head>
<style>

      @page { margin:10px 45px 20px 45px; }
        body {
        /* width: 21.59cm;
        height: 27.94cm; */
        /* padding: 20px; */
        font-family: 'figtree', sans-serif;
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
        <p style="font-size: 19px" class="subtitulo">LISTA DE GRUPO</p>
        <p style="text-transform: uppercase" class="subtitulo">LICENCIATURA EN {{$licenciatura_nombre->nombre}}</p>
        <p class="subtitulo">GENERACIÓN: {{$generacion->generacion}}</p>
        {{-- <p>GRADO: {{$grade->grado}}° GRUPO: {{ $group != null ? $group->grupo : "TODOS" }} </p> --}}

        <table>
            <thead>
                <tr>
                    <th>#</th>

                    {{-- <th>MATRÍCULA</th> --}}
                    <th>NOMBRE(S)</th>
                    <th>APELLIDO PATERNO</th>
                    <th>APELLIDO MATERNO</th>
                    <th>CURP</th>
                    <th>MODALIDAD</th>
                    <th>MES DE INGRESO</th>
                    <th>OBSERVACIONES</th>
                </tr>
            </thead>
            <tbody>
                @foreach($matricula as $key =>  $student)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        {{-- <td>{{ $student->matricula }}</td> --}}
                        <td>{{ $student->nombre }}</td>
                        <td>{{ $student->apellido_paterno }}</td>
                        <td>{{ $student->apellido_materno }}</td>
                        <td>{{ $student->CURP }}</td>
                        <td>ESCOLARIZADA</td>
                        <td>SEPTIEMBRE</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>


        {{-- <table>
            <tr>
                <td>H</td>
                <td>M</td>
                <td>TOTAL</td>
            </tr>
            <tr>
                <td>10</td>
                <td>20</td>
                <td>30</td>
            </tr>
        </table> --}}


     <footer>
        <p>{{$escuela->nombre}} C.C.T. {{$escuela->CCT}}. C. {{$escuela->calle}} No. {{$escuela->no_exterior}}, Col. {{$escuela->colonia}}, C.P. {{$escuela->codigo_postal}}, Cd. {{$escuela->ciudad}}, {{$escuela->estado}}.</p>
        <p>Fecha de expedición: {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}</p>
    </footer>

</body>
</html>
