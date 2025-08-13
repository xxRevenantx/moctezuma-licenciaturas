<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <title>Horario Semiescolarizada | {{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}</title>

    <style>
        @page { margin: 10px 45px 20px 45px; }
        body {
            font-family: 'figtree', sans-serif;
            margin: 0 auto;
            font-size: 14px;
            color: #111827;
        }

        .titulo {
            font-weight: bold;
            color: #4a5568;
            text-align: center;
            font-size: 22px;
            margin-top: 50px;
            padding: 10px 0;
            border-top: 2px solid #4a5568;
            border-bottom: 2px solid #4a5568;
            display: inline-block;
        }

        .subtitulo {
            text-align: center;
            font-size: 16px;
            padding: 3px 0;
            margin: 0;
            font-weight: bold;
        }

        .meta {
            width: 100%;
            margin: 12px 0 6px 0;
            border-collapse: collapse;
            font-size: 13px;
        }
        .meta td {
            padding: 4px 6px;
            vertical-align: middle;
        }
        .meta .et { color:#374151; font-weight:600; width: 110px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        th, td {
            padding: 8px 10px;
            text-align: left;
        }
        th {
            border: 1px solid #2d2d2d;
            background: #638acd;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            font-size: 12px;
        }
        td {
            border: 1px solid #8a8a8a;
            font-size: 12px;
        }

        .w-idx { width: 40px; text-align:center; }
        .w-hora { width: 140px; text-align:center; white-space: nowrap; }
        .w-dia  { width: 120px; text-align:center; }
        .w-mat  { width: auto; }
        .w-lic  { width: 220px; }

        .watermark {
            position: fixed;
            top: 68%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            z-index: -1;
            opacity: 0.08;
            text-align: center;
        }
        .watermark img { width: 70%; }

        img.img1 { position: absolute; top: 10px; left: 10px; }
        img.img2 { position: absolute; top: 10px; right: 10px; }

        footer {
            position: absolute;
            bottom: 0;
            left: 0;
            text-align: center;
            font-size: 12px;
            width: 100%;
            border-top: 1px solid #4a5568;
            border-bottom: 1px solid #4a5568;
            padding: 6px 0;
        }
        footer p { margin: 0; padding: 0; }
    </style>
</head>
<body>

    {{-- Marca de agua --}}
    <div class="watermark">
        <img src="{{ public_path('storage/letra.png') }}" alt="Watermark">
    </div>

    {{-- Encabezado / Título --}}
    <div style="text-align:center;">
        <img class="img1" src="{{ public_path('storage/letra2.jpg') }}" alt="Logo" height="100" width="100">
        <h1 class="titulo">CENTRO UNIVERSITARIO MOCTEZUMA</h1>
    </div>
    <p class="subtitulo">HORARIO DE CLASES</p>

    {{-- Datos del docente --}}
    <table class="meta">
        <tr>
            <td>Docente:</td>
            <td>
                {{ mb_strtoupper(trim($profesor->nombre.' '.$profesor->apellido_paterno.' '.$profesor->apellido_materno)) }}
            </td>
        </tr>
        <tr>
            <td>Modalidad:</td>
            <td>{{ $modalidad->nombre ?? 'Semiescolarizada' }}</td>
        </tr>
        <tr>
            <td>Fecha:</td>
            <td>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    {{-- Tabla principal --}}
    @php
        // Asegura colección y orden ASC por hora de inicio (ej. "8:00am-9:00am")
        $coleccion = collect($registros ?? []);
        $ordenados = $coleccion->sortBy(function ($r) {
            $ini = strtolower(trim(explode('-', $r->hora)[0] ?? ''));
            // strtotime entiende 8:00am, 1:30pm, etc.
            return strtotime($ini) ?: 0;
        })->values();

        // Función pequeña para nombre de licenciatura
        $licName = function($r) {
            return optional($r->licenciatura)->nombre
                ?? optional(optional($r->asignacionMateria)->materia?->licenciatura)->nombre
                ?? 'N/A';
        };
    @endphp

    <table>
        <thead>
            <tr>
                <th class="w-hora">Hora(s)</th>      {{-- <- antes de Materia --}}
                <th class="w-mat">Materia</th>
                <th class="w-lic">Licenciatura</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ordenados as $idx => $h)
                @php
                    $mat  = optional($h->asignacionMateria)->materia;
                    $clave = $mat->clave ?? null;
                @endphp
                <tr>
                    <td class="w-hora" style="text-align:center;">{{ $h->hora }}</td>
                    <td class="w-mat">
                        {{ $mat->nombre ?? '—' }}
                        @if($clave)
                            <span style="font-size:10px;color:#6b7280;"> ({{ $clave }})</span>
                        @endif
                    </td>
                    <td class="w-lic">{{ $licName($h) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Sin registros para este docente.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        <p>{{ $escuela->nombre }} — C.C.T. {{ $escuela->CCT }}</p>
        <p>
            C. {{ $escuela->calle }} No. {{ $escuela->no_exterior }},
            Col. {{ $escuela->colonia }}, C.P. {{ $escuela->codigo_postal }},
            Cd. {{ $escuela->ciudad }}, {{ $escuela->estado }}.
        </p>
        <p>Tel. {{ $escuela->telefono }}</p>
        <p style="font-weight:bold">Fecha de expedición: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </footer>
</body>
</html>
