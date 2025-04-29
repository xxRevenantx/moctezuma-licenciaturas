<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Actualiza tu nombre y dirección de correo electrónico')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            <div>
                <flux:input wire:model.live="photo" :label="__('Imagen de perfil')" type="file" accept="image/jpeg,image/jpg,image/png" />


                @if ($photo)
                    <div class="mt-4">
                        <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('Profile Preview') }}" class="w-20 h-20 rounded-full">
                        <flux:button wire:click="eliminarFoto" class="mt-2" variant="danger">Eliminar foto</flux:button>
                    </div>
                @elseif ($photoUrl)
                    <div class="mt-4">
                        <img src="{{ asset('storage/profile-photos/'. $photoUrl) }}" alt="{{ __('Current Profile Image') }}" class="w-20 h-20 rounded-full">
                        <flux:button wire:click="eliminarFoto" class="mt-2" variant="danger">Eliminar foto</flux:button>

                    </div>
                @else
                <flux:avatar circle class="mt-3 w-25 h-25"
                :initials="auth()->user()->initials()"
                :name="auth()->user()->username"
    />

                @endif
            </div>

            <flux:input wire:model="username" :label="__('Nombre de usuario')" type="text" required autofocus autocomplete="username" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button style="background: #04689c; color:white; cursor:pointer"  type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        {{-- <livewire:settings.delete-user-form /> --}}
    </x-settings.layout>
</section>

