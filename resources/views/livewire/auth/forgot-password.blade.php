<div class="flex flex-col gap-7 dark:bg-neutral-800  p-6 rounded-lg ">

    <x-auth-header :title="__('¿Olvidate tu contraseña?')" :description="__('Ingresa su correo electrónico para recibir un enlace de restablecimiento de contraseña')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email Address')"
            type="email"

            autofocus
            placeholder="email@ejemplo.com"
        />

        <flux:button  style="background: #04689c; color:white; cursor:pointer" type="submit" class="w-full">{{ __('Restablecer contraseña') }}</flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        {{ __('O, regresa para') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
    </div>
</div>
