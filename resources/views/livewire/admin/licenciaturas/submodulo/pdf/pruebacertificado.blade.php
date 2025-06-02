<?php
ob_start();
require_once 'vendor/autoload.php';

/* ================== KARDEX ====================== */
class certificadoEstudiosCtr{



static public function formatoFecha($date) {
    // Arreglos para convertir números a texto
    $days = [
        '01' => 'UNO', '02' => 'DOS', '03' => 'TRES', '04' => 'CUATRO', '05' => 'CINCO', '06' => 'SEIS',
        '07' => 'SIETE', '08' => 'OCHO', '09' => 'NUEVE', '10' => 'DIEZ', '11' => 'ONCE', '12' => 'DOCE',
        '13' => 'TRECE', '14' => 'CATORCE', '15' => 'QUINCE', '16' => 'DIECISEIS', '17' => 'DIECISIETE',
        '18' => 'DIECIOCHO', '19' => 'DIECINUEVE', '20' => 'VEINTE', '21' => 'VEINTIUNO', '22' => 'VEINTIDOS',
        '23' => 'VEINTITRÉS', '24' => 'VEINTICUATRO', '25' => 'VEINTICINCO', '26' => 'VEINTISEIS',
        '27' => 'VEINTISIETE', '28' => 'VEINTIOCHO', '29' => 'VEINTINUEVE', '30' => 'TREINTA', '31' => 'TREINTA Y UNO'
    ];

    $months = [
        '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO',
        '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTUBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE'
    ];

    $years = [
        '2020' => 'DOS MIL VEINTE',
        '2021' => 'DOS MIL VEINTIUNO',
        '2022' => 'DOS MIL VEINTIDÓS',
        '2023' => 'DOS MIL VEINTITRÉS',
        '2024' => 'DOS MIL VEINTICUATRO',
        '2025' => 'DOS MIL VEINTICINCO',
        '2026' => 'DOS MIL VEINTISÉIS',
        '2027' => 'DOS MIL VEINTISIETE',
        '2028' => 'DOS MIL VEINTIOCHO',
        '2029' => 'DOS MIL VEINTINUEVE',
        '2030' => 'DOS MIL TREINTA'
    ];

    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj) {
        return "Fecha inválida. Por favor, verifica el formato.";
    }

    $day = $dateObj->format('d');
    $month = $dateObj->format('m');
    $year = $dateObj->format('Y');

    $dayText = ltrim($day, '0'); // Quitar cero a la izquierda
    $monthText = $months[$month];
    $yearText = isset($years[$year]) ? $years[$year] : $year;

    return "CD. ALTAMIRANO, GUERRERO A <u>{$dayText}</u> DE <u>{$monthText}</u> DEL AÑO <u>{$yearText}</u>";
}



