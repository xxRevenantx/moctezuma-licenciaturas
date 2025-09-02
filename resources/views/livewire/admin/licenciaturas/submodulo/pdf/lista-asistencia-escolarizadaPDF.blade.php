<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>Lista de Asistencia | Gen: {{$generacion->generacion}} </title>
</head>
<style>
    @page { margin:10px 25px 20px 25px; }

    @font-face {
        font-family: 'calibri';
        font-style: normal;
        src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');
    }
    @font-face {
        font-family: 'calibri';
        font-style: normal;
        font-weight: 700;
        src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
    }

    body{
        font-family: 'calibri';
        margin: auto;
        font-size: 13px;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }

    table{ width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td{ padding: 2px; font-size: 13px; }
    td{ border: 1px solid #000; text-align: left; }
    th{
        border: 1px solid #2d2d2d;
        background: #e1e1e1;
        font-weight: bold;
        text-align: center;
        color: #000;
    }

    .titulo{
        font-weight: bold; color: #4a5568; text-align: center;
        font-size: 27px; margin-top: 50px; padding: 0;
        border-top: 2px solid #4a5568; border-bottom: 2px solid #4a5568;
        display: inline-block;
    }
    p.licenciatura{ text-transform: uppercase; font-size: 16px; line-height: 7px; }
    p.cuatrimestre{ text-transform: uppercase; font-size: 18px; text-align: center; }

    img.img1{ position: absolute; top: 10px; left: 10px; }
    img.img2{ position: absolute; top: 10px; right: 10px; }

    .watermark{
        position: fixed; top: 70%; left: 50%; transform: translate(-50%, -50%);
        width: 100%; height: 100%; z-index: -1; opacity: .1; text-align: center;
    }

    footer{
        position: absolute; bottom: 0; left: 0; text-align: center;
        font-size: 12px; line-height: 12px; width: 100%;
        border-top: 1px solid #4a5568; border-bottom: 1px solid #4a5568;
    }
    footer p{ margin:0; padding:0; }

    /* columnas fijas */
    .col-num{ width:30px; text-align:center; }
    .col-mat{ width:70px; }
    .col-nom{ width:230px; }
    .col-dia{ width:18px; min-width:18px; text-align:center; padding:0; }

    /* Leyenda (tabla pequeña a la derecha) */
    .legend{
        width: 220px; border-collapse: collapse; margin-top: 8px;
    }
    .legend td{
        border: 1px solid #000; font-size: 12px; padding: 6px 8px;
    }
    .legend .sym{ width: 30px; text-align: center; font-weight: 700; }
    .legend .txt{ text-transform: uppercase; }
</style>
<body>
    <div class="watermark">
        <img src="{{ public_path('storage/letra.png') }}" alt="Watermark">
    </div>

    <div style="text-align: center;">
        <img class="img1" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo" height="100" width="100">
        <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA</h1>

        @if ($materia->licenciatura->imagen)
            <img class="img2" src="{{ public_path('storage/licenciaturas/'.$materia->licenciatura->imagen) }}" alt="Logo Licenciatura" height="100" width="100">
        @else
            <img class="img2" src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo" height="100" width="100">
        @endif
    </div>

    <p style="font-size: 24px; text-align:center; font-weight:bold; margin-top:-30px; line-height:20px">
        LISTA DE ASISTENCIA <br>C.C.T. {{$escuela->CCT}}
    </p>

    <p class="licenciatura">LICENCIATURA EN: <b>{{ $materia->licenciatura->nombre }}</b></p>
    <p class="licenciatura">DOCENTE: <b>{{ $materia->profesor->nombre }} {{ $materia->profesor->apellido_paterno }} {{ $materia->profesor->apellido_materno }}</b></p>
    <p class="licenciatura">MATERIA: <b>{{ $materia->materia->nombre }}</b></p>

    <p class="cuatrimestre">
        <b><u>{{ $materia->cuatrimestre->cuatrimestre }}° CUATRIMESTRE</u></b>
        &nbsp;&nbsp;&nbsp;&nbsp;
        MODALIDAD: <b><u>{{ $materia->modalidad->nombre }}</u></b>
    </p>

    <table>
        <thead>
            <tr>
                <th class="col-num" rowspan="2"  >No.</th>
                <th class="col-mat" rowspan="2" >Matrícula</th>
                <th class="col-nom"  >NOMBRE COMPLETO</th>
                    @for ($i = 1; $i <= 30; $i++)
                        <th class="col-dia" style="height:65px;" rowspan="2"></th>
                    @endfor
                 </tr>

                 <tr>
                    <th></th>

                 </tr>

        </thead>
        <tbody>
            @foreach($alumnos as $key => $alumno)
                <tr>
                    <td class="col-num">{{ $key+1 }}</td>
                    <td class="col-mat">{{ $alumno->matricula }}</td>
                    <td class="col-nom">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                    @for ($i = 1; $i <= 30; $i++)
                        <td class="col-dia"></td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- LEYENDA DE MARCAS (agregada al final) -->
    <table class="legend">
        <tr>
            <td class="sym">&bull;</td>
            <td class="txt">Asistencia</td>
        </tr>
        <tr>
            <td class="sym">/</td>
            <td class="txt">Retardo</td>
        </tr>
        <tr>
            <td class="sym">x</td>
            <td class="txt">Falta</td>
        </tr>
    </table>

    <footer>
        <p>{{$escuela->nombre}} C.C.T. {{$escuela->CCT}} C. {{$escuela->calle}} No. {{$escuela->no_exterior}}, Col. {{$escuela->colonia}}, C.P. {{$escuela->codigo_postal}}, Cd. {{$escuela->ciudad}}, {{$escuela->estado}}.</p>
        <p>Fecha de expedición: {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}</p>
    </footer>
</body>
</html>
