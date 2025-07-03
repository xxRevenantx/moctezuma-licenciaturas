<!DOCTYPE html>
<html lang="es">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>CONSTANCIA | {{ $constancia->alumno->nombre }} {{ $constancia->alumno->apellido_paterno }} {{ $constancia->alumno->apellido_materno }}   </title>
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
        padding: 70px 100px;
        margin-top:30px;
        font-family: 'calibri'

    }


    .asunto{
        margin-top: 200px;
        text-align: right;
        font-weight: bold;
          font-size:18px;
          text-transform: uppercase
    }




    .descripcion{
        font-size: 18px;
        line-height: 19px;
        text-align: justify;
        text-indent: 30px;
        margin-top: 20px;
    }

    .rector{
        text-transform: uppercase;
    }


    .detalles{
        /* font-family: 'Times new Roman' */
        line-height: 14px;
        margin-top: 50px;
        font-size:18px;
        position:absolute;
        float: right
    }


    .detalles_titulo{
        text-align: right;

    }

    .corresponda{
        text-align: left;
        font-size: 21px;
        font-weight: bold;
        margin-top: 60px;
        line-height: 15px;
    }


</style>
<body>
    @php

    // $nombreRector = "{$rector->nombre} {$rector->apellido_paterno} {$rector->apellido_materno}";
    // $nombreDirectora = "{$directora->nombre} {$directora->apellido_paterno} {$directora->apellido_materno}";

    // $nombreJefe = "{$jefe->titulo}{$jefe->nombre} {$jefe->apellido_paterno} {$jefe->apellido_materno}";
    // $nombreSubjefe = "{$subjefe->titulo}{$subjefe->nombre} {$subjefe->apellido_paterno} {$subjefe->apellido_materno}";
@endphp

        <div class="fondo">
            <img src="{{ public_path('storage/membrete_constancia.jpg') }}" alt="fondo" style="width: 100%; height: 100%;">
        </div>

        <div class="contenedor">

            <table class="detalles">
                <tr>
                    <td class="detalles_titulo"><b>Dependencia:</b></td>
                    <td>{{ $escuela->nombre }}</td>
                </tr>
                <tr>
                    <td class="detalles_titulo"><b>C.C.T:</b></td>
                    <td>{{ $escuela->CCT }}</td>
                </tr>
                <tr>
                    <td class="detalles_titulo"><b>Sección: </b></td>
                    <td>Administrativa</td>
                </tr>
                <tr>
                    <td class="detalles_titulo"><b>Expediente: </b></td>
                    <td>{{ $ciclo_escolar->ciclo_escolar }}</td>
                </tr>
                <tr>
                    <td class="detalles_titulo"><b>Núm. de oficio: </b></td>
                    <td>
                        @if ($constancia->no_constancia < 9)
                             0{{ $constancia->no_constancia }}
                        @else
                             {{ $constancia->no_constancia }}
                        @endif
                    </td>
                </tr>
            </table>


            <p class="asunto">

                     ASUNTO: CONSTANCIA DE ESTUDIOS

               <br>
                Ciudad Altamirano, Gro., a {{ \Carbon\Carbon::parse($constancia->fecha_expedicion)->translatedFormat('d \d\e F \d\e\l Y') }}
            </p>


          <p class="corresponda">
             @if ($constancia->tipo_constancia == 1)
                      A QUIEN CORRESPONDA:
                @else
                  SECRETARÍA DE RELACIONES EXTERIORES: <br>
                P R E S E N T E
               @endif

          </p>

          <p class="descripcion">
            El que suscribe M.C. José Rubén Solórzano Carbajal, en mi carácter de rector del
            Centro Universitario Moctezuma, con clave de incorporación 12PSU0173I, ubicada en
            calle Francisco I. Madero Ote. 800 de la localidad de Cd. Altamirano, municipio de
            Pungarabato, Gro. Región Tierra Caliente.

          </p>

        </div>


</body>
</html>
