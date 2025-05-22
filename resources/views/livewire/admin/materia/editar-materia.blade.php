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

           <form wire:submit.prevent="actualizarMateria">

               <flux:field>
                <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

                    <div class="w-120 border-2 border-gray-50 bg-white  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white text-center">Editar Materia <flux:badge color="indigo">{{ $nombre }} </flux:badge></h2>

                         <flux:field>


                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">


                        <flux:input type="text" badge="Requerido" label="Materia" placeholder="Materia" wire:model.live="nombre" />
                        <flux:input type="text" variant="filled"  badge="Requerido" label="url" placeholder="url" wire:model="slug" />


                        <flux:select badge="Requerido" label="Licenciatura" placeholder="Selecciona una licenciatura" wire:model="licenciatura_id">
                            <option value="">--Selecciona una licenciatura--</option>
                            @foreach($licenciaturas as $licenciatura)
                                <option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select badge="Requerido" label="Cuatrimestre" placeholder="Selecciona un cuatrimestre" wire:model="cuatrimestre_id">
                            <option value="">--Selecciona un cuatrimestre--</option>
                            @foreach($cuatrimestres as $cuatrimestre)
                                <option value="{{ $cuatrimestre->id }}">{{ $cuatrimestre->nombre_cuatrimestre }}</option>
                            @endforeach
                        </flux:select>



                        <flux:input type="text" badge="Requerido" label="Clave" placeholder="Clave" wire:model="clave" />
                        <flux:input type="number" badge="Requerido" label="Créditos" placeholder="Créditos" wire:model="creditos" />


                        <flux:select badge="Requerido" label="Calificable" wire:model="calificable">
                            <option value="">--Selecciona una opción--</option>
                             <option value="true">Sí</option>
                             <option value="false">No</option>
                        </flux:select>



                    </div>
                </flux:field>

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
