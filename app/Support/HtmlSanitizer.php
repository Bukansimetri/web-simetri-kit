<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitasi HTML berbasis allowlist tanpa dependency baru (Principle V,
 * research.md R4). Dipakai untuk konten trust bar Banner, yang kemungkinan
 * besar ditempel dari sumber luar (potongan lencana, widget ulasan).
 *
 * Tag di luar allowlist dibuang namun teks/anaknya dipertahankan (unwrap).
 * Seluruh atribut `on*` dibuang. Skema `href`/`src` dibatasi pada `http`,
 * `https`, `mailto`, `tel`, dan path relatif — skema lain (mis.
 * `javascript:`) menyebabkan atribut tsb dibuang, bukan seluruh elemen.
 */
class HtmlSanitizer
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'span', 'div', 'strong', 'b', 'em', 'i', 'u', 'a', 'img', 'ul', 'ol', 'li', 'small',
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_ATTRIBUTES = [
        'class', 'href', 'src', 'alt', 'title', 'target', 'rel', 'width', 'height',
    ];

    /**
     * @var array<int, string>
     */
    private const URL_ATTRIBUTES = ['href', 'src'];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /**
     * Bersihkan `$html` menurut allowlist di atas. Masukan null atau kosong
     * menghasilkan string kosong.
     */
    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument;

        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><div id="__sanitizer_root__">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('__sanitizer_root__');

        if (! $root instanceof DOMElement) {
            return '';
        }

        self::sanitizeChildren($root, $dom);

        $result = '';

        foreach (iterator_to_array($root->childNodes) as $child) {
            $result .= $dom->saveHTML($child);
        }

        return trim($result);
    }

    /**
     * Sanitasi seluruh anak `$node` secara rekursif: elemen di luar
     * allowlist di-unwrap (diganti dengan anaknya sendiri), elemen yang
     * diizinkan dibersihkan atributnya.
     */
    private static function sanitizeChildren(DOMNode $node, DOMDocument $dom): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            self::sanitizeChildren($child, $dom);

            $tag = strtolower($child->tagName);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                self::unwrap($child, $node);

                continue;
            }

            self::sanitizeAttributes($child);
        }
    }

    /**
     * Pindahkan seluruh anak `$element` ke posisi `$element` pada `$parent`,
     * lalu buang `$element` itu sendiri — mempertahankan teks/anak tanpa
     * tag terlarangnya.
     */
    private static function unwrap(DOMElement $element, DOMNode $parent): void
    {
        foreach (iterator_to_array($element->childNodes) as $grandChild) {
            $parent->insertBefore($grandChild, $element);
        }

        $parent->removeChild($element);
    }

    /**
     * Buang seluruh atribut di luar allowlist (termasuk seluruh `on*`), dan
     * validasi skema pada atribut URL (`href`/`src`).
     */
    private static function sanitizeAttributes(DOMElement $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);

            if (str_starts_with($name, 'on') || ! in_array($name, self::ALLOWED_ATTRIBUTES, true)) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (in_array($name, self::URL_ATTRIBUTES, true) && ! self::hasAllowedScheme($attribute->value)) {
                $element->removeAttribute($attribute->name);
            }
        }
    }

    /**
     * Benar bila `$value` adalah path relatif atau memakai skema yang
     * diizinkan (`http`, `https`, `mailto`, `tel`).
     */
    private static function hasAllowedScheme(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return true;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        if ($scheme === null) {
            return true;
        }

        return in_array(strtolower($scheme), self::ALLOWED_SCHEMES, true);
    }
}
