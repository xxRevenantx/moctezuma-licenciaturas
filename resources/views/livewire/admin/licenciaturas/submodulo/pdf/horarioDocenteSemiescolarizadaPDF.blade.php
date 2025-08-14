<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Horario Semiescolarizada | {{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}</title>

    <style>

        @page { margin: 6px 40px 36px 40px; }

        /* Fuentes (opcional Calibri local) */
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

        body {
            font-family: calibri, DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }

        /* ===== Encabezado / marcas ===== */
        .brand-bar {
            width:100%;
            border-top: 3px solid #0ea5e9;
            border-bottom: 3px solid #0ea5e9;
            padding: 10px 0 6px 0;
            text-align:center;
            margin-top: 10px;
            letter-spacing: .5px;
        }
        .titulo { font-size: 18px; font-weight: 700; margin: 0; }
        .subtitulo { margin: 4px 0 0 0; font-size: 12px; color:#334155; font-weight: 600; }
        .logo-left  { position:absolute; top:30px; left: 40px; height:50px; }
        .logo-right { position:absolute; top:30px; right:40px; height:50px; }

        /* ===== Watermark ===== */
        .watermark {
            position: fixed;
            top: 58%; left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            text-align: center;
            opacity: .06; z-index: -1;
        }
        .watermark img { width: 80%; }

        /* ===== Cabecera de documento (meta / QR / foto) ===== */
        .header-grid {
            width:100%;
            display: table;
            margin: 10px 0 8px 0;
            table-layout: fixed;
        }
        .header-col { display: table-cell; vertical-align: top; }
        .col-left   { width: 62%; padding-right: 10px; }
        .col-mid    { width: 18%; text-align:center; }
        .col-right  { width: 20%; text-align:center; }

        .meta {
            width:100%;
            border-collapse: separate;
            border-spacing: 0 6px;
        }
        .meta td {
            padding: 6px 10px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 11px;
        }
        .meta .etiqueta { width: 130px; color:#334155; font-weight:700; }

        .qr-box, .foto-box {
            display:inline-block;
            border:1px solid #e5e7eb;
            background:#f8fafc;
            border-radius: 10px;
            padding: 6px;
        }
        .qr-box img { width: 120px; height: 120px; }
        .foto-box img { width: 120px; height: 120px; object-fit: cover; }

        .folio {
            margin-top: 6px;
            font-size: 10px;
            color:#475569;
        }

        /* ===== Tabla principal “estilo tarjeta” ===== */
        .table-wrap {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #0ea5e9;
            color: #fff;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: .5px;
            padding: 8px 10px;
            text-align: center;
        }
        tbody td {
            border-top: 1px solid #e5e7eb;
            padding: 8px 10px;
            vertical-align: middle;
            background: #ffffff;
            font-size: 12px;
        }
        tbody tr:nth-child(odd) td { background:#fcfcfd; }

        /* Fila separadora por día */
        .day-sep td {
            background:#f1f5f9;
            color:#0f172a;
            font-weight:700;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-top:1px solid #cbd5e1;
            border-bottom:1px solid #cbd5e1;
            padding:6px 10px;
        }

        /* ===== Chips / Badges ===== */
        .chip {
            display:inline-block; padding:3px 8px;
            border-radius:999px; background:#f1f5f9; color:#0f172a;
            font-size:10px; border:1px solid #e2e8f0; white-space:nowrap;
        }
        .chip-strong { font-weight:700; background:#e2e8f0; }
        .badge {
            display:inline-block; padding:2px 8px;
            border-radius:999px; font-size:10px; font-weight:700;
            background:#dbeafe; color:#1e3a8a; border:1px solid #bfdbfe; white-space:nowrap;
        }
        .badge-warn { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
        .tag-lic {
            display:inline-block; padding:2px 8px;
            border-radius:999px; background:#ecfeff; color:#155e75;
            border:1px solid #a5f3fc; font-size:10px; white-space:nowrap;
        }
        .tag-grupo { background:#fef9c3; color:#854d0e; border-color:#fef08a; }
        .tag-mod   { background:#e0e7ff; color:#3730a3; border-color:#c7d2fe; }

        /* ===== Columnas ===== */
        .col-dia   { width: 105px; text-align:center; }
        .col-hora  { width: 170px; text-align:center; }
        .col-mat   { width: auto; }
        .col-clave { width: 90px; text-align:center; }
        .col-grupo { width: 110px; text-align:center; }
        .col-aula  { width: 90px; text-align:center; }
        .col-lic   { width: 170px; text-align:center; }
        .col-estado{ width: 120px; text-align:center; }

        /* ===== Resúmenes ===== */
        .summary {
            margin-top:10px; border:1px solid #e5e7eb; border-radius:10px; padding:10px;
            background:#f8fafc;
        }
        .summary h4 { margin:0 0 6px 0; font-size:13px; text-transform:uppercase; color:#334155; }
        .summary .item { display:inline-block; margin:2px 6px 2px 0; font-size: 13px }

        /* ===== Leyenda colores ===== */
        .legend { margin-top:8px; font-size:13px; color:#475569; }
        .legend .swatch {
            display:inline-block; width:10px; height:10px; border-radius:2px; margin-right:6px; vertical-align:middle;
            border:1px solid #cbd5e1;
        }

        /* ===== Observaciones / Firmas ===== */
        .note-box, .firmas {
            margin-top: 12px; border:1px solid #e5e7eb; border-radius:10px; padding:10px; background:#ffffff;
        }
        .note-box h4, .firmas h4 { margin:0 0 8px 0; font-size:12px; text-transform:uppercase; color:#334155; }
        .obs { min-height: 54px; border:1px dashed #cbd5e1; padding:6px; color:#64748b; }
        .firmas .row { display:table; width:100%; table-layout:fixed; margin-top:18px; }
        .firmas .col { display:table-cell; width:33.3%; text-align:center; vertical-align:bottom; }
        .line { border-top:1px solid #0f172a; margin:0 18px 4px 18px; }

        /* ===== Footer ===== */
        footer {
            position: fixed;
            bottom: 1px; left: 0; right: 0;
            text-align: center; font-size: 10px; color:#334155;
            padding: 6px 0 0 0; border-top: 1px solid #94a3b8;
        }
        footer p { margin: 0px 0; line-height: 14px; font-size: 13px}
    </style>
</head>
<body>

    {{-- Marca de agua --}}
    <div class="watermark">
        <img src="{{ public_path('storage/letra.png') }}" alt="Marca de agua">
    </div>

    {{-- Logos opcionales --}}
    <img class="logo-left"  src="{{ public_path('storage/logo.png') }}" alt="Logo">
    {{-- <img class="logo-right" src="{{ public_path('storage/otro_logo.png') }}" alt="Logo"> --}}

    {{-- Barra institucional --}}
    <div class="brand-bar">
        <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA <br>HORARIO DE CLASES — MODALIDAD {{ mb_strtoupper($modalidad->nombre ?? 'Semiescolarizada') }}</h1>
    </div>

    @php
        use Carbon\Carbon;

        // --- Preparación de datos ---
        $coleccion = collect($registros ?? []);

        // Orden por día (Lunes..Domingo) y por hora de inicio
        $ordenDia = ['Lunes'=>1,'Martes'=>2,'Miércoles'=>3,'Miercoles'=>3,'Jueves'=>4,'Viernes'=>5,'Sábado'=>6,'Sabado'=>6,'Domingo'=>7];
        $ordenados = $coleccion->sortBy(function($r) use($ordenDia){
            $dia = optional($r->dia)->dia ?? 'Z';
            $rank = $ordenDia[$dia] ?? 99;
            $ini = strtolower(trim(explode('-', $r->hora)[0] ?? ''));
            $t = strtotime($ini) ?: 0;
            return sprintf('%02d-%06d', $rank, $t);
        })->values();

        // Color por licenciatura (determinístico)
        $palette = ['#38bdf8','#fbbf24','#a78bfa','#34d399','#f472b6','#f59e0b','#60a5fa','#22d3ee','#fb7185','#84cc16'];
        $licColors = [];
        $getLic = function($r){
            return optional($r->licenciatura)->nombre
                ?? optional(optional($r->asignacionMateria)->materia?->licenciatura)->nombre
                ?? 'N/A';
        };
        foreach ($ordenados as $r) {
            $ln = $getLic($r);
            if (!isset($licColors[$ln])) {
                $idx = abs(crc32($ln)) % max(1,count($palette));
                $licColors[$ln] = $palette[$idx];
            }
        }

        // Resúmenes
        $resumenDia = [];            // ['Lunes' => horasDecimal]
        $resumenMateria = [];        // ['Nombre (CLAVE)' => horasDecimal]
        $totalHoras = 0;

        // Ayudas de tiempo
        $minutos = function($range){
            [$ini, $fin] = array_map('trim', explode('-', strtolower($range)));
            $a = Carbon::parse($ini); $b = Carbon::parse($fin);
            return max(0, $a->diffInMinutes($b));
        };

        // Detección de solapes por día
        $histDia = [];   // ['Lunes' => [['ini'=>m, 'fin'=>m], ...]]

        foreach ($ordenados as $r) {
            $dia = optional($r->dia)->dia ?? '—';
            $mins = $minutos($r->hora);
            $hdec = $mins / 60;
            $resumenDia[$dia] = ($resumenDia[$dia] ?? 0) + $hdec;
            $totalHoras += $hdec;

            $mat = optional(optional($r->asignacionMateria)->materia);
            $clave = $mat->clave ?? '';
            $keyMat = ($mat->nombre ?? '—').($clave ? " ({$clave})" : '');
            $resumenMateria[$keyMat] = ($resumenMateria[$keyMat] ?? 0) + $hdec;

            // Solapes
            $a = Carbon::parse(strtolower(trim(explode('-', $r->hora)[0] ?? '')));
            $b = Carbon::parse(strtolower(trim(explode('-', $r->hora)[1] ?? '')));
            $ini = $a->hour*60 + $a->minute;
            $fin = $b->hour*60 + $b->minute;
            $histDia[$dia][] = ['ini'=>$ini,'fin'=>$fin];
        }

        // Helper duración como texto
        $duracionTxt = function($range){
            try {
                [$ini, $fin] = array_map('trim', explode('-', strtolower($range)));
                $a = Carbon::parse($ini); $b = Carbon::parse($fin);
                $m = $a->diffInMinutes($b);
                return $m % 60 === 0 ? ($m/60).' h' : number_format($m/60, 1).' h';
            } catch (\Throwable $e) {
                return '1 h';
            }
        };

        // Documento / trazabilidad
        $docSeed = ($profesor->id ?? '0').'|'.($modalidad->id ?? '0').'|'.($ordenados->count()).'|'.(now()->format('YmdHi'));
        $docId = strtoupper(substr(sha1($docSeed), 0, 10));
        $generadoPor = $generadoPor ?? (auth()->user()->name ?? 'Sistema');
    @endphp

    {{-- Cabecera con meta + QR + foto --}}
    <div class="header-grid">
        <div class="header-col col-left">
           @php
        // Helper para determinar si el color es oscuro
        function isDark($hexColor) {
            $hexColor = ltrim($hexColor, '#');
            if (strlen($hexColor) === 3) {
                $hexColor = $hexColor[0].$hexColor[0].$hexColor[1].$hexColor[1].$hexColor[2].$hexColor[2];
            }
            $r = hexdec(substr($hexColor, 0, 2));
            $g = hexdec(substr($hexColor, 2, 2));
            $b = hexdec(substr($hexColor, 4, 2));
            // Luminosidad relativa
            $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b);
            return $lum < 128;
        }
        $bgColor = $profesor->color;
        $textColor = isDark($bgColor) ? '#fff' : '#0f172a';
    @endphp
    <table class="meta">
        <tr>
            <td style="font-size: 16px; background:{{ $bgColor }}; color:{{ $textColor }};">
                <strong>DOCENTE:</strong>
                {{ mb_strtoupper(trim($profesor->nombre.' '.$profesor->apellido_paterno.' '.$profesor->apellido_materno)) }}
            </td>
        </tr>
    </table>
        </div>


    </div>

    {{-- Tabla principal --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th class="col-hora" style="font-size: 14px; background:#cbd5e1; color:#0f172a">Hora(s)</th>
                    <th class="col-mat" style="font-size: 14px; background:#cbd5e1; color:#0f172a"> Materia</th>
                    <th class="col-clave" style="font-size: 14px; background:#cbd5e1; color:#0f172a" >Cuatrimestre</th>
                    <th class="col-lic"  style="font-size: 14px; background:#cbd5e1; color:#0f172a">Licenciatura</th>
                </tr>
            </thead>
            <tbody>
                @php $ultimoDia = null; @endphp
                @forelse ($ordenados as $r)
                    @php
                        $dia = optional($r->dia)->dia ?? '—';
                        $mat = optional(optional($r->asignacionMateria)->materia);
                        $clave = $mat->clave ?? '—';
                        $lic = $getLic($r);
                        $licColor = $licColors[$lic] ?? '#e2e8f0';
                        $dur = $duracionTxt($r->hora);

                        // Estado: pendiente si no hay aula
                        $estado = empty($r->aula) ? 'Pendiente de aula' : 'Confirmado';

                        // Solape (simple): si hay intersección con alguno del mismo día
                        $a = Carbon::parse(strtolower(trim(explode('-', $r->hora)[0] ?? '')));
                        $b = Carbon::parse(strtolower(trim(explode('-', $r->hora)[1] ?? '')));
                        $ini = $a->hour*60 + $a->minute; $fin = $b->hour*60 + $b->minute;
                        $conf = false;
                        if (!empty($histDia[$dia])) {
                            foreach ($histDia[$dia] as $slot) {
                                if ($ini < $slot['fin'] && $fin > $slot['ini'] && !($ini==$slot['ini'] && $fin==$slot['fin'])) { $conf = true; break; }
                            }
                        }
                    @endphp

                    {{-- Separador por día --}}
                    @if($ultimoDia !== $dia)
                        <tr class="day-sep"><td colspan="8">{{ $dia }}</td></tr>
                        @php $ultimoDia = $dia; @endphp
                    @endif

                    <tr>

                        <td class="col-hora">
                            <div style="display:flex; gap:6px; align-items:center; justify-content:center;">
                                <span style=" font-size:14px" class="chip">{{ $r->hora }}</span>
                            </div>
                        </td>
                        <td class="col-mat" style="vertical-align: middle; text-align: center;">
                            <div style="font-weight:700; display: inline-flex; align-items: center; justify-content: center; font-size:14px">
                                {{ $mat->nombre ?? '—' }}
                                <span class="chip" style="margin-left:8px; ">{{ $clave }}</span>
                            </div>
                        </td>
                        <td class="col-clave">

                            <div style="display:flex; gap:6px; align-items:center; justify-content:center;">
                                <span style=" font-size:14px" class="chip"> {{ $mat->cuatrimestre->cuatrimestre ?? '—' }}°</span>
                            </div>
                        </td>

                        <td class="col-lic">
                            <span class="tag-lic" style="border-color:{{ $licColor }}; color:#0f172a; background:#ffffff;">
                                <span style="font-size:14px; display:inline-block;width:8px;height:8px;border-radius:2px;background:{{ $licColor }};vertical-align:middle;margin-right:6px;"></span>
                                {{ $lic }}
                            </span>
                        </td>

                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center; padding:16px; color:#64748b;">Sin registros para este docente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Resúmenes --}}


    {{-- Leyenda de colores por licenciatura --}}
    <div class="legend">
        <strong>Leyenda de colores:</strong>
        @foreach($licColors as $ln => $col)
            <span style="margin-left:10px;">
                <span class="swatch" style="background:{{ $col }}"></span>{{ $ln }}
            </span>
        @endforeach
    </div>


    <div class="summary">
        <h4>Resumen por materia</h4>
        @foreach($resumenMateria as $m => $h)
            <span class="item chip">{{ $m }} — {{ number_format($h, 1) }} h</span>
        @endforeach
    </div>





    <footer>
        <p><strong>{{ $escuela->nombre }}</strong> — C.C.T. {{ $escuela->CCT }}</p>
        <p>
            C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }},
            Col. {{ $escuela->colonia }}, C.P. {{ $escuela->codigo_postal }},
            {{ $escuela->ciudad }}, {{ $escuela->estado }}. Tel. {{ $escuela->telefono }}
        </p>
        <p><strong>Fecha de expedición:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} </p>
    </footer>


</body>
</html>
