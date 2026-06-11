@props([
    'url',
    'type',        // 'deadline' | 'task' | 'hearing'
    'title',       // texto do header
    'badge' => null, // texto do badge à direita (ex: "Faltam 3 dias")
])

<a href="{{ $url }}" class="fi-card">
    <div class="fi-card-header fi-card-header--{{ $type }}">
        <div class="fi-card-header-row">
            <span class="fi-card-title">{{ $title }}</span>
            @if ($badge)
                <span class="fi-card-badge">{{ $badge }}</span>
            @endif
        </div>
    </div>
    <div class="fi-card-body">
        <div class="grid gap-1">
            {{ $slot }}
        </div>
    </div>
</a>