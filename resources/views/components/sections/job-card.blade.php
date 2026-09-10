@props(['job'])

<div class="bg-white rounded-lg p-6 border border-surface-container-low flex flex-col md:flex-row md:items-center justify-between gap-4 hover:shadow-md transition-shadow">
    <div>
        <div class="flex flex-wrap items-center gap-3 mb-2">
            <h3 class="font-headline-lg text-lg text-on-surface">{{ $job->title }}</h3>
            <span class="bg-surface-container-high text-on-surface text-xs px-2 py-1 rounded-md font-label-bold text-label-bold">
                {{ \App\Models\JobOpening::EMPLOYMENT_TYPES[$job->employment_type] ?? \Illuminate\Support\Str::of($job->employment_type)->replace('-', ' ')->title() }}
            </span>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-sm">location_on</span> {{ $job->location }}
        </p>
        <p class="font-body-sm text-body-sm text-on-surface-variant max-w-2xl line-clamp-2">{{ $job->description }}</p>
    </div>

    <a href="{{ url('/kontak') }}" class="shrink-0 border border-primary-container text-primary-container px-5 py-2.5 rounded-lg font-label-bold text-label-bold text-center hover:bg-primary-container hover:text-white transition-colors">
        Lamar Sekarang
    </a>
</div>
