<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Lista general de alumnos foráneos por licenciatura</title>

    <style>
        @page {
            margin: 12mm 11mm 19mm 11mm;
        }

        * {
            box-sizing: border-box;
        }



        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .nueva-pagina {
            page-break-before: always;
        }

        .encabezado {
            width: 100%;
            margin: 0 0 4mm;
            border-collapse: collapse;
        }

        .encabezado td {
            padding: 0;
            border: none;
            vertical-align: middle;
        }

        .encabezado-logo {
            width: 15%;
            text-align: center;
        }

        .encabezado-centro {
            width: 70%;
            padding: 0 7mm !important;
            text-align: center;
        }

        .logo {
            display: block;
            width: 22mm;
            height: 22mm;
            margin: 0 auto;
            object-fit: contain;
        }

        .titulo-institucion {
            margin: 0;
            padding: 2.8mm 0;
            color: #4a5568;
            font-size: 18px;
            font-weight: bold;
            line-height: 1.15;
            text-align: center;
            border-top: 1.4px solid #4a5568;
            border-bottom: 1.4px solid #4a5568;
        }

        .informacion {
            width: 100%;
            margin-bottom: 4mm;
            text-align: center;
        }

        .titulo-lista {
            margin: 0 0 1.2mm;
            font-size: 15px;
            font-weight: bold;
            line-height: 1.2;
        }

        .subtitulo {
            margin: 0 0 1mm;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .detalle {
            margin: 0;
            color: #4a5568;
            font-size: 8.5px;
            font-weight: bold;
            line-height: 1.2;
        }

        .resumen-titulo {
            margin: 3mm 0 2mm;
            color: #374151;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }

        .tabla-resumen,
        .tabla-alumnos {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .tabla-resumen thead,
        .tabla-alumnos thead {
            display: table-header-group;
        }

        .tabla-resumen tr,
        .tabla-alumnos tr {
            page-break-inside: avoid;
        }

        .tabla-resumen th,
        .tabla-resumen td,
        .tabla-alumnos th,
        .tabla-alumnos td {
            border: 0.6px solid #7b7b7b;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: middle;
        }

        .tabla-resumen th,
        .tabla-alumnos th {
            padding: 4px 3px;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.15;
            text-align: center;
            background-color: #638acd;
            border-color: #525252;
        }

        .tabla-resumen td,
        .tabla-alumnos td {
            padding: 4px 3px;
            font-size: 10px;
            line-height: 1.2;
            text-align: left;
        }

        .tabla-resumen tfoot td {
            font-size: 8px;
            font-weight: bold;
            background: #eef4ff;
        }

        .generacion-bloque {
            width: 100%;
            margin: 0 0 4mm;
        }

        .generacion-cabecera {
            padding: 5px 4px !important;
            color: #1f2937 !important;
            font-size: 10px !important;
            text-align: left !important;
            background-color: #dbeafe !important;
            border-color: #638acd !important;
        }

        .numero,
        .centrado {
            text-align: center !important;
        }

        .curp {
            font-size: 10px !important;
            letter-spacing: -0.1px;
        }

        .col-numero {
            width: 3%;
        }

        .col-nombre {
            width: 10%;
        }

        .col-paterno {
            width: 10%;
        }

        .col-materno {
            width: 10%;
        }

        .col-curp {
            width: 18%;
        }

        .col-cuatrimestre {
            width: 11%;
        }

        .col-modalidad {
            width: 10%;
        }

        .col-procedencia {
            width: 9%;
        }

        .col-ingreso {
            width: 9%;
        }

        .col-observaciones {
            width: 10%;
        }

        .res-col-numero {
            width: 5%;
        }

        .res-col-licenciatura {
            width: 48%;
        }

        .res-col-generaciones {
            width: 32%;
        }

        .res-col-total {
            width: 15%;
        }

        .watermark {
            position: fixed;
            top: 33%;
            left: 32%;
            z-index: -1000;
            width: 36%;
            text-align: center;
            opacity: 0.045;
        }

        .watermark img {
            width: 100%;
            height: auto;
        }

        footer {
            position: fixed;
            right: 0;
            bottom: -14mm;
            left: 0;
            width: 100%;
            padding: 1.3mm 0;
            color: #1f2937;
            font-size: 7px;
            line-height: 1.15;
            text-align: center;
            border-top: 0.6px solid #4a5568;
            border-bottom: 0.6px solid #4a5568;
        }

        footer p {
            margin: 0;
            padding: 0.4mm 0;
        }
    </style>
</head>

<body>
    <div class="watermark">
        <img src="{{ public_path('storage/letra.png') }}" alt="Marca de agua">
    </div>

    {{-- Resumen general --}}
    <section>
        <table class="encabezado">
            <tr>
                <td class="encabezado-logo">
                    <img class="logo" src="{{ public_path('storage/letra2.jpg') }}"
                        alt="Centro Universitario Moctezuma">
                </td>

                <td class="encabezado-centro">
                    <div class="titulo-institucion">
                        CENTRO UNIVERSITARIO MOCTEZUMA
                    </div>
                </td>

                <td class="encabezado-logo">
                    <img class="logo" src="{{ public_path('storage/letra2.jpg') }}"
                        alt="Centro Universitario Moctezuma">
                </td>
            </tr>
        </table>

        <div class="informacion">
            <p class="titulo-lista">
                LISTA GENERAL DE ALUMNOS FORÁNEOS
            </p>

            <p class="subtitulo">
                POR LICENCIATURA
            </p>

            <p class="detalle">
                Solo alumnos activos · Todas las generaciones · TOTAL GENERAL: {{ $totalGeneral }}
            </p>
        </div>

        <p class="resumen-titulo">RESUMEN GENERAL</p>

        <table class="tabla-resumen">
            <colgroup>
                <col class="res-col-numero">
                <col class="res-col-licenciatura">
                <col class="res-col-generaciones">
                <col class="res-col-total">
            </colgroup>

            <thead>
                <tr>
                    <th>#</th>
                    <th>LICENCIATURA</th>
                    <th>GENERACIONES INCLUIDAS</th>
                    <th>TOTAL DE FORÁNEOS</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($resumen as $fila)
                    <tr>
                        <td class="numero">{{ $loop->iteration }}</td>
                        <td>{{ mb_strtoupper($fila['licenciatura']) }}</td>
                        <td class="centrado">{{ mb_strtoupper($fila['generaciones']) }}</td>
                        <td class="centrado">{{ $fila['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">TOTAL GENERAL</td>
                    <td class="centrado">{{ $totalGeneral }}</td>
                </tr>
            </tfoot>
        </table>
    </section>

    {{-- Una sección por licenciatura --}}
    @foreach ($listas as $lista)
        @php
            $licenciatura = $lista['licenciatura'];
            $generaciones = $lista['generaciones'];
        @endphp

        <section class="nueva-pagina">
            <table class="encabezado">
                <tr>
                    <td class="encabezado-logo">
                        <img class="logo" src="{{ public_path('storage/letra2.jpg') }}"
                            alt="Centro Universitario Moctezuma">
                    </td>

                    <td class="encabezado-centro">
                        <div class="titulo-institucion">
                            CENTRO UNIVERSITARIO MOCTEZUMA
                        </div>
                    </td>

                    <td class="encabezado-logo">
                        @if ($licenciatura->imagen && file_exists(public_path('storage/licenciaturas/' . $licenciatura->imagen)))
                            <img class="logo"
                                src="{{ public_path('storage/licenciaturas/' . $licenciatura->imagen) }}"
                                alt="Logo de la licenciatura">
                        @endif
                    </td>
                </tr>
            </table>

            <div class="informacion">
                <p class="titulo-lista">
                    LISTA GENERAL DE ALUMNOS FORÁNEOS
                </p>

                <p class="subtitulo">
                    LICENCIATURA EN {{ $licenciatura->nombre }}
                </p>

                <p class="detalle">
                    Alumnos activos · Todas las generaciones · TOTAL: {{ $lista['total'] }}
                </p>
            </div>

            @foreach ($generaciones as $grupo)
                @php
                    $generacion = $grupo['generacion'];
                    $matricula = $grupo['alumnos'];
                @endphp

                <div class="generacion-bloque">
                    <table class="tabla-alumnos">
                        <colgroup>
                            <col class="col-numero">
                            <col class="col-nombre">
                            <col class="col-paterno">
                            <col class="col-materno">
                            <col class="col-curp">
                            <col class="col-cuatrimestre">
                            <col class="col-modalidad">
                            <col class="col-procedencia">
                            <col class="col-ingreso">
                            <col class="col-observaciones">
                        </colgroup>

                        <thead>
                            <tr>
                                <th colspan="10" class="generacion-cabecera">
                                    GENERACIÓN: {{ mb_strtoupper($generacion->generacion ?? 'SIN GENERACIÓN') }}
                                    &nbsp;·&nbsp;
                                    TOTAL: {{ $grupo['total'] }}
                                </th>
                            </tr>

                            <tr>
                                <th>#</th>
                                <th>NOMBRE(S)</th>
                                <th>APELLIDO PATERNO</th>
                                <th>APELLIDO MATERNO</th>
                                <th>CURP</th>
                                <th>CUATRIMESTRE</th>
                                <th>MODALIDAD</th>
                                <th>PROCEDENCIA</th>
                                <th>MES DE INGRESO</th>
                                <th>OBSERVACIONES</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($matricula as $student)
                                <tr>
                                    <td class="numero">{{ $loop->iteration }}</td>
                                    <td>{{ mb_strtoupper($student->nombre ?? '') }}</td>
                                    <td>{{ mb_strtoupper($student->apellido_paterno ?? '') }}</td>
                                    <td>{{ mb_strtoupper($student->apellido_materno ?? '') }}</td>
                                    <td class="curp">{{ mb_strtoupper($student->CURP ?? '-') }}</td>
                                    <td>
                                        {{ mb_strtoupper($student->cuatrimestre->nombre_cuatrimestre ?? ($student->cuatrimestre->cuatrimestre ?? '-')) }}
                                    </td>
                                    <td>{{ mb_strtoupper($student->modalidad->nombre ?? '-') }}</td>
                                    <td class="centrado">FORÁNEO</td>
                                    <td class="centrado">SEPTIEMBRE</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </section>
    @endforeach

    <footer>
        @if ($escuela)
            <p>
                {{ $escuela->nombre }} C.C.T. {{ $escuela->CCT }}.
                C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }},
                Col. {{ $escuela->colonia }}, C.P. {{ $escuela->codigo_postal }},
                Cd. {{ $escuela->ciudad }}, {{ $escuela->estado }}.
            </p>
        @endif

        <p>
            Fecha de expedición:
            {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}
        </p>
    </footer>
</body>

</html>
