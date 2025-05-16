<x-layouts.app :title="__('Panel - ' . $accionn)">
    <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">



        <livewire:admin.licenciaturas.navbar :slug_licenciatura="$slug_licenciatura" :slug_modalidad="$slug_modalidad" :acciones="$acciones" />


         <div class="flex  bg-gray-100 flex-col xl:flex-row gap-5 mt-5 items-center justify-center dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 shadow-md">
                <div>
                    <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase">
                        {{$licenciatura->nombre}} | {{$slug_modalidad}} | {{$accionn}}

                    </h1>

                </div>
            </div>
        @switch($submodulo)
            @case('inscripcion')
                <livewire:admin.licenciaturas.submodulo.inscripcion :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                @break;

            @case('matricula')
                <livewire:admin.licenciaturas.submodulo.matricula :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                @break;
            @case('bajas')
                <livewire:admin.licenciaturas.submodulo.baja :licenciatura="$slug_licenciatura" :modalidad="$slug_modalidad" :submodulo="$submodulo" />
                @break;

        @endswitch

    </div>
</x-layouts.app>

