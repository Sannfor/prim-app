<?php

namespace Modules\Admin\Livewire;

use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
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
 * Pengelolaan data layanan premium pada katalog PRIM.
 */
#[Layout('layouts::app')]
#[Title('Kelola Layanan')]
class ServiceManager extends Component
{
    use ManagesResource, StoresUploadedImages, WithFileUploads;

    public ?int $providerId = null;
    public ?int $categoryId = null;
    public string $name = '';
    public string $tagline = '';
    public string $description = '';
    public string $website = '';
    public bool $isActive = true;
    public $logo = null;

    protected function defaultFormData(): array
    {
        return [
            'providerId' => null,
            'categoryId' => null,
            'name' => '',
            'tagline' => '',
            'description' => '',
            'website' => '',
            'isActive' => true,
            'logo' => null,
        ];
    }

    protected function rules(): array
    {
        return [
            'providerId' => ['required', 'integer', 'exists:providers,id'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:255'],
            'isActive' => ['boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'providerId' => 'penyedia',
            'categoryId' => 'kategori',
            'name' => 'nama layanan',
            'tagline' => 'tagline',
            'description' => 'deskripsi',
            'website' => 'situs web',
            'isActive' => 'status aktif',
            'logo' => 'logo',
        ];
    }

    protected function findRecord(?int $id): Model
    {
        return Service::query()->findOrFail($id);
    }

    protected function newRecord(): Model
    {
        return new Service;
    }

    protected function formDataFrom(Model $record): array
    {
        return [
            'providerId' => $record->provider_id,
            'categoryId' => $record->category_id,
            'name' => $record->name,
            'tagline' => (string) $record->tagline,
            'description' => (string) $record->description,
            'website' => (string) $record->website,
            'isActive' => $record->is_active,
            'logo' => null,
        ];
    }

    protected function beforeSave(Model $record, array $validated): void
    {
        $record->provider_id = $this->providerId;
        $record->category_id = $this->categoryId;
        $record->is_active = $this->isActive;
        $record->tagline = $this->tagline !== '' ? $this->tagline : null;
        $record->description = $this->description !== '' ? $this->description : null;
        $record->website = $this->website !== '' ? $this->website : null;

        if ($this->logo !== null) {
            $record->logo_path = $this->storeImage($this->logo, 'services', $record->logo_path);
        }
    }

    protected function beforeDelete(Model $record): bool
    {
        if ($record->transactions()->exists()) {
            Flux::toast(
                variant: 'danger',
                text: 'Layanan tidak dapat dihapus karena sudah pernah ditransaksikan. Nonaktifkan saja.'
            );

            return false;
        }

        $this->deleteImage($record->logo_path);

        return true;
    }

    public function render(): View
    {
        return view('admin::livewire.service-manager', [
            'services' => Service::query()
                ->with(['provider', 'category'])
                ->withCount('plans')
                ->orderBy('name')
                ->get(),
            'providers' => Provider::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }
}
