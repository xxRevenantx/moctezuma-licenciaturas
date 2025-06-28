<!DOCTYPE html>
<html lang="es">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>OFICIO DE KARDEX | GEN: {{ $generacion->generacion }}  </title>
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
        padding: 70px 50px;
        margin-top:30px;
        font-family: 'calibri'

    }


    .lugar, .asunto, .generacion{
        font-size: 18px;
        text-align: right;
        color: #000;
        font-family: 'calibri';
        font-weight: bold;
        line-height: 7px;
    }

    .jefe{
        width: 30%;
        /* border: 1px solid #000; */
        line-height: 4px;
        font-size: 18px;
        margin-top: 35px;

    }


    .subjefe{
        width: 44%;
        /* border: 1px solid #000; */
        font-size: 18px;
         margin-left: 350px;
        margin-top: -60px;
        line-height: 19px;
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

    .directora{
        text-transform: uppercase;
    }

</style>
<body>
    @php

    $nombreRector = "{$rector->nombre} {$rector->apellido_paterno} {$rector->apellido_materno}";
    $nombreDirectora = "{$directora->nombre} {$directora->apellido_paterno} {$directora->apellido_materno}";

    $nombreJefe = "{$jefe->titulo}{$jefe->nombre} {$jefe->apellido_paterno} {$jefe->apellido_materno}";
    $nombreSubjefe = "{$subjefe->titulo}{$subjefe->nombre} {$subjefe->apellido_paterno} {$subjefe->apellido_materno}";
@endphp

        <div class="fondo">
            <img src="{{ public_path('storage/membrete_oficios.jpg') }}" alt="fondo" style="width: 100%; height: 100%;">
        </div>

        <div class="contenedor">

          <p class="lugar">
            Ciudad Altamirano, Gro., a {{ \Carbon\Carbon::parse($fecha)->translatedFormat('d \d\e F \d\e\l Y') }}
          </p>
          <p class="asunto"><b>Asunto:</b> Solicitud de Kardex</p>
          <p class="generacion">Generación: {{$generacion->generacion}}</p>


          <table class="jefe">
            <tr>
                <td style="width:400px">
                    <p style="text-transform: uppercase"><b>{{$nombreJefe}}</b></p>
                    <p>{{$jefe->cargo}}</p>
                    <p>Nivel Superior</p>
                    <p>Secretaría de Educación Guerrero</p>
                    <p><b>P R E S E N T E</b></p>
                </td>
            </tr>
          </table>


          <p class="descripcion">
            El que suscribe <b>{{ $rector->titulo }} {{ $nombreRector }}</b>, en mi carácter de Rector del
            <b>{{ $escuela->nombre }}</b>, con Clave de Centro de Trabajo <b>{{ $escuela->CCT }}</b>, con domicilio en
            calle Francisco I. Madero Ote. 800, Col. Esquipulas de la localidad de Ciudad Altamirano,
            municipio de Pungarabato del estado de Guerrero, y de acuerdo a mis facultades concedidas,
            de manera atenta y respetuosa me dirijo a usted para solicitar los <b>KARDEX</b> correspondientes a la
            <b>GENERACIÓN {{ $generacion->generacion }}</b>. A continuación se anexan las licenciaturas correspondientes a la
            generación solicitada:
            <ul style="margin-top: -10px; line-height:20px">

            @foreach ($licenciaturas as $lic )
                    <li style="text-transform: uppercase; font-size:19px"> <b>LICENCIATURA EN {{ $lic->nombre }}</b></li>
            @endforeach

             </ul>
          </p>


          <p style="font-size: 19px; text-indent: 30px; line-height:20px;  margin-top:-10px">Quedando atento a nuestra solicitud, solo me resta enviar un afectuoso saludo y ponernos a sus finas atenciones.</p>



          <p style="text-align: center">
            <b>A T E N T A M E N T E</b> <br> <span style="text-transform: uppercase">{{ $rector->cargo }}</span>
          </p>


          <table style="width: 100%; margin-top:60px">
                <tr>
                    <td style="text-align: center;">
                        <span style="display: block; border-top: 1px solid #000; width: 300px; margin: 0 auto;"></span>
                        <span class="rector" style="font-family: 'calibri'; font-size: 16px; bold;">{{ $rector->titulo }} {{ $nombreRector }}</span><br>
                    </td>

                </tr>
          </table>

          <p style="font-size:12px; margin-top:-10px">C.c.p. Archivo</p>



        </div>


</body>
</html>
