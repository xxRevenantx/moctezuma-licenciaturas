<div>
   <form wire:submit.prevent="guardarTitulo" class="space-y-6">
  {{-- Encabezado --}}
  <div class="rounded-2xl border bg-white dark:bg-neutral-900 p-5">
    <h2 class="text-base font-semibold">Registro de Título</h2>
    <p class="text-sm text-neutral-500 dark:text-neutral-400">Completa la información según el formato oficial.</p>
  </div>

  {{-- Relación principal --}}
  <div class="rounded-2xl border bg-white dark:bg-neutral-900 p-5 space-y-4">
    <h3 class="font-semibold text-sm">Alumno</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <flux:select
        wire:model.live="form.alumno_id"
        :label="__('Alumno')"
        placeholder="Selecciona un alumno"
        searchable
      >
        @foreach($alumnos as $al)
          <flux:select.option value="{{ $al->id }}">{{ $al->matricula }} — {{ $al->nombre }} {{$al->apellido_paterno}} {{$al->apellido_materno}} </flux:select.option>
        @endforeach
      </flux:select>

    </div>
  </div>

  {{-- Texto visible en documento --}}
  <div class="rounded-2xl border bg-white dark:bg-neutral-900 p-5 space-y-4">
    <h3 class="font-semibold text-sm">Datos del título</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <flux:input
        wire:model.live="form.grado_titulo"
        :label="__('Grado/Título')"
        placeholder="LICENCIATURA EN …"
      />
    </div>
  </div>

  {{-- Fundamento legal --}}
  <div class="rounded-2xl border bg-white dark:bg-neutral-900 p-5 space-y-4">
    <h3 class="font-semibold text-sm">Fundamento legal</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <flux:input
        wire:model.live="form.acuerdo_numero"
        :label="__('Acuerdo No.')"
        placeholder="Ej. 123/2021"
      />
      <flux:input
        wire:model.live="form.acuerdo_fecha"
        type="date"
        :label="__('Fecha del acuerdo')"
      />
      <flux:input
        wire:model.live="form.examen_fecha"
        type="date"
        :label="__('Fecha del examen')"
      />
    </div>
  </div>

  {{-- Expedición del título --}}
  <div class="rounded-2xl border bg-white dark:bg-neutral-900 p-5 space-y-4">
    <h3 class="font-semibold text-sm">Expedición del título</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <flux:input
        wire:model.live="form.expedido_en"
        :label="__('Expedido en')"
        placeholder="Ciudad Altamirano, Guerrero"
      />
      <flux:input
        wire:model.live="form.expedicion_fecha"
        type="date"
        :label="__('Fecha de expedición')"
      />
    </div>
  </div>

  {{-- Registro y certificación --}}
  <div class="rounded-2xl border bg-white dark:bg-neutral-900 p-5 space-y-4">
    <h3 class="font-semibold text-sm">Departamento de Registro y Certificación</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <flux:input wire:model.live="form.registro" :label="__('Registro')" />
      <flux:input wire:model.live="form.libro" :label="__('Libro')" />
      <flux:input wire:model.live="form.foja" :label="__('Foja')" />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <flux:input
        wire:model.live="lugar_registro"
        :label="__('Lugar de registro')"
        placeholder="Chilpancingo de los Bravo, Guerrero"
      />
      <flux:input
        wire:model.live="form.registro_fecha"
        type="date"
        :label="__('Fecha de registro')"
      />
    </div>
  </div>

  {{-- Datos de Escuela --}}
  <div class="rounded-2xl border bg-white dark:bg-neutral-900 p-5 space-y-4">
    <h3 class="font-semibold text-sm">Datos de la Escuela</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <flux:input wire:model.live="form.plan_estudios" :label="__('Plan de estudios')" />
      <flux:input
        wire:model.live="form.anio_egreso"
        :label="__('Año de egreso')"
        type="number"
        min="1950"
        max="{{ date('Y') + 1 }}"
        inputmode="numeric"
      />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <flux:input wire:model.live="form.acta_examen" :label="__('Acta de examen')" placeholder="Tipo/Nombre del acta" />
      <flux:input wire:model.live="form.acta_numero" :label="__('Número de acta')" />
      <flux:input wire:model.live="form.acta_fecha" type="date" :label="__('Fecha de acta')" />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <flux:input wire:model.live="form.acta_expedida_en" :label="__('Acta expedida en')" />
      <flux:input wire:model.live="form.titulo_numero" :label="__('Título número')" />
    </div>
  </div>

  {{-- Control y estado --}}
  <div class="rounded-2xl border bg-white dark:bg-neutral-900 p-5 space-y-4">
    <h3 class="font-semibold text-sm">Control</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <flux:input
        wire:model.live="form.folio_cadena"
        :label="__('Folio (cadena)')"
        placeholder="Opcional hasta emitir"
      />
      <flux:select wire:model.live="form.estatus" :label="__('Estatus')">
        <flux:select.option value="borrador">Borrador</flux:select.option>
        <flux:select.option value="emitido">Emitido</flux:select.option>
        <flux:select.option value="cancelado">Cancelado</flux:select.option>
      </flux:select>
    </div>
  </div>

  {{-- Acciones --}}
  <div class="flex items-center justify-end gap-3">
    <flux:button variant="primary" type="button" >Cancelar</flux:button>
    <flux:button type="submit">Guardar</flux:button>
  </div>
</form>

</div>
