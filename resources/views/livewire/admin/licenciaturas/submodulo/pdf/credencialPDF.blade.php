<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>CREDENCIALES DE ESTUDIANTES</title>

    <style>
        @page {
            margin: 28px 0 0 0;
        }

        @font-face {
            font-family: 'calibri';
            font-style: normal;
            font-weight: 400;
            src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'calibri';
            font-style: normal;
            font-weight: 700;
            src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'calibri', sans-serif;
        }

        .contenedorCredenciales {
            width: 100%;
            margin: 40px auto;
        }

        .bloqueCredencial {
            position: relative;
            width: 18cm;
            height: 5.7cm;
            margin: 5px auto;
        }

        .credencialFondo {
            display: block;
            width: 18cm;
            height: 5.7cm;
            border: 1px solid #000;
        }

        .fotoAlumno {
            position: absolute;
            top: 0.62cm;
            left: 0.38cm;
            width: 2.5cm;
            height: 3cm;
            object-fit: cover;
            border: 1px solid #d7dde2;
            background: #fff;
        }

        .sinFoto {
            position: absolute;
            top: 0.62cm;
            left: 0.38cm;
            width: 2.5cm;
            height: 3cm;
            border: 1px solid #b8c2cc;
            background: #fff;
            color: #a0a8b0;
            text-align: center;
            font-size: 9px;
            font-weight: 700;
            line-height: 12px;
            padding-top: 1.08cm;
        }

        .logoLicenciatura {
            position: absolute;
            top: -30px;
            right: 10px;
            width: 1.35cm;
            max-height: 1.35cm;
        }

        .info {
            position: absolute;
            top: 30px;
            left: 3.40cm;
            width: 10.1cm;
            font-size: 11px;
            line-height: 10px;
            color: #111;
        }

        .titulo {
            display: block;
            margin-top: -15px;
            margin-bottom: 0px;
            margin-left: 50px;
            font-size: 10.5px;
            font-weight: 700;
            color: #fefefe;
        }

        .nombreAlumno {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="contenedorCredenciales">
        @foreach ($alumnos as $index => $alumno)
            @php
                $logoLicenciatura = $alumno->licenciatura?->imagen
                    ? public_path('storage/licenciaturas/' . $alumno->licenciatura->imagen)
                    : null;
                $fotoDataUri = $alumno->foto_data_uri;
                $ciclo = $ciclo_escolar?->ciclo_escolar ?: 'N/A';
                $vigencia = preg_match('/(\d{4})$/', (string) $ciclo, $coincidenciaVigencia)
                    ? 'Agosto ' . $coincidenciaVigencia[1]
                    : 'N/A';
            @endphp

            <div class="bloqueCredencial">
                <img class="credencialFondo" src="{{ public_path('storage/credencial-frontal.png') }}" alt="Credencial">

                @if ($fotoDataUri)
                    <img class="fotoAlumno" src="{{ $fotoDataUri }}" alt="Fotografía del alumno">
                @else
                    <div class="sinFoto">FOTO + SELLO</div>
                @endif

                @if ($logoLicenciatura && is_file($logoLicenciatura))
                    <img class="logoLicenciatura" src="{{ $logoLicenciatura }}" alt="Logo de licenciatura">
                @endif

                <div class="info">
                    <span class="titulo">CREDENCIAL DEL ESTUDIANTE</span>
                    <strong>Nombre:</strong>
                    <span class="nombreAlumno">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }}
                        {{ $alumno->apellido_materno }}</span><br>
                    <strong>Matrícula:</strong> {{ $alumno->matricula ?: $alumno->matricula_interna ?: 'N/A' }}<br>
                    <strong>CURP:</strong> {{ $alumno->CURP ?: 'N/A' }}<br>
                    <strong>Licenciatura:</strong>
                    {{ $alumno->licenciatura?->nombre_corto ?: $alumno->licenciatura?->nombre ?: 'N/A' }}<br>
                    <strong>Ciclo escolar:</strong> {{ $ciclo }}<br>
                    <strong>Vigencia:</strong> {{ $vigencia }}
                </div>
            </div>

            @if (($index + 1) % 4 === 0 && !$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    </div>
</body>

</html>
