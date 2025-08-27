<x-layouts.app :title="__('Panel - ' . $accionn)">
    <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">



        {{-- ENLACE PARA ENVIARME A LA OTRA MODALIDAD Y A LA ACCION --}}
      @php
                $otraModalidad = $slug_modalidad === 'escolarizada' ? 'semiescolarizada' : 'escolarizada';
            @endphp

            <div x-data="{ showBtn:false }" x-init="setTimeout(() => showBtn = true, 120)" x-cloak class="flex justify-center mb-6">
                <a
                    x-show="showBtn"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    href="{{ route('licenciaturas.submodulo', [
                        'slug_licenciatura' => $slug_licenciatura,
                        'slug_modalidad' => $otraModalidad,
                        'submodulo' => $submodulo
                    ]) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white
                        bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600
                        shadow-lg hover:shadow-xl hover:scale-105 transform transition
                        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    aria-label="Cambiar a modalidad {{ ucfirst($otraModalidad) }}"
                >
                    {{-- Icono flecha cambio --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                    <span>Cambiar a {{ ucfirst($otraModalidad) }}</span>
                </a>
            </div>




        <livewire:admin.licenciaturas.navbar :slug_licenciatura="$slug_licenciatura" :slug_modalidad="$slug_modalidad" :acciones="$acciones" />


       {{-- Header animado: Licenciatura | Modalidad | Acción --}}
            <div
                x-data="{ show:false }"
                x-init="setTimeout(() => show = true, 30)"
                x-cloak
                aria-live="polite"
            >
                <div
                    x-show="show"
                    x-transition:enter="transition ease-out duration-400"
                    x-transition:enter-start="opacity-0 translate-y-3 blur-sm"
                    x-transition:enter-end="opacity-100 translate-y-0 blur-0"
                    class="relative mt-6 rounded-2xl border border-neutral-200 dark:border-neutral-700
                        bg-gradient-to-r from-sky-50 via-indigo-50 to-purple-50
                        dark:from-neutral-900 dark:via-neutral-800 dark:to-neutral-900
                        shadow-lg p-6 flex flex-col xl:flex-row items-center justify-center gap-4 text-center"
                    role="region"
                    aria-label="Encabezado de Licenciatura y Submódulo"
                >
                    {{-- Decoraciones difusas --}}
                    <div class="pointer-events-none absolute -top-2 -left-2 w-28 h-28 bg-sky-400/10 rounded-br-full blur-2xl"></div>
                    <div class="pointer-events-none absolute -bottom-2 -right-2 w-28 h-28 bg-indigo-400/10 rounded-tl-full blur-2xl"></div>

                    {{-- Título Licenciatura --}}
                    <h1 class="text-3xl font-extrabold tracking-wide text-neutral-800 dark:text-neutral-100 uppercase">
                        {{ $licenciatura->nombre }}
                    </h1>

                    {{-- Separador sutil en pantallas grandes --}}
                    <span class="hidden xl:inline-block h-6 w-px bg-neutral-300 dark:bg-neutral-700"></span>

                    {{-- Badges animados (con microinteracciones) --}}
                    <div class="flex items-center gap-3">
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-600 text-white shadow-md
                                hover:shadow-lg hover:scale-[1.03] transition"
                            title="Modalidad"
                        >
                            {{ ucfirst($slug_modalidad) }}
                        </span>

                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full bg-indigo-600 text-white shadow-md
                                hover:shadow-lg hover:scale-[1.03] transition"
                            title="Acción"
                        >
                            {{ ucfirst($accionn) }}
                        </span>
                    </div>
                </div>
            </div>



        @switch($submodulo)
            @case('inscripcion')
                <livewire:admin.licenciaturas.submodulo.inscripcion :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                @break;
            @case('matricula')
                <livewire:admin.licenciaturas.submodulo.matricula :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                @break;

            @case('asignacion-de-materias')
                <livewire:admin.licenciaturas.submodulo.asignar-materia :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                @break;

            @case('horarios')
                    @if ($slug_modalidad == 'escolarizada')
                        <livewire:admin.licenciaturas.submodulo.horario-escolarizada :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                    @else
                        <livewire:admin.licenciaturas.submodulo.horario-semiescolarizada :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                    @endif
                @break;

            @case('calificaciones')
                <livewire:admin.licenciaturas.submodulo.calificacion :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                @break;

            @case('bajas')
                <livewire:admin.licenciaturas.submodulo.baja :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                @break;

        @endswitch

    </div>
</x-layouts.app>

