<div
    x-data="{
        isUploading: false,
        progress: 0,
        fileUrl: null,
        showModal: false,
        showModalConfirmDelete: false,
        guardado: @entangle('guardado'),
        archivoGuardadoUrl: @entangle('archivoGuardadoUrl'),
        nombreArchivo: @entangle('nombreArchivo'),
        tamanoArchivo: @entangle('tamanoArchivo'),
    }"
    x-on:livewire-upload-start="isUploading = true"
    x-on:livewire-upload-finish="isUploading = false"
    x-on:livewire-upload-error="isUploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    x-on:archivo-guardado-{{ Str::slug($wireId, '_') }}.window="
        progress = 0;
        isUploading = false;
        nombreArchivo = $event.detail.nombre;
        tamanoArchivo = $event.detail.tamano;
        fileUrl = archivoGuardadoUrl;
        guardado = true;
    "
    x-on:archivo-eliminado-{{ Str::slug($wireId, '_') }}.window="
        fileUrl = null; progress = 0; isUploading = false;
        nombreArchivo = ''; tamanoArchivo = ''; guardado = false;
    "
    class="relative rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow overflow-hidden"
    aria-live="polite"
>
    <!-- Accent -->
    <div class="h-1 w-full bg-gradient-to-r from-indigo-600 via-violet-500 to-fuchsia-500"></div>

    <div class="p-4 sm:p-6 space-y-4">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 grid place-items-center rounded-xl bg-indigo-50 dark:bg-indigo-900/30 ring-1 ring-indigo-100 dark:ring-indigo-800">
                    <!-- PDF icon -->
                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none">
                        <path stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                              d="M7 21h10a2 2 0 0 0 2-2V8.5L14.5 3H7a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2Z"/>
                        <path stroke="currentColor" stroke-width="1.6" d="M14.5 3V8h4.5"/>
                        <rect x="8" y="12.5" width="8" height="1.5" rx=".75" fill="currentColor"/>
                        <rect x="8" y="15.5" width="6" height="1.5" rx=".75" fill="currentColor"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">
                        {{ strtoupper($label) }} <span class="font-normal text-xs">(PDF)</span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Sube, previsualiza, reemplaza o elimina el documento.
                    </p>
                </div>
            </div>

            <!-- Estado -->
            <span
                x-show="guardado"
                class="inline-flex items-center gap-1 rounded-full border border-emerald-300/60 bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/50"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Guardado
            </span>
            <span
                x-show="!guardado"
                class="inline-flex items-center gap-1 rounded-full border border-amber-300/60 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/50"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Sin guardar
            </span>
        </div>

        <!-- Uploader -->
        <div class="flex flex-wrap items-center gap-2">
            <label
                :class="guardado
                    ? 'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white shadow cursor-pointer transition'
                    : 'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white shadow cursor-pointer transition'"
                title="Seleccionar PDF"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    <path d="M12 5v14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                <span x-text="guardado ? 'Reemplazar {{ $label }}' : 'Subir {{ $label }}'"></span>

                <input
                    type="file"
                    wire:model="archivo"
                    accept="application/pdf"
                    class="hidden"
                    x-on:change="
                        const file = $event.target.files[0];
                        if (file && file.type === 'application/pdf') {
                            fileUrl = URL.createObjectURL(file);
                            nombreArchivo = file.name;
                            tamanoArchivo = (file.size/1024).toFixed(2) + ' KB';
                            guardado = false;
                        } else {
                            fileUrl = null; progress = 0; isUploading = false;
                            alert('El archivo debe ser un PDF válido.');
                            nombreArchivo = ''; tamanoArchivo = ''; $event.target.value = '';
                        }
                    "
                />
            </label>

            <!-- Ver PDF temporal -->
            <button
                type="button"
                x-show="fileUrl && !guardado"
                @click="showModal = true"
                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 ring-1 ring-gray-200 dark:ring-neutral-700 text-indigo-700 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-neutral-800 transition"
                title="Previsualizar PDF antes de guardar"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5c7 0 10 7 10 7s-3 7-10 7S2 12 2 12s3-7 10-7Zm0 3.5A3.5 3.5 0 1 0 12 18a3.5 3.5 0 0 0 0-7Z"/></svg>
                Vista previa
            </button>

            <!-- Ver PDF guardado -->
            <button
                type="button"
                x-show="guardado && archivoGuardadoUrl"
                @click="fileUrl = archivoGuardadoUrl; showModal = true"
                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 ring-1 ring-gray-200 dark:ring-neutral-700 text-emerald-700 hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-neutral-800 transition"
                title="Ver documento guardado"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5c7 0 10 7 10 7s-3 7-10 7S2 12 2 12s3-7 10-7Zm0 3.5A3.5 3.5 0 1 0 12 18a3.5 3.5 0 0 0 0-7Z"/></svg>
                Ver guardado
            </button>

            <!-- Eliminar -->
            <button
                type="button"
                x-show="guardado && archivoGuardadoUrl"
                @click="showModalConfirmDelete = true"
                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 ring-1 ring-gray-200 dark:ring-neutral-700 text-rose-700 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-neutral-800 transition"
                title="Eliminar archivo"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l1 2h5v2H3V5h5l1-2Zm1 7h2v7h-2v-7Zm4 0h2v7h-2v-7ZM6 10h2v7H6v-7Z"/></svg>
                Eliminar
            </button>
        </div>

        <!-- Nombre / tamaño -->
        <div class="text-xs text-gray-600 dark:text-gray-300 space-y-1">
            <template x-if="nombreArchivo">
                <p><span class="font-medium">Archivo:</span> <span class="font-mono" x-text="nombreArchivo"></span></p>
            </template>
            <template x-if="tamanoArchivo">
                <p><span class="font-medium">Tamaño:</span> <span class="font-mono" x-text="tamanoArchivo"></span></p>
            </template>
            <template x-if="!archivoGuardadoUrl && !fileUrl">
                <p class="text-rose-500">No se ha subido ningún archivo.</p>
            </template>
        </div>

        <!-- Progreso -->
        <div x-show="progress > 0" class="space-y-1" role="status" aria-atomic="true" aria-busy="true">
            <div class="w-full h-2 rounded-full bg-gray-200 dark:bg-neutral-700 overflow-hidden">
                <div class="h-2 bg-indigo-600 transition-all duration-300" :style="'width:'+progress+'%'"></div>
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400" x-text="`Progreso: ${progress}%`"></div>
        </div>

        <!-- Guardar -->
        <div class="pt-1">
            <button
                x-show="progress === 100 && fileUrl && !guardado"
                x-transition
                wire:click="guardarArchivo"
                wire:loading.attr="disabled"
                wire:target="guardarArchivo"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white shadow cursor-pointer"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M5 12h14M12 5v14"/></svg>
                Guardar {{ $label }}
            </button>
        </div>

        @error('archivo')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        @if ($mensaje)
            <p class="text-sm font-medium text-emerald-600">{{ $mensaje }}</p>
        @endif
    </div>

    <!-- Overlay de carga general (opcional) -->
    <div
        x-show="isUploading"
        x-transition.opacity
        class="pointer-events-none absolute inset-0 grid place-items-center bg-white/60 dark:bg-neutral-900/60"
    >
        <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-gray-200 dark:ring-neutral-800 shadow">
            <svg class="h-5 w-5 animate-spin text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span class="text-sm text-gray-800 dark:text-gray-200">Subiendo…</span>
        </div>
    </div>

    <!-- Modal visor PDF -->
    <div
        x-show="showModal"
        x-trap.noscroll="showModal"
        @keydown.escape.window="showModal = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center"
        aria-modal="true" role="dialog"
    >
        <div class="absolute inset-0 bg-neutral-900/70 backdrop-blur-sm" @click="showModal = false"></div>

        <div
            class="relative w-[92vw] sm:w-[88vw] md:w-[80vw] max-w-5xl h-[78vh] bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        >
            <div class="h-1 w-full bg-gradient-to-r from-indigo-600 via-sky-400 to-indigo-600"></div>
            <div class="absolute top-2 right-2">
                <button
                    @click="showModal = false"
                    class="inline-flex items-center justify-center rounded-xl p-2 text-neutral-500 hover:text-neutral-800 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    aria-label="Cerrar"
                >
                    &times;
                </button>
            </div>
            <template x-if="fileUrl">
                <iframe :src="fileUrl" class="w-full h-full"></iframe>
            </template>
        </div>
    </div>

    <!-- Modal Confirmación Eliminar -->
    <div
        x-show="showModalConfirmDelete"
        x-trap.noscroll="showModalConfirmDelete"
        @keydown.escape.window="showModalConfirmDelete = false"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center"
        aria-modal="true" role="dialog"
    >
        <div class="absolute inset-0 bg-neutral-900/60 backdrop-blur-sm" @click="showModalConfirmDelete = false"></div>

        <div
            class="relative w-[92vw] sm:w-[88vw] md:w-[480px] bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
            x-show="showModalConfirmDelete"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        >
            <div class="h-1 w-full bg-gradient-to-r from-rose-600 via-pink-500 to-rose-600"></div>
            <div class="p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">¿Eliminar documento?</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Esta acción no se puede deshacer.</p>

                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-2">
                    <button
                        type="button"
                        @click="showModalConfirmDelete = false"
                        class="inline-flex justify-center rounded-xl px-4 py-2.5 border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-300 dark:focus:ring-offset-neutral-900"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        @click="showModalConfirmDelete = false; $wire.eliminarArchivo()"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white shadow"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3h6l1 2h5v2H3V5h5l1-2Zm1 7h2v7h-2v-7Zm4 0h2v7h-2v-7ZM6 10h2v7H6v-7Z"/></svg>
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
