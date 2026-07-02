<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadística completa de licenciaturas</title>
    <style>
        @page {
            size: letter landscape;
            margin: 12mm 9mm 13mm 9mm;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #1f2937;
        }

        .page-break { page-break-before: always; }

        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 7px;
        }

        .header td { vertical-align: middle; }

        .logo-cell { width: 95px; text-align: center; }

        .logo {
            max-width: 82px;
            max-height: 64px;
        }

        .brand-fallback {
            width: 56px;
            height: 56px;
            line-height: 56px;
            margin: 0 auto;
            border-radius: 50%;
            background: #006492;
            color: #ffffff;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }

        .title-cell { text-align: center; }

        .institution {
            margin: 0;
            color: #006492;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .cct {
            margin-top: 3px;
            font-size: 8px;
            color: #4b5563;
        }

        .report-title {
            margin: 7px 0 0;
            font-size: 16px;
            color: #374151;
            font-weight: bold;
        }

        .cycle {
            margin-top: 2px;
            font-size: 12px;
            color: #111827;
        }

        .date-cell {
            width: 155px;
            text-align: right;
            vertical-align: bottom !important;
            font-size: 7px;
            color: #6b7280;
        }

        .bar {
            background: #006492;
            color: #ffffff;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 0;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data thead { display: table-header-group; }
        table.data tfoot { display: table-row-group; }
        table.data tr { page-break-inside: avoid; }

        table.data th,
        table.data td {
            border: 0.6px solid #ffffff;
            padding: 3px 2px;
            vertical-align: middle;
        }

        table.data th {
            background: #b9935a;
            color: #111111;
            text-align: center;
            font-weight: bold;
        }

        table.data thead tr.sub th {
            background: #d8c09a;
            font-size: 7px;
        }

        table.data td {
            background: #f4e3ca;
            color: #111827;
        }

        table.data tbody tr:nth-child(even) td { background: #efd8b8; }

        .left { text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }

        .active-total { background: #dcedc8 !important; color: #365314 !important; font-weight: bold; }
        .drop-total { background: #fee2e2 !important; color: #991b1b !important; font-weight: bold; }
        .graduate-total { background: #fef3c7 !important; color: #92400e !important; font-weight: bold; }
        .grand-total { background: #dbeafe !important; color: #075985 !important; font-weight: bold; }

        tfoot td {
            background: #d1d5db !important;
            font-weight: bold;
        }

        .summary {
            width: 100%;
            margin-top: 7px;
            border-collapse: separate;
            border-spacing: 5px 0;
        }

        .summary td {
            width: 25%;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            text-align: center;
        }

        .summary .number {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .summary .label {
            color: #4b5563;
            font-size: 7px;
            text-transform: uppercase;
        }

        .criteria {
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #d1d5db;
            font-size: 6.5px;
            color: #6b7280;
        }

        .signature {
            margin-top: 12px;
            text-align: center;
            font-size: 7px;
        }

        .signature-line {
            width: 235px;
            margin: 18px auto 3px;
            border-top: 1px solid #111827;
        }

        .footer {
            position: fixed;
            bottom: -8mm;
            left: 0;
            right: 0;
            text-align: center;
            color: #9ca3af;
            font-size: 6px;
        }

        .col-license { width: 19%; }
        .col-rvoe { width: 9%; }
        .col-mode { width: 10%; }
        .col-generation { width: 7%; }
        .col-term { width: 9%; }
        .col-number { width: 3.4%; }
        .col-final { width: 5%; }
    </style>
</head>
<body>
    <div class="footer">
        {{ $escuela['nombre'] ?? 'Centro Universitario Moctezuma A.C.' }} ·
        {{ $escuela['CCT'] ?? '12PSU0173I' }} · Estadística institucional
    </div>

    @forelse ($reporte['secciones'] as $seccion)
        <div class="{{ !$loop->first ? 'page-break' : '' }}">
            <table class="header">
                <tr>
                    <td class="logo-cell">
                        @if (file_exists(public_path('storage/logo.png')))
                            <img class="logo" src="{{ public_path('storage/logo.png') }}" alt="Logotipo">
                        @elseif (file_exists(public_path('storage/logo-moctezuma.jpg')))
                            <img class="logo" src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logotipo">
                        @else
                            <div class="brand-fallback">CUM</div>
                        @endif
                    </td>
                    <td class="title-cell">
                        <p class="institution">{{ $escuela['nombre'] ?? 'Centro Universitario Moctezuma A.C.' }}</p>
                        <div class="cct">C.C.T. {{ $escuela['CCT'] ?? '12PSU0173I' }}</div>
                        <div class="report-title">Distribución escolar de licenciaturas</div>
                        <div class="cycle">{{ $seccion['ciclo_escolar'] }}</div>
                    </td>
                    <td class="date-cell">
                        Generado: {{ $fechaGeneracion->format('d/m/Y h:i a') }}
                    </td>
                </tr>
            </table>

            <div class="bar">ESTADÍSTICA COMPLETA DE LICENCIATURAS</div>

            <table class="data">
                <thead>
                    <tr>
                        <th rowspan="2" class="col-license">Licenciatura</th>
                        <th rowspan="2" class="col-rvoe">RVOE</th>
                        <th rowspan="2" class="col-mode">Modalidad</th>
                        <th rowspan="2" class="col-generation">Generación</th>
                        <th rowspan="2" class="col-term">Cuatrimestre</th>
                        <th colspan="3">Activos</th>
                        <th colspan="3">Bajas</th>
                        <th colspan="3">Egresados</th>
                        <th rowspan="2" class="col-final">Total</th>
                    </tr>
                    <tr class="sub">
                        @foreach (['H', 'M', 'T', 'H', 'M', 'T', 'H', 'M', 'T'] as $encabezado)
                            <th class="col-number">{{ $encabezado }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($seccion['filas'] as $fila)
                        <tr>
                            <td class="left bold">{{ $fila['licenciatura'] }}</td>
                            <td class="left">{{ $fila['rvoe'] }}</td>
                            <td class="left">{{ $fila['modalidad'] }}</td>
                            <td class="center">{{ $fila['generacion'] }}</td>
                            <td class="center">{{ $fila['cuatrimestre'] }}</td>
                            <td class="center">{{ $fila['activos_hombres'] }}</td>
                            <td class="center">{{ $fila['activos_mujeres'] }}</td>
                            <td class="center active-total">{{ $fila['activos_total'] }}</td>
                            <td class="center">{{ $fila['bajas_hombres'] }}</td>
                            <td class="center">{{ $fila['bajas_mujeres'] }}</td>
                            <td class="center drop-total">{{ $fila['bajas_total'] }}</td>
                            <td class="center">{{ $fila['egresados_hombres'] }}</td>
                            <td class="center">{{ $fila['egresados_mujeres'] }}</td>
                            <td class="center graduate-total">{{ $fila['egresados_total'] }}</td>
                            <td class="center grand-total">{{ $fila['total_general'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="right">TOTAL DEL CICLO</td>
                        <td class="center">{{ $seccion['totales']['activos_hombres'] }}</td>
                        <td class="center">{{ $seccion['totales']['activos_mujeres'] }}</td>
                        <td class="center active-total">{{ $seccion['totales']['activos_total'] }}</td>
                        <td class="center">{{ $seccion['totales']['bajas_hombres'] }}</td>
                        <td class="center">{{ $seccion['totales']['bajas_mujeres'] }}</td>
                        <td class="center drop-total">{{ $seccion['totales']['bajas_total'] }}</td>
                        <td class="center">{{ $seccion['totales']['egresados_hombres'] }}</td>
                        <td class="center">{{ $seccion['totales']['egresados_mujeres'] }}</td>
                        <td class="center graduate-total">{{ $seccion['totales']['egresados_total'] }}</td>
                        <td class="center grand-total">{{ $seccion['totales']['total_general'] }}</td>
                    </tr>
                </tfoot>
            </table>

            <table class="summary">
                <tr>
                    <td>
                        <span class="number" style="color:#15803d;">{{ $seccion['totales']['activos_total'] }}</span>
                        <span class="label">Alumnos activos</span>
                    </td>
                    <td>
                        <span class="number" style="color:#b91c1c;">{{ $seccion['totales']['bajas_total'] }}</span>
                        <span class="label">Bajas</span>
                    </td>
                    <td>
                        <span class="number" style="color:#b45309;">{{ $seccion['totales']['egresados_total'] }}</span>
                        <span class="label">Egresados</span>
                    </td>
                    <td>
                        <span class="number" style="color:#006492;">{{ $seccion['totales']['total_general'] }}</span>
                        <span class="label">Total general</span>
                    </td>
                </tr>
            </table>

            @if ($loop->last)
                <div class="criteria">
                    Criterio de contabilización: baja cuando el registro tiene status inactivo o fecha de baja;
                    egresado cuando no es baja y el campo egresado está activo, la generación está inactiva o concluyó el 9.º cuatrimestre;
                    los registros restantes se contabilizan como activos. Los estados son excluyentes.
                </div>

                @if (!empty($directivo))
                    <div class="signature">
                        <div class="signature-line"></div>
                        <strong>
                            {{ trim(($directivo['titulo'] ?? '') . ' ' . ($directivo['nombre'] ?? '') . ' ' . ($directivo['apellido_paterno'] ?? '') . ' ' . ($directivo['apellido_materno'] ?? '')) }}
                        </strong><br>
                        {{ $directivo['cargo'] ?? '' }}
                    </div>
                @endif
            @endif
        </div>
    @empty
        <table class="header">
            <tr>
                <td class="title-cell">
                    <p class="institution">{{ $escuela['nombre'] ?? 'Centro Universitario Moctezuma A.C.' }}</p>
                    <div class="report-title">Estadística completa de licenciaturas</div>
                </td>
            </tr>
        </table>
        <div class="bar">SIN RESULTADOS</div>
        <p style="text-align:center; margin-top:30px; font-size:12px;">
            No existen alumnos que coincidan con los filtros seleccionados.
        </p>
    @endforelse
</body>
</html>
