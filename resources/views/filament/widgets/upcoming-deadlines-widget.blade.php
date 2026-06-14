<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-danger-500" />
                Prazos fatais próximos
            </span>
    </x-slot>

        @php($deadlines = $this->getDeadlines())

        @if ($deadlines->isEmpty())
            <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                <x-heroicon-o-check-circle class="h-10 w-10 mb-2" />
                <span class="text-sm">Nenhum prazo fatal nos próximos 7 dias</span>
            </div>
        @else
            <div class="fi-card-grid">
                @foreach ($deadlines as $d)
                    <x-dashboard-card
                        :url="$d['url']"
                        type="deadline"
                        :title="$d['type']"
                        :badge="$d['days_label']"
                    >
                        <div class="fi-card-row">
                            <span class="fi-card-label">Prazo fatal:</span>
                            <span class="fi-card-strong">{{ $d['fatal_date'] }}</span>
                            @if ($d['internal_date'])
                                <span class="fi-card-label"> · Interno: {{ $d['internal_date'] }}</span>
                            @endif
                        </div>
                        @if ($d['process'])
                            <div class="fi-card-row"><span class="fi-card-label">Processo:</span> {{ $d['process'] }}</div>
                        @endif
                        @if ($d['lawyers'])
                            <div class="fi-card-row"><span class="fi-card-label">Advogado(s):</span> {{ $d['lawyers'] }}</div>
                        @endif
                    </x-dashboard-card>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>