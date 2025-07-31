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

        padding: 2px;
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

        .sin-borde {
            border: none;
        }

        .titulo-seccion {
            background-color: #d8e4bc;
            font-weight: bold;
            text-align: center;
        }

        .subtitulo {
            background-color: #d8e4bc;
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

           <table>
        <tr>
            <td  class="subtitulo">NOMBRE DEL ALUMNO (A)</td>
            <td>CALDERON</td>
            <td>RIOS</td>
            <td>JOCELYN</td>

            <td rowspan="4" colspan="2" class="foto">
                <img src="{{ public_path('img/alumna.png') }}" width="70">
            </td>
        </tr>

        <tr>
            <td class="subtitulo">EDAD</td>
            <td>19</td>
        </tr>
        <tr>
            <td class="subtitulo">FECHA DE NACIMIENTO</td>
            <td>0000-00-00</td>
            <td class="subtitulo">SEXO</td>
            <td>FEMENINO</td>
        </tr>
        <tr>
            <td class="subtitulo">ESTADO CIVIL</td>
            <td></td>
            <td class="subtitulo">CURP</td>
            <td>CARJ040217MGRLSCA1</td>
        </tr>
        <tr>
            <td class="subtitulo">LUGAR DE NACIMIENTO</td>
            <td colspan="3">0, 0</td>
        </tr>
        <tr>
            <td class="subtitulo">DOMICILIO DE PROCEDENCIA</td>
            <td colspan="3">Ciudad / Estado</td>
        </tr>
        <tr>
            <td class="subtitulo">COLONIA</td>
            <td>0</td>
            <td class="subtitulo">CP</td>
            <td>0</td>
        </tr>
        <tr>
            <td class="subtitulo">MUNICIPIO</td>
            <td>0</td>
            <td class="subtitulo">EMAIL</td>
            <td class="email">calderonjocelyn312@gmail.com</td>
        </tr>
        <tr>
            <td class="subtitulo">TELÉFONO</td>
            <td></td>
            <td class="subtitulo">CELULAR</td>
            <td></td>
        </tr>
        <tr>
            <td class="subtitulo">NOMBRE DEL PADRE O TUTOR</td>
            <td colspan="3">0</td>
        </tr>
        <tr>
            <td class="subtitulo">BACHILLERATO DE PROCEDENCIA</td>
            <td colspan="3">0</td>
        </tr>
    </table>

    <h2>DATOS DE LA LICENCIATURA</h2>

    <table>
        <tr>
            <td class="subtitulo">LICENCIATURA SOLICITADA</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td class="subtitulo">GENERACIÓN</td>
            <td>4</td>
            <td class="subtitulo">TURNO</td>
            <td></td>
        </tr>
    </table>

    <h2>REGISTRO DE DOCUMENTACIÓN</h2>

    <table>
        <tr>
            <td>- CERTIFICADO DE BACHILLERATO:</td>
            <td>- ACTA DE NACIMIENTO:</td>
        </tr>
        <tr>
            <td>- CERTIFICADO MÉDICO:</td>
            <td>- 6 FOTOGRAFÍAS TAMAÑO INFANTIL B/N:</td>
        </tr>
        <tr>
            <td>- CURP:</td>
            <td>- OTROS:</td>
        </tr>
    </table>


    <footer>
        <p>{{$escuela->nombre}} C.C.T. {{$escuela->CCT}} C. {{$escuela->calle}} No. {{$escuela->no_exterior}}, Col. {{$escuela->colonia}}, C.P. {{$escuela->codigo_postal}}, Cd. {{$escuela->ciudad}}, {{$escuela->estado}}.</p>
        <p>Fecha de expedición: {{ now()->translatedFormat('d \d\e F \d\e\l Y \a \l\a\s H:i') }}</p>
    </footer>

</body>
</html>
