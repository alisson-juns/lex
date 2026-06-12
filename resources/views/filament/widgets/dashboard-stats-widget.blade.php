<x-filament-widgets::widget>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;"
    >
        @foreach ($this->getStats() as $stat)
            <a
                href="{{ $stat['url'] }}"
                style="
                    position: relative;
                    display: block;
                    overflow: hidden;
                    border-radius: 0.75rem;
                    padding: 1.25rem;
                    color: #fff;
                    box-shadow: 0 1px 2px rgba(0,0,0,.05);
                    transition: transform .2s, box-shadow .2s;
                    background-color: {{ $stat['color'] }};
                "
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 15px rgba(0,0,0,.1)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 1px 2px rgba(0,0,0,.05)'"
            >
                {{-- Ícone translúcido grande no canto --}}
                <x-dynamic-component
                    :component="$stat['icon']"
                    style="position:absolute; right:-0.75rem; bottom:-0.75rem; height:6rem; width:6rem; color:rgba(255,255,255,.2); pointer-events:none;"
                />

                <div style="position: relative; z-index: 10;">
                    <div style="font-size: 1.875rem; font-weight: 700; line-height: 1;">
                        {{ $stat['value'] }}
                    </div>
                    <div style="margin-top: 0.5rem; font-size: 0.875rem; font-weight: 600;">
                        {{ $stat['label'] }}
                    </div>
                    <div style="margin-top: 0.25rem; font-size: 0.75rem; color: rgba(255,255,255,.8);">
                        {{ $stat['sub'] }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>