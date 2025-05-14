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
           <form wire:submit.prevent="actualizarUsuario">
               <flux:field>

               <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

                   <div class="w-120 border-2 border-gray-50  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                       <h2 class="text-xl font-bold mb-4 text-center text-accent">Editar Usuario <flux:badge color="indigo">{{ $username }}</flux:badge></h2>




                       <flux:input wire:model.live="username" :label="__('Nombre de usuario')" type="text" placeholder="Nombre de usuario"  autofocus autocomplete="Nombre de usuario" />
                          <flux:input wire:model.live="CURP" :label="__('CURP')" type="text" placeholder="CURP"  autocomplete="CURP" />
                       <flux:input wire:model.live="email" :label="__('Email')" type="email" placeholder="Email"  autocomplete="Email" />


                              @if($rol_name == "SuperAdmin")
                                    <flux:label>Status</flux:label>
                                @if($toggle === true)
                                    <flux:switch wire:model.live="status" />
                                @else
                                    <flux:switch variant="filled" disabled wire:model.live="status" />
                                @endif
                                  <flux:button wire:click="toggleStatus" variant="primary">Activar status</flux:button>
                                @else
                                  <flux:label>Status</flux:label>
                                  <flux:switch wire:model.live="status" />
                            @endif




                        <flux:checkbox.group wire:model.live="rol" label="Listado de roles">



                            @foreach ($roles as $rol )
                                    <flux:checkbox
                                    label="{{ $rol->name }}"
                                    value="{{ $rol->id }}"
                                />



                            @endforeach


                       </flux:checkbox.group>





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
