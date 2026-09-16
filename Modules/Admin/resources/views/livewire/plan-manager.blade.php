<div class="space-y-6">
    <nav class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('admin.services.index') }}" class="hover:text-indigo-600" wire:navigate>Kelola Layanan</a>
        <span>/</span>
        <span class="font-medium text-zinc-800 dark:text-zinc-200">Paket {{ $service->name }}</span>
    </nav>

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Paket — {{ $service->name }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Penyedia: {{ $service->provider->name }}
            </p>
        </div>

        <flux:button wire:click="create" variant="filled" icon="plus">Tambah Paket</flux:button>
    </div>

    @if ($plans->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-600 dark:bg-zinc-800">
            <flux:icon.list-bullet class="mx-auto size-8 text-zinc-400" />
            <p class="mt-3 font-medium">Belum ada paket</p>
            <p class="mt-1 text-sm text-zinc-500">Layanan harus memiliki minimal satu paket agar dapat dibeli.</p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($plans as $plan)
                <article
                    wire:key="plan-{{ $plan->id }}"
                    class="flex flex-col rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-semibold">{{ $plan->name }}</h2>
                            <p class="mt-0.5 text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $plan->formattedPrice() }}
                            </p>
                        </div>

                        <flux:badge size="sm" :color="$plan->is_active ? 'green' : 'zinc'">
                            {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                        </flux:badge>
                    </div>

                    <dl class="mt-4 space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                        <div class="flex justify-between">
                            <dt>Durasi</dt>
                            <dd>{{ $plan->durationLabel() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Perangkat</dt>
                            <dd>{{ $plan->max_devices }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Terjual</dt>
                            <dd>{{ $plan->transactions_count }} transaksi</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Langganan</dt>
                            <dd>{{ $plan->subscriptions_count }}</dd>
                        </div>
                    </dl>

                    @if (! empty($plan->features))
                        <ul class="mt-3 flex-1 space-y-1 border-t border-zinc-100 pt-3 text-xs text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                            @foreach (array_slice($plan->features, 0, 4) as $feature)
                                <li>• {{ $feature }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-4 flex justify-end gap-1">
                        <flux:button wire:click="edit({{ $plan->id }})" variant="ghost" size="sm" icon="pencil" />
                        <flux:button wire:click="confirmDelete({{ $plan->id }})" variant="ghost" size="sm" icon="trash" />
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <x-admin::form-modal :title="$editingId ? 'Sunting Paket' : 'Tambah Paket'">
        <flux:input wire:model="name" label="Nama Paket" placeholder="Contoh: Premium 6 Bulan" required />

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:input wire:model="price" type="number" min="0" step="1000" label="Harga (Rp)" required />
            <flux:input wire:model="durationDays" type="number" min="1" label="Durasi (hari)" required />
            <flux:input wire:model="maxDevices" type="number" min="1" label="Maks. Perangkat" required />
        </div>

        <flux:textarea wire:model="description" label="Deskripsi Singkat" rows="2" />

        <flux:textarea
            wire:model="featuresText"
            label="Fitur"
            rows="5"
            description="Tulis satu fitur per baris."
            placeholder="Kualitas hingga 4K&#10;Tanpa iklan&#10;Unduh offline"
        />

        <flux:switch wire:model="isActive" label="Paket aktif dan dapat dibeli" />
    </x-admin::form-modal>

    <x-admin::confirm-delete message="Paket yang sudah pernah ditransaksikan tidak dapat dihapus. Nonaktifkan saja." />
</div>
