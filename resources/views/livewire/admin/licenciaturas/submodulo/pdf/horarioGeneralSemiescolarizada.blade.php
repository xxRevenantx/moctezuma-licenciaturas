<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>Horario General Semiescolarizada</title>
    <style>
        @page { margin: 10px 10px 10px 10px; }
        body { font-family: 'figtree', sans-serif; font-size: 12px; color:#111; }
        h2 { margin: 0 0 10px 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 6px 4px; text-align: center; }
        th { background-color: #eee; font-weight: 700; }
        .left { text-align: left; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    <h2 style="text-align:center; line-height:18px">
        Centro Universitario Moctezuma<br>
        Horario General Semiescolarizada
    </h2>

    {{-- Tabla de horarios por Cuatrimestre/Licenciatura --}}
    <table>
        <thead>
            <tr>
                <th>Hora</th>
                @foreach($columnasUnicas as $col)
                    <th>{{ $col['etiqueta'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($horasUnicas as $hora)
                <tr>
                    <td><strong>{{ $hora }}</strong></td>
                    @foreach ($columnasUnicas as $col)
                        @php
                            $item = $horarios->first(function ($h) use ($hora, $col) {
                                return $h->hora === $hora
                                    && $h->cuatrimestre_id === $col['cuatrimestre_id']
                                    && $h->licenciatura_id === $col['licenciatura_id'];
                            });

                            $materia     = optional(optional($item)->asignacionMateria)->materia?->nombre;
                            $profesorObj = optional(optional($item)->asignacionMateria)->profesor;
                            $profesor    = $profesorObj
                                ? trim($profesorObj->nombre . ' ' . $profesorObj->apellido_paterno . ' ' . $profesorObj->apellido_materno)
                                : '';
                            $color       = $profesorObj?->color ?? '#FFFFFF';

                            // Texto negro/blanco según luminancia
                            $r = hexdec(substr($color,1,2)); $g = hexdec(substr($color,3,2)); $b = hexdec(substr($color,5,2));
                            $l = (0.299*$r + 0.587*$g + 0.114*$b);
                            $textoColor = $l > 186 ? '#000000' : '#FFFFFF';
                        @endphp
                        <td style="background-color: {{ $color }}; color: {{ $textoColor }}">
                            @if ($item)
                                <div><strong>{{ $materia }}</strong></div>
                                <div style="font-size: 10px">{{ $profesor }}</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    {{-- ===== Materias del Profesor y Horas Totales ===== --}}
    <h2 style="text-align:center; margin: 6px 0 10px;">Materias del Profesor y Horas Totales</h2>

    <table>
        <thead>
            <tr>
                <th>Profesor</th>
                <th>Materias (únicas)</th>
                <th>Total de horas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resumenDocentes as $row)
                @php
                    $bg = $row['color'] ?? '#e5e7eb';
                    $r = hexdec(substr($bg,1,2)); $g = hexdec(substr($bg,3,2)); $b = hexdec(substr($bg,5,2));
                    $l = (0.299*$r + 0.587*$g + 0.114*$b);
                    $txt = $l > 186 ? '#000' : '#FFF';
                @endphp
                <tr>
                    <td style="background-color: {{ $bg }}; color: {{ $txt }}; font-weight:600;">
                        {{ $row['nombre'] }}
                    </td>
                    <td class="left">
                        @if(count($row['materias']))
                            @foreach($row['materias'] as $m)
                                • {{ $m['nombre'] }}
                                  <span style="color:#555;">({{ $m['clave'] }})</span>
                                  — Lic.: <span style="color:#444;">{{ $m['licenciatura'] }}</span><br>
                            @endforeach
                        @else
                            <em style="color:#888;">Sin materias</em>
                        @endif
                    </td>
                    <td>{{ $row['total_horas'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Sin información de docentes.</td>
                </tr>
            @endforelse

            <tr>
                <td colspan="3" style="text-align:center; font-weight:700;">
                    Total general de horas: {{ $totalGeneralHoras }}
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>
