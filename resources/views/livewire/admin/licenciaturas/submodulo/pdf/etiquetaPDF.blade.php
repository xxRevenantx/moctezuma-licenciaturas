<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ETIQUETAS</title>

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


         .contenedorEtiquetas{
            width: 100%;
         }
        .etiquetas {
            height: 100%;
            width: 100%;
        }

        .contenedor1{
            margin-top: -940px;

        }



        .nombre1{
            text-align: center;
            font-size: 70px;
            line-height: 50px;
            font-weight: bold;

        }

        .rotacion{
        transform: rotate(-180deg);
        }


        .nombre2{
            margin-top: 150px;
            text-align: center;
            font-size: 70px;
            line-height: 50px;
            font-weight: bold
        }

        .licenciatura1, .licenciatura2{
            text-align: center;
            font-size: 35px;
            font-family: 'raleway';
            font-weight: bold;
            margin-top: -40px;
        }

        .generacion1, .generacion2{
             text-align: center;
            font-size: 35px;
            font-family: 'raleway';
            font-weight: bold;
            margin-top: -40px;
        }




        .page-break {
            page-break-after: always;
        }




     </style>
</head>
<body>
      <div class="contenedorEtiquetas">
     @foreach ($alumnos as $index => $alumno)
            <img class="etiquetas" src="{{ public_path('storage/etiquetas.jpg') }}">
        {{-- <div class="credenciales" style="background-image: url('{{ public_path('storage/credencial-frontal.png') }}')"> --}}

            <div class="contenedor1">
                <div class="rotacion">
                <p class="nombre1">{{ $alumno->nombre  }} <br> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>
                <p class="licenciatura1">Licenciatura en {{ $alumno->licenciatura->nombre }}</p>
                <p class="generacion1">Generación: {{ $alumno->generacion->generacion }}</p>
                </div>




                <p class="nombre2">{{ $alumno->nombre  }} <br> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>
                <p class="licenciatura2">Licenciatura en {{ $alumno->licenciatura->nombre }}</p>
                <p class="generacion2">Generación: {{ $alumno->generacion->generacion }}</p>






            </div>




        @if (($index + 1) % 2 === 0)
            <div class="page-break"></div>
        @endif
    @endforeach
      </div>

</body>
</html>
