<div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">


    <div class="w-full">
        <div class="mx-auto sm:px-6 lg:px-8">
          <div class="relative isolate overflow-hidden px-6 py-10 text-center sm:rounded-3xl sm:px-16">
            <h2 class="font-nudge-extrabold mx-auto max-w-2xl text-2xl font-bold uppercase tracking-wide sm:text-3xl">Selecciona la modalidad de {{ $licenciatura->nombre }}</h2>

                <div class="text-center ">


                    <div class="flex flex-col xl:flex-row items-center">
                    @foreach ($modalidades as $modalidad)
                          @php
                            $estadisticas = $this->obtenerEstadisticasPorModalidad($modalidad);
                        @endphp
                                <div class="mx-4 sm:max-w-sm md:max-w-sm lg:max-w-sm xl:max-w-sm sm:mx-auto md:mx-auto lg:mx-auto xl:mx-auto mt-16 bg-white shadow-xl rounded-lg text-gray-900 dark:bg-gray-800 dark:text-white">
                            <div class="rounded-t-lg h-32 overflow-hidden">
                                <img class="object-cover object-top w-full" src='{{asset('storage/banner.png')}}'>
                            </div>
                            <div class="mx-auto w-32 h-32 relative -mt-16 border-4 border-white rounded-full overflow-hidden">
                                <img class="object-cover object-center h-32" src='{{asset('storage/logo-moctezuma.jpg')}}'>
                            </div>
                            <div class="text-center mt-2">
                                <h2 class="font-semibold">{{$modalidad->nombre}}</h2>
                            </div>
                            <ul class="py-4 mt-2 text-gray-700 flex items-center justify-around dark:text-gray-200 ">
                                <li class="flex flex-col items-center justify-around">

                                   <flux:badge color="amber"> HOMBRES</flux:badge>
                                    <div>{{ $estadisticas['hombres'] }}</div>

                                     {{-- <div>{{ $modalidad->inscripcion->where('licenciatura_id', $licenciatura->id)->where('status', "true")->where('sexo', 'H')->count() }}</div> --}}
                                </li>
                                <li class="flex flex-col items-center justify-between">
                                    <flux:badge color="zinc">TOTAL</flux:badge>
                                    <div>{{ $estadisticas['total'] }}</div>

                                </li>
                                <li class="flex flex-col items-center justify-around">
                                    <flux:badge color="violet">MUJERES</flux:badge>

                                    <div>{{ $estadisticas['mujeres'] }}</div>
                                </li>
                            </ul>
                            <div class="p-4 border-t mx-8 mt-2">



                                 <flux:button variant="primary"  wire:navigate wire:click="irAModalidad('{{strtolower($modalidad->slug)}}')">{{$modalidad->nombre}}</flux:button>

                            </div>
                            </div>

                    @endforeach
                </div>
            </div>



        </div>
      </div>






</div>


    </div>


