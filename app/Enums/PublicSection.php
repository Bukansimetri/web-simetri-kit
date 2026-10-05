<?php

namespace App\Enums;

use App\Filament\Resources\AboutMissionResource;
use App\Filament\Resources\AboutTrustResource;
use App\Filament\Resources\AboutValueResource;
use App\Filament\Resources\CallToActionResource;
use App\Filament\Resources\CareerValueResource;
use App\Filament\Resources\ClientLogoResource;
use App\Filament\Resources\FaqItemResource;
use App\Filament\Resources\HowItWorksStepResource;
use App\Filament\Resources\PageBlockResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\RecruitmentStepResource;
use App\Filament\Resources\TeamMemberResource;
use App\Filament\Resources\TestimonialResource;
use App\Filament\Resources\WhyChooseItemResource;

/**
 * Section bernama di situs publik yang bisa disembunyikan admin. Daftarnya tetap di kode (bukan page builder):
 * admin hanya menyalakan atau mematikan, tidak menambah atau mengurutkan section.
 */
enum PublicSection: string
{
    case HomeWhyChoose = 'beranda.mengapa-beralih';
    case HomeHowItWorks = 'beranda.cara-kerja';
    case HomeSolutions = 'beranda.solusi';
    case HomeTestimonials = 'beranda.testimoni';
    case HomeCta = 'beranda.cta';

    case AboutWhoWeAre = 'tentang-kami.siapa-kami';
    case AboutVision = 'tentang-kami.visi';
    case AboutMission = 'tentang-kami.misi';
    case AboutValues = 'tentang-kami.nilai';
    case AboutTrust = 'tentang-kami.trust-strip';
    case AboutTeam = 'tentang-kami.tim';
    case AboutTestimonials = 'tentang-kami.testimoni';
    case AboutClientLogos = 'tentang-kami.logo-klien';
    case AboutCta = 'tentang-kami.cta';

    case CareerValues = 'karir.mengapa-bergabung';
    case CareerProcess = 'karir.proses-rekrutmen';
    case CareerCta = 'karir.cta';

    case ProductCtaCalculator = 'produk.cta-kalkulator';
    case ProductFaq = 'produk.faq';
    case ProductCtaClosing = 'produk.cta-penutup';

    case ProductDetailCta = 'produk-detail.cta';
    case ArticleCta = 'artikel.cta';
    case ArticleDetailCta = 'artikel-detail.cta';
    case FaqCta = 'faq.cta';
    case ContactFaq = 'kontak.faq';

    public const PAGE_HOME = 'Beranda';

    public const PAGE_ABOUT = 'Tentang Kami';

    public const PAGE_CAREER = 'Karir';

    public const PAGE_PRODUCT = 'Produk';

    public const PAGE_PRODUCT_DETAIL = 'Detail Produk';

    public const PAGE_ARTICLE = 'Artikel';

    public const PAGE_ARTICLE_DETAIL = 'Detail Artikel';

    public const PAGE_FAQ = 'FAQ';

    public const PAGE_CONTACT = 'Kontak';

    /**
     * Halaman situs, dalam urutan tampil di halaman Tampilan Section.
     *
     * @return list<string>
     */
    public static function pages(): array
    {
        return [
            self::PAGE_HOME,
            self::PAGE_ABOUT,
            self::PAGE_CAREER,
            self::PAGE_PRODUCT,
            self::PAGE_PRODUCT_DETAIL,
            self::PAGE_ARTICLE,
            self::PAGE_ARTICLE_DETAIL,
            self::PAGE_FAQ,
            self::PAGE_CONTACT,
        ];
    }

    /**
     * @return list<self>
     */
    public static function forPage(string $page): array
    {
        return array_values(array_filter(self::cases(), fn (self $section): bool => $section->page() === $page));
    }

