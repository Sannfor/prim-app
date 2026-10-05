<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts::settings', ['title' => 'Tampilan'])] class extends Component {
    //
}; ?>

<div>
    <x-settings.layout heading="Tampilan" subheading="Pilih tema antarmuka yang paling nyaman untukmu.">
        <div class="rounded-xl bg-white p-6 shadow-brand-xs">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">Terang</flux:radio>
                <flux:radio value="dark" icon="moon">Gelap</flux:radio>
                <flux:radio value="system" icon="computer-desktop">Ikut Sistem</flux:radio>
            </flux:radio.group>

            <p class="mt-4 text-xs leading-relaxed text-muted">
                Pilihan ini disimpan pada peramban yang kamu pakai. Mengubahnya tidak
                memengaruhi tampilan pada perangkat lain.
            </p>
        </div>
    </x-settings.layout>
</div>
