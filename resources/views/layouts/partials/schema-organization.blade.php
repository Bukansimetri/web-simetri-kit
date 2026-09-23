{{--
    JSON-LD Organization global (AMC-223 FR-008). $site, $appearance, dan
    $social disediakan oleh layouts/public.blade.php.
--}}
<x-seo.json-ld :schema="\App\Support\Seo\JsonLd::organization($site, $appearance, $social)" />
