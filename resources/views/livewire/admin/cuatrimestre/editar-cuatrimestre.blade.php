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
           <form wire:submit.prevent="actualizarCuatrimestre">

               <flux:field>
                <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

                    <div class="w-120 border-2 border-gray-50 bg-white  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white text-center">Editar <flux:badge color="indigo">{{ $nombre_cuatrimestre }} </flux:badge></h2>


                        <flux:input  badge="Requerido" wire:model="cuatrimestre" :label="__('Cuatrimestre')" min="1"  type="number" placeholder="No. de cuatrimestre" autocomplete="cuatrimestre" />

                         <flux:input  badge="Requerido" wire:model.live="nombre_cuatrimestre" :label="__('Nombre Cuatrimestre')" type="text" placeholder="Nombre de cuatrimestre" autocomplete="nombre_cuatrimestre" />


                        <flux:select  badge="Requerido"  label="Selecciona los meses" wire:model.live="mes_id">
                            <flux:select.option value="0">--Selecciona los meses--</flux:select.option>
                            @foreach($meses as $mes)

                                <flux:select.option value="{{ $mes->id }}">{{ $mes->meses }}</flux:select.option>
                            @endforeach
                        </flux:select>



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
