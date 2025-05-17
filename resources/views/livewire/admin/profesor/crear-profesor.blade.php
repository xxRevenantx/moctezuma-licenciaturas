<div>
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Crear profesor</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para crear profesores.</p>
    </div>

    <div x-data="{ open: false }" class="my-4">
        <button
            @click="open = !open"
            class="bg-blue-500 text-white py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            {{ __('Nuevo profesor') }}
            <span x-show="!open" class="material-icons">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.75l7.5 7.5 7.5-7.5" />
            </svg>
            </span>
            <span x-show="open" class="material-icons">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 14.25l7.5-7.5 7.5 7.5" />
            </svg>
            </span>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="mt-4"
        >
            <form wire:submit.prevent="guardarProfesor">

                <flux:field>
                     <flux:input wire:model.live="foto" :label="__('Foto del profesor')" type="file" accept="image/jpeg,image/jpg,image/png" />

                        <div wire:loading wire:target="foto" class="flex items-center gap-2 text-blue-600 mt-2">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Cargando imagen...</span>
                        </div>

                        @if ($foto)
                            <div class="mt-4 flex flex-col items-center justify-center">
                                <img src="{{ $foto->temporaryUrl() }}" alt="{{ __('Profile Preview') }}" class="w-40 h-40 rounded-full">
                            </div>
                        @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">



                        <flux:select badge="Requerido" label="Usuario" wire:model.live="user_id">
                            <option value="">--Seleccione un usuario--</option>
                            @foreach($usuarios as $key =>  $usuario)
                                <option value="{{ $usuario->id }}">{{ $key+1 }}.- {{ $usuario->username }} => {{ $usuario->CURP }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input type="text" badge="Requerido" variant="filled" readonly label="CURP" placeholder="CURP"  value="{{$CURP}}" />
                        <div wire:loading wire:target="user_id" class="flex items-center gap-2 text-blue-600 ">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Cargando datos del CURP...</span>
                        </div>
                        <flux:input type="text" badge="Requerido" label="Nombre" placeholder="Nombre" wire:model.live="nombre" />
                        <flux:input type="text" badge="Requerido" label="Apellido Paterno" placeholder="Apellido Paterno" wire:model="apellido_paterno" />
                        <flux:input type="text" badge="Opcional" label="Apellido Materno" placeholder="Apellido Materno" wire:model="apellido_materno" />
                        <flux:input type="text" badge="Opcional" label="Teléfono" placeholder="Teléfono" wire:model="telefono" />
                        <flux:input type="text" badge="Opcional" label="Perfil" placeholder="Perfil" wire:model="perfil" />
                        <flux:input type="color" badge="Opcional" label="Color" placeholder="Color" wire:model="color" />


                        <div class="flex items-center gap-4 mt-3">
                            <div class="flex items-center">
                                <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
                            </div>
                        </div>
                    </div>
                </flux:field>



            </form>
        </div>
    </div>
</div>
