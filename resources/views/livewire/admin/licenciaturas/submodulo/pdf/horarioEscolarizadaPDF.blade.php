<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <title>Horario Escolarizado | Cua: {{$cuatrimestre}}° | Lic. {{$licenciatura_nombre->nombre}}</title>

    <style>
        /* Margen de página amplio para header/footer fijos */
        @page { margin: 10px 45px 110px 45px; }


        /* Encabezado y pie fijos para todas las páginas */
        header {
            position: fixed; top: 0; left: 0; right: 0; height: 110px;
            padding: 12px 0 8px 0; border-bottom: 1px solid #9ca3af;
        }
        footer {
            text-align: center;
            position: fixed; bottom: 0; left: 0; right: 0; height: 90px;
            border-top: 1px solid #9ca3af; padding: 6px 0;
            font-size: 11px; color: #374151;
        }
        .container { width: 100%; }

        /* Layout del header */
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .logo { width: 90px; height: 90px; object-fit: contain; }
        .title-wrap { text-align: center; }
        .inst { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; color: #374151; }
        .subtitle { margin-top: 4px; font-size: 13px; font-weight: 600; }
        .chips { margin-top: 6px; }
        .chip {
            display: inline-block; font-size: 11px; font-weight: 700;
            border: 1px solid #111827; color: #111827; padding: 3px 6px; border-radius: 6px; margin: 0 4px 2px 4px;
        }
        .meta-line { font-size: 11px; margin-top: 6px; color: #4b5563; }
        .emision {
            position: absolute; right: 0; top: 0; font-weight: 700; font-size: 11px; color: #6b7280; padding: 4px 8px;
        }

        /* Pie de página */
        .footer-top { text-align: center; margin-bottom: 4px; }
        .footer-mid { text-align: center; }
        .footer-bot {
            margin-top: 3px; display: flex; justify-content: space-between; align-items: center;
            font-size: 11px;
        }
        .page-number:before { content: counter(page) " / " counter(pages); }

        /* Marca de agua (imagen + texto) */
        .watermark {
            position: fixed; top: 55%; left: 50%; transform: translate(-50%, -50%);
            width: 70%; z-index: -1; opacity: 0.06; text-align: center;
        }
        .watermark-text {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-size: 60px; font-weight: 800; letter-spacing: 4px; color: #111827; opacity: 0.06; text-transform: uppercase;
            z-index: -1;
        }

        /* Bloques y títulos del contenido */
        .section-title {
            text-align: center; font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 2px 0; text-transform: uppercase;
        }
        .section-subtitle { text-align: center; font-size: 13px; font-weight: 700; margin: 0 0 8px 0; }
        .badge-bar {
            margin: 8px 0 12px 0; text-align: center; font-weight: 800; font-size: 12px; text-transform: uppercase;
            border: 1px dashed #111827; padding: 6px 8px; display: inline-block;
        }

        /* Tablas base (B/N friendly) */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; }
        th {
            background: #111827; color: #ffffff; border: 1px solid #1f2937;
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; text-align: center;
        }
        td { border: 1px solid #9ca3af; font-size: 12px; vertical-align: top; }
        tbody tr:nth-child(even) td { background: #f7f7f7; }  /* Zebra */
        .no-border { border: none !important; }

        /* Celdas especiales */
        .cell-center { text-align: center; }
        .cell-right { text-align: right; }
        .muted { color: #6b7280; }
        .mono  { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }

        /* Fila de receso (patrón sin depender de color) */
        .receso-time { font-weight: 800; text-align: center; background: #e5e7eb; }
        .receso-cell {
            font-weight: 800; text-align: center; color: #111827; background: repeating-linear-gradient(45deg, #f3f4f6, #f3f4f6 6px, #e5e7eb 6px, #e5e7eb 12px);
            letter-spacing: 6px;
        }

        /* Bloques de firmas */
        .signs { margin-top: 16px; }
        .sign-grid { width: 100%; border-collapse: separate; border-spacing: 10px; }
        .sign-box {
            height: 90px; border: 1px dashed #6b7280; border-radius: 8px; padding: 8px; position: relative;
        }
        .sign-line { position: absolute; left: 8px; right: 8px; bottom: 10px; border-top: 1px solid #111827; }
        .sign-role { position: absolute; left: 8px; right: 8px; bottom: -2px; font-size: 11px; text-align: center; }

        /* Info auxiliar */
        .info-grid { width: 100%; margin: 8px 0 12px 0; }
        .info-grid td { border: 0px solid white; padding: 6px 8px; }
        .tag { display: inline-block; border: 1px solid #374151; padding: 1px 6px; border-radius: 999px; font-size: 10px; font-weight: 700; }

        /* Bloque de observaciones y leyendas */
        .notes { font-size: 12px; margin-top: 6px; }
        .legend { font-size: 11px; margin-top: 6px; }

        /* Contenido principal */
        main { }

    </style>
</head>
<body>

@php
    use Carbon\Carbon;

    // Helpers para tiempos
    $parseTime = function($t) {
        $t = trim(strtolower($t));
        return Carbon::createFromFormat('g:ia', $t);
    };
    $parseRange = function($range) use ($parseTime) {
        [$ini, $fin] = array_map('trim', explode('-', strtolower($range)));
        return [$parseTime($ini), $parseTime($fin)];
    };

    // Ordenar horas únicas por inicio
    $horasUnicas = collect($horario)
        ->pluck('hora')
        ->filter()
        ->unique()
        ->sortBy(function($h) use ($parseRange) {
            [$ini, $fin] = $parseRange($h);
            return $ini->format('H:i');
        })
        ->values();

    // Receso dinámico (si se define $receso_inicio/$receso_fin). Fallback: 10:00am-10:30am tras 9:00am-10:00am
    $receso_inicio = $receso_inicio ?? '10:00am';
    $receso_fin    = $receso_fin    ?? '10:30am';

    // Cargas horarias por materia y por profesor (minutos)
    $cargasMateria  = [];   // materia_id => ['nombre' => , 'clave' => , 'min' => ]
    $cargasProfesor = [];   // profesor_id => ['nombre' => , 'min' => ]

    $totalMinutosGrupo = 0;

    foreach ($horario as $item) {
        if (!isset($item->hora) || empty($item->hora)) continue;
        [$ini, $fin] = $parseRange($item->hora);
        $min = $ini->diffInMinutes($fin);
        $totalMinutosGrupo += $min;

        $am = $item->asignacionMateria ?? null;
        if ($am) {
            $materiaId = $am->materia->id ?? null;
            $profId    = $am->profesor->id ?? null;

            if ($materiaId) {
                $cargasMateria[$materiaId]['nombre'] = $am->materia->nombre ?? '—';
                $cargasMateria[$materiaId]['clave']  = $am->materia->clave  ?? ($item->asignacionMateria->materia->clave ?? '—');
                $cargasMateria[$materiaId]['min']    = ($cargasMateria[$materiaId]['min'] ?? 0) + $min;
            }
            if ($profId) {
                $nombreProf = trim(($am->profesor->nombre ?? '').' '.($am->profesor->apellido_paterno ?? '').' '.($am->profesor->apellido_materno ?? ''));
                $cargasProfesor[$profId]['nombre'] = $nombreProf ?: '—';
                $cargasProfesor[$profId]['min']    = ($cargasProfesor[$profId]['min'] ?? 0) + $min;
            }
        }
    }

    $minutosAHoras = function($min) {
        $h = intdiv($min, 60);
        $m = $min % 60;
        return sprintf('%02d:%02d', $h, $m);
    };

    $grupoHorasStr = $minutosAHoras($totalMinutosGrupo);

    // Metadatos
    $folio             = $folio             ?? ('CUM-HOR-'.strtoupper($licenciatura_nombre->nombre_corto ?? ($licenciatura_nombre->nombre ?? 'LIC')).'-'.str_pad((string)($generacion->id ?? 0), 3, '0', STR_PAD_LEFT));
    $version           = $version           ?? 'v1';
    $estado_documento  = $estado_documento  ?? 'Publicado';
    $turno             = $turno             ?? 'Matutino';
    $periodo_inicio    = isset($periodo_inicio) ? Carbon::parse($periodo_inicio) : Carbon::now()->startOfMonth();
    $periodo_fin       = isset($periodo_fin)    ? Carbon::parse($periodo_fin)    : Carbon::now()->endOfMonth();
    $document_hash     = $document_hash     ?? substr(hash('sha256', $folio.'|'.$version.'|'.($generacion->generacion ?? '').'|'.($cuatrimestre ?? '').'|'.now()), 0, 10);
    $tipo_emision      = strtoupper($tipo_emision ?? 'Original');  // ORIGINAL | COPIA
    $fecha_actualizacion = $fecha_actualizacion ?? \Carbon\Carbon::now()->format('d/m/Y H:i:s');


@endphp

<!-- Encabezado repetible -->
<header style="margin-bottom: 20px">
    <div class="container">
        <table class="header-table">
            <tr>
                <td style="width: 22%; text-align: left;">
                    <img class="logo" src="{{ public_path('storage/letra2.jpg') }}" alt="Escudo institucional">
                </td>
                <td class="title-wrap" style="width: 56%;">
                    <div class="inst">CENTRO UNIVERSITARIO MOCTEZUMA</div>
                    <div class="subtitle">Licenciatura en {{ strtoupper($licenciatura_nombre->nombre) }}</div>
                    <div class="chips">
                        <span class="chip">Cuat.: {{$cuatrimestre}}°</span>
                        <span class="chip">Turno: {{$turno}}</span>
                        <span class="chip">Generación: {{$generacion->generacion}}</span>
                    </div>

                </td>
                <td style="width: 22%; text-align: right;">
                    <img class="logo" src="{{ public_path('storage/licenciaturas/'.$licenciatura_nombre->imagen) }}" alt="Logotipo de la licenciatura">
                </td>
            </tr>
        </table>
    </div>
</header>

<!-- Pie repetible -->
<footer>
    <div class="container">
        <div class="footer-top">
            {{ $escuela->nombre }} · C.C.T. {{ $escuela->CCT }}
        </div>
        <div class="footer-mid">
            C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }}, Col. {{ $escuela->colonia }}, C.P. {{ $escuela->codigo_postal }}, {{ $escuela->ciudad }}, {{ $escuela->estado }} · Tel. {{ $escuela->telefono }}
        </div>
        <div class="footer-bot">
            <div>Fecha de expedición: {{ \Carbon\Carbon::now('America/Mexico_City')->format('d/m/Y H:i:s') }} (Hora local)</div>
            <div>Pág. <span class="page-number"></span></div>

        </div>
    </div>
</footer>

<!-- Marca de agua -->
<div class="watermark">
    <img src="{{ public_path('storage/letra.png') }}" alt="Marca de agua institucional" style="width: 100%; height: auto;">
</div>


<!-- Contenido principal -->
<main>
    <div class="container">

        @php
            // Utilidad para saber si tras una hora actual debe insertarse el receso
            $shouldInsertRecesoAfter = function($horaActual) use ($parseRange, $receso_inicio) {
                try {
                    [$ini, $fin] = $parseRange($horaActual);
                    $recesoIni = Carbon::createFromFormat('g:ia', strtolower($receso_inicio));
                    return $fin->format('H:i') === $recesoIni->format('H:i');
                } catch (\Throwable $e) { return false; }
            };
        @endphp

        <!-- TABLA DE HORARIO -->
        <table style="margin-top:130px">
            <thead>
                <tr>
                    <th style="width: 140px;">Hora</th>
                    @foreach ($dias as $dia)
                        <th>{{ strtoupper($dia->dia) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($horasUnicas as $hora)
                    <tr>
                        <td class="cell-center mono">{{ strtoupper($hora) }}</td>
                        @foreach ($dias as $dia)
                            @php
                                $registro = collect($horario)->first(function($item) use ($hora, $dia) {
                                    return ($item->hora === $hora) && ((int)$item->dia_id === (int)$dia->id);
                                });
                            @endphp
                            <td>
                                @if ($registro)
                                    <div><strong>{{ $registro->asignacionMateria->materia->nombre ?? '—' }}</strong></div>
                                    <div class="muted">
                                        @php
                                            $tipo = $registro->tipo ?? null; // 'Teoría'|'Práctica'|'Lab'|'Virtual'
                                            $aula = $registro->aula ?? null;
                                        @endphp
                                        @if($tipo) <span class="tag">{{ strtoupper($tipo) }}</span> @endif
                                        @if($aula) <span class="tag">Aula: {{ $aula }}</span> @endif
                                    </div>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    {{-- Receso dinámico --}}
                    @if ($shouldInsertRecesoAfter($hora))
                        <tr>
                            <td class="receso-time mono">{{ strtoupper($receso_inicio.'-'.$receso_fin) }}</td>
                            <td class="receso-cell" colspan="{{ max(1, count($dias)) }}">RECESO</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>



        <!-- RESUMEN DE CARGAS HORARIAS -->
        <h2 class="section-title" style="margin-top: 14px;">Resumen de cargas horarias</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 90px;">Clave</th>
                    <th>Materia</th>
                    <th style="width: 220px;">Profesor</th>
                </tr>
            </thead>
            <tbody>
                @php $idx = 1; @endphp
                @foreach ($materias as $m)
                    @php
                        // Buscar id de la materia en cargas para horas
                        $materiaId = $m->id ?? ($m->materia_id ?? null);
                        $min = $materiaId && isset($cargasMateria[$materiaId]['min']) ? $cargasMateria[$materiaId]['min'] : 0;
                        $hh = $minutosAHoras($min);
                        $profNombre = trim(($m->profesor ?? '').' '.($m->apellido_paterno ?? '').' '.($m->apellido_materno ?? ''));
                    @endphp
                    <tr>
                        <td class="cell-center">{{ $idx++ }}</td>
                        <td class="mono cell-center">{{ $m->clave ?? '—' }}</td>
                        <td>{{ $m->nombre ?? '—' }}</td>
                        <td>{{ $profNombre ?: '—' }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>

        <!-- CARGA POR PROFESOR -->
        <h2 class="section-title" style="margin-top: 10px;">Carga por profesor (horas/semana)</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Profesor</th>
                    <th style="width: 140px;">Horas/Sem</th>
                </tr>
            </thead>
            <tbody>
                @php $pidx=1; @endphp
                @foreach ($cargasProfesor as $row)
                    <tr>
                        <td class="cell-center">{{ $pidx++ }}</td>
                        <td>{{ $row['nombre'] ?? '—' }}</td>
                        <td class="cell-center mono">{{ $minutosAHoras($row['min'] ?? 0) }}</td>
                    </tr>
                @endforeach
                @if(empty($cargasProfesor))
                    <tr><td colspan="3" class="cell-center muted">Sin datos de carga por profesor.</td></tr>
                @endif
            </tbody>
        </table>






        <!-- Historial de cambios (opcional, mostrar si se pasa $historial) -->
        @if (!empty($historial ?? []))
            <h2 class="section-title" style="margin-top: 10px;">Historial de modificaciones</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 140px;">Fecha/Hora</th>
                        <th style="width: 160px;">Usuario</th>
                        <th>Cambio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($historial as $h)
                        <tr>
                            <td class="mono">{{ \Carbon\Carbon::parse($h['fecha'] ?? now())->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $h['usuario'] ?? '—' }}</td>
                            <td>{{ $h['descripcion'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Auditoría ligera -->
        <div class="meta-line" style="margin-top: 8px;">
            Documento: <span class="mono">{{ $folio }}</span> ({{ $version }})
        </div>
    </div>
</main>

</body>
</html>
