<?php

namespace Modules\Admin\Livewire\Provider;

use App\Models\Plan;
use App\Models\Service;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Admin\Livewire\Concerns\ManagesResource;
use Modules\Admin\Livewire\Provider\Concerns\ScopesToProvider;

/**
 * Pengelolaan paket pada satu layanan milik penyedia yang sedang masuk.
 *
 * Segmen URL hanya berisi slug layanan. Layannya dicari di antara milik
 * penyedia ini, sehingga slug milik penyedia lain menghasilkan 404. Seluruh
 * paket yang dikelola pun selalu dibatasi pada layanan tersebut.
 */
#[Layout('layouts::admin')]
#[Title('Kelola Paket')]
class PlanManager extends Component
{
    use ManagesResource, ScopesToProvider;

    public int $serviceId = 0;

    public string $name = '';

    public int $price = 0;

    public int $durationDays = 30;

    public int $maxDevices = 1;

    public string $description = '';

    public string $featuresText = '';

    public bool $isActive = true;

    /**
     * Muat layanan dari segmen URL, dibatasi pada milik penyedia ini.
     */
    public function mount(string $service): void
    {
        $this->serviceId = $this->penyedia()
            ->services()
            ->where('slug', $service)
            ->firstOrFail()
            ->id;
    }

    /**
     * Layanan yang sedang dikelola.
     */
    private function service(): Service
    {
        return $this->penyedia()
            ->services()
            ->with('category')
            ->findOrFail($this->serviceId);
    }

    protected function defaultFormData(): array
    {
        return [
            'name' => '',
            'price' => 0,
            'durationDays' => 30,
            'maxDevices' => 1,
            'description' => '',
            'featuresText' => '',
            'isActive' => true,
        ];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'durationDays' => ['required', 'integer', 'min:1', 'max:3650'],
            'maxDevices' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'featuresText' => ['nullable', 'string', 'max:1000'],
            'isActive' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama paket',
            'price' => 'harga',
            'durationDays' => 'durasi (hari)',
            'maxDevices' => 'jumlah perangkat',
            'description' => 'deskripsi',
            'featuresText' => 'fitur',
            'isActive' => 'status aktif',
        ];
    }

    /**
     * Pastikan paket yang disunting memang milik layanan penyedia ini.
     */
    protected function findRecord(?int $id): Model
    {
        return Plan::query()->where('service_id', $this->serviceId)->findOrFail($id);
    }

    protected function newRecord(): Model
    {
        return new Plan(['service_id' => $this->serviceId]);
    }

    protected function formDataFrom(Model $record): array
    {
        return [
            'name' => $record->name,
            'price' => $record->price,
            'durationDays' => $record->duration_days,
            'maxDevices' => $record->max_devices,
            'description' => (string) $record->description,
            'featuresText' => implode(PHP_EOL, $record->features ?? []),
            'isActive' => $record->is_active,
        ];
    }

    protected function beforeSave(Model $record, array $validated): void
    {
        $record->service_id = $this->serviceId;
        $record->duration_days = $this->durationDays;
        $record->max_devices = $this->maxDevices;
        $record->is_active = $this->isActive;
        $record->description = $this->description !== '' ? $this->description : null;

        // Fitur diisi satu per baris pada textarea, lalu dirapikan menjadi array.
        $record->features = collect(preg_split('/\r\n|\r|\n/', $this->featuresText))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    protected function beforeDelete(Model $record): bool
    {
        if ($record->subscriptions()->exists() || $record->transactions()->exists()) {
            Flux::toast(
                variant: 'danger',
                text: 'Paket tidak dapat dihapus karena sudah pernah ditransaksikan. Nonaktifkan saja.'
            );

            return false;
        }

        if (Plan::query()->where('service_id', $this->serviceId)->count() <= 1) {
            Flux::toast(
                variant: 'danger',
                text: 'Layanan harus memiliki minimal satu paket.'
            );

            return false;
        }

        return true;
    }

    public function render(): View
    {
        $service = $this->service();

        return view('admin::livewire.provider.plans', [
            'service' => $service,
            'plans' => Plan::query()
                ->where('service_id', $this->serviceId)
                ->withCount(['subscriptions', 'transactions'])
                ->orderBy('price')
                ->get(),
        ])->title('Kelola Paket — '.$service->name);
    }
}
