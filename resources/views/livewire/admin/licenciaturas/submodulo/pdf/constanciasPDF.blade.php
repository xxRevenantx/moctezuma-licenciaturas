<!DOCTYPE html>
<html lang="es">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>CONSTANCIA | {{ $constancia->alumno->nombre }} {{ $constancia->alumno->apellido_paterno }} {{ $constancia->alumno->apellido_materno }}   </title>
</head>
<style>

    @page { margin:0px 0px 0px 0px; }


    .page-break {
     page-break-after: always;
    }

    @font-face {
            font-family: 'greatVibes';
            font-style: normal;
            src: url('{{ storage_path('fonts/GreatVibes-Regular.ttf') }}') format('truetype');

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


    .fondo {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
    .contenedor{
        padding: 50px 85px 0;
        margin-top:30px;
        font-family: 'calibri'

    }


    .asunto{
        margin-top: 160px;
        text-align: right;
        font-weight: bold;
        font-size:18px;
        text-transform: uppercase;
        line-height: 15px;
    }




    .descripcion{
        font-size: 18px;
        line-height: 16px;
        text-align: justify;
        text-indent: 30px;
        margin-top: 20px;
    }
    .descripcion2{
        font-size: 18px;
      line-height: 16px;
        text-align: justify;
        text-indent: 30px;
        margin-top: -20px;
    }

    .rector{
        text-transform: uppercase;
    }


    .detalles{
        /* font-family: 'Times new Roman' */
        line-height: 13px;
        margin-top: 30px;
        font-size:18px;
        position:absolute;
        float: right
    }


    .detalles_titulo{
        text-align: right;

    }

    .corresponda{
        text-align: left;
        font-size: 19px;
        font-weight: bold;
        margin-top: 80px;
        line-height: 15px;
    }

    .cierre{
        text-align: justify;
        font-size: 18px;
        line-height: 18px;
        margin-top: 20px;
        text-indent: 30px;
    }


</style>
<body>
    @php

    $nombreRector = "{$rector->nombre} {$rector->apellido_paterno} {$rector->apellido_materno}";
    $nombreAlumno = "{$constancia->alumno->nombre} {$constancia->alumno->apellido_paterno} {$constancia->alumno->apellido_materno}";

    // $nombreJefe = "{$jefe->titulo}{$jefe->nombre} {$jefe->apellido_paterno} {$jefe->apellido_materno}";
    // $nombreSubjefe = "{$subjefe->titulo}{$subjefe->nombre} {$subjefe->apellido_paterno} {$subjefe->apellido_materno}";
@endphp

        <div class="fondo">
            <img src="{{ public_path('storage/membrete_constancia.jpg') }}" alt="fondo" style="width: 100%; height: 100%;">
        </div>

        <div class="contenedor">

            <table class="detalles">
                <tr>
                    <td class="detalles_titulo"><b>Dependencia:</b></td>
                    <td>{{ $escuela->nombre }}</td>
                </tr>
                <tr>
                    <td class="detalles_titulo"><b>C.C.T:</b></td>
                    <td>{{ $escuela->CCT }}</td>
                </tr>
                <tr>
                    <td class="detalles_titulo"><b>Sección: </b></td>
                    <td>Administrativa</td>
                </tr>
                <tr>
                    <td class="detalles_titulo"><b>Expediente: </b></td>
                    <td>{{ $ciclo_escolar->ciclo_escolar }}</td>
                </tr>
                <tr>
                    <td class="detalles_titulo"><b>Núm. de oficio: </b></td>
                    <td>
                        @if ($constancia->no_constancia < 9)
                             0{{ $constancia->no_constancia }}
                        @else
                             {{ $constancia->no_constancia }}
                        @endif
                    </td>
                </tr>
            </table>


            <p class="asunto">

                     ASUNTO: CONSTANCIA DE ESTUDIOS

               <br>
                Ciudad Altamirano, Gro., a {{ \Carbon\Carbon::parse($constancia->fecha_expedicion)->translatedFormat('d \d\e F \d\e\l Y') }}
            </p>


          <p class="corresponda">
             @if ($constancia->tipo_constancia == 1)
                      A QUIEN CORRESPONDA:
                @else
                  SECRETARÍA DE RELACIONES EXTERIORES: <br>
                P R E S E N T E
               @endif

          </p>

          <p class="descripcion">
            El que suscribe  <b>{{$rector->titulo}} {{$nombreRector}}</b>, en mi carácter de rector del
            Centro Universitario Moctezuma, con clave de incorporación {{$escuela->CCT}}, ubicada en
            calle Francisco I. Madero Ote. 800 de la localidad de Cd. Altamirano, municipio de
            Pungarabato, Gro. Región Tierra Caliente.
          </p>

          <p style="text-align:center; font-size: 50px; font-weight: bold; margin-top: 20px; ">
            HACE CONSTAR
          </p>

          <p class="descripcion2">
            @if ($constancia->alumno->sexo == 'H')
                 Que el alumno:  <b style="text-transform: uppercase">{{$nombreAlumno}}</b>, con CURP:
                    <b style="text-transform: uppercase">{{$constancia->alumno->CURP}}</b> y matrícula:
                    <b>{{$constancia->alumno->matricula}}</b>, de acuerdo a la documentación
                    que obra en el archivo de la escuela, cursa el <b>{{$constancia->alumno->cuatrimestre_id}}° Cuatrimestre</b> (con fecha de inicio
                  {{ \Carbon\Carbon::parse($periodo->inicio_periodo)->translatedFormat('d \d\e F \d\e\l Y') }}
                    al {{ \Carbon\Carbon::parse($periodo->termino_periodo)->translatedFormat('d \d\e F \d\e\l Y') }}) de la Licenciatura en <b style="text-transform: uppercase">{{$constancia->alumno->licenciatura->nombre}}</b> asistiendo en forma regular a clases en este plantel educativo
                    en el ciclo escolar {{$ciclo_escolar->ciclo_escolar}}.



            @else
                 Que la alumna:  <b style="text-transform: uppercase">{{$nombreAlumno}}</b>, con CURP:
                    <b style="text-transform: uppercase">{{$constancia->alumno->CURP}}</b> y matrícula:
                    <b>{{$constancia->alumno->matricula}}</b>, de acuerdo a la documentación
                    que obra en el archivo de la escuela, cursa el <b>{{$constancia->alumno->cuatrimestre_id}}° Cuatrimestre</b>
                    (con fecha de inicio {{ \Carbon\Carbon::parse($periodo->inicio_periodo)->translatedFormat('d \d\e F \d\e\l Y') }}
                    al {{ \Carbon\Carbon::parse($periodo->termino_periodo)->translatedFormat('d \d\e F \d\e\l Y') }}) de la Licenciatura en <b style="text-transform: uppercase">{{$constancia->alumno->licenciatura->nombre}}</b> asistiendo en forma regular a clases en este plantel educativo
                    en el ciclo escolar {{$ciclo_escolar->ciclo_escolar}}.

            @endif
          </p>

          <p class="cierre">
            A petición de la parte interesada y para todos los efectos y usos legales a que haya
            lugar, se extiende la presente constancia en
            Cd. Altamirano, estado de Guerrero a los {{ \Carbon\Carbon::parse($constancia->fecha_expedicion)->translatedFormat('d \d\i\a\s \d\e\l \m\e\s \d\e F \d\e\l Y') }}.

          </p>

          <p style="text-align: center; font-size: 17px; margin-top: -20px; line-height: 13px;">
            <b>A T E N T A M E N T E</b><br>
            EDUCACIÓN INTEGRAL ¡ELIGE CUM! <br><br>
            </p>

            <p style="text-align: center; font-size: 17px; line-height: 15px;">
                  _______________________________ <br>
            <b style="text-transform:uppercase">{{$rector->titulo}} {{$nombreRector}}</b> <br>
                <span style="text-transform: uppercase">{{$rector->cargo}}</span>
                </p>





        </div>


</body>
</html>
