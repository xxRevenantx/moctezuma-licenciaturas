<div class="space-y-5">
    <div
        class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-5">
            <h2 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                Lista de promedios
            </h2>

            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Selecciona una licenciatura y una generación para generar el reporte de promedios en PDF o Word.
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

            <div class="flex flex-wrap items-end gap-2">
                <button type="submit"
                    formaction="{{ route('admin.pdf.documentacion.promedios') }}"
                    @disabled(!$licenciatura_id || !$generacion_id)
                    class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                    PDF
                </button>
                <button type="submit"
                    formaction="{{ route('admin.word.documentacion.promedios') }}"
                    @disabled(!$licenciatura_id || !$generacion_id)
                    class="flex-1 rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#00547b] disabled:cursor-not-allowed disabled:opacity-50">
                    Word
                </button>
            </div>
        </form>
    </div>
</div>
