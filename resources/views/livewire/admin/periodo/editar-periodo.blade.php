<div x-data="{ show: @entangle('open') }"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="show = false; $wire.cerrarModal()"
        class="fixed inset-0 bg-gray-50 dark:bg-neutral-800 z-50 flex items-center justify-center ">


       {{-- <div @click.away="show = false;  $wire.cerrarModal()" class="relative"> --}}
       <div class="relative">
           <button @click="show = false" class="absolute text-2xl top-2 right-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-500">
               &times;
           </button>

           <form wire:submit.prevent="actualizarPeriodo">

               <flux:field>
                <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

                    <div class="w-120 border-2 border-gray-50 bg-white  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white text-center">Editar Periodo</h2>

                        <flux:input  badge="Requerido" wire:model="ciclo_escolar" :label="__('Ciclo escolar')" type="text" placeholder="2020-2023"   autocomplete="ciclo_escolar" />

                        <flux:select  badge="Requerido" wire:model.live="cuatrimestre_id" :label="__('Cuatrimestre')">
                            <flux:select.option value="0" >--Selecciona un cuatrimestre--</flux:select.option>
                            @foreach ($cuatrimestres as $cuatrimestre)
                            <flux:select.option value="{{ $cuatrimestre->id }}" >{{ $cuatrimestre->cuatrimestre }} ° CUATRIMESTRE</flux:select.option>
                            @endforeach
                        </flux:select>

                          <flux:input  badge="Requerido* Selecciona un cuatrimestre" variant="filled" readonly type="text" wire:model="mesesPeriodo" :label="__('Meses')"  value="{{$mesesPeriodo}}" />

                        <flux:select  badge="Requerido" wire:model="generacion_id" :label="__('Generación')">
                            <flux:select.option value="0" >--Selecciona una generación--</flux:select.option>
                            @foreach ($generaciones as $generacion)
                            <flux:select.option value="{{ $generacion->id }}" >{{ $generacion->generacion }}</flux:select.option>
                            @endforeach
                        </flux:select>


                        <flux:input wire:model="inicio_periodo" :label="__('Inicio del periodo')" type="date"   autocomplete="fecha_inicio" />

                        <flux:input wire:model="termino_periodo" :label="__('Término del periodo')" type="date"  autocomplete="fecha_termino" />



                   <div class="mt-6 flex justify-end gap-2">
                              <div class="flex items-center">
                                   <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Actualizar') }}</flux:button>
                            </div>
                             <div class="flex items-center">
                               <flux:button  @click="show = false; $wire.cerrarModal()" class="w-full cursor-pointer">{{ __('Cancelar') }}</flux:button>
                             </div>
                     </div>
                    </div>
                </div>

                    </flux:field>

                    </form>
            </div>


       </div>
   </div>

</div>
