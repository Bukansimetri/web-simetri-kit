<?php

namespace App\Enums;

/**
 * Section daftar item yang isinya dikelola admin. Aturan desain per section
 * (batas item aktif, ikon, penonjolan, judul, nomor) ditetapkan di sini,
 * bukan oleh admin, agar tampilan tetap sesuai desain.
 */
enum PageSection: string
{
    case WhyChoose = 'beranda.mengapa-beralih';
    case HowItWorks = 'beranda.cara-kerja';
    case CareerValues = 'karir.mengapa-bergabung';
    case RecruitmentProcess = 'karir.proses-rekrutmen';
    case AboutMission = 'tentang-kami.misi';
    case AboutValues = 'tentang-kami.nilai';
    case AboutTrust = 'tentang-kami.trust-strip';

    /**
     * Label menu panel; konteks halamannya sudah diberikan oleh grup navigasi.
     */
    public function label(): string
    {
        return match ($this) {
            self::WhyChoose => 'Mengapa Beralih',
            self::HowItWorks => 'Cara Kerja',
            self::CareerValues => 'Mengapa Bergabung',
            self::RecruitmentProcess => 'Proses Rekrutmen',
            self::AboutMission => 'Misi',
            self::AboutValues => 'Nilai',
            self::AboutTrust => 'Trust Strip',
        };
    }

    /**
     * Label lengkap dengan nama halaman, untuk judul dan pesan yang berdiri sendiri.
     */
    public function fullLabel(): string
    {
        return $this->pageName().' – '.$this->label();
    }

    public function navigationGroup(): string
    {
        return $this->pageName();
    }

    /**
     * Urutan di dalam grup navigasi (Banner, Lowongan Kerja, dan Tim/Logo Klien/Testimoni mengisi urutan lain).
     */
    public function navigationSort(): int
    {
        return match ($this) {
            self::WhyChoose => 2,
            self::HowItWorks => 3,
            self::CareerValues => 2,
            self::RecruitmentProcess => 3,
            self::AboutMission => 1,
            self::AboutValues => 2,
            self::AboutTrust => 3,
        };
    }

    public function pageName(): string
    {
        return match ($this) {
            self::WhyChoose, self::HowItWorks => 'Beranda',
            self::CareerValues, self::RecruitmentProcess => 'Karir',
            self::AboutMission, self::AboutValues, self::AboutTrust => 'Tentang Kami',
        };
    }

    public function maxActiveItems(): int
    {
        return match ($this) {
            self::WhyChoose, self::CareerValues, self::AboutValues, self::AboutTrust => 3,
            self::HowItWorks, self::RecruitmentProcess => 4,
            self::AboutMission => 5,
        };
    }

    public function hasIcon(): bool
    {
        return in_array($this, [self::WhyChoose, self::CareerValues, self::AboutValues, self::AboutTrust], true);
    }

    public function supportsEmphasis(): bool
    {
        return in_array($this, [self::WhyChoose, self::HowItWorks], true);
    }

    /**
     * Section yang desainnya punya judul besar (Trust Strip tidak punya).
     */
    public function hasHeading(): bool
    {
        return $this !== self::AboutTrust;
    }

    public function hasSubtitle(): bool
    {
        return ! in_array($this, [self::RecruitmentProcess, self::AboutTrust], true);
    }

    public function hasEyebrow(): bool
    {
        return $this === self::AboutMission;
    }

    public function hasFeaturedCard(): bool
    {
        return $this === self::AboutValues;
    }

    public function itemTitleLabel(): string
    {
        return $this === self::AboutTrust ? 'Angka' : 'Judul';
    }

    public function itemDescriptionLabel(): string
    {
        return $this === self::AboutTrust ? 'Keterangan' : 'Deskripsi';
    }

    public function itemTitleMaxLength(): int
    {
        return match ($this) {
            self::AboutMission, self::AboutValues => 120,
            self::AboutTrust => 60,
            default => 60,
        };
    }

    public function itemDescriptionMaxLength(): int
    {
        return match ($this) {
            self::AboutMission, self::AboutValues => 500,
            self::AboutTrust => 120,
            default => 200,
        };
    }

    public function headingTitleMaxLength(): int
    {
        return in_array($this, [self::AboutMission, self::AboutValues], true) ? 255 : 80;
    }

    public function headingSubtitleMaxLength(): int
    {
        return in_array($this, [self::AboutMission, self::AboutValues], true) ? 500 : 250;
    }

    /**
     * Nomor langkah sesuai format desain section; null untuk section kartu.
     */
    public function stepNumber(int $position): ?string
    {
        return match ($this) {
            self::HowItWorks => sprintf('%02d', $position),
            self::RecruitmentProcess => (string) $position,
            default => null,
        };
    }
}
