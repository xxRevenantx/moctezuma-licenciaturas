<div
    x-data="{
        abierto: @entangle('abierto'),
        visor: false,
        visorUrl: '',
        visorTitulo: '',
        arrastrando: null
    }"
    x-cloak
>
    <div
        x-show="abierto"
        x-transition.opacity
        @keydown.escape.window="if (!visor) { $wire.cerrar() }"
        class="fixed inset-0 z-[11000] flex items-center justify-center bg-slate-950/75 p-2 backdrop-blur-sm sm:p-4"
    >
        <div class="flex h-[96vh] w-full max-w-[1500px] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
            <header class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 dark:border-neutral-700 sm:px-6">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#006492]/10 text-[#006492] dark:bg-[#006492]/20 dark:text-sky-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3.75H6.75A2.25 2.25 0 004.5 6v12a2.25 2.25 0 002.25 2.25h10.5A2.25 2.25 0 0019.5 18V6a2.25 2.25 0 00-2.25-2.25H15M9 3.75A2.25 2.25 0 0111.25 1.5h1.5A2.25 2.25 0 0115 3.75M9 3.75A2.25 2.25 0 0011.25 6h1.5A2.25 2.25 0 0015 3.75M9 12h6m-6 3.75h6"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-xl font-bold text-slate-950 dark:text-white">Organizar páginas del expediente</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-neutral-400">
                            Asigna cada página a un documento, cambia el orden y corrige la orientación. Los archivos originales se conservan.
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="cerrar" class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 dark:text-neutral-300 dark:hover:bg-neutral-800" aria-label="Cerrar organizador">✕</button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto">
                @if ($fuentes === [])
                    <div class="flex min-h-[55vh] items-center justify-center p-8 text-center">
                        <div>
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-neutral-800 dark:text-neutral-300">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5V5.625A3.375 3.375 0 0011.25 2.25h-4.5A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25v-5.25z"/></svg>
                            </div>
                            <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Todavía no hay archivos fuente</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Sube un PDF, JPG o PNG desde cualquiera de las tarjetas documentales.</p>
                        </div>
                    </div>
                @else
                    <div class="grid min-h-full lg:grid-cols-[280px_minmax(0,1fr)]">
                        <aside class="border-b border-slate-200 bg-slate-50/80 p-4 dark:border-neutral-700 dark:bg-neutral-950/40 lg:border-b-0 lg:border-r">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">Archivos fuente</p>
                                    <p class="text-xs text-slate-500 dark:text-neutral-400">{{ count($fuentes) }} archivo(s)</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $paginasSinClasificar > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                                    {{ $paginasSinClasificar }} sin clasificar
                                </span>
                            </div>

                            <div class="mt-4 space-y-2">
                                @foreach ($fuentes as $fuente)
                                    <button
                                        type="button"
                                        wire:click="seleccionarFuente({{ $fuente['id'] }})"
                                        @class([
                                            'w-full rounded-xl border p-3 text-left transition',
                                            'border-[#006492] bg-white ring-2 ring-[#006492]/10 dark:bg-neutral-900' => $fuenteActivaId === $fuente['id'],
                                            'border-slate-200 bg-white/70 hover:border-slate-300 dark:border-neutral-700 dark:bg-neutral-900/50' => $fuenteActivaId !== $fuente['id'],
                                        ])
                                    >
                                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white" title="{{ $fuente['nombre'] }}">{{ $fuente['nombre'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $fuente['paginas'] }} página(s) · {{ $fuente['fecha'] }}</p>
                                    </button>
                                @endforeach
                            </div>

                            @php
                                $fuenteActiva = collect($fuentes)->firstWhere('id', $fuenteActivaId);
                            @endphp
                            @if ($fuenteActiva)
                                <a href="{{ $fuenteActiva['original_url'] }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-6L12 15m0 0l4.5-4.5M12 15V3"/></svg>
                                    Descargar original
                                </a>
                            @endif

                            @if ($historial !== [])
                                <details class="mt-5 rounded-xl border border-slate-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                                    <summary class="cursor-pointer px-3 py-2.5 text-sm font-semibold text-slate-700 dark:text-neutral-200">Historial de organización</summary>
                                    <div class="space-y-2 border-t border-slate-200 p-3 dark:border-neutral-700">
                                        @foreach ($historial as $version)
                                            <div class="rounded-lg bg-slate-50 p-2.5 text-xs dark:bg-neutral-800">
                                                <p class="font-semibold text-slate-800 dark:text-neutral-100">Versión {{ $version['version'] }} · {{ $version['paginas'] }} páginas</p>
                                                <p class="mt-1 text-slate-500 dark:text-neutral-400">{{ $version['fecha'] }} · {{ $version['usuario'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </aside>

                        <main class="min-w-0 p-4 sm:p-6">
                            @if ($fuenteActiva)
                                <section class="rounded-2xl border border-[#006492]/20 bg-[#006492]/5 p-4 dark:border-sky-900/50 dark:bg-sky-950/20">
                                    <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                                        <div>
                                            <h3 class="font-bold text-slate-900 dark:text-white">Asignación rápida por rangos</h3>
                                            <p class="mt-1 text-xs text-slate-600 dark:text-neutral-300">Aplica al archivo seleccionado. Ejemplos: <span class="font-mono">1-2</span>, <span class="font-mono">3,5-7</span>. Una página no puede repetirse.</p>
                                        </div>
                                        <button type="button" wire:click="aplicarRangos" class="shrink-0 rounded-xl bg-[#006492] px-4 py-2 text-sm font-semibold text-white hover:bg-[#00547b]">Aplicar rangos</button>
                                    </div>
                                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                        @foreach ($tipos as $tipoConfig)
                                            <label class="block">
                                                <span class="text-xs font-semibold text-slate-700 dark:text-neutral-200">{{ $tipoConfig['label'] }}</span>
                                                <input
                                                    type="text"
                                                    wire:model.defer="rangos.{{ $tipoConfig['tipo'] }}"
                                                    placeholder="Ej. 1-2,4"
                                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-[#006492] focus:ring-2 focus:ring-[#006492]/15 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                                >
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('rangos') <p class="mt-3 text-sm font-medium text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
                                </section>

                                @php
                                    $paginasFuente = collect($paginas)
                                        ->where('fuente_id', $fuenteActivaId)
                                        ->sortBy('pagina')
                                        ->values();
                                @endphp

                                <section class="mt-6">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Páginas de {{ $fuenteActiva['nombre'] }}</h3>
                                            <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Selecciona el tipo de documento de cada página. Puedes ampliar y girar la vista.</p>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-neutral-400">El orden final se ajusta en la sección inferior.</p>
                                    </div>

                                    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                                        @foreach ($paginasFuente as $pagina)
                                            @php
                                                $tipoPagina = collect($tipos)->firstWhere('tipo', $pagina['tipo']);
                                            @endphp
                                            <article class="overflow-hidden rounded-2xl border {{ $pagina['tipo'] ? 'border-slate-200 dark:border-neutral-700' : 'border-amber-300 bg-amber-50/30 dark:border-amber-800 dark:bg-amber-950/10' }}">
                                                <div class="relative h-64 bg-slate-200 dark:bg-neutral-800">
                                                    <iframe src="{{ $pagina['preview_url'] }}#toolbar=0&navpanes=0&scrollbar=0" loading="lazy" class="pointer-events-none h-full w-full" title="Página {{ $pagina['pagina'] }}"></iframe>
                                                    <button
                                                        type="button"
                                                        @click="visor = true; visorUrl = '{{ $pagina['preview_url'] }}'; visorTitulo = @js($pagina['fuente_nombre'].' · Página '.$pagina['pagina'])"
                                                        class="absolute inset-0 flex items-end justify-end bg-transparent p-3 opacity-0 transition hover:bg-slate-950/10 hover:opacity-100"
                                                        title="Ampliar página"
                                                    >
                                                        <span class="rounded-lg bg-slate-950/80 px-2.5 py-1.5 text-xs font-semibold text-white">Ampliar</span>
                                                    </button>
                                                </div>
                                                <div class="space-y-3 p-3">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <p class="font-semibold text-slate-900 dark:text-white">Página {{ $pagina['pagina'] }}</p>
                                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $pagina['tipo'] ? 'bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                                            {{ $tipoPagina['label'] ?? 'Sin clasificar' }}
                                                        </span>
                                                    </div>

                                                    <select
                                                        wire:change="actualizarTipo('{{ $pagina['clave'] }}', $event.target.value)"
                                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#006492] focus:ring-2 focus:ring-[#006492]/15 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                                    >
                                                        <option value="" @selected(! $pagina['tipo'])>Sin clasificar</option>
                                                        @foreach ($tipos as $tipoConfig)
                                                            <option value="{{ $tipoConfig['tipo'] }}" @selected($pagina['tipo'] === $tipoConfig['tipo'])>{{ $tipoConfig['label'] }}</option>
                                                        @endforeach
                                                    </select>

                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="text-xs text-slate-500 dark:text-neutral-400">Rotación: {{ $pagina['rotacion'] }}°</span>
                                                        <div class="flex gap-1">
                                                            <button type="button" wire:click="rotarPagina('{{ $pagina['clave'] }}', -90)" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800" title="Girar a la izquierda">↶</button>
                                                            <button type="button" wire:click="rotarPagina('{{ $pagina['clave'] }}', 90)" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800" title="Girar a la derecha">↷</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>

                                <section class="mt-8 border-t border-slate-200 pt-6 dark:border-neutral-700">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Orden final por documento</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Arrastra páginas dentro del mismo documento o usa las flechas. Se pueden combinar páginas de distintos archivos fuente.</p>
                                    </div>

                                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                                        @foreach ($tipos as $tipoConfig)
                                            @php
                                                $paginasTipo = collect($paginas)
                                                    ->where('tipo', $tipoConfig['tipo'])
                                                    ->sortBy('orden')
                                                    ->values();
                                            @endphp
                                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                                                <div class="flex items-center justify-between gap-3">
                                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $tipoConfig['label'] }}</p>
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-neutral-800 dark:text-neutral-300">{{ $paginasTipo->count() }} pág.</span>
                                                </div>

                                                @if ($paginasTipo->isEmpty())
                                                    <p class="mt-3 rounded-xl border border-dashed border-slate-300 px-3 py-4 text-center text-xs text-slate-500 dark:border-neutral-700 dark:text-neutral-400">Sin páginas asignadas.</p>
                                                @else
                                                    <div class="mt-3 space-y-2">
                                                        @foreach ($paginasTipo as $pagina)
                                                            <div
                                                                draggable="true"
                                                                @dragstart="arrastrando = '{{ $pagina['clave'] }}'"
                                                                @dragend="arrastrando = null"
                                                                @dragover.prevent
                                                                @drop.prevent="$wire.reordenarPagina(arrastrando, '{{ $pagina['clave'] }}'); arrastrando = null"
                                                                class="flex cursor-grab items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-2.5 active:cursor-grabbing dark:border-neutral-700 dark:bg-neutral-800/60"
                                                            >
                                                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-bold text-[#006492] shadow-sm dark:bg-neutral-900 dark:text-sky-300">{{ $pagina['orden'] }}</span>
                                                                <div class="min-w-0 flex-1">
                                                                    <p class="truncate text-xs font-semibold text-slate-800 dark:text-neutral-100" title="{{ $pagina['fuente_nombre'] }}">{{ $pagina['fuente_nombre'] }}</p>
                                                                    <p class="text-[11px] text-slate-500 dark:text-neutral-400">Página {{ $pagina['pagina'] }} · {{ $pagina['rotacion'] }}°</p>
                                                                </div>
                                                                <div class="flex shrink-0 gap-1">
                                                                    <button type="button" wire:click="moverPagina('{{ $pagina['clave'] }}', 'arriba')" class="rounded-lg p-1.5 text-slate-500 hover:bg-white dark:text-neutral-300 dark:hover:bg-neutral-900" title="Subir">↑</button>
                                                                    <button type="button" wire:click="moverPagina('{{ $pagina['clave'] }}', 'abajo')" class="rounded-lg p-1.5 text-slate-500 hover:bg-white dark:text-neutral-300 dark:hover:bg-neutral-900" title="Bajar">↓</button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        </main>
                    </div>
                @endif
            </div>

            <footer class="shrink-0 border-t border-slate-200 bg-white px-5 py-4 dark:border-neutral-700 dark:bg-neutral-900 sm:px-6">
                @error('organizacion')
                    <p class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">{{ $message }}</p>
                @enderror

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        @if ($paginasSinClasificar > 0)
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ $paginasSinClasificar }} página(s) quedarán sin clasificar.</p>
                            <p class="text-xs text-slate-500 dark:text-neutral-400">Se conservarán en los archivos originales, pero no se incluirán en los documentos ni en las exportaciones.</p>
                        @else
                            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Todas las páginas están clasificadas.</p>
                        @endif
                        @if ($mensaje)
                            <p class="mt-1 text-xs font-medium text-[#006492] dark:text-sky-300">{{ $mensaje }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col-reverse gap-2 sm:flex-row">
                        <button type="button" wire:click="cerrar" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Guardar borrador y cerrar</button>
                        @if ($fuentes !== [])
                            <button type="button" wire:click="confirmar" wire:loading.attr="disabled" wire:target="confirmar" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#88AC2E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#769827] disabled:cursor-wait disabled:opacity-60">
                                <svg wire:loading wire:target="confirmar" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                <span wire:loading.remove wire:target="confirmar">Confirmar organización</span>
                                <span wire:loading wire:target="confirmar">Generando documentos…</span>
                            </button>
                        @endif
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div x-show="visor" x-transition.opacity class="fixed inset-0 z-[12000] flex items-center justify-center bg-slate-950/85 p-3 backdrop-blur-sm" @keydown.escape.window="visor = false">
        <div @click.outside="visor = false" class="flex h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="flex h-14 shrink-0 items-center justify-between border-b border-slate-200 px-4 dark:border-neutral-700">
                <p class="truncate pr-4 text-sm font-semibold text-slate-900 dark:text-white" x-text="visorTitulo"></p>
                <button type="button" @click="visor = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-neutral-300 dark:hover:bg-neutral-800">✕</button>
            </div>
            <iframe :src="visorUrl" class="min-h-0 flex-1 w-full" title="Vista ampliada de página"></iframe>
        </div>
    </div>
</div>
