@props(['value'])

@php
    use Illuminate\Support\Str;

    $label       = trim($slot);
    $searchText  = Str::of($label)->lower();
@endphp

<li>
    <button
        type="button"
        class="flex w-full items-center justify-between px-3 py-2 text-sm
               text-neutral-800 dark:text-neutral-100
               hover:bg-neutral-100 dark:hover:bg-neutral-800
               focus:outline-none"
        data-value="{{ $value }}"
        data-label="{{ $label }}"
        x-on:click="
            // actualiza el estado visual del select
            selectedValue  = '{{ $value }}';
            selectedLabel  = '{{ $label }}';
            open           = false;
            search         = '';

            // avisa a Livewire: cambia valor del input hidden y dispara 'input'
            if ($refs.hidden) {
                $refs.hidden.value = '{{ $value }}';
                $refs.hidden.dispatchEvent(new Event('input', { bubbles: true }));
            }
        "
        x-show="!search || '{{ $searchText }}'.includes(search.toLowerCase())"
        :class="selectedValue == '{{ $value }}'
            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200'
            : ''"
    >
        <span class="truncate">{{ $slot }}</span>

        <span x-show="selectedValue == '{{ $value }}'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                 fill="none" viewBox="0 0 24 24"
                 stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </span>
    </button>
</li>
