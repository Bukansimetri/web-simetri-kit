<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($staticUrls as $entry)
    <url>
        <loc>{{ $entry['loc'] }}</loc>
@if ($changefreq)
        <changefreq>{{ $changefreq }}</changefreq>
@endif
@if ($priority)
        <priority>{{ $priority }}</priority>
@endif
    </url>
@endforeach
@foreach ($items as $entry)
    <url>
        <loc>{{ $entry['loc'] }}</loc>
@if ($entry['lastmod'])
        <lastmod>{{ $entry['lastmod']->toAtomString() }}</lastmod>
@endif
@if ($changefreq)
        <changefreq>{{ $changefreq }}</changefreq>
@endif
@if ($priority)
        <priority>{{ $priority }}</priority>
@endif
    </url>
@endforeach
</urlset>
