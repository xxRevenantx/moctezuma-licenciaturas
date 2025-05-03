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
           <form wire:submit.prevent="actualizarDirectivo">

               <flux:field>
                <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

                    <div class="w-120 border-2 border-gray-50  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white text-center">Editar Directivo <flux:badge color="indigo">{{ $nombre }} {{ $apellido_paterno }} {{ $apellido_materno }} </flux:badge></h2>
                       <flux:input wire:model="titulo" :label="__('Título')" type="text" placeholder="Ejem: M.C, Lic, Dr, Mtro, Profr, etc."  autofocus autocomplete="titulo" />
                       <flux:input wire:model="nombre" :label="__('Nombre')" type="text" placeholder="Nombre del directivo"  autofocus autocomplete="nombre" />
                       <flux:input wire:model="apellido_paterno" :label="__('Apellido Paterno')" type="text" placeholder="Apellido Paterno"  autocomplete="apellido_paterno" />
                       <flux:input wire:model="apellido_materno" :label="__('Apellido Materno')" type="text" placeholder="Apellido Materno"  autocomplete="apellido_materno" />
                       <flux:input wire:model="cargo" :label="__('Cargo')" type="text" placeholder="Cargo" autocomplete="cargo" />
                       <flux:input wire:model="telefono" :label="__('Teléfono')" type="text" placeholder="Teléfono"  autocomplete="telefono" />
                       <flux:input wire:model="correo" :label="__('Correo Electrónico')" type="email" placeholder="Correo electrónico"  autocomplete="correo" />



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