    public function page(): string
    {
        return match ($this) {
            self::HomeWhyChoose, self::HomeHowItWorks, self::HomeSolutions, self::HomeTestimonials, self::HomeCta => self::PAGE_HOME,
            self::AboutWhoWeAre, self::AboutVision, self::AboutMission, self::AboutValues, self::AboutTrust,
            self::AboutTeam, self::AboutTestimonials, self::AboutClientLogos, self::AboutCta => self::PAGE_ABOUT,
            self::CareerValues, self::CareerProcess, self::CareerCta => self::PAGE_CAREER,
            self::ProductCtaCalculator, self::ProductFaq, self::ProductCtaClosing => self::PAGE_PRODUCT,
            self::ProductDetailCta => self::PAGE_PRODUCT_DETAIL,
            self::ArticleCta => self::PAGE_ARTICLE,
            self::ArticleDetailCta => self::PAGE_ARTICLE_DETAIL,
            self::FaqCta => self::PAGE_FAQ,
            self::ContactFaq => self::PAGE_CONTACT,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::HomeWhyChoose => 'Mengapa Beralih',
            self::HomeHowItWorks => 'Cara Kerja (Sederhana dan Mulus)',
            self::HomeSolutions => 'Solusi Untuk Setiap Kebutuhan',
            self::HomeTestimonials, self::AboutTestimonials => 'Testimoni (Partner Kami)',
            self::HomeCta, self::AboutCta, self::CareerCta, self::ProductDetailCta,
            self::ArticleCta, self::ArticleDetailCta, self::FaqCta => 'CTA',
            self::ProductCtaCalculator => 'CTA Kalkulator',
            self::ProductCtaClosing => 'CTA Penutup',
            self::AboutWhoWeAre => 'Siapa Kami',
            self::AboutVision => 'Visi',
            self::AboutMission => 'Misi',
            self::AboutValues => 'Nilai',
            self::AboutTrust => 'Trust Strip',
            self::AboutTeam => 'Tim',
            self::AboutClientLogos => 'Logo Klien (Dipercaya Oleh)',
            self::CareerValues => 'Mengapa Bergabung',
            self::CareerProcess => 'Proses Rekrutmen',
            self::ProductFaq => 'FAQ Seputar Produk',
            self::ContactFaq => 'FAQ Seputar Konsultasi',
        };
    }

    /**
     * Label dengan nama halaman, untuk penanda yang berdiri sendiri.
     */
    public function fullLabel(): string
    {
        return $this->page().' – '.$this->label();
    }

    /**
     * Alamat menu admin yang mengedit isi section; null bila tidak ada.
     */
    public function contentUrl(): ?string
    {
        return match ($this) {
            self::HomeWhyChoose => WhyChooseItemResource::getUrl('index'),
            self::HomeHowItWorks => HowItWorksStepResource::getUrl('index'),
            self::HomeSolutions => ProductResource::getUrl('index'),
            self::HomeTestimonials, self::AboutTestimonials => TestimonialResource::getUrl('index'),
            self::AboutWhoWeAre, self::AboutVision => PageBlockResource::getUrl('index'),
            self::AboutMission => AboutMissionResource::getUrl('index'),
            self::AboutValues => AboutValueResource::getUrl('index'),
            self::AboutTrust => AboutTrustResource::getUrl('index'),
            self::AboutTeam => TeamMemberResource::getUrl('index'),
            self::AboutClientLogos => ClientLogoResource::getUrl('index'),
            self::CareerValues => CareerValueResource::getUrl('index'),
            self::CareerProcess => RecruitmentStepResource::getUrl('index'),
            self::ProductFaq => FaqItemResource::getUrl('index', ['tableFilters' => ['placement' => ['value' => FaqPlacement::Product->value]]]),
            self::ContactFaq => FaqItemResource::getUrl('index', ['tableFilters' => ['placement' => ['value' => FaqPlacement::Contact->value]]]),
            self::HomeCta, self::AboutCta, self::CareerCta, self::ProductCtaCalculator, self::ProductCtaClosing,
            self::ProductDetailCta, self::ArticleCta, self::ArticleDetailCta, self::FaqCta => CallToActionResource::getUrl('index'),
        };
    }

    public static function fromCta(CtaPlacement $placement): self
    {
        return match ($placement) {
            CtaPlacement::Home => self::HomeCta,
            CtaPlacement::ProductCalculator => self::ProductCtaCalculator,
            CtaPlacement::ProductClosing => self::ProductCtaClosing,
            CtaPlacement::ProductDetail => self::ProductDetailCta,
            CtaPlacement::ArticleIndex => self::ArticleCta,
            CtaPlacement::ArticleDetail => self::ArticleDetailCta,
            CtaPlacement::About => self::AboutCta,
            CtaPlacement::Faq => self::FaqCta,
            CtaPlacement::Career => self::CareerCta,
        };
    }

    public static function fromPageSection(PageSection $section): self
    {
        return match ($section) {
            PageSection::WhyChoose => self::HomeWhyChoose,
            PageSection::HowItWorks => self::HomeHowItWorks,
            PageSection::CareerValues => self::CareerValues,
            PageSection::RecruitmentProcess => self::CareerProcess,
            PageSection::AboutMission => self::AboutMission,
            PageSection::AboutValues => self::AboutValues,
            PageSection::AboutTrust => self::AboutTrust,
        };
    }

    public static function forFaqPlacement(FaqPlacement $placement): ?self
    {
        return match ($placement) {
            FaqPlacement::Product => self::ProductFaq,
            FaqPlacement::Contact => self::ContactFaq,
            FaqPlacement::Faq => null,
        };
    }

    public static function forPageBlock(PageBlockType $type): ?self
    {
        return match ($type) {
            PageBlockType::AboutWhoWeAre => self::AboutWhoWeAre,
            PageBlockType::AboutVision => self::AboutVision,
            default => null,
        };
    }
}
