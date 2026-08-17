<x-mail::message>
# Boletas de calificaciones

Hola **{{ $alumno?->nombre ?? 'alumno(a)' }}**.

Se adjuntan {{ $totalBoletas }} boleta(s) de calificaciones correspondientes a tu expediente académico
@if($generacion)
de la generación **{{ $generacion->generacion }}**
@endif.

Por favor conserva estos documentos para tu control personal.

Atentamente,  
**Control Escolar · Centro Universitario Moctezuma**
</x-mail::message>
