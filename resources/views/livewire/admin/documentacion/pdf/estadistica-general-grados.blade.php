<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadística General por Grados</title>
    <style>
        @page { size: letter portrait; margin: 34mm 17mm 17mm 17mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Serif, serif; font-size: 9.2pt; color: #171717; }
        .page-header { position: fixed; top: -29mm; left: 0; right: 0; height: 26mm; border-top: 4px solid #bedb80; border-bottom: 1.5px solid #9dc4d8; }
        .page-header table { width: 100%; border-collapse: collapse; }
        .page-header td { vertical-align: middle; }
        .brand-cell { width: 26%; }
        .brand-img { max-width: 120px; max-height: 58px; }
        .institution { font: bold 12pt DejaVu Sans, sans-serif; color: #006492; text-align: center; }
        .cct { font: 7pt DejaVu Sans, sans-serif; text-align: center; color: #527389; margin-top: 3px; }
        .brand-tag { font: bold 7pt DejaVu Sans, sans-serif; color: #83b63a; text-align: right; }
        .page-footer { position: fixed; bottom: -12mm; left: 0; right: 0; font: 7pt DejaVu Sans, sans-serif; color: #667985; border-top: 1px solid #e4ebef; padding-top: 3px; }
        h1 { margin: 0 0 3mm; text-align: center; font-size: 16pt; }
        .subtitle { text-align: center; font: 7.5pt DejaVu Sans, sans-serif; color: #5b6770; margin-bottom: 11mm; }
        .licenciatura { page-break-inside: avoid; margin: 0 0 11mm; }
        .licenciatura h2 { font-size: 9.6pt; margin: 0 0 4mm; text-transform: uppercase; line-height: 1.4; }
        .grado { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 0 5mm; font-size: 9pt; }
        .grado td { border: .65px solid #222; text-align: center; padding: 2px 5px; line-height: 1.13; }
        .grado .titulo { background: #c8e2ba; font-weight: bold; }
        .grado .columnas { background: #b8cae8; font-weight: bold; }
        .grado .valores { background: #fff; font-weight: bold; }
        .resumen { margin-top: 9mm; page-break-inside: avoid; border-top: 1.4px solid #006492; padding-top: 5mm; font-weight: bold; }
        .resumen .texto { margin-bottom: 2mm; }
        .resumen .desglose { font: 8pt DejaVu Sans, sans-serif; color: #4e6172; font-weight: normal; }
        .vacio { text-align: center; padding: 12mm 2mm; font-family: DejaVu Sans, sans-serif; color: #667985; }
    </style>
</head>
<body>
    <header class="page-header">
        <table><tr>
            <td class="brand-cell">
                @if(file_exists(public_path('storage/logo.png')))
                    <img class="brand-img" src="{{ public_path('storage/logo.png') }}" alt="Logo" />
                @elseif(file_exists(public_path('storage/logo-moctezuma.jpg')))
                    <img class="brand-img" src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo" />
                @else
                    <div style="font: bold 16pt DejaVu Sans,sans-serif; color: #006492;">MOCTEZUMA</div>
                @endif
            </td>
            <td>
                <div class="institution">{{ $escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.' }}</div>
                <div class="cct">C.C.T. {{ $escuela['CCT'] ?? '12PSU0173I' }}</div>
            </td>
            <td style="width: 17%;"><div class="brand-tag">CONTROL ESCOLAR<br>LICENCIATURAS</div></td>
        </tr></table>
    </header>
    <footer class="page-footer">
        Estadística General por Grados · Corte: {{ \Carbon\Carbon::parse($reporte['fecha_corte'])->format('d/m/Y') }} · {{ $escuela['nombre'] ?? 'Centro Universitario Moctezuma' }}
    </footer>

    <h1>ESTADÍSTICA</h1>
    <p class="subtitle">Distribución por grados · Sólo generaciones y estudiantes con actividad vigente · {{ \Carbon\Carbon::parse($reporte['fecha_corte'])->format('d/m/Y') }}</p>
    @if(($reporte['alertas_avance']['total_pendientes'] ?? 0) > 0)
        <p style="font: bold 7.5pt DejaVu Sans,sans-serif; color: #935500; background: #fff4d6; padding: 6px;">
            AVISO DE CONTROL ESCOLAR: {{ $reporte['alertas_avance']['total_pendientes'] }} alumno(s) pendientes de verificar su cuatrimestre.
            Los totales están calculados conforme a la matrícula registrada, sin promover alumnos automáticamente.
        </p>
    @endif
    @forelse($reporte['licenciaturas'] as $licenciatura)
        <section class="licenciatura">
            <h2>{{ $licenciatura['nombre'] }}</h2>
            @foreach($licenciatura['grados'] as $grado)
                <table class="grado">
                    <tr><td colspan="3" class="titulo">{{ $grado['grado'] }}º GRADO</td></tr>
                    <tr class="columnas"><td>HOMBRES</td><td>MUJERES</td><td>TOTAL</td></tr>
                    <tr class="valores"><td>{{ $grado['hombres'] }}</td><td>{{ $grado['mujeres'] }}</td><td>{{ $grado['total'] }}</td></tr>
                </table>
            @endforeach
        </section>
    @empty
        <div class="vacio">No se encontraron alumnos activos pertenecientes a generaciones con periodo vigente.</div>
    @endforelse
    <div class="resumen">
        <div class="texto">Total de alumnos: {{ $reporte['totales']['total'] }}</div>
        <div class="desglose">Hombres: {{ $reporte['totales']['hombres'] }} · Mujeres: {{ $reporte['totales']['mujeres'] }}</div>
    </div>
</body>
</html>
