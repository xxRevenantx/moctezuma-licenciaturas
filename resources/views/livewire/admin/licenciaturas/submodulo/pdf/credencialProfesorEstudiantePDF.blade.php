<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CREDENCIAL DEL PROFESOR-ESTUDIANTE</title>

    <style>
        @page {
            margin: 30px 0px 0px 0px;
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
            font-family: 'calibri'
        }


        .contenedorCredenciales {
            width: 100%;
            /* background: #000; */
            margin: auto;
            padding: 0 0 0 70px;
        }

        .credenciales {
            border: 1px solid #000;
            width: 18cm;
            height: 5.7cm;
            margin: 5px auto;
            /* padding: 10px; */

        }

        .imagen {
            width: 50px;
            position: absolute;
            right: 90px;
            margin: 15px auto 0;

        }

        .titulo {
            font-size: 10px;
            color: #fff;
            margin-top: -164px;
            margin-left: 50px;
        }

        .info {
            font-size: 11px;
            font-family: 'calibri';
            margin-top: -150px;
            line-height: 10px;
            margin-left: 130px;
            width: 200px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="contenedorCredenciales">

        @foreach ($profesores as $index => $profesor)
            {{-- <img class="imagen" src="{{  public_path('storage/licenciaturas/'.$profesor->licenciatura->imagen) }}" alt=""> --}}
            <img class="credenciales" src="{{ public_path('storage/credencial-frontal.jpg') }}">
            {{-- <div class="credenciales" style="background-image: url('{{ public_path('storage/credencial-frontal.png') }}')"> --}}

            <div class="info">
                <h1 class="titulo">CREDENCIAL DEL ESTUDIANTE</h1>
                <b>Nombre: </b>{{ $profesor->nombre }} {{ $profesor->apellido_paterno }}
                {{ $profesor->apellido_materno }}<br>
                <b>Licenciatura: </b><span
                    style="text-transform: uppercase">{{ $licenciatura->nombre ?? 'No especificado' }}</span> <br>
                <b>CURP:</b> {{ $profesor->user->CURP }} <br>
                <b>Ciclo escolar:</b> {{ $ciclo_escolar->ciclo_escolar }} <br>
                <b>Vigencia:</b> Agosto {{ substr($ciclo_escolar->ciclo_escolar, -4) }}
            </div>


            @if (($index + 1) % 4 === 0)
                <div class="page-break"></div>
            @endif
        @endforeach
    </div>

</body>

</html>
