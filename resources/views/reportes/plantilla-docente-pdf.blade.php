<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><title>Plantilla docente por licenciatura</title>
<style>
@page { size: letter landscape; margin: 27mm 14mm 16mm 14mm; }
body { font-family: DejaVu Sans, sans-serif; color:#223442; font-size:8px; margin:0; }
header { position:fixed; top:-23mm; left:0; right:0; border-top:3px solid #88ac2e; border-bottom:1px solid #c9dde6; height:19mm; }
header table { width:100%; border:none; } header td { vertical-align:middle; border:0; }
header img { max-height:35px; max-width:110px; }
.inst { color:#006492; font-size:12px; font-weight:bold; text-align:center; } .sub { text-align:center; color:#526b7b; font-size:8px; }
footer { position:fixed; bottom:-11mm; left:0; right:0; border-top:1px solid #c9dde6; padding-top:5px; color:#637a8a; font-size:7px; }
section { page-break-after:always; } section:last-child { page-break-after:auto; }
h1 { font-size:15px; text-align:center; margin:0 0 12px; color:#006492; }
h2 { font-size:10px; margin:0 0 7px; }
.contexto { font-size:8px; color:#4e6878; margin-bottom:12px; }
table.docentes { border-collapse:collapse; width:100%; table-layout:fixed; }
.docentes th, .docentes td { border:0.65px solid #7b94a1; padding:5px 4px; word-break:break-word; vertical-align:middle; }
.docentes th { background:#b8cae8; font-weight:bold; font-size:7.5px; text-align:center; }
.docentes td { font-size:7.5px; line-height:1.24; }
.docentes tbody tr:nth-child(even) td { background:#f3f7f9; }
.total { border-top:3px solid #8aac2e; padding-top:8px; margin-top:12px; font-weight:bold; color:#006492; }
.alerta { padding:14px; border:1px dashed #aac1cc; text-align:center; }
</style></head><body>
<header><table><tr>
<td style="width:22%">
@if(file_exists(public_path('storage/logo.png')))
<img src="{{ public_path('storage/logo.png') }}" alt="Logo">
@elseif(file_exists(public_path('storage/logo-moctezuma.jpg')))
<img src="{{ public_path('storage/logo-moctezuma.jpg') }}" alt="Logo">
@else <span class="inst">MOCTEZUMA</span> @endif
</td>
<td><div class="inst">{{ $escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.' }}</div><div class="sub">C.C.T. {{ $escuela['CCT'] ?? '12PSU0173I' }}</div></td>
<td style="width:18%;text-align:right;color:#88ac2e;font-weight:bold;font-size:8px">CONTROL ESCOLAR<br>LICENCIATURAS</td>
</tr></table></header>
<footer>Centro Universitario Moctezuma · Profesores con horario programado · Ciclo {{ $reporte['ciclo'] }} · {{ $reporte['periodo'] }} · {{ $reporte['fecha'] }}</footer>
@forelse($reporte['grupos'] as $grupo)
<section>
<h1>PLANTILLA DOCENTE POR LICENCIATURA</h1>
<h2>{{ mb_strtoupper($grupo['licenciatura']) }}</h2>
<div class="contexto">Ciclo escolar: <b>{{ $reporte['ciclo'] }}</b> &nbsp;&nbsp; Periodo: <b>{{ $reporte['periodo'] }}</b> &nbsp;&nbsp; Fuente: Profesores programados</div>
@php
    $columnas = \App\Services\PlantillaDocenteWordService::columnas($reporte);
@endphp
<table class="docentes"><thead><tr>
@foreach($columnas as $key => $titulo)
<th style="width:{{ match ($key) { 'no' => '5%', 'nombre' => '19%', 'materias' => '26%', 'cuatrimestres' => '10%', 'modalidades' => '12%', 'generaciones' => '11%', 'bloques' => '7%', 'horas' => '10%', default => 'auto' } }}">{{ $titulo }}</th>
@endforeach
</tr></thead><tbody>
@foreach($grupo['docentes'] as $numero => $docente)
<tr>@foreach(\App\Services\PlantillaDocenteWordService::fila($docente, $numero + 1, $reporte) as $key => $valor)
<td style="{{ in_array($key, ['no','bloques','horas'], true) ? 'text-align:center' : '' }}">{{ $valor }}</td>
@endforeach</tr>
@endforeach
</tbody></table>
<div class="total">Total en la licenciatura: {{ $grupo['total_docentes'] }} docentes
@if($reporte['mostrar_horas']) · {{ $grupo['horas'] }} horas programadas @endif</div>
</section>
@empty
<h1>PLANTILLA DOCENTE POR LICENCIATURA</h1>
<div class="alerta">No existen profesores con horario programado para los filtros seleccionados.</div>
@endforelse
</body></html>
