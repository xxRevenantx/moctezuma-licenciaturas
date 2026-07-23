<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 24px 26px 30px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 8px; }
        h1 { margin: 0; color: #006492; font-size: 17px; }
        .sub { margin: 3px 0 12px; color: #6b7280; font-size: 9px; }
        .meta { width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 4px; }
        .meta td { padding: 6px 8px; border: 1px solid #dbe4ea; border-radius: 6px; background: #f8fafc; }
        .meta strong { color: #006492; }
        table.reporte { width: 100%; border-collapse: collapse; }
        .reporte th { padding: 5px 4px; background: #006492; color: #fff; border: 1px solid #00567d; font-size: 7px; text-transform: uppercase; }
        .reporte td { padding: 4px; border: 1px solid #d7dee3; vertical-align: top; }
        .reporte tbody tr:nth-child(even) { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 5px; border-radius: 8px; font-weight: bold; }
        .ok { background: #dcfce7; color: #166534; }
        .warn { background: #fef3c7; color: #92400e; }
        .danger { background: #fee2e2; color: #991b1b; }
        .muted { color: #6b7280; }
        footer { position: fixed; bottom: -18px; left: 0; right: 0; text-align: center; color: #6b7280; font-size: 7px; }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    <p class="sub">Generado el {{ now()->translatedFormat('d \d\e F \d\e Y, H:i') }} · {{ $alumnos->count() }} alumno(s)</p>

    @if (!empty($filtros))
        <table class="meta">
            <tr>
                @foreach ($filtros as $nombre => $valor)
                    <td><strong>{{ $nombre }}:</strong> {{ $valor }}</td>
                    @if ($loop->iteration % 4 === 0 && !$loop->last)
                        </tr><tr>
                    @endif
                @endforeach
            </tr>
        </table>
    @endif

    @if ($tipo === 'bajos')
        <table class="reporte">
            <thead>
                <tr>
                    <th>#</th><th>Matrícula</th><th>Alumno</th><th>Licenciatura</th><th>Materia</th>
                    <th>Cuatrimestre</th><th>Profesor</th><th>Calificación</th><th>Riesgo</th>
                </tr>
            </thead>
            <tbody>
                @php($numero = 0)
                @forelse ($alumnos as $alumno)
                    @foreach ($alumno->calificaciones as $calificacion)
                        @php
                            $numero++;
                            $valor = strtoupper(trim((string) $calificacion->calificacion));
                            $numerica = is_numeric($valor);
                            $profesor = trim(collect([
                                optional($calificacion->profesor)->nombre,
                                optional($calificacion->profesor)->apellido_paterno,
                                optional($calificacion->profesor)->apellido_materno,
                            ])->filter()->implode(' '));
                        @endphp
                        <tr>
                            <td>{{ $numero }}</td>
                            <td>{{ $alumno->matricula ?: '—' }}</td>
                            <td>{{ trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}") }}</td>
                            <td>{{ optional($alumno->licenciatura)->nombre ?? '—' }}</td>
                            <td>{{ optional(optional($calificacion->asignacionMateria)->materia)->nombre ?? '—' }}</td>
                            <td>{{ optional(optional($calificacion->asignacionMateria)->cuatrimestre)->nombre_cuatrimestre ?? optional(optional($calificacion->asignacionMateria)->cuatrimestre)->cuatrimestre ?? '—' }}</td>
                            <td>{{ $profesor ?: '—' }}</td>
                            <td><span class="badge danger">{{ $valor }}</span></td>
                            <td>{{ $numerica ? 'Numérica ≤ 6' : 'No presentada' }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="9" style="text-align:center; padding:20px;">No hay resultados.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="reporte">
            <thead>
                <tr>
                    <th>#</th><th>Matrícula</th><th>Estado matrícula</th><th>CURP</th><th>Alumno</th>
                    <th>Licenciatura</th><th>Modalidad</th><th>Generación</th><th>Cuatrimestre</th>
                    <th>Sexo</th><th>Procedencia</th><th>Estado</th><th>Inscripción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alumnos as $alumno)
                    @php
                        $matricula = strtoupper(trim((string) $alumno->matricula));
                        $duplicada = (int) ($alumno->matricula_coincidencias ?? 1) > 1;
                        $valida = app(\App\Services\MatriculaService::class)->esValida($matricula) && !$duplicada;
                        $estadoMatricula = $matricula === '' ? 'Vacía' : ($duplicada ? 'Duplicada' : ($valida ? 'Válida' : 'Formato incorrecto'));
                        $clase = $valida ? 'ok' : ($duplicada ? 'danger' : 'warn');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $matricula ?: '—' }}</td>
                        <td><span class="badge {{ $clase }}">{{ $estadoMatricula }}</span></td>
                        <td>{{ $alumno->CURP ?: '—' }}</td>
                        <td>{{ trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}") }}</td>
                        <td>{{ optional($alumno->licenciatura)->nombre ?? '—' }}</td>
                        <td>{{ optional($alumno->modalidad)->nombre ?? '—' }}</td>
                        <td>{{ optional($alumno->generacion)->generacion ?? '—' }}</td>
                        <td>{{ optional($alumno->cuatrimestre)->nombre_cuatrimestre ?? optional($alumno->cuatrimestre)->cuatrimestre ?? '—' }}</td>
                        <td>{{ $alumno->sexo === 'M' ? 'Mujer' : 'Hombre' }}</td>
                        <td>{{ $alumno->foraneo === 'true' ? 'Foráneo' : 'Local' }}</td>
                        <td>{{ $alumno->status === 'true' ? 'Activo' : 'Baja' }}</td>
                        <td>{{ optional($alumno->created_at)?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="13" style="text-align:center; padding:20px;">No hay resultados.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <footer>Centro Universitario Moctezuma · Reporte de control de matrículas</footer>
</body>
</html>
