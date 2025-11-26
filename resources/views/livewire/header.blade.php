<div class="w-full">

  <!-- BARRA SUPERIOR -->
  <div
    class="w-full flex flex-wrap items-center justify-between gap-4 rounded-2xl p-4 sm:p-5 bg-white/90 dark:bg-neutral-800/90 shadow-lg border border-neutral-200 dark:border-neutral-700 mb-4 relative overflow-visible">
    <div
      class="pointer-events-none absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500">
    </div>

    <!-- Fecha -->
    <div
      class="flex items-center gap-2 w-full sm:w-auto justify-center lg:justify-start text-neutral-700 dark:text-neutral-100">
      <div
        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
        <flux:icon.calendar />
      </div>
      <span class="font-medium">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
    </div>

    <!-- Widgets -->
    <div class="w-full sm:w-auto flex flex-col lg:flex-row items-center gap-3 mt-2 sm:mt-0">

      <!-- Campanita -->
      <div x-data="{ open: @entangle('open') }" x-cloak class="relative" wire:poll.20s>
        <button type="button" @click="open = !open"
          class="relative p-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition group"
          aria-label="Notificaciones de matrículas">
          <svg class="w-6 h-6 text-neutral-700 dark:text-neutral-200 group-hover:scale-105 transition"
            viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path
              d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22m6-6v-5a6 6 0 0 0-4-5.65V4a2 2 0 0 0-4 0v.35A6 6 0 0 0 6 11v5l-2 2v1h16v-1z" />
          </svg>
          <span
            class="absolute -top-1 -right-1 min-w-[1.15rem] h-5 px-1 inline-flex items-center justify-center text-[11px] font-semibold rounded-full text-white bg-indigo-600 shadow ring-2 ring-white dark:ring-neutral-800">
            {{ $total }}
          </span>
        </button>

        <!-- Popover -->
        <div x-show="open" x-transition @click.outside="open = false" @keydown.escape.window="open = false"
          class="absolute right-0 mt-2 w-[min(92vw,24rem)] bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl ring-1 ring-neutral-200 dark:ring-neutral-700 z-[10001]">
          <div class="p-4 flex items-center justify-between">
            <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">Matrículas</h4>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">
              Prefijos: <span class="font-mono">{{ $prefijoMin }}–{{ $prefijoMax }}</span>
            </span>
          </div>

          <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
            <li class="p-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/40 transition">
              <button type="button" class="w-full text-left" wire:click.prevent="openModal('con')"
                wire:loading.attr="disabled" @click.stop="open=false">
                <div class="flex items-start gap-3">
                  <span class="mt-1 w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                  <div class="flex-1">
                    <p class="font-medium text-neutral-900 dark:text-neutral-100">Alumnos con Matrícula</p>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300">Total: <span
                        class="font-semibold">{{ $conPrefijo }}</span> ({{ $porcConPrefijo }}%)</p>
                  </div>
                </div>
              </button>
            </li>

            <li class="p-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/40 transition">
              <button type="button" class="w-full text-left" wire:click.prevent="openModal('sin')"
                wire:loading.attr="disabled" @click.stop="open=false">
                <div class="flex items-start gap-3">
                  <span class="mt-1 w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                  <div class="flex-1">
                    <p class="font-medium text-neutral-900 dark:text-neutral-100">Alumnos sin Matrícula</p>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300">Total: <span
                        class="font-semibold">{{ $sinPrefijo }}</span> ({{ $porcSinPrefijo }}%)</p>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">Incluye matrículas vacías.</p>
                  </div>
                </div>
              </button>
            </li>

            {{-- ALUMNOS CON CALIFICACIÓN MENOR O IGUAL A 6 O NP --}}
            <li class="p-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/40 transition">
              <button type="button" class="w-full text-left" wire:click.prevent="openModal('bajos')"
                wire:loading.attr="disabled" @click.stop="open=false">
                <div class="flex items-start gap-3">
                  <span class="mt-1 w-2.5 h-2.5 rounded-full bg-red-500"></span>
                  <div class="flex-1">
                    <p class="font-medium text-neutral-900 dark:text-neutral-100">Alumnos con ≤ 6 o NP</p>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300">Total: <span
                        class="font-semibold">{{ $bajos }}</span> ({{ $porcBajos }}%)</p>
                  </div>
                </div>
              </button>
            </li>

            <li class="p-3">
              <div class="flex items-start gap-3">
                <span class="mt-1 w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <div class="flex-1">
                  <p class="font-medium text-neutral-900 dark:text-neutral-100">Total de inscripciones</p>
                  <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ $total }}</p>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Chips -->
      <div class="inline-flex items-center gap-2">
        <div
          class="rounded-xl px-3 py-2 border border-neutral-200 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700/40 text-sm text-neutral-800 dark:text-neutral-100">
          Ciclo escolar
          <flux:badge color="indigo" class="ml-2">{{ $dashboard->ciclo_escolar ?? "0" }}</flux:badge>
        </div>
        <div
          class="rounded-xl px-3 py-2 border border-neutral-200 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700/40 text-sm text-neutral-800 dark:text-neutral-100">
          Periodo escolar
          <flux:badge class="uppercase ml-2" color="indigo">{{ $dashboard->periodo_escolar ?? "0" }}</flux:badge>
        </div>
      </div>

      <!-- Avatar -->
      @if(auth()->user()->photo)
        <div class="relative w-10 h-10 hidden lg:block">
          @if(auth()->user()->photo && file_exists(storage_path('app/public/profile-photos/' . auth()->user()->photo)))
            <div
              class="w-full h-full rounded-full overflow-hidden border-4 border-white shadow ring-1 ring-neutral-200 dark:ring-neutral-700">
              <img src="{{ asset('storage/profile-photos/' . auth()->user()->photo) }}" alt="Avatar"
                class="w-full h-full object-cover">
            </div>
          @else
            <flux:avatar circle badge badge:circle badge:color="green" :initials="auth()->user()->initials()"
              :name="auth()->user()->name" />
          @endif
          <span
            class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 border-2 border-white dark:border-neutral-800 rounded-full shadow-md"></span>
        </div>
      @else
        <flux:avatar circle badge badge:circle badge:color="green" class="hidden lg:block"
          :initials="auth()->user()->initials()" :name="auth()->user()->name" />
      @endif
    </div>
  </div>

  <!-- LOADER PANTALLA COMPLETA al abrir -->
  <div class="fixed inset-0 z-[9999] flex items-center justify-center" wire:loading wire:target="openModal"
    aria-live="assertive" role="status">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px]"></div>
    <div
      class="relative bg-white dark:bg-neutral-800 px-6 py-5 rounded-2xl shadow-2xl ring-1 ring-neutral-200 dark:ring-neutral-700">
      <div class="flex items-center gap-3">
        <span
          class="inline-block w-6 h-6 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
        <span class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Cargando alumnos…</span>
      </div>
    </div>
  </div>

  <!-- MODAL -->
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
  <div x-data="{ show: @entangle('modalOpen') }" x-cloak x-show="show" x-transition.opacity
    class="fixed inset-0 z-[10000] flex items-center justify-center p-3 sm:p-4"
    @keydown.escape.window="$wire.closeModal()" wire:key="alumnos-modal-{{ $modalTipo }}" wire:loading.remove
    wire:target="openModal"
    x-init="const toggle=v=>document.documentElement.classList.toggle('overflow-hidden',v); toggle(show); $watch('show',v=>toggle(v));"
    role="dialog" aria-modal="true" aria-labelledby="alumnos-modal-title">

    <div class="absolute inset-0 bg-black/45 backdrop-blur-sm" @click="$wire.closeModal()"></div>

    <div
      class="relative w-[1500px] max-w-[96vw] h-[820px] max-h-[96vh] flex flex-col bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl ring-1 ring-neutral-200 dark:ring-neutral-700 overflow-hidden"
      aria-busy="false" wire:loading.attr="aria-busy" wire:target="closeModal">

      <!-- Header -->
      <div
        class="px-4 sm:px-5 py-4 flex items-center justify-between border-b border-neutral-200 dark:border-neutral-700 bg-gradient-to-r from-white to-neutral-50 dark:from-neutral-800 dark:to-neutral-800/60">
        <h3 id="alumnos-modal-title" class="text-base sm:text-lg font-semibold text-neutral-900 dark:text-neutral-100">
          @if($modalTipo === 'con')
            Alumnos con matrícula
          @elseif($modalTipo === 'sin')
            Alumnos sin matrícula
          @else
            Alumnos con ≤ 6 o NP
          @endif

          @php
            $grupoTotal = $modalTipo === 'con' ? $conPrefijo : ($modalTipo === 'sin' ? $sinPrefijo : $bajos);
          @endphp
          <span class="ml-2 text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
            (mostrando {{ min($grupoTotal, $modalLimit) }} de {{ $grupoTotal }})
          </span>
        </h3>
        <button class="p-2 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-700 transition"
          @click="$wire.closeModal()" wire:loading.attr="disabled" wire:target="closeModal" aria-label="Cerrar modal">
          ✕
        </button>
      </div>

      <!-- Contenido -->
      <div class="px-4 sm:px-5 py-4 relative flex-1 overflow-y-auto">
        <!-- Toolbar -->
        <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
          <div class="relative w-full">
            <input type="text" wire:model.live="search"
              placeholder="Buscar por matrícula, nombre, apellidos, licenciatura, modalidad, generación, cuatrimestre…"
              class="w-full pl-10 pr-24 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" viewBox="0 0 24 24"
              fill="currentColor" aria-hidden="true">
              <path
                d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79L20 21.5 21.5 20l-6-6zM4 9.5C4 6.46 6.46 4 9.5 4S15 6.46 15 9.5 12.54 15 9.5 15 4 12.54 4 9.5z" />
            </svg>
            <span class="absolute right-16 top-1/2 -translate-y-1/2" wire:loading wire:target="search">
              <span
                class="inline-block w-4 h-4 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
            </span>
            @if(strlen($search ?? '') > 0)
              <button type="button" wire:click="$set('search','')"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-xs px-2 py-1 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700">
                Limpiar
              </button>
            @endif
          </div>

          <div class="hidden sm:flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <span>Mostrando</span>
            <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $this->alumnos->count() }}</span>
            <span>de</span>
            @php $totalGrupo = $modalTipo === 'con' ? $conPrefijo : ($modalTipo === 'sin' ? $sinPrefijo : $bajos); @endphp
            <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $totalGrupo }}</span>
            <span>registros</span>
          </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 shadow-sm"
          wire:loading.class="opacity-50" wire:target="loadMore,search">
          {{-- ===================== TABLA: MATERIAS CON ≤6 O NP (solo en modal "bajos") ===================== --}}
          {{-- ===================== TABLA: MATERIAS CON ≤6 O NP (solo en modal "bajos") ===================== --}}
          @if($modalTipo === 'bajos')
            <div class="mt-6">
              <div class="mb-3 flex items-center justify-between">
                <h4 class="text-sm sm:text-base font-semibold text-neutral-900 dark:text-neutral-100">
                  Materias con calificación ≤ 6 o NP
                </h4>

                <flux:button wire:click="exportarReprobados" variant="primary"
                  class="bg-green-700 hover:bg-green-800 focus:ring-4 dark:text-white">
                  <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor" class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>

                    <span>Exportar</span>
                  </div>
                </flux:button>
                <div class="text-xs text-neutral-500 dark:text-neutral-400">
                  (Se agrupa por alumno; una fila por cada materia con calificación baja/NP)
                </div>
              </div>

              <div class="overflow-x-auto rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 shadow-sm"
                wire:loading.class="opacity-50" wire:target="loadMore,search">
                <table class="min-w-full text-sm">
                  <thead
                    class="sticky top-0 z-10 bg-gradient-to-r from-rose-600 to-red-600 dark:from-rose-700 dark:to-red-700 text-white shadow">
                    <tr>
                      <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">#</th>
                      <th
                        class="px-3 py-3 text-left text-[11px] tracking-wider uppercase sticky left-0 z-30 bg-gradient-to-r from-rose-600 to-red-600 dark:from-rose-700 dark:to-red-700 border-r border-white/20">
                        Matrícula</th>
                      <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Nombre</th>
                      <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Licenciatura</th>
                      <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Cuatrimestre Actual</th>
                      <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Materia</th>
                      <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Cuatrimestre Materia</th>
                      <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Calificación</th>
                    </tr>
                  </thead>

                  <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @php $k = 0; @endphp
                    @forelse($this->alumnos as $al)
                      @php
                        // Filtra calificaciones bajas según tu esquema: columna 'calificacion'
                        $cals = collect($al->calificaciones ?? [])->filter(function ($c) {
                          $raw = $c->calificacion;
                          if (is_null($raw))
                            return false;
                          $u = strtoupper((string) $raw);
                          $isCode = in_array($u, ['NP', 'N/P', 'N.P.', 'NA']);
                          $isNum = is_numeric($raw) && floatval($raw) <= 6;
                          return $isCode || $isNum;
                        })->values();
                        $rowspan = $cals->count();
                      @endphp

                      @if($rowspan > 0)
                        @foreach($cals as $idx => $cal)
                          @php
                            $k++;
                            $lic = optional($al->licenciatura)->nombre;
                            $rvoe = optional($al->licenciatura)->RVOE;
                            $cua = optional($al->cuatrimestre)->cuatrimestre;

                            // 🔴 Nombre correcto de la materia: calificacion -> asignacionMateria -> materia -> nombre
                            $materiaNombre = optional(optional($cal->asignacionMateria)->materia)->nombre ?? '—';

                            $cuatrimestreMateria = optional($cal->asignacionMateria)->cuatrimestre->cuatrimestre ?? '—';
                            $valor = $cal->calificacion;
                            $u = strtoupper((string) $valor);
                            $isCode = in_array($u, ['NP', 'N/P', 'N.P.', 'NA']);
                            $isNum = is_numeric($valor) && floatval($valor) <= 6;

                            $badgeClass = $isCode
                              ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200 dark:bg-rose-900/20 dark:text-rose-200 dark:ring-rose-800/60'
                              : 'bg-red-100 text-red-700 ring-1 ring-red-200 dark:bg-red-900/20 dark:text-red-200 dark:ring-red-800/60';

                            $obs = $cal->observaciones ?? $cal->comentarios ?? null;
                          @endphp

                          <tr
                            class="group odd:bg-neutral-50/60 dark:odd:bg-neutral-800/40 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                            {{-- # --}}
                            @if($idx === 0)
                              <td class="px-3 py-2 align-middle" rowspan="{{ $rowspan }}">
                                <span class="block font-medium text-neutral-900 dark:text-neutral-100">{{ $idx + 1 }}</span>
                              </td>
                            @endif

                            {{-- Matrícula --}}
                            @if($idx === 0)
                              <td
                                class="px-3 py-2 align-middle sticky left-0 z-20 bg-white dark:bg-neutral-800 group-odd:bg-neutral-50/60 dark:group-odd:bg-neutral-800/40 group-hover:bg-rose-50 dark:group-hover:bg-rose-900/20 border-r border-neutral-200 dark:border-neutral-700"
                                rowspan="{{ $rowspan }}">
                                <span
                                  class="inline-block px-2 py-1 rounded-lg bg-neutral-100 dark:bg-neutral-700/70 font-mono text-[13px] text-neutral-800 dark:text-neutral-100">
                                  {{ $al->matricula ?? '—' }}
                                </span>
                              </td>
                            @endif

                            {{-- Nombre --}}
                            @if($idx === 0)
                              <td class="px-3 py-2 align-middle max-w-[240px]" rowspan="{{ $rowspan }}">
                                <span class="block font-medium text-neutral-900 dark:text-neutral-100 truncate"
                                  title="{{ $al->nombre }} {{ $al->apellido_paterno }} {{ $al->apellido_materno }}">
                                  {{ $al->nombre }} {{ $al->apellido_paterno }} {{ $al->apellido_materno }}
                                </span>
                              </td>
                            @endif

                            {{-- Licenciatura --}}
                            @if($idx === 0)
                              <td class="px-3 py-2 align-middle" rowspan="{{ $rowspan }}">
                                <span
                                  class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:ring-emerald-800/60 text-[12px] truncate max-w-[220px]"
                                  title="{{ $lic }}">
                                  @if ($lic) {{$lic}} / {{ $rvoe }} @else — @endif
                                </span>
                              </td>
                            @endif

                            {{-- Cuatrimestre Actual --}}
                            @if($idx === 0)
                              <td class="px-3 py-2 align-middle" rowspan="{{ $rowspan }}">
                                <span
                                  class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-200 dark:ring-indigo-800/60 text-[12px]">
                                  {{ $cua ? $cua . 'º' : '—' }}
                                </span>
                              </td>
                            @endif

                            {{-- Materia (¡ya visible!) --}}
                            <td class="px-3 py-2 align-middle max-w-[280px]">
                              <span class="block truncate" title="{{ $materiaNombre }}">{{ $materiaNombre }}</span>
                            </td>

                            {{-- Cuatrimestre --}}
                            <td class="px-3 py-2 align-middle">
                              <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md bg-neutral-100 text-neutral-700 ring-1 ring-neutral-200 dark:bg-neutral-700/60 dark:text-neutral-200 dark:ring-neutral-600/60 text-[12px]">
                                {{ $cuatrimestreMateria }}
                              </span>
                            </td>

                            {{-- Calificación (badge rojo) --}}
                            <td class="px-3 py-2 align-middle">
                              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[12px] {{ $badgeClass }}">
                                {{ $isNum ? number_format((float) $valor, 1) : $u }}
                              </span>
                            </td>

                          </tr>
                        @endforeach
                      @endif
                    @empty
                      <tr>
                        <td colspan="9" class="px-3 py-8 text-center text-neutral-500">Sin registros.</td>
                      </tr>
                    @endforelse
                  </tbody>

                  {{-- Skeleton mientras carga --}}
                  <tbody wire:loading wire:target="loadMore,search" class="divide-y divide-transparent">
                    @for ($i = 0; $i < 5; $i++)
                      <tr>
                        @for ($j = 0; $j < 9; $j++)
                          <td class="px-3 py-2">
                            <div class="h-3 w-full max-w-[160px] rounded bg-neutral-200 dark:bg-neutral-700 animate-pulse">
                            </div>
                          </td>
                        @endfor
                      </tr>
                    @endfor
                  </tbody>
                </table>
              </div>
            </div>
          @endif
          {{-- ===================== /TABLA: MATERIAS CON ≤6 O NP ===================== --}}


        </div>

        <!-- Overlay local para loadMore/search -->
        <div
          class="absolute inset-0 bg-white/60 dark:bg-neutral-900/50 backdrop-blur-[1px] flex items-center justify-center rounded-b-2xl"
          wire:loading wire:target="loadMore,search">
          <span
            class="inline-block w-8 h-8 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
          <span class="ml-3 text-sm font-medium text-neutral-800 dark:text-neutral-100">Cargando…</span>
        </div>

        @php $totalGrupo = $modalTipo === 'con' ? $conPrefijo : ($modalTipo === 'sin' ? $sinPrefijo : $bajos); @endphp
        @if($totalGrupo > $modalLimit)
          <div class="mt-4 flex justify-center">
            <button wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore"
              class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 inline-flex items-center gap-2">
              <span wire:loading.remove wire:target="loadMore">Cargar más</span>
              <span wire:loading wire:target="loadMore" class="inline-flex items-center gap-2">
                <span class="w-4 h-4 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                Cargando…
              </span>
            </button>
          </div>
        @endif
      </div>

      <!-- 🔒 Loader mientras CIERRA el modal -->
      <div
        class="absolute inset-0 z-50 bg-white/70 dark:bg-neutral-900/60 backdrop-blur-sm flex items-center justify-center"
        wire:loading wire:target="closeModal">
        <span
          class="inline-flex items-center gap-3 px-4 py-2 rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 bg-white dark:bg-neutral-800 shadow">
          <span
            class="w-6 h-6 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
          <span class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Cerrando…</span>
        </span>
      </div>
      <!-- /Loader cierre -->

    </div>
  </div>

  <livewire:admin.licenciaturas.submodulo.matricula-editar>

</div>