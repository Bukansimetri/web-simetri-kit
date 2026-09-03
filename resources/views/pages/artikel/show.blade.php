@extends('layouts.public')

@section('title', $article->title.' — '.(app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name')))
@section('meta_description', $article->excerpt)

@section('content')
    <article class="pt-32 pb-24 px-6 max-w-3xl mx-auto">
        <p class="text-sm font-semibold text-primary/70 uppercase tracking-widest mb-4">
            <a href="{{ url('/artikel') }}" class="hover:text-primary transition-colors">Artikel</a>
            <span class="mx-2">/</span>
            {{ str($article->category)->title() }}
        </p>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">{{ $article->title }}</h1>
        <p class="text-sm text-outline mb-8">{{ $article->published_at?->translatedFormat('d F Y') }}</p>

        <div class="aspect-video w-full bg-surface-container rounded-lg mb-10"></div>

        <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed space-y-4">
            @foreach (explode("\n", $article->content) as $paragraph)
                @if (trim($paragraph) !== '')
                    <p>{{ trim($paragraph) }}</p>
                @endif
            @endforeach
        </div>
    </article>
@endsection
