@extends('layouts.public')

@php
    $site = app(\App\Settings\SiteSettings::class);
    $appName = $site->site_name ?: config('app.name');
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('kontak', 'Kontak'))
@section('meta_description', 'Hubungi tim '.$appName.' untuk konsultasi gratis kebutuhan panel surya Anda.')

@section('content')
    <section class="pt-40 pb-12 px-6 max-w-7xl mx-auto text-center">
        <nav class="flex justify-center text-sm text-outline mb-4">
            <ol class="flex items-center gap-2">
                <li><a class="hover:text-primary transition-colors" href="{{ url('/') }}">Beranda</a></li>
                <li class="flex items-center"><span class="material-symbols-outlined text-sm">chevron_right</span></li>
                <li class="text-primary font-semibold">Kontak</li>
            </ol>
        </nav>
        <h1 class="font-headline-xl text-4xl md:text-5xl font-extrabold text-primary tracking-tight mb-4">Mari Wujudkan Rumah Hemat Energi</h1>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">
            Tim kami siap membantu menjawab pertanyaan dan memberikan konsultasi gratis untuk kebutuhan energi surya Anda.
        </p>
    </section>

    <section class="px-6 max-w-7xl mx-auto pb-24 flex flex-col lg:flex-row gap-8">
        {{-- Form Kontak (AMC-216: submit sungguhan ke POST /kontak) --}}
        <div
            x-data="{
                submitted: false,
                submitting: false,
                whatsappUrl: null,
                serverError: null,
                errors: {},
                form: { nama: '', phone: '', email: '', kebutuhan: '', pesan: '' },
                honeypot: '',
                formToken: @js($formToken),
                validate() {
                    this.errors = {};
                    if (! this.form.nama.trim()) this.errors.nama = 'Nama lengkap wajib diisi.';
                    if (! /^[0-9+\-\s]{8,15}$/.test(this.form.phone.trim())) this.errors.phone = 'Nomor HP/WhatsApp tidak valid.';
                    if (! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email.trim())) this.errors.email = 'Email tidak valid.';
                    if (! this.form.pesan.trim()) this.errors.pesan = 'Pesan tidak boleh kosong.';
                    return Object.keys(this.errors).length === 0;
                },
                async submit() {
                    this.serverError = null;

                    if (! this.validate()) {
                        return;
                    }

                    this.submitting = true;

                    try {
                        const response = await fetch('{{ route('kontak.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({
                                nama: this.form.nama,
                                phone: this.form.phone,
                                email: this.form.email,
                                kebutuhan: this.form.kebutuhan,
                                pesan: this.form.pesan,
                                website: this.honeypot,
                                form_token: this.formToken,
                            }),
                        });

                        const data = await response.json();

                        if (response.status === 422) {
                            this.errors = {
                                nama: data.errors?.nama?.[0],
                                phone: data.errors?.phone?.[0],
                                email: data.errors?.email?.[0],
                                pesan: data.errors?.pesan?.[0],
                            };

                            return;
                        }

                        if (response.status === 429) {
                            this.serverError = data.message ?? 'Terlalu banyak percobaan. Silakan coba lagi nanti.';

                            return;
                        }

                        if (! response.ok) {
                            this.serverError = 'Terjadi kesalahan. Silakan coba lagi beberapa saat lagi.';

                            return;
                        }

                        this.submitted = true;
                        this.whatsappUrl = data.whatsapp_url;

                        // Buka WhatsApp otomatis (FR-012) — tombol fallback tetap
                        // ditampilkan di bawah untuk kasus pop-up diblokir browser.
                        if (this.whatsappUrl) {
                            window.open(this.whatsappUrl, '_blank');
                        }
                    } catch (e) {
                        this.serverError = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                    } finally {
                        this.submitting = false;
                    }
                },
            }"
            class="w-full lg:w-3/5 bg-white shadow-lg rounded-lg p-8 md:p-12"
        >
            <h2 class="font-headline-lg text-2xl md:text-3xl font-extrabold text-primary mb-8">Kirim pesan ke tim kami</h2>

            <template x-if="submitted">
                <div class="bg-primary/10 text-primary p-6 rounded-lg text-center" role="status">
                    <span class="material-symbols-outlined text-3xl mb-2">check_circle</span>
                    <p class="font-semibold">Terima kasih! Pesan Anda telah kami terima.</p>
                    <p class="font-body-sm text-body-sm mt-1">Tim kami akan segera menghubungi Anda kembali.</p>
                    <template x-if="whatsappUrl">
                        <a :href="whatsappUrl" target="_blank" class="inline-flex items-center gap-2 mt-4 bg-primary text-white px-6 py-3 rounded-lg font-semibold">
                            <span class="material-symbols-outlined">chat</span> Buka WhatsApp
                        </a>
                    </template>
                </div>
            </template>

            <p x-show="serverError" x-cloak x-text="serverError" class="text-sm text-error font-medium mb-4"></p>

            <form @submit.prevent="submit()" x-show="! submitted" class="space-y-6">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2" for="nama">Nama Lengkap</label>
                    <input
                        id="nama" name="nama" type="text" x-model="form.nama"
                        placeholder="Masukkan nama Anda"
                        class="w-full bg-surface-container border border-transparent rounded-lg px-6 py-4 focus:border-primary-container focus:ring-0"
                    >
                    <p x-show="errors.nama" x-cloak x-text="errors.nama" class="text-sm text-error mt-1"></p>
                </div>

                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2" for="phone">No. HP / WhatsApp</label>
                    <input
                        id="phone" name="phone" type="tel" x-model="form.phone"
                        placeholder="Contoh: 08123456789"
                        class="w-full bg-surface-container border border-transparent rounded-lg px-6 py-4 focus:border-primary-container focus:ring-0"
                    >
                    <p x-show="errors.phone" x-cloak x-text="errors.phone" class="text-sm text-error mt-1"></p>
                </div>

                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2" for="email">Email</label>
                    <input
                        id="email" name="email" type="email" x-model="form.email"
                        placeholder="nama@email.com"
                        class="w-full bg-surface-container border border-transparent rounded-lg px-6 py-4 focus:border-primary-container focus:ring-0"
                    >
                    <p x-show="errors.email" x-cloak x-text="errors.email" class="text-sm text-error mt-1"></p>
                </div>

                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2" for="kebutuhan">Topik Kebutuhan</label>
                    <select id="kebutuhan" name="kebutuhan" x-model="form.kebutuhan" class="w-full bg-surface-container border border-transparent rounded-lg px-6 py-4">
                        <option value="">Pilih topik</option>
                        <option value="umum">Konsultasi Umum</option>
                        <option value="residensial">Residensial</option>
                        <option value="komersial">Komersial &amp; Industri</option>
                        <option value="pompa">Pompa Air Tenaga Surya</option>
                    </select>
                </div>

                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2" for="pesan">Pesan Anda</label>
                    <textarea
                        id="pesan" name="pesan" rows="4" x-model="form.pesan"
                        placeholder="Ceritakan detail kebutuhan Anda..."
                        class="w-full bg-surface-container border border-transparent rounded-3xl px-6 py-4 resize-none focus:border-primary-container focus:ring-0"
                    ></textarea>
                    <p x-show="errors.pesan" x-cloak x-text="errors.pesan" class="text-sm text-error mt-1"></p>
                </div>

                {{-- Honeypot anti-bot: disembunyikan dari mata & dari urutan tab, jadi
                     tidak pernah terisi manusia. Bot pengisi-otomatis cenderung mengisinya,
                     dan submit yang field ini terisi akan ditolak diam-diam di server.
                     JANGAN dihapus atau diberi label yang terlihat pengunjung. --}}
                <div aria-hidden="true" class="absolute w-px h-px overflow-hidden -left-[9999px] top-auto">
                    <label>Website</label>
                    <input type="text" x-model="honeypot" tabindex="-1" autocomplete="off">
                </div>

                <button
                    type="submit"
                    :disabled="submitting"
                    :class="submitting && 'opacity-60 cursor-not-allowed'"
                    class="btn-fill w-full bg-primary-container text-white font-bold py-4 rounded-lg hover:shadow-md transition-all"
                >
                    <span x-show="! submitting">Kirim Pesan</span>
                    <span x-show="submitting" x-cloak>Mengirim...</span>
                </button>

                <p class="text-center text-sm text-on-surface-variant flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">schedule</span>
                    Kami biasanya membalas dalam 1x24 jam kerja
                </p>
            </form>
        </div>

        {{-- Info Kontak --}}
        <div class="w-full lg:w-2/5 bg-primary rounded-lg p-8 md:p-12 text-white relative overflow-hidden shadow-2xl">
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
            <div class="relative z-10 space-y-10">
                @if ($site->company_address)
                    <div>
                        <h3 class="text-sm text-primary-fixed-dim uppercase tracking-wider mb-4 flex items-center gap-3">
                            <span class="material-symbols-outlined">location_on</span> Kantor Pusat
                        </h3>
                        <p class="text-white/90 leading-relaxed">{{ $site->company_address }}</p>
                    </div>
                @endif
                @if ($site->company_email)
                    <div>
                        <h3 class="text-sm text-primary-fixed-dim uppercase tracking-wider mb-4 flex items-center gap-3">
                            <span class="material-symbols-outlined">mail</span> Email
                        </h3>
                        <a href="mailto:{{ $site->company_email }}" class="text-white/90 hover:text-white transition-colors">{{ $site->company_email }}</a>
                    </div>
                @endif
                <div>
                    <h3 class="text-sm text-primary-fixed-dim uppercase tracking-wider mb-4 flex items-center gap-3">
                        <span class="material-symbols-outlined">forum</span> Hubungi Langsung
                    </h3>
                    <a href="{{ $site->whatsappUrl('Halo, saya ingin konsultasi tentang solusi tenaga surya SUOER.') ?: '#' }}" class="inline-flex items-center gap-4 group">
                        <div class="w-14 h-14 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center group-hover:-translate-y-1 transition-transform shrink-0">
                            <span class="material-symbols-outlined text-2xl">chat</span>
                        </div>
                        <div>
                            <p class="font-headline-lg text-xl font-bold text-white group-hover:text-primary-fixed-dim transition-colors">Chat via WhatsApp</p>
                            <p class="text-sm text-primary-fixed-dim mt-1">Senin - Jumat, 09:00 - 17:00 WIB</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ Konsultasi --}}
    @php
        $consultFaqs = [
            ['q' => 'Setelah kirim pesan, apa langkah selanjutnya?', 'a' => 'Tim ahli energi surya kami akan meninjau pesan Anda dan membalas dalam maksimal 1x24 jam kerja. Kami mengatur diskusi awal via telepon atau video call untuk memahami kebutuhan energi dan kondisi lokasi Anda sebelum menjadwalkan survei teknis.'],
            ['q' => 'Apakah survei lokasi berbayar?', 'a' => 'Untuk area Jabodetabek, survei lokasi awal gratis. Untuk area di luar Jabodetabek, biaya survei didiskusikan terlebih dahulu dan dapat diakumulasikan ke nilai proyek jika Anda memutuskan menggunakan layanan kami.'],
            ['q' => 'Bisa konsultasi tanpa datang ke kantor?', 'a' => 'Tentu. Mayoritas konsultasi awal kami dilakukan daring untuk kenyamanan Anda. Kami memakai data satelit awal untuk estimasi kapasitas atap sebelum tim teknis melakukan kunjungan fisik.'],
        ];
    @endphp
    <section class="reveal-element bg-surface-container-lowest border-t border-surface-container-high px-6 py-20">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="font-headline-lg text-2xl md:text-3xl font-extrabold text-primary mb-4">Pertanyaan Seputar Konsultasi</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Informasi singkat mengenai proses setelah Anda menghubungi kami.</p>
            </div>
            <div class="space-y-4" x-data="{ open: 0 }">
                @foreach ($consultFaqs as $i => $faq)
                    <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-surface-container-low">
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                                class="w-full flex justify-between items-center gap-4 p-6 text-left font-headline-lg text-lg text-on-surface hover:text-primary transition-colors">
                            <span>{{ $faq['q'] }}</span>
                            <span class="material-symbols-outlined transition-transform shrink-0" :class="open === {{ $i }} && 'rotate-180'">expand_more</span>
                        </button>
                        <div x-show="open === {{ $i }}" x-cloak x-transition class="px-6 pb-6 -mt-1 font-body-md text-body-md text-on-surface-variant">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
