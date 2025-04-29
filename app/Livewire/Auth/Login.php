<?php

namespace App\Livewire\Auth;

use App\Helpers\Flash;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string')]
    public string $matricula = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
{
    $this->validate();

    $this->ensureIsNotRateLimited();

    // Buscar al usuario por matrícula
    $user = \App\Models\User::where('matricula', $this->matricula)->first();

    // Verificar que exista el usuario
    if (! $user) {
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages([
            'matricula' => __('auth.failed'),
        ]);
    }

    // Verificar si tiene status activo
    if ($user->status === 'false') {
        throw ValidationException::withMessages([
            'matricula' => 'Tu cuenta está inactiva. Contacta al administrador.',
        ]);
    }

    // Intentar autenticar
    if (! Auth::attempt(['matricula' => $this->matricula, 'password' => $this->password], $this->remember)) {
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages([
            'matricula' => __('auth.failed'),
        ]);
    }

    RateLimiter::clear($this->throttleKey());
    Session::regenerate();

    Flash::success('Bienvenido de nuevo, ' . Auth::user()->username . '.');

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
}



    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'matricula' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->matricula).'|'.request()->ip());
    }
}
