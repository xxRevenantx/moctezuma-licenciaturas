<x-layouts.app :title="__('Panel - Profesores')">
    <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">

        <livewire:admin.profesor.crear-profesor  />
        <livewire:admin.profesor.mostrar-profesores />

    </div>
</x-layouts.app>

