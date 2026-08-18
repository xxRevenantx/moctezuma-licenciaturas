<x-layouts.app :title="__('Calificaciones por docente')">
    <div class="school-page-shell relative overflow-hidden rounded-[28px] border border-slate-200/70 p-3 shadow-sm dark:border-slate-800 sm:p-5 lg:p-6">
        <div class="pointer-events-none absolute inset-0 opacity-[0.035] dark:opacity-[0.025]"
             style="background-image: linear-gradient(#006492 1px, transparent 1px), linear-gradient(90deg, #006492 1px, transparent 1px); background-size: 28px 28px;"></div>
        <div class="relative">
            <livewire:admin.calificaciones-docente.panel />
        </div>
    </div>
</x-layouts.app>
