<!DOCTYPE html>
<html lang="es">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>OFICIO DE MATRÍCULAS |  </title>
</head>
<style>

    @page { margin:0px 0px 0px 0px; }


    .page-break {
     page-break-after: always;
    }

    @font-face {
            font-family: 'greatVibes';
            font-style: normal;
            src: url('{{ storage_path('fonts/GreatVibes-Regular.ttf') }}') format('truetype');

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


    .fondo {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
    .contenedor{
        padding: 70px 80px;
        margin-top:30px;
        font-family: 'calibri'

    }


    .lugar, .asunto, .generacion{
        font-size: 19px;
        text-align: right;
        color: #000;
        font-family: 'calibri';
        font-weight: bold;
        line-height: 7px;
    }

    .autoridades p{
        line-height: 4px;
        font-size: 18px;
    }


    .rector{
        text-transform: uppercase;
    }

    .directora{
        text-transform: uppercase;
    }

</style>
<body>
    @php

    $nombreRector = "{$rector->nombre} {$rector->apellido_paterno} {$rector->apellido_materno}";
    $nombreDirectora = "{$directora->nombre} {$directora->apellido_paterno} {$directora->apellido_materno}";

    $nombreJefe = "{$jefe->titulo}{$jefe->nombre} {$jefe->apellido_paterno} {$jefe->apellido_materno}";
    $subjefe = "{$subjefe->titulo}{$subjefe->nombre} {$subjefe->apellido_paterno} {$subjefe->apellido_materno}";
@endphp

        <div class="fondo">
            <img src="{{ public_path('storage/membrete_oficios.jpg') }}" alt="fondo" style="width: 100%; height: 100%;">
        </div>

        <div class="contenedor">

          <p class="lugar">
            Ciudad Altamirano, Gro., a {{ \Carbon\Carbon::parse($fecha)->translatedFormat('d \d\e F \d\e\l Y') }}
          </p>
          <p class="asunto"><b>Asunto:</b> Solicitud de Matrículas</p>
          <p class="generacion">Generación: {{$generacion->generacion}}</p>


          <table class="autoridades">
                <td>
                    <p style="text-transform: uppercase"><b>{{$nombreJefe}}</b></p>
                    <p>{{$jefe->cargo}}</p>
                    <p>Nivel Superior</p>
                    <p>Secretaría de Educación Guerrero</p>
                    <p><b>P R E S E N T E</b></p>
                </td>
                <td></td>
                <td>
                    <p>CATT'N</p>
                    <p style="text-transform: uppercase"><b>{{$subjefe}}</b></p>
                </td>
          </table>








          <table>
                <tr>
                    <td style="text-align: center;">
                        <span style="display: block; border-top: 1px solid #000; width: 300px; margin: 0 auto;"></span>
                        <span class="rector" style="font-family: 'calibri'; font-size: 18px; bold;">{{ $rector->titulo }} {{ $nombreRector }}</span><br>
                        <span style="font-family: 'calibri'; font-size: 17px;">{{ $rector->cargo }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span style="display: block; border-top: 1px solid #000; width: 300px; margin: 0 auto;"></span>
                        <span class="directora" style="font-family: 'calibri'; font-size: 18px; bold;">M.S.P.{{ $nombreDirectora }}</span><br>
                        <span style="font-family: 'calibri'; font-size: 17px;">{{ $directora->cargo }}</span>
                    </td>
                </tr>
          </table>




        </div>


</body>
</html>
