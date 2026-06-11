<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">📋 Agendamentos próximos</span>
        </x-slot>

        @php($tasks = $this->getTasks())

        @if ($tasks->isEmpty())
            <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                <x-heroicon-o-check-circle class="h-10 w-10 mb-2" />
                <span class="text-sm">Nenhum agendamento nos próximos 7 dias</span>
            </div>
        @else
            <div class="fi-card-grid">
                @foreach ($tasks as $t)
                    <x-dashboard-card
                        :url="$t['url']"
                        type="task"
                        :title="$t['title']"
                        :badge="$t['days_label']"
                    >
                        <div class="fi-card-row">
                            <span class="fi-card-label">Data:</span>
                            <span class="fi-card-strong">{{ $t['date'] }}</span>
                            @if ($t['time'])
                                <span class="fi-card-label"> às {{ $t['time'] }}</span>
                            @endif
                        </div>
                        @if ($t['process'])
                            <div class="fi-card-row"><span class="fi-card-label">Processo:</span> {{ $t['process'] }}</div>
                        @endif
                        @if ($t['lawyers'])
                            <div class="fi-card-row"><span class="fi-card-label">Advogado(s):</span> {{ $t['lawyers'] }}</div>
                        @endif
                    </x-dashboard-card>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>