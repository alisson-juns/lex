<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">⚖️ Audiências próximas</span>
        </x-slot>

        @php($hearings = $this->getHearings())

        @if ($hearings->isEmpty())
            <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                <x-heroicon-o-check-circle class="h-10 w-10 mb-2" />
                <span class="text-sm">Nenhuma audiência nos próximos 7 dias</span>
            </div>
        @else
            <div class="fi-card-grid">
                @foreach ($hearings as $h)
                    <x-dashboard-card
                        :url="$h['url']"
                        type="hearing"
                        :title="$h['description']"
                        :badge="$h['days_label']"
                    >
                        <div class="fi-card-row">
                            <span class="fi-card-label">Data:</span>
                            <span class="fi-card-strong">{{ $h['date'] }}</span>
                            @if ($h['time'])
                                <span class="fi-card-label"> às {{ $h['time'] }}</span>
                            @endif
                        </div>
                        @if ($h['location'])
                            <div class="fi-card-row"><span class="fi-card-label">Local:</span> {{ $h['location'] }}</div>
                        @endif
                        @if ($h['process'])
                            <div class="fi-card-row"><span class="fi-card-label">Processo:</span> {{ $h['process'] }}</div>
                        @endif
                        @if ($h['lawyer'])
                            <div class="fi-card-row"><span class="fi-card-label">Advogado:</span> {{ $h['lawyer'] }}</div>
                        @endif
                    </x-dashboard-card>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>