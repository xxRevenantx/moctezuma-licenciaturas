<div class="flex flex-col gap-7 dark:bg-neutral-800  p-6 rounded-lg ">
    <x-auth-header :title="__('Inicia sesión en tu cuenta')" :description="__('Ingresa tu matrícula para iniciar sesión en tu panel')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Coreo electrónico')"
            type="email"
            autofocus
            autocomplete="Email"
            placeholder="Correo electrónico"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                autocomplete="current-password"
                :placeholder="__('Password')"
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('¿Olvidaste tu contraseña?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button style="background: #04689c; color:white; cursor:pointer" type="submit" class="w-full text-white">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    {{-- @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif --}}
</div>
