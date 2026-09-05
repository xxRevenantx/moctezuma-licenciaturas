<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Horario General Semiescolarizado</title>
    <style>
        @page {
            margin: 78px 20px 34px 20px;
        }

        /* Dompdf 3.x: no usar box-sizing global ni page-break-inside:avoid alrededor de tablas largas. */
        @font-face {
            font-family: 'calibri';
            src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'calibri';
            font-weight: 700;
            src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
        }

        body {
            margin: 0;
            font-family: calibri, DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            font-size: 9px;
        }

        header {
            position: fixed;
            top: -67px;
            left: 0;
            right: 0;
            height: 58px;
            border-bottom: 2px solid #006492;
        }

        footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -25px;
            height: 18px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 7.5px;
            padding-top: 5px;
        }

        .logo-left {
            position: absolute;
            left: 2px;
            top: 7px;
            height: 45px;
        }

        .logo-right {
            position: absolute;
            right: 2px;
            top: 8px;
            height: 42px;
        }

        .head-center {
            text-align: center;
            padding: 5px 90px 0;
        }

        .school {
            font-size: 16px;
            font-weight: 700;
            color: #0f355e;
            letter-spacing: .3px;
        }

        .title {
            margin-top: 1px;
            font-size: 11px;
            font-weight: 700;
        }

        .meta {
            margin-top: 3px;
            font-size: 8px;
            color: #475569;
        }

        .footer-left {
            float: left;
            width: 78%;
        }

        .footer-right {
            float: right;
            width: 20%;
            text-align: right;
        }

        .page-number:after {
            content: "Página " counter(page);
        }

        .watermark {
            position: fixed;
            left: 365px;
            top: 205px;
            width: 500px;
            text-align: center;
            opacity: .028;
            z-index: -1;
        }

        .watermark img {
            width: 100%;
        }

        .section-title {
            margin: 0 0 6px;
            padding: 5px 8px;
            background: #edf7fb;
            border-left: 4px solid #006492;
            font-size: 10px;
            font-weight: 700;
            color: #0f355e;
        }

        .schedule {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .schedule th,
        .schedule td {
            border: 1px solid #64748b;
            text-align: center;
            vertical-align: middle;
            padding: 3px 2px;
            word-wrap: break-word;
            white-space: normal;
            height: auto;
        }

        .schedule tr {
            height: auto;
        }

        .schedule thead th {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 700;
        }

        .hour {
            width: 66px;
            font-weight: 700;
            background: #f8fafc;
            font-size: 7.5px;
        }

        /*
         * IMPORTANTE PARA DOMPDF 3.x:
         * dentro de celdas no usamos DIV/BLOCK. Dompdf puede calcular la altura
         * de un bloque interno tomando como referencia la altura de la pagina,
         * haciendo que cada fila ocupe casi una hoja completa.
         */
        .group-name,
        .group-meta,
        .subject,
        .teacher,
        .pill {
            display: inline;
            margin: 0;
            padding: 0;
            height: auto;
        }

        .group-name {
            font-weight: 700;
            line-height: 1.05;
        }

        .group-meta {
            color: #475569;
            font-size: 6.8px;
            font-weight: 400;
            line-height: 1.05;
        }

        .subject {
            font-weight: 700;
            line-height: 1.08;
        }

        .teacher {
            font-size: 6.8px;
            line-height: 1.05;
            font-style: italic;
        }

        .empty {
            background: #fff;
        }

        .receso-time {
            background: #e2e8f0;
            font-weight: 700;
        }

        .receso {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            letter-spacing: 6px;
            font-size: 8px;
        }

        .page-break {
            page-break-before: always;
        }

        .block+.block {
            margin-top: 8px;
        }

        .legible-block+.legible-block {
            page-break-before: always;
        }

        .compacto .schedule th,
        .compacto .schedule td {
            padding: 2.2px 1.2px;
        }

        .compacto .group-name {
            font-size: 6.6px;
        }

        .compacto .group-meta {
            font-size: 5.7px;
        }

        .compacto .subject {
            font-size: 6.2px;
        }

        .compacto .teacher {
            font-size: 5.4px;
        }

        .compacto .hour {
            width: 58px;
            font-size: 6.4px;
        }

        .legible .group-name {
            font-size: 8px;
        }

        .legible .group-meta {
            font-size: 6.8px;
        }

        .legible .subject {
            font-size: 7.8px;
        }

        .legible .teacher {
            font-size: 6.5px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: top;
            height: auto;
            white-space: normal;
            word-wrap: break-word;
        }

        .summary-table tr {
            height: auto;
        }

        .summary-table th {
            background: #0f355e;
            color: #fff;
            font-size: 8px;
            text-transform: uppercase;
        }

        .summary-table td {
            font-size: 7.5px;
        }

        .pill {
            border: 0;
            background: transparent;
            line-height: 1.18;
        }

        .summary-total {
            margin-top: 6px;
            padding: 6px;
            background: #edf7fb;
            border: 1px solid #b8dbea;
            text-align: right;
            font-weight: 700;
        }
    </style>
</head>

<body class="{{ $modo }}">
    @php
        $pastel = function (?string $hex, float $mezcla = 0.78) {
            $hex = ltrim((string) ($hex ?: '#cbd5e1'), '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
                $hex = 'cbd5e1';
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $r = (int) round($r + (255 - $r) * $mezcla);
            $g = (int) round($g + (255 - $g) * $mezcla);
            $b = (int) round($b + (255 - $b) * $mezcla);
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };
        $esFinReceso = function ($hora) {
            $partes = array_map('trim', explode('-', strtolower((string) $hora), 2));
            if (count($partes) !== 2) {
                return false;
            }
            $ts = strtotime($partes[1]);
            return $ts !== false && date('H:i', $ts) === '10:00';
        };
    @endphp

    <header>
        <img class="logo-left" src="{{ public_path('storage/letra2.jpg') }}" alt="Centro Universitario Moctezuma">
        <img class="logo-right" src="{{ public_path('storage/letra.png') }}" alt="Centro Universitario Moctezuma">
        <div class="head-center">
            <div class="school">CENTRO UNIVERSITARIO MOCTEZUMA</div>
            <div class="title">HORARIO GENERAL · {{ mb_strtoupper($modalidad?->nombre ?? 'SEMIESCOLARIZADA') }}</div>
            <div class="meta">Ciclo {{ $cicloEscolar }} · Periodo {{ $periodoEscolar }} · C.C.T.
                {{ $escuela?->CCT ?? '—' }} · Versión {{ mb_strtoupper($modo) }}</div>
        </div>
    </header>

    <footer>
        <div class="footer-left">
            {{ $escuela?->nombre ?? 'Centro Universitario Moctezuma' }} · Generado:
            {{ $fechaGeneracion->format('d/m/Y H:i') }}
        </div>
        <div class="footer-right"><span class="page-number"></span></div>
    </footer>

    <div class="watermark"><img src="{{ public_path('storage/letra.png') }}" alt=""></div>

    <main>
        @if ($horarios->isEmpty())
            <div style="margin-top:30px; text-align:center; padding:30px; border:1px solid #cbd5e1;">
                <strong>No hay horarios para los filtros seleccionados.</strong>
            </div>
        @else
            @foreach ($bloques as $bloqueIndex => $columnasBloque)
                @php
                    $primerCol = $columnasBloque->first();
                    $tituloBloque =
                        $modo === 'compacto'
                            ? 'Horario consolidado'
                            : ($primerCol['cuatrimestre'] ?? ($primerCol['cuatrimestre_id'] ?? '—')) . '° CUATRIMESTRE';
                @endphp
                <section class="{{ $modo === 'legible' ? 'legible-block' : '' }}">
                    <div class="section-title">{{ $tituloBloque }} · {{ $columnasBloque->count() }} grupo(s)</div>
                    @php
                        $cantidadGrupos = max(1, $columnasBloque->count());
                        $anchoHora = $modo === 'compacto' ? 4.5 : 7.5;
                        $anchoGrupo = (100 - $anchoHora) / $cantidadGrupos;
                    @endphp
                    <table class="schedule">
                        <colgroup>
                            <col style="width:{{ number_format($anchoHora, 3, '.', '') }}%;">
                            @foreach ($columnasBloque as $col)
                                <col style="width:{{ number_format($anchoGrupo, 3, '.', '') }}%;">
                            @endforeach
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="hour">HORA</th>
                                @foreach ($columnasBloque as $col)
                                    <th>
                                        <span class="group-name">{{ mb_strtoupper($col['licenciatura_corta']) }}</span><br>
                                        <span class="group-meta">{{ $col['cuatrimestre'] }}° · GEN. {{ $col['generacion'] }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($horasUnicas as $hora)
                                <tr>
                                    <td class="hour">{{ strtoupper($hora) }}</td>
                                    @foreach ($columnasBloque as $col)
                                        @php
                                            $key =
                                                $hora .
                                                '|' .
                                                $col['cuatrimestre_id'] .
                                                '|' .
                                                $col['licenciatura_id'] .
                                                '|' .
                                                $col['generacion_id'];
                                            $item = $celdas->get($key);
                                            $materia = $item?->asignacionMateria?->materia;
                                            $profesor = $item?->asignacionMateria?->profesor;
                                            $fondo = $item ? $pastel($profesor?->color) : '#ffffff';
                                        @endphp
                                        <td class="{{ $item ? '' : 'empty' }}"
                                            style="background-color:{{ $fondo }};">
                                            @if ($item)
                                                <span class="subject">{{ $materia?->nombre ?? 'Materia no disponible' }}</span><br>
                                                <span class="teacher">{{ $profesor ? mb_strtoupper(trim($profesor->nombre . ' ' . $profesor->apellido_paterno . ' ' . $profesor->apellido_materno)) : 'SIN PROFESOR' }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @if ($esFinReceso($hora))
                                    <tr>
                                        <td class="receso-time">10:00AM-10:30AM</td>
                                        <td class="receso" colspan="{{ $columnasBloque->count() }}">RECESO</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endforeach

            <div class="page-break"></div>
            <section>
                <div class="section-title">RESUMEN DE CARGA DOCENTE</div>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th style="width:24%">Profesor</th>
                            <th>Materias</th>
                            <th style="width:12%">Horas programadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resumenDocentes as $row)
                            <tr>
                                <td style="background:{{ $pastel($row['color'], 0.84) }}; font-weight:700;">
                                    {{ $row['nombre'] }}</td>
                                <td>
                                    @forelse($row['materias'] as $m)
                                        <span class="pill"><strong>{{ $m['nombre'] }}</strong>@if ($m['clave']) ({{ $m['clave'] }})@endif · {{ $m['licenciatura'] }}</span><br>
                                    @empty
                                        Sin materias
                                    @endforelse
                                </td>
                                <td style="text-align:center; font-weight:700;">{{ $row['total_horas'] }} h</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center;">Sin información de docentes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="summary-total">TOTAL PROGRAMADO: {{ $totalGeneralHoras }} h</div>
            </section>
        @endif
    </main>
</body>

</html>
