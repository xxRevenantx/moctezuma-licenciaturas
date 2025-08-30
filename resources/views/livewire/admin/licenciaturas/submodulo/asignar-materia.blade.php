<div
  x-data="{
    loader:false,
    phraseIndex:0,
    phrases:[
      'Buscando disponibilidad…',
      'Reservando espacio en el horario…',
      'Aplicando cambios…',
      'Casi listo…'
    ],
  }"
  x-init="
    window.addEventListener('recargar-pagina', () => { loader = true; setTimeout(() => window.location.reload(), 300); });
    setInterval(() => {
      if (loader || ($wire.__instance && $wire.__instance.__isLoading && $wire.__instance.__lastAction === 'asignarProfesor')) {
        phraseIndex = (phraseIndex + 1) % phrases.length;
      }
    }, 1200);
  "
>


<!-- LOADER ELEGANTE SIN FONDO NEGRO -->
<div
  x-show="loader || ($wire.__instance && $wire.__instance.__isLoading && $wire.__instance.__lastAction === 'asignarProfesor')"
  x-transition.opacity.duration.250ms
  class="fixed inset-0 z-50 flex items-center justify-center pointer-events-auto"
  style="display:none"
  role="status" aria-live="assertive" aria-busy="true"
>
  <!-- Fondo sutil (sin oscurecer): patrón + halo radial -->
  <div class="absolute inset-0 -z-10 loader-bg pointer-events-none"></div>

  <!-- Marco con borde degradado -->
  <div class="relative p-[2px] rounded-2xl bg-gradient-to-tr from-indigo-500 via-fuchsia-500 to-cyan-400 shadow-2xl ring-1 ring-black/5">
    <div class="relative isolate rounded-2xl bg-white/90 dark:bg-neutral-900/80 backdrop-blur-md px-8 py-7 flex flex-col items-center gap-4 min-w-[320px]"
         x-transition.scale.origin.center.duration.200ms>

      <!-- Aro degradado animado + brillo -->
      <div class="relative">
        <div class="h-16 w-16 rounded-full animate-spin-smooth bg-conic"></div>
        <div class="absolute inset-1 rounded-full bg-white/95 dark:bg-neutral-900/90 shadow-inner"></div>
        <div class="absolute -inset-1 rounded-full blur-xl opacity-30 bg-gradient-to-r from-indigo-400 via-fuchsia-400 to-cyan-400"></div>
        <!-- Ícono -->
        <svg class="absolute inset-0 m-auto h-7 w-7 text-indigo-600 dark:text-indigo-300 animate-pulse"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
      </div>

      <!-- Título con gradiente -->
      <p class="text-lg font-semibold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 via-fuchsia-600 to-cyan-500">
        Asignando profesor…
      </p>

      <!-- Frase rotativa -->
      <p x-text="phrases[phraseIndex]"
         class="text-sm text-neutral-600 dark:text-neutral-300 text-center"></p>

      <!-- Barra indeterminada con shimmer -->
      <div class="w-full mt-1">
        <div class="h-1.5 w-64 rounded-full bg-neutral-200/80 dark:bg-neutral-700/60 overflow-hidden">
          <div class="h-full w-1/3 rounded-full bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-cyan-400 animate-slide-x"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Utilidades del loader -->
