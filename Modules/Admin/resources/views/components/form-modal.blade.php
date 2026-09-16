@props(['title', 'submitLabel' => 'Simpan'])

<flux:modal wire:model.self="showModal" class="w-full max-w-lg">
    <form wire:submit="save" class="space-y-5">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            <flux:subheading>Lengkapi data di bawah ini lalu simpan.</flux:subheading>
        </div>

        {{ $slot }}

        <div class="flex justify-end gap-2 pt-2">
            <flux:button type="button" wire:click="closeModal" variant="ghost">Batal</flux:button>
            <flux:button type="submit" variant="filled" icon="check">{{ $submitLabel }}</flux:button>
        </div>
    </form>
</flux:modal>
