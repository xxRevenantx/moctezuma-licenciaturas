@session('success')

<div
    x-data="{ showToast: false}"
    x-init="setTimeout(() => showToast = true, 1000)"
>
    <!-- Toast -->
    <div
        x-show="showToast"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-x-10"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-10"
        class="fixed top-5 right-5 bg-white border-2 text-gray-800 border-green-300 p-4 rounded-lg shadow-lg flex items-start gap-3 min-w-[250px]"
        @click.away="showToast = false"
        x-init="setTimeout(() => showToast = false, 3000)"
    >
        <div class="flex-1">
            <p>{{ $value }}</p>

        </div>
        <button @click="showToast = false" class="text-gray-800 hover:text-gray-600 font-bold text-lg leading-none">
            &times;
        </button>
    </div>
</div>
@endsession
