<?php

namespace Modules\Admin\Livewire;

use App\Enums\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pengelolaan pengguna platform: pencarian, penyaringan peran, dan
 * pengubahan peran akun.
 *
 * Administrator tidak diizinkan menurunkan perannya sendiri agar tidak
 * kehilangan akses ke panel pengelola.
 */
#[Layout('layouts::app')]
#[Title('Kelola Pengguna')]
class UserManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'roleFilter'], true)) {
            $this->resetPage();
        }
    }

    /**
     * Ubah peran seorang pengguna.
     */
    public function changeRole(int $userId, string $role): void
    {
        $target = User::query()->findOrFail($userId);

        if ($target->id === auth()->id() && $role !== Role::Admin->value) {
            Flux::toast(
                variant: 'danger',
                text: 'Anda tidak dapat menurunkan peran akun Anda sendiri.'
            );

            return;
        }

        if (! in_array($role, Role::values(), true)) {
            Flux::toast(variant: 'danger', text: 'Peran tidak dikenal.');

            return;
        }

        $target->update(['role' => $role]);

        Flux::toast(variant: 'success', text: 'Peran pengguna berhasil diperbarui.');
    }

    public function render(): View
    {
        $users = User::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->when($this->roleFilter !== '', fn ($q) => $q->where('role', $this->roleFilter))
            ->withCount(['transactions', 'subscriptions'])
            ->latest()
            ->paginate(12);

        return view('admin::livewire.user-manager', [
            'users' => $users,
            'roles' => Role::options(),
        ]);
    }
}
