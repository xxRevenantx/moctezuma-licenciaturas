<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de promedios</title>

    <style>
        @page {
            margin: 30px 35px 45px 35px;
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

        body {
            font-family: 'calibri', sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .header {
            position: relative;
            text-align: center;
            border-bottom: 3px solid #006492;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .logo-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 75px;
            height: 75px;
            object-fit: contain;
        }

        .logo-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 75px;
            height: 75px;
            object-fit: contain;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #006492;
            margin: 0;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 15px;
            font-weight: bold;
            margin: 3px 0;
            color: #374151;
            text-transform: uppercase;
        }

        .badge {
            display: inline-block;
            background: #88AC2E;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
        }

        .summary {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #d1d5db;
            padding: 6px;
            font-size: 11px;
        }

        .summary .label {
            background: #f3f4f6;
            font-weight: bold;
            width: 22%;
            color: #374151;
        }

        table.promedios {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.promedios th {
            background: #006492;
            color: #ffffff;
            border: 1px solid #0f172a;
            padding: 7px 5px;
            font-size: 11px;
            text-transform: uppercase;
        }

        table.promedios td {
            border: 1px solid #9ca3af;
            padding: 6px 5px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .promedio {
            font-weight: bold;
            color: #006492;
        }

        .lugar {
            font-weight: bold;
            color: #88AC2E;
        }

        .sin-promedio {
            color: #9ca3af;
            font-style: italic;
        }

        footer {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #374151;
            border-top: 1px solid #d1d5db;
            padding-top: 5px;
        }

        .nota {
            margin-top: 10px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>

<body>

    <div class="header">
        @if (file_exists(public_path('storage/letra2.jpg')))
            <img class="logo-left" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo">
        @endif

        @if (!empty($licenciatura->imagen) && file_exists(public_path('storage/licenciaturas/' . $licenciatura->imagen)))
            <img class="logo-right" src="{{ public_path('storage/licenciaturas/' . $licenciatura->imagen) }}"
                alt="Logo licenciatura">
        @elseif(file_exists(public_path('storage/logo-moctezuma.jpg')))
            <img class="logo-right" src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo licenciatura">
        @endif

        <h1 class="title">Centro Universitario Moctezuma</h1>
        <p class="subtitle">Lista general de promedios</p>
        <span class="badge">Generación {{ $generacion->generacion }}</span>
    </div>

    <table class="summary">
        <tr>
            <td class="label">Licenciatura</td>
            <td>{{ $licenciatura->nombre }}</td>
            <td class="label">Generación</td>
            <td>{{ $generacion->generacion }}</td>
        </tr>
        <tr>
            <td class="label">Total de alumnos</td>
            <td>{{ $alumnos->count() }}</td>
            <td class="label">Fecha de emisión</td>
            <td>{{ now()->translatedFormat('d \d\e F \d\e Y') }}</td>
        </tr>
    </table>

    <table class="promedios">
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th style="width: 17%;">Matrícula</th>
                <th>Nombre del alumno</th>
                <th style="width: 15%;">Materias</th>
                <th style="width: 15%;">Promedio</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($alumnos as $index => $alumno)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>

                    <td class="text-center">
                        {{ $alumno->matricula ?? 'N/A' }}
                    </td>

                    <td class="text-left">
                        {{ $alumno->apellido_paterno }}
                        {{ $alumno->apellido_materno }}
                        {{ $alumno->nombre }}
                    </td>

                    <td class="text-center">
                        {{ $alumno->total_materias ?? 0 }}
                    </td>

                    <td class="text-center">
                        @if ($alumno->promedio_final !== null)
                            <span class="promedio">{{ number_format($alumno->promedio_final, 1) }}</span>
                        @else
                            <span class="sin-promedio">Sin calificaciones</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        No se encontraron alumnos activos para esta licenciatura y generación.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="nota">
        Nota: el promedio se calcula únicamente con calificaciones numéricas registradas en el sistema.
    </p>

    <footer>
        @if ($escuela)
            {{ $escuela->nombre }}.
            C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }},
            Col. {{ $escuela->colonia }},
            C.P. {{ $escuela->codigo_postal }},
            {{ $escuela->ciudad }}, {{ $escuela->estado }}.
        @endif
        <br>
        Documento generado el {{ now()->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i') }}
    </footer>

</body>

</html>
