<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Lista nominal por licenciatura y grado</title>
<style>
    @page { size: letter portrait; margin: 30mm 16mm 18mm 16mm; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 8.6pt; color: #20272c; }
    .header { position: fixed; top: -26mm; left: 0; right: 0; height: 23mm; border-top: 3px solid #8aac2e; border-bottom: 1px solid #b5d4e2; }
    .header table { width: 100%; border: none; border-collapse: collapse; }
    .header td { border: none; vertical-align: middle; }
    .header img { max-height: 47px; max-width: 118px; }
    .institution { text-align: center; font-weight: bold; color: #006492; font-size: 11pt; }
    .subinstitution { text-align: center; font-size: 7pt; color: #546c7a; margin-top: 3px; }
    .tag { text-align: right; font-weight: bold; font-size: 7pt; color: #7b9d24; }
    .footer { position: fixed; bottom: -13mm; left: 0; right: 0; border-top: 1px solid #c8d9e0; padding-top: 3px; color: #6f7f89; font-size: 7pt; }
    .sheet { page-break-after: always; }
    .sheet:last-child { page-break-after: auto; }
    h1 { margin: 0 0 8mm; color: #006492; text-align: center; font-size: 13pt; }
    .info { margin: 0 0 3mm; line-height: 1.5; }
    .info strong { color: #153d56; }
    .filtros { margin: 0 0 4mm; font-size: 7.2pt; color: #586d79; }
    .aviso { margin: 0 0 4mm; padding: 5px; background: #fff2d5; color: #9a5b10; font-weight: bold; font-size: 7pt; }
    table.nominal { border-collapse: collapse; table-layout: fixed; width: 100%; font-size: 8pt; }
    .nominal th, .nominal td { border: .7px solid #292929; padding: 4px 5px; line-height: 1.27; overflow-wrap: break-word; word-wrap: break-word; }
    .nominal th { background: #b8cae8; text-align: center; font-weight: bold; }
    .nominal td { vertical-align: middle; }
    .nominal tbody tr:nth-child(even) td { background: #f7f9fa; }
    .num, .edad { text-align: center; }
    .resumen { margin-top: 10mm; width: 70%; border-collapse: collapse; font-size: 8.7pt; page-break-inside: avoid; }
    .resumen th, .resumen td { border: .8px solid #292929; padding: 6px; width: 33.333%; text-align: center; }
    .resumen th { background: #c8e2ba; }
    .resumen td { font-weight: bold; }
    .vacio { color: #617581; text-align: center; padding: 12mm; border: 1px dashed #adb8bd; }
</style></head>
<body>
    <header class="header"><table><tr>
        <td style="width: 26%;">
            @if(file_exists(public_path('storage/logo.png')))
                <img src="{{ public_path('storage/logo.png') }}" alt="Logo">
            @elseif(file_exists(public_path('storage/logo-moctezuma.jpg')))
                <img src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo">
            @else
                <span style="font-weight:bold;color:#006492;font-size:12pt;">MOCTEZUMA</span>
            @endif
        </td>
        <td><div class="institution">{{ $escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.' }}</div><div class="subinstitution">C.C.T. {{ $escuela['CCT'] ?? '12PSU0173I' }}</div></td>
        <td style="width:22%;"><div class="tag">CONTROL ESCOLAR<br>LICENCIATURAS</div></td>
    </tr></table></header>
    <footer class="footer">Centro Universitario Moctezuma · Lista nominal · Corte: {{ \Carbon\Carbon::parse($reporte['fecha_corte'])->format('d/m/Y') }}</footer>

    @forelse($reporte['grupos'] as $grupo)
        <section class="sheet">
            <h1>LISTA NOMINAL POR GRADOS</h1>
            <p class="info"><strong>Licenciatura:</strong> {{ mb_strtoupper($grupo['licenciatura']) }}</p>
            <p class="info"><strong>Grado:</strong> {{ $grupo['grado'] }}º GRADO</p>
            <p class="filtros">Modalidad(es): {{ $reporte['modalidades'] }} · Generación(es): {{ $reporte['generaciones'] }}<br>Fecha de corte: {{ \Carbon\Carbon::parse($reporte['fecha_corte'])->format('d/m/Y') }}</p>
            @if(($reporte['alertas_avance']['total_pendientes'] ?? 0) > 0)
                <p class="aviso">Aviso de Control Escolar: {{ $reporte['alertas_avance']['total_pendientes'] }} alumnos pendientes de revisión de cuatrimestre. Se utiliza el cuatrimestre registrado.</p>
            @endif
            <table class="nominal">
                <thead><tr><th style="width:8%;">No.</th><th style="width:24%;">Apellido<br>Paterno</th><th style="width:24%;">Apellido<br>Materno</th><th style="width:32%;">Nombre</th><th style="width:12%;">Edad</th></tr></thead>
                <tbody>
                @foreach($grupo['alumnos'] as $indice => $alumno)
                    <tr><td class="num">{{ $indice + 1 }}</td><td>{{ $alumno['apellido_paterno'] }}</td><td>{{ $alumno['apellido_materno'] }}</td><td>{{ $alumno['nombre'] }}</td><td class="edad">{{ $alumno['edad'] ?? '—' }}</td></tr>
                @endforeach
                @for($i = 0; $i < $reporte['renglones_vacios']; $i++)
                    <tr><td style="height:21px;">&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                @endfor
                @if(count($grupo['alumnos']) === 0 && $reporte['renglones_vacios'] === 0)
                    <tr><td colspan="5" style="text-align:center;color:#71818a">Sin alumnos en este grado</td></tr>
                @endif
                </tbody>
            </table>
            <table class="resumen"><thead><tr><th>Hombres</th><th>Mujeres</th><th>Totales</th></tr></thead>
                <tbody><tr><td>{{ $grupo['totales']['hombres'] }}</td><td>{{ $grupo['totales']['mujeres'] }}</td><td>{{ $grupo['totales']['total'] }}</td></tr></tbody>
            </table>
        </section>
    @empty
        <h1>LISTA NOMINAL POR GRADOS</h1><div class="vacio">No hay alumnos activos para la selección actual.</div>
    @endforelse
</body></html>
