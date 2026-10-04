<x-layouts::admin
    :title="$title ?? null"
    :heading="$heading ?? null"
    :subheading="$subheading ?? null"
>
    {{ $slot }}
</x-layouts::admin>
