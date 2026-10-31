<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><style>
@page{size:letter;margin:15mm 21mm 15mm 21mm}
body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#151515;line-height:1.34}
.hoja{page-break-after:always;min-height:235mm;position:relative}
.hoja:last-child{page-break-after:auto}
.logos{width:100%;margin-bottom:0}.logos td{vertical-align:middle}
.logo-seg{height:51px}.logo-uni{width:230px}
.titulo{text-align:center;font-size:12px;font-weight:700;margin:14px 0 18px}
.cct{text-align:center;font-size:8px;font-weight:bold}
.foto{height:76px;width:61px;border:1px solid #111;text-align:center;overflow:hidden}
.foto img{width:61px;height:76px}
.intro{width:100%;margin:6px 0 18px}.intro td{vertical-align:top}
.texto{text-align:justify;margin:0 0 13px}
.nombres{margin:7px 0 18px 85px}.nombres div{border-bottom:1px solid #444;width:300px;padding:3px 1px}
.protesta{text-align:center;font-weight:bold;text-decoration:underline;margin:14px}
.firma-centro{text-align:center;margin:45px 0 24px}
.firma-linea{border-top:1px solid #111;padding-top:3px;text-align:center;min-height:26px;font-size:8px}
.firmas{width:100%;border-collapse:separate;border-spacing:22px 30px;margin-top:16px}
.firmas td{width:50%;vertical-align:bottom;text-align:center}
.revision{width:100%;border-collapse:collapse;margin-top:35px}.revision td{border:1px solid #222;width:50%;height:106px;text-align:center;vertical-align:top;padding:6px;font-size:8px}
.revision .pie{margin-top:55px}.preliminar{text-align:center;color:#a52222;font-weight:bold;font-size:10px;padding:5px;border:1px solid #d99}
.pie{font-size:8px;color:#444}
</style></head><body>
@foreach($actas as $acta)
@php($d = $documentos->data($acta))
<div class="hoja">
  <table class="logos"><tr><td width="30%"><img class="logo-seg" src="{{ resource_path('actas-examen/img/seg.jpeg') }}"></td>
  <td width="70%" align="right"><img class="logo-uni" src="{{ resource_path('actas-examen/img/moctezuma.png') }}"></td></tr></table>
  <div class="cct">CLAVE: C.C.T. {{ $d['cct'] }}</div>
  <div class="titulo">ACTA DE EXAMEN PROFESIONAL</div>
  @unless($d['final']) <div class="preliminar">{{ $d['estado'] === 'cancelada' ? 'ACTA CANCELADA - NO VÁLIDA' : 'DOCUMENTO PRELIMINAR - SIN AUTORIZAR' }}</div> @endunless
  <table class="intro"><tr>
    <td width="20%"><div class="foto">@if($foto = $documentos->fotoDataUri($acta))<img src="{{ $foto }}">@endif</div></td>
    <td width="80%">Entidad federativa: <u>{{ $d['entidad'] }}</u>. Número de autorización <u>{{ $d['autorizacion'] }}</u>, siendo las <u>{{ $d['hora'] }}</u> horas del día <u>{{ $d['fecha'] }}</u>.</td>
  </tr></table>
  <div style="margin-left:84px">Se reunió el Sínodo integrado por los CC.:</div>
  <div class="nombres"><div>{{ $d['presidente'] }}</div><div>{{ $d['secretario'] }}</div><div>{{ $d['vocal'] }}</div></div>
  <div class="texto">Para aplicar el examen de titulación de:<br><strong><u>C. {{ $d['nombre'] }}</u></strong></div>
  <p class="texto">Bajo la Presidencia de la primera persona, con carácter de secretaría la segunda y vocalía la tercera, para proceder a efectuar la evaluación de EXAMEN PROFESIONAL para obtener el TÍTULO de <u>{{ $d['titulo'] }}</u>, con reconocimiento de validez oficial de la Secretaría de Educación Guerrero, según acuerdo número <u>{{ $d['acuerdo'] }}</u> de fecha <u>{{ $d['fecha_acuerdo'] }}</u>. Los miembros del Sínodo examinaron a la persona sustentante y después de deliberar entre sí, resolvieron declararlo(a): <strong><u>{{ $d['resultado'] }}</u></strong>.</p>
  @if($d['aprobado'])
    <p class="texto">A continuación, la presidencia del sínodo comunicó a la persona sustentante el resultado obtenido y le tomó la protesta de ley, en los términos siguientes:</p>
    <p class="texto">¿PROTESTA USTED EJERCER LA CARRERA CON ENTUSIASMO Y HONRADEZ, VELAR SIEMPRE POR EL PRESTIGIO Y BUEN NOMBRE DE ÉSTA Y CONTINUAR ESFORZÁNDOSE POR MEJORAR SU PREPARACIÓN EN TODOS LOS ÓRDENES PARA GARANTIZAR LOS INTERESES DE LA JUVENTUD Y DE LA PATRIA?</p>
    <div class="protesta">“SÍ PROTESTO”</div>
    <p class="texto">SI ASÍ LO HICIERE QUE SUS COMPAÑEROS Y LA NACIÓN SE LO PREMIEN Y SI NO, SE LO DEMANDEN.</p>
  @else
    <p class="texto"><em>La protesta de ley únicamente se asentará cuando el resultado sea aprobatorio y el acta se haya autorizado.</em></p>
  @endif
</div>
<div class="hoja">
  @unless($d['final']) <div class="preliminar">{{ $d['estado'] === 'cancelada' ? 'ACTA CANCELADA - NO VÁLIDA' : 'DOCUMENTO PRELIMINAR - SIN AUTORIZAR' }}</div> @endunless
  <p class="texto">Terminado el acto se levanta, para constancia, la presente acta, que firman de conformidad el(la) Sustentante, el(la) Rector(a) y el(la) Director(a).</p>
  <div class="firma-centro"><div style="width:60%;border-bottom:1px solid #111;margin:0 auto">&nbsp;</div><div class="pie">{{ $d['nombre'] }}<br>Nombre y firma de el (la) sustentante</div></div>
  <table class="firmas">
  @foreach(['presidente'=>'PRESIDENTE(A)','secretario'=>'SECRETARIO(A)','vocal'=>'VOCAL'] as $rol=>$etiqueta)
  <tr><td><div class="firma-linea">{{ $d[$rol] }}<br>{{ $etiqueta }}</div></td><td><div class="firma-linea">FIRMA</div></td></tr>
  @endforeach
  <tr><td><div class="firma-linea">{{ $d['rector'] }}<br>RECTOR(A)</div></td><td><div class="firma-linea">{{ $d['director'] }}<br>DIRECTOR(A)</div></td></tr>
  </table>
  <table class="revision"><tr>
  <td>REVISADO Y CONFRONTADO POR<br>{{ $d['revisado'] }}<div class="pie">FECHA: {{ $d['revision'] }}</div></td>
  <td>JEFE(A) DEL DEPARTAMENTO DE REGISTRO Y CERTIFICACIÓN<br>{{ $d['jefe'] }}<div class="pie">NOMBRE, FIRMA Y SELLO</div></td>
  </tr></table>
</div>
@endforeach
</body></html>
