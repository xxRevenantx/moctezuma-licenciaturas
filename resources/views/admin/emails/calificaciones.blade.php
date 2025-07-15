<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<h1>
    Correo de calificaciones
</h1>

    <p>Alumno: {{ $inscripcion->nombre }} {{ $inscripcion->apellido_paterno }} {{ $inscripcion->apellido_materno }} </p>
    <p>Cuatrimestre: {{ $cuatrimestre->cuatrimestre }}</p>
    <p>Licenciatura: {{ $licenciatura->nombre }}</p>


    <table style="margin-top: 50px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>ASIGNATURA</th>
                    <th>CALIFICACIÓN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($calificaciones as $calificacion)

                    <tr>
                        <td>{{ $calificacion->asignacionMateria->materia->nombre }}</td>
                        <td>{{ $calificacion->calificacion }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-weight: bold; font-size:17px; text-align:right">CALIFICACIÓN CUATRIMESTRAL</td>
                    <td style="font-weight: bold; font-size:17px;" >
                        @if ($calificaciones->count() > 0)
                            {{ number_format($calificaciones->sum('calificacion') / $calificaciones->count(), 1) }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </tbody>
            </tbody>
        </table>



</body>
</html>
