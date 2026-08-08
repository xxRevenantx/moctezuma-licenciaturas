<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de entrega de listas a profesores</title>
    <style>
        @page {
            size: letter landscape;
            margin: 22px 24px 32px 24px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 8px;
            line-height: 1.25;
        }

        .header-table,
        .meta-table,
        .summary-table,
        .teacher-table,
        .delivery-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td { vertical-align: middle; }
        .logo-cell { width: 18%; }
        .title-cell { width: 64%; text-align: center; }
        .cct-cell { width: 18%; text-align: right; color: #64748b; font-weight: bold; }
        .logo { max-width: 135px; max-height: 58px; }

        .institution {
            color: #006492;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: .2px;
            text-transform: uppercase;
        }

        .document-title {
            margin-top: 3px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .meta-table {
            margin-top: 8px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        .meta-table td {
            padding: 5px 7px;
            border-right: 1px solid #e2e8f0;
        }

        .meta-table td:last-child { border-right: 0; }
        .meta-label { color: #64748b; font-size: 6.7px; font-weight: 800; text-transform: uppercase; }
        .meta-value { margin-top: 1px; color: #1f2937; font-size: 7.5px; font-weight: 800; text-transform: uppercase; }

        .summary-table {
            margin-top: 8px;
            border-spacing: 5px 0;
            border-collapse: separate;
        }

        .summary-box {
            padding: 6px 5px;
            text-align: center;
            background: #eaf4f8;
            color: #006492;
            border: 1px solid #dbeafe;
            border-radius: 6px;
        }

        .summary-box.total {
            background: #88AC2E;
            color: #fff;
            border-color: #88AC2E;
        }

        .summary-number { font-size: 13px; font-weight: 900; }
        .summary-label { margin-top: 1px; font-size: 6.2px; font-weight: 800; text-transform: uppercase; letter-spacing: .3px; }

        .teacher-block { margin-top: 10px; }
        .teacher-head {
            width: 100%;
            border-collapse: collapse;
            page-break-after: avoid;
        }
        .teacher-head td { padding: 6px 8px; color: #fff; font-weight: 800; }
        .teacher-name { width: 72%; background: #006492; font-size: 8.6px; text-transform: uppercase; }
        .teacher-total { width: 28%; background: #88AC2E; text-align: center; font-size: 7px; }

        .teacher-table {
            border-left: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            table-layout: fixed;
        }

        .teacher-table thead { display: table-header-group; }
        .teacher-table tr { page-break-inside: avoid; }

        .teacher-table th {
            padding: 4px 4px;
            background: #e2e8f0;
            color: #334155;
            border-bottom: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            text-align: center;
            font-size: 6.2px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .teacher-table td {
            padding: 4px 4px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .teacher-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .center { text-align: center; vertical-align: middle !important; }
        .check {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1.2px solid #334155;
            vertical-align: middle;
        }
        .observation-line {
            display: block;
            margin-top: 7px;
            border-bottom: 1px solid #94a3b8;
            height: 1px;
        }

        .delivery-table {
            border: 1px solid #cbd5e1;
            border-top: 0;
            page-break-inside: avoid;
        }

        .delivery-table td {
            padding: 6px 7px;
            border-right: 1px solid #e2e8f0;
            font-size: 7px;
            font-weight: 800;
            vertical-align: middle;
        }

        .delivery-table td:last-child { border-right: 0; }

        .empty {
            margin-top: 25px;
            padding: 24px;
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            color: #64748b;
            text-align: center;
            font-size: 10px;
            font-weight: 800;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -22px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            color: #64748b;
            font-size: 6.5px;
        }
        .footer-left { float: left; }
        .footer-right { float: right; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    @php
        $logo = null;
        foreach ([
            public_path('storage/letra2.jpg'),
            public_path('storage/logo.png'),
            public_path('storage/logo-moctezuma.jpg'),
            public_path('storage/moctezuma.png'),
        ] as $candidate) {
            if (is_file($candidate)) {
                $logo = $candidate;
                break;
            }
        }
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if ($logo)
                    <img class="logo" src="{{ $logo }}" alt="Logo institucional">
                @endif
            </td>
            <td class="title-cell">
                <div class="institution">{{ $contexto['escuela']?->nombre ?? 'Centro Universitario Moctezuma' }}</div>
                <div class="document-title">Control de entrega de listas a profesores</div>
            </td>
            <td class="cct-cell">C.C.T. {{ $contexto['escuela']?->CCT ?? '12PSU0173I' }}</td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 16%;">
                <div class="meta-label">Ciclo escolar</div>
                <div class="meta-value">{{ $contexto['ciclo_escolar'] ?: 'Sin configurar' }}</div>
            </td>
            <td style="width: 13%;">
                <div class="meta-label">Periodo escolar</div>
                <div class="meta-value">{{ $contexto['periodo_escolar'] ?: 'Sin configurar' }}</div>
            </td>
            <td style="width: 18%;">
                <div class="meta-label">Fecha de generación</div>
                <div class="meta-value">{{ $fecha_emision->format('d/m/Y H:i') }}</div>
            </td>
            <td style="width: 25%;">
                <div class="meta-label">Licenciatura</div>
                <div class="meta-value">{{ $filtros_texto['licenciatura'] }}</div>
            </td>
            <td style="width: 14%;">
                <div class="meta-label">Modalidad</div>
                <div class="meta-value">{{ $filtros_texto['modalidad'] }}</div>
            </td>
            <td style="width: 14%;">
                <div class="meta-label">Generación</div>
                <div class="meta-value">{{ $filtros_texto['generacion'] }}</div>
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-box">
                <div class="summary-number">{{ $resumen['profesores'] }}</div>
                <div class="summary-label">Profesores</div>
            </td>
            <td class="summary-box">
                <div class="summary-number">{{ $resumen['materias_grupos'] }}</div>
                <div class="summary-label">Materias / grupos</div>
            </td>
            <td class="summary-box">
                <div class="summary-number">{{ $resumen['asistencias'] }}</div>
                <div class="summary-label">Listas asistencia</div>
            </td>
            <td class="summary-box">
                <div class="summary-number">{{ $resumen['evaluaciones'] }}</div>
                <div class="summary-label">Listas evaluación</div>
            </td>
            <td class="summary-box total">
                <div class="summary-number">{{ $resumen['documentos'] }}</div>
                <div class="summary-label">Documentos a entregar</div>
            </td>
        </tr>
    </table>

    @forelse ($profesores as $profesor)
        <div class="teacher-block">
            <table class="teacher-head">
                <tr>
                    <td class="teacher-name">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}. {{ $profesor['nombre'] }}
                    </td>
                    <td class="teacher-total">
                        {{ $profesor['total_listas'] }} materias/grupos · {{ $profesor['total_documentos'] }} documentos
                    </td>
                </tr>
            </table>

            <table class="teacher-table">
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 20%;">Materia</th>
                        <th style="width: 19%;">Licenciatura</th>
                        <th style="width: 10%;">Modalidad</th>
                        <th style="width: 6%;">Cuatr.</th>
                        <th style="width: 9%;">Generación</th>
                        <th style="width: 6%;">Asistencia</th>
                        <th style="width: 6%;">Evaluación</th>
                        <th style="width: 20%;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($profesor['registros'] as $registro)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $registro->materia }}</td>
                            <td>{{ $registro->licenciatura }}</td>
                            <td class="center">{{ $registro->modalidad }}</td>
                            <td class="center">{{ $registro->cuatrimestre }}°</td>
                            <td class="center">{{ $registro->generacion }}</td>
                            <td class="center"><span class="check"></span></td>
                            <td class="center"><span class="check"></span></td>
                            <td><span class="observation-line"></span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="delivery-table">
                <tr>
                    <td style="width: 28%;"><span class="check"></span>&nbsp;&nbsp; PAQUETE COMPLETO ENTREGADO</td>
                    <td style="width: 20%;">FECHA: ____________________</td>
                    <td style="width: 52%;">RECIBIÓ / FIRMA: __________________________________________________________</td>
                </tr>
            </table>
        </div>
    @empty
        <div class="empty">
            No hay profesores con materias en horario para el ciclo, periodo y filtros seleccionados.
        </div>
    @endforelse

    <div class="footer">
        <span class="footer-left">Control de entrega · {{ $contexto['ciclo_escolar'] ?: 'Sin ciclo' }} · {{ $contexto['periodo_escolar'] ?: 'Sin periodo' }}</span>
        <span class="footer-right">Página <span class="page-number"></span></span>
    </div>
</body>
</html>
