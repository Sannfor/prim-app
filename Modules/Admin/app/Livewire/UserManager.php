<?php

namespace Modules\Admin\Livewire;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
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
#[Layout('layouts::admin')]
#[Title('Kelola Pengguna')]
class UserManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'roleFilter', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    /**
     * Ubah status akun seorang pengguna.
     *
     * Status administrator hanya boleh diubah oleh Super Admin, agar
     * administrator biasa tidak dapat menyuspend rekannya sendiri.
     */
    public function changeStatus(int $userId, string $status): void
    {
        $target = User::query()->findOrFail($userId);

        if (! in_array($status, array_keys(UserStatus::options()), true)) {
            Flux::toast(variant: 'danger', text: 'Status tidak dikenal.');

            return;
        }

        if ($target->id === auth()->id()) {
            Flux::toast(variant: 'danger', text: 'Anda tidak dapat mengubah status akun Anda sendiri.');

            return;
        }

        if ($target->isAdmin() && ! auth()->user()->isSuperAdmin()) {
            Flux::toast(variant: 'danger', text: 'Hanya Super Admin yang dapat mengubah status administrator lain.');

            return;
        }

        $target->update(['status' => $status]);

        Flux::toast(variant: 'success', text: 'Status pengguna berhasil diperbarui.');
    }

    /**
     * Ubah peran seorang pengguna.
     *
     * Aturan tingkat administrator:
     *   - mengangkat pengguna menjadi admin hanya oleh Super Admin;
     *   - mengubah peran administrator lain hanya oleh Super Admin;
     *   - Super Admin terakhir tidak boleh diturunkan agar tidak ada akun yang
     *     dapat mengangkat administrator lagi.
     */
    public function changeRole(int $userId, string $role): void
    {
        $target = User::query()->findOrFail($userId);
        $pelaku = auth()->user();

        if ($target->id === $pelaku->id && $role !== Role::Admin->value) {
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

        $menjadiAdmin = $role === Role::Admin->value;
        $targetAdmin = $target->isAdmin();

        if (! $pelaku->isSuperAdmin() && $menjadiAdmin && ! $targetAdmin) {
            Flux::toast(
                variant: 'danger',
                text: 'Hanya Super Admin yang dapat mengangkat pengguna menjadi administrator.'
            );

            return;
        }

        if (! $pelaku->isSuperAdmin() && $targetAdmin && ! $menjadiAdmin) {
            Flux::toast(
                variant: 'danger',
                text: 'Hanya Super Admin yang dapat mengubah peran administrator lain.'
            );

            return;
        }

        $target->update(['role' => $role]);

        Flux::toast(variant: 'success', text: 'Peran pengguna berhasil diperbarui.');
    }

    /**
     * Pilihan peran yang boleh diberikan oleh pengguna yang sedang masuk.
     *
     * Administrator biasa hanya dapat memilih Pelanggan dan Penyedia Layanan;
     * pilihan Administrator hanya muncul untuk Super Admin.
     *
     * @return array<string, string>
     */
    private function pilihanPeran(): array
    {
        $semua = Role::options();

        if (auth()->user()?->isSuperAdmin()) {
            return $semua;
        }

        unset($semua[Role::Admin->value]);

        return $semua;
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
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->withCount(['transactions', 'subscriptions'])
            ->latest()
            ->paginate(12);

        $total = User::query()->count();
        $active = User::query()->where('status', UserStatus::Aktif->value)->count();
        $inactive = User::query()->where('status', UserStatus::NonAktif->value)->count();
        $suspended = User::query()->where('status', UserStatus::Suspend->value)->count();

        $share = fn (int $value): string => $total > 0
            ? round(($value / $total) * 100, 1).'% dari total'
            : 'Belum ada data';

        return view('admin::livewire.user-manager', [
            'users' => $users,
            'roles' => Role::options(),
            'roleOptions' => $this->pilihanPeran(),
            'isSuperAdmin' => (bool) auth()->user()?->isSuperAdmin(),
            'statuses' => UserStatus::options(),
            'cards' => [
                ['label' => 'Total Pengguna', 'value' => Number::format($total, locale: 'id'), 'hint' => 'Seluruh akun terdaftar'],
                ['label' => 'Aktif', 'value' => Number::format($active, locale: 'id'), 'hint' => $share($active)],
                ['label' => 'Non-aktif', 'value' => Number::format($inactive, locale: 'id'), 'hint' => $share($inactive)],
                ['label' => 'Disuspend', 'value' => Number::format($suspended, locale: 'id'), 'hint' => $share($suspended)],
            ],
        ]);
    }
}
