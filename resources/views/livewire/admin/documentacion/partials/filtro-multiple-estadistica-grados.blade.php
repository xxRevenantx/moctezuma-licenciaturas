@php
    $seleccionados = count($seleccion);
    $disponibles = count($opciones);
    $todos = $seleccionados === $disponibles;
@endphp
<div class="relative" x-data="{ abierto: false, busqueda: '' }" :class="abierto ? 'z-50' : 'z-10'" @keydown.escape.window="abierto = false">
    <label class="mb-1.5 block text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ $titulo }}</label>
    <button type="button" @click="abierto = !abierto; if (!abierto) busqueda = ''"
        :aria-expanded="abierto.toString()" aria-haspopup="true"
        aria-label="Seleccionar {{ mb_strtolower($titulo) }}"
        class="flex min-h-10 w-full items-center justify-between gap-3 rounded-lg border border-neutral-200 bg-white px-3 py-2 text-left text-sm shadow-sm transition hover:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-500/35 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
        <span class="min-w-0 truncate">
            @if ($disponibles === 0)
                <span class="text-neutral-500">No hay opciones disponibles</span>
            @elseif ($seleccionados === 0)
                <span class="font-medium text-amber-700 dark:text-amber-300">Ninguna seleccionada</span>
            @elseif ($todos)
                Todas ({{ $disponibles }})
            @else
                <span class="font-semibold text-[#006492] dark:text-sky-300">{{ $seleccionados }} de {{ $disponibles }} seleccionadas</span>
            @endif
        </span>
        <span class="flex shrink-0 items-center gap-2">
            @if ($seleccionados && ! $todos)
                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800 dark:bg-sky-900 dark:text-sky-100">{{ $seleccionados }}</span>
            @endif
            <svg class="size-4 text-neutral-500 transition" :class="abierto ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
        </span>
    </button>
    <div x-cloak x-show="abierto" x-transition.opacity.duration.120ms @click.outside="abierto = false"
        class="absolute inset-x-0 top-full z-50 mt-2 overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-neutral-700 dark:bg-neutral-900"
        role="group" aria-label="Opciones de {{ $titulo }}">
        <div class="border-b border-neutral-100 p-3 dark:border-neutral-800">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-2.5 size-4 text-neutral-400" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="search" x-model="busqueda" placeholder="Buscar {{ mb_strtolower($titulo) }}…"
                    aria-label="Buscar {{ mb_strtolower($titulo) }}"
                    class="w-full rounded-lg border border-neutral-200 bg-neutral-50 py-2 pl-9 pr-3 text-sm text-neutral-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white" />
            </div>
            <div class="mt-3 flex items-center justify-between gap-2">
                <button type="button" wire:click="seleccionarTodo('{{ $grupo }}')" class="text-xs font-semibold text-[#006492] hover:underline dark:text-sky-300">Seleccionar todas</button>
                <button type="button" wire:click="quitarTodo('{{ $grupo }}')" class="text-xs font-semibold text-neutral-600 hover:underline dark:text-neutral-300">Ninguna</button>
            </div>
        </div>
        <div class="max-h-64 space-y-0.5 overflow-y-auto p-2">
            @forelse ($opciones as $opcion)
                <label wire:key="opcion-grados-{{ $grupo }}-{{ $opcion['id'] }}" x-show="$el.textContent.toLocaleLowerCase().includes(busqueda.toLocaleLowerCase())"
                    class="flex cursor-pointer items-start gap-2.5 rounded-lg px-2.5 py-2 text-sm text-neutral-800 transition hover:bg-sky-50 dark:text-neutral-200 dark:hover:bg-neutral-800">
                    <input type="checkbox" wire:model.live="{{ $propiedad }}" value="{{ $opcion['id'] }}"
                        class="mt-0.5 size-4 shrink-0 rounded border-neutral-300 text-[#006492] focus:ring-sky-500 dark:border-neutral-600" />
                    <span>{{ $opcion[$campo] }}</span>
                </label>
            @empty
                <p class="p-3 text-center text-xs text-neutral-500">No hay opciones disponibles para este reporte.</p>
            @endforelse
        </div>
        <div class="border-t border-neutral-100 bg-neutral-50 px-3 py-2 text-right dark:border-neutral-800 dark:bg-neutral-800/60">
            <button type="button" @click="abierto = false; busqueda = ''" class="rounded-lg bg-[#006492] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#00567e]">Listo</button>
        </div>
    </div>
</div>
