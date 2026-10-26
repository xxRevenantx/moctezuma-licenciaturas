<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Control documental</title>
    <style>
        @page { margin: 22px 24px 28px; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 8.5px; }
        .header { border-bottom: 4px solid #88AC2E; padding-bottom: 8px; margin-bottom: 10px; }
        .title { margin: 0; color: #006492; font-size: 17px; text-align: center; }
        .subtitle { margin: 4px 0 0; text-align: center; color: #526071; font-size: 9px; }
        .summary { width: 100%; margin: 0 0 10px; border-collapse: separate; border-spacing: 5px 0; }
        .summary td { border: 1px solid #d9e1e8; border-radius: 6px; padding: 7px; text-align: center; background: #f7fafc; }
        .summary strong { display: block; color: #006492; font-size: 14px; }
        table.report { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .report th { background: #006492; color: #fff; padding: 5px 3px; border: 1px solid #075779; font-size: 7.5px; }
        .report td { border: 1px solid #dbe3ea; padding: 4px 3px; vertical-align: middle; }
        .report tr:nth-child(even) td { background: #f8fafc; }
        .generation-title { margin: 12px 0 7px; padding: 7px 9px; background: #eaf2d8; border-left: 4px solid #88AC2E; color: #38520d; font-size: 10px; font-weight: bold; page-break-after: avoid; }
        .career-title { margin: 11px 0 5px; padding: 7px 9px; background: #eaf4f9; border-left: 4px solid #006492; color: #006492; font-weight: bold; font-size: 10px; page-break-after: avoid; }
        .career-meta { margin: 0 0 7px; color: #526071; font-size: 8px; page-break-after: avoid; }
        .career-block { margin-bottom: 12px; }
        .report thead { display: table-header-group; }
        .report tr { page-break-inside: avoid; }
        /* Anchos: 3 + 23 + 8 + 7 + 7 + (6 x 7) + 10 = 100 %.
           Mantener la tabla dentro de la hoja carta horizontal en Dompdf. */
        .name { width: 23%; }
        .mat { width: 8%; text-align: center; }
        .status { width: 7%; text-align: center; }
        .foreign { width: 7%; text-align: center; white-space: nowrap; }
        .doc { width: 7%; text-align: center; }
        .progress { width: 10%; text-align: center; }
        .foreign-yes { color: #006492; font-weight: bold; }
        .foreign-no { color: #526071; font-weight: bold; }
        .ok { color: #147a42; font-weight: bold; }
        .no { color: #b42318; font-weight: bold; }
        .bar { height: 5px; width: 100%; background: #e5e7eb; border-radius: 4px; overflow: hidden; margin-top: 3px; }
        .bar span { display: block; height: 5px; background: #88AC2E; }
        .footer { position: fixed; bottom: -17px; left: 0; right: 0; text-align: center; color: #6b7280; font-size: 7px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">CONTROL DOCUMENTAL DE IDENTIDAD</h1>
        <p class="subtitle">
            Licenciatura: <strong>{{ $licenciaturaNombre }}</strong> ·
            Generación: <strong>{{ $generacionNombre }}</strong> ·
            Estado: <strong>{{ ucfirst($estado) }}</strong> ·
            Fecha: {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>

    <table class="summary">
        <tr>
            <td><strong>{{ $resumen['alumnos'] }}</strong>Alumnos</td>
            <td><strong>{{ $resumen['completos'] }}</strong>Expedientes completos</td>
            <td><strong>{{ $resumen['con_documentos'] }}</strong>Con documentación</td>
            <td><strong>{{ $resumen['sin_documentos'] }}</strong>Sin documentación</td>
        </tr>
    </table>

    @forelse ($secciones as $generacion)
        <div class="generation-title">Generación: {{ $generacion['generacion'] }}</div>

        @foreach ($generacion['licenciaturas'] as $licenciatura)
            <div class="career-block">
                <div class="career-title">Licenciatura: {{ $licenciatura['nombre'] }}</div>
                <div class="career-meta">
                    {{ $licenciatura['resumen']['alumnos'] }} alumnos ·
                    {{ $licenciatura['resumen']['completos'] }} expedientes completos ·
                    {{ $licenciatura['resumen']['con_documentos'] }} con documentación ·
                    {{ $licenciatura['resumen']['sin_documentos'] }} sin documentación
                </div>
                <table class="report">
                    <thead>
                        <tr>
                            <th style="width:3%">#</th>
                            <th class="name">Alumno</th>
                            <th class="mat">Matrícula</th>
                            <th class="status">Estado</th>
                            <th class="foreign">Foráneo</th>
                            @foreach ($tipos as $config)
                                <th class="doc">{{ $config['label'] }}</th>
                            @endforeach
                            <th class="progress">Avance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($licenciatura['alumnos'] as $alumno)
                            <tr>
                                <td style="text-align:center">{{ $loop->iteration }}</td>
                                <td class="name">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                                <td class="mat">{{ $alumno->matricula ?: '—' }}</td>
                                <td class="status">
                                    @if ($alumno->status === 'false') BAJA
                                    @elseif ($alumno->egresado === 'true') EGRESADO
                                    @else ACTIVO
                                    @endif
                                </td>
                                <td class="foreign">
                                    @if ($alumno->foraneo === 'true')
                                        <span class="foreign-yes">SÍ</span>
                                    @elseif ($alumno->foraneo === 'false')
                                        <span class="foreign-no">NO</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                @foreach ($tipos as $tipo => $config)
                                    <td class="doc {{ data_get($alumno->documentos_estado, $tipo) ? 'ok' : 'no' }}">
                                        {{ data_get($alumno->documentos_estado, $tipo) ? 'SÍ' : 'NO' }}
                                    </td>
                                @endforeach
                                <td class="progress">
                                    <strong>{{ $alumno->documentos_entregados }}/{{ $alumno->documentos_total }}</strong>
                                    <div class="bar"><span style="width: {{ $alumno->documentos_porcentaje }}%"></span></div>
                                    {{ $alumno->documentos_porcentaje }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @empty
        <table class="report"><tbody>
            <tr><td style="padding:20px; text-align:center">No hay alumnos con los filtros seleccionados.</td></tr>
        </tbody></table>
    @endforelse

    <div class="footer">Los indicadores se calculan verificando tanto el registro en la base de datos como la existencia física del PDF privado.</div>
</body>
</html>
