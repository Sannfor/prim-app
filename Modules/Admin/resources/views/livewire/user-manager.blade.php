<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Kelola Pengguna</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            Cari pengguna, lihat aktivitasnya, dan atur perannya pada platform.
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="Cari nama, email, atau nomor telepon…"
            class="max-w-sm"
            clearable
        />

        <flux:select wire:model.live="roleFilter" class="max-w-48">
            <flux:select.option value="">Semua peran</flux:select.option>
            @foreach ($roles as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-left dark:border-zinc-700 dark:bg-zinc-900">
                <tr>
                    <th class="p-3 font-medium">Pengguna</th>
                    <th class="p-3 font-medium">Telepon</th>
                    <th class="p-3 font-medium">Transaksi</th>
                    <th class="p-3 font-medium">Langganan</th>
                    <th class="p-3 font-medium">Peran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold dark:bg-zinc-700">
                                    {{ $user->initials() }}
                                </span>
                                <div>
                                    <p class="font-medium">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="text-xs text-zinc-500">(Anda)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="p-3">{{ $user->phone ?: '—' }}</td>
                        <td class="p-3">{{ $user->transactions_count }}</td>
                        <td class="p-3">{{ $user->subscriptions_count }}</td>
                        <td class="p-3">
                            <flux:select
                                wire:change="changeRole({{ $user->id }}, $event.target.value)"
                                class="max-w-44"
                                size="sm"
                            >
                                @foreach ($roles as $value => $label)
                                    <flux:select.option value="{{ $value }}" :selected="$user->role->value === $value">
                                        {{ $label }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-zinc-500">Tidak ada pengguna yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $users->links() }}
    </div>

    <p class="text-xs text-zinc-500">
        Peran <strong>Administrator</strong> dapat mengakses seluruh panel pengelola,
        <strong>Penyedia Layanan</strong> mewakili pihak pemasok katalog, dan
        <strong>Pelanggan</strong> hanya dapat berbelanja serta melihat langganannya.
    </p>
</div>
