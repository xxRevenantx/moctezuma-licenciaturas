<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <title>EXPEDIENTE DEL ALUMNO | {{$alumno->nombre}} {{$alumno->apellido_paterno}} {{$alumno->apellido_materno}}</title>
</head>
<style>

    @page { margin: 0px 0px 10px 0px; }

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
        font-size: 13px;
    }



    .fecha {
        font-size: 16px;
        font-weight: bold;
    }


    table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }

    th, td {

        /* padding: 2px; */
        text-align: left;
        font-size: 13px;
    }
    td{
         border: 1px solid #000;
        text-align: left;
    }

    th {
         border: 1px solid #2d2d2d;
        background: #638acd;
        font-weight: bold;
        text-align: center;
        color: white;
    }


    p.nota {
        font-size: 14px;
        color: red;
    }

    footer {
        position: absolute;
        bottom: 0;
        left: 5%;
        text-align: center;
        font-size: 12px;
        line-height: 12px;
        width: 90%;
        margin: auto;

        border-top: 1px solid #4a5568;
        border-bottom: 1px solid #4a5568;

    }
    footer p{
        margin: 0;
        padding: 0;
    }


    .watermark {
        position: fixed;
        top: 100%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 150%;
        height: 150%;
        z-index: -1;
        opacity: 0.1;
        text-align: center;
    }


        h2 {
            background-color: #b3d38f;
            text-align: center;
            padding: 5px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        td, th {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        .center{
            text-align: center
        }

        .sin-borde {
            border: none;
        }

        .titulo-seccion {
            background-color: #d8e4bc;
            font-weight: bold;
            text-align: center;
        }

        .subtitulo {
            background-color: #eaeaea;
            font-weight: bold;
        }

        .comentarios {
            font-size: 11px;
            margin-top: 5px;
        }

        .firma {
            margin-top: 20px;
            font-size: 11px;
        }

        .email {
            color: #333;
            font-weight: bold;
        }

        .foto {
            width: 80px;
            height: 80px;
            border: 1px solid #000;
            text-align: center;
        }

        .bg-green {
            background-color: #d8e4bc;
        }

        .contenedor{
            padding: 0 35px
        }

</style>
<body>


    <div style="width: 12%; text-align: center; margin-top: 0px;  position: absolute; left: 50px; top: 20px; ">
        <img style="width: 100%;"  src="{{ public_path('storage/licenciaturas/' . $alumno->licenciatura->imagen) }}" alt="{{ $alumno->licenciatura->nombre }}">
     </div>
    <div style="width: 100%; text-align: center; margin-top: 0px;">
        <img style="width: 100%;"  src="{{ public_path('storage/expediente_encabezado.png') }}" alt="">
     </div>

     <div style="text-align: center; margin-top: 0px; font-size:25px;  position: absolute; right: 147px; top: 3px; ">
        <p>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('DD') }}</p>
     </div>
     <div style="text-align: center; margin-top: 0px; font-size:25px;  position: absolute; right: 105px; top: 3px; ">
        <p>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('MM') }}</p>
     </div>
     <div style="text-align: center; margin-top: 0px; font-size:25px;  position: absolute; right: 60px; top: 3px; ">
        <p>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('YY') }}</p>
     </div>

    <div class="contenedor">
        <div class="watermark">
            <img src="{{ public_path('storage/letra.png') }}" alt="Watermark">
        </div>


        <h2>DATOS DEL ALUMNO</h2>
           <table>
        <tr>
            <td colspan="2"  class="subtitulo">NOMBRE DEL ALUMNO (A)</td>
            <td style="text-align: center">{{ $alumno->nombre }}</td>
            <td style="text-align: center">{{ $alumno->apellido_paterno }}</td>
            <td style="text-align: center">{{ $alumno->apellido_materno }}</td>

            <td rowspan="3"  class="foto">
                <img src="{{ public_path('storage/estudiantes/' . $alumno->foto) }}" width="75" height="90" style="object-fit: cover;">
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
            <td style="text-align: center; font-size:9px; padding:0px;">NOMBRE(S)</td>
            <td style="text-align: center; font-size:9px; padding:0px;">A. PATERNO</td>
            <td style="text-align: center; font-size:9px; padding:0px;">A. MATERNO</td>
        </tr>

        <tr>
            <td class="subtitulo">FECHA DE NACIMIENTO</td>
            <td class="center" colspan="1">{{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') }}</td>
           <td class="subtitulo center" >CURP</td>
            <td colspan="2" class="center">{{ $alumno->CURP }}</td>



        </tr>

        <tr>

            <td class="subtitulo">LUGAR DE NACIMIENTO</td>
            <td class="center" colspan="5">
                @if(!empty($alumno->ciudadNacimiento->nombre))
                   {{ $alumno->ciudadNacimiento->nombre }}, {{ $alumno->estadoNacimiento->nombre }}
                @else
                   -------------
                @endif
            </td>
        </tr>

        <tr>
            <td class="subtitulo">DOMICILIO</td>
            <td colspan="5" class="center" >
                @if(!empty($alumno->calle))
                    {{ $alumno->calle }}
                    @if(!empty($alumno->numero_exterior))
                        NO. EXT.{{ $alumno->numero_exterior }}
                    @else
                        S/N
                    @endif
                    @if(!empty($alumno->numero_interior))
                        {{ $alumno->numero_interior }}
                    @endif

                @else
                    -------------
                @endif
            </td>

        </tr>

        <tr>
             <td class="subtitulo">COLONIA</td>
            <td colspan="2" class="center">
                @if(!empty($alumno->colonia))
                    {{ $alumno->colonia }}
                @else
                    -------------
                @endif
            </td>
            <td class="subtitulo center">CP</td>
            <td colspan="2" class="center">
                @if(!empty($alumno->codigo_postal))
                    {{ $alumno->codigo_postal }}
                @else
                    -------------
                @endif
            </td>
        </tr>

        <tr>
            <td class="subtitulo">MUNICIPIO</td>
            <td colspan="2" class="center">
                @if(!empty($alumno->municipio))
                    {{ $alumno->municipio }}
                @else
                    -------------
                @endif
            </td>
            <td class="subtitulo center">EMAIL</td>
            <td colspan="2" class="email center">{{ $alumno->user->email }}</td>
        </tr>
        <tr>
            <td class="subtitulo ">TELÉFONO</td>
            <td class="center" colspan="2">
                @if(!empty($alumno->telefono))
                    {{ $alumno->telefono }}
                @else
                    -------------
                @endif
            </td>
            <td class="subtitulo center">CELULAR</td>
            <td class="center" colspan="2">
                @if(!empty($alumno->celular))
                    {{ $alumno->celular }}
                @else
                    -------------
                @endif
            </td>
        </tr>
        <tr>
            <td class="subtitulo">NOMBRE DEL PADRE O TUTOR</td>
            <td style="text-transform: uppercase;" colspan="5" class="center">
                @if(!empty($alumno->tutor))
                    {{ $alumno->tutor }}
                @else
                    -------------
                @endif
            </td>
        </tr>
        <tr>
            <td class="subtitulo">BACHILLERATO DE PROCEDENCIA</td>
            <td style="text-transform: uppercase;" colspan="5" class="center">
                @if(!empty($alumno->bachillerato_procedente))
                    {{ $alumno->bachillerato_procedente }}

                @else
                    -------------
                @endif
            </td>
        </tr>
    </table>

    <h2>DATOS DE LA LICENCIATURA</h2>

    <table>
        <tr>
            <td class="subtitulo">LICENCIATURA ASIGNADA</td>
            <td colspan="3" style="text-align: center; text-transform: uppercase;">{{ $alumno->licenciatura->nombre}}</td>
        </tr>
        <tr>
            <td class="subtitulo">GENERACIÓN</td>
            <td style="text-align: center; text-transform: uppercase;">{{ $alumno->generacion->generacion }}</td>
            <td class="subtitulo center">MODALIDAD</td>
            <td style="text-align: center; text-transform: uppercase;">{{ $alumno->modalidad->nombre }}</td>
        </tr>
    </table>

    <h2>REGISTRO DE DOCUMENTACIÓN</h2>

    <table>
        <tr>
            <td>CERTIFICADO DE BACHILLERATO</td>
            <td class="center">
                @if($alumno->certificado == "true")
                    <span style="color: green; font-weight: bold;">ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">NO ENTREGADO</span>
                @endif
            </td>
              <td rowspan="5" style="width: 50%; text-align: justify; line-height: 12px">
              <p style="padding: 10px">
                 • LA DOCUMENTACIÓN MENCIONADA DEBERÁ
                ENTREGARSE AL MOMENTO DE INSCRIBIRSE. <br>
                • EL ALUMNO PODRÁ INSCRIBIRSE CARECIENDO DEL
                CERTIFICADO CORRESPONDIENTE SIEMPRE Y CUANDO
                PRESENTE CONSTANCIA DE ACREDITACIÓN Y LO
                PRESENTE EN EL PLAZO QUE INDIQUE, DE NO HACERLO
                ASÍ, NO SE LE PODRÁ DAR SEGUIMIENTO AL TRÁMITE
                CORRESPONDIENTE ANTE LAS AUTORIDADES
                EDUCATIVAS DEL ESTADO Y POR LO TANTO SU
                INSCRIPCIÓN SE ANULARÁ.
              </p>

            </td>
        </tr>
        <tr>
            <td>ACTA DE NACIMIENTO</td>
            <td class="center">
                @if($alumno->acta_nacimiento == "true")
                    <span style="color: green; font-weight: bold;">ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">NO ENTREGADO</span>
                @endif
            </td>

        </tr>
        <tr>
            <td>CERTIFICADO MÉDICO</td>
            <td class="center">
                @if($alumno->certificado_medico == "true")
                    <span style="color: green; font-weight: bold;">ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">NO ENTREGADO</span>
                @endif
            </td>
        </tr>
        <tr>
            <td>FOTOGRAFÍA TAMAÑO INFANTIL</td>
            <td class="center">
                @if($alumno->fotos_infantiles == "true")
                    <span style="color: green; font-weight: bold;">ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">NO ENTREGADO</span>
                @endif
            </td>
        </tr>

        <tr>
            <td>OTROS</td>
            <td class="center">
            @if(!empty($alumno->otros))
                {{ $alumno->otros }}
            @else
                -------------
            @endif
            </td>
        </tr>

           <tr>
            <td style=" text-align: justify; line-height: 12px; text-transform: uppercase;" colspan="3">
                ME COMPROMETO A ENTREGAR LOS DOCUMENTOS FALTANTES A MÁS TARDAR EL DÍA________ DE_______________________
                DEL___________. ESTOY DE ACUERDO QUE EN CASO DE NO CUMPLIR CON EL COMPROMISO ANTERIOR, SEA CANCELADA MI
                INSCRIPCIÓN Y CAUSE BAJA SIN NINGUNA RESPONSABILIDAD PARA EL {{ $escuela->nombre }}.
            </td>
        </tr>
        <tr>
            <td colspan="3" class="center" style="text-transform: uppercase">
                CD. ALTAMIRANO, GRO., A {{ \Carbon\Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') }}
            </td>
        </tr>

    </table>


    <footer>
        <p>{{ $escuela->nombre }} | {{ $escuela->pagina_web }} | Tel: {{ $escuela->telefono}}</p>
        <p>EXPEDIENTE DEL ALUMNO - PÁGINA 1</p>
    </footer>

    <div class="page-break"></div>

    <div style="width: 100%; text-align: center; margin-top: 20px;">
        <img style="width: 50%;"  src="{{ public_path('storage/logo.png') }}" alt="">
    </div>

    <div style="font-size: 15px; line-height: 14px; text-align: justify">
    <p>Todo pago que se efectúa al {{ $escuela->nombre }} presupone una serie de trámites internos, así como el apartado de
    un lugar en el curso por iniciar. Esto implica que el alumno que cubra cuotas por cualquier concepto y desee darse de baja
    estará sujeto a las siguientes condiciones:</p>

    <p style="text-indent: 10px"> <b>a)</b> Los alumnos que hayan realizado el pago total o parcial de la inscripción y cancelen su ingreso al Centro Universitario
    Moctezuma, no se les reembolsará monto alguno de lo pagado. Realizado el pago parcial de la inscripción deberán liquidar el
    100% de la inscripción al valor vigente de la misma en que esto suceda.
    </p>
    <p style="text-indent: 10px"><b>b)</b> En caso de reinscripción de alumnos en cuatrimestres o semestres avanzados, que cancelen su reingreso al Centro
    Universitario Moctezuma, no se les reembolsará monto alguno de lo pagado</p>
    <p style="text-indent: 10px"><b>c)</b> Cuando el alumno deje de asistir a clases sin previo aviso, el {{ $escuela->nombre }} tendrá la facultad de procesar la
    baja correspondiente y el alumno deberá cubrir las colegiaturas correspondientes, de acuerdo a la fecha de baja procesada por
    el CUM.</p>

    <p><b>PAGO ANTICIPADO</b></p>
    <p>El pago del cuatrimestre por anticipado tendrá un 8% de descuento exclusivamente sobre las colegiaturas sin incluir
        otras promociones. Como fecha límite para gozar de dicho descuento se tendrá la fecha establecida en el calendario de
        vencimientos.</p>

    <p><b>PAGOS VENCIDOS</b></p>

    <p style="text-indent: 10px"> <b>a)</b> Al acumularse <b>dos pagos</b> vencidos el alumno será suspendido, perdiendo el derecho al servicio educativo y administrativo aún
    y cuando se encontrara en periodo de exámenes parciales hasta que no liquide su adeudo. Las faltas no son justificables.
    </p>
    <p style="text-indent: 10px"> <b>b)</b> Al acumular <b>tres pagos vencidos</b> el alumno podrá ser dado de baja, no pudiendo asistir a clases hasta en tanto liquide el
    adeudo pendiente. Deberá tomarse en cuenta que las faltas acumuladas por este retraso afectara la acreditación de sus
    asignaturas.
    </p>

   <p style="text-indent: 10px"> <b>c)</b> Al finalizar el ciclo escolar, no podrán presentar exámenes de fin de período y extraordinarios si no se tienen liquidados al
    100% los pagos de Colegiaturas, Inscripción, Reinscripción, Cursos de Regularización y /o cualquier otro servicio.</p>
    <p style="text-indent: 10px"> <b>d)</b> Los pagos de colegiatura deberán realizarse dentro de los primeros cinco días de inicio del mes correspondiente.</p>


    <p style="text-transform: uppercase; font-weight: bold; ">
        DEBO Y PAGARÉ INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE {{ $escuela->nombre }}, EN
        _________________________________, EL DÍA _____________________ , LA CANTIDAD DE $_________________________
        _____________________________________MXN. POR CONCEPTO DE ADEUDOS EN COLEGIATURAS VENCIDAS. ESTE PAGARÉ CAUSARÁ
        INTERESES A RAZÓN DEL ____ MENSUAL DESDE LA FECHA DE VENCIMIENTO HASTA SU TOTAL LIQUIDACIÓN, PAGADERO
        CONJUNTAMENTE CON EL PRINCIPAL.
    </p>

    </div>

    <p style="text-transform: uppercase; font-weight: bold; text-align: center;">ACEPTAMOS:</p>

    <table style="width: 100%; text-align: center; border: none;">
        <tr>
            <td class="sin-borde" style="text-align: center; width: 50%;">_____________________________________<br>NOMBRE Y FIRMA DEL ALUMNO</td>
            <td class="sin-borde" style="text-align: center; width: 50%;">_____________________________________<br>NOMBRE Y FIRMA DEL PADRE/MADRE O TUTOR</td>
        </tr>
    </table>

    <footer>
        <p>{{ $escuela->nombre }} | {{ $escuela->pagina_web }} | Tel: {{ $escuela->telefono}}</p>
        <p>EXPEDIENTE DEL ALUMNO - PÁGINA 2</p>
    </footer>

    </div>

</body>
</html>
