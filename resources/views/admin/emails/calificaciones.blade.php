{{-- resources/views/admin/emails/calificaciones.blade.php --}}
@php
    $promedio = $calificaciones->count()
        ? number_format($calificaciones->avg('calificacion'), 1)
        : 'N/A';

    $colorPrimario = '#006492';  // Azul institucional
    $colorAcento   = '#88AC2E';  // Verde institucional

    // Genera el título honorífico si aplica
    $mencionHonorifica = is_numeric($promedio) && $promedio >= 9.9
        ? ' • Mención Honorífica'
        : '';
@endphp

<x-mail::message>
{{-- ENCABEZADO CON LOGO Y TÍTULO --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;">
    <tr>
        <td align="center">
            @if(!empty($escuela?->logo))
                <img src="{{ $escuela->logo }}" alt="Logotipo {{ $escuela->nombre ?? 'Centro Universitario Moctezuma' }}" style="max-height:70px; display:block; margin:0 auto 8px;">
            @endif
            <div style="font-size:12px; letter-spacing:.5px; color:#666; margin-bottom:4px;">
                {{ $escuela->nombre ?? 'Centro Universitario Moctezuma' }} · Área de Control Escolar
            </div>
            <div style="font-weight:700; font-size:20px; color:{{ $colorPrimario }}; text-transform:uppercase;">
                Calificaciones del {{ $cuatrimestre->cuatrimestre }}° Cuatrimestre
            </div>
        </td>
    </tr>
</table>

{{-- SALUDO --}}
@if ($inscripcion->sexo == 'M')
<p style="margin-top:0;margin-bottom:10px;">Estimada Alumna: <strong>{{ $inscripcion->nombre }} {{ $inscripcion->apellido_paterno }} {{ $inscripcion->apellido_materno }}</strong>,</p>
@else
<p style="margin-top:0;margin-bottom:10px;">Estimado Alumno: <strong>{{ $inscripcion->nombre }} {{ $inscripcion->apellido_paterno }} {{ $inscripcion->apellido_materno }}</strong>,</p>
@endif

<p style="margin-top:0;">
    Le informamos que se encuentran disponibles sus resultados del
    <strong>{{ $cuatrimestre->cuatrimestre }}° cuatrimestre</strong> de la
    <strong>Licenciatura en {{ $licenciatura->nombre }}</strong>.
</p>

{{-- RESUMEN ACADÉMICO --}}
<x-mail::panel>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:4px 0;"><strong>Licenciatura:</strong> {{ $licenciatura->nombre }}</td>
            <td style="padding:4px 0;"><strong>Generación:</strong> {{ $generacion->generacion ?? ($generacion->nombre ?? '—') }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;">
                <strong>Periodo:</strong>
                {{ \Carbon\Carbon::parse($periodo->inicio_periodo)->format('d/m/Y') ?? '—' }}
                al
                {{ \Carbon\Carbon::parse($periodo->termino_periodo)->format('d/m/Y') ?? '—' }}
            </td>
            <td style="padding:4px 0;"><strong>Ciclo escolar:</strong> {{ $ciclo_escolar->ciclo_escolar ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;" colspan="2">
                <strong>Promedio cuatrimestral:</strong>
                <span style="background:{{ $colorAcento }}; color:#fff; padding:2px 8px; border-radius:999px; font-weight:700;">
                    {{ $promedio }}{!! $mencionHonorifica !!}
                </span>
            </td>
        </tr>
    </table>
</x-mail::panel>

{{-- TABLA DE CALIFICACIONES --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; width:100%; margin: 6px 0 14px; border:1px solid #E5E7EB;">
    <thead>
        <tr>
            <th align="left" style="padding:10px 12px; font-size:12px; text-transform:uppercase; letter-spacing:.4px; background:{{ $colorPrimario }}; color:#fff; border-bottom:1px solid #D1D5DB;">Asignatura</th>
            <th align="center" style="padding:10px 12px; font-size:12px; text-transform:uppercase; letter-spacing:.4px; background:{{ $colorPrimario }}; color:#fff; border-bottom:1px solid #D1D5DB; width:140px;">Calificación</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($calificaciones as $i => $calificacion)
            @php $isZebra = $i % 2 === 1; @endphp
            <tr style="background:{{ $isZebra ? '#F9FAFB' : '#FFFFFF' }};">
                <td style="padding:10px 12px; border-bottom:1px solid #E5E7EB;">
                    {{ $calificacion->asignacionMateria->materia->nombre }}
                </td>
                <td align="center" style="padding:10px 12px; border-bottom:1px solid #E5E7EB; font-weight:700;">
                    {{ $calificacion->calificacion }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2" style="padding:14px 12px; text-align:center; color:#6B7280;">
                    No hay calificaciones registradas en este periodo.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>



{{-- NOTAS Y CIERRE --}}
<p style="margin:18px 0 0;">
    <em>Nota:</em> Este mensaje incluye (o puede incluir) su boleta en formato PDF como anexo. Para cualquier aclaración,
    agradeceremos comunicarse con <strong>Control Escolar</strong>.
</p>

<p style="margin-top:18px;">Sin otro particular, reciba un cordial saludo.</p>

{{-- FIRMA --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:6px;">
    <tr>
        <td style="border-left:4px solid {{ $colorAcento }}; padding-left:12px;">
            <div style="font-weight:700;">Ing. Carlos Núñez Pérez</div>
            <div>Encargado del Área de Control Escolar</div>
            <div>{{ $escuela->nombre ?? 'Centro Universitario Moctezuma' }}</div>
        </td>
    </tr>
</table>

{{-- SUBCOPY / AVISO DE CONFIDENCIALIDAD --}}
<x-mail::subcopy>
Este mensaje y sus anexos pueden contener información confidencial y de uso exclusivo del destinatario. Si usted ha
recibido este correo por error, por favor notifíquelo y elimínelo de su sistema.
</x-mail::subcopy>
</x-mail::message>
