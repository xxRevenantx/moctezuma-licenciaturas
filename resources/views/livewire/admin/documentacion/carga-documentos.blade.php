<div>
    <div
        x-data="{
            isUploading: false,
            progress: 0,
            fileUrl: null,
            showModal: false,
            showModalConfirmDelete: false,
            guardado: @entangle('guardado'),
            archivoGuardadoUrl: @entangle('archivoGuardadoUrl'),
            // ❗ sin .defer para que Livewire empuje el valor inmediatamente
            nombreArchivo: @entangle('nombreArchivo'),
            tamanoArchivo: @entangle('tamanoArchivo'),
        }"

        x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"

        {{-- 🔔 escucha el evento ESPECÍFICO de este widget --}}
        x-on:archivo-guardado-{{ Str::slug($wireId, '_') }}.window="
            progress = 0;
            isUploading = false;
            // nombre y tamaño vienen desde el backend (BD)
            nombreArchivo = $event.detail.nombre;
            tamanoArchivo = $event.detail.tamano;
            // asegúrate de que el visor muestre el archivo guardado en disco
            fileUrl = archivoGuardadoUrl;
        "

        {{-- cuando se elimina, limpia todo SOLO para este widget --}}
        x-on:archivo-eliminado-{{ Str::slug($wireId, '_') }}.window="
            fileUrl = null;
            progress = 0;
            isUploading = false;
            nombreArchivo = '';
            tamanoArchivo = '';
        "

        x-effect="document.body.style.overflow = showModal || showModalConfirmDelete ? 'hidden' : 'auto'"
        class="border rounded-md p-4 mt-4 shadow-sm bg-white dark:bg-neutral-800 space-y-3"
    >
        <h2 class="text-sm font-semibold text-gray-700 dark:text-white border-b pb-2 mb-2 uppercase">
            {{ strtoupper($label) }} (PDF)
        </h2>

        <div class="flex items-center gap-2">
            <label
                :class="guardado
                    ? 'flex items-center bg-green-700 hover:bg-green-600 text-white font-medium px-4 py-2 rounded cursor-pointer'
                    : 'flex items-center bg-blue-700 hover:bg-blue-600 text-white font-medium px-4 py-2 rounded cursor-pointer'">
                <template x-if="guardado">
                    <span>↻ Reemplazar {{ $label }}</span>
                </template>
                <template x-if="!guardado">
                    <span>📤 Subir {{ $label }}</span>
                </template>

                <input
                    type="file"
                    wire:model="archivo"
                    accept="application/pdf"
                    class="hidden"
                    x-on:change="
                        const file = $event.target.files[0];
                        if (file && file.type === 'application/pdf') {
                            fileUrl = URL.createObjectURL(file);
                            // ⬇️ esto es solo temporal; luego el backend lo pisa con el nombre de BD
                            nombreArchivo = file.name;
                            tamanoArchivo = (file.size / 1024).toFixed(2) + ' KB';
                            guardado = false;
                        } else {
                            fileUrl = null;
                            progress = 0;
                            isUploading = false;
                            alert('El archivo debe ser un PDF válido.');
                            nombreArchivo = '';
                            tamanoArchivo = '';
                            $event.target.value = '';
                        }
                    "
                />
            </label>

            <!-- 📋 Ver PDF temporal -->
            <button
                type="button"
                x-show="fileUrl && !guardado"
                @click="showModal = true"
                class="text-blue-700 hover:text-blue-900 text-xl"
                title="Previsualizar PDF antes de guardar"
            >📋</button>

            <!-- 📋 Ver PDF guardado -->
            <button
                type="button"
                x-show="guardado && archivoGuardadoUrl"
                @click="fileUrl = archivoGuardadoUrl; showModal = true"
                class="text-green-700 hover:text-green-900 text-xl"
                title="Ver documento guardado"
            >📋</button>

            <!-- 🗑️ Eliminar -->
            <button
                type="button"
                x-show="guardado && archivoGuardadoUrl"
                @click="showModalConfirmDelete = true"
                class="text-red-600 hover:text-red-800 text-xl"
                title="Eliminar archivo"
            >🗑️</button>
        </div>

        <!-- Nombre / tamaño -->
        <div class="ml-1 mt-1 text-xs text-gray-500 space-y-1">
            <template x-if="nombreArchivo">
                <p x-text="'Archivo: ' + nombreArchivo"></p>
            </template>

            <template x-if="tamanoArchivo">
                <p x-text="'Tamaño: ' + tamanoArchivo"></p>
            </template>

            <template x-if="!archivoGuardadoUrl && !fileUrl">
                <p class="text-red-500">No se ha subido ningún archivo.</p>
            </template>
        </div>

        <!-- Barra de carga -->
        <div class="w-full h-2 bg-gray-200 dark:bg-neutral-700 rounded overflow-hidden mt-2" x-show="progress > 0">
            <div class="h-2 bg-blue-500 transition-all duration-300" :style="'width: ' + progress + '%'"></div>
        </div>

        <!-- Guardar -->
        <button
            x-show="progress === 100 && fileUrl && !guardado"
            x-transition
            wire:click="guardarArchivo"
            wire:loading.attr="disabled"
            wire:target="guardarArchivo"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded mt-2">
            Guardar {{ $label }}
        </button>

        @error('archivo')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        @if ($mensaje)
            <p class="text-sm text-green-700 font-semibold">{{ $mensaje }}</p>
        @endif

        <!-- Modal visor PDF -->
        <div
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 overflow-hidden"
            x-show="showModal"
            @keydown.escape.window="showModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div
                class="bg-white w-full max-w-3xl h-[80vh] rounded shadow-lg relative transform transition-all"
                @click.outside="showModal = false">
                <button @click="showModal = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl font-bold">&times;</button>
                <template x-if="fileUrl">
                    <iframe :src="fileUrl" class="w-full h-full rounded-b"></iframe>
                </template>
            </div>
        </div>

        <!-- Modal Confirmación Eliminar -->
        <div
            class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 overflow-hidden"
            x-show="showModalConfirmDelete"
            @keydown.escape.window="showModalConfirmDelete = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6 text-center relative" @click.outside="showModalConfirmDelete = false">
                <h2 class="text-lg font-semibold text-gray-800">¿Eliminar documento?</h2>
                <p class="text-sm text-gray-600 mt-2">Esta acción no se puede deshacer.</p>
                <div class="mt-6 flex justify-center gap-4">
                    <button @click="showModalConfirmDelete = false" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded text-sm font-medium text-gray-800">
                        Cancelar
                    </button>
                    <button @click="showModalConfirmDelete = false; $wire.eliminarArchivo()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
