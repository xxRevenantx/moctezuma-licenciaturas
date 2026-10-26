<x-layouts.app :title="__('Panel - Profesores')">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="grid size-12 place-items-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300"><flux:icon.academic-cap class="size-7" /></div>
                <div>
                    <h1 class="text-xl font-bold text-zinc-900 dark:text-white sm:text-2xl">Profesores PRO</h1>
                    <p class="text-sm text-zinc-500">Directorio institucional y disponibilidad académica</p>
                </div>
            </div>
            <livewire:admin.profesor.crear-profesor />
        </div>
        <livewire:admin.profesor.mostrar-profesores />
    </div>
</x-layouts.app>