public static function certificadoEstudios(){

    $ruta = explode("/",$_GET["url"]);
    $css = file_get_contents('Views/css/certificado_estudios.css');



    $cedula = AlumnosCtr::consultarAlumnoIdCtr($_POST["idDocumento"]);
    $licenciatura =  LicenciaturasCtr::consultarLicenciaturasIdCtr($cedula["Id_licenciatura"]);


    $cuatrimestres = FormulariosCtr::consultarCuatrimestresCtr();


    $nombreAlumno = mb_strtoupper($cedula["Nombre"]." ".$cedula["ApellidoP"]." ".$cedula["ApellidoM"]);


    $materiasTotal = MateriasCtr::consultarMateriasLicenciaturaCtr($cedula["Id_licenciatura"], $cedula["Modalidad"]);


    $fechaDocumento =   CalificacionesCtr::consultarLugarFechaDocumentoCtr();
    $historialAcademico = DatosCtr::consultarHistorialAcademicoCtr($cedula["Id"]);

    $mpdf = new \Mpdf\Mpdf([
            "format" => "LEGAL"
        ]);
        $mpdf->AddPage('', '', 0, 0, 0, 10, 10, 13); //

        $creditosTotales = 0;

        $materias = DatosCtr::consultarMateriaLicenciaturaModalidadCtr($cedula["Id_licenciatura"], $cedula["Modalidad"]);
        foreach ($materias as $key => $value) {
          $creditosTotales += DatosCtr::creditosTotalesCtr($value["Id_materia"]);
        }




         $plantilla = '<body>
         <header class="clearfix">
           <div id="logos">

               <img class="logo1" src="Views/images/imagen_kardex.png">

                  </div>
         </header>
         <main>


         <div class="clearfix">

           <table class="tblDatos">

           <tr style="margin-bottom:100px">
           <td style="text-align:center;" colspan="12"><h4 style="font-size:19.5px; margin-bottom:10px">CERTIFICADO DE ESTUDIOS</h4></td>
           </tr>


           <tr>

           <td style="width:220px; margin-top:40px"></td>
           <td>
           <p  style="margin: 50px 0 0 0; font-size:15px; margin:0">CERTIFICA QUE: <b style="font-size:15px;"><u>'.mb_strtoupper($cedula["Nombre"]." ".$cedula["ApellidoP"]." ".$cedula["ApellidoM"]).'</u></b> </p><br>
           <p style="font-size:13.5px; margin-top:-20px">CON CLAVE ÚNICA DE REGISTRO DE POBLACIÓN (CURP): <b><u>'.$cedula["CURP"].'</u></b> <br>
           CURSÓ Y ACREDITÓ LAS ASIGNATURAS CORRESPONDIENTES A LA <b>LICENCIATURA EN '.$licenciatura["Carrera"].' </b>  <br>
           CON RECONOCIMIENTO DE VALIDEZ OFICIAL DE ESTUDIOS DE LA SECRETARÍA DE EDUCACIÓN GUERRERO.
           SEGÚN ACUERDO NÚMERO: <b><u>'.$licenciatura["Rvoe"].'</u></b>, DE FECHA <b><u>29 DE ENERO DEL 2021</u></b>, Y CON CLAVE
           CENTRO DE TRABAJO <b>12PSU0173I. </p>

           </td>
           </tr>

           </table>
            <p  style="text-align:right; font-size:13.5px; margin-top:-5px">MATRÍCULA: <b><u>'.$cedula["Matricula"].'</u><b></p>

           ';


          if($cedula["Id_licenciatura"] == 6){
            $plantilla .= '  <table width="100%"  style="height:200px" class="tblPrincipal fisica"  cellpadding="0" cellspacing="0">';
          }else{
            $plantilla .= '  <table width="100%" class="tblPrincipal"  cellspacing="0">';
          }




          $plantilla .= '<tr>
              <!-- Tabla 1 -->
              <td width="50%" height="50%" valign="top" >
            <table width="100%" class="tbl1">
                <thead>
                    <tr>
                        <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5"><b>ASIGNATURAS</b></th>
                        <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5"><b>CAL.<br>FINAL</b></th>';
                       if($cedula["Id_licenciatura"] == 6){
                            $plantilla .= '<th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent; font-size:10px"><b>OBSERVA<br>CIONES</b></th>';

                        }else{
                            $plantilla .= '<th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent;"><b>OBSERVACIONES</b></th>';

                        }
                    $plantilla .= '</tr>
                </thead>
                <tbody>';
                        $suma1 = 0;
                        $sumaCreditos = 0;
                foreach ($cuatrimestres as $key => $cuatrimestre) {

                  if($cuatrimestre["Id"] < 5){

                      $fechas_periodo = PeriodosCtr::consultarPeriodosCtr($cedula["Generacion"], $cuatrimestre["Id"]);
                      $materias = MateriasCtr::consultarMateriasLicenciaturaCuatrimestreCtr($cedula["Id_licenciatura"], $cuatrimestre["Id"], $cedula["Modalidad"]);

                      $plantilla .= '<tr>
                          <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000">
                              <strong>' . mb_strtoupper(str_replace("-", " ", $cuatrimestre["url"])) . '</strong><br>
                              <strong>CICLO ESCOLAR: ' . $fechas_periodo["Ciclo_escolar"] . '</strong>
                          </td>
                          <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000"></td>
                      </tr>';
                      foreach ($materias as $key => $materia) {
                          $mat = MateriasCtr::consultarMateriasIdCtr($materia["Id_materia"]);
                          $calificaciones = CalificacionesCtr::consultarCalificacionesIdAlumnoMateriaLicenciaturaGeneracionCuatrimestreCtr($cedula["Id"], $materia["Id"], $cedula["Id_licenciatura"], $cuatrimestre["Id"], $cedula["Modalidad"]);


                          $suma1 += $calificaciones["calificacion"];
                          // Mostrar el contenido solo en la columna para cuatrimestres 6 y en adelante

                            if(!empty($calificaciones["calificacion"])){
                                $plantilla .= '<tr>';
                                $plantilla .= '
                                <td style="text-align:left; text-transform:uppercase; padding-left:10px;  border-right:1px solid #000">'.$mat["Materia"].'</td>
                                <td style="text-align:center; border-right:1px solid #000">'.$calificaciones["calificacion"].'</td>
                                ';
                            $plantilla .= '</tr>';
                            }else{
                                $plantilla .= '<tr>';
                                $plantilla .= '
                                <td style="text-align:left; text-transform:uppercase; padding-left:10px;  border-right:1px solid #000">'.$mat["Materia"].'</td>
                                <td style="text-align:center; border-right:1px solid #000">0</td>
                                ';
                            $plantilla .= '</tr>';
                            }




                          }


                  }



                }



                $plantilla .= '</tbody>
            </table>
        </td>

        <!-- Tabla 2 -->
        <td width="50%" valign="top">
            <table width="100%" class="tbl2">
                <thead>
                    <tr>
                     <th style="border-top:1px transparent; border-right:1px solid #000;background:#C5C5C5 "><b>ASIGNATURAS</b></th>
                        <th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5"><b>CAL.<br>FINAL</b></th>';

                        if($cedula["Id_licenciatura"] == 6){
                            $plantilla .= '<th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent; font-size:10px"><b>OBSERVA<br>CIONES</b></th>';

                        }else{
                            $plantilla .= '<th style="border-top:1px transparent; border-left:1px transparent;background:#C5C5C5; border-right:1px transparent;"><b>OBSERVACIONES</b></th>';

                        }
                    $plantilla .= '</tr>
                </thead>
               <tbody>';
                        $suma2 = 0;
                foreach ($cuatrimestres as $key => $cuatrimestre) {

                  if($cuatrimestre["Id"] > 4){

                      $fechas_periodo = PeriodosCtr::consultarPeriodosCtr($cedula["Generacion"], $cuatrimestre["Id"]);
                      $materias = MateriasCtr::consultarMateriasLicenciaturaCuatrimestreCtr($cedula["Id_licenciatura"], $cuatrimestre["Id"], $cedula["Modalidad"]);

                      $plantilla .= '<tr>
                          <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000;  border-left:1px solid #000">
                              <strong>' . mb_strtoupper(str_replace("-", " ", $cuatrimestre["url"])) . '</strong><br>
                              <strong>CICLO ESCOLAR: ' . $fechas_periodo["Ciclo_escolar"] . '</strong>
                          </td>
                          <td style="padding-top:10px; padding-left:10px; text-align:center; border-right:1px solid #000"></td>

                      </tr>';



                      foreach ($materias as $key => $materia) {
                          $mat = MateriasCtr::consultarMateriasIdCtr($materia["Id_materia"]);
                          $calificaciones = CalificacionesCtr::consultarCalificacionesIdAlumnoMateriaLicenciaturaGeneracionCuatrimestreCtr($cedula["Id"], $materia["Id"], $cedula["Id_licenciatura"], $cuatrimestre["Id"], $cedula["Modalidad"]);
                          $suma2 += $calificaciones["calificacion"];


                          if(!empty($calificaciones["calificacion"])){
                            $plantilla .= '<tr>';
                            $plantilla .= ' <td style="text-align:left; text-transform:uppercase; padding-left:10px; border-left:1px solid #000; border-right:1px solid #000">'.$mat["Materia"].'</td>
                            <td style="text-align:center; border-right:1px solid #000">'.$calificaciones["calificacion"].'</td>';
                        $plantilla .= '</tr>';

                          }else{
                            $plantilla .= '<tr>';
                            $plantilla .= ' <td style="text-align:left; text-transform:uppercase; padding-left:10px; border-left:1px solid #000; border-right:1px solid #000">'.$mat["Materia"].'</td>
                            <td style="text-align:center; border-right:1px solid #000">--</td>';
                        $plantilla .= '</tr>';
                          }



                          }

                  }



                }

                $promedio = FunctionsMVXC::promedio(($suma1+$suma2)/count($historialAcademico));
                $plantilla .= '</tbody>
            </table>
        </td>
    </tr>
</table>
          ';

           // <!--  TABLA PRINCIPAL FINNNNNNNN -->
           $plantilla .= '

         </div>


         </main>

        <p style="font-align:justify; font-size:13px; margin:0">EL PRESENTE CERTIFICADO DE AMPARA <u><b>'.mb_strtoupper(FunctionsMVXC::numeroALetras(count($materiasTotal))).'</b></u> ASIGNATURAS, LAS CUALES CUBREN ÍNTEGRAMENTE EL PLAN DE ESTUDIOS DE LA LICENCIATURA <b>'.$licenciatura["Carrera"].'</b>
        CON UN TOTAL DE <b>'.$creditosTotales.'</b> CRÉDITOS Y UN PROMEDIO GENERAL DE APROVECHAMIENTO DE <b>'.$promedio.'</b> LA ESCALA DE CALIFICACIONES ES DE (5 A 10) Y LA MÍNIMA APROBATORIA ES DE 6 (SEIS).
        </p>

        <p style="font-align:justify; font-size:13px">EXPEDIDO EN '.certificadoEstudiosCtr::formatoFecha($fechaDocumento["Fecha"]).'.</p>';

        if($cedula["Id_licenciatura"] == 6){
            $plantilla .= '<table style="margin:25px auto 0; text-align:center; font-size:14px; width:100%">
            <tr>
            <td  colspan="5">_______________________________</td>
            </tr>
            <tr>
            <td colspan="5"><b>JOSÉ RUBÉN SOLÓRZANO CARBAJA</b>L</td>
            </tr>
            <tr>
            <td  colspan="5"><b>RECTOR</b></td>
            </tr>
            </table>';
        }else{
            $plantilla .= '<table style="margin:80px auto 0; text-align:center; font-size:13px; width:100%">
            <tr>
            <td  colspan="5">_______________________________</td>
            </tr>
            <tr>
            <td colspan="5"><b>JOSÉ RUBÉN SOLÓRZANO CARBAJA</b>L</td>
            </tr>
            <tr>
            <td  colspan="5"><b>RECTOR</b></td>
            </tr>
            </table>';
        }

    $fecha = CalificacionesCtr::consultarLugarFechaDocumentoCtr();


         $plantilla .= '
         <pagebreak>


         <table style="width:100%; margin-top:100px">
            <tr>
                <td style="border:1px solid #000; width:250px; text-align:center; font-size:16px; border-bottom:1px transparent">REVISADO Y CONFRONTADO POR:</td>
                <td style="width:220px"></td>
                <td style="border:1px solid #000; width:250px; text-align:center; font-size:16px; ">JEFE(A) DEL DEPARTAMENTO DE <br> REGISTRO Y CERTIFICACIÓN</td>
            </tr>
            <tr>
                <td style="border:1px solid #000; width:250px; height:100px; text-align:center; font-size:16px;  border-bottom:1px transparent"></td>
                <td style="width:220px;  border-bottom:1px transparent"></td>
                <td style="border:1px solid #000; width:250px; height:100px; text-align:center; font-size:16px;border-bottom:1px transparent; border-top:1px transparent"></td>
            </tr>
            <tr>
                <td style="border:1px solid #000; width:200px; height:100px; font-size:17px; text-align:center; border-top:1px transparent; border-bottom:1px transparent; padding:0 10px"><p style="text-align:center;">BERNARDO LÓPEZ BELLO</p></td>
                <td style="width:220px;font-size:15px; text-align:center"><br><br><b>SELLO</b></td>
                <!--<td style="border:1px solid #000;  text-align:center; width:200px; height:70px; text-align:center; font-size:17px; border-top:1px transparent">PEDRO PASTOR DEL MORAL</td> -->
                <td style="border:1px solid #000; width:200px;  text-align:center; height:100px; text-align:center; border-top:1px transparent;  border-bottom:1px transparent; font-size:17px; border-top:1px transparent">FRANCISCO JAVIER MEDINA MARIN</td>
            </tr>
            <tr>
                <td style="width:200px; height:45px; font-size:16px; text-align:left; border:1px solid #000; border-top:1px transparent; padding:0 10px">FECHA:</td>
                <td style="width:220px;font-size:15px; text-align:center"></td>
                <td style="border:1px solid #000; width:200px;  text-align:center; height:55px;  text-align:center; font-size:16px; border-top:1px transparent"></td>
            </tr>
         </table>

         <table style="width:37%; margin-top:90px">
            <tr>
                <td style="width:100px; font-size:16px;padding:5px; text-align:center">
                _________________________<br>
                    SILVIA AGUSTÍN MAGAÑA <br>
                    DIRECTOR(A) GENERAL

                </td>
            </tr>
         </table>


         <table style="width:100%; margin-top:1020px">
            <tr>
                <td style="border:1px solid #000; width:160px;  font-size:16px; border:2px solid #000; padding:5px">FOLIO: <b>'.$cedula["Folio"].'</b></td>
                <td style="border:1px solid #000; text-align:left; padding:0 5px; font-size:10.4px; border:1px transparent">ESTE CERTIFICADO REQUIERE DE TRAMITES ADICIONALES DE LEGALIZACIÓN. NO ES VÁLIDO SI PRESENTA BORRADURAS O ENMENDADURAS</td>
            </tr>
         </table>

</body>


         ';


         ;






   $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
   // $html = mb_convert_encoding($plantilla, 'UTF-8', 'UTF-8');
   $mpdf->WriteHTML($plantilla, \Mpdf\HTMLParserMode::HTML_BODY);



   ob_end_clean();

   $mpdf->Output("CERTIFICADO_DE_ESTUDIOS_".$nombreAlumno.".pdf", 'I');

}
}
