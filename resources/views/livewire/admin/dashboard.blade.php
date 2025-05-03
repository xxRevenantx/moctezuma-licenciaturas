<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-1">
            <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">
                <form wire:submit.prevent="guardarDatos">
                    <flux:field>
                        <flux:input wire:model.live="ciclo_escolar" :label="__('Ciclo escolar')" type="text"  autofocus autocomplete="ciclo_escolar" />
                        <flux:input style="text-transform: uppercase" wire:model.live="periodo_escolar" :label="__('Periodo escolar')" type="text"  autofocus autocomplete="periodo_escolar" />
                        <div class="flex items-center gap-4">
                            <div class="flex items-center">
                                <flux:button variant="primary" type="submit" class="w-full">{{ __('Guardar') }}</flux:button>
                            </div>
                        </div>
                    </flux:field>

                </form>



            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>

        @include('components.toast-message')
    </div>

