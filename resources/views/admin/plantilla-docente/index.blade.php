<x-layouts.app :title="__('Plantilla Docente por Licenciatura PRO')">
    <div class="space-y-4">
        <nav class="flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
            <a class="font-semibold text-[#006492] hover:underline" href="{{ route('admin.horario-general.index') }}">Horario General</a>
            <span>›</span>
            <span>Plantilla Docente PRO</span>
        </nav>
        <livewire:admin.horario-general.plantilla-docente />
    </div>
</x-layouts.app>
