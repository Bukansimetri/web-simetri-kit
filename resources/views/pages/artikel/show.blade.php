@extends('layouts.public')

@section('title', $article->title.' — '.(app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name')))
@section('meta_description', $article->excerpt)

@section('content')
    <article class="pt-32 pb-24 px-6 max-w-3xl mx-auto">
        <p class="text-sm font-semibold text-primary/70 uppercase tracking-widest mb-4">
            <a href="{{ url('/artikel') }}" class="hover:text-primary transition-colors">Artikel</a>
            <span class="mx-2">/</span>
            {{ $article->articleCategory->name }}
        </p>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">{{ $article->title }}</h1>
        <p class="text-sm text-outline mb-8">
            {{ $article->published_at?->translatedFormat('d F Y') }}
            @if ($article->redaksi)
                <span class="mx-2">&middot;</span>
                {{ $article->redaksi }}
            @endif
        </p>

        <div class="aspect-video w-full bg-surface-container rounded-lg mb-10 overflow-hidden">
            @if ($article->image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
            @else
                <div data-article-image-placeholder class="w-full h-full flex items-center justify-center text-outline">
                    <span class="material-symbols-outlined text-6xl">image</span>
                </div>
            @endif
        </div>

        <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed space-y-4">
            {!! $article->content !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <div class="flex flex-wrap gap-2 mt-10">
                @foreach ($article->tags as $tag)
                    <span class="bg-surface-variant text-on-surface-variant px-3 py-1 rounded-full text-xs font-label-bold text-label-bold">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </article>
@endsection
