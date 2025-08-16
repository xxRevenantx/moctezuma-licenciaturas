<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Horario Semiescolarizada | Lic. {{ $licenciatura_nombre->nombre }} | Gen. {{ $generacion->generacion }}</title>

    <style>
        /* Márgenes de página (reservan espacio para header/footer fijos) */
        @page { margin: 10px 45px 110px 45px; }

        /* Calibri para igualar el diseño anterior */
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
        header { position: fixed; top: 0; left: 0; right: 0; height: 112px; padding: 8px 0 6px 0; }
        footer {
            position: fixed; bottom: 1px; left: 0; right: 0;
            text-align: center; font-size: 11px; color: #334155;
            padding: 6px 0 0; border-top:1px solid #94a3b8;
        }
        footer p{ margin:0; line-height:14px; font-size:13px; }
        .container{ width:100%; }

        /* Barra de marca (idéntico look & feel) */
        .brand-bar{ width:100%; border-top:3px solid #0ea5e9; border-bottom:3px solid #0ea5e9; padding:10px 0 6px; text-align:center; letter-spacing:.5px;}
        .titulo{ font-size:18px; font-weight:700; margin:0; line-height:20px; }
        .subtitulo{ margin:4px 0 0; font-size:14px; color:#334155; }
        .logo-left{ position:absolute; top:18px; left:40px; height:50px; }
        .logo-right{ position:absolute; top:18px; right:40px; height:50px; }

        /* Marca de agua */
        .watermark{ position:fixed; top:58%; left:50%; transform:translate(-50%,-50%); width:100%; text-align:center; opacity:.06; z-index:-1; }
        .watermark img{ width:80%; }

        /* Chips / etiquetas */
        .chip{ display:inline-block; padding:3px 5px; border-radius:999px; background:#f1f5f9; color:#0f172a; font-size:11px; border:1px solid #e2e8f0; white-space:nowrap; }
        .tag{ display:inline-block; padding:2px 8px; border-radius:999px; background:#ecfeff; color:#155e75; border:1px solid #a5f3fc; font-size:10px; white-space:nowrap; }

        /* Tabla base */
        .table-wrap{ border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
        table{ width:100%; border-collapse:collapse; }
        thead th{ background:#cbd5e1; color:#0f172a; text-transform:uppercase; font-size:12px; letter-spacing:.5px; padding:8px 10px; text-align:center; }
        tbody td{ border-top:1px solid #e5e7eb; padding:8px 8px; vertical-align:middle; background:#fff; font-size:12px; }
        tbody tr:nth-child(odd) td{ background:#fcfcfd; }

        /* Receso */
        .receso-time { font-weight:700; text-align:center; background:#e5e7eb; }
        .receso-cell { font-weight:700; text-align:center; color:#0f172a; background:repeating-linear-gradient(45deg,#f3f4f6,#f3f4f6 6px,#e5e7eb 6px,#e5e7eb 12px); letter-spacing:6px; }

        /* Bloques de resumen */
        .summary{ margin-top:10px; border:1px solid #e5e7eb; border-radius:10px; padding:10px; background:#f8fafc; }
        .summary h4{ margin:0 0 6px; font-size:13px; text-transform:uppercase; color:#334155; }
        .summary .item{ display:inline-block; margin:2px 6px 2px 0; font-size:13px; }

        .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
        .muted{ color:#64748b; }
    </style>
</head>
<body>
@php
    use Carbon\Carbon;

    /* === Helpers de tiempo para receso dinámico === */
    $parseTime = function($t) { $t = trim(strtolower($t)); return Carbon::createFromFormat('g:ia', $t); };
    $parseRange = function($range) use ($parseTime) {
        [$ini, $fin] = array_map('trim', explode('-', strtolower($range)));
        return [$parseTime($ini), $parseTime($fin)];
    };

    $receso_inicio = $receso_inicio ?? '10:00am';
    $receso_fin    = $receso_fin    ?? '10:30am';

    $shouldInsertRecesoAfter = function($horaActual) use ($parseRange,$receso_inicio){
        try {
            [$ini,$fin] = $parseRange($horaActual);
            $recesoIni = Carbon::createFromFormat('g:ia', strtolower($receso_inicio));
            return $fin->format('H:i') === $recesoIni->format('H:i');
        } catch (\Throwable $e) { return false; }
    };

    /* Total semanal (grupo) */
    $totalMin = 0;
    foreach ($horario as $h) {
        if (!isset($h->hora) || empty($h->hora)) continue;
        [$ini,$fin] = $parseRange($h->hora);
        $totalMin += $ini->diffInMinutes($fin);
    }
    $fmtHoras = function($min){ $h=intdiv($min,60); $m=$min%60; return sprintf('%02d:%02d',$h,$m); };
    $totalSem = $fmtHoras($totalMin);

    /* ====== AGRUPAR POR PROFESOR (materias únicas + horas totales) ====== */
    $profesores = []; // [prof_id|'sin' => ['nombre'=>string,'materias'=>[mat_id=>['nombre','clave']], 'min'=>int]]
    foreach ($horario as $h) {
        $am = $h->asignacionMateria ?? null;
        $prof = $am?->profesor;
        $mat  = $am?->materia;

        $pid = $prof->id ?? 'sin';
        $nombreProf = trim(($prof->nombre ?? 'Sin asignar').' '.($prof->apellido_paterno ?? '').' '.($prof->apellido_materno ?? ''));

        if (!isset($profesores[$pid])) {
            $profesores[$pid] = ['nombre' => $nombreProf ?: 'Sin asignar', 'materias' => [], 'min' => 0];
        }

        if ($mat) {
            $matId = $mat->id ?? spl_object_id($mat);
            $profesores[$pid]['materias'][$matId] = [
                'nombre' => $mat->nombre ?? '—',
                'clave'  => $mat->clave ?? '—',
            ];
        }

        if (!empty($h->hora)) {
            [$ini,$fin] = $parseRange($h->hora);
            $profesores[$pid]['min'] += $ini->diffInMinutes($fin);
        }
    }

    /* Ordenar profesores alfabéticamente (Sin asignar al final) */
    uksort($profesores, function($a,$b) use ($profesores){
        if ($a === 'sin' && $b === 'sin') return 0;
        if ($a === 'sin') return 1;
        if ($b === 'sin') return -1;
        return mb_strtolower($profesores[$a]['nombre'],'UTF-8') <=> mb_strtolower($profesores[$b]['nombre'],'UTF-8');
    });
@endphp

<!-- HEADER -->
<header>
    <img class="logo-left"  src="{{ public_path('storage/letra2.jpg') }}" alt="Escudo">
    <img class="logo-right" src="{{ public_path('storage/licenciaturas/'.$licenciatura_nombre->imagen) }}" alt="Licenciatura">
    <div class="brand-bar">
        <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA</h1>
        <p class="subtitulo">HORARIO DE CLASES — LICENCIATURA EN {{ mb_strtoupper($licenciatura_nombre->nombre) }}</p>
        <div style="margin-top:6px;">
            <span class="chip">Modalidad: Semiescolarizada</span>
            <span class="chip">Generación: {{ $generacion->generacion }}</span>
            <span class="chip">Cuatrimestre: {{ $cuatrimestre }}°</span>
            <span class="chip">Total semanal: {{ $totalSem }} h</span>
        </div>
    </div>
</header>

<!-- FOOTER -->
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

<!-- CONTENIDO -->
<main>
    <div class="container" style="margin-top:130px">

        <!-- TABLA HORARIO LINEAL -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:160px;">Hora</th>
                        <th>Materia</th>
                        <th style="width:140px;">Clave</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($horario as $h)
                        <tr>
                            <td class="mono" style="text-align:center;">{{ strtoupper($h->hora) }}</td>
                            <td style="text-align:center;">
                                <div style="font-weight:700; font-size:14px;">
                                    {{ $h->asignacionMateria->materia->nombre }}
                                </div>
                                @php
                                    $tipo = $h->tipo ?? null;   /* Teoría | Práctica | Lab | Virtual */
                                    $aula = $h->aula ?? null;
                                @endphp
                                <div style="margin-top:2px;">
                                    @if($tipo)<span class="tag">{{ strtoupper($tipo) }}</span>@endif
                                    @if($aula)<span class="tag" style="margin-left:4px;">Aula: {{ $aula }}</span>@endif
                                </div>
                            </td>
                            <td class="mono" style="text-align:center;">{{ $h->asignacionMateria->materia->clave }}</td>
                        </tr>

                        {{-- RECESO DINÁMICO --}}
                        @if ($shouldInsertRecesoAfter($h->hora))
                            <tr>
                                <td class="receso-time mono">{{ strtoupper($receso_inicio.'-'.$receso_fin) }}</td>
                                <td class="receso-cell" colspan="2">RECESO</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- ASIGNACIÓN DE PROFESORES (AGRUPADA) -->
        <div class="summary">
            <h4>Asignación de profesores</h4>
            <table style="width:100%; border-collapse:separate; border-spacing:0 6px;">
                <thead>
                    <tr>
                        <th style="background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">#</th>
                        <th style="background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">Profesor</th>
                        <th style="background:#cbd5e1; width:50px; color:#0f172a; border:1px solid #e5e7eb;">Materias</th>
                        <th style=" background:#cbd5e1; color:#0f172a; border:1px solid #e5e7eb;">Horas/Sem</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i=1; @endphp
                    @forelse ($profesores as $pid => $data)
                        <tr>
                            <td class="mono" style="text-align:center; border:1px solid #e5e7eb; background:#fff;">{{ $i++ }}</td>
                            <td style="border:1px solid #e5e7eb; background:#fff;">
                                {{ $data['nombre'] }}
                            </td>
                            <td style="border:1px solid #e5e7eb; background:#fff;">
                                @if(count($data['materias']))
                                    <div>
                                        @foreach ($data['materias'] as $m)
                                            <span class="chip">{{ $m['nombre'] }} <span class="mono">({{ $m['clave'] }})</span></span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="muted">Sin materias</span>
                                @endif
                            </td>
                            <td class="mono" style="text-align:center; border:1px solid #e5e7eb; background:#fff;">
                                {{ $fmtHoras($data['min']) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center; color:#64748b; border:1px solid #e5e7eb; background:#fff;">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="summary">
            <div class="item"><strong>Total semanal del grupo:</strong> {{ $totalSem }} h</div>
        </div>
    </div>
</main>

</body>
</html>
