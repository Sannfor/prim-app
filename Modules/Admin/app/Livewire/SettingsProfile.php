<?php

namespace Modules\Admin\Livewire;

use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Pengaturan profil administrator.
 *
 * Mengikuti frame "ADMIN - Pengaturan profile admin".
 */
#[Layout('layouts::admin')]
#[Title('Profile Admin')]
class SettingsProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = (string) $user->phone;
        $this->address = (string) $user->address;
    }

    public function save(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = now();
        }

        $user->save();

        Flux::toast(variant: 'success', text: 'Profil berhasil diperbarui.');
    }

    public function render(): View
    {
        return view('admin::livewire.settings-profile', [
            'user' => Auth::user(),
        ]);
    }
}
