<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $tituloLista }} | Todas las listas</title>

    <style>
        @page {
            margin: 10px 45px 35px 45px;
        }

        * {
            box-sizing: border-box;
        }


        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
        }

        .nueva-pagina {
            page-break-before: always;
        }

        .lista {
            width: 100%;
        }

        .encabezado {
            width: 100%;
            margin: 0 0 4mm;
            border-collapse: collapse;
            table-layout: fixed;
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
            margin-bottom: 3mm;
            text-align: center;
            font-size: 20px;
        }

        .titulo-lista {
            margin: 0 0 1.2mm;
            font-size: 15px;
            font-weight: bold;
            line-height: 1.2;
        }

        .subtitulo {
            margin: 0 0 0.8mm;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .detalle-lista {
            margin: 0 0 0.8mm;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.2;
        }

        .filtro {
            margin: 0;
            color: #4a5568;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
        }

        .tabla-alumnos {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        .tabla-alumnos thead {
            display: table-header-group;
        }

        .tabla-alumnos tr {
            page-break-inside: avoid;
        }

        .tabla-alumnos th,
        .tabla-alumnos td {
            border: 0.6px solid #7b7b7b;
            white-space: normal;
            /* word-wrap: break-word; */
            /* overflow-wrap: break-word; */
            vertical-align: middle;
        }

        .tabla-alumnos th {
            padding: 4px 2px;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.15;
            text-align: center;
            background-color: #638acd;
            border-color: #525252;
        }

        .tabla-alumnos td {
            padding: 4px 3px;
            font-size: 11px;
            line-height: 1.2;
            text-align: left;
        }

        .numero,
        .centrado {
            text-align: center !important;
        }

        .curp {
            font-size: 11px !important;
            letter-spacing: -0.1px;
        }

        .col-numero {
            width: 3%;
        }

        .col-matricula {
            width: 8%;
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
            width: 17%;
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
            width: 11%;
        }

        .watermark {
            position: fixed;
            top: 33%;
            left: 32%;
            z-index: -1000;
            width: 36%;
            text-align: center;
            opacity: 0.05;
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

    @foreach ($listas as $lista)
        @php
            $esListaGeneral = $lista['tipo'] === 'generacion';
            $generacion = $lista['generacion'];
            $modalidad = $lista['modalidad'];
            $matricula = $lista['alumnos'];
        @endphp

        <div class="lista {{ !$loop->first ? 'nueva-pagina' : '' }}">
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
                        @if ($licenciatura_nombre?->imagen && file_exists(public_path('storage/licenciaturas/' . $licenciatura_nombre->imagen)))
                            <img class="logo"
                                src="{{ public_path('storage/licenciaturas/' . $licenciatura_nombre->imagen) }}"
                                alt="Logo de la licenciatura">
                        @endif
                    </td>
                </tr>
            </table>

            <div class="informacion">
                <p class="titulo-lista">{{ $tituloLista }}</p>

                <p class="subtitulo">
                    LICENCIATURA EN {{ $licenciatura_nombre->nombre }}
                </p>

                <p class="detalle-lista">
                    GENERACIÓN: {{ $generacion->generacion }}

                    @unless ($esListaGeneral)
                        &nbsp;·&nbsp;
                        MODALIDAD: {{ mb_strtoupper($modalidad?->nombre ?? 'SIN MODALIDAD') }}
                    @endunless
                </p>

                <p class="filtro">
                    {{ $esListaGeneral ? 'Lista general de la generación' : 'Lista por modalidad' }}
                    &nbsp;·&nbsp;
                    TOTAL: {{ $matricula->count() }}
                </p>
            </div>

            @if ($esListaGeneral)
                <table class="tabla-alumnos">
                    <colgroup>
                        <col class="col-numero">
                        <col class="col-nombre">
                        <col class="col-paterno">
                        <col class="col-materno">
                        <col class="col-curp">
                        <col class="col-cuatrimestre">
                        <col class="col-modalidad">
                        <col class="col-ingreso">
                        <col class="col-observaciones">
                    </colgroup>

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

                                <td class="centrado">SEPTIEMBRE</td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <table class="tabla-alumnos">
                    <colgroup>
                        <col class="col-numero">
                        <col class="col-matricula">
                        <col class="col-nombre">
                        <col class="col-paterno">
                        <col class="col-materno">
                        <col class="col-curp">
                        <col class="col-cuatrimestre">
                        <col class="col-ingreso">
                        <col class="col-observaciones">
                    </colgroup>

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>MATRÍCULA</th>
                            <th>NOMBRE(S)</th>
                            <th>APELLIDO PATERNO</th>
                            <th>APELLIDO MATERNO</th>
                            <th>CURP</th>
                            <th>CUATRIMESTRE</th>
                            <th>MES DE INGRESO</th>
                            <th>OBSERVACIONES</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($matricula as $student)
                            <tr>
                                <td class="numero">{{ $loop->iteration }}</td>
                                <td>{{ mb_strtoupper($student->matricula ?? '-') }}</td>
                                <td>{{ mb_strtoupper($student->nombre ?? '') }}</td>
                                <td>{{ mb_strtoupper($student->apellido_paterno ?? '') }}</td>
                                <td>{{ mb_strtoupper($student->apellido_materno ?? '') }}</td>
                                <td class="curp">{{ mb_strtoupper($student->CURP ?? '-') }}</td>
                                <td>
                                    {{ mb_strtoupper($student->cuatrimestre->nombre_cuatrimestre ?? ($student->cuatrimestre->cuatrimestre ?? '-')) }}
                                </td>

                                <td class="centrado">SEPTIEMBRE</td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

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
