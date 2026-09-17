<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('about_page.hero_image_path', null);
        $this->migrator->add('about_page.hero_subtitle', 'Menghadirkan solusi energi surya inovatif dan berkelanjutan untuk masa depan Indonesia yang lebih cerah.');

        $this->migrator->add('about_page.siapa_kami_image_path', null);
        $this->migrator->add('about_page.siapa_kami_badge_text', 'Bagian dari Sinar Mas Elektrindo');
        $this->migrator->add('about_page.siapa_kami_eyebrow', 'Tentang Kami');
        $this->migrator->add('about_page.siapa_kami_heading', 'Menghadirkan Energi Surya Andal & Terpercaya untuk Indonesia');
        $this->migrator->add(
            'about_page.siapa_kami_body',
            'Sebagai bagian dari <strong>PT Sinar Mas Elektrindo</strong>, {app_name} hadir membawa komitmen kuat dalam menghadirkan solusi energi surya yang inovatif, efisien, dan andal. Kami memadukan kekuatan infrastruktur global dengan pemahaman mendalam tentang kebutuhan lokal Indonesia.'
        );
        $this->migrator->add(
            'about_page.siapa_kami_quote',
            'Misi kami bukan sekadar menjual panel, tetapi menjadi <span class="text-secondary">mitra transformasi energi</span> yang memberdayakan masyarakat dan bisnis menuju masa depan yang lebih hijau.'
        );

        $this->migrator->add('about_page.visi_eyebrow', 'Visi Kami');
        $this->migrator->add(
            'about_page.visi_heading',
            'Menjadi pelopor energi surya di Asia Tenggara yang paling dipercaya, mendorong masa depan di mana setiap bangunan mandiri energi dan berkelanjutan.'
        );
        $this->migrator->add(
            'about_page.visi_subtext',
            'Membangun ekosistem tenaga surya yang terintegrasi, transparan, dan dapat diakses oleh seluruh lapisan masyarakat.'
        );

        $this->migrator->add('about_page.misi_eyebrow', 'Misi');
        $this->migrator->add('about_page.misi_heading', 'Bagaimana Kami Mewujudkannya');
        $this->migrator->add(
            'about_page.misi_subtext',
            'Langkah konkret kami dalam menghadirkan ekosistem energi surya terpadu, presisi, dan berkelanjutan untuk Indonesia.'
        );
        $this->migrator->add('about_page.misi_items', json_encode([
            ['title' => 'Solusi Premium & Teruji', 'description' => 'Menyediakan panel surya dan inverter berkualitas terbaik yang telah teruji secara global untuk performa maksimal di iklim tropis.'],
            ['title' => 'Pemasangan Presisi', 'description' => 'Menjamin instalasi yang aman, rapi, dan efisien oleh tim teknisi bersertifikat yang memahami standar kelistrikan nasional.'],
            ['title' => 'Dukungan Purna Jual', 'description' => 'Memberikan ketenangan pikiran melalui pemeliharaan responsif dan garansi performa jangka panjang yang dapat diandalkan.'],
            ['title' => 'Edukasi Berkelanjutan', 'description' => 'Meningkatkan kesadaran masyarakat tentang manfaat dan pentingnya beralih ke energi bersih.'],
            ['title' => 'Inovasi Teknologi', 'description' => 'Terus mengadopsi teknologi terbaru dalam penyimpanan dan manajemen energi untuk efisiensi yang lebih baik.'],
        ]));

        $this->migrator->add('about_page.nilai_heading', 'Nilai-Nilai Kami');
        $this->migrator->add(
            'about_page.nilai_subtext',
            'Fondasi dan komitmen kami dalam melayani pelanggan dan menjaga kelestarian bumi.'
        );
        $this->migrator->add('about_page.nilai_featured_image_path', null);
        $this->migrator->add('about_page.nilai_featured_icon', 'eco');
        $this->migrator->add('about_page.nilai_featured_title', 'Ekonomi Hijau & Lapangan Kerja');
        $this->migrator->add(
            'about_page.nilai_featured_description',
            'Kami tidak hanya membangun infrastruktur energi, tetapi juga menggerakkan roda ekonomi hijau dengan menciptakan lapangan kerja baru bagi tenaga kerja lokal.'
        );
        $this->migrator->add('about_page.nilai_items', json_encode([
            ['icon' => 'savings', 'title' => 'Efisien & Terjangkau', 'description' => 'Menghadirkan solusi energi yang menekan biaya operasional jangka panjang.'],
            ['icon' => 'school', 'title' => 'Edukasi Masyarakat', 'description' => 'Memberikan pemahaman mendalam tentang transisi energi terbarukan.'],
            ['icon' => 'handshake', 'title' => 'Kolaborasi & Infrastruktur', 'description' => 'Membangun ekosistem bersama mitra strategis untuk jangkauan luas.'],
        ]));

        $this->migrator->add('about_page.trust_items', json_encode([
            ['icon' => 'group', 'value' => '5.000+', 'label' => 'Pelanggan Puas'],
            ['icon' => 'solar_power', 'value' => '10+ MW', 'label' => 'Total Kapasitas Terpasang'],
            ['icon' => 'calendar_month', 'value' => '15+ Tahun', 'label' => 'Pengalaman Industri'],
        ]));
    }
};
