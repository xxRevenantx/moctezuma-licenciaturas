<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Índice de listas del profesor</title>
    <style>
        @page { margin: 24px 28px 28px; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #263d48;
            font-size: 8.5px;
        }

        .header {
            width: 100%;
            margin-bottom: 13px;
            border-collapse: collapse;
        }

        .header td {
            border: 0;
            vertical-align: middle;
        }

        .brand {
            color: #006492;
            font-size: 17px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .meta {
            color: #71858e;
            font-size: 8px;
            line-height: 1.5;
            text-align: right;
        }

        .rule {
            height: 4px;
            margin-bottom: 14px;
            background: #006492;
            border-right: 150px solid #88AC2E;
        }

        h1 {
            margin: 0 0 5px;
            color: #173c4d;
            font-size: 20px;
        }

        .description {
            margin: 0 0 13px;
            color: #70838c;
            font-size: 9px;
        }

        table.index {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.index thead { display: table-header-group; }
        table.index tr { page-break-inside: avoid; }

        table.index th {
            padding: 7px 5px;
            border: 1px solid #0c5778;
            background: #006492;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        table.index td {
            padding: 6px 5px;
            border: 1px solid #d5e1e6;
            background: #ffffff;
            vertical-align: middle;
        }

        table.index tr:nth-child(even) td { background: #f6fafb; }

        .center { text-align: center; }
        .strong { font-weight: 700; color: #173c4d; }
        .type { font-weight: 700; color: #006492; }
        .page { font-size: 10px; font-weight: 700; color: #4d6f0f; }

        .section-title {
            margin: 18px 0 8px;
            padding: 7px 10px;
            border-left: 5px solid #88AC2E;
            background: #f2f7e7;
            color: #4e6814;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        table.omitted {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.omitted thead { display: table-header-group; }
        table.omitted tr { page-break-inside: avoid; }

        table.omitted th {
            padding: 6px 5px;
            border: 1px solid #bf8b1a;
            background: #e9b949;
            color: #3d2c04;
            font-size: 7px;
            text-transform: uppercase;
        }

        table.omitted td {
            padding: 5px;
            border: 1px solid #ead9ad;
            background: #fffaf0;
            vertical-align: top;
        }

        .empty {
            padding: 13px;
            border: 1px solid #cfe3b1;
            border-radius: 6px;
            background: #f6faef;
            color: #4e6814;
            text-align: center;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -13px;
            left: 0;
            color: #87979e;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="brand">{{ $escuela->nombre ?? 'Centro Universitario Moctezuma' }}</div>
            </td>
            <td class="meta">
                Profesor:
                {{ $resumen['profesor']->nombre }}
                {{ $resumen['profesor']->apellido_paterno }}
                {{ $resumen['profesor']->apellido_materno }}<br>
                Periodo {{ $resumen['periodo'] }} · Ciclo {{ $resumen['ciclo_escolar'] }}
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    <h1>Índice del paquete</h1>
    <p class="description">
        {{ $resumen['total_listas'] }} lista{{ $resumen['total_listas'] === 1 ? '' : 's' }} generada{{ $resumen['total_listas'] === 1 ? '' : 's' }}.
        El número de página corresponde al inicio de cada documento dentro del PDF combinado.
    </p>

    <table class="index">
        <thead>
            <tr>
                <th style="width: 3.5%;">#</th>
                <th style="width: 8%;">Tipo</th>
                <th style="width: 25%;">Materia</th>
                <th style="width: 25%;">Licenciatura</th>
                <th style="width: 8%;">Modalidad</th>
                <th style="width: 7%;">Cuat.</th>
                <th style="width: 9%;">Generación</th>
                <th style="width: 7%;">Alumnos</th>
                <th style="width: 7.5%;">Página</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documentos as $indice => $documento)
                <tr>
                    <td class="center">{{ $indice + 1 }}</td>
                    <td class="center type">{{ $documento['tipo'] }}</td>
                    <td class="strong">{{ $documento['materia'] }}</td>
                    <td>{{ $documento['licenciatura'] }}</td>
                    <td class="center">{{ $documento['modalidad'] }}</td>
                    <td class="center">{{ $documento['cuatrimestre'] }}°</td>
                    <td class="center">{{ $documento['generacion'] }}</td>
                    <td class="center">{{ $documento['alumnos'] }}</td>
                    <td class="center page">{{ $documento['pagina_inicio'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Documentos omitidos</div>

    @if (count($omitidas) > 0)
        <table class="omitted">
            <thead>
                <tr>
                    <th style="width: 10%;">Tipo</th>
                    <th style="width: 23%;">Materia</th>
                    <th style="width: 23%;">Licenciatura</th>
                    <th style="width: 7%;">Cuat.</th>
                    <th style="width: 10%;">Generación</th>
                    <th style="width: 27%;">Motivo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($omitidas as $omitida)
                    <tr>
                        <td class="center">{{ $omitida['tipo'] }}</td>
                        <td>{{ $omitida['materia'] }}</td>
                        <td>{{ $omitida['licenciatura'] }}</td>
                        <td class="center">{{ $omitida['cuatrimestre'] }}°</td>
                        <td class="center">{{ $omitida['generacion'] }}</td>
                        <td>{{ $omitida['motivo'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No se omitió ninguna lista durante la generación del paquete.</div>
    @endif

    <div class="footer">
        Índice generado por la Plataforma Moctezuma · {{ $resumen['generado_en']->translatedFormat('d/m/Y H:i') }}
    </div>
</body>
</html>
