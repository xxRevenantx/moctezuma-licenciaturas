@props([
    'label' => null,
    'placeholder' => 'Selecciona una opción',
    'name' => null,
])

@php
    $wireModel = $attributes->wire('model');
@endphp

<div x-data="{
    open: false,
    search: '',
    selectedValue: @entangle($wireModel),
    selectedLabel: '',
    options: [],

    init() {
        this.options = Array.from(this.$refs.options.querySelectorAll('[data-option]')).map((option) => {
            return {
                value: option.dataset.value,
                label: option.dataset.label,
                search: this.normalizar(option.dataset.label),
            };
        });

        this.actualizarTextoSeleccionado();

        this.$watch('selectedValue', () => {
            this.actualizarTextoSeleccionado();
        });
    },

    normalizar(texto) {
        return String(texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    },

    actualizarTextoSeleccionado() {
        const encontrado = this.options.find((option) => String(option.value) === String(this.selectedValue));

        this.selectedLabel = encontrado ? encontrado.label : '';
    },

    seleccionar(option) {
        this.selectedValue = option.value;
        this.selectedLabel = option.label;
        this.search = '';
        this.open = false;
    },

    limpiar() {
        this.selectedValue = null;
        this.selectedLabel = '';
        this.search = '';
        this.open = false;
    },

    get opcionesFiltradas() {
        const texto = this.normalizar(this.search);

        if (!texto) {
            return this.options;
        }

        return this.options.filter((option) => option.search.includes(texto));
    }
}" class="relative w-full" @click.outside="open = false">
    @if ($label)
        <label class="mb-1.5 block text-sm font-medium text-neutral-700 dark:text-neutral-200">
            {{ $label }}
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" x-model="selectedValue">

    <button type="button" @click="open = !open"
        class="flex w-full items-center justify-between gap-3 rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-left text-sm text-neutral-900 shadow-sm transition hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:hover:bg-neutral-800">
        <span class="block truncate"
            :class="selectedLabel ? 'text-neutral-900 dark:text-neutral-100' : 'text-neutral-400 dark:text-neutral-500'"
            x-text="selectedLabel || @js($placeholder)"></span>

        <div class="flex items-center gap-2">
            <button type="button" x-show="selectedValue" @click.stop="limpiar()"
                class="rounded-lg p-1 text-neutral-400 hover:bg-neutral-100 hover:text-red-500 dark:hover:bg-neutral-800"
                title="Limpiar selección">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <svg class="h-4 w-4 text-neutral-400 transition-transform" :class="{ 'rotate-180': open }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <div x-show="open" x-transition.opacity.scale.origin.top
        class="absolute z-50 mt-2 w-full overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-neutral-700 dark:bg-neutral-900">
        <div class="border-b border-neutral-100 p-2 dark:border-neutral-800">
            <input type="text" x-model.debounce.150ms="search" placeholder="Buscar alumno..."
                class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-2 text-sm text-neutral-900 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 dark:focus:bg-neutral-900">
        </div>

        <div class="max-h-72 overflow-y-auto p-1">
            <template x-for="option in opcionesFiltradas" :key="option.value">
                <button type="button" @click="seleccionar(option)"
                    class="flex w-full items-start justify-between gap-3 rounded-xl px-3 py-2 text-left text-sm transition hover:bg-indigo-50 dark:hover:bg-indigo-900/20"
                    :class="String(selectedValue) === String(option.value) ?
                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200' :
                        'text-neutral-700 dark:text-neutral-200'">
                    <span class="block leading-snug" x-text="option.label"></span>

                    <svg x-show="String(selectedValue) === String(option.value)"
                        class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </template>

            <div x-show="opcionesFiltradas.length === 0"
                class="px-3 py-6 text-center text-sm text-neutral-500 dark:text-neutral-400">
                No se encontraron resultados.
            </div>
        </div>
    </div>

    <div x-ref="options" class="hidden">
        {{ $slot }}
    </div>
</div>
