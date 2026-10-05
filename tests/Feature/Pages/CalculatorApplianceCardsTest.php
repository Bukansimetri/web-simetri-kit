<?php

namespace Tests\Feature\Pages;

use App\Models\ElectricityAppliance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kartu peralatan di kalkulator beranda (spec 033): stepper seragam, teks ringan, nama panjang aman.
 * Kartu dirender Alpine di browser, jadi tes memeriksa template-nya; ukuran nyata diukur di browser saat verifikasi.
 */
class CalculatorApplianceCardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ElectricityAppliance::query()->delete();
        ElectricityAppliance::factory()->create(['name' => 'TV', 'slug' => 'tv', 'watt' => 100]);
        ElectricityAppliance::factory()->create(['name' => 'Mesin Cuci 2 Tabung', 'slug' => 'mesin-cuci', 'watt' => 160]);
        ElectricityAppliance::factory()->create(['name' => 'Peralatan Dapur Serbaguna Berkapasitas Besar Sekali', 'slug' => 'panjang', 'watt' => 1500, 'icon_image' => 'appliances/kustom.webp']);
    }

    /**
     * Potongan template kartu: dari `<template x-for="item in appliances"` sampai penutup `</template>` pasangannya.
     */
    private function cardTemplate(): string
    {
        $html = $this->get('/')->assertOk()->getContent();
        $start = strpos($html, '<template x-for="item in appliances"');

        $this->assertNotFalse($start, 'Template kartu peralatan tidak ditemukan.');

        $depth = 0;
        $offset = $start;

        while (preg_match('#<template\b|</template>#', $html, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $depth += str_starts_with($m[0][0], '</') ? -1 : 1;
            $offset = $m[0][1] + strlen($m[0][0]);

            if ($depth === 0) {
                return substr($html, $start, $offset - $start);
            }
        }

        $this->fail('Penutup template kartu tidak ditemukan.');
    }

    /**
     * Daftar kelas elemen pertama yang atributnya memuat penanda.
     *
     * @return list<string>
     */
    private function classesOf(string $snippet, string $marker): array
    {
        $this->assertSame(1, preg_match('#<[a-z]+\b[^>]*'.preg_quote($marker, '#').'[^>]*>#', $snippet, $tag), "Elemen dengan {$marker} tidak ditemukan.");
        $this->assertSame(1, preg_match('#\bclass="([^"]*)"#', $tag[0], $cls), "Elemen {$marker} tanpa class.");

        return preg_split('/\s+/', trim($cls[1]));
    }

    public function test_catalog_is_rendered_for_the_alpine_template_including_short_long_and_custom_icon_items(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Mesin Cuci 2 Tabung', $html);
        $this->assertStringContainsString('Peralatan Dapur Serbaguna Berkapasitas Besar Sekali', $html);
        $this->assertStringContainsString('kustom.webp', $html);
    }

    public function test_quantity_behaviour_is_unchanged(): void
    {
        $card = $this->cardTemplate();

        $this->assertStringContainsString('item.qty = Math.max(0, item.qty - 1); resetResult()', $card);
        $this->assertStringContainsString('item.qty++; resetResult()', $card);
        $this->assertStringContainsString('@input="resetResult()"', $card);
        $this->assertStringContainsString('x-model.number="item.qty"', $card);
        $this->assertStringContainsString('min="0"', $card);
    }

    public function test_stepper_has_a_fixed_size_that_cannot_shrink(): void
    {
        $classes = $this->classesOf($this->cardTemplate(), 'grid-cols-[1.75rem_1fr_1.75rem]');

        foreach (['shrink-0', 'w-20', 'h-8'] as $required) {
            $this->assertContains($required, $classes, "Stepper tanpa {$required}.");
        }

        $this->assertNotContains('h-10', $classes);
    }

    public function test_buttons_keep_a_32px_touch_target_via_pseudo_element(): void
    {
        $card = $this->cardTemplate();

        preg_match_all('#<button\b[^>]*class="([^"]*)"[^>]*>#', $card, $buttons);

        $this->assertCount(2, $buttons[1]);

        foreach ($buttons[1] as $classes) {
            $tokens = preg_split('/\s+/', trim($classes));

            $this->assertContains('relative', $tokens);
            $this->assertContains('h-8', $tokens);
            $this->assertContains('before:absolute', $tokens);
            $this->assertNotContains('w-7', $tokens);
        }
    }

    public function test_number_input_has_no_browser_spin_buttons_and_fixed_height(): void
    {
        $classes = $this->classesOf($this->cardTemplate(), 'x-model.number="item.qty"');

        foreach (['p-0', 'h-8', 'text-center', '[appearance:textfield]', '[&::-webkit-inner-spin-button]:appearance-none', '[&::-webkit-outer-spin-button]:appearance-none'] as $required) {
            $this->assertContains($required, $classes, "Input tanpa {$required}.");
        }

        $this->assertNotContains('font-bold', $classes);
    }

    public function test_appliance_name_is_smaller_and_lighter_and_clamped_to_two_lines(): void
    {
        $card = $this->cardTemplate();
        $classes = $this->classesOf($card, 'x-text="item.label"');

        foreach (['text-xs', 'font-medium', 'leading-tight', 'line-clamp-2'] as $required) {
            $this->assertContains($required, $classes, "Nama alat tanpa {$required}.");
        }

        $this->assertNotContains('font-bold', $classes);
        $this->assertNotContains('text-sm', $classes);
        $this->assertStringContainsString(':title="item.label"', $card);
    }

    public function test_watt_caption_is_not_smaller_than_11px_and_not_heavier_than_the_name(): void
    {
        $classes = $this->classesOf($this->cardTemplate(), "x-text=\"'~' + item.watt + 'W'\"");

        $this->assertContains('text-[11px]', $classes);
        $this->assertContains('font-normal', $classes);
        $this->assertNotContains('text-[10px]', $classes);
        $this->assertNotContains('font-bold', $classes);
    }

    public function test_text_group_gives_way_to_the_stepper_and_icon_box_is_uniform(): void
    {
        $card = $this->cardTemplate();

        $group = $this->classesOf($card, 'min-w-0 flex-1');
        $this->assertContains('min-w-0', $group);
        $this->assertContains('flex-1', $group);

        $icon = $this->classesOf($card, 'w-8 h-8 shrink-0');
        foreach (['shrink-0', 'w-8', 'h-8'] as $required) {
            $this->assertContains($required, $icon);
        }

        $this->assertStringContainsString('x-if="item.iconImage"', $card);
        $this->assertStringContainsString('x-if="!item.iconImage"', $card);
    }

    public function test_cards_stay_equal_height_in_a_one_then_two_column_grid(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $card = $this->cardTemplate();

        $this->assertStringContainsString('grid grid-cols-1 sm:grid-cols-2 gap-3', $html);
        $this->assertContains('h-20', $this->classesOf($card, 'justify-between'));
    }

    public function test_other_calculator_parts_are_untouched(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Pilih jumlah peralatan listrik di rumah Anda:', $html);
        $this->assertStringContainsString('Lengkapi data Anda', $html);
        $this->assertStringNotContainsString('calculatorPltsComponent', $html);
    }
}
