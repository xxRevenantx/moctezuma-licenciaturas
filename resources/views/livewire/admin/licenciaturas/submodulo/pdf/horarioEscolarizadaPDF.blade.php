<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Horario Escolarizado | Cua: {{$cuatrimestre}}° | Lic. {{$licenciatura_nombre->nombre}}</title>

    <style>
        /* Márgenes de página (reservan espacio para header/footer fijos) */
        @page { margin: 10px 45px 10px 45px; }

        /* Calibri como en el diseño base */
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

        /* Header & Footer (fijos) */
        header {
            position: fixed; top: 0; left: 0; right: 0; height: 112px;
            padding: 8px 0 6px 0;
        }
        footer {
            position: fixed; bottom: 1px; left: 0; right: 0;
            text-align: center; font-size: 11px; color: #334155;
            padding: 6px 0 0; border-top:1px solid #94a3b8;
        }
        footer p{ margin:0; line-height:14px; font-size:13px; }

        /* Barra de marca (idéntico estilo del primer PDF) */
        .brand-bar{ width:100%; border-top:3px solid #0ea5e9; border-bottom:3px solid #0ea5e9; padding:10px 0 6px; text-align:center; letter-spacing:.5px;}
        .titulo{ font-size:18px; font-weight:700; margin:0; line-height:20px; }
        .subtitulo{ margin:4px 0 0; font-size:14px; color:#334155; }
        .logo-left{ position:absolute; top:18px; left:40px; height:50px; }
        .logo-right{ position:absolute; top:18px; right:40px; height:50px; }

        /* Marca de agua */
        .watermark{ position:fixed; top:58%; left:50%; transform:translate(-50%,-50%); width:100%; text-align:center; opacity:.06; z-index:-1; }
        .watermark img{ width:80%; }

        /* Chips / etiquetas */
        .chip{ display:inline-block; padding:3px 8px; border-radius:999px; background:#f1f5f9; color:#0f172a; font-size:11px; border:1px solid #e2e8f0; white-space:nowrap; }
        .tag{ display:inline-block; padding:2px 8px; border-radius:999px; background:#ecfeff; color:#155e75; border:1px solid #a5f3fc; font-size:10px; white-space:nowrap; }

        /* Tabla principal (look del primero) */
        .table-wrap{ border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
        table{ width:100%; border-collapse:collapse; }
        thead th{ background:#cbd5e1; color:#0f172a; text-transform:uppercase; font-size:12px; letter-spacing:.5px; padding:8px 10px; text-align:center; }
        tbody td{ border-top:1px solid #e5e7eb; padding:8px 8px; vertical-align:middle; background:#fff; font-size:12px; }
        tbody tr:nth-child(odd) td{ background:#fcfcfd; }
        .col-hora{ width:160px; text-align:center; }

        /* Receso (estilo discreto) */
        .receso-time { font-weight:700; text-align:center; background:#e5e7eb; }
        .receso-cell { font-weight:700; text-align:center; color:#0f172a; background:repeating-linear-gradient(45deg,#f3f4f6,#f3f4f6 6px,#e5e7eb 6px,#e5e7eb 12px); letter-spacing:6px; }

        /* Bloques de resumen (idéntico al primero) */
        .summary{ margin-top:10px; border:1px solid #e5e7eb; border-radius:10px; padding:10px; background:#f8fafc; }
        .summary h4{ margin:0 0 6px; font-size:13px; text-transform:uppercase; color:#334155; }
        .summary .item{ display:inline-block; margin:2px 6px 2px 0; font-size:13px; }

        /* Leyenda */
        .legend{ margin-top:8px; font-size:13px; color:#475569; }
        .legend .swatch{ display:inline-block; width:10px; height:10px; border-radius:2px; margin-right:6px; vertical-align:middle; border:1px solid #cbd5e1; }

        /* Miscelánea */
        .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
        .muted{ color:#64748b; }
        .container{ width:100%; }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;

    /* ===== Helpers de tiempo ===== */
    $parseTime = function($t) { $t = trim(strtolower($t)); return Carbon::createFromFormat('g:ia', $t); };
    $parseRange = function($range) use ($parseTime) {
        [$ini, $fin] = array_map('trim', explode('-', strtolower($range)));
        return [$parseTime($ini), $parseTime($fin)];
    };

    /* Ordenar horas únicas por inicio */
    $horasUnicas = collect($horario)
        ->pluck('hora')->filter()->unique()
        ->sortBy(function($h) use ($parseRange){ [$ini] = $parseRange($h); return $ini->format('H:i'); })
        ->values();

    /* Receso configurable */
    $receso_inicio = $receso_inicio ?? '10:00am';
    $receso_fin    = $receso_fin    ?? '10:30am';

    /* Cargas (minutos) por materia/profesor */
    $cargasMateria = []; $cargasProfesor = []; $totalMinutosGrupo = 0;
    foreach ($horario as $item) {
        if (!isset($item->hora) || empty($item->hora)) continue;
        [$ini,$fin] = $parseRange($item->hora); $min = $ini->diffInMinutes($fin); $totalMinutosGrupo += $min;

        $am = $item->asignacionMateria ?? null;
        if ($am) {
            $materiaId = $am->materia->id ?? null; $profId = $am->profesor->id ?? null;
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
    $minutosAHoras = function($min){ $h=intdiv($min,60); $m=$min%60; return sprintf('%02d:%02d',$h,$m); };
    $grupoHorasStr = $minutosAHoras($totalMinutosGrupo);

    /* Metadatos */
    $folio = $folio ?? ('CUM-HOR-'.strtoupper($licenciatura_nombre->nombre_corto ?? ($licenciatura_nombre->nombre ?? 'LIC')).'-'.str_pad((string)($generacion->id ?? 0),3,'0',STR_PAD_LEFT));
    $version = $version ?? 'v1';
    $turno = $turno ?? 'Matutino';
@endphp

<!-- HEADER con barra de marca y logotipos -->
<header>
    <img class="logo-left"  src="{{ public_path('storage/letra2.jpg') }}" alt="Escudo">
    <img class="logo-right" src="{{ public_path('storage/licenciaturas/'.$licenciatura_nombre->imagen) }}" alt="Licenciatura">

    <div class="brand-bar">
        <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA</h1>
        <p class="subtitulo">HORARIO DE CLASES — LICENCIATURA EN {{ mb_strtoupper($licenciatura_nombre->nombre) }}</p>
        <div style="margin-top:6px;">
            <span class="chip">Cuat.: {{$cuatrimestre}}°</span>
            <span class="chip">Turno: {{$turno}}</span>
            <span class="chip">Generación: {{$generacion->generacion}}</span>
            <span class="chip">Total semanal grupo: {{ $grupoHorasStr }} h</span>
        </div>
    </div>
</header>

<!-- FOOTER con datos institucionales -->
<footer>
    <p><strong>{{ $escuela->nombre }}</strong> — C.C.T. {{ $escuela->CCT }}</p>
    <p>
        C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }},
        Col. {{ $escuela->colonia }}, C.P. {{ $escuela->codigo_postal }},
        {{ $escuela->ciudad }}, {{ $escuela->estado }} · Tel. {{ $escuela->telefono }}
    </p>
    <p><strong>Fecha de expedición:</strong> {{ \Carbon\Carbon::now('America/Mexico_City')->format('d/m/Y H:i:s') }}</p>
</footer>

<!-- Marca de agua -->
<div class="watermark">
    <img src="{{ public_path('storage/letra.png') }}" alt="Marca de agua">
</div>

<main>
    <div class="container">

        @php
            /* Insertar fila de RECESO después de la hora que termina justo al inicio del receso */
            $shouldInsertRecesoAfter = function($horaActual) use ($parseRange,$receso_inicio){
                try {
                    [$ini,$fin] = $parseRange($horaActual);
                    $recesoIni = Carbon::createFromFormat('g:ia', strtolower($receso_inicio));
                    return $fin->format('H:i') === $recesoIni->format('H:i');
                } catch (\Throwable $e) { return false; }
            };
        @endphp

        <!-- MATRIZ HORARIA -->
        <div class="table-wrap" style="margin-top:130px">
            <table>
                <thead>
                <tr>
                    <th class="col-hora">Hora</th>
                    @foreach ($dias as $dia)
                        <th>{{ strtoupper($dia->dia) }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($horasUnicas as $hora)
                    <tr>
                        <td class="col-hora"><span class="chip mono" style="font-size:13px">{{ strtoupper($hora) }}</span></td>
                        @foreach ($dias as $dia)
                            @php
                                $registro = collect($horario)->first(function($item) use ($hora, $dia) {
                                    return ($item->hora === $hora) && ((int)$item->dia_id === (int)$dia->id);
                                });
                            @endphp
                            <td style="text-align:center;">
                                @if ($registro)
                                    <div style="font-weight:700; font-size:14px;">
                                        {{ $registro->asignacionMateria->materia->nombre ?? '—' }}
                                    </div>
                                    <div style="margin-top:4px;">
                                        @php
                                            $tipo = $registro->tipo ?? null;   /* Teoría | Práctica | Lab | Virtual */
                                            $aula = $registro->aula ?? null;
                                        @endphp
                                        @if($tipo)<span class="tag">{{ strtoupper($tipo) }}</span>@endif
                                        @if($aula)<span class="tag" style="margin-left:4px;">Aula: {{ $aula }}</span>@endif
                                    </div>
                                @else
                                    <span class="chip">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    @if ($shouldInsertRecesoAfter($hora))
                        <tr>
                            <td class="receso-time mono">{{ strtoupper($receso_inicio.'-'.$receso_fin) }}</td>
                            <td class="receso-cell" colspan="{{ max(1, count($dias)) }}">RECESO</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- RESUMEN DE CARGAS (presentación tipo “summary” del diseño base) -->
        <div class="summary">
            <h4>Resumen de cargas horarias</h4>
            <table style="width:100%; border-collapse:separate; border-spacing:0 6px;">
                <thead>
                <tr>
                    <th style="width:30px; background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">#</th>
                    <th style="width:90px; background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">Clave</th>
                    <th style="background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">Materia</th>
                    <th style="width:220px; background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">Profesor</th>
                </tr>
                </thead>
                <tbody>
                @php $idx=1; @endphp
                @foreach ($materias as $m)
                    @php
                        $materiaId = $m->id ?? ($m->materia_id ?? null);
                        $min = $materiaId && isset($cargasMateria[$materiaId]['min']) ? $cargasMateria[$materiaId]['min'] : 0;
                        $hh = $minutosAHoras($min);
                        $profNombre = trim(($m->profesor ?? '').' '.($m->apellido_paterno ?? '').' '.($m->apellido_materno ?? ''));
                    @endphp
                    <tr>
                        <td class="mono" style="text-align:center; border:1px solid #e5e7eb; background:#fff;">{{ $idx++ }}</td>
                        <td class="mono" style="text-align:center; border:1px solid #e5e7eb; background:#fff;">{{ $m->clave ?? '—' }}</td>
                        <td style="border:1px solid #e5e7eb; background:#fff;">{{ $m->nombre ?? '—' }}</td>
                        <td style="border:1px solid #e5e7eb; background:#fff;">{{ $profNombre ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="item" style="margin-top:6px;"><strong>Total semanal del grupo:</strong> {{ $grupoHorasStr }} h</div>
        </div>


        <!-- HISTORIAL (opcional) -->
        @if (!empty($historial ?? []))
            <div class="summary">
                <h4>Historial de modificaciones</h4>
                <table style="width:100%; border-collapse:separate; border-spacing:0 6px;">
                    <thead>
                    <tr>
                        <th style="width:140px; background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">Fecha/Hora</th>
                        <th style="width:160px; background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">Usuario</th>
                        <th style="background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">Cambio</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($historial as $h)
                        <tr>
                            <td class="mono" style="border:1px solid #e5e7eb; background:#fff;">{{ \Carbon\Carbon::parse($h['fecha'] ?? now())->format('d/m/Y H:i:s') }}</td>
                            <td style="border:1px solid #e5e7eb; background:#fff;">{{ $h['usuario'] ?? '—' }}</td>
                            <td style="border:1px solid #e5e7eb; background:#fff;">{{ $h['descripcion'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif


    </div>
</main>

</body>
</html>
