<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Escolarizada | {{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}</title>
    <style>
        @page { margin: 6px 40px 36px 40px; }

        @font-face {
            font-family: 'calibri';
            src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'calibri';
            font-weight: 700;
            src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
        }

        body { font-family: calibri, DejaVu Sans, Arial, Helvetica, sans-serif; color:#0f172a; font-size:12px; }

        .brand-bar{ width:100%; border-top:3px solid #0ea5e9; border-bottom:3px solid #0ea5e9; padding:10px 0 6px; text-align:center; margin-top:10px; letter-spacing:.5px;}
        .titulo{ font-size:18px; font-weight:700; margin:0; line-height: 20px }
        .subtitulo{ margin:4px 0 0; font-size:18px; color:#334155;  }
        .logo-left{ position:absolute; top:26px; left:40px; height:50px; }

        .watermark{ position:fixed; top:58%; left:50%; transform:translate(-50%,-50%); width:100%; text-align:center; opacity:.06; z-index:-1; }
        .watermark img{ width:80%; }

        .meta{ width:100%; border-collapse:separate; border-spacing:0 6px; margin:10px 0 8px; }
        .meta td{ padding:6px 10px; border:1px solid #e5e7eb; border-radius:8px; font-size:12px; }

        .table-wrap{ border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
        table{ width:100%; border-collapse:collapse; }
        thead th{  color:#fff; text-transform:uppercase; font-size:12px; letter-spacing:.5px; padding:8px 10px; text-align:center;}
        tbody td{ border-top:1px solid #e5e7eb; padding:0px 0px; vertical-align:middle; background:#fff; font-size:12px; }
        tbody tr:nth-child(odd) td{ background:#fcfcfd; }

        .chip{ display:inline-block; padding:3px 8px; border-radius:999px; background:#f1f5f9; color:#0f172a; font-size:10px; border:1px solid #e2e8f0; white-space:nowrap; }
        .tag-lic{ display:inline-block; padding:2px 8px; border-radius:999px; background:#ecfeff; color:#155e75; border:1px solid #a5f3fc; font-size:10px; white-space:nowrap; }

        .col-hora{ width:160px; text-align:center; }

        .summary{ margin-top:10px; border:1px solid #e5e7eb; border-radius:10px; padding:10px; background:#f8fafc; }
        .summary h4{ margin:0 0 6px; font-size:13px; text-transform:uppercase; color:#334155; }
        .summary .item{ display:inline-block; margin:2px 6px 2px 0; font-size:13px; }

        .legend{ margin-top:8px; font-size:13px; color:#475569; }
        .legend .swatch{ display:inline-block; width:10px; height:10px; border-radius:2px; margin-right:6px; vertical-align:middle; border:1px solid #cbd5e1; }

        footer{ position:fixed; bottom:1px; left:0; right:0; text-align:center; font-size:10px; color:#334155; padding:6px 0 0; border-top:1px solid #94a3b8; }
        footer p{ margin:0; line-height:14px; font-size:13px; }
    </style>
</head>
<body>

    <img class="logo-left" src="{{ public_path('storage/logo.png') }}" alt="Logo">

    <div class="brand-bar">
        <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA <br> HORARIO DE CLASES — MODALIDAD {{ mb_strtoupper($modalidad->nombre ?? 'Escolarizada') }}</h1>

    </div>

    {{-- Meta --}}
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

    {{-- Preparación de datos para matriz: días → columnas, horas → filas --}}
    @php
        use Carbon\Carbon;

        $normDia = function($d) {
            $d = ucfirst(mb_strtolower(trim($d ?? '—'), 'UTF-8'));
            if ($d === 'Miercoles') $d = 'Miércoles';
            if ($d === 'Sabado')    $d = 'Sábado';
            return $d;
        };

        // Orden estándar (ajusta si no usas Domingo)
        $ordenDia = ['Lunes'=>1,'Martes'=>2,'Miércoles'=>3,'Jueves'=>4,'Viernes'=>5,'Sábado'=>6];

        $coleccion = collect($registros ?? []);

        // Orden por día y hora
        $ordenados = $coleccion->sortBy(function($r) use($ordenDia, $normDia){
            $dia  = $normDia(optional($r->dia)->dia);
            $rank = $ordenDia[$dia] ?? 99;
            $ini  = strtolower(trim(explode('-', $r->hora)[0] ?? ''));
            $t    = strtotime($ini) ?: 0;
            return sprintf('%02d-%06d', $rank, $t);
        })->values();

        // Columnas: días en orden estándar; Filas: horas únicas ordenadas
        $diasPresentes = collect($ordenDia)->keys()->filter(function($d) use ($ordenados, $normDia){
            return $ordenados->contains(function($r) use ($d, $normDia){ return $normDia(optional($r->dia)->dia) === $d; });
        })->values()->all();

        $horasPresentes = $ordenados->pluck('hora')->unique()->sortBy(function($range){
            $ini = strtolower(trim(explode('-', $range)[0] ?? ''));
            return strtotime($ini) ?: 0;
        })->values()->all();

        // Color por licenciatura
        $palette = ['#38bdf8','#fbbf24','#a78bfa','#34d399','#f472b6','#f59e0b','#60a5fa','#22d3ee','#fb7185','#84cc16'];
        $getLic = function($r){
            return optional($r->licenciatura)->nombre
                ?? optional(optional($r->asignacionMateria)->materia?->licenciatura)->nombre
                ?? 'N/A';
        };
        $licColors = [];
        foreach ($ordenados as $r) {
            $ln = $getLic($r);
            if (!isset($licColors[$ln])) {
                $licColors[$ln] = $palette[abs(crc32($ln)) % max(1,count($palette))];
            }
        }

        // Mapa grid[hora][dia] = [registros...]
        $grid = [];
        foreach ($ordenados as $r) {
            $d = $normDia(optional($r->dia)->dia);
            $h = $r->hora;
            $grid[$h][$d][] = $r;
        }

        // Resúmenes
        $minutos = function($range){
            [$ini,$fin] = array_map('trim', explode('-', strtolower($range)));
            $a = Carbon::parse($ini); $b = Carbon::parse($fin);
            return max(0, $a->diffInMinutes($b));
        };
        $resumenDia = []; $resumenMateria = []; $totalHoras = 0;
        foreach ($ordenados as $r) {
            $d = $normDia(optional($r->dia)->dia);
            $m = $minutos($r->hora)/60; $totalHoras += $m;
            $resumenDia[$d] = ($resumenDia[$d] ?? 0) + $m;

            $mat = optional(optional($r->asignacionMateria)->materia);
            $clave = $mat->clave ?? '';
            $key = ($mat->nombre ?? '—').($clave ? " ({$clave})" : '');
            $resumenMateria[$key] = ($resumenMateria[$key] ?? 0) + $m;
        }
    @endphp

    {{-- Matriz: horas (filas) × días (columnas) --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="background:#cbd5e1; color:#0f172a" class="col-hora">Hora</th>
                    @foreach ($diasPresentes as $dia)
                        <th style="background:#cbd5e1; color:#0f172a">{{ $dia }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($horasPresentes as $horaFila)
                    <tr>
                        <td class="col-hora"><span class="chip" style="font-size:12px">{{ $horaFila }}</span></td>

                        @foreach ($diasPresentes as $diaCol)
                            @php $items = $grid[$horaFila][$diaCol] ?? []; @endphp
                            <td style="text-align:center;">
                                @if (count($items))
                                    @foreach ($items as $r)
                                        @php
                                            $mat   = optional(optional($r->asignacionMateria)->materia);
                                            $clave = $mat->clave ?? '—';
                                            $cuat  = optional($mat->cuatrimestre)->cuatrimestre ?? '—';
                                            $lic   = $getLic($r);
                                            $licColor = $licColors[$lic] ?? '#e2e8f0';
                                        @endphp

                                        <div style="display:inline-block; text-align:center; margin:3px 0;">
                                            <div style="font-weight:700; font-size:14px;">
                                                <span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:{{ $licColor }};vertical-align:middle;margin-right:2px; margin-top:8px"></span>
                                                {{ $mat->nombre ?? '—' }}</div>
                                            <div style="margin-top:2px;">
                                                <span class="chip" style="font-size:10px;">{{ $clave }}</span>
                                                <span class="chip" style="font-size:12px; margin-left:4px;">{{ $cuat==='—' ? '—' : $cuat.'°' }}</span>
                                            </div>

                                        </div>
                                    @endforeach
                                @else
                                    <span class="chip">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + count($diasPresentes) }}" style="text-align:center; padding:16px; color:#64748b;">
                            Sin registros para este docente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Resúmenes --}}
    @if(!empty($resumenDia))
    <div class="summary">
        <h4>Resumen por día</h4>
        @foreach($resumenDia as $d => $h)
            <span class="item chip"><strong>{{ $d }}:</strong>&nbsp;{{ number_format($h, 1) }} h</span>
        @endforeach
        <span class="item" style="margin-left:6px;"><strong>Total:</strong> {{ number_format($totalHoras,1) }} h</span>
    </div>
    @endif

    @if(!empty($resumenMateria))
    <div class="summary">
        <h4>Resumen por materia</h4>
        @foreach($resumenMateria as $m => $h)
            <span class="item chip">{{ $m }} — {{ number_format($h, 1) }} h</span>
        @endforeach
    </div>
    @endif

    {{-- Leyenda por licenciatura --}}
    @if(!empty($licColors))
    <div class="legend">
        <strong>Leyenda de colores:</strong>
        @foreach($licColors as $ln => $col)
            <span style="margin-left:10px; ">
                <span class="swatch" style="background:{{ $col }}; margin-top:20px"></span>{{ $ln }}
            </span>
        @endforeach
    </div>
    @endif

    <footer>
        <p><strong>{{ $escuela->nombre }}</strong> — C.C.T. {{ $escuela->CCT }}</p>
        <p>
            C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }},
            Col. {{ $escuela->colonia }}, C.P. {{ $escuela->codigo_postal }},
            {{ $escuela->ciudad }}, {{ $escuela->estado }}. Tel. {{ $escuela->telefono }}
        </p>
        <p><strong>Fecha de expedición:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </footer>
</body>
</html>
