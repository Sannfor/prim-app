<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-medium text-ink-strong">Kelola Layanan</h1>
            <p class="mt-1 text-sm text-muted">
                Layanan premium yang ditampilkan pada katalog PRIM.
            </p>
        </div>

        <flux:button wire:click="create" variant="filled" icon="plus">Tambah Layanan</flux:button>
    </div>

    @if ($services->isEmpty())
        <div class="rounded-xl border border-dashed border-line bg-white p-12 text-center">
            <flux:icon.squares-2x2 class="mx-auto size-8 text-muted-2" />
            <p class="mt-3 font-medium">Belum ada layanan</p>
            <p class="mt-1 text-sm text-muted">Tambahkan layanan untuk mulai mengisi katalog.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-xl bg-white shadow-brand-xs">
            <table class="w-full text-sm">
                <thead class="border-b border-line-soft bg-canvas text-left">
                    <tr>
                        <th class="p-3 font-medium">Layanan</th>
                        <th class="p-3 font-medium">Kategori</th>
                        <th class="p-3 font-medium">Penyedia</th>
                        <th class="p-3 font-medium">Paket</th>
                        <th class="p-3 font-medium">Status</th>
                        <th class="p-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr wire:key="service-{{ $service->id }}" class="border-b border-line-soft last:border-0">
                            <td class="p-3">
                                <p class="font-medium">{{ $service->name }}</p>
                                <p class="font-mono text-xs text-muted">{{ $service->slug }}</p>
                            </td>
                            <td class="p-3">{{ $service->category->name }}</td>
                            <td class="p-3">{{ $service->provider->name }}</td>
                            <td class="p-3">{{ $service->plans_count }}</td>
                            <td class="p-3">
                                <flux:badge size="sm" :color="$service->is_active ? 'green' : 'zinc'">
                                    {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                </flux:badge>
                            </td>
                            <td class="p-3">
                                <div class="flex justify-end gap-1">
                                    <flux:button
                                        :href="route('admin.plans.index', $service->slug)"
                                        variant="ghost"
                                        size="sm"
                                        icon="list-bullet"
                                        title="Kelola paket"
                                        wire:navigate
                                    />
                                    <flux:button wire:click="edit({{ $service->id }})" variant="ghost" size="sm" icon="pencil" />
                                    <flux:button wire:click="confirmDelete({{ $service->id }})" variant="ghost" size="sm" icon="trash" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <x-admin::form-modal :title="$editingId ? 'Sunting Layanan' : 'Tambah Layanan'">
        <flux:select wire:model="providerId" label="Penyedia" required>
            <flux:select.option value="">Pilih penyedia</flux:select.option>
            @foreach ($providers as $provider)
                <flux:select.option value="{{ $provider->id }}">{{ $provider->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="categoryId" label="Kategori" required>
            <flux:select.option value="">Pilih kategori</flux:select.option>
            @foreach ($categories as $category)
                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="name" label="Nama Layanan" placeholder="Contoh: Nusantara Film Premium" required />
        <flux:input wire:model="tagline" label="Tagline" placeholder="Ringkasan singkat dalam satu baris" />
        <flux:textarea wire:model="description" label="Deskripsi" rows="4" />
        <flux:input wire:model="website" type="url" label="Situs Web" placeholder="https://contoh.id" />

        <flux:switch wire:model="isActive" label="Tampilkan di katalog publik" />

        <div>
            <flux:input wire:model="logo" type="file" accept="image/*" label="Logo (opsional)" />
            <p class="mt-1 text-xs text-muted">Format gambar, maksimal 2 MB.</p>
            @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </x-admin::form-modal>

    <x-admin::confirm-delete message="Layanan yang sudah pernah ditransaksikan tidak dapat dihapus. Nonaktifkan saja." />
</div>
