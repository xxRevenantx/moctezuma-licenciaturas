<div>
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Crear Licenciaturas</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para crear licenciaturas.</p>
        </div>
<form wire:submit.prevent="guardarLicenciatura">
        <flux:field>

        <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

            <div class="w-120 border-2 border-gray-100 bg-white dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                <flux:input wire:model.live="imagen" :label="__('Imagen de la licenciatura')" type="file" accept="image/jpeg,image/jpg,image/png" />

                @if ($imagen)
                <div class="mt-4 flex flex-col items-center justify-center">
                    <img src="{{ $imagen->temporaryUrl() }}" alt="{{ __('Profile Preview') }}" class="w-20 h-20 rounded-full">
                </div>
            {{-- @elseif ($photoUrl)
                <div class="mt-4">
                    <img src="{{ asset('storage/profile-photos/'. $photoUrl) }}" alt="{{ __('Current Profile Image') }}" class="w-20 h-20 rounded-full">
                    <flux:button wire:click="eliminarFoto" class="mt-2" variant="danger">Eliminar foto</flux:button>

                </div> --}}
            @endif

                <flux:input wire:model.live="nombre" :label="__('Licenciatura')" type="text" placeholder="Nombre de la licenciatura"  autofocus autocomplete="nombre" />
                <flux:input wire:model.live="slug" readonly :label="__('Url')" type="text" placeholder="Url"  autofocus autocomplete="slug" />
                <flux:input wire:model.live="nombre_corto" :label="__('Nombre corto')" type="text" placeholder="Nombre corto"  autofocus autocomplete="nombre_corto" />
                <flux:input wire:model.live="RVOE" :label="__('RVOE')" type="text" placeholder="RVOE"  autofocus autocomplete="RVOE" />

                <div class="flex items-center gap-4 mt-3">
                    <div class="flex items-center">
                        <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
                    </div>
                </div>
            </div>



        </div>


        </flux:field>

    </form>

    @include('components.toast-message')
</div>



{{-- @push('scripts')

    <script>
    document.addEventListener('livewire:navigated', () => {
    const uploadArea = document.getElementById("uploadArea");
    const fileInput = document.getElementById("fileInput");
    const preview = document.getElementById("preview");

    uploadArea.addEventListener("dragover", (e) => {
    e.preventDefault();
    uploadArea.style.backgroundColor = "#e9ecef";
    });

    uploadArea.addEventListener("dragleave", () => {
    uploadArea.style.backgroundColor = "transparent";
    });

    uploadArea.addEventListener("drop", (e) => {
    e.preventDefault();
    uploadArea.style.backgroundColor = "transparent";
    const file = e.dataTransfer.files[0];
    if (file) {
        showPreview(file);
    }
    });

    fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (file) {
        showPreview(file);
    }
    });

    function showPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.src = e.target.result;
        preview.classList.remove("d-none");
    };
    reader.readAsDataURL(file);
    }
    })

    </script>


@endpush --}}
