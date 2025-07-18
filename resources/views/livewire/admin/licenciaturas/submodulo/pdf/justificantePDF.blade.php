<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>JUSTIFICANTE | {{ $justificantes->alumno->nombre."_".$justificantes->alumno->apellido_paterno."_".$justificantes->alumno->apellido_materno }}</title>

     <style>
         @page { margin:0px 0px 0px 0px; }

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

       @font-face {
        font-family: 'raleway';
        font-style: normal;
        src: url('{{ storage_path('fonts/Raleway.ttf') }}') format('truetype');
         }

         @font-face {
        font-family: 'raleway';
        font-style: bold;
        font-weight: 700;
        src: url('{{ storage_path('fonts/Raleway-Medium.ttf') }}') format('truetype');
         }

         body{
            font-family: 'calibri'
         }


         .contenedorJustificante{
            width: 100%;
         }
        .justificante {
            width: 100%;
        }

        .contenedor{
            margin-top: -430px;
            padding: 0 70px;

        }


        .fecha{
            text-align: center;
            font-size: 18px;
        }

        table{
            margin-top: 20px;
        }
        table td{
            text-align: center;
            font-size: 18px;
        }

        .x{
            width: 15px;
        }




     </style>
</head>
<body>
      <div class="contenedorJustificante">
            <img class="justificante" src="{{ public_path('storage/justificante.png') }}">
        {{-- <div class="credenciales" style="background-image: url('{{ public_path('storage/credencial-frontal.png') }}')"> --}}

            <div class="contenedor">

                <p style="text-align: right" class="fecha">Fecha: <u>{{ \Carbon\Carbon::parse($justificantes->fecha_expedicion)->translatedFormat('d \\d\\e F \\d\\e Y') }}</u></p>

                <p style="text-align: center; font-size:30px; font-weight:bold; margin-top:-20px">JUSTIFICANTE</p>

                <p style="font-size: 18px;"><i>De: <u><b>{{ $justificantes->alumno->nombre }} {{ $justificantes->alumno->apellido_paterno }} {{ $justificantes->alumno->apellido_materno }}</b></i></u></p>
                <p style="font-size: 18px;  margin-top:-10px"><i>Licenciatura y Cuatrimestre: <u><b>{{ $justificantes->alumno->licenciatura->nombre }} - {{ $justificantes->alumno->cuatrimestre->cuatrimestre}}° CUATRIMESTRE</b></u></i></p>

                <p style="font-size: 18px;  margin-top:-10px">
                    <i>
                        Por la presente justifica el/los día/s:
                        <u>
                            <b>
                                  @php
                                    $fechas = explode(',', $justificantes->fechas_justificacion);
                                @endphp
                                @foreach ($fechas as $index => $fecha)
                                    {{ \Carbon\Carbon::parse(trim($fecha))->translatedFormat('d \d\e F \d\e Y') }}@if($index !== count($fechas) - 1),@endif
                                @endforeach

                                {{-- {{ \Carbon\Carbon::parse(trim($fecha))->translatedFormat('d \d\e F \d\e Y') }} --}}
                            </b>
                        </u>
                    </i>
                </p>

                <p style="font-size: 18px;  margin-top:-5px">
                    <i>Por la siguiente razón (es):
                    </i>
                </p>


                        @if ($justificantes->justificacion == 'Asuntos personales')
                            <img class="x" src="{{ public_path('storage/x.png') }}">
                             <i><u>{{ $justificantes->justificacion }}</u></i> <br>
                             <img class="x" src="{{ public_path('storage/cuadro.png') }}">
                             <i>Problemas de salud</i> <br>
                             <img class="x" src="{{ public_path('storage/cuadro.png') }}">
                             <i>Otro</i>

                        @elseif ($justificantes->justificacion == 'Problemas de salud')

                             <img class="x" src="{{ public_path('storage/cuadro.png') }}">
                             <i>Asuntos personales</i> <br>
                             <img class="x" src="{{ public_path('storage/x.png') }}">
                            <i><u>{{ $justificantes->justificacion }}</u></i> <br>
                             <img class="x" src="{{ public_path('storage/cuadro.png') }}">
                             <i>Otro</i>

                        @elseif ($justificantes->justificacion == 'Otro')
                            <img class="x" src="{{ public_path('storage/cuadro.png') }}">
                             <i>Asuntos personales</i> <br>
                             <img class="x" src="{{ public_path('storage/cuadro.png') }}">
                            <i>Problemas de salud</i> <br>
                             <img class="x" src="{{ public_path('storage/x.png') }}">
                             <i><u>{{ $justificantes->justificacion }}</u></i>

                        @endif



                <table style="width:100%">
                    <tr>
                        <td>_______________________<br>Firma del tutor</td>
                        <td>_______________________<br>Expide</td>
                    </tr>
                </table>

                <p style="font-size:12px">C.c.p. Sistema Escolar Interno</p>
                <p style="font-size:12px; margin-top:-15px">C.c.p. Archivo</p>


                {{-- <p class="nombre">{{ $alumno->nombre  }} <br> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>
                @if(strlen($alumno->licenciatura->nombre) > 30)
                    <p class="licenciatura" style="font-size:22px;">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @else
                    <p class="licenciatura">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @endif
                <p class="generacion">Generación: {{ $alumno->generacion->generacion }}</p> --}}



            </div>
      </div>

</body>
</html>
