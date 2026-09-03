@props(['items'])

<div class="space-y-4">
    @foreach ($items as $item)
        <div x-data="{ open: false }" class="bg-white border border-outline-variant/20 rounded-lg overflow-hidden">
            <button
                type="button"
                @click="open = !open"
                class="w-full flex items-center justify-between gap-4 p-6 text-left"
                :aria-expanded="open"
            >
                <h3 class="font-headline-lg text-headline-lg text-body-md font-semibold text-on-surface">{{ $item->question }}</h3>
                <span class="material-symbols-outlined text-primary transition-transform shrink-0" :class="open && 'rotate-180'">expand_more</span>
            </button>
            <div x-show="open" x-cloak x-transition class="px-6 pb-6">
                <p class="font-body-md text-body-md text-on-surface-variant">{{ $item->answer }}</p>
            </div>
        </div>
    @endforeach
</div>
