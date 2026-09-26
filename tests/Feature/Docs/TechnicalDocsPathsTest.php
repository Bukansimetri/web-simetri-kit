<?php

namespace Tests\Feature\Docs;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Menjaga dokumentasi teknis (AMC-233) tetap sesuai kode: setiap path repo
 * yang disebut sebagai inline code dan setiap tautan relatif harus ada
 * (FR-015, SC-004). Path hipotetis untuk contoh harus ditulis di dalam
 * fenced code block supaya tidak ikut diperiksa.
 */
class TechnicalDocsPathsTest extends TestCase
{
    private const PATH_PREFIXES = [
        'app/', 'resources/', 'routes/', 'database/', 'config/',
        'tests/', 'docs/', 'public/', '.specify/',
    ];

    private const ROOT_FILES = [
        'vite.config.js', 'composer.json', 'package.json', 'README.md',
    ];

    /**
     * @return array<string, array{0: string}>
     */
    public static function documents(): array
    {
        return [
            'arsitektur' => ['docs/arsitektur.md'],
            'panduan-section' => ['docs/panduan-section.md'],
            'panduan-tema' => ['docs/panduan-tema.md'],
        ];
    }

    #[DataProvider('documents')]
    public function test_document_exists_and_has_last_updated_date(string $document): void
    {
        $this->assertFileExists(base_path($document), "{$document} belum dibuat.");

        $this->assertMatchesRegularExpression(
            '/^\*\*Terakhir diperbarui\*\*: \d{4}-\d{2}-\d{2}$/m',
            file_get_contents(base_path($document)),
            "{$document} tidak memuat baris '**Terakhir diperbarui**: YYYY-MM-DD'."
        );
    }

    #[DataProvider('documents')]
    public function test_inline_repo_paths_exist(string $document): void
    {
        $this->assertFileExists(base_path($document), "{$document} belum dibuat.");

        $text = $this->withoutFencedCodeBlocks(file_get_contents(base_path($document)));

        preg_match_all('/`([^`\n]+)`/', $text, $matches);

        foreach (array_unique($matches[1]) as $candidate) {
            $path = preg_replace('/(:\d+(-\d+)?|#.*)$/', '', trim($candidate));

            if (! $this->looksLikeRepoPath($path)) {
                continue;
            }

            $this->assertFileExists(
                base_path($path),
                "{$document} menyebut path yang tidak ada: {$path}"
            );
        }
    }

    #[DataProvider('documents')]
    public function test_relative_markdown_links_resolve(string $document): void
    {
        $this->assertFileExists(base_path($document), "{$document} belum dibuat.");

        $text = $this->withoutFencedCodeBlocks(file_get_contents(base_path($document)));

        preg_match_all('/\[[^\]]*\]\(([^)\s]+)\)/', $text, $matches);

        foreach (array_unique($matches[1]) as $target) {
            if (preg_match('#^(https?:|mailto:|\#)#', $target)) {
                continue;
            }

            $file = preg_replace('/#.*$/', '', $target);
            $resolved = dirname(base_path($document)).'/'.$file;

            $this->assertFileExists(
                $resolved,
                "{$document} memuat tautan relatif yang tidak valid: {$target}"
            );
        }
    }

    private function withoutFencedCodeBlocks(string $markdown): string
    {
        return preg_replace('/^```.*?^```/ms', '', $markdown) ?? $markdown;
    }

    private function looksLikeRepoPath(string $candidate): bool
    {
        if (str_contains($candidate, ' ') || str_contains($candidate, '*') || str_contains($candidate, '{')) {
            return false;
        }

        if (in_array($candidate, self::ROOT_FILES, true)) {
            return true;
        }

        foreach (self::PATH_PREFIXES as $prefix) {
            if (str_starts_with($candidate, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
