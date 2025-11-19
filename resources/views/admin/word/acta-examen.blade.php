<div style="position: relative; width: 100%; height: 100%; font-family: 'Times New Roman', serif;">

    {{-- FILA SUPERIOR: LOGOS --}}
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
        <!-- Logo SEG -->
        <div style="width: 20%; text-align: left;">
            {{-- <img src="{{ public_path('img/seg-logo.png') }}" alt="SEG" style="max-width: 100%; height: auto;" /> --}}
        </div>

        <!-- Espacio al centro (vacío) -->
        <div style="width: 20%;"></div>

        <!-- Logo Centro Universitario Moctezuma -->
        <div style="width: 40%; text-align: right;">
            {{-- <img src="{{ public_path('img/cum-logo.png') }}" alt="Centro Universitario Moctezuma"
                 style="max-width: 100%; height: auto;" /> --}}
        </div>
    </div>

    {{-- ESCUDO EN MARCA DE AGUA A PANTALLA COMPLETA --}}
    <div style="
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.18;
        z-index: 0;
        text-align: center;
    ">
        <img
            src="{{ public_path('storage/titulo.png') }}"
            alt="Escudo"
            style="width: 100%; height: 100%;"
        />
    </div>

    {{-- CONTENIDO PRINCIPAL (resto de tu HTML aquí...) --}}
    <div style="margin-top: 120px; text-align: center; z-index: 1; position: relative;">
        <p style="font-size: 16pt; font-style: italic; margin-bottom: 40px;">
            Otorga a:
        </p>
        <p style="font-size: 18pt; font-weight: bold; margin-bottom: 30px;">
            NOMBRE DEL ALUMNO(A)
        </p>
        <p style="font-size: 14pt; font-weight: bold; margin-bottom: 80px;">
            EL TÍTULO DE:
        </p>
    </div>

    <!-- ... resto del texto y firmas ... -->

</div>
