@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'Kontak — '.$appName)
@section('meta_description', 'Hubungi tim '.$appName.' untuk konsultasi gratis kebutuhan panel surya Anda.')

@section('content')
    <section class="pt-32 pb-16 px-6 max-w-7xl mx-auto">
        <p class="text-sm font-semibold text-primary/70 uppercase tracking-widest mb-4">
            <a href="{{ url('/') }}" class="hover:text-primary transition-colors">Beranda</a> <span class="mx-2">/</span> Kontak
        </p>
        <h1 class="font-headline-xl text-headline-xl text-on-surface max-w-2xl mb-4">Hubungi Kami</h1>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-lg">
            Tim kami siap membantu menjawab pertanyaan dan memberikan konsultasi gratis untuk kebutuhan energi surya Anda.
        </p>
    </section>

    <section class="px-6 max-w-7xl mx-auto pb-24 grid grid-cols-1 md:grid-cols-5 gap-12">
        {{-- Info Kontak --}}
        <div class="md:col-span-2 space-y-6">
            <div class="bg-surface-container-low p-6 rounded-lg flex items-start gap-4">
                <span class="material-symbols-outlined text-primary">location_on</span>
                <div>
                    <h3 class="font-bold text-on-surface mb-1">Alamat Kantor</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190</p>
                </div>
            </div>
            <div class="bg-surface-container-low p-6 rounded-lg flex items-start gap-4">
                <span class="material-symbols-outlined text-primary">mail</span>
                <div>
                    <h3 class="font-bold text-on-surface mb-1">Email</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">hello@suoer.id</p>
                </div>
            </div>
            <div class="bg-surface-container-low p-6 rounded-lg flex items-start gap-4">
                <span class="material-symbols-outlined text-primary">call</span>
                <div>
                    <h3 class="font-bold text-on-surface mb-1">Telepon</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">(021) 555-0123</p>
                </div>
            </div>
        </div>

        {{-- Form Kontak (AMC-216: submit sungguhan ke POST /kontak) --}}
        <div
            x-data="{
                submitted: false,
                submitting: false,
                whatsappUrl: null,
                serverError: null,
                errors: {},
                form: { nama: '', phone: '', kebutuhan: '', pesan: '' },
                validate() {
                    this.errors = {};
                    if (! this.form.nama.trim()) this.errors.nama = 'Nama lengkap wajib diisi.';
                    if (! /^[0-9+\-\s]{8,15}$/.test(this.form.phone.trim())) this.errors.phone = 'Nomor HP/WhatsApp tidak valid.';
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
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                            },
                            body: JSON.stringify({
                                nama: this.form.nama,
                                phone: this.form.phone,
                                kebutuhan: this.form.kebutuhan,
                                pesan: this.form.pesan,
                            }),
                        });

                        const data = await response.json();

                        if (response.status === 422) {
                            this.errors = {
                                nama: data.errors?.nama?.[0],
                                phone: data.errors?.phone?.[0],
                                pesan: data.errors?.pesan?.[0],
                            };

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
            class="md:col-span-3 bg-white shadow-md rounded-lg p-8"
        >
            <h2 class="font-headline-lg text-headline-lg text-primary mb-8">Kirim pesan ke tim kami</h2>

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

                <button
                    type="submit"
                    :disabled="submitting"
                    :class="submitting && 'opacity-60 cursor-not-allowed'"
                    class="btn-fill w-full bg-primary-container text-white font-bold py-4 rounded-lg hover:shadow-md transition-all"
                >
                    <span x-show="! submitting">Kirim Pesan</span>
                    <span x-show="submitting" x-cloak>Mengirim...</span>
                </button>
            </form>
        </div>
    </section>
@endsection
