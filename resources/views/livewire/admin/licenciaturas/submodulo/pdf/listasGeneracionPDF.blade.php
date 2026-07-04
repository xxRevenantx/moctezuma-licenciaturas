<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listas por generación</title>
    <style>
        @page {
            margin: 17mm 11mm 18mm 11mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 9px;
        }

        .page-break {
            page-break-before: always;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header td {
            border: 0;
            vertical-align: middle;
        }

        .logo-cell {
            width: 18%;
            text-align: center;
        }

        .logo {
            width: 25mm;
            height: 20mm;
            object-fit: contain;
        }

        .header-center {
            width: 64%;
            text-align: center;
        }

        .institution {
            color: #006492;
            font-size: 16px;
            font-weight: bold;
        }

        .report-title {
            margin-top: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin: 7px 0 9px;
        }

        .meta td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
        }

        .meta-label {
            width: 18%;
            font-weight: bold;
            background: #eef6f8;
            color: #006492;
        }

        .section-title {
            margin: 8px 0 5px;
            padding: 6px 7px;
            background: #006492;
            color: white;
            font-size: 11px;
            font-weight: bold;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data thead {
            display: table-header-group;
        }

        table.data tr {
            page-break-inside: avoid;
        }

        table.data th,
        table.data td {
            border: .6px solid #94a3b8;
            padding: 4px;
        }

        table.data th {
            background: #88AC2E;
            color: white;
            text-align: center;
            font-size: 8.2px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .empty {
            padding: 16px !important;
            text-align: center;
            color: #64748b;
            font-style: italic;
        }

        .summary {
            margin-top: 8px;
            font-weight: bold;
        }

        .badge-local {
            color: #5b21b6;
            font-weight: bold;
        }

        .badge-foraneo {
            color: #b91c1c;
            font-weight: bold;
        }

        .group-row td {
            background: #eaf3f6;
            color: #006492;
            font-weight: bold;
            padding: 5px 6px !important;
        }

        .stats {
            width: 58%;
            margin: 12px 0 0 auto;
            border-collapse: collapse;
        }

        .stats th,
        .stats td {
            border: .7px solid #94a3b8;
            padding: 5px;
            text-align: center;
        }

        .stats th {
            background: #006492;
            color: white;
        }

        .stats .total-row td {
            background: #eef6f8;
            font-weight: bold;
        }

        .watermark {
            position: fixed;
            top: 34%;
            left: 34%;
            width: 32%;
            opacity: .035;
            z-index: -1;
        }

        .watermark img {
            width: 100%;
        }

        footer {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            border-top: .6px solid #94a3b8;
            padding-top: 3px;
            font-size: 7px;
            text-align: center;
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="watermark">
        @if (file_exists(public_path('storage/letra.png')))
            <img src="{{ public_path('storage/letra.png') }}" alt="Marca de agua">
        @endif
    </div>

    @foreach ($listas as $lista)
        <section class="{{ !$loop->first ? 'page-break' : '' }}">
            <table class="header">
                <tr>
                    <td class="logo-cell">
                        @if (file_exists(public_path('storage/letra2.jpg')))
                            <img class="logo" src="{{ public_path('storage/letra2.jpg') }}"
                                alt="Centro Universitario Moctezuma">
                        @endif
                    </td>
                    <td class="header-center">
                        <div class="institution">CENTRO UNIVERSITARIO MOCTEZUMA</div>
                        <div class="report-title">LISTA DE ALUMNOS POR GENERACIÓN</div>
                    </td>
                    <td class="logo-cell">
                        @if (
                            $lista['licenciatura']->imagen &&
                                file_exists(public_path('storage/licenciaturas/' . $lista['licenciatura']->imagen)))
                            <img class="logo"
                                src="{{ public_path('storage/licenciaturas/' . $lista['licenciatura']->imagen) }}"
                                alt="Licenciatura">
                        @endif
                    </td>
                </tr>
            </table>

            <table class="meta">
                <tr>
                    <td class="meta-label">GENERACIÓN</td>
                    <td>{{ $generacion->generacion }}</td>
                    <td class="meta-label">PROCEDENCIA</td>
                    <td>{{ $procedenciaTexto }}</td>
                </tr>
                <tr>
                    <td class="meta-label">CICLO ESCOLAR</td>
                    <td>{{ $cicloEscolar }}</td>
                    <td class="meta-label">PERIODO</td>
                    <td>{{ $periodoEscolar }}</td>
                </tr>
                <tr>
                    <td class="meta-label">FECHA DE EMISIÓN</td>
                    <td>{{ $fechaEmision->format('d/m/Y') }}</td>
                    <td class="meta-label">TOTAL GENERAL</td>
                    <td>{{ $totalGeneral }}</td>
                </tr>
            </table>

            <div class="section-title">LICENCIATURA EN {{ mb_strtoupper($lista['licenciatura']->nombre) }}</div>

            <table class="data">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>MATRÍCULA</th>
                        <th>NOMBRE COMPLETO</th>
                        <th>PROCEDENCIA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lista['alumnos'] as $alumno)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td class="center">{{ $alumno->matricula }}</td>
                            <td>{{ mb_strtoupper(trim($alumno->apellido_paterno . ' ' . $alumno->apellido_materno . ' ' . $alumno->nombre)) }}
                            </td>
                            <td class="center {{ $alumno->foraneo === 'true' ? 'badge-foraneo' : 'badge-local' }}">
                                {{ $alumno->foraneo === 'true' ? 'FORÁNEO' : 'LOCAL' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty">SIN ALUMNOS REGISTRADOS PARA EL FILTRO SELECCIONADO</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="summary right">LOCALES: {{ $lista['locales'] }} &nbsp;|&nbsp; FORÁNEOS:
                {{ $lista['foraneos'] }} &nbsp;|&nbsp; TOTAL LICENCIATURA: {{ $lista['total'] }}</div>
        </section>
    @endforeach

    <section class="page-break">
        <table class="header">
            <tr>
                <td class="logo-cell">
                    @if (file_exists(public_path('storage/letra2.jpg')))
                        <img class="logo" src="{{ public_path('storage/letra2.jpg') }}"
                            alt="Centro Universitario Moctezuma">
                    @endif
                </td>
                <td class="header-center">
                    <div class="institution">CENTRO UNIVERSITARIO MOCTEZUMA</div>
                    <div class="report-title">LISTA GENERAL DE ALUMNOS DE LA GENERACIÓN</div>
                </td>
                <td class="logo-cell"></td>
            </tr>
        </table>

        <table class="meta">
            <tr>
                <td class="meta-label">GENERACIÓN</td>
                <td>{{ $generacion->generacion }}</td>
                <td class="meta-label">PROCEDENCIA</td>
                <td>{{ $procedenciaTexto }}</td>
            </tr>
            <tr>
                <td class="meta-label">CICLO ESCOLAR</td>
                <td>{{ $cicloEscolar }}</td>
                <td class="meta-label">PERIODO</td>
                <td>{{ $periodoEscolar }}</td>
            </tr>
            <tr>
                <td class="meta-label">FECHA DE EMISIÓN</td>
                <td>{{ $fechaEmision->format('d/m/Y') }}</td>
                <td class="meta-label">TOTAL GENERAL</td>
                <td>{{ $totalGeneral }}</td>
            </tr>
        </table>

        <table class="data">
            <colgroup>
                <col style="width:1%">
                <col style="width:13%">
                <col style="width:27%">
                <col style="width:28%">
                <col style="width:14%">
                <col style="width:13%">
            </colgroup>
            <thead>
                <tr>
                    <th>N.º</th>
                    <th>MATRÍCULA</th>
                    <th>NOMBRE COMPLETO</th>
                    <th>LICENCIATURA</th>
                    <th>GENERACIÓN</th>
                    <th>PROCEDENCIA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($listas as $lista)
                    <tr class="group-row">
                        <td colspan="6">LICENCIATURA EN {{ mb_strtoupper($lista['licenciatura']->nombre) }} — TOTAL:
                            {{ $lista['total'] }}</td>
                    </tr>
                    @forelse ($lista['alumnos'] as $alumno)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td class="center">{{ $alumno->matricula }}</td>
                            <td>{{ mb_strtoupper(trim($alumno->apellido_paterno . ' ' . $alumno->apellido_materno . ' ' . $alumno->nombre)) }}
                            </td>
                            <td>{{ mb_strtoupper($lista['licenciatura']->nombre) }}</td>
                            <td class="center">{{ $generacion->generacion }}</td>
                            <td class="center {{ $alumno->foraneo === 'true' ? 'badge-foraneo' : 'badge-local' }}">
                                {{ $alumno->foraneo === 'true' ? 'FORÁNEO' : 'LOCAL' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">SIN ALUMNOS REGISTRADOS PARA EL FILTRO SELECCIONADO</td>
                        </tr>
                    @endforelse
                @endforeach
            </tbody>
        </table>

        <table class="stats">
            <thead>
                <tr>
                    <th colspan="2">ESTADÍSTICA GENERAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>HOMBRES</td>
                    <td>{{ $totalHombres }}</td>
                </tr>
                <tr>
                    <td>MUJERES</td>
                    <td>{{ $totalMujeres }}</td>
                </tr>
                <tr>
                    <td>LOCALES</td>
                    <td>{{ $totalLocales }}</td>
                </tr>
                <tr>
                    <td>FORÁNEOS</td>
                    <td>{{ $totalForaneos }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL GENERAL</td>
                    <td>{{ $totalGeneral }}</td>
                </tr>
            </tbody>
        </table>
    </section>

    <footer>
        Centro Universitario Moctezuma · Generación {{ $generacion->generacion }} · Locales: {{ $totalLocales }} ·
        Foráneos: {{ $totalForaneos }} · Total: {{ $totalGeneral }}
    </footer>

    <script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
        $pdf->page_text(505, 760, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 7, array(0.35, 0.35, 0.35));
    }
</script>
</body>

</html>
