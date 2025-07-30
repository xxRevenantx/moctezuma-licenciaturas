<div class="w-full flex flex-wrap justify-between rounded-xl p-4 bg-white dark:bg-neutral-800 shadow-md border border-neutral-200 dark:border-neutral-700 mb-4">
    <div class="flex items-center gap-2 w-full sm:w-auto text-center justify-center lg:justify-start">
        <flux:icon.calendar /> {{ now()->translatedFormat('d \d\e F \d\e Y') }}

    </div>

    <div class="w-full flex gap-4 flex-col lg:flex-row  sm:w-auto text-center mt-5 sm:mt-0 items-center">
        <div class="border-1 border-gray-200 dark:border-gray-400 dark:bg-indigo-700  p-2 rounded-2xl">Ciclo escolar <flux:badge color="indigo">{{ $dashboard->ciclo_escolar ?? "0" }}</flux:badge></div>
        <div class="border-1 border-gray-200 dark:border-gray-400 dark:bg-indigo-700  p-2 rounded-2xl ">Periodo escolar <flux:badge class="uppercase" color="indigo">{{ $dashboard->periodo_escolar ?? "0" }}</flux:badge></div>

        @if(auth()->user()->photo)

            <div class="relative w-10 h-10  invisible lg:visible">
                <!-- Avatar circular con imagen -->
                @if(auth()->user()->photo && file_exists(storage_path('app/public/profile-photos/' . auth()->user()->photo)))
                    <div class="w-full h-full rounded-full overflow-hidden border-4 border-gray-100 shadow ">
                        <img src="{{ asset('storage/profile-photos/' . auth()->user()->photo) }}"
                            alt="Avatar"
                            class="w-full h-full object-cover">
                    </div>
                @else
                    <flux:avatar circle badge badge:circle badge:color="green"
                        :initials="auth()->user()->initials()"
                        :name="auth()->user()->name"
                    />
                @endif

                <!-- Badge verde (posición inferior derecha) -->
                <span class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 border-2 border-white rounded-full shadow-md"></span>
            </div>

        @else
               <flux:avatar circle badge badge:circle badge:color="green" class="invisible lg:visible"
                            :initials="auth()->user()->initials()"
                            :name="auth()->user()->name"
                />
        @endif





    </div>
</div>
