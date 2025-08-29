<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HISTORIAL ACADÉMICO | {{ $alumno->matricula }}</title>

    <style>
        /* ===== Tamaño página y tipografía ===== */
        @page { size: legal portrait; margin: 18px 24px 24px 24px; } /* Legal 8.5x14" */
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
        html,body{ font-family:'calibri'; color:#1f2937; font-size:10px; line-height:1.25; }

        /* ===== Utilidades ===== */
        .text-center{ text-align:center; } .text-right{ text-align:right; }
        .uppercase{ text-transform:uppercase; } .fw-700{ font-weight:700; }
        .muted{ color:#6b7280; } .mb-6{ margin-bottom:12px; } .mb-8{ margin-bottom:14px; }
        .mb-10{ margin-bottom:18px; } .chip{ display:inline-block; padding:2px 6px; border-radius:10px; border:1px solid #d1d5db; background:#f9fafb; font-weight:700; }



        .title{
            text-align:center; font-weight:700; letter-spacing:.5px;
            font-size:13px; padding:4px 0 7px 0; border-bottom:1px solid #cfe7fb; margin-bottom:8px;
        }

        /* ===== Tabla meta (escuela/alumno) ===== */
        table { width:100%; border-collapse:collapse; }
        .meta{ border:1px solid #e5e7eb; }
        .meta td{ padding:4px 6px; text-align:center; }
        .meta .h{ background:#f3f4f6; font-weight:700; }
        .meta .v{ font-weight:700; font-size:11px; }

        /* ===== Cards resumen compactas ===== */
        .cards td{ width:25%; padding:0px; }
        .card{ border:1px solid #e5e7eb; }
        .card .head{ background:#f3f4f6; font-weight:700; text-align:center; padding:0px 4px; }
        .card .body{ text-align:center; padding:0px 4px; font-size:12px; font-weight:700; }

        /* ===== Columnas para lista de materias ===== */
        .columns{ display:flex; gap:10px; }
        .col{ width:100%; }

        /* ===== Tabla materias compacta ===== */
        .grades thead th{
            background:#0ea5e9; color:#fff; font-weight:700; padding:0px 5px; font-size:9px; letter-spacing:.3px;
        }
        .grades tbody td{ border-bottom:1px solid #eef2f7; padding:0px 5px; font-size:9px; }
        .grades tbody tr:nth-child(even){ background:#fbfdff; }
        .col-idx{ width:20px; text-align:center; }
        .col-clave{ width:58px; text-align:center; }
        .col-cred{ width:46px; text-align:center; }
        .col-cuat{ width:58px; text-align:center; }
        .col-name{ font-size:9px; text-transform:uppercase; }
        .col-score{ width:72px; text-align:center; font-weight:700; }
        .col-type{ width:78px; text-align:center; }

        .score{ display:inline-block; min-width:34px; padding:2px 5px; border-radius:7px; border:1px solid #d1d5db; background:#fff; }
        .score--ok{ background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
        .score--np{ background:#fff1f2; border-color:#fecdd3; color:#9f1239; }
        .score--rep{ background:#fef2f2; border-color:#fecaca; color:#991b1b; }

        /* ===== Footer fijo ===== */
        footer{
            position: fixed; left:0; right:0; bottom: 14px; text-align:center;
            font-size:9px; color:#4b5563; border-top:1px solid #d1d5db; padding-top:5px;
        }
        footer p{ margin:0; line-height:1.2; }

        /* Evitar cortes feos */
        tr, td, th{ page-break-inside:avoid; }


             /* ===== Encabezado ===== */
        .encabezado { margin-bottom:4px; width: 100% }
        .encabezado img{ width:70%; }
    </style>
</head>
<body>

    {{-- ===== Encabezado ===== --}}
    <div class="encabezado">
        <img src="{{ public_path('storage/encabezado.png') }}" alt="Encabezado">
    </div>

    <div class="title uppercase">Historial Académico</div>

    {{-- ===== Meta institución / alumno ===== --}}
    <table class="meta mb-8">
        <tr class="h">
            <td>Nombre de la escuela</td>
            <td>No. de acuerdo de incorporación</td>
            <td>Clave del centro de trabajo</td>
        </tr>
        <tr>
            <td class="v uppercase">{{ $escuela->nombre }}</td>
            <td class="v uppercase">{{ $licenciatura->RVOE ?? '-------' }}</td>
            <td class="v uppercase">{{ $escuela->CCT }}</td>
        </tr>
        <tr class="h">
            <td>Nombre(s)</td><td>Primer apellido</td><td>Segundo apellido</td>
        </tr>
        <tr>
            <td class="v uppercase">{{ $alumno->nombre }}</td>
            <td class="v uppercase">{{ $alumno->apellido_paterno }}</td>
            <td class="v uppercase">{{ $alumno->apellido_materno }}</td>
        </tr>
        <tr class="h">
            <td>Matrícula</td><td>Licenciatura</td><td>Modalidad</td>
        </tr>
        <tr>
            <td class="v uppercase">{{ $alumno->matricula }}</td>
            <td class="v uppercase">{{ $licenciatura->nombre }}</td>
            <td class="v uppercase"><span class="chip">Escolarizada</span></td>
        </tr>
    </table>

    {{-- ===== Cálculos resumen ===== --}}
    @php
        $promedio = \DB::table('calificaciones')
            ->where('alumno_id', $alumno->id)
            ->whereNotNull('calificacion')
            ->whereNotIn('calificacion', ['', '0', 'NP'])
            ->pluck('calificacion')->map(fn($v)=>(float)$v);
        $suma = $promedio->sum(); $cuenta = $promedio->count();
        $promedio = $cuenta ? floor($suma / $cuenta * 10) / 10 : '';

        $creditosAlumno = \DB::table('calificaciones')
            ->join('asignacion_materias','calificaciones.asignacion_materia_id','=','asignacion_materias.id')
            ->join('materias','asignacion_materias.materia_id','=','materias.id')
            ->where('calificaciones.alumno_id',$alumno->id)
            ->whereNotNull('calificaciones.calificacion')
            ->whereNotIn('calificaciones.calificacion', ['','0','NP'])
            ->where('materias.calificable','!=','false')
            ->where('calificaciones.calificacion','>',5)
            ->sum('materias.creditos');

        $creditosTotales = \DB::table('asignacion_materias')
            ->join('materias','asignacion_materias.materia_id','=','materias.id')
            ->where('asignacion_materias.licenciatura_id',$alumno->licenciatura_id)
            ->where('asignacion_materias.modalidad_id',$alumno->modalidad_id)
            ->whereNotNull('asignacion_materias.cuatrimestre_id')
            ->where('materias.calificable','!=','false')
            ->sum('materias.creditos');

        $materiasAprobadas = \DB::table('calificaciones')
            ->join('asignacion_materias','calificaciones.asignacion_materia_id','=','asignacion_materias.id')
            ->join('materias','asignacion_materias.materia_id','=','materias.id')
            ->where('calificaciones.alumno_id',$alumno->id)
            ->whereNotNull('calificaciones.calificacion')
            ->whereNotIn('calificaciones.calificacion', ['','0','NP'])
            ->where('materias.calificable','!=','false')
            ->where('calificaciones.calificacion','>',5)
            ->count();

        $materiasReprobadas = \DB::table('calificaciones')
            ->join('asignacion_materias','calificaciones.asignacion_materia_id','=','asignacion_materias.id')
            ->join('materias','asignacion_materias.materia_id','=','materias.id')
            ->where('calificaciones.alumno_id',$alumno->id)
            ->whereNotNull('calificaciones.calificacion')
            ->whereNotIn('calificaciones.calificacion', ['','0','NP'])
            ->where('materias.calificable','!=','false')
            ->where('calificaciones.calificacion','<',6)
            ->count();

        $materiasNoPresentadas = \DB::table('calificaciones')
            ->join('asignacion_materias','calificaciones.asignacion_materia_id','=','asignacion_materias.id')
            ->join('materias','asignacion_materias.materia_id','=','materias.id')
            ->where('calificaciones.alumno_id',$alumno->id)
            ->where(function($q){ $q->whereNull('calificaciones.calificacion')->orWhere('calificaciones.calificacion','NP'); })
            ->count();
    @endphp

    {{-- ===== Cards resumen compactas ===== --}}
    <table class="cards mb-10">
        <tr>
            <td>
                <table class="card" role="presentation"><tr><td class="head uppercase">Promedio</td></tr><tr><td class="body">{{ $promedio }}</td></tr></table>
            </td>
            <td>
                <table class="card" role="presentation"><tr><td class="head uppercase">Créditos</td></tr><tr><td class="body">{{ $creditosAlumno }} / {{ $creditosTotales }}</td></tr></table>
            </td>
            <td>
                <table class="card" role="presentation"><tr><td class="head uppercase">Aprobadas</td></tr><tr><td class="body">{{ $materiasAprobadas }}</td></tr></table>
            </td>
            <td>
                <table class="card" role="presentation"><tr><td class="head uppercase">Reprobadas / NP</td></tr><tr><td class="body">{{ $materiasReprobadas }} / {{ $materiasNoPresentadas }}</td></tr></table>
            </td>
        </tr>
    </table>

    {{-- ===== Construcción de filas (y división en dos columnas) ===== --}}
    @php
        $rows = [];
        foreach ($periodos as $periodo) {
            $materias = \DB::table('asignacion_materias')
                ->join('materias','asignacion_materias.materia_id','=','materias.id')
                ->where('asignacion_materias.licenciatura_id',$alumno->licenciatura_id)
                ->where('asignacion_materias.modalidad_id',$alumno->modalidad_id)
                ->where('asignacion_materias.cuatrimestre_id',$periodo->cuatrimestre->id)
                ->where('materias.calificable','!=','false')
                ->orderBy('materias.clave','asc')
                ->select('materias.*','asignacion_materias.id as asignacion_materia_id')
                ->get();

            foreach ($materias as $m) {
                $c = \DB::table('calificaciones')
                    ->where('alumno_id',$alumno->id)
                    ->where('asignacion_materia_id',$m->asignacion_materia_id)
                    ->first();

                $rows[] = (object)[
                    'clave' => $m->clave,
                    'creditos' => $c ? $m->creditos : '—',
                    'cuatri' => $m->cuatrimestre_id,
                    'nombre' => $m->nombre,
                    'calificacion' => $c ? $c->calificacion : null,
                    'tipo' => $c ? 'ORD' : 'EN PROCESO',
                ];
            }
        }

        $total = count($rows);
        $split = (int) ceil($total / 2);
        $colA = array_slice($rows, 0, $split);
        $colB = array_slice($rows, $split);
        $idxA = 1; $idxB = $split + 1;
    @endphp

    {{-- ===== Tabla en dos columnas ===== --}}
    <div class="columns">
        {{-- Columna A --}}
        <div class="col">
            {{-- ===== Tabla en una sola columna ===== --}}
<table class="grades">
    <thead>
        <tr class="uppercase">
            <th class="col-idx">#</th>
            <th class="col-clave">Clave</th>
            <th class="col-cred">Créd.</th>
            <th class="col-cuat">Cuat.</th>
            <th class="col-name">Asignatura</th>
            <th class="col-score">Calif.</th>
            <th class="col-type">Tipo</th>
        </tr>
    </thead>
    <tbody>
    @php $idx = 1; @endphp
    @foreach($rows as $r)
        @php
            $isNP  = ($r->calificacion === 'NP');
            $isRep = (is_numeric($r->calificacion) && $r->calificacion < 6);
            $isOK  = (is_numeric($r->calificacion) && $r->calificacion >= 6);
            $badge = $isOK ? 'score score--ok' : ($isNP ? 'score score--np' : ($isRep ? 'score score--rep' : 'score'));
        @endphp
        <tr>
            <td class="col-idx" style="line-height: 9px">{{ $idx++ }}</td>
            <td class="col-clave" style="line-height: 9px">{{ $r->clave }}</td>
            <td class="col-cred" style="line-height: 9px">{{ $r->creditos }}</td>
            <td class="col-cuat" style="line-height: 9px">{{ $r->cuatri }}</td>
            <td class="col-name" style="line-height: 9px">{{ $r->nombre }}</td>
            <td class="col-score" style="line-height: 9px">
                @if(!is_null($r->calificacion))
                  {{ $r->calificacion }}
                @else
                    <span class="chip">EN PROCESO</span>
                @endif
            </td>
            <td class="col-type" style="line-height: 0px">{{ $r->tipo }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

        </div>


    </div>

    {{-- ===== Footer ===== --}}
    <footer>
        <p class="uppercase fw-700">{{ $escuela->nombre }} · C.C.T. {{ $escuela->CCT }}</p>
        <p>
            C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }}, Col. {{ $escuela->colonia }},
            C.P. {{ $escuela->codigo_postal }}, Cd. {{ $escuela->ciudad }}, {{ $escuela->estado }} · Tel. {{ $escuela->telefono }}
        </p>
    </footer>
</body>
</html>
