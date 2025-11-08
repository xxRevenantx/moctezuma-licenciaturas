<!DOCTYPE html>
<html lang="es">
<head>

   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ public_path('storage/ok.png') }}" />
  <title>Documentación de Alumnos</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; color:#111827; font-size:12px; margin: 18px; }

    .report-title { font-weight: bold; font-size:16px; color:#111827; margin: 0 0 6px 0; text-align: center; }
    .report-subtitle { font-size:12px; color:#6b7280; margin: 0 0 14px 0; }

    table { width: 100%; border-collapse: collapse; table-layout: fixed; border: 1px solid #e5e7eb; }
    thead th {
      background: #0f172a; color: #ffffff; text-align: left;
      padding: 3px 10px; font-weight: bold; border-right: 1px solid #1f2937; font-size: 11.5px;
    }
    thead th:last-child { border-right: none; }

    tbody tr { border-top: 1px solid #e5e7eb; page-break-inside: avoid; }
    tbody tr:nth-child(odd) { background: #f9fafb; }
    tbody td { padding: 8px 10px; color:#111827; vertical-align: middle; border-right: 1px solid #eef2f7; word-wrap: break-word; }
    tbody td:last-child { border-right: none; }

    .col-id { text-align: center; width: 40px; }
    .col-name { width: 220px; }
    .col-mat { width: 120px; }
    .col-doc { width: 54px; text-align: center; }
    .icon { width: 12px; height: 12px; display: inline-block; }
    .badge { display:inline-block; padding:2px 6px; border-radius:6px; font-size:11px; line-height:1.2; border:1px solid #e5e7eb; background:#f3f4f6; color:#374151; }

    /* Fila de título de grupo (generación) */
    .group-row td {
      background: #e5e7eb; /* gris claro, seguro para DomPDF */
      color: #111827;
      font-weight: bold;
      padding: 6px 10px;
      border-top: 2px solid #d1d5db;
    }

    /* salto de página opcional entre grupos largos */
    .page-break { page-break-before: always; }
  </style>
</head>
<body>

  <h1 class="report-title">DOCUMENTACIÓN DE ALUMNOS</h1>
  <p class="report-subtitle">Licenciatura: <strong>{{ $licenciaturaNombre }}</strong></p>

  <table>
    <thead>
      <tr>
        <th class="col-id">#</th>
        <th class="col-name">Nombre</th>
        <th class="col-mat">Matrícula</th>
        <th class="col-doc">INE</th>
        <th class="col-doc">CURP</th>
        <th class="col-doc">Cert. estudios</th>
        <th class="col-doc">Acta Nac.</th>
        <th class="col-doc">Comp. dom.</th>
      </tr>
    </thead>
    <tbody>

      @foreach($grupos as $generacionLabel => $items)
        {{-- Título del grupo (Generación) --}}
        <tr class="group-row">
          <td colspan="8">Generación: {{ $generacionLabel }}</td>
        </tr>

        @foreach($items as $alumno)
          <tr>
            <td class="col-id">{{ $loop->iteration }}</td>
            <td class="col-name">
              {{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}
            </td>
            <td class="col-mat">
              @if($alumno->matricula)
                <span class="badge">{{ $alumno->matricula }}</span>
              @else
                <span class="badge" style="background:#fef2f2; color:#991b1b; border-color:#fecaca;">N/A</span>
              @endif
            </td>

            {{-- INE --}}
            <td class="col-doc">
              @if($alumno->ine)
                <img class="icon" src="{{ public_path('storage/ok.png') }}" alt="ok">
              @else
                <img class="icon" src="{{ public_path('storage/error.png') }}" alt="pendiente">
              @endif
            </td>

            {{-- CURP --}}
            <td class="col-doc">
              @if($alumno->CURP_documento)
                <img class="icon" src="{{ public_path('storage/ok.png') }}" alt="ok">
              @else
                <img class="icon" src="{{ public_path('storage/error.png') }}" alt="pendiente">
              @endif
            </td>

            {{-- Certificado de estudios --}}
            <td class="col-doc">
              @if($alumno->certificado_estudios)
                <img class="icon" src="{{ public_path('storage/ok.png') }}" alt="ok">
              @else
                <img class="icon" src="{{ public_path('storage/error.png') }}" alt="pendiente">
              @endif
            </td>

            {{-- Acta de nacimiento --}}
            <td class="col-doc">
              @if($alumno->acta_nacimiento)
                <img class="icon" src="{{ public_path('storage/ok.png') }}" alt="ok">
              @else
                <img class="icon" src="{{ public_path('storage/error.png') }}" alt="pendiente">
              @endif
            </td>

            {{-- Comprobante de domicilio --}}
            <td class="col-doc">
              @if($alumno->comprobante_domicilio)
                <img class="icon" src="{{ public_path('storage/ok.png') }}" alt="ok">
              @else
                <img class="icon" src="{{ public_path('storage/error.png') }}" alt="pendiente">
              @endif
            </td>
          </tr>
        @endforeach

      @endforeach

    </tbody>
  </table>

</body>
</html>
