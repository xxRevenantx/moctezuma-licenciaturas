<div x-data="{ show:false }" x-init="setTimeout(() => show = true, 50)" x-cloak
     class="relative overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 sm:p-8 bg-white dark:bg-neutral-900">

    {{-- Fondo decorativo sutil --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-sky-400/10 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-indigo-500/10 blur-3xl"></div>
    </div>

    {{-- Encabezado --}}
    <div x-show="show"
         x-transition:enter="transition ease-out duration-700"
         x-transition:enter-start="opacity-0 -translate-y-4 blur-sm"
         x-transition:enter-end="opacity-100 translate-y-0 blur-0"
         class="mx-auto w-full text-center">
        <h2 class="text-2xl sm:text-3xl font-extrabold uppercase tracking-wide text-neutral-800 dark:text-neutral-100">
            Selecciona la modalidad de {{ $licenciatura->nombre }}
        </h2>
        <p class="mt-2 text-sm sm:text-base text-neutral-600 dark:text-neutral-300">
            Elige cómo cursarás tu programa. Revisa las estadísticas rápidas de cada modalidad.
        </p>
    </div>

    {{-- CONTENEDOR CENTRADO --}}
    <div class="mt-10 mx-auto w-full justify-center text-center">
        <div class="flex flex-wrap justify-center gap-8">
            @foreach ($modalidades as $i => $modalidad)
                @php
                    $estadisticas = $this->obtenerEstadisticasPorModalidad($modalidad);
                    $displayTotal = (int) ($estadisticas['total'] ?? 0);
                    $hombres      = (int) ($estadisticas['hombres'] ?? 0);
                    $mujeres      = (int) ($estadisticas['mujeres'] ?? 0);

                    $den = max(1, $displayTotal);
                    $pctH = min(100, round(($hombres / $den) * 100));
                    $pctM = min(100, round(($mujeres / $den) * 100));
                @endphp

                {{-- TARJETA con animación escalonada --}}
                <div x-show="show"
                     x-transition:enter="transition ease-out duration-700 delay-{{ $i * 150 }}"
                     x-transition:enter-start="opacity-0 translate-y-6 blur-sm"
                     x-transition:enter-end="opacity-100 translate-y-0 blur-0"
                     class="w-full max-w-sm group relative overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700
                            bg-gradient-to-b from-white to-neutral-50 dark:from-neutral-900 dark:to-neutral-800
                            shadow-md hover:shadow-xl transition duration-300"
                     role="region"
                     aria-label="Tarjeta modalidad {{ $modalidad->nombre }}"
                >
                    {{-- Banner superior --}}
                    <div class="relative h-28 w-full overflow-hidden">
                        <img class="h-full w-full object-cover"
                             src="{{ asset('storage/banner.png') }}"
                             alt="Banner de modalidad {{ $modalidad->nombre }}">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/10 to-black/0"></div>
                    </div>

                    {{-- Avatar/Logo --}}
                    <div class="relative -mt-10 mx-auto h-20 w-20 rounded-full ring-4 ring-white dark:ring-neutral-900 overflow-hidden">
                        <img class="h-full w-full object-cover"
                             src="{{ asset('storage/logo-moctezuma.jpg') }}"
                             alt="Logotipo Moctezuma">
                    </div>

                    {{-- Título --}}
                    <div class="px-5 mt-3 text-center">
                        <h3 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 uppercase">
                            {{ $modalidad->nombre }}
                        </h3>
                    </div>

                    {{-- Chips de estadísticas --}}
                    <ul class="px-5 pt-4 pb-2 flex items-center justify-center gap-3 text-sm">
                        <li class="flex items-center gap-2">
                            <flux:badge color="amber">Hombres</flux:badge>
                            <span class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $hombres }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <flux:badge color="zinc">Total</flux:badge>
                            <span class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $displayTotal }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <flux:badge color="violet">Mujeres</flux:badge>
                            <span class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $mujeres }}</span>
                        </li>
                    </ul>

                    {{-- Barras de proporción --}}
                    <div class="px-5 pb-4">
                        <div class="space-y-2">
                            <div>
                                <div class="flex justify-between text-xs text-neutral-600 dark:text-neutral-300">
                                    <span>Hombres</span><span>{{ $pctH }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-600 transition-[width] duration-700 ease-out"
                                         style="width: {{ $pctH }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs text-neutral-600 dark:text-neutral-300">
                                    <span>Mujeres</span><span>{{ $pctM }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-fuchsia-500 to-indigo-600 transition-[width] duration-700 ease-out"
                                         style="width: {{ $pctM }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botón CTA --}}
                    <div class="px-5 pb-5">
                        <flux:button
                            variant="primary"
                            class="w-full justify-center bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600
                                   hover:from-sky-600 hover:via-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:navigate
                            wire:click="irAModalidad('{{ strtolower($modalidad->slug) }}')"
                            aria-label="Ir a modalidad {{ $modalidad->nombre }}"
                        >
                            <div class="flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10"/>
                                </svg>
                                <span>{{ $modalidad->nombre }}</span>
                            </div>
                        </flux:button>
                    </div>

                    {{-- Brillo sutil al hover --}}
                    <div class="pointer-events-none absolute inset-x-0 -top-10 h-20 translate-y-0 bg-gradient-to-b from-white/40 to-transparent opacity-0 group-hover:opacity-100 group-hover:translate-y-6 transition duration-500"></div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Nota / ayuda --}}
    <div x-show="show"
         x-transition:enter="transition ease-out duration-1000 delay-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
        Consejo: Puedes cambiar de modalidad después desde el panel de la licenciatura.
    </div>
</div>
