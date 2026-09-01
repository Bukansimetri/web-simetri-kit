@props(['job'])

<div class="bg-white border border-outline-variant/20 rounded-lg p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 hover:shadow-md transition-shadow">
    <div>
        <h3 class="font-headline-lg text-headline-lg text-lg text-primary mb-1">{{ $job->title }}</h3>
        <div class="flex flex-wrap gap-4 text-sm text-on-surface-variant">
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-base">location_on</span> {{ $job->location }}
            </span>
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-base">work</span> {{ str($job->employment_type)->replace('-', ' ')->title() }}
            </span>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-3 max-w-2xl">{{ $job->description }}</p>
    </div>

    <a href="{{ url('/kontak') }}" class="shrink-0 bg-primary-container text-white font-label-bold text-label-bold px-6 py-3 rounded-lg text-center hover:shadow-md transition-all">
        Lamar Sekarang
    </a>
</div>
