<div>
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Crear Licenciaturas</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para crear licenciaturas.</p>
    </div>

    <div x-data="{ open: false }" class="my-4">
        <button
            @click="open = !open"
            class="bg-blue-500 text-white py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            {{ __('Nueva Licenciatura') }}
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
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="mt-4">
            <form wire:submit.prevent="guardarLicenciatura">
                <flux:field>
                    <div class="flex flex-col items-center justify-center gap-5 mb-4">
                        <div class="w-120 border-2 border-gray-100 bg-white dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                            <flux:input wire:model.live="imagen" :label="__('Imagen de la licenciatura')" type="file" accept="image/jpeg,image/jpg,image/png" />

                            @if ($imagen)
                                <div class="mt-4 flex flex-col items-center justify-center">
                                    <img src="{{ $imagen->temporaryUrl() }}" alt="{{ __('Profile Preview') }}" class="w-20 h-20 rounded-full">
                                </div>
                            @endif

                            <flux:input wire:model.live="nombre" :label="__('Licenciatura')" type="text" placeholder="Nombre de la licenciatura" autofocus autocomplete="nombre" />
                            <flux:input wire:model.live="slug" readonly :label="__('Url')" type="text" placeholder="Url" autofocus autocomplete="slug" />
                            <flux:input wire:model.live="nombre_corto" :label="__('Nombre corto')" type="text" placeholder="Nombre corto" autofocus autocomplete="nombre_corto" />
                            <flux:input wire:model.live="RVOE" :label="__('RVOE')" type="text" placeholder="RVOE" autofocus autocomplete="RVOE" />

                            <div class="flex items-center gap-4 mt-3">
                                <div class="flex items-center">
                                    <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                </flux:field>
            </form>
        </div>
    </div>
</div>