<style>
  /* Fondo sutil: grid de puntitos + halo radial claro (sin oscurecer) */
  .loader-bg{
    --dot: rgba(100,116,139,.22); /* slate-500/22 */
    background:
      radial-gradient(circle at 50% 40%, rgba(99,102,241,.25), rgba(99,102,241,0) 40%),
      radial-gradient(circle at 70% 70%, rgba(6,182,212,.18), rgba(6,182,212,0) 42%),
      radial-gradient(circle at 30% 75%, rgba(236,72,153,.18), rgba(236,72,153,0) 45%),
      radial-gradient(#0000 1.9px, var(--dot) 2px) 0 0/16px 16px;
  }

  /* Aro con conic-gradient + máscara para anillo */
  .bg-conic{
    background: conic-gradient(#6366f1, #a855f7, #06b6d4, #6366f1);
    -webkit-mask: radial-gradient(farthest-side, #0000 60%, #000 61%);
            mask: radial-gradient(farthest-side, #0000 60%, #000 61%);
    filter: drop-shadow(0 6px 16px rgba(0,0,0,.2));
  }
  .animate-spin-smooth{ animation: spin 1.1s linear infinite; }
  @keyframes spin{ to{ transform: rotate(360deg); } }

  /* Barra indeterminada */
  .animate-slide-x{ animation: slide 1.25s ease-in-out infinite; }
  @keyframes slide{
    0%   { transform: translateX(-110%); }
    50%  { transform: translateX(20%); }
    100% { transform: translateX(110%); }
  }
</style>



    @php
function isColorLight($hexColor) {
    $hexColor = str_replace('#', '', $hexColor);

    // Si es de 3 caracteres (#abc) conviértelo a 6 caracteres (#aabbcc)
    if (strlen($hexColor) == 3) {
        $r = hexdec(str_repeat(substr($hexColor,0,1),2));
        $g = hexdec(str_repeat(substr($hexColor,1,1),2));
        $b = hexdec(str_repeat(substr($hexColor,2,1),2));
    } else {
        $r = hexdec(substr($hexColor,0,2));
        $g = hexdec(substr($hexColor,2,2));
        $b = hexdec(substr($hexColor,4,2));
    }

    // Luminosidad (algoritmo estándar)
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    return $luminance > 0.6; // > 0.6 es claro, < 0.6 es oscuro (ajusta el umbral si lo necesitas)
}
@endphp


    <h3 class="mt-5 flex items-center gap-1 text-2xl font-bold text-gray-800 dark:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
        </svg>
       <span>Filtrar por:</span>
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2  p-2">


    {{-- <flux:field >
        <flux:label></flux:label>
        <flux:select wire:model.live="filtrar_foraneo">
            <flux:select.option value="">--Selecciona una opción---</flux:select.option>
            <flux:select.option value="true">Foráneo</flux:select.option>
            <flux:select.option value="false">Local</flux:select.option>
        </flux:select>
      </flux:field> --}}


    </div>

    <div class="overflow-x-auto">
        <h3 class="mt-5">Buscar Materia:</h3>
        <flux:input type="text" wire:model.live="search" placeholder="Buscar Materia" class="p-2 mb-4 w-full" />

    <div wire:loading.remove
                 wire:target="search">
    <div class="my-3">
          {{ $materias->links() }}
    </div>

  <div wire:loading.flex wire:target="asignarProfesor"
     class="fixed inset-0 z-50 flex items-center justify-center
            backdrop-blur-md backdrop-saturate-150
            bg-white/20 dark:bg-neutral-900/10">
  <div class="bg-white/90 dark:bg-neutral-900/90 rounded-2xl p-6 shadow-2xl ring-1 ring-black/5 flex flex-col items-center">
    <svg class="animate-spin h-10 w-10 text-indigo-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
    <span class="text-indigo-600 dark:text-indigo-300 font-semibold text-base">Asignando profesor…</span>
  </div>
</div>



     <table class="min-w-full border-collapse border border-gray-200 table-striped">
            <thead>
                <tr>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">#</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Materia</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cuatrimestre</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Asignar Profesor</th>

                </tr>
            </thead>
            <tbody>


        @if ($materias->isEmpty())
            <tr>
                <td colspan="2" class="border px-4 py-2 text-center text-gray-600 dark:bg-gray-500 dark:text-white">
                    No hay materias disponibles
                </td>
            </tr>
        @endif
        @foreach ($materias as $key => $materia)

            <tr>
                <td class="border px-4 py-2">{{ $key + 1 }}</td>
                <td class="border px-4 py-2">{{ $materia->nombre }}</td>
                <td class="border px-4 py-2">{{ $materia->cuatrimestre->nombre_cuatrimestre }}</td>
                <td class="border px-4 py-2">

                            @php
                    $color = '';
                    $textColor = '#fff';
                    if (!empty($profesor_seleccionado[$materia->id])) {
                        $profesor = $profesores->firstWhere('id', $profesor_seleccionado[$materia->id]);
                        $color = $profesor ? $profesor->color : '';
                        if ($color && isColorLight($color)) {
                            $textColor = '#000';
                        }
                    }
                @endphp

                {{-- <flux:select
                    wire:model.live="profesor_seleccionado.{{ $materia->id }}"
                    style='{{ $color ? "background-color: $color; color: $textColor;" : "" }}'
                >
                    <flux:select.option value="">--Seleccionar profesor--</flux:select.option>
                    @foreach ($profesores as $profesor)
                        <flux:select.option value="{{ $profesor->id }}">
                            {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }} {{ $profesor->nombre }}
                        </flux:select.option>
                    @endforeach
                </flux:select> --}}

                    <select
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm transition-colors bg-white dark:bg-neutral-800 dark:text-white"
                        wire:change="asignarProfesor({{ $materia->id }}, $event.target.value)"
                        style="{{ $color ? "background-color: $color; color: $textColor;" : "" }}"
                    >
                        <option value="">--Seleccionar profesor--</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id }}"
                                @if(($profesor_seleccionado[$materia->id] ?? '') == $profesor->id) selected @endif>
                                {{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}
                            </option>
                        @endforeach
                    </select>






                </td>
             </tr>
         @endforeach


            </tbody>
        </table>

         <div class="my-3">
          {{ $materias->links() }}
      </div>
        </div>
        </div>
</div>
