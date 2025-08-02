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

    @page { margin:10px 45px 20px 45px; }


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
        left: 0;
        text-align: center;
        font-size: 12px;
        line-height: 12px;
        width: 100%;

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


</style>
<body>
        <div class="watermark">
            <img src="{{ public_path('storage/letra.png') }}" alt="Watermark">
        </div>

        <h1 style="background: #88AC2E; text-align: center; padding: 5px; width: 50%; margin: 20px auto;">EXPEDIENTE DEL ALUMNO</h1>

        <h2>DATOS DEL ALUMNO</h2>
           <table>
        <tr>
            <td colspan="2"  class="subtitulo">NOMBRE DEL ALUMNO (A)</td>
            <td style="text-align: center">{{ $alumno->nombre }}</td>
            <td style="text-align: center">{{ $alumno->apellido_paterno }}</td>
            <td style="text-align: center">{{ $alumno->apellido_materno }}</td>

            <td rowspan="3"  class="foto">
                <img src="{{ public_path('storage/estudiantes/' . $alumno->foto) }}" width="70">
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
            <td style="text-align: center; font-size:9px; padding:0px;">A. PATERNO</td>
            <td style="text-align: center; font-size:9px; padding:0px;">A. MATERNO</td>
            <td style="text-align: center; font-size:9px; padding:0px;">NOMBRE(S)</td>
        </tr>

        <tr>
            <td class="subtitulo">FECHA DE NACIMIENTO</td>
            <td class="center" colspan="1">{{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') }}</td>
           <td class="subtitulo">CURP</td>
            <td colspan="2">{{ $alumno->CURP }}</td>



        </tr>

        <tr>

            <td class="subtitulo">LUGAR DE NACIMIENTO</td>
            <td class="center" colspan="5">
                @if(!empty($alumno->lugar_nacimiento))
                    {{ $alumno->lugar_nacimiento }}
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
            <td class="subtitulo">LICENCIATURA SOLICITADA</td>
            <td colspan="3" style="text-align: center; text-transform: uppercase;">{{ $alumno->licenciatura->nombre}}</td>
        </tr>
        <tr>
            <td class="subtitulo">GENERACIÓN</td>
            <td style="text-align: center; text-transform: uppercase;">{{ $alumno->generacion->generacion }}</td>
            <td class="subtitulo">MODALIDAD</td>
            <td style="text-align: center; text-transform: uppercase;">{{ $alumno->modalidad->nombre }}</td>
        </tr>
    </table>

    <h2>REGISTRO DE DOCUMENTACIÓN</h2>

    <table>
        <tr>
            <td>CERTIFICADO DE BACHILLERATO</td>
            <td>
                @if($alumno->certificado)
                    <span style="color: green; font-weight: bold;">ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">NO ENTREGADO</span>
                @endif
            </td>
              <td rowspan="6" style="width: 50%; text-align: justify;">
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
            <td>
                @if($alumno->acta_nacimiento)
                    <span style="color: green; font-weight: bold;">&#10003; ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">&#10007; NO ENTREGADO</span>
                @endif
            </td>

        </tr>
        <tr>
            <td>CERTIFICADO MÉDICO</td>
            <td>
                @if($alumno->certificado_medico)
                    <span style="color: green; font-weight: bold;">ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">NO ENTREGADO</span>
                @endif
            </td>
        </tr>
        <tr>
            <td>FOTOGRAFÍA TAMAÑO INFANTIL</td>
            <td>
                @if($alumno->fotos_infantiles)
                    <span style="color: green; font-weight: bold;">ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">NO ENTREGADO</span>
                @endif
            </td>
        </tr>
        <tr>
            <td>CURP</td>
            <td>
                @if($alumno->CURP)
                    <span style="color: green; font-weight: bold;">ENTREGADO</span>
                @else
                    <span style="color: red; font-weight: bold;">NO ENTREGADO</span>
                @endif
            </td>
        </tr>
        <tr>
            <td>OTROS</td>
            <td></td>
        </tr>



    </table>


    <footer>
        <p>{{$escuela->nombre}} C.C.T. {{$escuela->CCT}} C. {{$escuela->calle}} No. {{$escuela->no_exterior}}, Col. {{$escuela->colonia}}, C.P. {{$escuela->codigo_postal}}, Cd. {{$escuela->ciudad}}, {{$escuela->estado}}.</p>
        <p>Fecha de expedición: {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}</p>
    </footer>

</body>
</html>
