<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificaciones - {{ $contexto->materia }}</title>
    <style>
        @page { margin: 32px 38px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 10px; }
        h1 { margin: 0; color: #006492; font-size: 18px; text-align: center; }
        .sub { margin: 4px 0 16px; text-align: center; color: #4b5563; }
        .meta { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .meta td { width: 50%; padding: 4px 6px; vertical-align: top; }
        .label { font-weight: bold; color: #006492; }
        table.cal { width: 100%; border-collapse: collapse; }
        .cal th { background: #006492; color: #fff; padding: 7px 6px; border: 1px solid #d1d5db; }
        .cal td { padding: 6px; border: 1px solid #d1d5db; }
        .center { text-align: center; }
        .summary { margin-top: 12px; text-align: right; font-weight: bold; }
        .sign { margin-top: 52px; width: 100%; }
        .sign td { width: 50%; text-align: center; padding: 0 20px; }
        .line { border-top: 1px solid #111827; padding-top: 5px; }
    </style>
</head>
<body>
    <h1>CENTRO UNIVERSITARIO MOCTEZUMA</h1>
    <div class="sub">Lista de calificaciones por docente</div>

    <table class="meta">
        <tr>
            <td><span class="label">Docente:</span> {{ trim($contexto->profesor_nombre.' '.$contexto->profesor_apellido_paterno.' '.$contexto->profesor_apellido_materno) }}</td>
            <td><span class="label">Materia:</span> {{ $contexto->materia }} @if($contexto->materia_clave) ({{ $contexto->materia_clave }}) @endif</td>
        </tr>
        <tr>
            <td><span class="label">Licenciatura:</span> {{ $contexto->licenciatura }}</td>
            <td><span class="label">Modalidad:</span> {{ $contexto->modalidad }}</td>
        </tr>
        <tr>
            <td><span class="label">Generación:</span> {{ $contexto->generacion }}</td>
            <td><span class="label">Cuatrimestre:</span> {{ $contexto->cuatrimestre }}°</td>
        </tr>
    </table>

    <table class="cal">
        <thead>
            <tr>
                <th style="width: 7%">No.</th>
                <th style="width: 19%">Matrícula</th>
                <th>Alumno</th>
                <th style="width: 18%">Calificación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $i => $alumno)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="center">{{ $alumno->matricula }}</td>
                    <td>{{ mb_strtoupper(trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre), 'UTF-8') }}</td>
                    <td class="center">{{ $calificaciones[$alumno->id] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Alumnos: {{ $alumnos->count() }} &nbsp; | &nbsp;
        Promedio numérico: {{ $promedio !== null ? number_format($promedio, 2) : '—' }}
    </div>

    <table class="sign">
        <tr>
            <td><div class="line">DOCENTE</div></td>
            <td><div class="line">CONTROL ESCOLAR</div></td>
        </tr>
    </table>
</body>
</html>
