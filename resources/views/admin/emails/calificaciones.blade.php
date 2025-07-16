<x-mail::header :url="config('app.url')">
    Centro Universitario Moctezuma
</x-mail::header>

<x-mail::message>
{{-- This is the view for the email sent to students with their grades --}}
{{-- It includes the student's name, semester, degree, and a table of grades --}}
{{-- The table displays each subject and its corresponding grade, along with the overall semester grade --}}
{{-- The email is styled with a header and a table for better readability --}}
{{-- The table is designed to be clear and easy to read, with bold headers and a summary row for the overall grade --}}
{{-- The email is sent using the CalificacionMail class in Laravel --}}
{{-- The email uses Blade templating for dynamic content rendering --}}
{{-- The email is sent in HTML format for better presentation --}}
{{-- The email is designed to be responsive and accessible --}}

<x-mail::panel>
<h1 style="text-align: center; font-size: 24px; color: #333;">CALIFICACIONES DEL {{ $cuatrimestre->cuatrimestre }}° CUATRIMESTRE</h1>

@if ($inscripcion->sexo == 'M')
<p>Estimada Alumna: {{ $inscripcion->nombre }} {{ $inscripcion->apellido_paterno }} {{ $inscripcion->apellido_materno }},</p>
@else
<p>Estimado Alumno: {{ $inscripcion->nombre }} {{ $inscripcion->apellido_paterno }} {{ $inscripcion->apellido_materno }},</p>
@endif

<p>El <strong>Centro Universitario Moctezuma</strong>, a través del área de <strong>Control Escolar</strong>, le informa que las calificaciones correspondientes al <strong>{{ $cuatrimestre->cuatrimestre }}° cuatrimestre</strong> de la <strong>Licenciatura en {{ $licenciatura->nombre }}</strong> ya se encuentran disponibles.</p>

<p>A continuación, podrá consultar el desglose de las evaluaciones obtenidas durante el periodo académico.</p>
</x-mail::panel>


<table style="margin-top: 50px; border-collapse: collapse; width: 100%; text-align: left; border: 1px solid #000;">
<thead>
<tr>
<th style="border: 1px solid #000; padding: 8px; background-color: #f2f2f2;">ASIGNATURA</th>
<th style="border: 1px solid #000; padding: 8px; background-color: #f2f2f2;">CALIFICACIÓN</th>
</tr>
</thead>
<tbody>
@foreach ($calificaciones as $calificacion)
<tr>
<td style="border: 1px solid #000; padding: 8px;">{{ $calificacion->asignacionMateria->materia->nombre }}</td>
<td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $calificacion->calificacion }}</td>
</tr>
@endforeach
<tr>
<td style="border: 1px solid #000; font-weight: bold; font-size:17px; text-align:right; padding: 8px;">CALIFICACIÓN CUATRIMESTRAL</td>
<td style="border: 1px solid #000; font-weight: bold; font-size:17px; padding: 8px; text-align: center;">
@if ($calificaciones->count() > 0)
{{ number_format($calificaciones->sum('calificacion') / $calificaciones->count(), 1) }}
@else
N/A
@endif
</td>
</tr>
</tbody>
</table>

<p style="margin-top: 30px;">Sin otro particular, reciba un cordial saludo.</p>

<p><strong>Atentamente,</strong><br>
Ing. Carlos Núñez Pérez<br>
Encargado del Área de Control Escolar<br>
Centro Universitario Moctezuma</p>








</x-mail::message>
