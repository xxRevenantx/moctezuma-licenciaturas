<div>
  <form action="{{ route('admin.pdf.documentacion.documento_expedicion') }}"
        method="GET"
        target="_blank"
        class="space-y-4">




    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      {{-- Licenciatura --}}
      <flux:select wire:model.live="licenciatura_id"
                   name="licenciatura"
                   label="Selecciona la licenciatura"
                   class="w-full"
                   required>
        <flux:select.option value="">Selecciona una licenciatura</flux:select.option>
        @foreach($licenciaturas as $lic)
          <flux:select.option value="{{ $lic->id }}">{{ $lic->nombre }}</flux:select.option>
        @endforeach
      </flux:select>

      {{-- Generación --}}
      <flux:select wire:model.live="generacion_id"
                   name="generacion"
                   label="Selecciona la generación"
                   class="w-full"
                   required>
        <flux:select.option value="">Selecciona una generación</flux:select.option>
        @foreach($generaciones as $gen)
          <flux:select.option value="{{ $gen->id }}">{{ $gen->generacion }}</flux:select.option>
        @endforeach
      </flux:select>

      {{-- ========================= --}}
      {{-- A) MULTI con flux:select  --}}
      {{-- ========================= --}}
      @php
        $multiFluxDisponible = true; // si tu flux soporta multiple; si no, usa el fallback B
      @endphp

      @if($multiFluxDisponible)
        <flux:select
            wire:model.live="alumno_ids"
            name="alumno_ids[]"
            label="Selecciona alumnos (múltiple)"
            class="w-full"
            multiple
            searchable
            placeholder="Elige uno o más alumnos"
        >
          @foreach($alumnos as $al)
            <flux:select.option value="{{ $al->id }}">
              {{ $al->nombre }} {{ $al->apellido_paterno }} {{ $al->apellido_materno }}
            </flux:select.option>
          @endforeach
        </flux:select>
      @else
      {{-- ========================= --}}
      {{-- B) Fallback nativo       --}}
      {{-- ========================= --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">
            Selecciona alumnos (múltiple)
          </label>
          <select
              wire:model.live="alumno_ids"
              name="alumno_ids[]"
              class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-neutral-900"
              multiple
              size="6"
          >
            @foreach($alumnos as $al)
              <option value="{{ $al->id }}">
                {{ $al->nombre }} {{ $al->apellido_paterno }} {{ $al->apellido_materno }}
              </option>
            @endforeach
          </select>
          <p class="text-xs text-slate-500 mt-1">Tip: Ctrl/Cmd + clic para selección múltiple.</p>
        </div>
      @endif

      {{-- Tipo de documento --}}
      <flux:select name="documento"
                   label="Selecciona el tipo de documento"
                   class="w-full"
                   required>
        <flux:select.option value="">Selecciona un tipo de documento</flux:select.option>
        <flux:select.option value="registro-escolaridad">Registro de Escolaridad</flux:select.option>
        <flux:select.option value="acta-resultados">Acta de Resultados</flux:select.option>
      </flux:select>

      <div class="flex md:items-end gap-2 md:mt-6">
        <flux:button type="button" variant="ghost" class="w-full md:w-auto" wire:click="seleccionarTodosAlumnos">
          Seleccionar todos
        </flux:button>
        <flux:button type="button" variant="ghost" class="w-full md:w-auto" wire:click="limpiarSeleccion">
          Limpiar selección
        </flux:button>
        <flux:button label="Descargar" type="submit" variant="primary" class="w-full md:w-auto">
          Descargar
        </flux:button>
      </div>
    </div>

    {{-- Campos ocultos útiles para el back (IDs de filtros) --}}
    <input type="hidden" name="licenciatura_id" value="{{ $licenciatura_id }}">
    <input type="hidden" name="generacion_id"   value="{{ $generacion_id }}">

    {{-- IMPORTANTE: enviar cada alumno seleccionado como alumno_ids[] --}}
    @foreach($alumno_ids as $aid)
      <input type="hidden" name="alumno_ids[]" value="{{ $aid }}">
    @endforeach
  </form>
</div>
