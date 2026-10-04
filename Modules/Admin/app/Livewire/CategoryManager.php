<?php

namespace Modules\Admin\Livewire;

use App\Models\Category;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Admin\Livewire\Concerns\ManagesResource;

/**
 * Pengelolaan kategori layanan premium.
 */
#[Layout('layouts::admin')]
#[Title('Kelola Kategori')]
class CategoryManager extends Component
{
    use ManagesResource;

    /**
     * Ikon Heroicons yang boleh dipakai kategori.
     *
     * @var list<string>
     */
    public const ICONS = [
        'film', 'musical-note', 'book-open', 'briefcase', 'paint-brush',
        'code-bracket', 'shield-check', 'cloud', 'device-phone-mobile',
        'academic-cap', 'chart-bar', 'sparkles',
    ];

    public string $name = '';

    public string $icon = '';

    public string $description = '';

    protected function defaultFormData(): array
    {
        return ['name' => '', 'icon' => '', 'description' => ''];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', $this->uniqueNameRule()],
            'icon' => ['nullable', 'string', 'in:'.implode(',', self::ICONS)],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Aturan nama unik, mengabaikan record yang sedang disunting.
     */
    private function uniqueNameRule(): Unique
    {
        $rule = Rule::unique('categories', 'name');

        return $this->editingId === null ? $rule : $rule->ignore($this->editingId);
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama kategori',
            'icon' => 'ikon',
            'description' => 'deskripsi',
        ];
    }

    protected function findRecord(?int $id): Model
    {
        return Category::query()->findOrFail($id);
    }

    protected function newRecord(): Model
    {
        return new Category;
    }

    protected function formDataFrom(Model $record): array
    {
        return [
            'name' => $record->name,
            'icon' => (string) $record->icon,
            'description' => (string) $record->description,
        ];
    }

    protected function beforeDelete(Model $record): bool
    {
        if ($record->services()->exists()) {
            Flux::toast(
                variant: 'danger',
                text: 'Kategori tidak dapat dihapus karena masih dipakai layanan.'
            );

            return false;
        }

        return true;
    }

    public function render(): View
    {
        return view('admin::livewire.category-manager', [
            'categories' => Category::query()->withCount('services')->orderBy('name')->get(),
            'icons' => self::ICONS,
        ]);
    }
}
