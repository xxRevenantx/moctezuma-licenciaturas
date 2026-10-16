<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Lista general</title>
    <style>
        @page {
            margin: 12mm 10mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #203149;
            font-size: {{ count($columnas) > 11 ? '6.4' : '8' }}px;
        }

        header {
            margin-bottom: 10px;
        }

        .encabezado {
            width: 100%;
            border-collapse: collapse;

        }

        .encabezado td {
            border: 0;
            text-align: center;
            vertical-align: middle;
        }

        .logo {
            max-width: 100px;
            max-height: 100px;
        }

        .nombre {
            font-size: 15px;
            font-weight: bold;
            color: #006492;
            border-bottom: 1px solid #006492;
        }

        .direccion {
            font-size: 9px;
            color: #64748b;
        }

        .titulo {
            font-size: 13px;
            margin: 7px 0 3px;
            font-weight: bold;
            text-align: center;
        }

        .grupo {
            text-align: center;
            font-weight: bold;
            color: #006492;
            font-size: 11px;
            margin: 2px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 8px;
            margin: 3px 0 8px;
        }

        .data {
            width: 100%;
            border-collapse: collapse;

            position: relative;
        }

        .data thead {
            display: table-header-group;
        }

        .data tr {
            page-break-inside: avoid;
        }

        .data th {
            padding: 5px 2px;
            background-color: #006492;
            color: #fff;
            border: 1px solid #a0b5c3;
            overflow-wrap: anywhere;
            font-weight: bold;
        }

        .data td {
            border: 1px solid #a9b8c8;
            padding: 3px 3px;
            font-size: 11px;
            vertical-align: middle;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }

        .data tbody tr:nth-child(even) td {
            background: #f1f7f9;
        }

        .total {
            text-align: right;
            color: #006492;
            font-weight: bold;
            margin-top: 6px;
        }

        .separacion {
            page-break-before: always;
        }

        footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            border-top: 1px solid #ccd8df;
            text-align: center;
            font-size: 9px;
            font-style: italic;
            color: #526477;
        }

        .watermark {
            position: fixed;
            left: 29%;
            top: 36%;
            width: 42%;
            opacity: 0.055;
            z-index: -1;
        }

        .watermark img {
            width: 100%;
        }
    </style>
</head>

<body>
    @if ($institucion['agua'])
        <div class="watermark"><img src="{{ $institucion['agua'] }}" alt=""></div>
    @endif
    <footer>CENTRO UNIVERSITARIO MOCTEZUMA A.C. · {{ $fecha }} </footer>
    @foreach ($grupos as $index => $grupo)
        <section class="{{ $index ? 'separacion' : '' }}">
            <header>
                <table class="encabezado">
                    <tr>
                        <td style="width:20%">
                            @if ($institucion['logo'])
                                <img class="logo" src="{{ $institucion['logo'] }}" alt="Logo">
                            @endif
                        </td>
                        <td style="width:60%">
                            <div class="nombre">
                                {{ mb_strtoupper($institucion['escuela']->nombre ?? 'CENTRO UNIVERSITARIO MOCTEZUMA') }}
                            </div>
                            <div class="direccion">{{ $institucion['escuela']->calle ?? '' }}
                                {{ $institucion['escuela']->no_exterior ?? '' }} ·
                                {{ $institucion['escuela']->ciudad ?? '' }} ·
                                {{ $institucion['escuela']->telefono ?? '' }}</div>
                        </td>
                        <td style="width:20%">
                            @if ($logos[$index])
                                <img class="logo" src="{{ $logos[$index] }}" alt="Licenciatura">
                            @endif
                        </td>
                    </tr>
                </table>
                <div class="titulo">{{ $filtros['titulo'] ?? 'LISTA DE GRUPO' }}</div>
                <div class="grupo">{{ mb_strtoupper($grupo['titulo']) }}</div>
                @if ($filtros['ciclo_escolar'] ?? null)
                    <div class="subtitle">CICLO ESCOLAR: {{ $filtros['ciclo_escolar'] }}</div>
                @endif
                @if ($filtros['subtitulo'] ?? null)
                    <div class="subtitle">{{ $filtros['subtitulo'] }}</div>
                @endif
            </header>
            <table class="data">
                <thead>
                    <tr>
                        @foreach ($columnas as $titulo)
                            <th>{{ mb_strtoupper($titulo) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grupo['filas'] as $fila)
                        <tr>
                            @foreach ($fila as $valor)
                                <td>{{ $valor }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="total">TOTAL: {{ count($grupo['alumnos']) }} ALUMNOS</p>
        </section>
    @endforeach
</body>

</html>
