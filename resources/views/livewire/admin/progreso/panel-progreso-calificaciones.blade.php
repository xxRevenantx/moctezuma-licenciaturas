<div class="flex w-full flex-col gap-6"
     x-data="{ openL:true, openG:true, openM:true, openC:true }">

    {{-- ENCABEZADO / FILTROS --}}
    <div class="relative overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
        <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-fuchsia-500"></div>
        <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-neutral-900 dark:text-neutral-100">
                        Progreso de Calificaciones
                    </h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Filtra y visualiza el avance de captura y las calificaciones faltantes.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="limpiarFiltros"
                            class="inline-flex items-center gap-2 rounded-lg px-3 py-2 ring-1 ring-neutral-300 text-neutral-700 hover:bg-neutral-50 dark:text-neutral-200 dark:ring-neutral-600 dark:hover:bg-neutral-700">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Limpiar
                    </button>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="text-xs font-semibold mb-1 block">Licenciatura</label>
                    <flux:select wire:model.live="licenciatura_id" class="w-full rounded-lg border-neutral-300 dark:bg-neutral-800 dark:border-neutral-700">
                        <option value="">Todas</option>
                        @foreach($licenciaturas as $l)
                            <option value="{{ $l->id }}">{{ $l->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <label class="text-xs font-semibold mb-1 block">Generación</label>
                    <flux:select wire:model.live="generacion_id" class="w-full rounded-lg border-neutral-300 dark:bg-neutral-800 dark:border-neutral-700">
                        <option value="">Todas</option>
                        @foreach($generaciones as $g)
                            <option value="{{ $g->id }}">{{ $g->generacion }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <label class="text-xs font-semibold mb-1 block">Modalidad</label>
                    <flux:select wire:model.live="modalidad_id" class="w-full rounded-lg border-neutral-300 dark:bg-neutral-800 dark:border-neutral-700">
                        <option value="">Todas</option>
                        @foreach($modalidades as $m)
                            <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <label class="text-xs font-semibold mb-1 block">Cuatrimestre</label>
                    <flux:select wire:model.live="cuatrimestre_id" class="w-full rounded-lg border-neutral-300 dark:bg-neutral-800 dark:border-neutral-700">
                        <option value="">Todos</option>
                        @foreach($cuatrimestres as $c)
                            <option value="{{ $c->id }}">{{ $c->cuatrimestre }}°</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </div>

        {{-- Loader superior al recalcular --}}
        <div wire:loading.delay.class.remove="hidden" class="hidden border-t border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center gap-2 px-4 py-3 text-sm">
                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                Actualizando métricas…
            </div>
        </div>
    </div>

    {{-- TARJETA GLOBAL --}}
    <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">Resumen global</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Capturadas (válidas): <b>{{ number_format($capturadas) }}</b> de <b>{{ number_format($esperadas) }}</b>
                    ({{ number_format($porcentaje,1) }}%)
                    · Faltantes: <b>{{ number_format($faltantes) }}</b> ({{ number_format($porcentaje_faltantes,1) }}%)
                    @if(!is_null($promedio)) · Promedio: <span class="font-semibold">{{ number_format($promedio,2) }}</span>@endif
                </p>
            </div>
            <span class="text-xs px-2 py-1 rounded bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200">En tiempo real</span>
        </div>

        {{-- Barras: Avance y Faltantes --}}
        <div class="mt-4 space-y-3">
            {{-- Avance (capturadas) --}}
            <div>
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="font-medium text-neutral-700 dark:text-neutral-300">Avance</span>
                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($porcentaje,1) }}%</span>
                </div>
                <div class="h-3 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                    <div class="h-full bg-gradient-to-r from-sky-500 via-indigo-500 to-fuchsia-600"
                         x-bind:style="'width: {{ $porcentaje }}%'"
                         x-init="$el.style.width='0%'; requestAnimationFrame(()=>{$el.style.width='{{ $porcentaje }}%'})"
                         style="transition: width .7s ease"></div>
                </div>
            </div>

            {{-- Faltantes --}}
            <div>
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="font-medium text-neutral-700 dark:text-neutral-300">Faltantes</span>
                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($porcentaje_faltantes,1) }}%</span>
                </div>
                <div class="h-3 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                    <div class="h-full bg-gradient-to-r from-rose-500 via-amber-500 to-yellow-400"
                         x-bind:style="'width: {{ $porcentaje_faltantes }}%'"
                         x-init="$el.style.width='0%'; requestAnimationFrame(()=>{$el.style.width='{{ $porcentaje_faltantes }}%'})"
                         style="transition: width .7s ease"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRIDS POR GRUPO --}}
    <div class="grid gap-4">
        {{-- LICENCIATURA --}}
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="flex items-center justify-between p-4 sm:p-5">
                <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">Por Licenciatura</h4>
                <button @click="openL=!openL" class="text-sm text-indigo-600 dark:text-indigo-300 hover:underline">Toggle</button>
            </div>
            <div x-show="openL" x-collapse class="px-4 sm:px-5 pb-5">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($porLicenciatura as $card)
                        <div class="rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 bg-white/70 dark:bg-neutral-800/60">
                            <div class="flex items-center justify-between">
                                <span class="font-medium truncate">{{ $card['label'] }}</span>
                                <span class="text-xs text-neutral-500">
                                    {{ $card['capturadas'] }}/{{ $card['esperadas'] }} · faltan {{ $card['faltantes'] }}
                                </span>
                            </div>

                            {{-- Avance --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-300">Avance</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($card['porcentaje'],1) }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600"
                                         style="width: {{ $card['porcentaje'] }}%; transition: width .6s ease"></div>
                                </div>
                            </div>

                            {{-- Faltantes --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-300">Faltantes</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($card['porcentaje_faltantes'],1) }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-rose-500 via-amber-500 to-yellow-400"
                                         style="width: {{ $card['porcentaje_faltantes'] }}%; transition: width .6s ease"></div>
                                </div>
                            </div>

                            <div class="mt-2 text-xs text-neutral-600 dark:text-neutral-300">
                                @if(!is_null($card['promedio'])) Prom: <b>{{ number_format($card['promedio'],2) }}</b>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- GENERACIÓN --}}
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="flex items-center justify-between p-4 sm:p-5">
                <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">Por Generación</h4>
                <button @click="openG=!openG" class="text-sm text-indigo-600 dark:text-indigo-300 hover:underline">Toggle</button>
            </div>
            <div x-show="openG" x-collapse class="px-4 sm:px-5 pb-5">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($porGeneracion as $card)
                        <div class="rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 bg-white/70 dark:bg-neutral-800/60">
                            <div class="flex items-center justify-between">
                                <span class="font-medium truncate">{{ $card['label'] }}</span>
                                <span class="text-xs text-neutral-500">
                                    {{ $card['capturadas'] }}/{{ $card['esperadas'] }} · faltan {{ $card['faltantes'] }}
                                </span>
                            </div>

                            {{-- Avance --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-300">Avance</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($card['porcentaje'],1) }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-600"
                                         style="width: {{ $card['porcentaje'] }}%; transition: width .6s ease"></div>
                                </div>
                            </div>

                            {{-- Faltantes --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-300">Faltantes</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($card['porcentaje_faltantes'],1) }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-rose-500 via-amber-500 to-yellow-400"
                                         style="width: {{ $card['porcentaje_faltantes'] }}%; transition: width .6s ease"></div>
                                </div>
                            </div>

                            <div class="mt-2 text-xs text-neutral-600 dark:text-neutral-300">
                                @if(!is_null($card['promedio'])) Prom: <b>{{ number_format($card['promedio'],2) }}</b>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- MODALIDAD --}}
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="flex items-center justify-between p-4 sm:p-5">
                <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">Por Modalidad</h4>
                <button @click="openM=!openM" class="text-sm text-indigo-600 dark:text-indigo-300 hover:underline">Toggle</button>
            </div>
            <div x-show="openM" x-collapse class="px-4 sm:px-5 pb-5">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($porModalidad as $card)
                        <div class="rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 bg-white/70 dark:bg-neutral-800/60">
                            <div class="flex items-center justify-between">
                                <span class="font-medium truncate">{{ strtoupper($card['label']) }}</span>
                                <span class="text-xs text-neutral-500">
                                    {{ $card['capturadas'] }}/{{ $card['esperadas'] }} · faltan {{ $card['faltantes'] }}
                                </span>
                            </div>

                            {{-- Avance --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-300">Avance</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($card['porcentaje'],1) }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-sky-500 to-indigo-600"
                                         style="width: {{ $card['porcentaje'] }}%; transition: width .6s ease"></div>
                                </div>
                            </div>

                            {{-- Faltantes --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-300">Faltantes</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($card['porcentaje_faltantes'],1) }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-rose-500 via-amber-500 to-yellow-400"
                                         style="width: {{ $card['porcentaje_faltantes'] }}%; transition: width .6s ease"></div>
                                </div>
                            </div>

                            <div class="mt-2 text-xs text-neutral-600 dark:text-neutral-300">
                                @if(!is_null($card['promedio'])) Prom: <b>{{ number_format($card['promedio'],2) }}</b>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- CUATRIMESTRE --}}
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="flex items-center justify-between p-4 sm:p-5">
                <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">Por Cuatrimestre</h4>
                <button @click="openC=!openC" class="text-sm text-indigo-600 dark:text-indigo-300 hover:underline">Toggle</button>
            </div>
            <div x-show="openC" x-collapse class="px-4 sm:px-5 pb-5">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($porCuatrimestre as $card)
                        <div class="rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 bg-white/70 dark:bg-neutral-800/60">
                            <div class="flex items-center justify-between">
                                <span class="font-medium truncate">Cuatri {{ $card['label'] }}°</span>
                                <span class="text-xs text-neutral-500">
                                    {{ $card['capturadas'] }}/{{ $card['esperadas'] }} · faltan {{ $card['faltantes'] }}
                                </span>
                            </div>

                            {{-- Avance --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-300">Avance</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($card['porcentaje'],1) }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-600"
                                         style="width: {{ $card['porcentaje'] }}%; transition: width .6s ease"></div>
                                </div>
                            </div>

                            {{-- Faltantes --}}
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-neutral-600 dark:text-neutral-300">Faltantes</span>
                                    <span class="text-neutral-500 dark:text-neutral-400">{{ number_format($card['porcentaje_faltantes'],1) }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-fuchsia-500 via-pink-600 to-rose-600"
                                         style="width: {{ $card['porcentaje_faltantes'] }}%; transition: width .6s ease"></div>
                                </div>
                            </div>

                            <div class="mt-2 text-xs text-neutral-600 dark:text-neutral-300">
                                @if(!is_null($card['promedio'])) Prom: <b>{{ number_format($card['promedio'],2) }}</b>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
