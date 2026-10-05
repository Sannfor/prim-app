@props(['status' => null, 'label' => null, 'tone' => null])

@php
    /*
     | Lencana status sesuai palet desain Figma:
     | Selesai #EAF3DE/#3B6D11, Diproses #E6F1FB/#0C447C,
     | Menunggu #FAEEDA/#854F0B, Dibatalkan #FCEBEB/#E24B4A, Baru #EEEDFE/#534AB7
     */
    $resolvedLabel = $label;
    $resolvedTone = $tone;

    $enumStatuses = [
        \App\Enums\TransactionStatus::class,
        \App\Enums\UserStatus::class,
        \App\Enums\SubscriptionStatus::class,
    ];

    if ($status !== null && in_array($status::class, $enumStatuses, true)) {
        $resolvedLabel ??= $status->label();
        $resolvedTone ??= $status->tone();
    } elseif (is_string($status)) {
        $enum = \App\Enums\TransactionStatus::tryFrom($status)
            ?? \App\Enums\UserStatus::tryFrom($status)
            ?? \App\Enums\SubscriptionStatus::tryFrom($status);

        if ($enum) {
            $resolvedLabel ??= $enum->label();
            $resolvedTone ??= $enum->tone();
        } else {
            $resolvedLabel ??= $status;
        }
    }

    $resolvedTone ??= 'new';

    $palette = [
        'done' => 'bg-status-done-bg text-status-done-fg',
        'process' => 'bg-status-process-bg text-status-process-fg',
        'wait' => 'bg-status-wait-bg text-status-wait-fg',
        'cancel' => 'bg-status-cancel-bg text-status-cancel-fg',
        'new' => 'bg-status-new-bg text-status-new-fg',
    ];

    $classes = $palette[$resolvedTone] ?? $palette['new'];
@endphp

<span {{ $attributes->merge(['class' => 'prim-badge ' . $classes]) }}>
    {{ $resolvedLabel }}
</span>
