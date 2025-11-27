<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>ACTA DE EXAMEN | {{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}
    </title>

    <style>
        @page {
            size: letter;
            margin: 25mm 25mm 25mm 25mm;
        }

        @font-face {
            font-family: 'calibri';
            font-style: normal;
            src: url('{{ public_path('fonts/calibri-regular.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'calibri';
            font-style: bold;
            font-weight: 700;
            src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
        }

        body {
            font-family: 'calibri';
            font-size: 25px;
            line-height: 1.2;
            color: #000;
        }

        .page {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .logo-row {
            width: 100%;
            display: table;
            margin-bottom: 10px;
        }

        .logo-left,
        .logo-center,
        .logo-right {
            display: table-cell;
            vertical-align: middle;
        }

        .logo-left {
            width: 20%;
            text-align: left;
        }

        .logo-center {
            width: 60%;
            text-align: center;
        }

        .logo-right {
            width: 20%;
            text-align: right;
        }

        .logo-img {
            max-height: 55px;
        }

        .spg-logo {
            max-height: 60px;
        }

        .clave {
            font-size: 9px;
            margin-top: 4px;
        }

        .title-acta {
            text-align: center;
            font-weight: 600;
            margin-top: 40px;
            margin-bottom: 30px;
            font-size: 16px;

        }

        .foto-cuadro {
            width: 70px;
            height: 80px;
            border: 1px solid #000;
            margin-top: 10px;
        }

        .contenido {
            margin-top: 8px;
            font-size: 13px;
        }

        .mb-5 {
            margin-bottom: 18px;
        }

        .mb-3 {
            margin-bottom: 10px;
        }

        .mb-2 {
            margin-bottom: 6px;
        }

        .indent {
            text-indent: 18px;
        }

        .underline {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding-left: 60px;
            padding-right: 60px;
            height: 12px;
        }

        .underline-short {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding-left: 30px;
            padding-right: 30px;
            height: 12px;
        }

        .underline-long {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding-left: 90px;
            padding-right: 90px;
            height: 12px;
        }

        .sinodo-line {
            width: 60%;
            border-bottom: 1px solid #000;
            margin: 2px 0;
        }

        .texto-centro {
            text-align: center;
        }

        .texto-justificado {
            text-align: justify;
        }

        .protesta {
            font-weight: 600;
            text-decoration: underline;
            text-align: center;
            margin-top: 12px;
            font-family: 'calibri';
            margin-bottom: 14px;
        }

        /** 2a página **/
        .texto-parrafo {
            font-size: 15px;
            text-align: justify;
            margin-bottom: 20px;
        }

        .firma-sustentante-titulo {
            text-align: right;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .firmas-nombre-firma {
            width: 100%;
            margin-bottom: 6px;
        }

        .firmas-nombre-firma td {
            font-size: 10px;
            text-align: center;
        }

        .firma-row {
            width: 100%;
            margin-bottom: 22px;
        }

        .firma-row td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 10px;
        }

        .linea-firma {
            width: 80%;
            border-bottom: 1px solid #000;
            margin: 0 auto 4px auto;
            height: 12px;
        }

        .firmas-rector-director {
            width: 100%;
            margin-top: 40px;
        }

        .firmas-rector-director td {
            width: 50%;
            text-align: center;
            font-size: 10px;
        }

        .cuadro-revision {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            font-size: 9px;
        }

        .cuadro-revision td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        .cuadro-header {
            text-align: center;
            font-weight: 600;
            font-size: 8px;
            margin-bottom: 25px;
        }

        .cuadro-firma {
            text-align: center;
            margin-top: 80px;
        }

        .cuadro-label-fecha {
            margin-top: 80px;
            font-weight: 600;
        }

        .small {
            font-size: 9px;
        }

        .sangria {
            margin-left: 70px;
        }
    </style>
</head>

<body>

    @php
        $sustentante = $alumno->nombre . ' ' . $alumno->apellido_paterno . ' ' . $alumno->apellido_materno;
    @endphp

    {{-- ================== PÁGINA 1 ================== --}}
    <div class="page">

        <img style="width: 70%;" src="{{ public_path('storage/encabezado.png') }}" alt="">

        <div class="title-acta">
            ACTA DE EXAMEN PROFESIONAL
        </div>

        <table width="100%">
            <tr>
                <td width="18%" valign="top">
                    <div class="foto-cuadro"></div>
                </td>
                <td width="82%" valign="top">
                    <div class="contenido">
                        Entidad federativa: <u>Guerrero</u>. Número de autorización
                        <span class="underline-short"></span>, siendo las
                        <span class="underline-short"></span>
                        horas del día
                        <span class="underline-short"></span>
                        del mes de
                        <span class="underline-short"></span>
                        del dos mil
                        <span class="underline-short"></span>.
                    </div>
                </td>
            </tr>
        </table>

        <div class="contenido sangria mb-5" style="margin-top:-20px; margin-left: 118px;">
            Se reunió el Sínodo integrado por los CC.
            <div class="sinodo-line"></div>
            <div class="sinodo-line"></div>
            <div class="sinodo-line"></div>
        </div>

        <div class="contenido sangria mb-3">
            Para aplicar el examen de titulación de:<br>
            <strong><u>C. {{ $sustentante }}</u></strong>
        </div>

        <div class="contenido sangria texto-justificado mb-2">
            Bajo la Presidencia de la primera, con carácter de secretaria la segunda y vocal la tercera, para
            proceder a efectuar la evaluación de EXAMEN PROFESIONAL para obtener el TÍTULO de
            <span class="underline-long"></span>, con reconocimiento de validez oficial de
            la Secretaría de Educación Guerrero, según acuerdo número
            <span class="underline-short"></span> de
            fecha <span class="underline-short"></span>. Los miembros del Sínodo examinaron al sustentante y
            después de deliberar entre sí, resolvieron declararlo(a):
            <span class="underline-short"></span>.
        </div>

        <div class="contenido sangria texto-justificado mb-3 ">
            A continuación, la presidenta del sínodo comunicó a el (la) C. Sustentante el resultado obtenido
            y le tomó la protesta de ley, en los términos siguientes:
        </div>

        <div class="contenido sangria texto-justificado mb-3">
            ¿PROTESTA USTED EJERCER LA CARRERA CON ENTUSIASMO Y HONRADEZ, VELAR SIEMPRE POR
            EL PRESTIGIO Y BUEN NOMBRE DE ÉSTA Y CONTINUAR ESFORZÁNDOSE POR MEJORAR SU
            PREPARACIÓN EN TODOS LOS ÓRDENES PARA GARANTIZAR LOS INTERESES DE LA JUVENTUD Y
            DE LA PATRIA?
        </div>

        <div class="protesta" style="font-family: 'calibri';">
            “SI PROTESTO”
        </div>

        <div class="contenido sangria texto-justificado">
            SI ASÍ LO HICIERE QUE SUS COMPAÑEROS Y LA NACIÓN SE LO PREMIEN Y SI NO, SE LO
            DEMANDEN.
        </div>
    </div>

    {{-- ================== PÁGINA 2 ================== --}}
    <div class="page">
        <div class="texto-parrafo">
            Terminado el acto se levanta, para constancia, la presente acta, que firman de conformidad
            el(la) Sustentante, el(la) Rector(a) y el(la) Director(a).
        </div>

        <div class="firma-sustentante-titulo">
            ______________________________________<br>Nombre y firma de el (la) sustentante
        </div>

        <table class="firmas-nombre-firma">
            <tr>
                <td>Nombre</td>
                <td>Firma</td>
            </tr>
        </table>

        <table class="firma-row">
            <tr>
                <td>
                    <div class="linea-firma"></div>
                    <div>Presidente(a)</div>
                </td>
                <td>
                    <div class="linea-firma"></div>
                </td>
            </tr>
        </table>

        <table class="firma-row">
            <tr>
                <td>
                    <div class="linea-firma"></div>
                    <div>Secretario(a)</div>
                </td>
                <td>
                    <div class="linea-firma"></div>
                </td>
            </tr>
        </table>

        <table class="firma-row">
            <tr>
                <td>
                    <div class="linea-firma"></div>
                    <div>Vocal</div>
                </td>
                <td>
                    <div class="linea-firma"></div>
                </td>
            </tr>
        </table>

        <table class="firmas-rector-director" style="margin-top:50px;">
            <tr>
                <td>
                    <div class="linea-firma"></div>
                    <div>RECTOR(A)</div>
                </td>
                <td>
                    <div class="linea-firma"></div>
                    <div>DIRECTOR(A)</div>
                </td>
            </tr>
        </table>

        <table class="cuadro-revision">
            <tr>
                <td width="50%">
                    <div class="cuadro-header">
                        REVISADO Y CONFRONTADO POR
                    </div>
                    <div class="cuadro-label-fecha">
                        FECHA:
                    </div>
                </td>
                <td width="50%">
                    <div class="cuadro-header">
                        JEFE(A) DEL DEPARTAMENTO DE REGISTRO Y<br>
                        CERTIFICACIÓN
                    </div>
                    <div class="cuadro-firma">
                        NOMBRE, FIRMA Y SELLO
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>