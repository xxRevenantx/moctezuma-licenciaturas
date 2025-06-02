<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Certificado de Estudios | {{ $alumno->matricula }}</title>
</head>
<style>

      @page { margin:5px 45px 0px 45px; }

      .page-break {
    page-break-after: always;
    }

     @font-face {
        font-family: 'calibri';
        font-style: normal;
        src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');

    }

    @font-face {
    font-family: 'calibri';
    font-style: bold;
    font-weight: 700;
    src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
    }



    body {
        font-family: 'calibri';
        margin: auto;
    }

     .encabezado {
        text-align: center;
        margin-top: 30px;
        font-size: 16px;
    }
    .img_encabezado {
        width: 80%;
        margin-left: -100px;
    }

    .titulo {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        margin-top: 10px;
        margin-bottom: 5px;
    }

    table.datos {
        margin-top: -40px;
        width: 100%;
        border-collapse: collapse;
        text-transform: uppercase;
    }

    table.tblPrincipal{
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
      border:  1px solid #000;
      /* height: 100%; */
      padding: 0;
      margin-top: 10px;
    }

    table.fisica td{

      padding: .5px;
      margin-top: 20px;
    }

    table tr.cuatrimestres td {
        font-size: 12px;
        line-height: 10px;
    }



    </style>
