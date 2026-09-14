@props(['location'])

@php
    $items = \App\Models\MenuItem::treeForLocation($location);
@endphp

@if ($items->isNotEmpty())
    <ul {{ $attributes->class(['flex flex-col gap-3']) }}>
        @foreach ($items as $item)
            <li>
                @if ($item['href'])
                    <a href="{{ $item['href'] }}" target="{{ $item['target'] }}" @if ($item['target'] === '_blank') rel="noopener noreferrer" @endif class="hover:text-primary-container transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span>{{ $item['label'] }}</span>
                @endif

                @if (! empty($item['children']))
                    <ul class="pl-4 mt-2 flex flex-col gap-2">
                        @foreach ($item['children'] as $child)
                            <li>
                                @if ($child['href'])
                                    <a href="{{ $child['href'] }}" target="{{ $child['target'] }}" @if ($child['target'] === '_blank') rel="noopener noreferrer" @endif class="hover:text-primary-container transition-colors">
                                        {{ $child['label'] }}
                                    </a>
                                @else
                                    <span>{{ $child['label'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
@endif
