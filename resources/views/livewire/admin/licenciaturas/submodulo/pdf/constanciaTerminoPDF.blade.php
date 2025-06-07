<!DOCTYPE html>
<html lang="es">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>CONSTANCIA DE TÉRMINO | {{$alumno->nombre}} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} </title>
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
        padding: 70px 80px; ;
        margin-top:100px;
        /* background: #d7d7d7; */
    }

    .asunto{
        font-size: 20px;

        text-align: right;
        color: #000;
        font-family: 'calibri';
        font-weight: bold;
        margin: 0px 0px 20px 275px;
        line-height: 20px;
        border-top: 4px double #000;
        border-bottom: 4px double #000;
        width: 385px;
        /* padding: 10px; */


    }

    .lugar{
         font-size: 20px;
          text-align: right;
        color: #000;
        font-family: 'calibri';
        margin-top: -20px;
    }

    .corresponda{
        font-size: 25px;
        color: #000;
        font-family: 'calibri';
        font-weight: bold;
        margin-top: 25px;
        line-height: 20px;
        text-transform: uppercase;
    }

    .alumno{
        font-size: 50px;
        text-align: center;
        color: #000;
        font-family: 'greatVibes';
        text-decoration: underline;
        margin-top: -20px;

    }

    .descripcion{
        font-size: 20px;
        text-align: justify;
        color: #000;
        font-family: 'calibri';
        margin-top: -20px;
        line-height: 20px;
    }

    .descripcion2{
        font-size: 20px;
        text-align: justify;
        color: #000;
        font-family: 'calibri';
        margin-top: 20px;
        line-height: 20px;
    }


    .licenciatura{
        text-align: center;
        color: #000;
        font-family: 'calibri';
        margin-top: -20px;
        text-transform: uppercase;
        font-weight: bold;
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
     $nombreFormateado = Str::title(Str::lower($nombreCompleto)); // Por si viene con mayúsculas raras


    $nombreRector = "{$rector->nombre} {$rector->apellido_paterno} {$rector->apellido_materno}";
    $nombreDirectora = "{$directora->nombre} {$directora->apellido_paterno} {$directora->apellido_materno}";

    $licenciaturaMayuscula = Str::upper($alumno->licenciatura->nombre);
@endphp


        <div class="fondo">
            <img src="{{ public_path('storage/carta_pasante.jpg') }}" alt="fondo" style="width: 100%; height: 100%;">
        </div>

        <div class="contenedor">
            <p class="asunto">Asunto: Constancia de término y acreditación.</p>
            <p class="lugar">
            Cd. Altamirano, Gro., a {{ \Carbon\Carbon::parse($fecha)->translatedFormat('d \d\e F \d\e\l Y') }}.
          </p>

          <p class="corresponda">A QUIEN CORRESPONDA:</p>

          <p style="text-align: center; font-size: 20px; color: #000; font-family: 'calibri';  margin-top: 20px;">
            Por este conducto se hace constar que la alumna
          </p>

          <p class="alumno">{{ $nombreFormateado }}</p>

          <p class="descripcion">
           Con CURP de registro <b>{{$alumno->CURP}}</b>, cursó y acreditó
           satisfactoriamente las asignaturas correspondientes a la Licenciatura en
        <b>{{$licenciaturaMayuscula}}</b>, finalizando su último cuatrimestre el 20 de agosto del año en curso,
        en la generación <b>{{$alumno->generacion->generacion}}</b>, y actualmente su documentación oficial se
        encuentra en trámite ante las autoridades educativas correspondientes en la
        Ciudad de Chilpancingo, Gro.
          </p>

          <p class="descripcion2">
            Para los fines legales que el interesado(a) convengan, y conforme a derecho se
                extiende la presente a los
                {{ \NumberFormatter::create('es', \NumberFormatter::SPELLOUT)->format(\Carbon\Carbon::parse($fecha)->day) }}
                días del mes de {{ \Carbon\Carbon::parse($fecha)->translatedFormat('F') }} del {{ \Carbon\Carbon::parse($fecha)->translatedFormat('Y') }}
                en Cd.
                Altamirano,Gro.

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
