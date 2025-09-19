<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BOLETA DEL {{ $cuatrimestre->cuatrimestre }}° CUATRIMESTRE</title>

    <style>
        /* ========= Página y tipografías ========= */
        @page { margin: 14px 48px 0px 48px; } /* margen mayor abajo por footer fijo */
        @font-face {
            font-family: 'calibri';
            font-style: normal;
            src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'calibri';
            font-weight: 700;
            src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
        }
        html, body { font-family: 'calibri'; color:#1f2937; font-size:11px; line-height:1.35; }

        /* ========= Utilidades ========= */
        .text-center{ text-align:center; } .text-right{ text-align:right; }
        .uppercase{ text-transform:uppercase; } .fw-700{ font-weight:700; }
        .muted{ color:#6b7280; } .mb-2{ margin-bottom:6px; } .mb-4{ margin-bottom:10px; }
        .mb-6{ margin-bottom:14px; } .mb-8{ margin-bottom:18px; } .mt-2{ margin-top:6px; }
        .small{ font-size:10px; } .xs{ font-size:9px; } .lg{ font-size:13px; } .xl{ font-size:16px; }

        /* ========= Encabezado ========= */
        .banner{
            border:1px solid #e5e7eb; border-radius:10px; padding:8px 12px; margin-top:4px; margin-bottom:8px;
            background: linear-gradient(180deg,#f8fafc 0%, #f3f4f6 100%);
        }
        .banner-table{ width:100%; border-collapse:collapse; }
        .banner-table td{ vertical-align:middle; }
        .logo{ width:78px; height:78px; object-fit:contain; }
        .titular{
            text-align:center;
        }
        .titular h1{
            margin:0; font-size:18px; letter-spacing:.3px; color:#334155; font-weight:700;
        }
        .titular h2{
            margin:2px 0 0; font-size:13px; color:#475569; font-weight:700;
        }
        .titular .chip{
            display:inline-block; margin-top:6px; padding:2px 8px; border:1px solid #cbd5e1; border-radius:999px; background:#ffffff; font-weight:700;
        }

        /* ========= Marca de agua ========= */
        .watermark{
            position: fixed; top: 55%; left: 50%; transform: translate(-50%, -50%);
            width: 80%; opacity: 0.06; z-index: -1; text-align: center;
        }
        .watermark img{ width:100%; }

        /* ========= Tablas base ========= */
        table{ width:100%; border-collapse:collapse; }
        th, td{ padding:6px 8px; }

        /* ========= Tarjeta meta ========= */
        .meta{
            border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;
        }
        .meta thead th{
            background:#0ea5e9; color:#fff; font-weight:700; letter-spacing:.4px; padding:6px 8px; font-size:10px; text-align:center;
        }
        .meta tbody td{
            text-align:center; border-top:1px solid #eef2f7; font-size:11px;
        }
        .meta .row-alt td{ background:#f8fafc; }

        /* ========= Tabla de calificaciones ========= */
        .grades{
            border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; margin-top:16px;
        }
        .grades thead th{
            background:#0284c7; color:#fff; font-weight:700; letter-spacing:.4px; font-size:10px; padding:7px 8px;
        }
        .grades tbody td{
            border-top:1px solid #eef2f7; font-size:11px; vertical-align:middle;
        }
        .grades tbody tr:nth-child(even){ background:#fbfdff; }
        .col-asig { text-align:left; }
        .col-cal  { width:110px; text-align:center; }

        /* Chips de calificación */
        .score{
            display:inline-block; min-width:42px; padding:0px 8px; border-radius:999px; border:1px solid #cbd5e1; background:#fff; font-weight:700;
        }
        .ok{ background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
        .bien{ background:#f2f2f2; border-color:#dadada; color:#3d3d3d; }
        .rep{ background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        .np{ background:#fff1f2; border-color:#fecdd3; color:#9f1239; }
        .enproceso{ background:#f1f5f9; border-color:#e2e8f0; color:#334155; }

        /* Promedio final */
        .resumen{
            margin-top:20px;
            border:1px dashed #cbd5e1; border-radius:10px; padding:8px 10px; background:#f8fafc;
        }
        .resumen .lbl{ font-weight:700; margin-top: 10px }
        .resumen .valor{
            display:inline-block; min-width:58px; text-align:center; font-weight:700; padding:4px 10px; border-radius:999px; border:1px solid #cbd5e1; background:#ffffff; font-size:13px;
        }

        /* ========= Leyenda y firmas ========= */
        .leyenda{ margin-top:12px; color:#475569; font-size:10px; }
        .firmas{
            margin-top:28px;
        }
        .firmas td{ text-align:center; padding-top:24px; }
        .firma-linea{
            border-top:1px solid #94a3b8; padding-top:4px; margin-top:40px; display:inline-block; width:85%;
        }
        .cargo{ color:#475569; font-size:10px; }

        /* ========= Footer fijo ========= */
        footer{
            position: fixed; left: 0; right: 0; bottom: 12px; text-align:center;
            font-size:10px; color:#475569; border-top:1px solid #cbd5e1; padding-top:6px;
        }
        footer p{ margin:0; line-height:1.25; }

        /* Evitar cortes feos */
        tr, td, th{ page-break-inside:avoid; }
        .avoid-break{ page-break-inside:avoid; }
    </style>
</head>
<body>

    <!-- Marca de agua -->
    <div class="watermark">
        <img src="{{ public_path('storage/letra.png') }}" alt="Watermark">
    </div>

    <!-- Encabezado -->
    <div class="banner">
        <table class="banner-table">
            <tr>
                <td style="width:92px;">
                    <img class="logo" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo Izquierdo">
                </td>
                <td class="titular">
                    <h1 class="uppercase" style="font-size: 25px">Centro Universitario Moctezuma</h1>
                    <h2 class="uppercase" style="font-size: 20px">Ciclo Escolar {{ $ciclo_escolar->ciclo_escolar }}</h2>
                    <div class="chip uppercase" style="font-size: 18px">Boleta de Calificaciones · {{ $cuatrimestre->cuatrimestre }}° Cuatrimestre</div>
                </td>
                <td style="width:92px; text-align:right;">
                    @if(!empty($licenciatura->imagen) && file_exists(public_path('storage/licenciaturas/'.$licenciatura->imagen)))
                        <img class="logo" src="{{ public_path('storage/licenciaturas/'.$licenciatura->imagen) }}" alt="Logo Licenciatura">
                    @else
                        <img class="logo" src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo Licenciatura">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Meta / Identificación -->
    <table class="meta avoid-break">
        <thead>
            <tr>
                <th>Licenciatura</th>
                <th>C.C.T.</th>
                <th>Cuatrimestre</th>
                <th>Periodo Esc.</th>
                <th>Generación</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-alt">
                <td class="uppercase fw-700">{{ $licenciatura->nombre }}</td>
                <td class="uppercase fw-700">{{ $escuela->CCT }}</td>
                <td class="uppercase fw-700">{{ $cuatrimestre->cuatrimestre }}°</td>
                <td class="uppercase fw-700">{{ $periodo->mes->meses_corto }}</td>
                <td class="uppercase fw-700">{{ $periodo->generacion->generacion }}</td>
            </tr>
            <tr>
                <td colspan="3" class="uppercase">{{ $escuela->calle }} #{{ $escuela->no_exterior }} · Col. {{ $escuela->colonia }} · Cd. {{ $escuela->ciudad }}</td>
                <td class="uppercase">{{ $escuela->municipio }}</td>
                <td class="uppercase">{{ $escuela->estado }}</td>
            </tr>
            <tr class="row-alt">
                <td colspan="2" class="uppercase fw-700">{{ $inscripcion->apellido_paterno }}</td>
                <td class="uppercase fw-700">{{ $inscripcion->apellido_materno }}</td>
                <td colspan="2" class="uppercase fw-700">{{ $inscripcion->nombre }}</td>
            </tr>
            <tr>
                <td colspan="2" class="xs muted">Apellido paterno</td>
                <td class="xs muted">Apellido materno</td>
                <td colspan="2" class="xs muted">Nombre(s)</td>
            </tr>
        </tbody>
    </table>

    <!-- Calificaciones -->
                <table class="grades avoid-break">
                    <thead>
                        <tr class="uppercase">
                            <th class="col-asig">Asignatura</th>
                            <th class="col-cal">Calificación</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $suma = 0;
                        $cuenta = 0;
                        $hasNP = false; // bandera para detectar NP
                    @endphp

                    @foreach ($calificaciones as $calificacion)
                        @php
                            $val = $calificacion->calificacion;
                            $isNP = (strtoupper((string)$val) === 'NP');
                            $isNum = is_numeric($val);
                            $clase = $isNP ? 'np' : ($isNum ? ($val < 6 ? 'rep' : 'ok') : 'enproceso');

                            if($isNum){
                                $suma += (float)$val;
                                $cuenta++;
                            } elseif($isNP) {
                                $hasNP = true; // marcar que hay NP
                            }
                        @endphp
                        <tr>
                            <td class="col-asig uppercase">{{ $calificacion->asignacionMateria->materia->nombre }}</td>
                            <td class="col-cal">
                                @if($isNP)
                                    <span class="score np">NP</span>
                                @elseif($isNum)
                                    <span class="score {{ $clase }}">{{ floor($val * 10) / 10 }}</span>
                                @else
                                    <span class="score enproceso">EN PROCESO</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                        <tr>
                            <td class="text-right fw-700 uppercase" style="padding-right:12px;">Calificación Cuatrimestral</td>
                            <td class="col-cal">
                                @php
                                    if($hasNP){
                                        $prom = 0; // si hay al menos un NP, forzar a 0
                                    } else {
                                        $prom = $cuenta ? floor(($suma / $cuenta) * 10) / 10 : 'N/A';
                                    }
                                @endphp
                                <span class="score {{ ($prom !== 'N/A' && (float)$prom >= 6) ? 'ok' : (($prom !== 'N/A' && (float)$prom < 6) ? 'rep' : 'enproceso') }}">
                                    {{ $prom }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>


    <!-- Resumen / leyenda -->
    <div class="resumen avoid-break">
        <span class="lbl">Leyenda:</span>
        <span class="score ok">≥ 6 Aprobada</span>
        <span class="score rep"> &lt; 6 Reprobada</span>
        <span class="score np">NP No presentó</span>
    </div>



    <!-- Footer -->
    <footer>
        <p class="uppercase fw-700">{{ $escuela->nombre }} · C.C.T. {{ $escuela->CCT }}</p>
        <p>
            C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }}, Col. {{ $escuela->colonia }},
            C.P. {{ $escuela->codigo_postal }}, Cd. {{ $escuela->ciudad }}, {{ $escuela->estado }}.
        </p>
        <p>Fecha de expedición: {{ now()->translatedFormat('d \\d\\e F \\d\\e\\l Y \\a \\l\\a\\s H:i') }}</p>
    </footer>
</body>
</html>