<body>

    <div class="encabezado">
          <img class="img_encabezado" src="{{ public_path('storage/imagen_kardex.png') }}" alt="Encabezado">
    </div>

          <table class="datos">

                    <tr>
                    <td style="text-align:center;" colspan="12"><h4 style="font-size:19.5px; margin-bottom:10px">CERTIFICADO DE ESTUDIOS</h4></td>
                    </tr>


                    <tr>

                    <td style="width:218px; margin-top:40px"></td>
                    <td>
                    <p  style="margin: 50px 0 0 0; font-size:15px; margin:0">CERTIFICA QUE: <b style="font-size:15px;"><u>{{$alumno->nombre}} {{$alumno->apellido_paterno}} {{$alumno->apellido_materno}}</u></b> </p><br>
                    <p style="font-size:13.5px; margin-top:-20px">CON CLAVE ÚNICA DE REGISTRO DE POBLACIÓN (CURP): <b><u>{{$alumno->CURP}}</u></b> <br>
                    CURSÓ Y ACREDITÓ LAS ASIGNATURAS CORRESPONDIENTES A LA <b>LICENCIATURA EN {{$licenciatura->nombre}} </b>  <br>
                    CON RECONOCIMIENTO DE VALIDEZ OFICIAL DE ESTUDIOS DE LA SECRETARÍA DE EDUCACIÓN GUERRERO.
                    SEGÚN ACUERDO NÚMERO: <b><u>{{$licenciatura->RVOE}}</u></b>, DE FECHA <b><u>29 DE ENERO DEL 2021</u></b>, Y CON CLAVE
                    CENTRO DE TRABAJO <b>{{$escuela->CCT}}. </p>

                    </td>
                    </tr>

           </table>
            <p  style="text-align:right; font-size:13.5px; margin-top:-5px">MATRÍCULA: <b><u>{{$alumno->matricula}}</u><b></p>


            @if ($alumno->licenciatura_id == 6)
                <table width="100%"  style="height:200px" class="tblPrincipal fisica"  cellpadding="0" cellspacing="0">
            @else
                <table width="100%" class="tblPrincipal"  cellspacing="0">
            @endif




          <tr>
              <!-- Tabla 1 -->
              <td width="50%" height="50%" valign="top" >
            <table width="100%" class="tbl1">
                <thead>
                    <tr>
                        <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5"><b>ASIGNATURAS</b></th>
                        <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5"><b>CAL.<br>FINAL</b></th>';

                        @if($alumno->licenciatura_id == 6)
                               <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent; font-size:10px"><b>OBSERVA<br>CIONES</b></th>
                        @else
                            <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent;"><b>OBSERVACIONES</b></th>
                        @endif

                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($cuatrimestres as $key => $cuatrimestre)

                                @php
                                    $nombreCuatrimestre = '';
                                   switch ($cuatrimestre->id) {
                                    case '1':
                                        $nombreCuatrimestre = 'PRIMER CUATRIMESTRE';
                                        break;
                                    case '2':
                                        $nombreCuatrimestre = 'SEGUNDO CUATRIMESTRE';
                                        break;
                                    case '3':
                                        $nombreCuatrimestre = 'TERCER CUATRIMESTRE';
                                        break;
                                    case '4':
                                        $nombreCuatrimestre = 'CUARTO CUATRIMESTRE';
                                        break;
                                    case '5':
                                        $nombreCuatrimestre = 'QUINTO CUATRIMESTRE';
                                        break;
                                    case '6':
                                        $nombreCuatrimestre = 'SEXTO CUATRIMESTRE';
                                        break;
                                    case '7':
                                        $nombreCuatrimestre = 'SEPTIMO CUATRIMESTRE';
                                    break;
                                    case '8':
                                        $nombreCuatrimestre = 'OCTAVO CUATRIMESTRE';
                                        break;
                                    case '9':
                                        $nombreCuatrimestre = 'NOVENO CUATRIMESTRE';
                                        break;

                                    default:
                                        # code...
                                        break;
                                 }


                                    // $nombreMeses = '';
                                    //     switch ($dato->mes->meses) {
                                    //         case 'SEPTIEMBRE/DICIEMBRE':
                                    //                 $nombreMeses = "SEPTIEMBRE - DICIEMBRE";
                                    //             break;
                                    //         case 'ENERO/ABRIL':
                                    //                 $nombreMeses = "ENERO - ABRIL";
                                    //             break;
                                    //         case 'MAYO/AGOSTO':
                                    //                 $nombreMeses = "MAYO - AGOSTO";
                                    //             break;
                                    //         default:
                                    //             $nombreMeses = "---";
                                    //             break;
                                    //     }



                                @endphp

                                 @php
                                        $periodo = \App\Models\Periodo::where('generacion_id', $alumno->generacion_id)
                                            ->first();

                                        //   $materias = MateriasCtr::consultarMateriasLicenciaturaCuatrimestreCtr($cedula["Id_licenciatura"], $cuatrimestre["Id"], $cedula["Modalidad"]);
                                            $materias = \App\Models\AsignacionMateria::where('licenciatura_id', $alumno->licenciatura_id)
                                                ->where('modalidad_id', $alumno->modalidad_id)
                                                ->where('cuatrimestre_id', $cuatrimestre->id)
                                                ->get();



                                    @endphp

                            @if($cuatrimestre->id < 5)




                                    <tr class="cuatrimestres">
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000">
                                            <strong>{{$nombreCuatrimestre}}</strong><br>
                                            <strong>CICLO ESCOLAR: {{$periodo->ciclo_escolar}}</strong>
                                        </td>
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000"></td>
                                    </tr>


                                    @foreach ($materias as $key => $materia)
                                            <tr>
                                                <td style="text-align:left; font-size:11px; line- text-transform:uppercase; padding-left:10px; border-left:1px solid #000; border-right:1px solid #000">{{$materia->materia->nombre}}</td>
                                                <td style="text-align:center; border-right:1px solid #000"></td>
                                            </tr>

                                  @endforeach


                           @endif

                        @endforeach

                      <tr>
                          <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000">
                              <strong></strong><br>
                              <strong></strong>
                          </td>
                          <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000"></td>
                        </tr>


              </tbody>
            </table>
        </td>

        <!-- Tabla 2 -->
        <td width="50%" valign="top">
            <table width="100%" class="tbl2">
                <thead>
                    <tr>
                     <th style="border-top:1px transparent; border-right:1px solid #000;background:#C5C5C5 "><b>ASIGNATURAS</b></th>
                        <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5"><b>CAL.<br>FINAL</b></th>';

                        @if ($alumno->licenciatura_id == 6)
                            <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent; font-size:10px"><b>OBSERVA<br>CIONES</b></th>
                            @else
                            <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent;"><b>OBSERVACIONES</b></th>
                        @endif

                  </tr>
                </thead>
                      <tbody>
                       @foreach ($cuatrimestres as $key => $cuatrimestre)

                                @php
                                    $nombreCuatrimestre = '';
                                   switch ($cuatrimestre->id) {
                                    case '1':
                                        $nombreCuatrimestre = 'PRIMER CUATRIMESTRE';
                                        break;
                                    case '2':
                                        $nombreCuatrimestre = 'SEGUNDO CUATRIMESTRE';
                                        break;
                                    case '3':
                                        $nombreCuatrimestre = 'TERCER CUATRIMESTRE';
                                        break;
                                    case '4':
                                        $nombreCuatrimestre = 'CUARTO CUATRIMESTRE';
                                        break;
                                    case '5':
                                        $nombreCuatrimestre = 'QUINTO CUATRIMESTRE';
                                        break;
                                    case '6':
                                        $nombreCuatrimestre = 'SEXTO CUATRIMESTRE';
                                        break;
                                    case '7':
                                        $nombreCuatrimestre = 'SEPTIMO CUATRIMESTRE';
                                    break;
                                    case '8':
                                        $nombreCuatrimestre = 'OCTAVO CUATRIMESTRE';
                                        break;
                                    case '9':
                                        $nombreCuatrimestre = 'NOVENO CUATRIMESTRE';
                                        break;

                                    default:
                                        # code...
                                        break;
                                 }


                                    // $nombreMeses = '';
                                    //     switch ($dato->mes->meses) {
                                    //         case 'SEPTIEMBRE/DICIEMBRE':
                                    //                 $nombreMeses = "SEPTIEMBRE - DICIEMBRE";
                                    //             break;
                                    //         case 'ENERO/ABRIL':
                                    //                 $nombreMeses = "ENERO - ABRIL";
                                    //             break;
                                    //         case 'MAYO/AGOSTO':
                                    //                 $nombreMeses = "MAYO - AGOSTO";
                                    //             break;
                                    //         default:
                                    //             $nombreMeses = "---";
                                    //             break;
                                    //     }



                                @endphp

                            @if($cuatrimestre->id > 4)
                                   <tr class="cuatrimestres">
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000">
                                            <strong>{{$nombreCuatrimestre}}</strong><br>
                                            <strong>CICLO ESCOLAR: {{$periodo->ciclo_escolar}}</strong>
                                        </td>
                                        <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000"></td>
                                    </tr>
                           @endif

                        @endforeach

                     </tbody>
            </table>
        </td>
    </tr>
</table>

<p style="font-align:justify; font-size:13px; margin:0; text-transform:uppercase">EL PRESENTE CERTIFICADO DE AMPARA <u><b></b></u> ASIGNATURAS, LAS CUALES CUBREN ÍNTEGRAMENTE EL PLAN DE ESTUDIOS DE LA LICENCIATURA <b>{{$licenciatura->nombre}}</b>
        CON UN TOTAL DE <b></b> CRÉDITOS Y UN PROMEDIO GENERAL DE APROVECHAMIENTO DE <b></b> LA ESCALA DE CALIFICACIONES ES DE (5 A 10) Y LA MÍNIMA APROBATORIA ES DE 6 (SEIS).
        </p>

        <p style="font-align:justify; font-size:13px">EXPEDIDO EN </p>




</body>
</html>
