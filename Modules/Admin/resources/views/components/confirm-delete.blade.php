@props(['message' => 'Data yang dihapus tidak dapat dikembalikan.'])

<flux:modal wire:model.self="confirmingDelete" class="w-full max-w-md">
    <div class="space-y-5">
        <div>
            <flux:heading size="lg">Konfirmasi Hapus</flux:heading>
            <flux:subheading>{{ $message }}</flux:subheading>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button wire:click="cancelDelete" variant="ghost">Batal</flux:button>
            <flux:button wire:click="delete" variant="danger" icon="trash">Hapus</flux:button>
        </div>
    </div>
</flux:modal>
