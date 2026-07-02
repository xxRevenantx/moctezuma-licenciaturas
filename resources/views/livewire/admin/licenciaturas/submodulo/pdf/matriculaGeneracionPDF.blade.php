<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{ $tituloLista }} | Generación {{ $generacion->generacion }}</title>

    <style>
        @page {
            margin: 10px 45px 35px 45px;
        }

        body {
            margin: auto;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 7px;
            border: 1px solid #8a8a8a;
        }

        th {
            color: white;
            text-align: center;
            background: #638acd;
            border-color: #2d2d2d;
        }

        td {
            text-align: left;
        }

        .titulo {
            display: inline-block;
            margin-top: 50px;
            padding: 10px 0;
            color: #4a5568;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            border-top: 2px solid #4a5568;
            border-bottom: 2px solid #4a5568;
        }

        .subtitulo {
            margin: 0;
            padding: 3px 0;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .filtro {
            margin: 3px 0 8px;
            color: #4a5568;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }

        .img1 {
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .img2 {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .watermark {
            position: fixed;
            top: 70%;
            left: 50%;
            z-index: -1;
            width: 100%;
            height: 100%;
            text-align: center;
            opacity: 0.1;
            transform: translate(-50%, -50%);
        }

        footer {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #4a5568;
            border-bottom: 1px solid #4a5568;
        }

        footer p {
            margin: 0;
            padding: 1px 0;
        }
    </style>
</head>

<body>
    <div class="watermark">
        <img src="{{ public_path('storage/letra.png') }}" alt="Marca de agua">
    </div>

    <div style="text-align: center;">
        <img class="img1" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo" height="100" width="100">

        <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA</h1>

        <img class="img2" src="{{ public_path('storage/licenciaturas/' . $licenciatura_nombre->imagen) }}"
            alt="Logo de la licenciatura" height="100" width="100">
    </div>

    <p class="subtitulo" style="font-size: 19px;">{{ $tituloLista }}</p>
    <p class="subtitulo" style="text-transform: uppercase;">
        LICENCIATURA EN {{ $licenciatura_nombre->nombre }}
    </p>
    <p class="subtitulo">GENERACIÓN: {{ $generacion->generacion }}</p>
    <p class="filtro"> TOTAL: {{ $matricula->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>NOMBRE(S)</th>
                <th>APELLIDO PATERNO</th>
                <th>APELLIDO MATERNO</th>
                <th>CURP</th>
                <th>CUATRIMESTRE</th>
                <th>MODALIDAD</th>
                <th>MES DE INGRESO</th>
                <th>OBSERVACIONES</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($matricula as $student)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $student->nombre }}</td>
                    <td>{{ $student->apellido_paterno }}</td>
                    <td>{{ $student->apellido_materno }}</td>
                    <td>{{ $student->CURP }}</td>
                    <td>{{ $student->cuatrimestre->nombre_cuatrimestre ?? '-' }}</td>
                    <td>{{ $student->modalidad->nombre ?? '-' }}</td>
                    <td>SEPTIEMBRE</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="padding: 20px; text-align: center;">
                        NO HAY ALUMNOS PARA EL FILTRO SELECCIONADO
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        <p>
            {{ $escuela->nombre }} C.C.T. {{ $escuela->CCT }}.
            C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }},
            Col. {{ $escuela->colonia }}, C.P. {{ $escuela->codigo_postal }},
            Cd. {{ $escuela->ciudad }}, {{ $escuela->estado }}.
        </p>
        <p>
            Fecha de expedición:
            {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}
        </p>
    </footer>
</body>

</html>
