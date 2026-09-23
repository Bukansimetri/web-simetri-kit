@props(['title', 'url'])

{{--
    Tombol berbagi konten (FR-022, FR-023). Hanya platform yang dipilih
    admin di Media Sosial yang dirender, dan setiap tombol membawa alamat
    konten yang sedang dibuka.
--}}
@php
    $socialShareSettings = app(\App\Settings\SocialSettings::class);
@endphp

@if ($socialShareSettings->share_buttons_enabled && count($socialShareSettings->share_platforms))
    @php
        $encodedUrl = urlencode($url);
        $encodedTitle = urlencode($title);

        $shareLinks = [
            'facebook' => ['label' => 'Facebook', 'icon' => 'thumb_up', 'href' => "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}"],
            'twitter' => ['label' => 'Twitter/X', 'icon' => 'tag', 'href' => "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}"],
            'linkedin' => ['label' => 'LinkedIn', 'icon' => 'work', 'href' => "https://www.linkedin.com/sharing/share-offsite/?url={$encodedUrl}"],
            'pinterest' => ['label' => 'Pinterest', 'icon' => 'push_pin', 'href' => "https://pinterest.com/pin/create/button/?url={$encodedUrl}&description={$encodedTitle}"],
            'reddit' => ['label' => 'Reddit', 'icon' => 'forum', 'href' => "https://reddit.com/submit?url={$encodedUrl}&title={$encodedTitle}"],
            'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'chat', 'href' => "https://wa.me/?text={$encodedTitle}%20{$encodedUrl}"],
            'telegram' => ['label' => 'Telegram', 'icon' => 'send', 'href' => "https://t.me/share/url?url={$encodedUrl}&text={$encodedTitle}"],
            'email' => ['label' => 'Email', 'icon' => 'mail', 'href' => "mailto:?subject={$encodedTitle}&body={$encodedUrl}"],
        ];
    @endphp

    <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-on-surface-variant">Bagikan:</span>
        @foreach ($socialShareSettings->share_platforms as $platformKey)
            @continue(! isset($shareLinks[$platformKey]))
            @php $shareLink = $shareLinks[$platformKey]; @endphp
            <a href="{{ $shareLink['href'] }}" target="_blank" rel="noopener noreferrer"
               aria-label="Bagikan ke {{ $shareLink['label'] }}"
               class="w-9 h-9 rounded-full bg-surface-container-high text-primary hover:bg-primary hover:text-white flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-lg">{{ $shareLink['icon'] }}</span>
            </a>
        @endforeach
    </div>
@endif
