<?php

namespace Modules\Admin\Livewire\Provider;

use App\Models\Category;
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
use Modules\Admin\Livewire\Provider\Concerns\ScopesToProvider;

/**
 * Pengelolaan layanan milik penyedia yang sedang masuk.
 *
 * Berbeda dari panel admin, penyedia tidak memilih penyedia pada formulir:
 * penyedia selalu diambil dari akun yang masuk, dan setiap pencarian record
 * dibatasi pada layanan miliknya. Penyedia juga tidak boleh menghapus layanan
 * yang sudah pernah ditransaksikan.
 */
#[Layout('layouts::admin')]
#[Title('Layanan Saya')]
class ServiceManager extends Component
{
    use ManagesResource, ScopesToProvider, StoresUploadedImages, WithFileUploads;

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
            'categoryId' => 'kategori',
            'name' => 'nama layanan',
            'tagline' => 'tagline',
            'description' => 'deskripsi',
            'website' => 'situs web',
            'isActive' => 'status aktif',
            'logo' => 'logo',
        ];
    }

    /**
     * Cari layanan hanya di antara milik penyedia yang sedang masuk.
     *
     * Id dari URL tidak pernah dipercaya: layanan milik penyedia lain akan
     * menghasilkan 404, bukan 403, agar keberadaannya tidak terbocorkan.
     */
    protected function findRecord(?int $id): Model
    {
        return $this->penyedia()
            ->services()
            ->findOrFail($id);
    }

    protected function newRecord(): Model
    {
        return new Service(['provider_id' => $this->penyedia()->id]);
    }

    protected function formDataFrom(Model $record): array
    {
        return [
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
        // Penyedia selalu dari akun yang masuk, bukan dari masukan formulir.
        $record->provider_id = $this->penyedia()->id;
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
        $provider = $this->penyedia();

        return view('admin::livewire.provider.services', [
            'provider' => $provider,
            'services' => $provider->services()
                ->with('category')
                ->withCount('plans')
                ->orderBy('name')
                ->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }
}
