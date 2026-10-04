<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-medium text-ink-strong">Kelola Kategori</h1>
            <p class="mt-1 text-sm text-muted">
                Kategori dipakai untuk menyaring katalog dan membandingkan layanan sejenis.
            </p>
        </div>

        <flux:button wire:click="create" variant="filled" icon="plus">Tambah Kategori</flux:button>
    </div>

    @if ($categories->isEmpty())
        <div class="rounded-xl border border-dashed border-line bg-white p-12 text-center">
            <flux:icon.squares-2x2 class="mx-auto size-8 text-muted-2" />
            <p class="mt-3 font-medium">Belum ada kategori</p>
            <p class="mt-1 text-sm text-muted">Tambahkan kategori agar layanan dapat dikelompokkan.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <article
                    wire:key="category-{{ $category->id }}"
                    class="flex flex-col rounded-xl bg-canvas p-5"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-lg bg-brand-soft dark:bg-indigo-950">
                                <flux:icon :name="$category->icon ?: 'squares-2x2'" class="size-5 text-brand" />
                            </span>
                            <div>
                                <h2 class="font-semibold">{{ $category->name }}</h2>
                                <p class="font-mono text-xs text-muted">{{ $category->slug }}</p>
                            </div>
                        </div>

                        <div class="flex gap-1">
                            <flux:button wire:click="edit({{ $category->id }})" variant="ghost" size="sm" icon="pencil" />
                            <flux:button wire:click="confirmDelete({{ $category->id }})" variant="ghost" size="sm" icon="trash" />
                        </div>
                    </div>

                    @if ($category->description)
                        <p class="mt-3 flex-1 text-sm text-muted">{{ $category->description }}</p>
                    @endif

                    <flux:badge size="sm" color="zinc" class="mt-4 self-start">
                        {{ $category->services_count }} layanan
                    </flux:badge>
                </article>
            @endforeach
        </div>
    @endif

    <x-admin::form-modal :title="$editingId ? 'Sunting Kategori' : 'Tambah Kategori'">
        <flux:input wire:model="name" label="Nama Kategori" placeholder="Contoh: Hiburan & Streaming" required />

        <flux:select wire:model="icon" label="Ikon" description="Ikon ditampilkan pada kartu kategori.">
            <flux:select.option value="">Tanpa ikon</flux:select.option>
            @foreach ($icons as $icon)
                <flux:select.option value="{{ $icon }}">{{ $icon }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:textarea wire:model="description" label="Deskripsi" rows="3" />
    </x-admin::form-modal>

    <x-admin::confirm-delete message="Kategori yang masih dipakai layanan tidak dapat dihapus." />
</div>
