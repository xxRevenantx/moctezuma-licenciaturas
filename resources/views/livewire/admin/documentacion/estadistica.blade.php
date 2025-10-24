<div class="grid grid-cols-1 md:grid-cols-4 gap-4">

          <flux:select wire:model="filtrar_licenciatura" name="licenciatura" label="Selecciona la licenciatura" class="w-full" required>
            <flux:select.option value="">Selecciona una licenciatura</flux:select.option>
            @foreach($licenciaturas as $licenciatura)
              <flux:select.option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</flux:select.option>
            @endforeach
          </flux:select>


          <div class="flex md:items-end">
            <flux:button wire:click="exportarEstadistica" variant="primary" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <div class="flex items-center gap-1">
                        <flux:icon.sheet />
                        <span>Exportar a Excel</span>
                        </div>
                </flux:button>
          </div>
</div>
