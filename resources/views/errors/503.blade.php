<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Mantenimiento</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400..800&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="icon" href="{{ asset('storage/letra.png') }}" type="image/png">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
<section class="py-24 relative">
    <div class="w-full max-w-7xl px-4 md:px-5 lg:px-5 mx-auto">
        <div class="w-full flex-col justify-center items-center lg:gap-14 gap-10 inline-flex">

                <img

                    src="{{ asset('storage/logo.png') }}"
                    class="w-100"
                    alt=""
                />


            <div class="w-full flex-col justify-center items-center gap-5 flex">
                <div class="w-full flex-col justify-center items-center gap-6 flex">
                    <div class="w-full flex-col justify-start items-center gap-2.5 flex">
                        <h2 class="text-center text-gray-800 text-3xl font-bold font-manrope leading-normal">
                            ¡Por favor, ten paciencia! Actualmente estamos en mantenimiento.
                        </h2>
                        <p class="text-center text-gray-500 text-base font-normal leading-relaxed">
                            Estamos trabajando arduamente para mejorar tu experiencia. Te notificaremos cuando estemos de vuelta.
                        </p>
                    </div>


                </div>
                <img src="https://pagedone.io/asset/uploads/1718004199.png" alt="under maintenance image" class="object-cover">
            </div>
        </div>
    </div>
</section>
    </body>
</html>




