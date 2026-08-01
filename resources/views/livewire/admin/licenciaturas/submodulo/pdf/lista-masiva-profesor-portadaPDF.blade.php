<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Paquete de listas del profesor</title>
    <style>
        @page { margin: 28px 36px; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #18313f;
            font-size: 12px;
        }

        .top-line {
            height: 8px;
            background: #006492;
            border-radius: 6px;
        }

        .header-table,
        .stats-table,
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: 0;
            vertical-align: middle;
            padding-top: 18px;
        }

        .logo-cell { width: 135px; }

        .logo {
            width: 112px;
            max-height: 92px;
            object-fit: contain;
        }

        .school-name {
            margin: 0;
            color: #006492;
            font-size: 23px;
            font-weight: 700;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .school-data {
            margin-top: 6px;
            color: #657985;
            font-size: 10px;
            line-height: 1.45;
        }

        .green-line {
            height: 3px;
            margin: 16px 0 24px;
            background: #88AC2E;
        }

        .eyebrow {
            margin: 0 0 8px;
            color: #88AC2E;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-align: center;
            text-transform: uppercase;
        }

        .title {
            margin: 0;
            color: #12384a;
            font-size: 31px;
            line-height: 1.15;
            text-align: center;
            text-transform: uppercase;
        }

        .subtitle {
            margin: 10px 0 24px;
            color: #617985;
            font-size: 13px;
            text-align: center;
        }

        .professor-card {
            margin: 0 auto 18px;
            padding: 17px 20px;
            border: 1px solid #cfe2ea;
            border-left: 7px solid #006492;
            border-radius: 10px;
            background: #f5fbfd;
        }

        .professor-label {
            margin: 0 0 5px;
            color: #708791;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .professor-name {
            margin: 0;
            color: #12384a;
            font-size: 21px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .professor-email {
            margin-top: 5px;
            color: #526c79;
            font-size: 10px;
        }

        .stats-table {
            margin: 0 0 18px;
            table-layout: fixed;
        }

        .stats-table td {
            width: 25%;
            padding: 0 5px;
            border: 0;
        }

        .stat {
            min-height: 76px;
            padding: 13px 10px;
            border: 1px solid #dce9ee;
            border-radius: 9px;
            background: #ffffff;
            text-align: center;
        }

        .stat-number {
            color: #006492;
            font-size: 23px;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            margin-top: 7px;
            color: #6e828c;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .info-table {
            border: 1px solid #d8e5ea;
            border-radius: 8px;
            background: #ffffff;
        }

        .info-table td {
            width: 33.333%;
            padding: 11px 14px;
            border-right: 1px solid #d8e5ea;
            vertical-align: top;
        }

        .info-table td:last-child { border-right: 0; }

        .info-label {
            display: block;
            margin-bottom: 4px;
            color: #7a8e97;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .7px;
            text-transform: uppercase;
        }

        .info-value {
            color: #173b4c;
            font-size: 11px;
            font-weight: 700;
        }

        .notice {
            margin-top: 17px;
            padding: 10px 14px;
            border-radius: 8px;
            background: #f1f7e4;
            color: #4e6814;
            font-size: 9px;
            line-height: 1.5;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            color: #7d9099;
            font-size: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="top-line"></div>

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if (file_exists(public_path('storage/letra2.jpg')))
                    <img class="logo" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo">
                @elseif (file_exists(public_path('storage/logo-moctezuma.jpg')))
                    <img class="logo" src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo">
                @endif
            </td>
            <td>
                <h1 class="school-name">{{ $escuela->nombre ?? 'Centro Universitario Moctezuma' }}</h1>
                <div class="school-data">
                    C.C.T. {{ $escuela->CCT ?? '—' }} ·
                    {{ $escuela->calle ?? '' }} {{ $escuela->no_exterior ?? '' }},
                    {{ $escuela->colonia ?? '' }}, {{ $escuela->ciudad ?? '' }}, {{ $escuela->estado ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="green-line"></div>

    <p class="eyebrow">Control escolar · Documentación académica</p>
    <h2 class="title">Paquete masivo de listas</h2>
    <p class="subtitle">Asistencia y evaluación organizadas por licenciatura, cuatrimestre, materia y generación.</p>

    <div class="professor-card">
        <p class="professor-label">Profesor</p>
        <p class="professor-name">
            {{ $resumen['profesor']->nombre }}
            {{ $resumen['profesor']->apellido_paterno }}
            {{ $resumen['profesor']->apellido_materno }}
        </p>
        @if ($resumen['profesor']->user?->email)
            <div class="professor-email">{{ $resumen['profesor']->user->email }}</div>
        @endif
    </div>

    <table class="stats-table">
        <tr>
            <td>
                <div class="stat">
                    <div class="stat-number">{{ $resumen['total_materias'] }}</div>
                    <div class="stat-label">Materias incluidas</div>
                </div>
            </td>
            <td>
                <div class="stat">
                    <div class="stat-number">{{ $resumen['total_listas'] }}</div>
                    <div class="stat-label">Listas generadas</div>
                </div>
            </td>
            <td>
                <div class="stat">
                    <div class="stat-number">{{ $resumen['total_alumnos'] }}</div>
                    <div class="stat-label">Registros de alumnos</div>
                </div>
            </td>
            <td>
                <div class="stat">
                    <div class="stat-number">{{ $resumen['total_omitidas'] }}</div>
                    <div class="stat-label">Listas omitidas</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>
                <span class="info-label">Ciclo escolar</span>
                <span class="info-value">{{ $resumen['ciclo_escolar'] }}</span>
            </td>
            <td>
                <span class="info-label">Periodo</span>
                <span class="info-value">{{ $resumen['periodo'] }}</span>
            </td>
            <td>
                <span class="info-label">Contenido</span>
                <span class="info-value">{{ $resumen['tipo'] }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border-top: 1px solid #d8e5ea;">
                <span class="info-label">Filtro de alumnos</span>
                <span class="info-value">{{ $resumen['filtro_alumnos'] }}</span>
            </td>
            <td style="border-top: 1px solid #d8e5ea;">
                <span class="info-label">Fecha de generación</span>
                <span class="info-value">{{ $resumen['generado_en']->translatedFormat('d/m/Y H:i') }}</span>
            </td>
        </tr>
    </table>

    <div class="notice">
        El índice de la página siguiente muestra la ubicación de cada documento. Las materias o generaciones sin alumnos
        activos, sin periodo escolar o sin grupo configurado se registran como omitidas y no producen hojas vacías.
    </div>

    <div class="footer">
        Documento generado por la Plataforma Moctezuma · {{ $resumen['generado_en']->translatedFormat('d \d\e F \d\e\l Y') }}
    </div>
</body>
</html>
