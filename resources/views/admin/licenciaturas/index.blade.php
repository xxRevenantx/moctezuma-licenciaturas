<x-layouts.app :title="__('Panel - ' . $accion)">
    <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">

        <flux:navbar class=" flex flex-col xl:flex-row gap-5 justify-around  dark:bg-neutral-800  rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 bg-gray-100">
            @foreach ($acciones as $accion)

               <flux:navbar.item href="{{route('licenciaturas.submodulo', ['slug' => $licenciatura->slug, 'modalidad' => $modalidad, 'submodulo' => $accion->slug])}}">
                <div class="flex items-center  gap-1">
                    <img class="w-7" src="{{asset('storage/acciones/'.$accion->icono)}}">

                    <span>{{$accion->accion}}</span>
                </div>
            </flux:navbar.item>
            @endforeach

            </flux:navbar>

         <div class="flex bg-gray-100 flex-col xl:flex-row gap-5 mt-5 items-center justify-center dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 shadow-md">
                <div>
                    <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase">
                        {{$licenciatura->nombre}} |
                    </h1>

                </div>

                <div >
                    <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase">

                        {{$modalidad}}
                    </h1>
                </div>



            </div>
        @switch($submodulo)
            @case('inscripcion')
                <livewire:admin.licenciaturas.submodulo.inscripcion :slug="$slug" :modalidad="$modalidad" :submodulo="$submodulo" />
                @break

            @case('ver-alumnos')
                Desde alumnos

                @break;
                @include('admin.licenciaturas.submodulo.inscripcion')
        @endswitch

    </div>
</x-layouts.app>

