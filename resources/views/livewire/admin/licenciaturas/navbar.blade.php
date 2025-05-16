<div>
        <flux:navbar class=" flex flex-col xl:flex-row gap-5 justify-around  dark:bg-neutral-800  rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 bg-gray-100">
            @foreach ($acciones as $accion)

               <flux:navbar.item href="{{route('licenciaturas.submodulo', ['slug_licenciatura' => $slug_licenciatura, 'slug_modalidad' => $slug_modalidad, 'submodulo' => $accion->slug])}}">
                <div class="flex items-center  gap-1">
                    <img class="w-7" src="{{asset('storage/acciones/'.$accion->icono)}}">

                    @if($accion->slug === 'bajas')
                        <flux:heading>
                        {{$accion->accion}} <flux:badge color="red" inset="top bottom"> {{$contar_bajas}}</flux:badge>
                    </flux:heading>
                    @else
                        <span>{{$accion->accion}}</span>
                    @endif

                </div>
            </flux:navbar.item>
            @endforeach

            </flux:navbar>
</div>
