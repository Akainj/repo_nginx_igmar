<x-layouts::app.sidebar :title="$title ?? null">
    <main class="olimpo-main">
        {{ $slot }}
    </main>
    <x-server-badge />
</x-layouts::app.sidebar>
