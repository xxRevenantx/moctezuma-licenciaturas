<div>
       <div class="flex flex-col gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Horario General</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Horario Escolarizada y SemiEscolarizada</p>
    </div>



    <div x-data="{ openAccordion: true }" x-cloak class="mb-4">
                <button
                    @click="openAccordion = !openAccordion"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                     <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Horarios Escolarizada</h1>
                    <svg :class="{'transform rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openAccordion" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">

                    <livewire:admin.horario-general.horario-general-escolarizada />

                </div>
            </div>



         <div x-data="{ openAccordion: true }" x-cloak class="mb-4">



                <button
                    @click="openAccordion = !openAccordion"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                     <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Horarios SemiEscolarizada</h1>
                    <svg :class="{'transform rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">

                  <livewire:admin.horario-general.horario-general-semiescolarizada />


                </div>
         </div>


</div>
