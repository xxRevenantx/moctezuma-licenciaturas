<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CREDENCIALES</title>

     <style>
         @page { margin:30px 0px 0px 0px; }

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

         body{
            font-family: 'calibri'
         }



        .credenciales {
            border: 1px solid #000;
         background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center;
            width: 18cm;
            height: 5.7cm;
            margin: 5px auto;
            /* padding: 10px; */

        }

        .info {
            /* position: absolute;
            bottom: 15px;
            left: 15px;
            color: #000;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 5px;
            border-radius: 5px; */
        }

        .page-break {
            page-break-after: always;
        }



     </style>
</head>
<body>
     @foreach ($alumnos as $index => $alumno)
        <div class="credenciales" style="background-image: url('{{ public_path('storage/credencial-frontal.png') }}')">
            <div class="info">
                <strong>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</strong><br>
                Matrícula: {{ $alumno->matricula }}<br>
                CURP: {{ $alumno->curp }}
            </div>
        </div>

        @if (($index + 1) % 4 === 0)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
