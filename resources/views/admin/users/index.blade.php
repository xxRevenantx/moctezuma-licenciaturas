<x-layouts.app :title="__('Panel - Usuarios')">
    <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">

        <livewire:admin.usuarios.crear-usuario  />
        <livewire:admin.usuarios.mostrar-usuarios />

    </div>
</x-layouts.app>

