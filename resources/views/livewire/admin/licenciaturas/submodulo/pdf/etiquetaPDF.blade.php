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
        .etiqueta {
            width: 100%;
        }

        .contenedor{
            margin-top: -1050px;

        }



        .nombre{
            text-align: center;
            font-size: 45px;
            line-height: 30px;
            font-weight: bold;
            color: #000

        }
        .nombre2{
            text-align: center;
           font-size: 45px;
            line-height: 30px;
            font-weight: bold;
            color: #000;
            margin-top: 60px;

        }
        .nombre3{
            text-align: center;
           font-size: 45px;
            line-height: 30px;
            font-weight: bold;
            color: #000;
            margin-top: 70px;

        }
        .nombre4{
            text-align: center;
           font-size: 45px;
            line-height: 30px;
            font-weight: bold;
            color: #000;
            margin-top: 70px;

        }
        .nombre5{
            text-align: center;
           font-size: 45px;
            line-height: 30px;
            font-weight: bold;
            color: #000;
            margin-top: 60px;

        }


        .licenciatura{
            text-align: center;
            font-size: 25px;
            font-family: 'raleway';
            font-weight: bold;
            margin-top: -45px;
            color: #000;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .generacion{
             text-align: center;
            font-size: 25px;
            font-family: 'raleway';
            font-weight: bold;
            margin-top: -40px;
            color: #000
        }




        .page-break {
            page-break-after: always;
        }




     </style>
</head>
<body>
      <div class="contenedorEtiquetas">
     @foreach ($alumnos as $index => $alumno)
            <img class="etiqueta" src="{{ public_path('storage/etiquetas2.jpg') }}">
        {{-- <div class="credenciales" style="background-image: url('{{ public_path('storage/credencial-frontal.png') }}')"> --}}

            <div class="contenedor">


                <p class="nombre">{{ $alumno->nombre  }} <br> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>
                @if(strlen($alumno->licenciatura->nombre) > 30)
                    <p class="licenciatura" style="font-size:22px;">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @else
                    <p class="licenciatura">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @endif
                <p class="generacion">Generación: {{ $alumno->generacion->generacion }}</p>


                <p class="nombre2">{{ $alumno->nombre  }} <br> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>
                 @if(strlen($alumno->licenciatura->nombre) > 30)
                    <p class="licenciatura" style="font-size:22px;">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @else
                    <p class="licenciatura">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @endif
                <p class="generacion">Generación: {{ $alumno->generacion->generacion }}</p>


                <p class="nombre3">{{ $alumno->nombre  }} <br> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>
                 @if(strlen($alumno->licenciatura->nombre) > 30)
                    <p class="licenciatura" style="font-size:22px;">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @else
                    <p class="licenciatura">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @endif
                <p class="generacion">Generación: {{ $alumno->generacion->generacion }}</p>


                <p class="nombre4">{{ $alumno->nombre  }} <br> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>
                 @if(strlen($alumno->licenciatura->nombre) > 30)
                    <p class="licenciatura" style="font-size:22px;">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @else
                    <p class="licenciatura">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @endif
                <p class="generacion">Generación: {{ $alumno->generacion->generacion }}</p>

                <p class="nombre5">{{ $alumno->nombre  }} <br> {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>
                 @if(strlen($alumno->licenciatura->nombre) > 30)
                    <p class="licenciatura" style="font-size:22px;">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @else
                    <p class="licenciatura">Lic. en {{ $alumno->licenciatura->nombre }}</p>
                @endif
                <p class="generacion">Generación: {{ $alumno->generacion->generacion }}</p>




            </div>




        @if (($index + 1) % 4 === 0)
            <div class="page-break"></div>
        @endif
    @endforeach
      </div>

</body>
</html>
