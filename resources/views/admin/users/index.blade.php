<x-layouts.app :title="__('Usuarios · Centro Universitario Moctezuma')">
    <div class="mx-auto w-full max-w-[1680px] space-y-5">
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="h-1 bg-gradient-to-r from-sky-700 via-cyan-500 to-lime-500"></div>
            <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-7">
                <div class="flex items-center gap-4">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                        <flux:icon.users class="size-6" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">Gestión de usuarios</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Cuentas, permisos y seguridad institucional · Licenciaturas</p>
                    </div>
                </div>
                <livewire:admin.usuarios.crear-usuario />
            </div>
        </div>
        <livewire:admin.usuarios.mostrar-usuarios />
    </div>
</x-layouts.app>
