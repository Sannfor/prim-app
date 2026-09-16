<?php

namespace Modules\Admin\Livewire;

use App\Models\Provider;
use App\Support\StoresUploadedImages;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Admin\Livewire\Concerns\ManagesResource;

/**
 * Pengelolaan data penyedia layanan premium.
 */
#[Layout('layouts::app')]
#[Title('Kelola Penyedia')]
class ProviderManager extends Component
{
    use ManagesResource, StoresUploadedImages, WithFileUploads;

    public string $name = '';
    public string $website = '';
    public string $description = '';
    public $logo = null;

    protected function defaultFormData(): array
    {
        return [
            'name' => '',
            'website' => '',
            'description' => '',
            'logo' => null,
        ];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama penyedia',
            'website' => 'situs web',
            'description' => 'deskripsi',
            'logo' => 'logo',
        ];
    }

    protected function findRecord(?int $id): Model
    {
        return Provider::query()->findOrFail($id);
    }

    protected function newRecord(): Model
    {
        return new Provider;
    }

    protected function formDataFrom(Model $record): array
    {
        return [
            'name' => $record->name,
            'website' => (string) $record->website,
            'description' => (string) $record->description,
            'logo' => null,
        ];
    }

    protected function beforeSave(Model $record, array $validated): void
    {
        if ($this->logo !== null) {
            $record->logo_path = $this->storeImage($this->logo, 'providers', $record->logo_path);
        }
    }

    protected function beforeDelete(Model $record): bool
    {
        if ($record->services()->exists()) {
            Flux::toast(
                variant: 'danger',
                text: 'Penyedia tidak dapat dihapus karena masih memiliki layanan.'
            );

            return false;
        }

        $this->deleteImage($record->logo_path);

        return true;
    }

    protected function deletedMessage(): string
    {
        return 'Penyedia berhasil dihapus.';
    }

    public function render(): View
    {
        return view('admin::livewire.provider-manager', [
            'providers' => Provider::query()
                ->withCount('services')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
