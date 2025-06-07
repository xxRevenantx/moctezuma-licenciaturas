<!DOCTYPE html>
<html lang="es">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>CARTA PASANE | {{$alumno->nombre}} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} </title>
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
        padding: 70px 0 ;
        margin-top:40px;
        /* background: #d7d7d7; */
    }

    .alumno{
        font-size: 30px;
        text-align: center;
        color: #000;
        font-family: 'calibri';
        text-decoration: underline;
        font-weight: bold;
    }

    .descripcion{
        font-size: 20px;
        text-align: center;
        color: #000;
        font-family: 'calibri';
        font-weight: bold;
        margin-top: 0px;
        line-height: 20px;
        text-transform: uppercase;
    }
    .licenciatura{
        text-align: center;
        color: #000;
        font-family: 'calibri';
        margin-top: -20px;
        text-transform: uppercase;
        font-weight: bold;
    }

    .contenedor-linea{
        display: block;
        margin: -30px auto;
        width: 90%;
    }
    p.expide{
        text-align: center;
        font-family: 'calibri';
        font-size: 25px;
        margin-bottom: 0
    }
    p.to{
          text-align: center;
        font-family: 'calibri';
        font-size: 30px;
        margin-top: -20px;
        margin-bottom: -20px;
    }

    p.carta{
        text-align: center;
        font-family: 'calibri';
        font-size: 45px;
        font-weight: bold;
        margin-top: 8px;
    }
    p.archivo{
        text-align: center;
        font-family: 'calibri';
        font-size: 20px;
        font-weight: bold;
        margin-top: -20px;
        line-height: 20px;
    }


    .generacion{
        font-size: 45px;
        text-align: center;
        color: #000;
         font-family: 'greatVibes';
        margin-top: -10px;
        line-height: 40px;
    }

    .lugar{
        font-size: 17px;
        text-align: center;
        color: #000;
        font-family: 'calibri';
        margin-top: -20px;
        text-transform: uppercase;
    }

    .atentamente{
         font-size: 17px;
        text-align: center;
        color: #000;
        font-family: 'calibri';
        text-transform: uppercase;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-transform: uppercase;
        margin-top: 80px;
        line-height: 17px;

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
    $nombreCompleto = "{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}";


    $nombreRector = "{$rector->nombre} {$rector->apellido_paterno} {$rector->apellido_materno}";
    $nombreDirectora = "{$directora->nombre} {$directora->apellido_paterno} {$directora->apellido_materno}";
@endphp


        <div class="fondo">
            <img src="{{ public_path('storage/carta_pasante.jpg') }}" alt="fondo" style="width: 100%; height: 100%;">
        </div>

        <div class="contenedor">
            <p class="expide">EXPIDE LA PRESENTE</p>
            <p class="carta">CARTA DE PASANTE</p>
            <p class="to">A</p>
          <p class="alumno">{{ $nombreCompleto }}</p>

          <p class="descripcion">
           EN ATENCIÓN A QUE APROBÓ TOTALMENTE LAS ASIGNATURAS QUE <br>
            COMPRENDEN EL PLAN DE ESTUDIOS CORRESPONDIENTES A LA <br>
            LICENCIATURA EN {{ $licenciatura->nombre }}.
          </p>



          <p class="generacion">
            Durante el periodo <br> {{ $alumno->generacion->generacion }}
          </p>

          <p class="archivo">
            SEGÚN CONSTANCIAS QUE EXISTEN EN EL ARCHIVO DE ESTA <br>
            INSTITUCIÓN.
          </p>

          <p class="lugar">
            CD. ALTAMIRANO, GRO., A {{ \Carbon\Carbon::parse($fecha)->translatedFormat('d \D\E F \D\E\L Y') }}
            {{-- CD. ALTAMIRANO, GRO., A 28 DE AGOSTO DEL 2024 --}}
          </p>

          <p class="atentamente">
           <b> A T E N T A M E N T E</b> <br>
        EDUCACIÓN INTEGRAL ¡ELIGE CUM!
          </p>


          <table>
                <tr>
                    <td style="text-align: center;">
                        <span style="display: block; border-top: 1px solid #000; width: 300px; margin: 0 auto;"></span>
                        <span class="rector" style="font-family: 'calibri'; font-size: 17px; bold;">{{ $rector->titulo }} {{ $nombreRector }}</span><br>
                        <span style="font-family: 'calibri'; font-size: 17px;">{{ $rector->cargo }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span style="display: block; border-top: 1px solid #000; width: 300px; margin: 0 auto;"></span>
                        <span class="directora" style="font-family: 'calibri'; font-size: 17px; bold;">M.S.P.{{ $nombreDirectora }}</span><br>
                        <span style="font-family: 'calibri'; font-size: 17px;">{{ $directora->cargo }}</span>
                    </td>
                </tr>
          </table>




        </div>


</body>
</html>
