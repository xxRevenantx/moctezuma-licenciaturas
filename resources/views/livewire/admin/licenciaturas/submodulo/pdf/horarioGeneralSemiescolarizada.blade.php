<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>Horario General SemiEscolarizado</title>
</head>
<style>

      @page { margin:0px 10px 0px 10px; }
        body {
        /* width: 21.59cm;
        height: 27.94cm; */
        /* padding: 20px; */
        font-family: 'figtree', sans-serif;
        margin: auto;
        font-size: 15px;

    }

     body { font-family: sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #eee; }

    </style>

<body>

<h2 style="text-align: center;">Horario General SemiEscolarizada</h2>

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
                            return $h->hora === $hora &&
                                   $h->cuatrimestre_id === $col['cuatrimestre_id'] &&
                                   $h->licenciatura_id === $col['licenciatura_id'];
                        });

                        $materia = optional(optional($item)->asignacionMateria)->materia?->nombre;
                        $profesorObj = optional(optional($item)->asignacionMateria)->profesor;
                        $profesor = $profesorObj
                            ? trim($profesorObj->nombre . ' ' . $profesorObj->apellido_paterno . ' ' . $profesorObj->apellido_materno)
                            : '';
                        $color = $profesorObj?->color ?? '#FFFFFF';

                        // Determinar color de texto (blanco o negro) según luminancia del fondo
                        $r = hexdec(substr($color, 1, 2));
                        $g = hexdec(substr($color, 3, 2));
                        $b = hexdec(substr($color, 5, 2));
                        $luminancia = (0.299 * $r + 0.587 * $g + 0.114 * $b);
                        $textoColor = $luminancia > 186 ? '#000000' : '#FFFFFF';
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


<h2 style="text-align: center; margin-top:20px">Resumen de docentes y materias</h2>

<table>
    <thead>
        <tr>
            <th>Profesor</th>
            <th>Materias asignadas</th>
            <th>Total de horas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($materiasPorDocente as $docente)
            @php
                $color = $docente['color'];
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));
                $luminancia = (0.299 * $r + 0.587 * $g + 0.114 * $b);
                $textoColor = $luminancia > 186 ? '#000000' : '#FFFFFF';
            @endphp
            <tr>
                <td style="background-color: {{ $color }}; color: {{ $textoColor }}">
                    {{ $docente['nombre'] }}
                </td>
                <td>
                    @foreach($docente['materias'] as $materia)
                        • {{ $materia }}<br>
                    @endforeach
                </td>
                <td>{{ $docente['total_horas'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


</body>
</html>
