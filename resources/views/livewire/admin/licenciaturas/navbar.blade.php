<div class="w-full mx-auto">
  <div class="relative rounded-2xl bg-white/90 dark:bg-neutral-800/90 ring-1 ring-neutral-200 dark:ring-neutral-700 shadow-lg overflow-hidden">
    <!-- Acento superior -->
    <div class="h-1.5 bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>

    <flux:navbar
      class="p-3 sm:p-4 flex flex-wrap items-stretch justify-start md:justify-center gap-2 sm:gap-3 lg:gap-4"
      role="navigation"
      aria-label="Acciones del submódulo"
    >
      @foreach ($acciones as $accion)
        @php
          $activo = request()->route('submodulo') === $accion->slug;
        @endphp

        <flux:navbar.item
          href="{{ route('licenciaturas.submodulo', ['slug_licenciatura' => $slug_licenciatura, 'slug_modalidad' => $slug_modalidad, 'submodulo' => $accion->slug]) }}"
          aria-current="{{ $activo ? 'page' : 'false' }}"
          class="
            group relative
            flex-1 sm:flex-none
            min-w-[200px] md:min-w-[220px]
            rounded-xl px-3.5 py-3
            ring-1
            transition-all duration-200
            focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500
            {{ $activo
                ? 'bg-indigo-50 dark:bg-indigo-900/15 ring-indigo-300 dark:ring-indigo-700'
                : 'bg-white/70 dark:bg-neutral-800/70 ring-neutral-200 dark:ring-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-700/40'
            }}
          "
        >
          <div class="flex items-center gap-3">
            <!-- Icono -->
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-neutral-100 text-neutral-600 ring-1 ring-neutral-200 dark:bg-neutral-900 dark:text-neutral-200 dark:ring-neutral-700 group-hover:scale-[1.03] transition">
              <img
                class="w-6 h-6 object-contain"
                src="{{ asset('storage/acciones/'.$accion->icono) }}"
                alt="{{ $accion->accion }}"
                loading="lazy"
              />
            </span>

            <!-- Texto + badge (bajas) -->
            <div class="flex items-center gap-2">
              @if($accion->slug === 'bajas')
                <span class="text-sm sm:text-[15px] font-semibold tracking-wide
                             {{ $activo ? 'text-indigo-700 dark:text-indigo-200' : 'text-neutral-800 dark:text-neutral-100' }}">
                  {{ $accion->accion }}
                </span>
                <flux:badge color="red" inset="top bottom">{{ $contar_bajas }}</flux:badge>
              @else
                <span class="text-sm sm:text-[15px] font-medium tracking-wide
                             {{ $activo ? 'text-indigo-700 dark:text-indigo-200' : 'text-neutral-800 dark:text-neutral-100' }}">
                  {{ $accion->accion }}
                </span>
              @endif
            </div>
          </div>

          <!-- Indicador activo (borde lateral en XL) -->
          @if($activo)
            <span class="pointer-events-none hidden xl:block absolute -right-px top-1/2 -translate-y-1/2 h-8 w-1 rounded-l-full bg-indigo-500/80"></span>
          @endif
        </flux:navbar.item>
      @endforeach
    </flux:navbar>
  </div>
</div>
