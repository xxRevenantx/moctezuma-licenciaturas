<?php

namespace App\Livewire\Settings;

use App\Helpers\Flash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{

    use WithFileUploads;

    public string $username = '';

    public string $email = '';

    public $photo;

    public $photoUrl;

    public $photoTemporary;

    /**
     * Mount the component.
     */
    #[On('refreshProfile')]
    public function mount(): void
    {
        $this->username = Auth::user()->username;
        $this->email = Auth::user()->email;
        $this->photoUrl  = Auth::user()->photo;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */




    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'username' => ['required', 'string', 'max:255','unique:users,username,' . $user->id],

            'photo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,jpg,png'],


            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);



             // Si se sube una nueva foto...
    if ($this->photo) {
        // Elimina la imagen anterior si no es la default
        if ($user->photo && $user->photo !== 'default.jpg') {
            Storage::delete('profile-photos/' . $user->photo);
        }

        // Guarda la nueva imagen
        $path = $this->photo->store('profile-photos');
        $validated['photo'] = str_replace('profile-photos/', '', $path);
    } else {
        unset($validated['photo']);
    }

        $user->fill($validated);



        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('refreshHeader');
        $this->dispatch('refreshProfile');
        $this->reset('photo'); // <- Esto limpia el input de tipo file

        $this->dispatch('profile-updated', name: $user->username);

    }

    public function eliminarFoto():void{
        $user = Auth::user();

        // Si existe una foto y no es la default, la eliminamos del disco
        if ($user->photo && $user->photo !== 'default.jpg') {
            Storage::delete('profile-photos/' . $user->photo);
        }

        // Establecemos el valor en la BD como 'default.jpg'
        $user->photo = null;
        $user->save();

        // Actualizamos la vista Livewire
        $this->photo = null;
        $this->photoUrl = $user->photo;

        $this->dispatch('refreshHeader');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}
