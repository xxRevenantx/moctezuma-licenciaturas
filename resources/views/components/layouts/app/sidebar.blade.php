<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gray-200 dark:bg-zinc-700">

        <flux:sidebar sticky stashable class="border-r border-zinc-200 shadow-md bg-white dark:border-zinc-700 dark:bg-zinc-900 ">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>


            <flux:navlist >
                <flux:navlist.group :heading="__('Platform')" class="grid ">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="school" :href="route('admin.escuela.index')" :current="request()->routeIs('admin.escuela.index')" wire:navigate>{{ __('Escuela') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>


            @can('admin.usuarios')
            <flux:navlist >
                <flux:navlist.group :heading="__('Usuarios')" class="grid ">
                    <flux:navlist.item icon="users" :href="route('admin.usuarios.index')" :current="request()->routeIs('admin.usuarios.index')" wire:navigate>{{ __('Usuarios') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endcan

            @can('admin.administracion')
            <flux:navlist >
                <flux:navlist.group :heading="__('Administración')" expandable  >
                    <flux:navlist >

                    <flux:navlist.group  :heading="__('Ciudades')" expandable   >
                        <flux:navlist.item icon="rectangle-stack" :href="route('admin.estados.index')"
                        :current="request()->routeIs('admin.estados.index')"
                        wire:navigate>{{ __('Estados') }}</flux:navlist.item>


                        <flux:navlist.item icon="rectangle-stack" :href="route('admin.ciudades.index')"
                        :current="request()->routeIs('admin.ciudades.index')"
                        wire:navigate>{{ __('Ciudad') }}</flux:navlist.item>
                    </flux:navlist.group>


                        <flux:navlist.item icon="rectangle-stack" :href="route('admin.acciones.index')" :current="request()->routeIs('admin.acciones.index')" wire:navigate>{{ __('Acciones') }}</flux:navlist.item>
                        <flux:navlist.item icon="academic-cap" :href="route('admin.asignacion.licenciaturas.index')" :current="request()->routeIs('admin.asignacion.licenciaturas.index')" wire:navigate>{{ __('Licenciaturas') }}</flux:navlist.item>

                        <flux:navlist.group :heading="__('Generaciones')" expandable   >
                            <flux:navlist.item icon="rectangle-stack" :href="route('admin.generaciones.index')"
                            :current="request()->routeIs('admin.generaciones.index')"
                            wire:navigate>{{ __('Crear') }}</flux:navlist.item>
                            <flux:navlist.item icon="rectangle-stack" :href="route('admin.asignar.generacion.index')"
                            :current="request()->routeIs('admin.asignar.generacion.index')"
                            wire:navigate>{{ __('Asignar') }}</flux:navlist.item>
                        </flux:navlist.group>
                    </flux:navlist>

                    <flux:navlist.item icon="squares-2x2" :href="route('admin.cuatrimestres.index')" :current="request()->routeIs('admin.cuatrimestres.index')" wire:navigate>{{ __('Cuatrimestres') }}</flux:navlist.item>
                    <flux:navlist.item icon="refresh-ccw" :href="route('admin.periodos.index')" :current="request()->routeIs('admin.periodos.index')" wire:navigate>{{ __('Periodos') }}</flux:navlist.item>
                    <flux:navlist.item icon="square-user-round" :href="route('admin.directivos.index')" :current="request()->routeIs('admin.directivos.index')" wire:navigate>{{ __('Personal directivo') }}</flux:navlist.item>


                   <flux:navlist.group  :heading="__('Profesores')" expandable   >
                        <flux:navlist.item icon="rectangle-stack" :href="route('admin.profesor.index')"
                        :current="request()->routeIs('admin.profesor.index')"
                        wire:navigate>{{ __('Crear Profesor') }}</flux:navlist.item>

                    </flux:navlist.group>
                   <flux:navlist.group  :heading="__('Materias')"   >
                        <flux:navlist.item icon="book-check" :href="route('admin.materia.index')"
                        :current="request()->routeIs('admin.materia.index')"
                        wire:navigate>{{ __('Materias') }}</flux:navlist.item>

                    </flux:navlist.group>

                </flux:navlist.group>


            </flux:navlist>
            @endcan





            @can('admin.licenciaturas')
            <livewire:sidebar>
            @endcan


            <flux:spacer />



            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">

            @if (auth()->user()->photo)
                <flux:profile circle badge badge:circle badge:color="green" src="{{ asset('storage/profile-photos/'.auth()->user()->photo) }}"
                    :initials="auth()->user()->initials()"
                    :name="auth()->user()->username"
                    icon-trailing="chevron-up"
                />
            @else
                <flux:profile circle badge badge:circle badge:color="green" class="overflow-hidden"
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
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
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

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">

            @if (auth()->user()->photo)
                <flux:avatar circle badge badge:circle badge:color="green" src="{{ asset('storage/profile-photos/'.auth()->user()->photo) }}"

                />
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
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
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
