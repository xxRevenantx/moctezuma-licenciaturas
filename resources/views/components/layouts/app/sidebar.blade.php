<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f3f6fa] text-slate-800 dark:bg-slate-950 dark:text-slate-100">

        <flux:sidebar sticky stashable class="school-sidebar border-r-0 bg-gradient-to-b from-[#073b72] via-[#075a81] to-[#087c83] text-white shadow-[12px_0_40px_rgba(15,23,42,0.12)] dark:from-[#062d58] dark:via-[#064a68] dark:to-[#075f63]">
            <flux:sidebar.toggle class="lg:hidden text-white" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="school-brand group mb-1 block rounded-2xl bg-white/95 p-3 shadow-lg shadow-slate-950/10 ring-1 ring-white/50 transition hover:-translate-y-0.5 hover:shadow-xl" wire:navigate>
                <x-app-logo />
                <div class="mt-1 flex items-center justify-center gap-1.5 text-[9px] font-extrabold uppercase tracking-[0.16em] text-[#006492]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#88AC2E]"></span>
                    Gestión académica integral
                </div>
            </a>

            <flux:navlist class="school-nav-list">
                <flux:navlist.group :heading="__('Plataforma')" class="school-nav-section grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Panel') }}</flux:navlist.item>
                    <flux:navlist.item icon="school" :href="route('admin.escuela.index')" :current="request()->routeIs('admin.escuela.index')" wire:navigate>{{ __('Escuela') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            @can('admin.usuarios')
                <flux:navlist class="school-nav-list">
                    <flux:navlist.group :heading="__('Usuarios')" class="school-nav-section grid">
                        <flux:navlist.item icon="users" :href="route('admin.usuarios.index')" :current="request()->routeIs('admin.usuarios.index')" wire:navigate>{{ __('Usuarios') }}</flux:navlist.item>
                        <flux:navlist.item icon="academic-cap" :href="route('admin.estudiante')" :current="request()->routeIs('admin.estudiante')" wire:navigate>{{ __('Estudiantes') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan

            @can('admin.administracion')
                <flux:navlist class="school-nav-list">
                    <flux:navlist.group :heading="__('Documentación')" expandable class="school-nav-section">
                        <flux:navlist>
                            <flux:navlist.item icon="rectangle-stack" :href="route('admin.listas-generales')" :current="request()->routeIs('admin.listas-generales')" wire:navigate>{{ __('Listas Generales') }}</flux:navlist.item>
                            <flux:navlist.item icon="document-text" :href="route('admin.constancias')" :current="request()->routeIs('admin.constancias')" wire:navigate>{{ __('Constancias') }}</flux:navlist.item>
                            <flux:navlist.item icon="book-open" :href="route('admin.boletas')" :current="request()->routeIs('admin.boletas')" wire:navigate>{{ __('Boletas') }}</flux:navlist.item>
                            <flux:navlist.item icon="folder-open" :href="route('admin.documentacion')" :current="request()->routeIs('admin.documentacion')" wire:navigate>{{ __('Documentos') }}</flux:navlist.item>
                        </flux:navlist>
                    </flux:navlist.group>
                </flux:navlist>
            @endcan

            @canany(['admin.administracion', 'calificaciones-docente.ver'])
                <flux:navlist class="school-nav-list">
                    <flux:navlist.group :heading="__('Administración')" expandable class="school-nav-section">
                        @can('admin.administracion')
                            <flux:navlist>
                                <flux:navlist.group :heading="__('Ciudades')" expandable class="school-nav-subgroup">
                                    <flux:navlist.item icon="map" :href="route('admin.estados.index')" :current="request()->routeIs('admin.estados.index')" wire:navigate>{{ __('Estados') }}</flux:navlist.item>
                                    <flux:navlist.item icon="map-pin" :href="route('admin.ciudades.index')" :current="request()->routeIs('admin.ciudades.index')" wire:navigate>{{ __('Ciudad') }}</flux:navlist.item>
                                </flux:navlist.group>

                                <flux:navlist.item icon="bolt" :href="route('admin.acciones.index')" :current="request()->routeIs('admin.acciones.index')" wire:navigate>{{ __('Acciones') }}</flux:navlist.item>
                                <flux:navlist.item icon="academic-cap" :href="route('admin.asignacion.licenciaturas.index')" :current="request()->routeIs('admin.asignacion.licenciaturas.index')" wire:navigate>{{ __('Licenciaturas') }}</flux:navlist.item>

                                <flux:navlist.group :heading="__('Generaciones')" expandable class="school-nav-subgroup">
                                    <flux:navlist.item icon="plus-circle" :href="route('admin.generaciones.index')" :current="request()->routeIs('admin.generaciones.index')" wire:navigate>{{ __('Crear') }}</flux:navlist.item>
                                    <flux:navlist.item icon="arrows-right-left" :href="route('admin.asignar.generacion.index')" :current="request()->routeIs('admin.asignar.generacion.index')" wire:navigate>{{ __('Asignar') }}</flux:navlist.item>
                                </flux:navlist.group>
                            </flux:navlist>

                            <flux:navlist.item icon="squares-2x2" :href="route('admin.cuatrimestres.index')" :current="request()->routeIs('admin.cuatrimestres.index')" wire:navigate>{{ __('Cuatrimestres') }}</flux:navlist.item>
                            <flux:navlist.item icon="user-group" :href="route('admin.grupos.index')" :current="request()->routeIs('admin.grupos.index')" wire:navigate>{{ __('Grupos') }}</flux:navlist.item>
                            <flux:navlist.item icon="calendar-days" :href="route('admin.periodos.index')" :current="request()->routeIs('admin.periodos.index')" wire:navigate>{{ __('Periodos') }}</flux:navlist.item>
                            <flux:navlist.item icon="identification" :href="route('admin.directivos.index')" :current="request()->routeIs('admin.directivos.index')" wire:navigate>{{ __('Personal directivo') }}</flux:navlist.item>

                            <flux:navlist.group :heading="__('Profesores')" expandable class="school-nav-subgroup">
                                <flux:navlist.item icon="user-plus" :href="route('admin.profesor.index')" :current="request()->routeIs('admin.profesor.index')" wire:navigate>{{ __('Crear Profesor') }}</flux:navlist.item>
                                <flux:navlist.item icon="clipboard-document-list" :href="route('admin.profesor.lista_profesores')" :current="request()->routeIs('admin.profesor.lista_profesores')" wire:navigate>{{ __('Lista de profesores') }}</flux:navlist.item>
                                <flux:navlist.item icon="identification" :href="route('admin.profesor.credencial_profesor')" :current="request()->routeIs('admin.profesor.credencial_profesor')" wire:navigate>{{ __('Credencial') }}</flux:navlist.item>
                            </flux:navlist.group>

                            <flux:navlist.item icon="calendar-days" :href="route('admin.horario-general.index')" :current="request()->routeIs('admin.horario-general.index')" wire:navigate>{{ __('Horario General') }}</flux:navlist.item>
                        @endcan

                        @can('calificaciones-docente.ver')
                            <flux:navlist.item icon="clipboard-document-check" :href="route('admin.calificaciones-docente.index')" :current="request()->routeIs('admin.calificaciones-docente.*')" wire:navigate>{{ __('Calificaciones por docente') }}</flux:navlist.item>
                        @endcan

                        @can('admin.administracion')
                            <flux:navlist.group :heading="__('Materias')" class="school-nav-subgroup">
                                <flux:navlist.item icon="book-open" :href="route('admin.materia.index')" :current="request()->routeIs('admin.materia.index')" wire:navigate>{{ __('Materias') }}</flux:navlist.item>
                            </flux:navlist.group>
                        @endcan
                    </flux:navlist.group>
                </flux:navlist>
            @endcanany

            @can('admin.licenciaturas')
                <div class="school-extra-nav">
                    <livewire:sidebar>
                </div>
            @endcan

            <flux:spacer />

            <div class="school-system-card hidden lg:block">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/20">
                        <flux:icon.academic-cap class="size-5" />
                    </span>
                    <div>
                        <div class="text-xs font-extrabold text-white">Sistema Escolar</div>
                        <div class="mt-0.5 text-[10px] leading-4 text-white/65">Control académico · Licenciaturas</div>
                    </div>
                </div>
                <div class="school-building-mark" aria-hidden="true">⌂</div>
            </div>

            {{-- Menú de usuario de escritorio --}}
            <flux:dropdown position="bottom" align="start">
                @if (auth()->user()->photo)
                    <flux:profile circle badge badge:circle badge:color="green" src="{{ asset('storage/profile-photos/'.auth()->user()->photo) }}"
                        :initials="auth()->user()->initials()"
                        :name="auth()->user()->username"
                        icon-trailing="chevron-up"
                        class="school-user-profile"
                    />
                @else
                    <flux:profile circle badge badge:circle badge:color="green" class="school-user-profile overflow-hidden"
                        :initials="auth()->user()->initials()"
                        :name="auth()->user()->username"
                        icon-trailing="chevron-up"
                    />
                @endif

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->username }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        {{-- Menú móvil --}}
        <flux:header class="school-mobile-header lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <div class="ml-2 text-sm font-extrabold text-[#006492] dark:text-sky-300">Moctezuma</div>
            <flux:spacer />

            <flux:dropdown position="top" align="end">
                @if (auth()->user()->photo)
                    <flux:avatar circle badge badge:circle badge:color="green" src="{{ asset('storage/profile-photos/'.auth()->user()->photo) }}" />
                @else
                    <flux:profile circle badge badge:circle badge:color="green"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-up"
                    />
                @endif

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->username }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @stack('scripts')
        @fluxScripts
    </body>
</html>
