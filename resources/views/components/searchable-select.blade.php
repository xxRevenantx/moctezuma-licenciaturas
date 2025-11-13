@php
    $wireModel = $attributes->get('wire:model.live')
        ?? $attributes->get('wire:model')
        ?? null;
@endphp

<flux:field :label="$label">
    <div
        x-data="{
            open: false,
            search: '',
            selectedValue: @js($value),
            selectedLabel: '',
            placeholder: @js($placeholder),

            init() {
                const hidden = this.$refs.hidden;

                // si Livewire ya traía un valor inicial
                if (hidden && hidden.value && !this.selectedValue) {
                    this.selectedValue = hidden.value;
                }

                if (this.selectedValue) {
                    const opt = this.$refs.list.querySelector(`[data-value='${this.selectedValue}']`);
                    if (opt) {
                        this.selectedLabel = opt.getAttribute('data-label') || opt.textContent.trim();
                    }
                }
            }
        }"
        x-init="init()"
        class="relative"
        x-on:keydown.escape.window="open = false"
    >
        {{-- Botón principal --}}
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-xl border
                   border-neutral-300 dark:border-neutral-700
                   bg-white dark:bg-neutral-900
                   px-3 py-2.5 text-sm text-neutral-900 dark:text-neutral-100
                   shadow-sm hover:bg-neutral-50 dark:hover:bg-neutral-800
                   focus:outline-none focus:ring-2 focus:ring-blue-500
                   focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900"
            x-on:click="open = !open"
            :aria-expanded="open"
        >
            <span class="truncate" x-text="selectedLabel || placeholder"></span>

            <svg
                class="h-4 w-4 ml-2 flex-shrink-0 text-neutral-500 transform transition-transform duration-150"
                :class="open ? 'rotate-180' : 'rotate-0'"
                xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        {{-- Input hidden que Livewire observa --}}
        <input
            type="hidden"
            x-ref="hidden"
            {{ $attributes->whereStartsWith('wire:model') }}
            :value="selectedValue"
        />

        {{-- Dropdown --}}
        <div
            x-show="open"
            x-transition.opacity
            x-on:click.outside="open = false"
            class="absolute z-50 mt-1 w-full rounded-2xl border
                   border-neutral-200 dark:border-neutral-700
                   bg-white dark:bg-neutral-900
                   shadow-lg shadow-black/10 dark:shadow-black/30"
        >
            {{-- Buscador --}}
            <div class="border-b border-neutral-200 dark:border-neutral-700 p-2">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-neutral-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m21 21-5.197-5.197M16.5 10.5a6 6 0 1 1-12 0 6 6 0 0 1 12 0z" />
                        </svg>
                    </span>

                    <input
                        type="text"
                        x-model="search"
                        class="w-full rounded-lg border border-neutral-200 dark:border-neutral-700
                               bg-neutral-50 dark:bg-neutral-900/60
                               pl-9 pr-3 py-2 text-sm
                               text-neutral-900 dark:text-neutral-100
                               placeholder-neutral-400 dark:placeholder-neutral-500
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Buscar..."
                    />
                </div>
            </div>

            {{-- Opciones --}}
            <ul x-ref="list" class="max-h-60 overflow-y-auto py-1">
                {{ $slot }}
            </ul>
        </div>
    </div>

    @if ($wireModel)
        @error($wireModel)
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ $message }}
            </p>
        @enderror
    @endif
</flux:field>
