<div>
    <div class="flex flex-col gap-4">

            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Asignación del personal Directivo</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para asignar un nuevo directivo.</p>
     </div>

        <div x-data="{ open: false }" class="my-4">
            <button
            @click="open = !open"
            class="bg-blue-500 text-white py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            {{ __('Nuevo Directivo') }}
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
            x-transition:enter-start="opacity-0 max-h-0"
            x-transition:enter-end="opacity-100 max-h-screen"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 max-h-screen"
            x-transition:leave-end="opacity-0 max-h-0"
            class="overflow-hidden">

            <form wire:submit.prevent="crearDirectivo" class="space-y-4 mt-4 p-1">
                <flux:field>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model="titulo" :label="__('Título')" type="text" placeholder="Ejem: M.C, Lic, Dr, Mtro, Profr, etc." autofocus autocomplete="titulo" />
                        <flux:input wire:model="nombre" :label="__('Nombre')" type="text" placeholder="Nombre del directivo" autofocus autocomplete="nombre" />
                        <flux:input wire:model="apellido_paterno" :label="__('Apellido Paterno')" type="text" placeholder="Apellido Paterno" autocomplete="apellido_paterno" />
                        <flux:input wire:model="apellido_materno" :label="__('Apellido Materno')" type="text" placeholder="Apellido Materno" autocomplete="apellido_materno" />
                        <flux:input wire:model="cargo" :label="__('Cargo')" type="text" placeholder="Cargo" autocomplete="cargo" />
                        <flux:input wire:model="telefono" :label="__('Teléfono')" type="text" placeholder="Teléfono" autocomplete="telefono" />
                        <flux:input wire:model="correo" :label="__('Correo Electrónico')" type="email" placeholder="Correo electrónico" autocomplete="correo" />
                        <div class="flex items-center gap-4 mt-5">
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
