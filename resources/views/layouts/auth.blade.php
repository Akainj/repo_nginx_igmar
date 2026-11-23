<x-layouts::auth.simple :title="$title ?? null">
    {{ $slot }}
    <x-server-badge />
</x-layouts::auth.simple>
