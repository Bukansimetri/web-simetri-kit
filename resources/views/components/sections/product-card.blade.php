@props(['product'])

<div class="bg-surface-container-lowest p-6 shadow-md border border-surface-container-high rounded-lg hover:-translate-y-1 transition-transform duration-300 flex flex-col h-full">
    <div class="aspect-video w-full rounded-lg overflow-hidden mb-6 bg-surface-container">
        @if ($product->coverImageUrl())
            <img src="{{ $product->coverImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        @else
            <div data-product-image-placeholder class="w-full h-full flex items-center justify-center text-outline">
                <span class="material-symbols-outlined text-4xl">image</span>
            </div>
        @endif
    </div>

    <span class="self-start bg-surface-variant text-on-surface-variant px-3 py-1 rounded-full font-label-bold text-label-bold uppercase tracking-wider text-[11px] mb-4">
        {{ $product->category->name }}
    </span>

    <h3 class="font-headline-lg text-headline-lg text-primary-container mb-3">{{ $product->name }}</h3>
    <p class="font-body-md text-body-md text-on-surface-variant mb-6 line-clamp-3">{{ $product->short_description }}</p>

    <div class="flex items-center gap-3 mb-6">
        <span class="text-headline-lg font-headline-lg text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
        @if ($product->strikethrough_price)
            <span class="text-body-sm font-body-sm text-outline-variant line-through">Rp {{ number_format($product->strikethrough_price, 0, ',', '.') }}</span>
        @endif
    </div>

    <a href="{{ url('/produk/'.$product->slug) }}" class="inline-flex items-center gap-2 text-primary-container font-label-bold text-label-bold font-semibold hover:text-secondary-container transition-colors mt-auto w-fit">
        Lihat detail <span class="material-symbols-outlined">arrow_right_alt</span>
    </a>
</div>
