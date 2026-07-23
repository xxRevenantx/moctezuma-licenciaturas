<div
    x-data="{
        visor: false,
        confirmarEliminacion: false,
        progreso: 0,
        subiendo: false
    }"
    x-on:livewire-upload-start="subiendo = true; progreso = 0"
    x-on:livewire-upload-progress="progreso = $event.detail.progress"
    x-on:livewire-upload-finish="subiendo = false; progreso = 100"
    x-on:livewire-upload-error="subiendo = false; progreso = 0"
    class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
>
    <div class="h-1 bg-gradient-to-r from-[#006492] to-[#88AC2E]"></div>

    <div class="p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#006492]/10 text-[#006492] dark:bg-[#006492]/20 dark:text-sky-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5V5.625A3.375 3.375 0 0011.25 2.25h-4.5A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25v-5.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 2.25V6a2.25 2.25 0 002.25 2.25H19.5"/></svg>
                </span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-semibold uppercase tracking-wide text-slate-900 dark:text-white">{{ $label }}</h3>
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $obligatorio ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-neutral-800 dark:text-neutral-300' }}">
                            {{ $obligatorio ? 'Obligatorio' : 'Opcional' }}
                        </span>
                        @if ($organizacionPendiente)
                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-700 dark:bg-sky-950/40 dark:text-sky-300">Organización pendiente</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">PDF, JPG o PNG · máximo {{ $maxMb }} MB. Puede contener una o varias páginas y documentos combinados.</p>
                </div>
            </div>

            @if ($inconsistente)
                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">Archivo faltante</span>
            @elseif ($guardado)
                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Entregado</span>
            @else
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">Pendiente</span>
            @endif
        </div>

        @if ($guardado)
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                <p class="truncate text-sm font-medium text-emerald-900 dark:text-emerald-100" title="{{ $nombreArchivo }}">{{ $nombreArchivo }}</p>
                <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">
                    {{ $tamanoArchivo }}
                    @if ($paginasDocumento > 0) · {{ $paginasDocumento }} página(s) @endif
                    · documento confirmado
                </p>
            </div>
        @elseif ($inconsistente)
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/20 dark:text-rose-200">
                La base de datos tenía una referencia, pero el archivo físico no existe. Sube una nueva copia y ejecuta la auditoría documental.
            </div>
        @endif

        @if ($organizacionPendiente)
            <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 p-3 text-xs text-sky-800 dark:border-sky-900/60 dark:bg-sky-950/20 dark:text-sky-200">
                Hay cambios guardados como borrador. El documento actual seguirá utilizándose hasta confirmar la organización de páginas.
            </div>
        @endif

        <div class="mt-4 flex flex-wrap gap-2">
            @if ($guardado && $archivoGuardadoUrl)
                @can('documentos-identidad.ver')
                    <button type="button" @click="visor = true" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Ver documento
                    </button>
                @endcan

                @can('documentos-identidad.descargar')
                    <a href="{{ $archivoDescargaUrl }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 10.5L12 15m0 0l4.5-4.5M12 15V3"/></svg>
                        Descargar documento
                    </a>
                @endcan
            @endif

            @if ($tieneFuentes)
                @canany(['documentos-identidad.subir', 'documentos-identidad.reemplazar'])
                    <button type="button" wire:click="abrirOrganizador" class="inline-flex items-center gap-2 rounded-xl border border-[#006492]/30 bg-[#006492]/5 px-3 py-2 text-sm font-semibold text-[#006492] transition hover:bg-[#006492]/10 dark:border-sky-800 dark:text-sky-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 6.75h15M4.5 12h15m-15 5.25h15"/></svg>
                        Organizar páginas
                    </button>
                @endcanany
            @endif

            @can($guardado ? 'documentos-identidad.reemplazar' : 'documentos-identidad.subir')
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-[#006492] px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-[#00547b]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5M3.75 15v3.75A2.25 2.25 0 006 21h12a2.25 2.25 0 002.25-2.25V15"/></svg>
                    {{ $guardado ? 'Agregar o reemplazar' : 'Subir archivo' }}
                    <input wire:model="archivo" type="file" accept="application/pdf,image/jpeg,image/png,.pdf,.jpg,.jpeg,.png" class="sr-only">
                </label>
            @endcan

            @if ($guardado)
                @can('documentos-identidad.eliminar')
                    <button type="button" @click="confirmarEliminacion = true" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50 dark:border-rose-900/60 dark:text-rose-300 dark:hover:bg-rose-950/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21H8.084a2.25 2.25 0 01-2.244-1.327L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0V4.477c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        Retirar
                    </button>
                @endcan
            @endif
        </div>

        @if ($fuentesOriginales !== [])
            <details class="mt-4 rounded-xl border border-slate-200 dark:border-neutral-700">
                <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-700 dark:text-neutral-200">Archivos originales utilizados ({{ count($fuentesOriginales) }})</summary>
                <div class="space-y-2 border-t border-slate-200 p-3 dark:border-neutral-700">
                    @foreach ($fuentesOriginales as $fuente)
                        <div class="flex flex-col gap-2 rounded-lg bg-slate-50 p-3 text-xs dark:bg-neutral-800/60 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800 dark:text-neutral-100" title="{{ $fuente['nombre'] }}">{{ $fuente['nombre'] }}</p>
                                <p class="mt-0.5 text-slate-500 dark:text-neutral-400">Páginas usadas: {{ implode(', ', $fuente['paginas']) }} de {{ $fuente['total_paginas'] }}</p>
                            </div>
                            @can('documentos-identidad.descargar')
                                <a href="{{ $fuente['url'] }}" class="shrink-0 font-semibold text-[#006492] hover:underline dark:text-sky-300">Descargar original</a>
                            @endcan
                        </div>
                    @endforeach
                </div>
            </details>
        @endif

        <div x-show="subiendo" x-cloak class="mt-4">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-neutral-400"><span>Subiendo y validando…</span><span x-text="progreso + '%'">0%</span></div>
            <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-700"><div class="h-full rounded-full bg-[#006492] transition-all" :style="`width:${progreso}%`"></div></div>
        </div>

        @if ($requiereConfirmacion)
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/20">
                <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">¿Cómo deseas integrar el nuevo archivo?</p>
                <p class="mt-1 text-xs text-amber-800 dark:text-amber-200">
                    El archivo tiene {{ $archivoPaginas }} página(s). Puedes sustituir las páginas actuales de {{ $label }} o agregarlas al final. Los archivos anteriores permanecen como respaldo privado.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button wire:click="guardarArchivo('reemplazar')" wire:loading.attr="disabled" wire:target="guardarArchivo" class="rounded-xl bg-amber-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-60">Reemplazar documento</button>
                    <button wire:click="guardarArchivo('agregar')" wire:loading.attr="disabled" wire:target="guardarArchivo" class="rounded-xl bg-[#006492] px-3.5 py-2 text-sm font-semibold text-white hover:bg-[#00547b] disabled:opacity-60">Agregar páginas</button>
                    <button wire:click="cancelarReemplazo" class="rounded-xl border border-amber-300 px-3.5 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100 dark:border-amber-800 dark:text-amber-200 dark:hover:bg-amber-950/40">Cancelar</button>
                </div>
            </div>
        @endif

        @error('archivo')
            <p class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">{{ $message }}</p>
        @enderror

        @if ($mensaje)
            <p class="mt-3 rounded-xl bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">{{ $mensaje }}</p>
        @endif

        @if ($historial !== [])
            <details class="mt-4 rounded-xl border border-slate-200 dark:border-neutral-700">
                <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-700 dark:text-neutral-200">Historial de versiones ({{ count($historial) }})</summary>
                <div class="border-t border-slate-200 px-4 py-3 dark:border-neutral-700">
                    <div class="space-y-2">
                        @foreach ($historial as $version)
                            <div class="flex flex-col gap-2 rounded-lg bg-slate-50 p-3 text-xs dark:bg-neutral-800/60 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-neutral-100">Versión {{ $version['version'] }} · {{ ucfirst($version['estado']) }}</p>
                                    <p class="mt-0.5 text-slate-500 dark:text-neutral-400">
                                        {{ $version['fecha'] }} · {{ $version['usuario'] }} · {{ $version['tamano'] }}
                                        @if ($version['paginas'] > 0) · {{ $version['paginas'] }} pág. @endif
                                    </p>
                                </div>
                                @if ($version['url'])
                                    @can('documentos-identidad.ver')
                                        <a href="{{ $version['url'] }}" target="_blank" class="font-semibold text-[#006492] hover:underline dark:text-sky-300">Consultar</a>
                                    @endcan
                                @else
                                    <span class="font-medium text-rose-600 dark:text-rose-300">Archivo no disponible</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </details>
        @endif
    </div>

    <div wire:loading.flex wire:target="archivo,guardarArchivo,eliminarArchivo,abrirOrganizador" class="absolute inset-0 z-20 items-center justify-center bg-white/75 backdrop-blur-sm dark:bg-neutral-900/75">
        <div class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-lg ring-1 ring-slate-200 dark:bg-neutral-900 dark:text-neutral-200 dark:ring-neutral-700">
            <svg class="h-5 w-5 animate-spin text-[#006492]" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            Procesando documento…
        </div>
    </div>

    @if ($archivoGuardadoUrl)
        <div x-cloak x-show="visor" @keydown.escape.window="visor = false" class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div @click.outside="visor = false" class="relative h-[86vh] w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
                <div class="flex h-14 items-center justify-between border-b border-slate-200 px-4 dark:border-neutral-700">
                    <p class="truncate pr-4 text-sm font-semibold text-slate-900 dark:text-white">{{ $label }} · {{ $nombreArchivo }}</p>
                    <button type="button" @click="visor = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-neutral-300 dark:hover:bg-neutral-800">✕</button>
                </div>
                <iframe src="{{ $archivoGuardadoUrl }}" class="h-[calc(86vh-3.5rem)] w-full" title="Vista previa de {{ $label }}"></iframe>
            </div>
        </div>
    @endif

    <div x-cloak x-show="confirmarEliminacion" @keydown.escape.window="confirmarEliminacion = false" class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm">
        <div @click.outside="confirmarEliminacion = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-neutral-900">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">¿Retirar {{ $label }}?</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-neutral-300">Dejará de contar como entregado. Las páginas pasarán a “Sin clasificar” y los archivos originales se conservarán para auditoría o una reorganización posterior.</p>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="confirmarEliminacion = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Cancelar</button>
                <button type="button" @click="confirmarEliminacion = false; $wire.eliminarArchivo()" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Sí, retirar</button>
            </div>
        </div>
    </div>
</div>
