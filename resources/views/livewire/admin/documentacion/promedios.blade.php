<div class="space-y-5">
    <div
        class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-5">
            <h2 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                Lista de promedios
            </h2>

            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Selecciona una licenciatura y una generación para generar el PDF con los promedios de los alumnos.
            </p>
        </div>

        <form action="{{ route('admin.pdf.documentacion.promedios') }}" method="GET" target="_blank"
            class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <flux:select wire:model.live="licenciatura_id" name="licenciatura_id" label="Licenciatura" class="w-full"
                required>
                <flux:select.option value="">Selecciona una licenciatura</flux:select.option>

                @foreach ($licenciaturas as $licenciatura)
                    <flux:select.option value="{{ $licenciatura->id }}">
                        {{ $licenciatura->nombre }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="generacion_id" name="generacion_id" label="Generación" class="w-full"
                :disabled="!$licenciatura_id" required>
                <flux:select.option value="">Selecciona una generación</flux:select.option>

                @foreach ($generaciones as $generacion)
                    <flux:select.option value="{{ $generacion->id }}">
                        {{ $generacion->generacion }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex items-end">
                <flux:button type="submit" variant="primary" class="w-full bg-red-600 text-white hover:bg-red-700"
                    :disabled="!$licenciatura_id || !$generacion_id">
                    Generar PDF
                </flux:button>
            </div>
        </form>
    </div>
</div>
