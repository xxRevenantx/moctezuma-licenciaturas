<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased dark:bg-linear-to-b dark:from-neutral-800 dark:to-neutral-800">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-r dark:border-neutral-800">
                <div class="absolute inset-0 ">
                    <img
                        src="{{ asset('storage/banner-lic.jpg') }}"
                        class="h-full w-full object-cover  shadow-sm dark:bg-neutral-950 dark:shadow-xs"
                    />
                </div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                    <span class="flex  w-80 items-center justify-center rounded-md">
                        <x-app-logo-icon class="me-2 h-7 fill-current text-white" />
                    </span>

                </a>

                @php
                    // [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                {{-- <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div> --}}
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto shadow-2xl p-6 flex w-full flex-col justify-center space-y-6 sm:w-[400px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex  w-80 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                        </span>

                        <span class="sr-only">{{ config('app.name', 'Licenciaturas') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
