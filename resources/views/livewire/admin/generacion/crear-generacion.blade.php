<div>
    <div class="flex flex-col gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Crear Generación</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para crear un nueva generación.</p>
    </div>

    <div  x-data="{ open: false }" class="my-4">
        <button
        @click="open = !open"
        class="bg-blue-500 text-white py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
        </svg>
        {{ __('Nueva generación') }}
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
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="overflow-hidden">
        <form wire:submit.prevent="crearGeneracion" class="space-y-4 mt-4  p-2">
            <flux:field>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <flux:input  badge="Requerido" wire:model="generacion" :label="__('Generación')" type="text" placeholder="2020-2023" autofocus autocomplete="titulo" />
                    <div class="flex items-center gap-4 md:mt-7">
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

